<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>وصل {{ $commande->numero_commande }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; width: 280px; margin: 0 auto; color: #111; }
        .line { border-top: 1px dashed #777; margin: 8px 0; }
        .row { display: flex; justify-content: space-between; gap: 8px; }
        .muted { color: #555; font-size: 11px; }
        .title { text-align: center; margin: 6px 0; }
        .totals p { margin: 4px 0; }
        .num-ltr { direction: ltr; unicode-bidi: isolate; display: inline-block; }
    </style>
</head>
<body onload="window.print()">
<h3 class="title">{{ $settings['nom_pressing'] ?? config('app.name') }}</h3>
@if(!empty($settings['adresse_pressing']))
    <p class="muted">{{ $settings['adresse_pressing'] }}</p>
@endif
@if(!empty($settings['telephone_pressing']))
    <p class="muted">الهاتف: {{ $settings['telephone_pressing'] }}</p>
@endif
<p class="row"><strong>الطلب</strong><span>{{ $commande->numero_commande }}</span></p>
<p class="row"><strong>تاريخ الإيداع</strong><span>{{ $commande->date_depot?->format('d/m/Y H:i') }}</span></p>
<p><strong>الزبون:</strong> {{ $commande->client?->full_name }} ({{ $commande->client?->telephone }})</p>
<div class="line"></div>
@foreach($commande->details as $detail)
    <div class="row">
        <span>{{ $detail->service?->libelle_ar ?: '-' }} x{{ $detail->quantite }}</span>
        <span class="num-ltr">{{ number_format((float) $detail->sous_total, 2, ',', ' ') }} MRU</span>
    </div>
@endforeach
<div class="line"></div>
<div class="totals">
    @if((float) $commande->total_remise > 0)
        <p class="row"><strong>الإجمالي قبل الخصم</strong><span class="num-ltr">{{ number_format((float) $commande->montant_total + (float) $commande->total_remise, 2, ',', ' ') }} MRU</span></p>
        @if((float) $commande->remise_depot_montant > 0)
            <p class="row"><strong>خصم الإيداع</strong><span class="num-ltr">{{ number_format((float) $commande->remise_depot_montant, 2, ',', ' ') }} MRU</span></p>
        @endif
        @if((float) $commande->remise_reglement_montant > 0)
            <p class="row"><strong>خصم التسوية</strong><span class="num-ltr">{{ number_format((float) $commande->remise_reglement_montant, 2, ',', ' ') }} MRU</span></p>
        @endif
        <p class="row"><strong>الإجمالي بعد الخصم</strong><span class="num-ltr">{{ number_format((float) $commande->montant_total, 2, ',', ' ') }} MRU</span></p>
    @else
        <p class="row"><strong>الإجمالي</strong><span class="num-ltr">{{ number_format((float) $commande->montant_total, 2, ',', ' ') }} MRU</span></p>
    @endif
    <p class="row"><strong>المدفوع</strong><span class="num-ltr">{{ number_format((float) $commande->montant_paye, 2, ',', ' ') }} MRU</span></p>
    <p class="row"><strong>المتبقي</strong><span class="num-ltr">{{ number_format((float) $commande->reste_a_payer, 2, ',', ' ') }} MRU</span></p>
</div>
@if(!empty($settings['footer_ticket']))
    <div class="line"></div>
    <p class="muted">{{ $settings['footer_ticket'] }}</p>
@endif
</body>
</html>
