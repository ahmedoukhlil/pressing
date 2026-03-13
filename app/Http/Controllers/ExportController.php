<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\Consommable;
use App\Models\Depense;
use App\Models\StockMouvement;
use Mpdf\Mpdf;

class ExportController extends Controller
{
    public function commandesPdf()
    {
        $commandes = Commande::query()
            ->forCurrentSuccursale()
            ->with(['client', 'details.service'])
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
}
