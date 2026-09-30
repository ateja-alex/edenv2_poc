<tr class="hidden-xs hidden-sm">
	<td colspan="5" class="no-border"></td>
	<td colspan="2" class="text-bold css_ligne_article_supplementaire css_no_border border-left-none">
		{{ modele('article', $article_id)->designation }}
	</td>
	@php 
		$type_de_la_promotion = $volet['volet_manoeuvre'];
		if(modele('article', $article_id)->commande == 1)
			$type_de_la_promotion = 'commandes';
	@endphp
	<td class="text-bold css_ligne_article_supplementaire css_no_border css_col_pu_ttc_panier">
		<div class="css_table_divide">
			@if(empty(modele('article', $article_id)->tarif))
				Offert
			@else
				{{ number_format(modele('article', $article_id)->tarif, 2, ',', ' ') }} &euro;
			@endif
		</div>
	</td>

	
	@if(\App\Managements\Ecommerce_management::promo_sur_volet_amc() !== false || !empty($forcer_remise))
		<td class="text-bold css_ligne_article_supplementaire css_no_border">
			<div class="css_table_divide">
			@if(empty($forcer_remise))
				- {{\App\Managements\Ecommerce_management::promo_sur_volet_amc()[$type_de_la_promotion]}} %
			@else
				- {{$forcer_remise}} %
			@endif
			</div>
		</td>
		<td class="text-bold css_ligne_article_supplementaire css_no_border"><div class="css_table_divide">
			@if(empty(modele('article', $article_id)->tarif))
				Offert
			@else
				@if(empty($forcer_remise))
					{{ montant((100 - \App\Managements\Ecommerce_management::promo_sur_volet_amc()[$type_de_la_promotion]) / 100 * modele('article', $article_id)->tarif) }} &euro;
				@else
					{{ montant((100 - $forcer_remise) / 100 * modele('article', $article_id)->tarif) }} &euro;
				@endif
			@endif
			
		</div></td>
		<td class="text-bold css_ligne_article_supplementaire css_no_border css_table_col_quantite_panier"><div class="css_table_divide">@if(isset($forcer_quantite)) {{ $forcer_quantite }} @else {{ $volet['quantite'] }} @endif</div></td>
		<td class="text-bold css_ligne_article_supplementaire css_no_border css_table_col_tarif_panier border-right-none">
			<div class="css_table_divide">
				@if(empty(modele('article', $article_id)->tarif))
					Offert
				@else
					@if(empty($forcer_remise))
						@if(isset($forcer_quantite))
							{{ montant((100 - \App\Managements\Ecommerce_management::promo_sur_volet_amc()[$type_de_la_promotion]) / 100 * modele('article', $article_id)->tarif * $forcer_quantite) }} &euro;
						@else
							{{ montant((100 - \App\Managements\Ecommerce_management::promo_sur_volet_amc()[$type_de_la_promotion]) / 100 * modele('article', $article_id)->tarif * $volet['quantite']) }} &euro;
						@endif	
					@else
						@if(isset($forcer_quantite))
							{{ montant((100 - $forcer_remise) / 100 * modele('article', $article_id)->tarif * $forcer_quantite) }} &euro;
						@else
							{{ montant((100 - $forcer_remise) / 100 * modele('article', $article_id)->tarif * $volet['quantite']) }} &euro;
						@endif	
					@endif	
				@endif
			</div>	
		</td>
	@else
		<td class="text-bold css_ligne_article_supplementaire css_no_border"><div class="css_table_divide">@if(isset($forcer_quantite)) {{ $forcer_quantite }} @else {{ $volet['quantite'] }} @endif</div></td>
		<td class="text-bold css_ligne_article_supplementaire css_no_border border-right-none">
			<div class="css_table_divide">
				@if(empty(modele('article', $article_id)->tarif))
					Offert
				@else
					@if(isset($forcer_quantite))
						{{ montant(modele('article', $article_id)->tarif * $forcer_quantite) }} &euro;
					@else
						{{ montant(modele('article', $article_id)->tarif * $volet['quantite']) }} &euro;
					@endif	
				@endif
				
			</div>
		</td>
	@endif

</tr>
{{-- Séparation ligne --}}
<tr class="css_divider_bottom">
	<td></td>
</tr>