@extends('eden::templates.template')

@push('styles')
	.input {
	  border: 1px solid #ccc;
	  font-family: inherit;
	  font-size: inherit;
	  padding: 1px 6px;
		background-color: white;
		min-width:200px;
	}
@endpush

@section('title') Paramétrage d'une fiche @stop

@section('content')
	<div class="content-wrapper" >
		<div id="base-content" class="container-fluid">

            @if(!$independant)
                @include('eden::includes.fil_ariane', ['fil_ariane' => array(
                    array('route' => 'parametrage.index', 'nom' => 'Paramétrage'),
                    array('route' => 'parametrage.table_libre.principales', 'nom' => 'Elements paramétrables'),
                    array('route' => 'parametrage.table_libre.zoom','arguments' => [table_libre($type_element)->type_element] , 'nom' => table_libre($type_element)->element),
                    array('nom' => 'Fiche')
                )])
            @endif

			<div class="row">
				<div class="col-md-12">
					<div class="sections_modales" style="margin: unset; position: relative;">
						<div class="section_modale">
							<span class="nom_section"
								  :class="(bloc_affiche == 'modules' ? 'section_active' : '')+' nom_section'"
								  @click="bloc_affiche = 'modules'"
							>Modules fiche</span>
							<div class="card mt-auto contenu_section" v-show="bloc_affiche == 'modules'">
								<div class="card-header">
									<h4 class="d-flex align-items-center">
										Paramétrage fiche {{ $element }}

										<div class="ml-auto" style="display: flex;align-items: center;gap: 0 10px;">

											@includeWhen($document,'eden::parametrage.include.modale_duplication_fiche')

											<div class="nav-item dropdown dropdown_hover d-inline-flex ml_responsive" aria-haspopup="true" aria-expanded="false">
												<div class="css_ajouter_element">
													<i class="css_action_icon fa fa-fw fa-plus-square" data-toggle="tooltip" data-placement="left" title="Ajouter"></i>
												</div>
												<div class="dropdown-menu" aria-labelledby="dropdownMenuButton" style="left:unset;right: 0;">
													<span class="dropdown-item" @click="ajouter_ligne"><i class="fa fa-fw fa-plus-square"></i> Ajouter ligne</span>
													<span class="dropdown-item" @click="ajouter_ligne_2_colonnes"><i class="fa fa-fw fa-plus-square"></i> Ajouter ligne 2 colonnes</span>
												</div>
											</div>

											<span data-toggle="tooltip" data-placement="left" title="" class="css_ajouter_element" data-original-title="Enregistrer" @click="enregistrer_fiche"><i class="css_action_icon far fa-save css_font_16" aria-hidden="true"></i></span>

										</div>
									</h4>
								</div>
								<div class="card-body">
									<div class="row js_parametrage_fiche">
										<template v-for="(module, nom_module) in fiche.modules">
											<!-- 2 colonnes -->
											<template v-if="module.taille == undefined">

												<div class="col-md-12 js_handle_structure">
													<div class="row">
														<template v-for="colonne_sous_module in module" v-if="colonne_sous_module.taille != 0">
															<div :class="'col-md-'+colonne_sous_module.taille">
																<div class="css_conteneur_col_param_fiche_client js_handle_module">
																	<div class="css_header_col_param_fiche_client">
																		<span data-toggle="tooltip" data-placement="right" title="" class="handle" data-original-title="Déplacer"><i class="css_action_icon secondaire fas fa-arrows-alt css_font_16" aria-hidden="true"></i></span>
																		<div style="display: flex;flex-wrap: nowrap;align-items: center;">
																			<span>Taille colonne :</span>
																			<select v-model="colonne_sous_module.taille" class="js_taille_colonne" style="width: 50px;">
																				@for($i=1; $i<=12; $i++)
																					<option value="{{ $i }}">{{ $i }}</option>
																				@endfor
																			</select>
																		</div>
																		<div style="display: flex;flex-wrap: nowrap;align-items: center;">
																			<span>Utiliser des onglets :</span>
																			<select v-model="colonne_sous_module.onglets" class="js_onglets_colonne" style="width: 55px;">
																				<option value="0">Non</option>
																				<option value="1">Oui</option>
																			</select>
																		</div>
																		@if($document)
																			<div style="display: flex;flex-wrap: nowrap;align-items: center;" v-if="colonne_sous_module.onglets == 1">
																				<span>Bouton précédent / suivant :</span>
																				<select v-model="colonne_sous_module.bouton_suivant" class="js_onglets_bouton_suivant" style="width: 55px;">
																					<option value="0">Non</option>
																					<option value="1">Oui</option>
																				</select>
																			</div>
																		@endif
																		<label :class="'label_cacher_bloc_v_if ' +(affichage_condition_vue_js(colonne_sous_module) ? 'actif' : '')" for="cacher_bloc_v_if" @click="afficher_condition_vue_js(colonne_sous_module)">V-if </label>
																		<span v-if="affichage_condition_vue_js(colonne_sous_module)" class="input js_cacher_colonne_v_if" role="textbox" v-html="colonne_sous_module.cacher_bloc_v_if" contenteditable></span>
																		<div style="margin-left:auto">
																			<span data-toggle="tooltip" data-placement="left" title="" class="css_ajouter_element ml-auto" data-original-title="Ajouter module" @click="ajouter_module_dans_colonne(colonne_sous_module)"><i class="css_action_icon secondaire fa fa-fw fa-plus-square css_font_16" aria-hidden="true"></i></span>
																			<span @click="supprimer_colonne(colonne_sous_module)"  data-toggle="tooltip" data-placement="right" data-original-title="Supprimer"><i class="css_action_icon mineur fas fa-trash" aria-hidden="true" style=""></i></span>
																		</div>
																	</div>

																	<div class="row js_ligne_sous_module_param_formulaire_fiche" style="gap: 20px 0;">
																		<template v-for="sous_module in colonne_sous_module.modules">
																			<div :class="'col-md-'+sous_module.taille">
																				<div class="css_ligne_param_formulaire_fiche">
																					<span data-toggle="tooltip" data-placement="right" title="" class="js_handle_sous_module" data-original-title="Déplacer"><i class="css_action_icon mineur fas fa-arrows-alt css_font_16" aria-hidden="true"></i></span>
																					<select class="css_select_param_fiche_client js_valeur_module" v-model="sous_module.module" style="width: 220px;" title="Choix du module" data-toggle="tooltip">
																						@include('eden::parametrage.include.fiche_select_options_module')
																					</select>
																					<span v-if="modules[sous_module.module] && modules[sous_module.module].restriction"
																						  :title="modules[sous_module.module].restriction == 'obligatoire' ? 'Ne peut être supprimé' : 'Présent uniquement en '+modules[sous_module.module].restriction"
																						  :class="'badge badge-'+(modules[sous_module.module].restriction == 'obligatoire' ? 'danger' : 'warning')" style="text-transform:capitalize">
																						@{{ modules[sous_module.module].restriction }}
																					</span>
																					<select class="css_select_param_fiche_client js_taille_bloc" v-model="sous_module.taille" style="width: 46px;" title="Taille du bloc" data-toggle="tooltip">
																						@for($i=1; $i<=12; $i++)
																							<option value="{{ $i }}">{{ $i }}</option>
																						@endfor
																					</select>
																					<select class="css_select_param_fiche_client js_afficher_bloc" v-model="sous_module.afficher_par_defaut"  title="Déplié / replié par défaut" data-toggle="tooltip">
																						<option value="false">Cacher par défaut</option>
																						<option value="true">Afficher par défaut</option>
																					</select>
																					<template v-if="colonne_sous_module.bouton_suivant != 1">
																						<label :class="'label_cacher_bloc_v_if ' +(affichage_condition_vue_js(sous_module) ? 'actif' : '')" for="cacher_bloc_v_if" @click="afficher_condition_vue_js(sous_module)">V-if </label>
																						<span v-if="affichage_condition_vue_js(sous_module)" class="input js_cacher_bloc_v_if" role="textbox" v-html="sous_module.cacher_bloc_v_if" contenteditable></span>
																					</template>
																					<span style="margin-left: auto;" v-if="!modules[sous_module.module] || !modules[sous_module.module].restriction || modules[sous_module.module].restriction != 'obligatoire'"  @click="supprimer_sous_module(sous_module)" data-toggle="tooltip" data-placement="right" data-original-title="Supprimer"><i class="css_action_icon mineur fas fa-trash" aria-hidden="true"></i></span>
																				</div>
																			</div>
																		</template>
																	</div>
																</div>
															</div>
														</template>
													</div>
												</div>
											</template>

											<!-- module simple -->
											<template v-else>
												<div class="col-md-12">
													<div class="row js_handle_structure">
															<div :class="'col-md-'+module.taille">
																<div class="css_conteneur_col_param_fiche_client">

																	<div class="css_ligne_param_formulaire_fiche">
																		<span data-toggle="tooltip" data-placement="right" title="" class="handle js_handle_sous_module" data-original-title="Déplacer"><i class="css_action_icon mineur fas fa-arrows-alt css_font_16" aria-hidden="true"></i></span>
																		<select class="css_select_param_fiche_client js_valeur_module" v-model="module.module" style="width: 220px;"  title="Choix du module" data-toggle="tooltip">
																			@include('eden::parametrage.include.fiche_select_options_module')
																		</select>
																		<span style="text-transform:capitalize" v-if="modules[module.module] && modules[module.module].restriction"
																			:title="modules[module.module].restriction == 'obligatoire' ? 'Ne peut être supprimé' : 'Présent uniquement en modification'"
																			:class="'badge badge-'+(modules[module.module].restriction == 'obligatoire' ? 'danger' : 'warning')">
																			@{{ modules[module.module].restriction | }}
																		</span>
																		<select class="css_select_param_fiche_client js_taille_bloc" v-model="module.taille" style="width: 50px;"  title="Taille du bloc" data-toggle="tooltip">
																			@for($i=1; $i<=12; $i++)
																				<option value="{{ $i }}">{{ $i }}</option>
																			@endfor
																		</select>
																		<select class="css_select_param_fiche_client js_afficher_bloc" v-model="module.afficher_par_defaut" title="Déplié / replié par défaut" data-toggle="tooltip">
																			<option value="false">Cacher par défaut</option>
																			<option value="true">Afficher par défaut</option>
																		</select>
																		<label :class="'label_cacher_bloc_v_if ' +(affichage_condition_vue_js(module) ? 'actif' : '')" for="cacher_bloc_v_if" @click="afficher_condition_vue_js(module)">V-if </label>
																		<span v-if="affichage_condition_vue_js(module)" class="input js_cacher_bloc_v_if" role="textbox" v-html="module.cacher_bloc_v_if" contenteditable></span>
																		<span style="margin-left: auto;" v-if="!modules[module.module] || !modules[module.module].restriction || modules[module.module].restriction != 'obligatoire'" @click="supprimer_module(module)" :id="module.module"  data-toggle="tooltip" data-placement="right" data-original-title="Supprimer">
																			<i class="css_action_icon mineur fas fa-trash" aria-hidden="true"></i>
																		</span>
																	</div>
																</div>
															</div>
														</div>
													</div>
											</template>
										</template>
									</div>
								</div>
							</div>
						</div>
						@if(!$document)
							<div class="section_modale">
								<span class="nom_section"
									  :class="(bloc_affiche == 'colonne_droite' ? 'section_active' : '')+' nom_section'"
									  @click="bloc_affiche = 'colonne_droite'"
								>Colonne de droite</span>
								<div class="card  mt-auto contenu_section" v-show="bloc_affiche == 'colonne_droite'">
									<div class="card-header">
										<h4 class="d-flex align-items-center">
											Colonne de droite

											<div class="d-inline-flex ml-auto">
												<div class="css_ajouter_element mr-3">
													<i class="css_action_icon fa fa-fw fa-plus-square" @click="ajouter_module_colonne_droite" data-toggle="tooltip" data-placement="left" title="Ajouter un bloc"></i>
												</div>

												<span data-toggle="tooltip" data-placement="left" title="" class="css_ajouter_element" data-original-title="Enregistrer" @click="enregistrer_fiche"><i class="css_action_icon far fa-save css_font_16" aria-hidden="true"></i></span>
											</div>
										</h4>
									</div>
									<div class="card-body">
										<div class="row js_parametrage_fiche">
											<template v-for="(module, nom_module) in fiche.colonne_droite">
												<div class="col-md-12 ">
													<div class="row js_handle_structure_colonne_droite">
															<div class="col-md-12" style="">
																<div class="css_conteneur_col_param_fiche_client">

																	<div class="css_ligne_param_formulaire_fiche">
																		<span data-toggle="tooltip" data-placement="right" title="" class="handle js_handle_sous_module" data-original-title="Déplacer"><i class="css_action_icon mineur fas fa-arrows-alt css_font_16" aria-hidden="true"></i></span>
																		<select class="css_select_param_fiche_client js_valeur_module" v-model="module.module" style="width: 220px;"  title="Choix du module" data-toggle="tooltip">
																			@include('eden::parametrage.include.fiche_select_options_module')
																		</select>
																		<label :class="'label_cacher_bloc_v_if ' +(affichage_condition_vue_js(module) ? 'actif' : '')" for="cacher_bloc_v_if" @click="afficher_condition_vue_js(module)"> V-if </label>
																		<span v-if="affichage_condition_vue_js(module)" class="input js_cacher_bloc_v_if" role="textbox" v-html="module.cacher_bloc_v_if" contenteditable></span>
																		<span style="margin-left:auto" @click="module.module = ''"  data-toggle="tooltip" data-placement="right" data-original-title="Supprimer"><i class="css_action_icon mineur fas fa-trash" aria-hidden="true"></i></span>
																	</div>
																</div>
															</div>
														</div>
													</div>
											</template>
										</div>
									</div>
								</div>
							</div>
						@endif
						<div class="section_modale">
								<span class="nom_section"
									  :class="(bloc_affiche == 'options' ? 'section_active' : '')+' nom_section'"
									  @click="bloc_affiche = 'options'"
								>Options</span>
							<div class="card  mt-auto contenu_section" v-show="bloc_affiche == 'options'">
								<div class="card-header">
									<h4 class="d-flex align-items-center">
										Options

										<div class="d-inline-flex ml-auto">
											<span data-toggle="tooltip" data-placement="left" title="" class="css_ajouter_element" data-original-title="Enregistrer" @click="enregistrer_fiche"><i class="css_action_icon far fa-save css_font_16" aria-hidden="true"></i></span>
										</div>
									</h4>
								</div>
								<div class="card-body">
									<div class="table-responsive">
										<table class="table table-bordered table-hover css_form" width="100%" cellspacing="0">
											<thead>
											<tr>
												<th scope="col">Paramètre</th>
												<th scope="col">Valeur</th>
											</tr>
											</thead>
											<tbody>

												@if($independant)
													@includeIf('eden::parametrage.fiche.options.'.$type_element)
												@else
													<tr>
														<td>Afficher fil ariane</td>
														<td>
															<select v-model="fiche.options.afficher_fil_ariane">
																<option value="0">Non</option>
																<option value="1">Oui</option>
															</select>
														</td>
													</tr>
													<tr>
														<td>Désactivation options fil ariane</td>
														<td>
															@foreach($options_pour_fil_ariane as $option)
																<div class="d-flex" style="gap:5px;">
																	<input id="{{$option['id']}}" type="checkbox" v-model="fiche.options.options_desactives" value="{{$option['id']}}">
																	<label for="{{$option['id']}}">{{$option['id']}}</label>
																</div>
															@endforeach
														</td>
													</tr>
													<template v-if="modules_utilises.includes('commerce')">
														<tr>
															<td>Commerce Vente : Onglet par défaut</td>
															<td>
																<select v-model="fiche.options.commerce_vente_defaut">
																	<option value="">Sans valeur</option>
																	<option v-for="type_document in documents_vente" :value="type_document" v-html="$root.traduction('tables_libres.'+type_document+'.element_pluriel')"></option>
																</select>
															</td>
														</tr>
														<tr>
															<td>Commerce Achat : Onglet par défaut</td>
															<td>
																<select v-model="fiche.options.commerce_achat_defaut">
																	<option value="">Sans valeur</option>
																	<option v-for="type_document in documents_achat" :value="type_document" v-html="$root.traduction('tables_libres.'+type_document+'.element_pluriel')"></option>
																</select>
															</td>
														</tr>
													</template>
													<tr>
														<td>Actions automatiques après événement</td>
														<td>
															<span class="css_action_icon fas fa-plus" @click="ajout_action_automatique"></span>
														</td>
													</tr>
													<template v-if="fiche.options.actions_apres_evenement != undefined" v-for="(action,index) in fiche.options.actions_apres_evenement">
														<tr>
															<td colspan="2">
																<div class="d-flex justify-content-between align-items-center">
																	<span>Action @{{ index+1 }}</span>
																	<i class="css_action_icon fas fa-trash" @click="fiche.options.actions_apres_evenement.splice(index,1)"></i>
																</div>
															</td>
														</tr>
														<tr>
															<td>Type</td>
															<td>
																<select v-model="action.type">
																	<option value="1">Redirection</option>
																</select>
															</td>
														</tr>
														<template v-if="action.type == 1">
															<tr>
																<td>Route de redirection</td>
																<td>
																	<select v-model="action.valeur_action">
																		<option value="retour_arriere">Page précédente</option>
																		<option value="url">URL</option>
																	</select>
																</td>
															</tr>
															<tr v-if="action.valeur_action == 'url'">
																<td>URL</td>
																<td>
																	<input type="text" v-model="action.valeur_action_annexe">
																</td>
															</tr>
														</template>
														<tr>
															<td>Évènement</td>
															<td>
																<select v-model="action.evenement" @change="action.condition_evenement = null">
																	<option value="enregistrement_fiche">Enregistrement fiche</option>
																	<option value="enregistrement_formulaire">Enregistrement formulaire</option>
																	<option value="suppression_fiche">Suppression fiche</option>
																	@foreach($options_pour_fil_ariane as $option)
																		@if(!empty($option['evenements']))
																			@foreach($option['evenements'] as $cle => $libelle)
																				<option value="{{ $cle }}">{{$libelle}}</option>
																			@endforeach
																		@endif
																	@endforeach
																</select>
															</td>
														</tr>
														<tr v-if="action.evenement == 'enregistrement_formulaire'">
															<td>Formulaire associé</td>
															<td>
																<select v-model="action.formulaire">
																	@foreach($formulaires_libres as $formulaire)
																		<option value="{{ $formulaire->nom_formulaire }}" v-html="traduction('{{$formulaire->index_traduction}}','titre')"></option>
																	@endforeach
																</select>
															</td>
														</tr>
														<tr v-if="action.evenement != 'suppression_fiche'">
															<td>Condition de lancement de l'action</td>
															<td>
																<input type="text" v-model="action.condition_evenement">
															</td>
														</tr>
													</template>
												@endif
											</tbody>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

			</div>
		</div>
	</div>
