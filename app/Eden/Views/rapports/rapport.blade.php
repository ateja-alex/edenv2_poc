@extends('eden::templates.template')

@section('title')
	@if($nom_rapport != false)
		{{ $nom_rapport }}
	@else
		{{ traduction('rapport.divers.rapport') }}
	@endif
@endsection

@if($type_rapport == 'carte')
    @push('link_styles')
        <link rel="stylesheet" href="{{ asset('eden/vendors/leaflet/leaflet.css') }}" />
        <link rel="stylesheet" href="{{ asset('eden/vendors/leafletmarkercluster/dist/MarkerCluster.css') }}" />
        <link rel="stylesheet" href="{{ asset('eden/vendors/leafletmarkercluster/dist/MarkerCluster.Default.css') }}" />
        <script src="{{ asset('eden/vendors/leaflet/leaflet.js') }}"></script>
        <script src="{{ asset('eden/vendors/leafletmarkercluster/dist/leaflet.markercluster.js') }}"></script>
    @endpush
@endif

@section('content')
	
	<div id="vue_app" class="popover-container">
		<div class="content-wrapper" >
			<div id="base-content" class="container-fluid">

				{{-- Fil d'arianne --}}
                @include('eden::rapports.include.fil_arianne_rapport')

				@if(admin() && $rapport_modifiable === true)
					@if(in_array($type_rapport, ['indicateur', 'pdf', 'histogramme', 'courbe', 'tableau', 'diagramme_circulaire', 'graphique_funnel', 'carte']))
						<a href="{{ route('parametrage.rapport.parametrer', [$id_rapport]) }}">@traduction('rapport.divers.parametrer_le_rapport')</a>
					@else
						<a href="{{ route('parametrage.rapport.modifier', [$id_rapport]) }}">@traduction('rapport.divers.modifier_le_rapport')</a>
					@endif

				@endif

                @if($type_rapport == 'liste_libre' && !empty($liste_libre))
                    @include('eden::listes.includes.liste_standard', $donnees_liste)
                @else
                    <rapport id_rapport="{{ $id_rapport }}"></rapport>
                @endif

			</div>
		</div>
	</div>

	<!-- Modale affichant détail des stocks -->
	<template v-if="creation_a_la_volee_en_cours">
		<transition name="modal" >
			<div class="modal-mask">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">

						<div class="modal-header">
							<h5 class="modal-title">@traduction('rapport.divers.details_des_stocks') - @{{ detail_article.designation }}</h5>
						</div>

						<div class="modal-body css_form js_selection_element" >

							<table v-if="Object.keys(rapport_detail).length > 0" class="table table-bordered table-hover">

								<thead>
								<tr class="css_tableau_titre">

									<th>@traduction('rapport.gestion_des_stocks.conditionnement')</th>
									<th>@traduction('rapport.gestion_des_stocks.stock_actuel_conditionne')</th>
									<th>@traduction('rapport.gestion_des_stocks.stock_actuel_en_unite')</th>
									<th>@traduction('rapport.gestion_des_stocks.stock_theorique_conditionne')</th>
									<th>@traduction('rapport.gestion_des_stocks.stock_theorique_en_unite')</th>
									<th>@traduction('rapport.gestion_des_stocks.adressage')</th>
									<th>@traduction('rapport.gestion_des_stocks.reference_fournisseur')</th>
								</tr>
								</thead>

								<tbody>
								<template v-for="(detail, id) in rapport_detail">

									<tr>
										<td v-if="id != 0">
											@{{ detail.conditionnement.nom }} (@{{ detail.conditionnement.quantite }} @{{ detail.unité }})
											<span v-if="detail.seuil_alerte" class="badge badge-warning"><i aria-hidden="true" class="fa fa-exclamation-triangle"></i></span>
											<span v-else-if="detail.seuil_mini" class="badge badge-danger"><i aria-hidden="true" class="fa fa-exclamation-triangle"></i></span>
											<span v-else class="badge badge-success"><i aria-hidden="true" class="fa fa-check"></i></span>
										</td>
										<td v-else>
											@traduction('rapport.gestion_des_stocks.sans_conditionnement')
											<span v-if="detail.seuil_alerte" class="badge badge-warning"><i aria-hidden="true" class="fa fa-exclamation-triangle"></i></span>
											<span v-else-if="detail.seuil_mini" class="badge badge-danger"><i aria-hidden="true" class="fa fa-exclamation-triangle"></i></span>
											<span v-else class="badge badge-success"><i aria-hidden="true" class="fa fa-check"></i></span>
										</td>
										<td v-if="id != 0">@{{ detail.quantite_conditionnement }}</td>
										<td v-else>@{{ detail.quantite }}</td>
										<td>@{{ detail.quantite }}</td>
										<td v-if="id != 0">@{{ detail.quantite_conditionnement_theorique }}</td>
										<td v-else>@{{ detail.quantite_theorique }}</td>
										<td>@{{ detail.quantite_theorique }}</td>
										<td>@{{ detail.adressage }}</td>
										<td>@{{ detail.reference_fournisseur }}</td>
									</tr>

								</template>
								</tbody>

							</table>

							<div v-else>
								@traduction('rapport.gestion_des_stocks.aucun_mouvement_de_stock_a_ce_jour')
							</div>

						</div>

						<div class="modal-footer">
							<button type="button" class="btn btn-secondary" @click="creation_a_la_volee_en_cours = false">@traduction('rapport.gestion_des_stocks.fermer')</button>
						</div>

					</div>
				</div>
			</div>
		</transition>
	</template>

	<!-- Modale affichant détail de l'inventaire_tournant -->
	<template v-if="creation_inventaire_tournant_en_cours">
		<transition name="modal" >
			<div class="modal-mask">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">

						<div class="modal-header">
							<h5 class="modal-title">@traduction('rapport.divers.details_des_stocks') - @{{ detail_article.designation }}</h5>
						</div>

						<div class="modal-body css_form js_selection_element" >

							<table v-if="Object.keys(detail_inventaire).length > 0" class="table table-bordered table-hover">

								<thead>
									<tr class="css_tableau_titre">

										<th>@traduction('rapport.divers.unite')</th>
										<th>@traduction('rapport.divers.stock_theorique')</th>
										<th>@traduction('rapport.divers.stock_inventaire_reel')</th>

									</tr>
								</thead>

								<tbody>

									<template v-for="(detail, id) in detail_inventaire">

										<tr>
											<td v-if="id != 0">
												@{{ detail.nom_conditionnement }} (@{{ detail.quantite_conditionnement }})
											</td>
											<td v-else>
												@traduction('composant.gestion_des_stocks.sans_conditionnement')
											</td>
											<td v-if="id != 0">
												@{{ detail.conditionne }} (@{{ detail.unite }})
											</td>
											<td v-else>
												@{{ detail.unite }}
											</td>
											<td>
												<input type="number" v-model="detail.quantite_reel" @wheel.prevent @keydown.up.prevent @keydown.down.prevent>
											</td>
										</tr>

									</template>

								</tbody>

							</table>

						</div>

						<div class="modal-footer">
							<button type="button" class="btn btn-secondary" @click="creation_inventaire_tournant_en_cours = false">@traduction('rapport.gestion_des_stocks.fermer')</button>
							<button type="button" class="btn btn-primary" @click="enregistre_inventaire_tournant()">@traduction('rapport.gestion_des_stocks.enregistrer')</button>
						</div>

					</div>
				</div>
			</div>
		</transition>
	</template>
	
	
@endsection

@push('donnees_pour_vuejs_data')
	article_id: "",
	entrepot_id: "",
	recherche_rapport: '',
	rapport_detail: {},
	detail_article: {},
	detail_inventaire: {},
	detail_inventaire_piece: {},
	creation_a_la_volee_en_cours: false,
	creation_inventaire_tournant_en_cours: false,

	rapport_vuejs: false,

@endpush

@push('donnees_pour_vuejs_methods')
	
	afficher_detail_inventaire_tournant: function(article_id, entrepot_id){

		var vue_composant = this;

		$.post({
			url: "/eden/detail_gestion_des_stocks",
			dataType:"json",
			data:{
				article_id: article_id,
				entrepot_id: entrepot_id,
			}
		}).done(function(donnees){

			vue_composant.article_id = article_id;
			vue_composant.entrepot_id = entrepot_id;
			vue_composant.creation_inventaire_tournant_en_cours = true;

			vue_composant.detail_inventaire = donnees;
		});

	},

	enregistre_inventaire_tournant: function(){

		var vue_composant = this;

		$.post({
			url: "/eden/rapports/ajustement_stock",
			dataType:"json",
			data:{
				article_id: vue_composant.article_id,
				entrepot_id: vue_composant.entrepot_id,
				detail_inventaire: vue_composant.detail_inventaire,
			}
		}).done(function(donnees){

			vue_composant.creation_inventaire_tournant_en_cours = false;
			vue_composant.detail_inventaire = {};
			vue_composant.detail_article = {};

		});

	},
	

@endpush


@push('scripts')

	<script>

		//Fonction utilisé pour afficher le detail des lignes dans le rapport "gestion_des_stocks"
		function affiche_detail_gestion_stock(article_id, entrepot_id){

			$.post({
				url: "/eden/detail_gestion_des_stocks",
				dataType:"json",
				data:{
					article_id: article_id,
					entrepot_id: entrepot_id,
				}
			}).done(function(donnees){

				vue_instance.article_id = article_id;
				vue_instance.entrepot_id = entrepot_id;
				vue_instance.creation_inventaire_tournant_en_cours = true;

				vue_instance.detail_inventaire = donnees;

			});

		}

	</script>

@endpush

