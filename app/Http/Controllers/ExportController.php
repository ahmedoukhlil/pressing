<?php

namespace App\Http\Controllers;

use App\Models\CaisseOperation;
use App\Models\Commande;
use App\Models\Consommable;
use App\Models\Depense;
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
            ->map(function (CaisseOperation $operation): array {
                return [
                    'date' => Carbon::parse($operation->date_operation),
                    'type' => 'recette',
                    'type_label' => 'إيراد',
                    'designation' => $operation->designation ?: ('تحصيل طلب #' . ($operation->fk_id_commande ?? '-')),
                    'mode_paiement' => $operation->mode_paiement ?: '-',
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
            ->map(function (Depense $depense): array {
                $reference = $depense->reference ? (' - ' . $depense->reference) : '';

                return [
                    'date' => Carbon::parse($depense->date_depense),
                    'type' => 'depense',
                    'type_label' => 'مصروف',
                    'designation' => ($depense->designation ?: 'مصروف') . $reference,
                    'mode_paiement' => $depense->mode_paiement ?: '-',
                    'recette' => 0.0,
                    'depense' => (float) $depense->montant,
                ];
            });

        return $recettes
            ->merge($depenses)
            ->sortByDesc(fn (array $operation) => $operation['date'])
            ->take(2000)
            ->values();
    }

    public function commandesPdf()
    {
        $commandes = Commande::query()
            ->forCurrentSuccursale()
            ->with('client')
            ->withSum('details as total_pieces', 'quantite')
            ->where('statut', 'en_cours')
            ->orderByDesc('date_depot')
            ->get();

        $html = view('exports.commandes-pdf', [
            'commandes' => $commandes,
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
        $beneficeNet = round($totalRecettes - $totalDepenses, 2);
        $periodeLabel = $this->libellePeriode($groupePar, $annee, $mois);

        $fileName = 'finances-details-' . now()->format('Ymd-His') . '.xls';

        return response()->streamDownload(function () use ($operations, $periodeLabel, $totalRecettes, $totalDepenses, $beneficeNet) {
            $handle = fopen('php://output', 'wb');

            // UTF-8 BOM for Excel Arabic support.
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['تقرير التفاصيل المالية (إيرادات/مصروفات)'], "\t");
            fputcsv($handle, ['الفترة', $periodeLabel], "\t");
            fputcsv($handle, ['إجمالي الإيرادات', number_format($totalRecettes, 2, '.', '')], "\t");
            fputcsv($handle, ['إجمالي المصروفات', number_format($totalDepenses, 2, '.', '')], "\t");
            fputcsv($handle, ['صافي الربح', number_format($beneficeNet, 2, '.', '')], "\t");
            fputcsv($handle, [], "\t");

            fputcsv($handle, ['التاريخ', 'النوع', 'البيان', 'طريقة الدفع', 'إيراد (MRU)', 'مصروف (MRU)'], "\t");

            foreach ($operations as $operation) {
                fputcsv($handle, [
                    $operation['date']->format('Y-m-d H:i'),
                    $operation['type_label'],
                    $operation['designation'],
                    $operation['mode_paiement'],
                    $operation['recette'] > 0 ? number_format((float) $operation['recette'], 2, '.', '') : '',
                    $operation['depense'] > 0 ? number_format((float) $operation['depense'], 2, '.', '') : '',
                ], "\t");
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
    }
}
