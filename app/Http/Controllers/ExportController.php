<?php

namespace App\Http\Controllers;

use App\Models\CaisseOperation;
use App\Models\Commande;
use App\Models\Consommable;
use App\Models\Depense;
use App\Models\ModePaiement;
use App\Models\StockMouvement;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Mpdf\Mpdf;

class ExportController extends Controller
{
    /**
     * @return array{0:string,1:int,2:int}
     */
    private function resolveFinancesFiltres(Request $request): array
    {
        $validated = $request->validate([
            'groupe_par' => ['nullable', 'in:jour,semaine,mois,annee'],
            'annee' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'mois' => ['nullable', 'integer', 'min:1', 'max:12'],
        ]);

        $groupePar = $validated['groupe_par'] ?? 'mois';
        $annee = (int) ($validated['annee'] ?? now()->year);
        $mois = (int) ($validated['mois'] ?? now()->month);

        return [$groupePar, $annee, $mois];
    }

    private function libellePeriode(string $groupePar, int $annee, int $mois): string
    {
        if ($groupePar === 'annee') {
            return 'كل السنوات';
        }

        if ($groupePar === 'mois') {
            return 'سنة ' . $annee;
        }

        if ($groupePar === 'semaine') {
            return 'أسابيع ' . Carbon::createFromDate($annee, $mois, 1)->translatedFormat('F Y');
        }

        return Carbon::createFromDate($annee, $mois, 1)->translatedFormat('F Y');
    }

    private function operationsFinancieres(string $groupePar, int $annee, int $mois): Collection
    {
        $libelles = ModePaiement::query()->pluck('libelle', 'code');

        $recettes = CaisseOperation::query()
            ->forCurrentSuccursale()
            ->select(['id', 'date_operation', 'designation', 'mode_paiement', 'montant_operation', 'fk_id_commande'])
            ->when(in_array($groupePar, ['jour', 'semaine'], true), fn ($q) => $q
                ->whereYear('date_operation', $annee)
                ->whereMonth('date_operation', $mois))
            ->when($groupePar === 'mois', fn ($q) => $q
                ->whereYear('date_operation', $annee))
            ->orderByDesc('date_operation')
            ->get()
            ->map(function (CaisseOperation $operation) use ($libelles): array {
                $code = $operation->mode_paiement;
                return [
                    'date' => Carbon::parse($operation->date_operation),
                    'type' => 'recette',
                    'type_label' => 'إيراد',
                    'designation' => $operation->designation ?: ('تحصيل طلب #' . ($operation->fk_id_commande ?? '-')),
                    'mode_paiement' => $code ? ($libelles[$code] ?? $code) : '-',
                    'recette' => (float) $operation->montant_operation,
                    'depense' => 0.0,
                ];
            });

        $depenses = Depense::query()
            ->forCurrentSuccursale()
            ->where('statut', 'validee')
            ->select(['id', 'date_depense', 'designation', 'mode_paiement', 'montant', 'reference'])
            ->when(in_array($groupePar, ['jour', 'semaine'], true), fn ($q) => $q
                ->whereYear('date_depense', $annee)
                ->whereMonth('date_depense', $mois))
            ->when($groupePar === 'mois', fn ($q) => $q
                ->whereYear('date_depense', $annee))
            ->orderByDesc('date_depense')
            ->get()
            ->map(function (Depense $depense) use ($libelles): array {
                $reference = $depense->reference ? (' - ' . $depense->reference) : '';
                $code = $depense->mode_paiement;
                return [
                    'date' => Carbon::parse($depense->date_depense),
                    'type' => 'depense',
                    'type_label' => 'مصروف',
                    'designation' => ($depense->designation ?: 'مصروف') . $reference,
                    'mode_paiement' => $code ? ($libelles[$code] ?? $code) : '-',
                    'recette' => 0.0,
                    'depense' => (float) $depense->montant,
                ];
            });

        return $recettes
            ->merge($depenses)
            ->sortByDesc(fn (array $operation) => $operation['date'])
            ->values();
    }

