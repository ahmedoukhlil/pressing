<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <style>
        html, body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #111827;
            direction: rtl;
            unicode-bidi: bidi-override;
            text-align: right;
        }
        h2 { margin: 0 0 6px; font-size: 15px; }
        .meta { margin-bottom: 10px; font-size: 10px; color: #4b5563; }
        .commande-block { margin-bottom: 18px; page-break-inside: avoid; }
        .commande-header {
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            padding: 6px 8px;
            font-weight: bold;
            font-size: 11px;
        }
        .commande-meta {
            font-size: 10px;
            color: #374151;
            border: 1px solid #d1d5db;
            border-top: none;
            padding: 4px 8px;
        }
        table { width: 100%; border-collapse: collapse; direction: rtl; }
        th, td { border: 1px solid #d1d5db; padding: 5px 6px; text-align: right; }
        th { background: #e5e7eb; font-size: 10px; }
        td { font-size: 10px; }
        .details-table { border-top: none; }
        .num { direction: ltr; unicode-bidi: isolate; display: inline-block; }
        .badge-encours  { color: #1d4ed8; }
        .badge-pret     { color: #d97706; }
        .badge-livre    { color: #15803d; }
        .badge-annule   { color: #dc2626; }
        .total-row td   { font-weight: bold; background: #f9fafb; }
        .grand-total {
            margin-top: 10px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
            color: #111827;
        }
    </style>
</head>
<body>
    <h2>تقرير الطلبات</h2>
    <div class="meta">
        تاريخ التوليد: {{ $generatedAt->format('Y-m-d H:i') }}
        &nbsp;·&nbsp;
        الفترة: {{ $dateDebut }} إلى {{ $dateFin }}
        @if($statut)
            &nbsp;·&nbsp;
            @php $statutLabels = ['en_cours'=>'قيد المعالجة','pret'=>'جاهز','livre'=>'تم التسليم','annule'=>'ملغى']; @endphp
            الحالة: {{ $statutLabels[$statut] ?? $statut }}
        @endif
        &nbsp;·&nbsp;
        عدد الطلبات: {{ $commandes->count() }}
    </div>

    @php
        $grandTotalPieces  = 0;
        $grandTotalMontant = 0;
        $grandTotalPaye    = 0;
    @endphp

    @forelse($commandes as $commande)
        @php
            $statutLabels = ['en_cours'=>'قيد المعالجة','pret'=>'جاهز','livre'=>'تم التسليم','annule'=>'ملغى'];
            $statutClass  = ['en_cours'=>'badge-encours','pret'=>'badge-pret','livre'=>'badge-livre','annule'=>'badge-annule'];
            $totalPieces  = $commande->details->sum('quantite');
            $grandTotalPieces  += $totalPieces;
            $grandTotalMontant += (float) $commande->montant_total;
            $grandTotalPaye    += (float) $commande->montant_paye;
        @endphp

        <div class="commande-block">
            <div class="commande-header">
                <span class="{{ $statutClass[$commande->statut] ?? '' }}">
                    ● {{ $statutLabels[$commande->statut] ?? $commande->statut }}
                </span>
                &nbsp;&nbsp;
                طلبية رقم: <span class="num">{{ $commande->numero_commande }}</span>
                &nbsp;·&nbsp;
                تاريخ الإيداع: <span class="num">{{ optional($commande->date_depot)->format('d/m/Y H:i') }}</span>
            </div>
            <div class="commande-meta">
                الزبون: {{ $commande->client?->full_name ?: '-' }}
                &nbsp;·&nbsp;
                الرمز: <span class="num">{{ $commande->client?->code_client ?: '-' }}</span>
                &nbsp;·&nbsp;
                الهاتف: <span class="num">{{ $commande->client?->telephone ?: '-' }}</span>
                &nbsp;·&nbsp;
                المدفوع: <span class="num">{{ number_format((float)$commande->montant_paye, 2) }}</span> MRU
                &nbsp;·&nbsp;
                المتبقي: <span class="num">{{ number_format((float)$commande->reste_a_payer, 2) }}</span> MRU
            </div>

            <table class="details-table">
                <thead>
                    <tr>
                        <th style="width:45%">الخدمة</th>
                        <th style="width:15%; text-align:center">الكمية</th>
                        <th style="width:20%">سعر الوحدة (MRU)</th>
                        <th style="width:20%">المجموع (MRU)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($commande->details as $detail)
                        <tr>
                            <td>{{ $detail->service?->libelle_ar ?: ($detail->service?->libelle ?: '-') }}</td>
                            <td style="text-align:center"><span class="num">{{ $detail->quantite }}</span></td>
                            <td><span class="num">{{ number_format((float)$detail->prix_unitaire, 2) }}</span></td>
                            <td><span class="num">{{ number_format((float)$detail->sous_total, 2) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" style="color:#9ca3af; text-align:center">لا توجد تفاصيل</td></tr>
                    @endforelse
                    <tr class="total-row">
                        <td colspan="2" style="text-align:center">المجموع: <span class="num">{{ $totalPieces }}</span> قطعة</td>
                        <td colspan="2"><span class="num">{{ number_format((float)$commande->montant_total, 2) }}</span> MRU</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @empty
        <p>لا توجد بيانات.</p>
    @endforelse

    @if($commandes->isNotEmpty())
        <div class="grand-total">
            الإجمالي العام:
            <span class="num">{{ $grandTotalPieces }}</span> قطعة
            &nbsp;·&nbsp;
            المفوتر: <span class="num">{{ number_format($grandTotalMontant, 2) }}</span> MRU
            &nbsp;·&nbsp;
            المحصّل: <span class="num">{{ number_format($grandTotalPaye, 2) }}</span> MRU
            &nbsp;·&nbsp;
            غير محصّل: <span class="num">{{ number_format($grandTotalMontant - $grandTotalPaye, 2) }}</span> MRU
        </div>
    @endif
</body>
</html>
