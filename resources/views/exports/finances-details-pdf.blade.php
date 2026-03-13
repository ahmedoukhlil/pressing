<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تقرير التفاصيل المالية</title>
    <style>
        body {
            font-family: dejavusans, sans-serif;
            font-size: 12px;
            color: #0f172a;
        }
        h1 {
            margin: 0 0 8px;
            font-size: 20px;
        }
        .meta {
            margin-bottom: 14px;
            color: #475569;
            font-size: 11px;
        }
        .summary {
            margin-bottom: 14px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 8px 10px;
            background: #f8fafc;
        }
        .summary-row {
            margin-bottom: 4px;
        }
        .summary-row:last-child {
            margin-bottom: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            vertical-align: top;
        }
        th {
            background: #e2e8f0;
            font-weight: 700;
            text-align: right;
        }
        .num {
            direction: ltr;
            text-align: left;
            unicode-bidi: isolate;
            white-space: nowrap;
        }
        .type-recette {
            color: #047857;
            font-weight: 700;
        }
        .type-depense {
            color: #b91c1c;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <h1>تقرير التفاصيل المالية</h1>
    <div class="meta">
        الفترة: {{ $periodeLabel }} |
        التجميع: {{ $groupePar === 'jour' ? 'يومي' : ($groupePar === 'mois' ? 'شهري' : 'سنوي') }} |
        تاريخ الإنشاء: {{ $generatedAt->format('Y-m-d H:i') }}
    </div>

    <div class="summary">
        <div class="summary-row"><strong>إجمالي الإيرادات:</strong> <span class="num">{{ number_format($totalRecettes, 2, ',', ' ') }} MRU</span></div>
        <div class="summary-row"><strong>إجمالي المصروفات:</strong> <span class="num">{{ number_format($totalDepenses, 2, ',', ' ') }} MRU</span></div>
        <div class="summary-row"><strong>صافي الربح:</strong> <span class="num">{{ number_format($beneficeNet, 2, ',', ' ') }} MRU</span></div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 16%;">التاريخ</th>
                <th style="width: 10%;">النوع</th>
                <th style="width: 36%;">البيان</th>
                <th style="width: 14%;">طريقة الدفع</th>
                <th style="width: 12%;">إيراد (MRU)</th>
                <th style="width: 12%;">مصروف (MRU)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($operations as $operation)
                <tr>
                    <td>{{ $operation['date']->format('Y-m-d H:i') }}</td>
                    <td class="{{ $operation['type'] === 'recette' ? 'type-recette' : 'type-depense' }}">{{ $operation['type_label'] }}</td>
                    <td>{{ $operation['designation'] }}</td>
                    <td>{{ $operation['mode_paiement'] }}</td>
                    <td class="num">{{ $operation['recette'] > 0 ? number_format($operation['recette'], 2, ',', ' ') : '-' }}</td>
                    <td class="num">{{ $operation['depense'] > 0 ? number_format($operation['depense'], 2, ',', ' ') : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center; color:#64748b;">لا توجد بيانات.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
