@extends('eden::templates.template')

@section('title') Tables libres @stop

@section('content')
	<div class="content-wrapper" >
		<div id="base-content" class="container-fluid">

			@include('eden::includes.fil_ariane', ['fil_ariane' => array(
				array('route' => 'parametrage.index', 'nom' => 'Paramétrage'),
				array('nom' => 'Tables libres')
			)])

			<div class="row">
				<div class="col-md-12">
					<div class="card mb-3">
						<div class="card-header d-flex align-items-center">
							<h4>
								Tables libres
							</h4>
							<span class="css_ajouter_element ml-auto" data-toggle="tooltip" data-placement="left" title="Nouvelle table libre" @click="ajouter">
								<i class="css_action_icon fa fa-fw fa-plus-square"></i>
							</span>

							<input class='css_input_recherche_liste ml-3' style="padding-left: 5px" type="text" name="recherche_champs_libres" v-model="recherche_tables_libres" placeholder="Recherche"/>
							<div class="css_btn_recherche_liste">
								<i class="css_action_icon fa fa-search" aria-hidden="true"></i>
							</div>
						</div>
						<div class="card-body">
							<div class="table-responsive">
								<table class="table table-bordered table-hover" id="liste_tables_libres" width="100%" cellspacing="0">
									<thead>
										<tr>
											<th scope="col">#</th>
											<th scope="col">Options</th>
											<th scope="col">Nom</th>
											<th scope="col">Type_element</th>
											<th scope="col">Disponible recherche rapide</th>
											<th scope="col">Création rapide</th>
										</tr>
									</thead>
									<tbody>
										<template v-for="(tables_libres, module) in liste_tables_libres">
											<tr>
												<td colspan="7">@{{ module }} </td>
											</tr>
											<tr v-for="(table_libre, index) in tables_libres" :key="table_libre.id" v-show="table_libre.nom_table.indexOf(recherche_tables_libres.toLowerCase()) != -1 || table_libre.type_element.indexOf(recherche_tables_libres.toLowerCase()) != -1 || table_libre.element.indexOf(recherche_tables_libres.toLowerCase()) != -1">

												<td><span class="css__lien"  @click="modification_table_libre(table_libre.id)">@{{ table_libre.id }}</span></td>
												<td><a class="css__lien" v-bind:href="'eden/maintenance/maj_chaine_tags_ajax/'+ table_libre.type_element" target="_blank" v-if="table_libre.vue_sql != 1">MAJ index recherche</a></td>
												<td><a v-bind:href="'eden/parametrage/table_libre/zoom/'+ table_libre.type_element">@{{ table_libre.nom_table }}  <span v-if="table_libre.vue_sql == 1" class="badge badge-warning">VUE SQL</span></td>
												<td><a v-bind:href="'eden/parametrage/table_libre/zoom/'+ table_libre.type_element">@{{ table_libre.type_element }}</td>

												<td>
													<template v-if="table_libre.vue_sql != 1">
													<div class="badge badge-success" @click="changement_etat($event,'disponible_recherche_rapide',table_libre.nom_table_sql,module,index)" :disponible_recherche_rapide="1" v-show="table_libre.disponible_recherche_rapide == 1">Disponible recherche rapide</div>
													<div class="badge badge-danger" @click="changement_etat($event,'disponible_recherche_rapide',table_libre.nom_table_sql,module,index)" :disponible_recherche_rapide="0" v-show="table_libre.disponible_recherche_rapide == 0">Disponible recherche rapide</div>
													</template>
												</td>
												<td>
													<template v-if="table_libre.vue_sql != 1">
														<div class="badge badge-success" @click="changement_etat($event,'creation_rapide',table_libre.nom_table_sql,module,index)" :creation_rapide="1" v-show="table_libre.creation_rapide == 1">Création rapide</div>
														<div class="badge badge-danger" @click="changement_etat($event,'creation_rapide',table_libre.nom_table_sql,module,index)" :creation_rapide="0" v-show="table_libre.creation_rapide == 0">Création rapide</div>
													</template>
												</td>
											</tr>
										</template>

									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Modal modification table libre -->
	@include('eden::parametrage.include.modal_modification_table_libre')

	<!-- Modal ajout élément -->
	<div class="modal fade" id="modal_ajout_element" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Gestion des tables libres</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<form action="#" method="post" class="css_form" id="formulaire_table_libre">
						{{ csrf_field() }}
						<input type="hidden" name="id_table" v-model="table_libre_modele.id_table" />
						<input type="hidden" name="type_element" v-model="table_libre_modele.type_element" />

						<div class="row">
							<div class="col-sm-12 css_form_ligne_titre">Informations générales</div>
						</div>
						<div class="row">
							<div class="col-sm-2">Nom *</div>
							<div class="col-sm-4">
								<input type="text" name="nom_table" v-model="table_libre_modele.nom_table"
									@change="calcul_nom_sql('table_libre_modele.element', 'table_libre_modele.nom_table')">
							</div>
							<div class="col-sm-2">Element *</div>
							<div class="col-sm-4">
								<input type="text" name="element" @change="calcul_nom_sql('table_libre_modele.element')" v-model="table_libre_modele.element">
							</div>
						</div>
						<div class="row">
							
						</div>
						<div class="row">
							<div class="col-sm-2">Element Pluriel *</div>
							<div class="col-sm-4"><input type="text" name="element_pluriel" v-model="table_libre_modele.element_pluriel" /></div>
						</div>
						@if(fonctionnalite('utiliser_extranet'))
							<div class="row">
								<div class="col-sm-2">Type de profil extranet</div>
								<div class="col-sm-4">
									<select name="type_profil_extranet" v-model="table_libre_modele.type_profil_extranet">
										<option value="client">Client</option>
										<option value="contact">Contact</option>
									</select>
								</div>
							</div>
							<div class="row">
								<div class="col-sm-2">Champ profil extranet</div>
								<div class="col-sm-4"><input type="text" name="champ_profil_extranet" v-model="table_libre_modele.champ_profil_extranet" /></div>
							</div>
							<div class="row">
								<div class="col-sm-2">Accés extranet</div>
								<div class="col-sm-4">
									<select name="acces_extranet" v-model="table_libre_modele.acces_extranet">
										<option value="0">Non</option>
										<option value="1">Oui</option>
									</select>
								</div>
							</div>
						@endif
						<div class="row">
							<div class="col-sm-2">Fiche</div>
							<div class="col-sm-4">
								<select name="fiche" v-model="table_libre_modele.fiche">
									<option value="0">Non</option>
									<option value="1">Oui</option>
								</select>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-2">Création rapide</div>
							<div class="col-sm-4">
								<select name="creation_rapide" v-model="table_libre_modele.creation_rapide">
									<option value="0">Non</option>
									<option value="1">Oui</option>
								</select>
							</div>
						</div>


					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
					<button type="button" class="btn btn-danger" data-dismiss="modal" @click="supprimer(table_libre_modele.id_table)" v-show="table_libre_modele.id_table != undefined">Supprimer</button>
					<button type="button" class="btn btn-primary" @click="enregistrer" v-show="table_libre_modele.nom_table != undefined && table_libre_modele.element != undefined && table_libre_modele.element_pluriel != undefined">Enregistrer</button>
				</div>
			</div>
		</div>
	</div>

