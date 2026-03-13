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
        .commande { border: 1px solid #e5e7eb; border-radius: 6px; padding: 8px; margin-bottom: 12px; }
        .commande-head { margin-bottom: 6px; line-height: 1.7; }
        .small { font-size: 11px; color: #4b5563; }
    </style>
</head>
<body>
    <h2>تقرير الطلبات قيد المعالجة</h2>
    <div class="meta">تاريخ التوليد: {{ $generatedAt->format('Y-m-d H:i') }}</div>

    @forelse($commandes as $commande)
        <div class="commande">
            <div class="commande-head">
                <div><strong>رقم الطلب:</strong> {{ $commande->numero_commande }}</div>
                <div><strong>الزبون:</strong> {{ $commande->client?->full_name ?: '-' }} | <strong>الهاتف:</strong> {{ $commande->client?->telephone ?: '-' }}</div>
                <div class="small">
                    تاريخ الإيداع: {{ $commande->date_depot?->format('Y-m-d H:i') }}
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>الخدمة</th>
                        <th>الكمية</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($commande->details as $detail)
                        <tr>
                            <td>{{ $detail->service?->libelle_ar ?: '-' }}</td>
                            <td><span class="num">{{ (int) $detail->quantite }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2">لا توجد تفاصيل لهذه الطلبية.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @empty
        <table>
            <tbody>
                <tr>
                    <td>لا توجد بيانات.</td>
                </tr>
            </tbody>
        </table>
    @endforelse
</body>
</html>

