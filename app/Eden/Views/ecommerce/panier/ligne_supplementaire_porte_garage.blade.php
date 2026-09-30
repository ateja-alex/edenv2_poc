<tr class="hidden-xs hidden-sm">
	<td colspan="3" class="no-border"></td>
	<td colspan="2" class="text-bold css_ligne_article_supplementaire border-left-none css_no_border">
		{{ modele('article', $article_id)->designation }}
	</td>
	<td class="text-bold css_ligne_article_supplementaire css_no_border">
		<div class="css_table_divide">
			{{ number_format(modele('article', $article_id)->tarif, 2, ',', ' ') }} &euro;
		</div>
	</td>
	
	@if(\App\Managements\Ecommerce_management::promo_sur_porte_garage_amc() !== false)
		<td class="text-bold css_ligne_article_supplementaire css_no_border"><div class="css_table_divide">- {{\App\Managements\Ecommerce_management::promo_sur_porte_garage_amc()}} %</div></td>
		<td class="text-bold css_ligne_article_supplementaire css_no_border"><div class="css_table_divide">{{ montant((100 - \App\Managements\Ecommerce_management::promo_sur_porte_garage_amc()) / 100 * modele('article', $article_id)->tarif) }} &euro;</div></td>
		<td class="text-bold css_ligne_article_supplementaire css_no_border"><div class="css_table_divide">@if(isset($forcer_quantite)) {{ $forcer_quantite }} @else {{ $porte_garage['quantite'] }} @endif</div></td>
		<td class="text-bold css_ligne_article_supplementaire css_no_border border-right-none">
			<div class="css_table_divide">
				@if(isset($forcer_quantite))
					{{ montant((100 - parametre('promo_generale_garage_amc')) / 100 * modele('article', $article_id)->tarif * $forcer_quantite) }} &euro;
				@else
					{{ montant((100 - parametre('promo_generale_garage_amc')) / 100 * modele('article', $article_id)->tarif * $porte_garage['quantite']) }} &euro;
				@endif	
			</div>	
		</td>
	@else
		<td class="text-bold css_ligne_article_supplementaire css_no_border"><div class="css_table_divide">@if(isset($forcer_quantite)) {{ $forcer_quantite }} @else {{ $porte_garage['quantite'] }} @endif</div></td>
		<td class="text-bold css_ligne_article_supplementaire css_no_border border-right-none">
			<div class="css_table_divide">
				@if(isset($forcer_quantite))
					{{ montant(modele('article', $article_id)->tarif * $forcer_quantite) }} &euro;
				@else
					{{ montant(modele('article', $article_id)->tarif * $porte_garage['quantite']) }} &euro;
				@endif
			</div>
		</td>
	@endif
</tr>
{{-- Séparation ligne --}}
<tr class="css_divider_bottom">
	<td></td>
</tr>