@extends('eden::fiche')

@section('title') {{traduction('module_sur_fiche.fiche.titre')}} {{  table_libre($type_element)->element }} @stop

@section('options_fil_ariane')
					
	<!-- supprimer -->
	<i class="css_action_icon primaire fa fa-fw fa-trash" @click="suppression_fiche('{{$management_element->_type_element}}','{{ $management_element->modele->id }}')" title="{{ traduction('module_sur_fiche.fiche.fiche_transporteur.supprime') }}" data-toggle="tooltip">
	</i>
				
@endsection

@section('content')

	<div class="content-wrapper" >
	    <div id="base-content" class="container-fluid css_conteneur_fiche">
			
            {{-- Fil d'arianne --}}
			@include('eden::includes.fil_ariane', ['fil_ariane' => $fil_ariane])

			<!-- l'élément a été supprimé -->
			@if($management_element->modele->inactif == 1)
				<div class="row">
					<div class="col-md-12">
						<div class="alert alert-danger text-center" role="alert">@traduction('module_sur_fiche.fiche.element.supprime')</div>
					</div>
				</div>
			@endif

			<!-- édition de l'élément -->
			<div class="row">
				<div class="col-md-12 col-lg-6">
					<formulaire-fiche :route="'{{ route('base_eden.element.enregistrer', [$management_element->_type_element, $management_element->modele->id]) }}'" :type_element="'{{$management_element->_type_element}}'" :element_id='{{ $management_element->modele->id }}'>
						<template slot="titre"> {!! $management_element->affiche() !!} </template>
					</formulaire-fiche>
				</div>

				<div class="col-md-12 col-lg-6">
					<div class="card mb-3">
						<div class="card-header">
							<div style="display:flex;justify-content:space-between">
								<h4>
									@traduction('module_sur_fiche.fiche_transporteur.titre')
								</h4>

								<button class="btn btn-success"  data-toggle="modal" data-target="#addModal" @click="transporteur_tarif_livraison = {nom:''}">@traduction('module_sur_fiche.fiche_transporteur.ajouter')</button>
							</div>
						</div>
						<div class="card-body">
							<table class="table">
								<thead>
								  	<tr>
										<th scope="col">@traduction('module_sur_fiche.fiche_transporteur.ref')</th>
										<th scope="col">@traduction('module_sur_fiche.fiche_transporteur.zone')</th>
										<th scope="col">@traduction('module_sur_fiche.fiche_transporteur.tarif')</th>
										<th scope="col">@traduction('module_sur_fiche.fiche_transporteur.poids_min')</th>
										<th scope="col">@traduction('module_sur_fiche.fiche_transporteur.poids_max')</th>
										<th>@traduction('module_sur_fiche.fiche_transporteur.action')</th>
								  	</tr>
								</thead>
								<tbody>

										<tr v-for="(tarif, index) in tarifs_de_la_liste" :key="index">
											<td>@{{tarif.id}}</td>
											<td>@{{tarif.nom}}</td>
											<td>@{{tarif.tarif}}</td>
											<td>@{{tarif.poids_min}}</td>
											<td>@{{tarif.poids_max}}</td>
											<td>
												<span class="btn btn-mini btn-xs btn-default css_icone_option_dans_liste css_btn_action_theme fa fa-pencil" title="{{traduction('module_sur_fiche.fiche_transporteur.editer')}}" @click="editer_tarif(tarif)"></span>
												<span class="btn btn-mini btn-xs btn-default css_icone_option_dans_liste css_btn_action_theme fa fa-trash" title="{{traduction('module_sur_fiche.fiche_transporteur.supprimer')}}" @click="remove(tarif.id, index)"></span>
											</td>
									   	</tr>
								</tbody>
							  </table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog" role="document">
		  	<form class="modal-content">
				<div class="modal-header">
			  		<h5 class="modal-title" id="exampleModalLabel">
			  			<span v-if="transporteur_tarif_livraison.id">@traduction('module_sur_fiche.fiche_transporteur.titre_modal_modifier')</span>
			  			<span v-else>@traduction('module_sur_fiche.fiche_transporteur.titre_modal_ajouter')</span>
			  		</h5>
			  		<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
			  		</button>
				</div>
				<div class="modal-body css_form">
					<div class="row">
						<div class="col-md-6">@traduction('module_sur_fiche.fiche_transporteur.zone')</div>
						<div class="col-md-6">{!! management('transporteur_tarif_livraison')->champ('zone_id')->cree() !!}</div>
					</div>
					<div class="row">
						<div class="col-md-6">@traduction('module_sur_fiche.fiche_transporteur.tarif')</div>
						<div class="col-md-6">{!! management('transporteur_tarif_livraison')->champ('tarif')->cree() !!}</div>
					</div>
					<div class="row">
						<div class="col-md-6">@traduction('module_sur_fiche.fiche_transporteur.poids_min')</div>
						<div class="col-md-6">{!! management('transporteur_tarif_livraison')->champ('poids_min')->cree() !!}</div>
					</div>
					<div class="row">
						<div class="col-md-6">@traduction('module_sur_fiche.fiche_transporteur.poids_max')</div>
						<div class="col-md-6">{!! management('transporteur_tarif_livraison')->champ('poids_max')->cree() !!}</div>
					</div>
				</div>
				<div class="modal-footer">
			  		<button type="button" class="btn btn-secondary" data-dismiss="modal">{{traduction('interface.modales.fermer')}}</button>
			 		<button type="button" class="btn btn-primary" @click="sauvegarder">{{traduction('interface.modales.enregistrer')}}</button>
				</div>
		  	</form>
		</div>
	</div>
	
@endsection

@section('donnees_pour_vuejs_data')
	{{ $management_element->_type_element }}: {!! $management_element->modele !!},
	 tarifs_de_la_liste: {!! $tarifs !!},
	 tarif_selectionne : [],
	 transporteur_tarif_livraison : {nom:''},
	 zone_transporteur : {nom:''},
@endsection

@section('donnees_pour_vuejs_methods')

	editer_tarif : function(tarif) {

		this.transporteur_tarif_livraison = tarif,
		$('#addModal').modal('show');
	},

 	sauvegarder : function() {

		$.ajax({

			method: 'POST',
			dataType: 'json',
			data: $('#addModal form').serialize(),
			url: '{!! route('base_eden.fiche.index_post', [$management_element->_type_element, $management_element->modele->id, 'ajouterTarif' ]) !!}'
		}).done(async function(donnees) {

			//console.log(donnees)

			if(donnees.success == true) {

				if(donnees.tarif)
					vue_instance.$data.tarifs_de_la_liste.push(donnees.tarif);

				$('#addModal').modal('hide');

			} else {

				await alerte_eden(donnees.success);
			}
		});
	},
	 
	remove : async function(id, index) {

		if(await confirm_eden('{{traduction('module_sur_fiche.fiche_transporteur.confirmation_suppression')}}')) {

			var data = {
				id_tarif : id,
			}

			$.ajax({

				method: 'POST',
				dataType: 'json',
				data: data,
				url: '{!! route('base_eden.fiche.index_post', [$management_element->_type_element, $management_element->modele->id, 'enleverTarif' ]) !!}'
			}).done(function(donnees) {

				//console.log(donnees)
				if(donnees.success) {

					vue_instance.$data.tarifs_de_la_liste.splice(index,1);
				}
			}); 
		}
	},

@endsection