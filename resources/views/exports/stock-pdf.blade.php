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
        h3 { margin: 14px 0 8px; font-size: 14px; }
        .meta { margin-bottom: 10px; font-size: 11px; color: #4b5563; }
        table { width: 100%; border-collapse: collapse; direction: rtl; margin-bottom: 8px; }
        th, td { border: 1px solid #d1d5db; padding: 6px; text-align: right; }
        th { background: #f3f4f6; }
        .num { direction: ltr; unicode-bidi: isolate; display: inline-block; }
        .small { font-size: 11px; color: #6b7280; }
    </style>
</head>
<body>
    <h2>تقرير المخزون</h2>
    <div class="meta">تاريخ التوليد: {{ $generatedAt->format('Y-m-d H:i') }}</div>

    <h3>الحالة الحالية للمخزون</h3>
    <table>
        <thead>
            <tr>
                <th>المادة</th>
                <th>الوحدة</th>
                <th>المخزون الحالي</th>
                <th>حد التنبيه</th>
                <th>الحالة</th>
            </tr>
        </thead>
        <tbody>
            @forelse($consommables as $item)
                @php
                    $stock = (float) $item->stock_actuel;
                    $seuil = (float) $item->seuil_alerte;
                @endphp
                <tr>
                    <td>{{ $item->libelle }}</td>
                    <td>{{ $item->unite }}</td>
                    <td><span class="num">{{ number_format($stock, 2, ',', ' ') }}</span></td>
                    <td><span class="num">{{ number_format($seuil, 2, ',', ' ') }}</span></td>
                    <td>{{ $stock <= $seuil ? 'تنبيه مخزون' : 'جيد' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">لا توجد بيانات.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h3>حركات المخزون</h3>
    <div class="small">آخر {{ $mouvements->count() }} حركة</div>
    <table>
        <thead>
            <tr>
                <th>التاريخ</th>
                <th>المادة</th>
                <th>النوع</th>
                <th>الكمية</th>
                <th>السبب</th>
                <th>المنفذ</th>
            </tr>
        </thead>
        <tbody>
            @forelse($mouvements as $m)
                <tr>
                    <td>{{ $m->date_mouvement?->format('Y-m-d H:i') }}</td>
                    <td>{{ $m->consommable?->libelle }}</td>
                    <td>{{ $m->type_mouvement === 'entree' ? 'دخول' : 'خروج' }}</td>
                    <td><span class="num">{{ number_format((float) $m->quantite, 2, ',', ' ') }}</span></td>
                    <td>{{ $m->motif ?: '-' }}</td>
                    <td>{{ $m->user?->name ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">لا توجد حركات.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