@endsection

@include('eden::parametrage.include.js_calcul_nom_sql')

@section('donnees_pour_vuejs_data')
	tables_libres: {},
	liste_tables_libres: {!! $liste_tables_libres !!},
	module: "",
	table_libre_modele: { },
	recherche_tables_libres: "",
	table_libre_modification : {},

@endsection

@section('donnees_pour_vuejs_methods')

	ajouter() {

		$('#modal_ajout_element').modal('show');

		// on réinitialise la table
		this.table_libre_modele = {
			description: '',
			feminin: 'e',
			fiche: 0,
			disponible_recherche_rapide: 0,
			nom_table_sql: '',
		};

	},

	//Permet d'afficher les colonnes de la table_libre dans chacun des champs correspondant dans la modale

	afficher(id) {


		$.each(vue_instance.tables_libres, function(osef, table_libre) {

			if(table_libre.id == id) {

				vue_instance.table_libre = table_libre;

			}
			$('#modal_ajout_element').modal('show');
		});


	},

	//Permet d'enregistrer les champs de la modal dans la BDD via Table_libre_controller et Table_libre_management

	enregistrer(){

		$('#modal_ajout_element').modal('show');

		var vue_contexte = this;

		var data = $('#formulaire_table_libre').serialize();
		//console.log(data);

		loading(true);

		// on enregistre les infos de la table libre
		$.post({
		    url: "{{ URL::to("eden/parametrage/table_libre/enregistrer") }}",
			dataType: "json",
			data: $('#formulaire_table_libre').serialize()
		}).done(async function(donnees) {

			loading(false);

			if(donnees.retour !== true) {

				await erreur(donnees.retour);
				return;
			}

			$('#modal_ajout_element').modal('hide');

			vue_contexte.tables_libres = donnees.tables_libres;
			location.reload();
		});

	},

	//Permet de supprimer les tables libres

	async supprimer(id) {

		if (await confirm_eden("Souhaitez vous vraiment supprimer cette table ? ")){

			// on récupère les infos du champ libre
			$.ajax({

				url: "{{ URL::to("eden/parametrage/table_libre") }}/"+id+"/supprimer",
				dataType: "json"
			}).done((table_libre) => {

				this.table_libre_modele = table_libre;
				location.reload();
			});
		}
		else {

			$('#modal_ajout_element').modal('hide');
		}
	},

	changement_etat(event,parametre,id_table,module,index) {

		var event = $(event.target);
		var vue_contexte = this;

		// on enregistre les infos du champ libre
		$.post({
			url: "{{ URL::to("eden/parametrage/table_libre/") }}/"+id_table+'/'+parametre+'/changement_etat',
			data:{
				valeur: event.attr(parametre),
				parametre: parametre
			},
			dataType: "json"
		}).done(async function(donnees) {

			if(donnees.retour !== true) {

				await erreur(donnees.retour);
				return;
			}

			vue_instance.liste_tables_libres[module][index] = donnees.table_libre;

{{--			Créé une erreur dans le console et ne semble pas être utilisé--}}
{{--			vue_instance.maj_notification_modales();--}}

			toastr.success('Mis à jour');

			vue_instance.$forceUpdate();
		});
	},

@endsection

@push('scripts')
	<script>
		$('[name=recherche_tables_libres]').focus();
	</script>
	{{-- Ouvrir modal édition si la route editer table à était utilisé --}}
	@if(Route::currentRouteName() == 'parametrage.table_libre.editer')
	<script>
		window.onload = function() {
			  vue_instance.modification_table_libre({{$id_modal}});
			};
	</script>
	@endif
@endpush
