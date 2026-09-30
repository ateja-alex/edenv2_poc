@extends('eden::templates.template')

@section('title') {{ traduction('rapport.divers.rapports') }} @endsection

@section('content')

   	<div class="content-wrapper" >
		<div  id="base-content" class="container-fluid">
			
			{{-- Fil d'arianne --}}
			<div class="row" style="margin-bottom: 2%;">
				<div class="col-md-12">
					<h5><a href="{{ URL::to('eden/accueil') }}" style="color: #212121;" ><i class="fa fa-home" aria-hidden="true" onmouseover="this.style.transform='scale(1.5)';" onmouseout="this.style.transform='scale(1)';" style="transition: transform .2s;"></i></a> > <span style="color: #a3a3a3;">@traduction('rapport.divers.rapports')</span></h5>
				</div>
			</div>

			@if($errors->count() > 0)
				<div class="alert alert-danger">{{ $errors->first() }}</div>
			@endif

			@foreach($categories as $id_categorie => $categorie)
			
				@if($categorie['rapports']->count() == 0)
					@continue
				@endif
				
				<div class="row">
					<div class="col-md-12">
						<div class="card mb-3">
							<div class="card-header">
								<h4>
                                    @traduction('{{ $categorie["index_traduction"] }}')
									@if(admin())
										<a href="{{route('parametrage.rapport.creer')}}"><span class="css__lien" >@traduction('rapport.divers.nouveau_rapport')</span></a>
									@endif
								</h4>
							</div>
							<div class="card-body">
								<div class="row">
									@if(admin())
										<div class="col-md-12">
											<div class="row js_sortable">
									@else
										<div class="col-md-12">
											<div class="row">
									@endif
										@foreach($categorie['rapports'] as $rapport)

											<?php
											/*
											<div class="row " style="margin-bottom: 2%;" data-idrapport="{{$rapport['id_rapport']}}" data-ordre={{$rapport['ordre']}}>
												<div class="col-md-1 " style="text-align: right;">
													@if(moi()->profil_id == 0)
														<i class="fa fa-{{$rapport['icone']}}" id="{{$rapport['id']}}" @click="modifier_rapport" style="color: #b0b0b0; font-size: 40px; margin-right: 10px;"></i>
													@else
														<i class="fa fa-{{$rapport['icone']}}" id="{{$rapport['id']}}" style="color: #b0b0b0; font-size: 40px; margin-right: 10px;"></i>
													@endif
												</div>
												<div class="col-md-5" style="text-align: left;">	
													<div class="row" style="padding-left: 0;">
														<div class="col-md-12" style="padding-left: 0;">
															@if($rapport->liste_libre === false)
																<a href="{{ route('eden.rapport', [$rapport['id_rapport']]) }}" style="font-size: 15px;">{{$rapport['titre']}}</a>
															@else
																<a href="{{ route('liste_libre_rapport_eden', [$rapport['id_rapport']]) }}" style="font-size: 15px;">{{$rapport['titre']}}</a>
															@endif
														</div>
													</div>
													<div class="row" style="padding-left: 0;">
														<div class="col-md-12" style="padding-left: 0;">
															<i style="font-style: italic">{{$rapport['description']}}</i>
														</div>
													</div>	
												</div>
											</div>
											*/
											?>
											<div class="col-md-6">
												@if(isset($rapport->tableau_de_bord))
													<div class="row">
														<div class="col-md-2 " style="text-align: right; margin-bottom: 20px;">
															<i class="fa fa-table" style="color: #b0b0b0; font-size: 40px; margin-right: 10px;"></i>
														</div>
														<div class="col-md-10" style="text-align: left;">	
															<div class="row" style="padding-left: 0;">
																<div class="col-md-12" style="padding-left: 0;">
																	
																	<a href="{{ route('base_eden.tableau_de_bord.index', [$rapport->tableau_de_bord->id]) }}" style="font-size: 15px;">
																		@traduction('{{$rapport->index_traduction}}','titre')
																	</a>
																</div>
															</div>
															<div class="row" style="padding-left: 0;">
																<div class="col-md-12" style="padding-left: 0;">
																	<i style="font-style: italic">
																		@traduction('{{$rapport->index_traduction}}','description')
																	</i>
																</div>
															</div>	
														</div>
													</div>

												@else
													
													<div class="row">
														<div class="col-md-2 " style="text-align: right; margin-bottom: 20px;">
															@if(admin())
																@if($rapport->liste_libre === false)
																	<i class="fa fa-{{$rapport['icone']}}" id="{{$rapport['id']}}" @click="modifier_rapport" style="color: #b0b0b0; font-size: 40px; margin-right: 10px;cursor:pointer;"></i>
																@else
																	<a href="{{route('parametrage.rapport.parametrer',['id_rapport'=>$rapport['id_rapport']])}}"><i class="fa fa-{{$rapport['icone']}}" id="{{$rapport['id']}}" style="color: #b0b0b0; font-size: 40px; margin-right: 10px;cursor:pointer;"></i></a>
																@endif
															@else
																<i class="fa fa-{{$rapport['icone']}}" id="{{$rapport['id']}}" style="color: #b0b0b0; font-size: 40px; margin-right: 10px;"></i>
															@endif
														</div>
														<div class="col-md-10" style="text-align: left;">	
															<div class="row" style="padding-left: 0;">
																<div class="col-md-12" style="padding-left: 0;">
																	
																	@if($rapport->favoris == 1)
																		<span class="fa fa-star" @click="rapport_favoris('{{$rapport['id_rapport']}}')" style="color: #f6bd1b; position: relative; top: -2px; cursor: pointer;"></span>
																	@else
																		<span class="fa fa-star" @click="rapport_favoris('{{$rapport['id_rapport']}}')" style="color: #b0b0b0; position: relative; top: -2px; cursor: pointer;"></span>
																	@endif
																	
																	@if($rapport->liste_libre === false) 
																		<a href="{{ route('base_eden.rapport.index', [$rapport['id_rapport']]) }}" style="font-size: 15px;">
																			@traduction('{{$rapport->index_traduction}}','titre')
																			{!! $rapport['indicateur'] !!}
																		</a>
																	@else
																		<a href="{{ route('base_eden.liste.rapport', [$rapport['id_rapport']]) }}" style="font-size: 15px;">
																			@traduction('{{$rapport->index_traduction}}','titre')
																			{!! $rapport['indicateur'] !!}
																		</a>
																	@endif
																</div>
															</div>
															<div class="row" style="padding-left: 0;">
																<div class="col-md-12" style="padding-left: 0;">
																	<i style="font-style: italic">
																		@traduction('{{$rapport->index_traduction}}','description')
																	</i>
																</div>
															</div>	
														</div>
													</div>
												@endif
											</div>
										@endforeach
										</div>
									</div>
								</div>

								<!-- Modal ajout élément -->
								<template v-if="modal_ajout_element">
									<transition name="modal" >
										<div class="modal-mask">
											<div class="modal-dialog modal-lg" role="document">
												<div class="modal-content">
													<div class="modal-header">
														<h5 class="modal-title">
															@traduction('rapport.divers.gestion_du_rapport')
														</h5>
														<button type="button" class="close" @click="modal_ajout_element = false" aria-label="Close">
															<span aria-hidden="true">&times;</span>
														</button>
													</div>
													<form action="#" method="post" class="css_form" id="formulaire_rapport_modification">
														<div class="modal-body">
															 {{ csrf_field() }}
															<input type="hidden" name="id" v-model="eden_rapport.id" />

															<div class="row">
																<div class="col-sm-12">
																	<traduction-table ref="traduction_table"  categorie="10" :filtrage_index="eden_rapport.index_traduction+'.'"></traduction-table>
																</div>
															</div>
															<div class="row">
																<div class="col-sm-2">@traduction('rapport.divers.categorie')</div>
																<div class="col-sm-4">
																	<select name="categorie" v-model="eden_rapport.categorie">
																		@foreach(\App\Eden\Rapports_libres::categories_index() as $id_categorie_tmp => $index_categorie)
																			<option value="{{ $id_categorie_tmp }}" v-html="traduction('{{$index_categorie}}')"></option>
																		@endforeach
																	</select>
																</div>
																<div class="col-sm-2" style="text-align: right;">@traduction('rapport.divers.icone')</div>
																<div class="col-sm-4">
																	<select name="icone" v-model="eden_rapport.icone">
																		<option value="table">{{ traduction('rapport.divers.table') }}</option>
																		<option value="chart-area">{{ traduction('rapport.divers.chart_area') }}</option>
																		<option value="info">{{ traduction('rapport.divers.info') }}</option>
																	</select>
																</div>
															</div>
															<div class="row">
																<div class="col-sm-2">@traduction('rapport.divers.inactif')</div>
																<div class="col-sm-4">
																	<select name="inactif" v-model="eden_rapport.inactif">
																		<option value="0">{{ traduction('rapport.divers.non') }}</option>
																		<option value="1">{{ traduction('rapport.divers.oui') }}</option>
																	</select>
																</div>
															</div>

															<!-- les objectifs -->
															<template v-if="eden_rapport.type_rapport == 'indicateur'">
																<div class="row">
																	<div class="col-sm-2">@traduction('rapport.divers.type_de_valeur')</div>
																	<div class="col-sm-10">
																		<select name="parametrage_rapport_libre[type_valeur]" v-model="eden_rapport.parametrage_rapport_libre.type_valeur">
																			<option value="">{{ traduction('rapport.divers.aucun') }}</option>
																			<option value="montant">{{ traduction('rapport.divers.montant') }}</option>
																		</select>
																	</div>
																</div>
																<div class="row">
																	<div class="col-sm-2">@traduction('rapport.divers.unites')</div>
																	<div class="col-sm-10">
																		<input type="text" name="parametrage_rapport_libre[unites]" v-model="eden_rapport.parametrage_rapport_libre.unites" />
																	</div>
																</div>
																<div class="row">
																	<div class="col-sm-2">@traduction('rapport.divers.objectif_global')</div>
																	<div class="col-sm-10">
																		<input type="text" name="parametrage_rapport_libre[objectif_global]" v-model="eden_rapport.parametrage_rapport_libre.objectif_global" />
																	</div>
																</div>
																<div class="row">
																	<div class="col-sm-2">@traduction('rapport.divers.objectif_sens')</div>
																	<div class="col-sm-10">
																		<select name="parametrage_rapport_libre[sens]" v-model="eden_rapport.parametrage_rapport_libre.sens">
																			<option value="croissant">{{ traduction('rapport.divers.plus_cest_haut_mieux_cest') }}</option>
																			<option value="décroissant">{{ traduction('rapport.divers.plus_cest_bas_mieux_cest') }}</option>
																		</select>
																	</div>
																</div>
															</template>
															<div class="row" v-if="eden_rapport.type_liste == 1">
																<div class="col-sm-2">@traduction('rapport.divers.kanban')</div>
																<div class="col-sm-10">
																	<select v-model="eden_rapport.kanban" name="kanban">
																		<option value="">{{ traduction('rapport.divers.pas_de_kanban') }}</option>
																		<option v-for="kanban in eden_rapport.champs_libres_kanban" v-bind:value="kanban.nom_sql">@{{ kanban.nom }}</option>
																	</select>
																</div>
																<div class="col-sm-2" v-if="eden_rapport.kanban != '' && eden_rapport.kanban !== null">@traduction('rapport.divers.colonnes_kanban')</div>
																<div class="col-sm-10" v-if="eden_rapport.kanban != '' && eden_rapport.kanban !== null">
																	<template v-for="colonnes in eden_rapport.colonnes_kanban">

																		<template v-for="colonne in colonnes.colonnes">
																			<input type="checkbox" v-if="eden_rapport.kanban == colonnes.champ_libre_sql" v-model="colonne.checked">
																			<span v-if="eden_rapport.kanban == colonnes.champ_libre_sql">
																			@{{ colonne.valeur }}<br>
																			</span>
																		</template>
																	</template>
																</div>
															</div>
															<input type="hidden" name="colonnes_kanban" v-model="JSON.stringify(colonnes_kanban)">
														</div>
														<div class="modal-footer">
															<button type="button" class="btn btn-secondary" @click="modal_ajout_element = false">@traduction('rapport.divers.fermer')</button>
															<button type="button" class="btn btn-primary" @click="enregistrer_modification_rapport" >@traduction('rapport.divers.enregistrer')</button>
														</div>
													</form>
												</div>
											</div>
										</div>
									</transition>
								</template>
							</div>
						</div>
					</div>
				</div>
			@endforeach
		</div>
	</div>


@endsection


@push('donnees_pour_vuejs_data')
	eden_rapport: {
		
		objectif: {},
		parametrage_rapport_libre: {},
	},
	colonnes_kanban: [],
	modal_ajout_element: false,
@endpush

@push('donnees_pour_vuejs_methods')	

	rapport_favoris: function(id_rapport) {
		
		var objet = $(event.target);
		
		loading(true);
		
		$.ajax({
			
			url: "{{ URL::to("eden/rapport/favoris") }}/"+id_rapport,
			dataType: "json"
		}).done(function(resultat) {
			
			if($(objet).hasClass('js_rapport_favoris')) {
					
				$(objet).removeClass('js_rapport_favoris');
				$(objet).css('color', '#b0b0b0');
			}
			else {
				
				$(objet).addClass('js_rapport_favoris');
				$(objet).css('color', '#f6bd1b');
			}
			
			loading(false);
		});
	},

	modifier_rapport(event) {

		var event = $(event.target);

		var vue_contexte = this;

		vue_contexte.modal_ajout_element = true;

		// on récupère les infos du rapport
		$.post({

			url: "{{ URL::to("eden/rapport") }}/"+event.attr('id'),
			dataType: "json"
		}).done(async function(eden_rapport) {

			vue_contexte.eden_rapport = eden_rapport;

			await vue_contexte.$refs.traduction_table != undefined;

			vue_contexte.$refs.traduction_table.charger_traductions();
		});
	},

	enregistrer_modification_rapport() {
		
		var vue_contexte = this;

		// On récupère les infos pour la colonne kanban
		vue_instance.colonnes_kanban = [];
		if(vue_instance.eden_rapport.kanban != "" && vue_instance.eden_rapport.kanban != null){
			vue_instance.eden_rapport.colonnes_kanban.forEach(function(colonnes){
			  
			  	if(colonnes.champ_libre_sql == vue_instance.eden_rapport.kanban){
					vue_instance.colonnes_kanban.push(colonnes);
			  	}
			});
		}
		vue_instance.$forceUpdate();
		loading(true);
		setTimeout(function(){
			// on enregistre les infos du rapport
			$.post({
				
				url: "{{ URL::to("eden/parametrage/rapport/modifier") }}",
				dataType: "json",
				data: 
					$('#formulaire_rapport_modification').serialize(),
			});
			loading(false);
			location.reload();

			vue_contexte.modal_ajout_element = false;
		},2000);
	},
@endpush

@section('scripts')
	<script>
		$('.js_sortable').sortable({

		update : function (event, ui) {

			var position = $(ui.originalPosition['top']);
			var position_origine = $(ui.position['top']);
			var difference = position[0] - position_origine[0];
			var item = ui.item[0];
			var ordre_origine = item.attributes["1"].value;
			var id_actuel = item.attributes["0"].value;

			if (difference > 0) {

				var ordre_nouveau = item.nextSibling.attributes["1"].nodeValue;
            	if (ordre_nouveau != null ) {

					item.setAttribute("data-ordre",ordre_nouveau);
				}
				else{

					item.setAttribute("data-ordre","1");
				}
            	var nb_element_a_modifier = ordre_origine - ordre_nouveau;
            	var nouveau_ordre_element_suivant = parseInt(ordre_nouveau) + 1;
            	var element_a_modifier = item.nextSibling;
            	var id_suivant = element_a_modifier.attributes["0"].value;

            	$.post({
			
						url: "{{ URL::to("/eden/parametrage/rapport/modifier_ordre") }}",
						dataType: "json",
						data: {
							id: id_actuel,
							ordre: ordre_nouveau,
						}
					});

            	for (var i = 1; i <= nb_element_a_modifier; i++) {

					if (element_a_modifier != null) {
	            		element_a_modifier.setAttribute("data-ordre", nouveau_ordre_element_suivant);
	            		$.post({

							url: "{{ URL::to("/eden/parametrage/rapport/modifier_ordre") }}",
							dataType: "json",
							data: {
								id: id_suivant,
								ordre: nouveau_ordre_element_suivant,
							}
						});
            		}


            		if (element_a_modifier != null) {

						element_a_modifier = element_a_modifier.nextElementSibling;
						if (element_a_modifier != null) {

            				id_suivant = element_a_modifier.attributes["0"].value;
            			}
            		}
            		nouveau_ordre_element_suivant++;
            	}

			}

			else{

				var ordre_nouveau = item.previousSibling.attributes["1"].nodeValue;
				if (ordre_nouveau != null ) {

					item.setAttribute("data-ordre",ordre_nouveau);
				}
				else{

					item.setAttribute("data-ordre","1");
				}
            	var nb_element_a_modifier = ordre_nouveau - ordre_origine;
            	var nouveau_ordre_element_suivant = parseInt(ordre_nouveau) - 1;
            	var element_a_modifier = item.previousSibling;
            	var id_suivant = element_a_modifier.attributes["0"].value;

            	$.post({
			
						url: "{{ URL::to("/eden/parametrage/rapport/modifier_ordre") }}",
						dataType: "json",
						data: {
							id: id_actuel,
							ordre: ordre_nouveau,
						}
					});

            	for (var i = 1; i <= nb_element_a_modifier; i++) {

            		if (element_a_modifier != null) {

            			element_a_modifier.setAttribute("data-ordre", nouveau_ordre_element_suivant);
	            		$.post({
				
							url: "{{ URL::to("/eden/parametrage/rapport/modifier_ordre") }}",
							dataType: "json",
							data: {
								id: id_suivant,
								ordre: nouveau_ordre_element_suivant,
							}
						});

            		}
            		
            		if (element_a_modifier != null) {

            			element_a_modifier = element_a_modifier.previousElementSibling;
            			if (element_a_modifier != null) {

            				id_suivant = element_a_modifier.attributes["0"].value;
            			}
            		}
            		nouveau_ordre_element_suivant--;
            	}

			}
			}
		});
	
	</script>
@endsection