@endsection

@push('donnees_pour_vuejs_data')
	fiche: {!! collect($fiche) !!},
	modules: {!! collect($modules) !!},
	bloc_affiche: 'modules',
	documents_vente: {!! collect(App\Eden\Variables::documents_vente_gescom_disponibles()) !!},
	documents_achat: {!! collect(App\Eden\Variables::documents_achat_gescom_disponibles()) !!},
@endpush

<script>

@push('donnees_pour_vuejs_computed')
	modules_utilises() {
		var modules = [];

		// pour la fiche classique
		if(this.fiche.modules !== undefined) {

			for(info_structure of Object.values(this.fiche.modules)) {

				if(info_structure.module !== undefined){

					modules.push(info_structure.module);
					continue;
				}

				for(info_structure_tmp of Object.values(info_structure)) {
					for(info_structure_niveau_2 of Object.values(info_structure_tmp.modules)) {
						if(info_structure_niveau_2.module !== undefined)
							modules.push(info_structure_niveau_2.module);
					}
				}
			}
		}

		// pour la colonne de droite
		if(this.fiche.colonne_droite !== undefined) {
			for(info_structure of Object.values(this.fiche.colonne_droite)) {
				if(info_structure.module !== undefined)
					modules.push(info_structure.module);
			}
		}

		return modules;
	},
