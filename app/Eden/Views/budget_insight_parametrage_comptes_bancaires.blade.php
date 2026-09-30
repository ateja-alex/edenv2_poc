@extends('eden::templates.template')

@section('title') Paramétrage synchronisation bancaire @stop

@php
	$client_id = config('services.budget_insight.client_id');

    if(config('fonctionnalites_integrations.budget_insight_client_id') != null)
        $client_id = config('fonctionnalites_integrations.budget_insight_client_id');
@endphp

@section('content')

	<div class="content-wrapper" >
		<div id="base-content" class="container-fluid">

			<div class="row">
				<div class="col-md-12">
					<div class="card mb-3">
						<div class="card-header">
							<div class="d-flex align-items-center">
								<h4>
									@traduction('interface.parametrage_synchro.titre')
								</h4>
								<a href="https://easydeveloppement.biapi.pro/2.0/auth/webview/connect/select?client_id={{ $client_id }}&redirect_uri={{route('budget_insight.index')}}" class="css__lien ml-3">
									<i class="far fa-plus-square"></i>@traduction('interface.parametrage_synchro.bouton_ajouter_compte')</a>
							</div>
						</div>
						<div class="card-body">
							<div class="table-responsive">
								<table class="table table-bordered table-hover" id="liste_champs_libres" width="100%" cellspacing="0">
									<tr>
										<th scope="col">#</th>
										<th scope="col">@traduction('interface.parametrage_synchro.tableau.colonne.IBAN')</th>
										<th scope="col">@traduction('interface.parametrage_synchro.tableau.colonne.entite')</th>
										<th scope="col">@traduction('interface.parametrage_synchro.tableau.colonne.compte_bancaire')</th>
										<th scope="col">@traduction('interface.parametrage_synchro.tableau.colonne.ordre')</th>
										<th scope="col">@traduction('interface.parametrage_synchro.tableau.colonne.statut')</th>
									</tr>
									<tr v-for="(compte_bancaire_budget_insight,index) in comptes_bancaires_budget_insight">
										<td><span class="css__lien" @click="modifie_synchro_bancaire(compte_bancaire_budget_insight,index)">@{{ compte_bancaire_budget_insight.id }}</span></td>
										<td>@{{ compte_bancaire_budget_insight.iban }}</td>
										<td>@{{ compte_bancaire_budget_insight.entite_id | affiche_entite }}</td>
										<td>@{{ compte_bancaire_budget_insight.compte_bancaire_id | affiche_compte_bancaire }}</td>
										<td>@{{ compte_bancaire_budget_insight.ordre }}</td>
										<td v-if="compte_bancaire_budget_insight.display == 1">@traduction('interface.parametrage_synchro.tableau.colonne.statut.valeur_actif')</td>
										<td v-else>@traduction('interface.parametrage_synchro.tableau.colonne.statut.valeur_desactive')</td>
									</tr>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<div class="modal fade" id="modal_ajout_element" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">@traduction('interface.parametrage_synchro.modale_ajout_element.titre')</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<form action="#" method="post" class="css_form" id="formulaire_element_liste" enctype="multipart/form-data">

						<input type="hidden" id="id_element" v-model="budget_insight_comptes.id" />

						{!! formulaire('budget_insight_comptes') !!}

					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">@traduction('interface.parametrage_synchro.modale_ajout_element.bouton_fermer')</button>
					<button type="button" class="btn btn-danger" v-if="budget_insight_comptes.display == 1" @click="activer_synchro_compte_bancaire('0')">@traduction('interface.parametrage_synchro.modale_ajout_element.bouton_desactiver')</button>
					<button type="button" class="btn btn-primary" v-else @click="activer_synchro_compte_bancaire('1')">@traduction('interface.parametrage_synchro.modale_ajout_element.bouton_activer')</button>
					<button type="button" class="btn btn-primary" @click="enregistrer_synchro_compte_bancaire">@traduction('interface.parametrage_synchro.modale_ajout_element.bouton_enregistrer')</button>
				</div>
			</div>
		</div>
	</div>

@endsection

@push('donnees_pour_vuejs_data')
	
	comptes_bancaires_budget_insight: {!! $comptes_bancaires_budget_insight !!},
	comptes_bancaires_eden: {!! $comptes_bancaires_eden !!},
	budget_insight_comptes: {},
	index_budget_insight_comptes: 0,
@endpush

@push('donnees_pour_vuejs_methods')
	
	modifie_synchro_bancaire: function(budget_insight_comptes,index) {
		
		this.budget_insight_comptes = budget_insight_comptes;

		this.index_budget_insight_comptes = index;
		
		$('#modal_ajout_element').modal('show');
	},
	
	enregistrer_synchro_compte_bancaire: function() {
		
		var formulaire = $('#formulaire_element_liste');

		if(formulaire.length == 0) {

			toastr.error("Le formulaire pour éditer l'élément doit avoir l'id formulaire_element_liste, il n'a pas été trouvé");
			return;
		}

		var url = "eden/element/budget_insight_comptes/"+$('#id_element').val()+"/enregistrer";

		// On afficher le loader
		loading();
		
		var vue_instance = this;

		// on enregistre les infos du compte bancaire
		$.post({

			url: url,
			dataType: "json",
			method: 'POST',
			data: formulaire.serialize(),
			cache: false,
		}).done(async function(donnees) {
			
			// On retire le loader
			loading(false);
			
			if(donnees.retour !== true) {
			
				await erreur(donnees.retour);
				return;
			}

			vue_instance.comptes_bancaires_budget_insight[vue_instance.index_budget_insight_comptes] = donnees.element;

			vue_instance.$forceUpdate();

			$('#modal_ajout_element').modal('hide');
		});
	},

	activer_synchro_compte_bancaire: function(valeur){

		var url = "eden/element/budget_insight_comptes/"+$('#id_element').val()+"/enregistrer";

		// On afficher le loader
		loading();

		var donnees = {'display' : valeur};

		var vue_instance = this;

		// on enregistre les infos du compte bancaire
		$.post({

			url: url,
			dataType: "json",
			method: 'POST',
			data: donnees,
			cache: false,
		}).done(async function(donnees) {

			// On retire le loader
			loading(false);

			if(donnees.retour !== true) {

				await erreur(donnees.retour);
				return;
			}

			//console.log(vue_instance.index_budget_insight_comptes,vue_instance.comptes_bancaires_budget_insight);

			vue_instance.comptes_bancaires_budget_insight[vue_instance.index_budget_insight_comptes] = donnees.element;

			vue_instance.$forceUpdate();

			$('#modal_ajout_element').modal('hide');
		});
	},
	
@endpush