    public function commandesPdf(Request $request)
    {
        $dateDebut  = $request->input('date_debut', now()->toDateString());
        $dateFin    = $request->input('date_fin', now()->toDateString());
        $statut     = $request->input('statut', '');
        $recherche  = $request->input('recherche', '');

        $commandes = Commande::query()
            ->forCurrentSuccursale()
            ->with('client')
            ->withSum('details as total_pieces', 'quantite')
            ->when($statut !== '', fn ($q) => $q->where('statut', $statut))
            ->when($dateDebut !== '', fn ($q) => $q->whereDate('date_depot', '>=', $dateDebut))
            ->when($dateFin !== '', fn ($q) => $q->whereDate('date_depot', '<=', $dateFin))
            ->when($recherche !== '', fn ($q) => $q
                ->whereHas('client', fn ($c) => $c
                    ->where('nom', 'like', '%' . $recherche . '%')
                    ->orWhere('prenom', 'like', '%' . $recherche . '%')
                    ->orWhere('telephone', 'like', '%' . $recherche . '%')
                )
                ->orWhere('numero_commande', 'like', '%' . $recherche . '%')
            )
            ->orderByDesc('date_depot')
            ->get();

        $html = view('exports.commandes-pdf', [
            'commandes'   => $commandes,
            'generatedAt' => now(),
            'dateDebut'   => $dateDebut,
            'dateFin'     => $dateFin,
            'statut'      => $statut,
        ])->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L',
            'default_font' => 'dejavusans',
            'directionality' => 'rtl',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
        ]);
        $mpdf->SetDirectionality('rtl');
        $mpdf->WriteHTML($html);

        return response(
            $mpdf->Output('commandes-' . now()->format('Ymd-His') . '.pdf', 'S'),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="commandes-' . now()->format('Ymd-His') . '.pdf"',
            ]
        );
    }

    public function depensesPdf()
    {
        $depenses = Depense::query()
            ->forCurrentSuccursale()
            ->with(['typeDepense', 'fournisseur'])
            ->orderByDesc('date_depense')
            ->get();

        $html = view('exports.depenses-pdf', [
            'depenses' => $depenses,
            'generatedAt' => now(),
        ])->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L',
            'default_font' => 'dejavusans',
            'directionality' => 'rtl',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
        ]);
        $mpdf->SetDirectionality('rtl');
        $mpdf->WriteHTML($html);

        return response(
            $mpdf->Output('depenses-' . now()->format('Ymd-His') . '.pdf', 'S'),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="depenses-' . now()->format('Ymd-His') . '.pdf"',
            ]
        );
    }

    public function stockPdf()
    {
        $consommables = Consommable::query()
            ->orderBy('libelle')
            ->get();

        $mouvements = StockMouvement::query()
            ->with(['consommable', 'user'])
            ->latest('date_mouvement')
            ->limit(500)
            ->get();

        $html = view('exports.stock-pdf', [
            'consommables' => $consommables,
            'mouvements' => $mouvements,
            'generatedAt' => now(),
        ])->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L',
            'default_font' => 'dejavusans',
            'directionality' => 'rtl',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
        ]);
        $mpdf->SetDirectionality('rtl');
        $mpdf->WriteHTML($html);

        return response(
            $mpdf->Output('stock-' . now()->format('Ymd-His') . '.pdf', 'S'),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="stock-' . now()->format('Ymd-His') . '.pdf"',
            ]
        );
    }

    public function financesDetailsPdf(Request $request)
    {
        [$groupePar, $annee, $mois] = $this->resolveFinancesFiltres($request);
        $operations = $this->operationsFinancieres($groupePar, $annee, $mois);

        $totalRecettes = round((float) $operations->sum('recette'), 2);
        $totalDepenses = round((float) $operations->sum('depense'), 2);
        $beneficeNet = round($totalRecettes - $totalDepenses, 2);

        $html = view('exports.finances-details-pdf', [
            'operations' => $operations,
            'periodeLabel' => $this->libellePeriode($groupePar, $annee, $mois),
            'groupePar' => $groupePar,
            'totalRecettes' => $totalRecettes,
            'totalDepenses' => $totalDepenses,
            'beneficeNet' => $beneficeNet,
            'generatedAt' => now(),
        ])->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L',
            'default_font' => 'dejavusans',
            'directionality' => 'rtl',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
        ]);
        $mpdf->SetDirectionality('rtl');
        $mpdf->WriteHTML($html);

        $fileName = 'finances-details-' . now()->format('Ymd-His') . '.pdf';

        return response(
            $mpdf->Output($fileName, 'S'),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ]
        );
    }

    public function financesDetailsExcel(Request $request)
    {
        [$groupePar, $annee, $mois] = $this->resolveFinancesFiltres($request);
        $operations = $this->operationsFinancieres($groupePar, $annee, $mois);

        $totalRecettes = round((float) $operations->sum('recette'), 2);
        $totalDepenses = round((float) $operations->sum('depense'), 2);
        $beneficeNet   = round($totalRecettes - $totalDepenses, 2);
        $periodeLabel  = $this->libellePeriode($groupePar, $annee, $mois);

        $fileName = 'finances-details-' . now()->format('Ymd-His') . '.xls';

        $e = fn (string $v): string => htmlspecialchars($v, ENT_QUOTES | ENT_XML1, 'UTF-8');

        $cell = fn (string $type, string $val, string $style = ''): string =>
            '<Cell' . ($style ? " ss:StyleID=\"{$style}\"" : '') . '>'
            . "<Data ss:Type=\"{$type}\">{$val}</Data></Cell>";

        $numFmt  = fn (float $v): string => number_format($v, 2, '.', '');

        $rows = '';

        // En-tête récapitulatif
        $rows .= '<Row><Cell ss:MergeAcross="5"><Data ss:Type="String">' . $e('تقرير التفاصيل المالية (إيرادات/مصروفات)') . '</Data></Cell></Row>';
        $rows .= '<Row>' . $cell('String', $e('الفترة'), 'Bold') . $cell('String', $e($periodeLabel)) . '</Row>';
        $rows .= '<Row>' . $cell('String', $e('إجمالي الإيرادات'), 'Bold') . $cell('Number', $numFmt($totalRecettes)) . '</Row>';
        $rows .= '<Row>' . $cell('String', $e('إجمالي المصروفات'), 'Bold') . $cell('Number', $numFmt($totalDepenses)) . '</Row>';
        $rows .= '<Row>' . $cell('String', $e('صافي الربح'), 'Bold') . $cell('Number', $numFmt($beneficeNet)) . '</Row>';
        $rows .= '<Row/>';

        // En-tête colonnes
        $rows .= '<Row>'
            . $cell('String', $e('التاريخ'), 'Bold')
            . $cell('String', $e('النوع'), 'Bold')
            . $cell('String', $e('البيان'), 'Bold')
            . $cell('String', $e('طريقة الدفع'), 'Bold')
            . $cell('String', $e('إيراد (MRU)'), 'Bold')
            . $cell('String', $e('مصروف (MRU)'), 'Bold')
            . '</Row>';

        foreach ($operations as $op) {
            $rows .= '<Row>'
                . $cell('String', $e($op['date']->format('Y-m-d H:i')))
                . $cell('String', $e($op['type_label']))
                . $cell('String', $e($op['designation']))
                . $cell('String', $e($op['mode_paiement']))
                . ($op['recette'] > 0 ? $cell('Number', $numFmt((float) $op['recette'])) : $cell('String', ''))
                . ($op['depense'] > 0 ? $cell('Number', $numFmt((float) $op['depense'])) : $cell('String', ''))
                . '</Row>';
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'
            . '<?mso-application progid="Excel.Sheet"?>'
            . '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"'
            . ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"'
            . ' xmlns:x="urn:schemas-microsoft-com:office:excel">'
            . '<Styles>'
            . '<Style ss:ID="Bold"><Font ss:Bold="1"/></Style>'
            . '</Styles>'
            . '<Worksheet ss:Name="' . $e('التفاصيل المالية') . '">'
            . '<Table>'
            . $rows
            . '</Table>'
            . '<WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">'
            . '<DisplayRightToLeft/>'
            . '</WorksheetOptions>'
            . '</Worksheet>'
            . '</Workbook>';

        return response($xml, 200, [
            'Content-Type'        => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Pragma'              => 'no-cache',
        ]);
    }
}