@endpush

@push('donnees_pour_vuejs_methods')


	ajouter_module_colonne_droite: function() {

		var nouvelle_ligne = {

			module: '',
			afficher_par_defaut: true,
			cacher_bloc_v_if: 1,
			taille_avant: 0,
			taille: 12,
			taille_apres: 0,
		};

		this.fiche.colonne_droite.push(nouvelle_ligne);
	},

	ajouter_ligne: function() {

		var nouvelle_ligne = {

			module: '',
			afficher_par_defaut: true,
			cacher_bloc_v_if: 1,
			taille_avant: 0,
			taille: 12,
			taille_apres: 0,
		};

		this.fiche.modules.push(nouvelle_ligne);
	},

	ajouter_ligne_2_colonnes: function() {

		var colonne_1 = {

			taille: 6,
			modules: [{

				module: '',
				taille_avant: 0,
				taille: 12,
				taille_apres: 0,
				afficher_par_defaut: true,
				cacher_bloc_v_if: 1,
			}],
		};
		var colonne_2 = {

			taille: 6,
			modules: [{

				module: '',
				taille_avant: 0,
				taille: 12,
				taille_apres: 0,
				cacher_bloc_v_if: 1,
				afficher_par_defaut: true,
			}],
		};

		var nouvelle_ligne = [colonne_1, colonne_2];

		this.fiche.modules.push(nouvelle_ligne);
	},

	ajouter_module_dans_colonne: function(colonne_sous_module) {

		var nouvelle_ligne = {

			module: '',
			cacher_bloc_v_if: 1,
			taille_avant: 0,
			taille: 12,
			taille_apres: 0,
		};

		let array_modules = [];
		colonne_sous_module.modules = Object.entries(colonne_sous_module.modules);
		colonne_sous_module.modules.forEach(function(item){
		  array_modules.push(item[1]);
		  //console.log(item);
		});

		colonne_sous_module.modules = array_modules;
		colonne_sous_module.modules.push(nouvelle_ligne);
	},

	enregistrer_fiche: function() {

		loading(true);

		// On intiailise le tableau de la structure
		var structure = [];
		var structure_colonne_droite = [];

		// On boucle sur tous les blocs
		$('.js_handle_structure').each(function(index, value) {

			var enfants  = $(this).children().children();

			var modules = [];

			// On récupère les enfants pour distinguer les lignes avec 2 colonnes et les lignes simples
			enfants.each(function(index, value) {

				var nom_de_la_classe = value.className

				// C'est une ligne à 2 colonnes
				if(value.className.includes('col-md-')) {

					// Pour chaque bloc de la ligne, on récupère la taille
					var taille_bloc = $(this).children().find('.js_taille_colonne').val();
					var utiliser_onglet = $(this).children().find('.js_onglets_colonne').val();
					var cacher_bloc_v_if_colonne = $(this).children().find('.js_cacher_colonne_v_if').text();
					var utiliser_suivant_precedent = 0;

					if(utiliser_onglet == 1)
						utiliser_suivant_precedent = $(this).children().find('.js_onglets_bouton_suivant').val();

					var modules_par_ligne = [];

					// On boucle sur les enfants, pour récupérer les modules de chaque bloc de chaque ligne
					$(this).children().find('.js_handle_sous_module').each(function(index, value) {

						// on récupère les informations
						var module = $(this).parent().find('.js_valeur_module').val();
						var cacher_bloc_v_if = $(this).parent().find('.js_cacher_bloc_v_if').text();
						var afficher_par_defaut = $(this).parent().find('.js_afficher_bloc').val();
						var taille = $(this).parent().find('.js_taille_bloc').val();
						var taille_avant = 0;
						var taille_apres = 0;

						var bloc = {module, afficher_par_defaut, taille, taille_avant, taille_apres, cacher_bloc_v_if};
						// console.log({bloc})
						modules_par_ligne.push(bloc);
					})

					var module = {
						taille : taille_bloc,
						onglet : utiliser_onglet,
						cacher_bloc_v_if : cacher_bloc_v_if_colonne,
						modules: modules_par_ligne
					};

					if(utiliser_suivant_precedent == 1)
						module.bouton_suivant = utiliser_suivant_precedent;

					modules.push(module);
				}

				// C'est une ligne simple
				else {

					// on récupère les informations + on l'ajoute dans la structure

					var module = $(this).find('.js_valeur_module').val();
					var cacher_bloc_v_if = $(this).parent().find('.js_cacher_bloc_v_if').text();
					var afficher_par_defaut = $(this).find('.js_afficher_bloc').val();
					var taille = $(this).find('.js_taille_bloc').val();
					var taille_avant = 0;
					var taille_apres = 0;

					var bloc = {module, afficher_par_defaut, taille, taille_avant, taille_apres, cacher_bloc_v_if};

					structure.push(bloc);
				}
			})

			// Si le modules est vide, ça signfie que c'était une ligne simple, donc on l'a déjà enregistré dans la structure
			if(modules.length > 0) {

				structure.push(modules)
			}
		});

		// On boucle sur tous les blocs pour la colonne de droite
		$('.js_handle_structure_colonne_droite').each(function(index, value) {

			var enfants  = $(this).children().children();

			// On récupère les enfants pour distinguer les lignes avec 2 colonnes et les lignes simples
			enfants.each(function(index, value) {

				var nom_de_la_classe = value.className

					// on récupère les informations + on l'ajoute dans la structure

					var module = $(this).find('.js_valeur_module').val();
					var afficher_par_defaut = true;
					var cacher_bloc_v_if = $(this).parent().find('.js_cacher_bloc_v_if').text();
					var taille = 12;
					var taille_avant = 0;
					var taille_apres = 0;

					var bloc = {module, afficher_par_defaut, taille, taille_avant, taille_apres, cacher_bloc_v_if};

					structure_colonne_droite.push(bloc);

			});
		});

		var context = this;

		if(window.location.pathname == "/eden/parametrage/extranet/fiche/{!! $type_element !!}"){
			var url = "{{ route('parametrage.extranet.fiche_post', [$type_element]) }}";
		}
		else{
			var url = "{{ route('parametrage.fiche.enregistrer', [$type_element]) }}";
		}

		$.post({

			url: url,
			dataType: "json",
			data: {
				modules : structure,
				colonne_droite : structure_colonne_droite,
				options: context.fiche.options
			}
		}).done(function(donnees) {

			document.location.reload();
		});
	},

	supprimer_colonne: function (colonne){

		vue_instance.fiche.modules.forEach(function(ligne){

			if (Array.isArray(ligne)) {

				if (JSON.stringify(ligne[0]) == JSON.stringify(colonne))
					vue_instance.fiche.modules[vue_instance.fiche.modules.indexOf(ligne)].splice(0, 1);

				if (JSON.stringify(ligne[1]) == JSON.stringify(colonne))
					vue_instance.fiche.modules[vue_instance.fiche.modules.indexOf(ligne)].splice(1, 1);
			}
		})
	},

	supprimer_module: function(module_unique){

		$('#' + module_unique.module).tooltip('hide');

		var index = vue_instance.fiche.modules.indexOf(module_unique);

		var modules = vue_instance.fiche.modules;

		if(index > -1){

			modules.splice(index,1);

		}

		vue_instance.fiche.modules = modules;

	},

	supprimer_sous_module: function(module_unique){

		$('#' + module_unique.module).tooltip('hide');

        var index_module_principale;
		var index_colonne;
		var index_sous_module;

		vue_instance.fiche.modules.forEach(function(module){

			// Si c'est une array, c'est un module en 2 colonnes
		  	if(Array.isArray(module)){

		  		module.forEach(function(colonne){

		  			colonne.modules.forEach(function(sous_module){

		  				if (sous_module == module_unique) {

		  					index_module_principale = vue_instance.fiche.modules.indexOf(module);
		  					index_colonne = module.indexOf(colonne);
		  					index_sous_module = colonne.modules.indexOf(sous_module);
		  				}
		  			});
		  		});

		  	}
		});

		vue_instance.fiche.modules[index_module_principale][index_colonne].modules.splice(index_sous_module,1);
	},

	affichage_condition_vue_js(module){

		return module.cacher_bloc_v_if != 1 && module.cacher_bloc_v_if != '1' && module.cacher_bloc_v_if != undefined;
	},

	afficher_condition_vue_js(module){

		module.cacher_bloc_v_if = module.cacher_bloc_v_if != 1 && module.cacher_bloc_v_if != '1' && module.cacher_bloc_v_if != undefined ? 1 : '';

		this.$forceUpdate();
	},

	ajout_action_automatique(){

		if(this.fiche.options.actions_apres_evenement == undefined)
			this.$set(this.fiche.options,'actions_apres_evenement',[]);

		this.fiche.options.actions_apres_evenement.push({
			type : 1,
			formulaire : null,
			valeur_action : 'retour_arriere',
			evenement : 'enregistrement_fiche'
		});
	},

@endpush
</script>
@push('scripts')
	<script>

	$('.js_parametrage_fiche').sortable({
		handle: '.handle'
	});
	$('.js_ligne_sous_module_param_formulaire_fiche').sortable({
		handle: '.js_handle_sous_module'
	});

	</script>
@endpush
