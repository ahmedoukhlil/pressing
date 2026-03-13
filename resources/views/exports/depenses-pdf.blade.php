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
    <h2>تقرير المصروفات</h2>
    <div class="meta">تاريخ التوليد: {{ $generatedAt->format('Y-m-d H:i') }}</div>

    <table>
        <thead>
            <tr>
                <th>التاريخ</th>
                <th>النوع</th>
                <th>الوصف</th>
                <th>المبلغ</th>
                <th>طريقة الدفع</th>
                <th>المورد</th>
                <th>الحالة</th>
            </tr>
        </thead>
        <tbody>
            @forelse($depenses as $depense)
                <tr>
                    <td>{{ $depense->date_depense?->format('Y-m-d') }}</td>
                    <td>{{ $depense->typeDepense?->libelle }}</td>
                    <td>{{ $depense->designation }}</td>
                    <td><span class="num">{{ number_format((float) $depense->montant, 2, ',', ' ') }}</span></td>
                    <td>{{ $depense->mode_paiement }}</td>
                    <td>{{ $depense->fournisseur?->nom }}</td>
                    <td>{{ $depense->statut }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">لا توجد بيانات.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

