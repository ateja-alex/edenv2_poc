<!-- Modal modification table libre -->
<template v-if="modal_modification_table_libre">
	<transition name="modal">
		<div class="modal-mask">
			<div class="modal-dialog modal-lg" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Gestion des tables libres <span v-if="table_libre_modification.vue_sql == 1"> : Vue </span></h5>
						<button type="button" class="close" @click="modal_modification_table_libre = false">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<form action="#" method="post" class="css_form">

							<div class="row">
								<div class="col-sm-12 css_form_ligne_titre">Informations générales</div>
							</div>

							<traduction-table ref="traduction_table"  categorie="2" :filtrage_index="table_libre_modification.index_traduction+'.'"></traduction-table>

							@if(fonctionnalite('utiliser_extranet'))

								<div class="row">
									<div class="col-sm-4">Accés extranet</div>
									<div class="col-sm-8">
										<select name="acces_extranet" v-model="table_libre_modification.acces_extranet">
											<option value="0">Non</option>
											<option value="1">Oui</option>
										</select>
									</div>
								</div>

								<template v-if="table_libre_modification.acces_extranet == 1 && table_libre_modification.vue_sql != 1">
									<div class="row">
										<div class="col-sm-4">Type de profil extranet</div>
										<div class="col-sm-8">
											<select name="type_profil_extranet" v-model="table_libre_modification.type_profil_extranet">
												<option value="client">Client</option>
												<option value="contact">Contact</option>
											</select>
										</div>
									</div>

									<div class="row">
										<div class="col-sm-4">Champ profil extranet</div>
										<div class="col-sm-8">
											<select name="champ_profil_extranet" v-model="table_libre_modification.champ_profil_extranet">
												<option v-if="champ_libre.type == 42 && champ_libre.type_element_ajax == table_libre_modification.type_profil_extranet" :value="champ_libre.nom_sql" v-for="champ_libre in champs_libres">@{{ champ_libre.nom }} (@{{ champ_libre.nom_sql }})</option>
											</select>
										</div>
									</div>

									<div class="row">
										<div class="col-sm-12">
											Valeurs forcées pour la création dans l'extranet
										</div>
									</div>
									<div class="row">
										<div class="col-sm-12">
											<table class="table table-bordered">
												<thead>
													<tr>
														<th style="width: 15%;">Champ</th>
														<th>Valeur</th>
														<th style="width: 5%;">Options</th>
													</tr>
												</thead>
												<tbody>
													<tr v-for="(valeur,champ) in table_libre_modification.valeurs_forcees_creation_extranet" :key="champ">
														<td>
															<span v-html="traduction('champs_libres.'+table_libre_modification.type_element+'.'+champ+'.nom')+' ('+champ+')'"></span>
														</td>
														<td>
															<div style="display:flex;align-items:center;gap:5px;">
																<select v-if="champ_libre(champ).type_element_ajax == 'client' || champ_libre(champ).type_element_ajax == 'contact'" style="width: 15%;" v-model="table_libre_modification.valeurs_forcees_creation_extranet[champ]">
																	<option v-if="valeur == '#client_id#' || valeur == '#contact_id#'" :value="valeur_par_defaut_champ(champ)">Valeur manuelle</option>
																	<option v-if="champ_libre(champ).type_element_ajax == 'client'" value="#client_id#">Client</option>
																	<option v-if="champ_libre(champ).type_element_ajax == 'contact'" value="#contact_id#">Contact</option>
																</select>
																<template v-if="valeur != '#client_id#' && valeur != '#contact_id#'">
																	<span v-if="champ_libre(champ).type_element_ajax == 'client' || champ_libre(champ).type_element_ajax == 'contact'">OU</span>
																	<component :is="affichage_champ(champ)"></component>
																</template>
															</div>
														</td>
														<td>
															<span class="fas fa-trash" @click="suppression_valeur_forcee(champ)"></span>
														</td>
													</tr>
													<tr>
														<td colspan="3">
															<div class="col-sm-12">
																<select @change="ajout_ligne_valeur_defaut_extranet" v-model="champ_extranet">
																	<option></option>
																	<option v-for="champ_libre in champs_libres" :value="champ_libre.nom_sql" v-if="champ_libre.nom_sql != table_libre_modification.champ_profil_extranet && !Object.keys(table_libre_modification.valeurs_forcees_creation_extranet).includes(champ_libre.nom_sql)">
																		@{{champ_libre.nom}} (@{{champ_libre.nom_sql}})
																	</option>
																</select>
															</div>
														</td>
													</tr>
												</tbody>
											</table>
										</div>
									</div>
								</template>
							@endif
							<div class="row">
								<div class="col-sm-4">Id des éléments dans l'index de recherche</div>
								<div class="col-sm-8">
									<select name="id_element_recherche" v-model="table_libre_modification.id_element_recherche">
										<option value="0">Non</option>
										<option value="1">Oui</option>
									</select>
								</div>
							</div>
							<div class="row">
								<div class="col-sm-4">Editable par le client</div>
								<div class="col-sm-8">
									<select name="editable_client" v-model="table_libre_modification.editable_client">
										<option value="0">Non</option>
										<option value="1">Oui</option>
									</select>
								</div>
							</div>
							
							<template v-if="table_libre_modification.vue_sql != 1">
							<div class="row">
								<div class="col-sm-4">Création rapide</div>
								<div class="col-sm-8">
									<select name="creation_rapide" v-model="table_libre_modification.creation_rapide">
										<option value="0">Non</option>
										<option value="1">Oui</option>
									</select>
								</div>
							</div>
							<div class="row">
								<div class="col-sm-4">Disponible recherche rapide *</div>
								<div class="col-sm-8">
									<select name="disponible_recherche_rapide" v-model="table_libre_modification.disponible_recherche_rapide">
										<option value="0">Non</option>
										<option value="1">Oui</option>
									</select>
								</div>
							</div>
							<div class="row">
								<div class="col-sm-4">Envoyer Mail *</div>
								<div class="col-sm-8">
									<select name="envoyer_email" v-model="table_libre_modification.envoyer_email">
										<option value="0">Non</option>
										<option value="1">Oui</option>
									</select>
								</div>
							</div>
							<div class="row">
								<div class="col-sm-4">Parametre *</div>
								<div class="col-sm-8">
									<select name="parametre" v-model="table_libre_modification.parametre">
										<option value="0">Non</option>
										<option value="1">Oui</option>
									</select>
								</div>
							</div>
							<div class="row">
								<div class="col-sm-4">Fiche *</div>
								<div class="col-sm-8">
									<select name="fiche" v-model="table_libre_modification.fiche">
										<option value="0">Non</option>
										<option value="1">Oui</option>
									</select>
								</div>
							</div>

							<div class="row">
								<div class="col-sm-4">Template responsive</div>
								<div class="col-sm-8"><textarea type="text" name="template_responsive" v-model="table_libre_modification.template_responsive" ></textarea></div>
							</div>

							<div class="row">
								<div class="col-sm-4">Catégorie</div>
								<div class="col-sm-8">
									<select name="categorie" v-model="table_libre_modification.categorie">
										<option value="element_primaire">Element primaire</option>
										<option value="document_de_vente">Document de vente</option>
										<option value="document_d_achat">Document d'achat</option>
									</select>
								</div>
							</div>

							<div class="row">
								<div class="col-sm-4">Icône Font Awesome</div>
								<div class="col-sm-8"><input type="text" name="icone_fontawesome" v-model="table_libre_modification.icone_fontawesome" /></div>
							</div>

							<div class="row">
								<div class="col-sm-4">Corbeille</div>
								<div class="col-sm-8">
									<select name="logo" v-model="table_libre_modification.corbeille">
										<option value="0">Non</option>
										<option value="1">Oui</option>
									</select>
								</div>
							</div>

							<div class="row">
								<div class="col-sm-4">Ne pas loguer</div>
								<div class="col-sm-8">
									<select name="logo" v-model="table_libre_modification.non_logue">
										<option value="0">Non</option>
										<option value="1">Oui</option>
									</select>
								</div>
							</div>

							@if(parametre('type_synchro_bibliotheque') == 'gdrive')

								<div class="row">
									<div class="col-sm-4">Dossier attribué (Google Drive,...)</div>
									<div class="col-sm-8">
										<select name="synchro_bibliotheque" v-model="table_libre_modification.synchro_bibliotheque">
											<option value="">Aucun dossier</option>
											@foreach(service('google')->recupere_fichiers_drive('', true) as $dossier)
												<option value="{{ $dossier->id }}">{{ $dossier->nom }}</option>
											@endforeach
										</select>
									</div>
								</div>

							@endif

							<div class="row">
								<div class="col-sm-4">Recherche globale sur l'élément avec un LIKE</div>
								<div class="col-sm-8">
									<select name="recherche_globale_like" v-model="table_libre_modification.recherche_globale_like">
										<option value="0">Non</option>
										<option value="1">Oui</option>
									</select>
								</div>
							</div>

							<div class="row">
								<div class="col-sm-4">Gestion du dedoublonnage</div>
								<div class="col-sm-8">
									<select name="dedoublonnage" v-model="table_libre_modification.dedoublonnage">
										<option value="0">Non</option>
										<option value="1">Oui</option>
									</select>
								</div>
							</div>

							</template>

						</form>
					</div>
					<div class="modal-footer">

						<button type="button" class="btn btn-secondary" @click="modal_modification_table_libre = false">Fermer</button>

						<button type="button" class="btn btn-primary" @click="enregistrer_modification_table_libre">Enregistrer</button>
					</div>
				</div>
			</div>
		</div>
	</transition>
