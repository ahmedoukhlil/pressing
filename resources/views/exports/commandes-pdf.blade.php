<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <style>
        html, body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111827;
            direction: rtl;
            unicode-bidi: bidi-override;
            text-align: right;
        }
        h2 { margin: 0 0 8px; font-size: 16px; }
        .meta { margin-bottom: 10px; font-size: 11px; color: #4b5563; }
        table { width: 100%; border-collapse: collapse; direction: rtl; }
        th, td { border: 1px solid #d1d5db; padding: 6px; text-align: right; }
        th { background: #f3f4f6; }
        .num { direction: ltr; unicode-bidi: isolate; display: inline-block; }
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
            الحالة:
            @php $statutLabels = ['en_cours'=>'قيد المعالجة','pret'=>'جاهز','livre'=>'تم التسليم','annule'=>'ملغى']; @endphp
            {{ $statutLabels[$statut] ?? $statut }}
        @endif
        &nbsp;·&nbsp;
        عدد الطلبات: {{ $commandes->count() }}
    </div>

    <table>
        <thead>
            <tr>
                <th>اسم الزبون</th>
                <th>رمز الزبون</th>
                <th>رقم الهاتف</th>
                <th>رقم الطلب</th>
                <th>إجمالي القطع</th>
            </tr>
        </thead>
        <tbody>
            @forelse($commandes as $commande)
                <tr>
                    <td>{{ $commande->client?->full_name ?: '-' }}</td>
                    <td><span class="num">{{ $commande->client?->code_client ?: '-' }}</span></td>
                    <td><span class="num">{{ $commande->client?->telephone ?: '-' }}</span></td>
                    <td><span class="num">{{ $commande->numero_commande }}</span></td>
                    <td><span class="num">{{ (int) ($commande->total_pieces ?? 0) }}</span></td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">لا توجد بيانات.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

