<p style="text-align: right;cursor: pointer;" class="js_fermer_detail_ligne"><i class="fa fa-times" aria-hidden="true"></i></p>
@if($management->_type_element != "transformation_stocks")
	<table class="table table-bordered" width="100%" cellspacing="0" colspan="8">
		<thead>
			<tr>
				<th>@traduction('interface.listes.details_ligne.code_article')</th>
				<th>@traduction('interface.listes.details_ligne.designation')</th>
				<th>@traduction('interface.listes.details_ligne.pu_ht')</th>
				<th>@traduction('interface.listes.details_ligne.quantite')</th>
				<th>@traduction('interface.listes.details_ligne.total_ht')</th>
				<th>@traduction('interface.listes.details_ligne.remise')</th>
				<th>@traduction('interface.listes.details_ligne.total_ht_remise')</th>
				<th>@traduction('interface.listes.details_ligne.tva')</th>

			</tr>
		</thead>
		<tbody>

			@foreach($lignes as $ligne)
				<tr>
					<td>
						<a href="{{ route('base_eden.fiche.index', array('article', $ligne->article_id), false) }}">{!! $ligne->code_article !!}</a>
					</td>
					<td>
						{!! $ligne->designation !!}
					</td>
					<td>
						{!! montant($ligne->tarif, fonctionnalite('nombre_de_chiffres_decimaux_sur_les_tarif',',', ' ')) !!}
						{!! maquette('devise_application_symbole') !!}
					</td>
					<td>
						{!! montant($ligne->quantite, 0) !!}	
					</td>
					<td>
						{!! montant($ligne->tarif * $ligne->quantite, fonctionnalite('nombre_de_chiffres_decimaux_sur_les_tarif',',', ' ') )  !!}{!! maquette('devise_application_symbole') !!}

					</td>

					<td>
						{!! $ligne->remise !!}%
					</td>

					<td>
						{!! montant($ligne->tarif * $ligne->quantite * (1 - $ligne->remise / 100) , fonctionnalite('nombre_de_chiffres_decimaux_sur_les_tarif',',', ' ') )  !!}{!! maquette('devise_application_symbole') !!}
					</td>
					<td>
						{!! $ligne->tva !!}	%
					</td>
				</tr>
			@endforeach
		</tbody>
	</table>
@else
	<table class="table table-bordered" width="100%" cellspacing="0" colspan="8">
		<thead>
			<tr>
				<th>#</th>
				<th>@traduction('interface.listes.details_ligne.quantite_conditionnement_de_depart')</th>
				<th>@traduction('interface.listes.details_ligne.conditionnement_arrivee')</th>
				<th>@traduction('interface.listes.details_ligne.quantite_conditionnement_arrivee')</th>
				<th>@traduction('interface.listes.details_ligne.quantite_colisee_arrivee')</th>
			</tr>
		</thead>
		<tbody>

			@foreach($lignes as $ligne)
				<tr>
					<td>
						{!! $ligne->id !!}
					</td>
					<td>
						{!! floatval($ligne->quantite_depart) !!}
					</td>
					@if($ligne->conditionnement_id != 0)
						<td>
							{!! ($cond = modele('conditionnement')->where('id',$ligne->conditionnement_id)->first()) ? floatval($cond['quantite']) : 0 !!}
						</td>
					@else
						<td>
							@traduction('interface.listes.details_ligne.sans_conditionnement')
						</td>
					@endif
					<td>
						{!! ($mouv = modele('mouvement_de_stock')->where('transformation_stocks_id',$ligne->id)->where('transformation_stock_total',null)->first()) ? floatval($mouv['quantite_conditionnement']) : 0 !!}
					</td>
					<td>
						{!! ($cond = modele('conditionnement')->where('id',$ligne->conditionnement_id)->first()) && ($mouv = modele('mouvement_de_stock')->where('transformation_stocks_id',$ligne->id)->where('transformation_stock_total',null)->first()) ? floatval($cond['quantite']) * floatval($mouv['quantite_conditionnement']) : 0 !!}
					</td>
				</tr>
			@endforeach
		</tbody>
	</table>
@endif
<br/>

<div class="row">
	@foreach($pieces_jointes as $piece_jointe)
		<div class="col-md-3">
			<div class="css_block_element_biblio">
				<div class="css_apercu_biblio">
					
					@if(in_array(strtolower($piece_jointe->extension), array('png', 'jpg', 'jpeg')))
						<img src="{{ storage_path('app/'.$piece_jointe->url_sur_serveur) }}" />
					@else
					
						<span class="fa fa-file"></span>
						.{{ $piece_jointe->extension }}<br/>
						<span style="font-size: 9px">{{ $piece_jointe->nom }}</span>
					@endif
					
					<div class="boutons"
						 style="position:absolute;float:right;opacity:0"
						 onmouseover="$(this).css('opacity',1);"
						 onmouseout="$(this).css('opacity',0);">

						<a
							href="/eden/fiche/{{$management->_type_element}}/{{$management->modele->id}}/telecharger_piece_jointe/{{$piece_jointe->id}}"
							target="_blank"
							style="font-size:11px;padding:5px;cursor:pointer;margin-left:10%;min-width:100px;width:80%;"
							class="btn btn-sm btn-success">@traduction('interface.listes.details_ligne.ouvrir')</a>						
					</div>

				</div>
			</div>
		</div>
	@endforeach
</div>
