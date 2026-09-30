@extends('eden::fiche')

@section('title') {{traduction('module_sur_fiche.fiche.titre')}} {{  table_libre($type_element)->element }} @stop

@section('options_fil_ariane')
					
	<!-- supprimer -->
	<i class="css_action_icon primaire fa fa-fw fa-trash" @click="suppression_fiche('{{$management_element->_type_element}}','{{ $management_element->modele->id }}')" title="{{ traduction('module_sur_fiche.fiche_zone_transporteur.supprime') }}" data-toggle="tooltip">
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
				<div class="col-md-6">
					<formulaire-fiche :route="'{{ route('base_eden.element.enregistrer', [$management_element->_type_element, $management_element->modele->id]) }}'" :type_element="'{{$management_element->_type_element}}'" :element_id='{{ $management_element->modele->id }}'>
						<template slot="titre">{{ $management_element->affiche() }}</template>
					</formulaire-fiche>
				</div>

				<div class="col-md-6">
					<div class="card mb-3">
						<div class="card-header">
							<div style="display:flex;justify-content:space-between">
								<h4>@traduction('module_sur_fiche.fiche_zone_transporteur.code_postaux')</h4>

								<button class="btn btn-success"  data-toggle="modal" data-target="#addModal">@traduction('module_sur_fiche.fiche_zone_transporteur.ajouter')</button>
							</div>
						</div>
						<div class="card-body">
							<table class="table">
								<thead>
								  	<tr>
										<th scope="col">@traduction('module_sur_fiche.fiche_zone_transporteur.ref')</th>
										<th scope="col">@traduction('module_sur_fiche.fiche_zone_transporteur.pays')</th>
										<th scope="col">@traduction('module_sur_fiche.fiche_zone_transporteur.departement')</th>
										<th>@traduction('module_sur_fiche.fiche_zone_transporteur.action')</th>
								  	</tr>
								</thead>
								<tbody>

										<tr v-for="(localisation, index) in localisations_de_la_liste" :key="index">
											<td>@{{localisation.id}}</td>
											<td>@{{localisation.nom}}</td>
											<td>@{{localisation.departement}}</td>
											<td><button class="btn btn-danger" @click="remove(localisation.id, index)">@traduction('module_sur_fiche.fiche_zone_transporteur.supprimer')</button></td>
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
			  		<h5 class="modal-title" id="exampleModalLabel">@traduction('module_sur_fiche.fiche_zone_transporteur.titre_modal')</h5>
			  		<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
			  		</button>
				</div>
				<div class="modal-body css_form">
					<div class="row">
						<div class="col-md-6">@traduction('module_sur_fiche.fiche_zone_transporteur.pays')</div>
						<div class="col-md-6">{!! management('zone_transporteur_cp')->champ('pays_id')->cree() !!}</div>
					</div>
					<div class="row">
						<div class="col-md-6">@traduction('module_sur_fiche.fiche_zone_transporteur.departement')</div>
						<div class="col-md-6">{!! management('zone_transporteur_cp')->champ('departement')->cree() !!}</div>
					</div>
				</div>
				<div class="modal-footer">
			  		<button type="button" class="btn btn-secondary" data-dismiss="modal">@traduction('interface.modales.fermer')</button>
			 		<button type="button" class="btn btn-primary" @click="sauvegarder">@traduction('interface.modales.enregistrer')</button>
				</div>
		  	</form>
		</div>
	</div>
	
@endsection

@section('donnees_pour_vuejs_data')
	{{ $management_element->_type_element }}: {!! $management_element->modele !!},
	 localisations_de_la_liste: {!! $cp !!},
	 localisation_selectionne : [],
	 zone_transporteur_cp: {},
@endsection


@section('donnees_pour_vuejs_methods')

 	sauvegarder : function() {

		$.ajax({

			method: 'POST',
			dataType: 'json',
			data: $('#addModal form').serialize(),
			url: '{!! route('base_eden.fiche.index_post', [$management_element->_type_element, $management_element->modele->id, 'ajouterDepartement' ]) !!}'
		}).done(function(donnees) {

			//console.log(donnees)
			if(donnees.success) {

				vue_instance.$data.localisations_de_la_liste.push(donnees.localisation);
				$('#addModal').modal('hide');
			}
		});
	},
	 
	remove : function(id, index) {

		var data = {
			id_localisation : id,
		}

		$.ajax({

			method: 'POST',
			dataType: 'json',
			data: data,
			url: '{!! route('base_eden.fiche.index_post', [$management_element->_type_element, $management_element->modele->id, 'enleverDepartement' ]) !!}'
		}).done(function(donnees) {

			//console.log(donnees)
			if(donnees.success) {

				vue_instance.$data.localisations_de_la_liste.splice(index,1);
			}
		}); 
	},

@endsection