</template>

@push('donnees_pour_vuejs_data')

	champs_libres : {!! collect($champs_libres ?? []) !!},
	champ_extranet : null,
	modal_modification_table_libre : false,
@endpush

@push('donnees_pour_vuejs_methods')

	modification_table_libre(id) {

		$.ajax({
			url: "{{ URL::to('eden/parametrage/table_libre') }}/"+id,
			dataType: "json"
		})
		.done((donnee) => {
			this.table_libre_modification = donnee.table_libre_modification;
			this.champs_libres = donnee.champs_libres;
			this.gestion_champs_libres_extranet();
			this.modal_modification_table_libre = true;
		});
	},

	enregistrer_modification_table_libre() {

		loading(true)

		var data = {
			table_libre : this.table_libre_modification
		}

		// on enregistre les infos de la table libre
		$.post({
		    url: "{{ URL::to("eden/parametrage/table_libre/modification") }}",
			dataType: "json",
			data: data,
		}).done(async (donnees) => {

			loading(false);

			if(donnees.success !== true){
				await alerte_eden(donnees.message);
				return;
			}

			this.modal_modification_table_libre = false;

		});

	},

	affichage_champ : function(champ){
		return this.champ_libre(champ).component;
	},

	ajout_ligne_valeur_defaut_extranet : function(){

		var champ_extranet = this.champ_extranet;

		this.champ_extranet = null;

		this.$set(this.table_libre_modification.valeurs_forcees_creation_extranet,champ_extranet,this.valeur_par_defaut_champ(champ_extranet));
	},

	valeur_par_defaut_champ : function(champ){

		var champ_libre_selectionne = this.champ_libre(champ);

		if([10,11,12].includes(champ_libre_selectionne.type))
			return [];

		if([7,15].includes(champ_libre_selectionne.type))
			return "[]";

		return null;
	},

	champ_libre : function(champ){

		for(champ_libre of this.champs_libres){

			if(champ_libre.nom_sql == champ)
				return champ_libre;
		}

		return {};
	},

	suppression_valeur_forcee : function(champ){

		this.$delete(this.table_libre_modification.valeurs_forcees_creation_extranet,champ);

	},

	gestion_champs_libres_extranet : function(){

		var data = {};

		data[this.table_libre_modification.type_element] = this.table_libre_modification.valeurs_forcees_creation_extranet;

		if(Array.isArray(this.table_libre_modification.valeurs_forcees_creation_extranet))
			this.table_libre_modification.valeurs_forcees_creation_extranet = {};

		for(champ of this.champs_libres){

			champ.component = {
				template:champ.champ_creation,
				methods:this.$options.methods,
				data: function(){
					return data;
				},
			};
		}
	},
@endpush
