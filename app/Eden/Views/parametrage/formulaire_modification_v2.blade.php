@extends('eden::templates.template')

@section('title') Paramétrage formulaire @stop

@section('styles')
<style type="text/css">
	/* Firefox */
	input[type=number] {
		-moz-appearance: textfield;
	}

	/* Chrome */
	input::-webkit-inner-spin-button,
	input::-webkit-outer-spin-button {
		-webkit-appearance: none;
		margin:0;
	}

	/* Opéra*/
	input::-o-inner-spin-button,
	input::-o-outer-spin-button {
		-o-appearance: none;
		margin:0
	}

	tr.js_scroll{
		background-color: rgb(238, 238, 238);
		transition-duration:1.5s;
		transition-timing-function: ease-in;
	}

	tr.js_scroll td.css_nom_champ {
		font-size: 1.2em;
		transition-duration:1.5s;
		transition-timing-function: ease-in;
	}

	tr {
		background-color: white;
		transition-duration:1.5s;
		transition-timing-function: ease-in;
	}

	td.css_nom_champ {
		font-size: 1em;
		transition-duration:1.5s;
		transition-timing-function: ease-in;
	}

	.element_apercu{

		background: #E3E1E1;
		color: #aaa;
		height: 30px;
		float:left
	}

	.avap{
		background-color: #CFCFCF;
	}

	.champ{
		background: #F1F0F0;
	}

	.bouton_header {
		float: right;
		margin-left: 10px;
	}

	.select_champs_libres .affichage{
		width: 100%!important;
	}

</style>

@endsection

@section('content')

<div class="content-wrapper" >
	<div id="base-content" class="container-fluid">

		@php
			$nom_formulaire_tmp = 'générique';
			if(!empty($formulaire->index_traduction)) {
				$nom_formulaire_tmp = \Illuminate\Support\Facades\Blade::compileString('@traduction("'.$formulaire->index_traduction.'","titre")');
			}
		@endphp
		@include('eden::includes.fil_ariane', ['fil_ariane' => array(
				array('route' => 'parametrage.index', 'nom' => 'Paramétrage'),
				array('route' => 'parametrage.table_libre.principales', 'nom' => 'Elements paramétrables'),
				array('route' => 'parametrage.table_libre.zoom','arguments' => [table_libre($type_element)->type_element] , 'nom' => table_libre($type_element)->element),
				array('nom' => 'Formulaire - '.$nom_formulaire_tmp)
			)])

		<div class="row">
			<div class="col-md-12">
				<div class="card mb-3">
					<div class="card-header">
						<h4>
							&Eacute;léments du formulaire
						</h4>
						<span @click="supprimer_formulaire()"  data-toggle="tooltip" data-placement="top" title="Supprimer le formulaire" class="css_ajouter_element css__lien bouton_header" data-original-title="Supprimer le formulaire"><i aria-hidden="true" class="css_action_icon fa fa-fw fa-trash"></i></span>

						<span data-toggle="tooltip" data-placement="left" title="" class="css_ajouter_element css__lien bouton_header" data-original-title="Modifier le formulaire" @click="modifier_formulaire()"><i class="css_action_icon fa fa-pencil-square-o" aria-hidden="true"></i></span>

						<div class="nav-item dropdown dropdown_hover css_ajouter_element css__lien bouton_header" aria-haspopup="true" aria-expanded="false" data-toggle="tooltip" data-placement="top" >
							<div class="dropdown-toggle">
								<i class="css_action_icon fas fa-file-invoice" data-toggle="tooltip" data-placement="left" title="Ajouter une vue existante"  data-original-title="Ajouter une vue existante" ></i>
							</div>
							<div class="dropdown-menu" style="left:auto;right:0;" aria-labelledby="dropdownMenuButton">
								<a v-for="vue_ajoutable in vues_ajoutables" @click="ajout_vue(vue_ajoutable)" class="dropdown-item" ><i class="fa fa-fw fa-plus-square"></i>@{{ vue_ajoutable.nom_lisible }} (@{{vue_ajoutable.type_vue}})</a>
								<a v-if="!vues_ajoutables.length" class="dropdown-item">Aucune vue disponible</a>
							</div>
						</div>

						<span @click="ajout_bloc_html()"  data-toggle="tooltip" data-placement="top" title="Ajouter un bloc HTML" class="css_ajouter_element css__lien bouton_header" data-original-title="Ajouter un bloc HTML"><i aria-hidden="true" class="css_action_icon fa fa-fw fa-code"></i></span>

						<span @click="ajout_sous_formulaire()"  data-toggle="tooltip" data-placement="top" title="Ajouter un sous-formulaire" class="css_ajouter_element css__lien bouton_header" data-original-title="Ajouter un sous-formulaire"><i aria-hidden="true" class="css_action_icon fa fa-fw fa-plus"></i></span>

						<span @click="modifier_champ()"  data-toggle="tooltip" data-placement="top" title="Ajouter un champ" class="css_ajouter_element css__lien bouton_header" data-original-title="Ajouter un champ"><i aria-hidden="true" class="css_action_icon fa fa-fw fa-plus-square"></i></span>
					</div>
					<div class="card-body css_form css_parametrage_formulaire" id="sortable">

						<!-- Si un fichier existe dans les vues, on affiche une alerte -->
						@if($existe_en_dur)
							<div class="alert alert-danger" v-if="formulaire.surcharger_la_vue != 1">
								Ce formulaire existe déjà !
								<button class="btn btn-warning btn-sm" style="line-height:20px;float:right;padding:5px;margin-top:-5px;" @click="formulaire.surcharger_la_vue='1';enregistrer_formulaire();">Personnaliser le formulaire</button>
							</div>
						@endif

						<div class="row" {!! $existe_en_dur ? 'v-if="formulaire.surcharger_la_vue == 1"' : '' !!}>
							<div class="col-md-12">
								<div class="w-100 mt-3">
									<div class="d-flex mb-1" style="flex-wrap: wrap;">
										<div class="css_th_parametrage_formulaire" style="width: 40%">Intitulé</div>
										<div class="css_th_parametrage_formulaire" style="width: 15%">Espace avant intitulé</div>
										<div class="css_th_parametrage_formulaire" style="width: 15%">Taille de l'intitulé</div>
										<div class="css_th_parametrage_formulaire" style="width: 15%">Taille du champ</div>
										<div class="css_th_parametrage_formulaire" style="width: 15%">Taille après le champ</div>
									</div>
									<draggable v-model="champs_libres" class="w-100 d-flex " style="flex-wrap: wrap;" @change="modification_ordre_champ" handle=".handle">
										<div class="css_ligne_parametrage_formulaire handle"  v-for="(champ, index) in champs_libres">
											<div v-if="champ.type_champ == 0 || champ.type_champ === null" class="w-100 d-flex">
												<div style="display: inline-flex;gap:5px;width: 40%;align-items:center;">
													<i @click="supprimer_le_champ(champ)"  class="fas fa-trash mr-1 ml-1"></i>
													<profil-droits-divers type="eden_formulaireslibres_champs" :index="champ.id" :bouton="true">
														<template v-slot:bouton="{gestion_profil,profil_droits_divers}">
															<i class="fas fa-users" :style="'border: solid 0.1px; padding: 5px;border-radius: 5px;' + (profil_droits_divers.profils.length > 0 ? 'background-color:green;color:white;' : '')" @click="gestion_profil()"></i>
														</template>
													</profil-droits-divers>
													<span @click="affichage_modale_condition(champ)"
														  :style="'border: solid 0.1px; padding: 5px;border-radius: 5px;'+(condition_presente(champ) ? 'background-color:green;color:white;' : '')">
														<i class="fab fa-vuejs"></i>
													</span>
													@{{ champ.nom }} (@{{ champ.nom_sql }})
												</div>
												<div class="css_td_input_parametrage_formulaire" style="width: 15%">
													<select v-model="champ.taille_avant" @change="enregistre_un_champ(champ)" style="width: 20%;">
														<option value="0">0</option>
														<option v-for="index in 12" :value="index" v-html="index"></option>
													</select>
												</div>
												<div class="css_td_input_parametrage_formulaire" style="width: 15%">
													<select v-model="champ.taille_libelle" @change="enregistre_un_champ(champ)" style="width: 20%;">
														<option value="0">0</option>
														<option v-for="index in 12" :value="index" v-html="index"></option>
													</select>
												</div>
												<div class="css_td_input_parametrage_formulaire" style="width: 15%">
													<select v-model="champ.taille_champ" @change="enregistre_un_champ(champ)" style="width: 20%;">
														<option value="0">0</option>
														<option v-for="index in 12" :value="index" v-html="index"></option>
													</select>
												</div>
												<div class="css_td_input_parametrage_formulaire" style="width: 15%">
													<select v-model="champ.taille_apres" @change="enregistre_un_champ(champ)" style="width: 20%;">
														<option value="0">0</option>
														<option v-for="index in 12" :value="index" v-html="index"></option>
													</select>
												</div>
											</div>
											<div v-if="champ.type_champ == 1 || champ.type_champ == 2" class="w-100 d-flex">
												<div style="display: inline-flex;gap:5px;width: 40%;align-items:center;" >
													<i @click="supprimer_le_champ(champ)"  class="fas fa-trash mr-1 ml-1"></i>
                                                    <profil-droits-divers type="eden_formulaireslibres_champs" :index="champ.id" :bouton="true">
                                                        <template v-slot:bouton="{gestion_profil,profil_droits_divers}">
                                                            <i class="fas fa-users" :style="'border: solid 0.1px; padding: 5px;border-radius: 5px;' + (profil_droits_divers.profils.length > 0 ? 'background-color:green;color:white;' : '')" @click="gestion_profil()"></i>
                                                        </template>
                                                    </profil-droits-divers>
                                                    <span @click="affichage_modale_condition(champ)"
                                                          :style="'border: solid 0.1px; padding: 5px;border-radius: 5px;'+(condition_presente(champ) ? 'background-color:green;color:white;' : '')">
														<i class="fab fa-vuejs"></i>
													</span>
                                                    <template v-if="champ.type_champ == 1">
                                                        Intégration bloc html @{{champ.id_editeur}}
                                                         <i class="fas fa-angle-up ml-1 js_toggle_bloc_edition_html"></i>
                                                        <div class="css_ajouter_element_formulaire_modification"  @click="enregistre_un_champ(champ)">
                                                            <span>Enregistrer les modifications</span>
                                                        </div>
                                                    </template>
                                                    <template v-else>
                                                        Vue : @{{ champ.nom_vue }} (@{{champ.type_vue}})
                                                    </template>
												</div>
												<div class="css_td_input_parametrage_formulaire" style="width: 15%">
													<select v-model="champ.taille_avant" @change="enregistre_un_champ(champ)" style="width: 20%;">
														<option value="0">0</option>
														<option v-for="index in 12" :value="index" v-html="index"></option>
													</select>
												</div>
												<div class="css_td_input_parametrage_formulaire" style="width: 15%">
													 On ne change pas la taille libelle
												</div>
												<div class="css_td_input_parametrage_formulaire" style="width: 15%">
													<select v-model="champ.taille_champ" @change="enregistre_un_champ(champ)" style="width: 20%;">
														<option value="0">0</option>
														<option v-for="index in 12" :value="index" v-html="index"></option>
													</select>
												</div>
												<div class="css_td_input_parametrage_formulaire" style="width: 15%">
													<select v-model="champ.taille_apres" @change="enregistre_un_champ(champ)" style="width: 20%;">
														<option value="0">0</option>
														<option v-for="index in 12" :value="index" v-html="index"></option>
													</select>
												</div>

											</div>
											<div v-if="champ.type_champ == 1" :id="'js_html_edition_'+champ.id_editeur" class="css_bloc_edition_html js_bloc_edition_html"></div>
											<div v-if="champ.type_champ == 3" class="w-100 d-flex">
												<div style="display: inline-flex;gap:5px;width: 40%;align-items:center;">
													<i @click="supprimer_le_champ(champ)"  class="fas fa-trash mr-1 ml-1"></i>
													<profil-droits-divers type="eden_formulaireslibres_champs" :index="champ.id" :bouton="true">
														<template v-slot:bouton="{gestion_profil,profil_droits_divers}">
															<i class="fas fa-users" :style="'border: solid 0.1px; padding: 5px;border-radius: 5px;' + (profil_droits_divers.profils.length > 0 ? 'background-color:green;color:white;' : '')" @click="gestion_profil()"></i>
														</template>
													</profil-droits-divers>
													<span @click="affichage_modale_condition(champ)"
														  :style="'border: solid 0.1px; padding: 5px;border-radius: 5px;'+(condition_presente(champ) ? 'background-color:green;color:white;' : '')">
														<i class="fab fa-vuejs"></i>
													</span>
													Sous-formulaire : @{{ champ.nom_sous_formulaire }}
													<span class="fa fa-pen" @click="ajout_sous_formulaire(champ)"></span>
												</div>
											</div>
										</div>

									</draggable>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="card mb-3">
					<div class="card-header">
						<h4>
							Aperçu affichage
						</h4>

						<span @click="modifier_formulaire()"  data-toggle="tooltip" data-placement="top" title="Modifier le formulaire" class="css_ajouter_element css__lien bouton_header" data-original-title="Ajouter un bloc HTML"><i aria-hidden="true" class="css_action_icon fa fa-pencil-square-o"></i></span>

						<div class="nav-item dropdown dropdown_hover css_ajouter_element css__lien bouton_header" aria-haspopup="true" aria-expanded="false" data-toggle="tooltip" data-placement="top" >
							<div class="dropdown-toggle">
								<i class="css_action_icon fas fa-file-invoice" data-toggle="tooltip" data-placement="left" title="Ajouter une vue existante"  data-original-title="Ajouter une vue existante" ></i>
							</div>
							<div class="dropdown-menu" style="left:auto;right:0;" aria-labelledby="dropdownMenuButton">
								<a v-for="vue_ajoutable in vues_ajoutables" @click="ajout_vue(vue_ajoutable)" class="dropdown-item" ><i class="fa fa-fw fa-plus-square"></i>@{{ vue_ajoutable.nom_lisible }} (@{{vue_ajoutable.type_vue}}) </a>
								<a v-if="!vues_ajoutables.length" class="dropdown-item">Aucune vue disponible</a>
							</div>
						</div>

						<span @click="ajout_bloc_html()"  data-toggle="tooltip" data-placement="top" title="Ajouter un bloc HTML" class="css_ajouter_element css__lien bouton_header" data-original-title="Ajouter un bloc HTML"><i aria-hidden="true" class="css_action_icon fa fa-fw fa-code"></i></span>

						<span @click="modifier_champ()"  data-toggle="tooltip" data-placement="top" title="Ajouter un champ" class="css_ajouter_element css__lien bouton_header" data-original-title="Ajouter un champ"><i aria-hidden="true" class="css_action_icon fa fa-fw fa-plus-square"></i></span>
					</div>
					<div class="card-body css_form css_parametrage_formulaire ">
						<draggable v-model="champs_libres" class="w-100 d-flex" style="flex-wrap: wrap;" @change="modification_ordre_champ"  handle=".handle" >
							<template v-for="champ in champs_libres">

								<div v-if="champ.type_champ == 2" :class="'handle col-sm-'+parseInt(champ.taille_total)" style="margin-bottom: 2px;">
                                    <div :style="'width:'+champ.pourcentage_avant+'%'" v-if="champ.taille_avant != 0" class="element_apercu avap"></div>
									<div :style="'width:'+champ.pourcentage_champ+'%'" class="element_apercu"><p style="text-align:center;font-weight: bold;">Vue : @{{ champ.nom_vue }} (@{{champ.type_vue}})</p></div>
                                    <div :style="'width:'+champ.pourcentage_apres+'%'" v-if="champ.taille_apres!= 0" class="element_apercu avap"></div>
								</div>
								<div v-else :class="'handle col-sm-'+parseInt(champ.taille_total)" style="margin-bottom: 2px;">
									<div :style="'width:'+champ.pourcentage_avant+'%'" v-if="champ.taille_avant != 0" class="element_apercu avap"></div>
									<div :style="'width:'+champ.pourcentage_libelle+'%'" v-if="champ.taille_libelle != 0 && champ.type_champ == 0" class="element_apercu"><p style="text-align:center;font-weight: bold;">@{{ champ.nom }}</p></div>
									<div :style="'width:'+champ.pourcentage_champ+'%'" v-if="champ.taille_champ != 0 && champ.type_champ == 0" class="element_apercu champ"><p style="text-align:center;">CHAMP</p></div>
									<div :style="'width:'+champ.pourcentage_champ+'%'" v-if="champ.taille_champ != 0 && champ.type_champ == 1" class="element_apercu"><p style="text-align:center;font-weight: bold;">@{{ champ.nom_sql }}</p></div>
									<div :style="'width:'+champ.pourcentage_apres+'%'" v-if="champ.taille_apres!= 0" class="element_apercu avap"></div>
									<div :style="'width:'+champ.pourcentage_champ+'%'" v-if="champ.taille_champ != 0 && champ.type_champ == 1" class="element_apercu"><p style="text-align:center;font-weight: bold;">@{{ champ.nom_sql }}</p></div>
								</div>
							</template>
						</draggable>
					</div>
				</div>
				@includeWhen($formulaire->type_formulaire != 'fiche','eden::parametrage.include.formulaire_valeurs_par_defaut')
			</div>
		</div>
	</div>
</div>

<!-- Modale d'ajout de sous-formulaire -->
<template v-if="modale_ajout_sous_formulaire">
	<transition name="modal" >
		<div class="modal-mask">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">

					<div class="modal-header">
						<h5 class="modal-title" >Ajouter un sous-formulaire</h5>
					</div>
					<div class="modal-body css_form js_selection_element css_form" >
						<div class="row" v-show="eden_sous_formulaire.id != undefined">
							<div class="col-sm-12">
								<traduction-table ref="traduction_table"  categorie="12" :filtrage_index="'formulaire.' + eden_sous_formulaire.nom_formulaire_parent + '.sous_formulaire.' + eden_sous_formulaire.nom_sous_formulaire + '.'"></traduction-table>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-2">
								@traduction('champs_libres.eden_sous_formulaire.type_element_enfant.nom')
							</div>
							<div class="col-sm-4">
								<div class="css_champ_obligatoire">
									<select name="type_element_enfant" @change="changement_type_element_sous_formulaire" v-model="eden_sous_formulaire.type_element_enfant">
										<option v-for="(nom_sql_champ, type_element_sous_formulaire) in type_elements_pour_sous_formulaire" :value="type_element_sous_formulaire" v-html="type_element_sous_formulaire"></option>
									</select>
								</div>
							</div>
							<div class="col-sm-2">
								@traduction('champs_libres.eden_sous_formulaire.champ_liaison.nom')
							</div>
							<div class="col-sm-4">
								{!! management('eden_sous_formulaire')->champ('champ_liaison')->attr('@change', 'calcul_nom_sql')->cree() !!}
							</div>
						</div>
						<div class="row">
							<div class="col-sm-4">
								@traduction('champs_libres.eden_sous_formulaire.nom_donnee_different.nom')
							</div>
							<div class="col-sm-2 css_champ_avec_helper">
								{!! management('eden_sous_formulaire')->champ('nom_donnee_different')->cree() !!}
								<i title="Aide" @click="tooltip_nom_donnee_different = !tooltip_nom_donnee_different" class="fas fa-question-circle css_pointer" aria-hidden="true" style="padding: 10px; background: #ff0000; color: white; height: 30px; text-align: center;"></i>
							</div>
							<template v-if="eden_sous_formulaire.nom_donnee_different == 1" class="css_champ_avec_helper">
								<div class="col-sm-3">
									@traduction('champs_libres.eden_sous_formulaire.type_element_remplacement.nom')
								</div>
								<div class="col-sm-3 css_champ_avec_helper">
									{!! management('eden_sous_formulaire')->champ('type_element_remplacement')->cree() !!}
									<i title="Aide" @click="tooltip_type_element_remplacement = !tooltip_type_element_remplacement" class="fas fa-question-circle css_pointer" aria-hidden="true" style="padding: 10px; background: #ff0000; color: white; height: 30px; text-align: center;"></i>
								</div>
							</template>
						</div>
                        <div class="row" v-if="tooltip_nom_donnee_different || tooltip_type_element_remplacement">
                            <div class="col-sm-2"></div>
                            <div class="col-sm-4">
								<template v-if="tooltip_nom_donnee_different">
									<p style="font-size:11px;background-color: #bababa;padding: 1px;">
										Cette option est à passer à "Oui" s'il y a plusieurs sous formulaire pour le même type élément.
									</p>
								</template>
                            </div>
                            <div class="col-sm-2"></div>
                            <div class="col-sm-4">
								<template v-if="tooltip_type_element_remplacement">
									<p style="font-size:11px;background-color: #bababa;padding: 1px;">
										Permet d'attribuer un type élément dans le cas où plusieurs sous formulaire ont le même type élément.<br>
										Exemple si l'on veut un sous formulaire adresse de livraison et un sous formulaire adresse de facturation : <br>
									</p>
									<p style="font-size:11px;background-color: #bababa;padding: 1px;padding-left:5px;">On mettra "adresse_de_livraison" pour le premier</p>
									<p style="font-size:11px;background-color: #bababa;padding: 1px;padding-left:5px;">On mettra "adresse_de_facturation" pour le second</p>
									<p style="font-size:11px;background-color: #bababa;padding: 1px;">
										Attention ce champ doit être unique pour tous les sous-formulaire.
									</p>
								</template>
                            </div>
                        </div>
						<div class="row">
							<div class="col-sm-2">
								Nom affichage
							</div>
							<div class="col-sm-4">
								<input type="text" v-model="eden_sous_formulaire.nom_affichage_sous_formulaire" name="nom_affichage_sous_formulaire">
							</div>
							@champ('eden_sous_formulaire','optionnel',2,4)
						</div>
						<div class="row">
							@champ('eden_sous_formulaire','unique',2,4)
							@champ('eden_sous_formulaire','nombre',2,4)
						</div>
						<div class="row" style="padding-left: 15px;padding-right: 15px;">
							<table class="table table-bordered table-hover">
								<thead>
									<tr>
										<th>
											Donnée vue
										</th>
										<th>
											Valeur par défaut
										</th>
										<th>
											Supprimer
										</th>
									</tr>
								</thead>
								<tbody>
									<tr v-for="(data_vue, index_data_vue) in eden_sous_formulaire.data_vue" :key="index_data_vue">
										<td>
											<input type="text" v-model="data_vue.nom">
										</td>
										<td>
											<input-parametrage
												:type_utilisateur="$root.moi.type_utilisateur"
												at_custom="#" name="valeur"
												:vmodel="data_vue"
												:donnees="champs_libres_affichage">
											</input-parametrage>
										</td>
										<td>
											<span class="fa fa-trash" @click="supprimer_ligne_sous_formulaire('data_vue', index_data_vue)"></span>
										</td>
									</tr>
								</tbody>
							</table>
							<span style="font-style: italic;color: gray;cursor: pointer;" @click="ajouter_ligne_sous_formulaire('data_vue')">Ajouter une ligne +</span>
						</div>
						<div class="row" style="padding-left: 15px;padding-right: 15px;">
							<table class="table table-bordered table-hover">
								<thead>
								<tr>
									<th>
										Valeur à remplacer
									</th>
									<th>
										Valeur de remplacement
									</th>
									<th>
										Supprimer
									</th>
								</tr>
								</thead>
								<tbody>
								<tr v-for="(remplacement, index_remplacement) in eden_sous_formulaire.remplacement_supplementaire" :key="index_remplacement">
									<td>
										<input type="text" v-model="remplacement[0]">
									</td>
									<td>
										<input type="text" v-model="remplacement[1]">
									</td>
									<td>
										<span class="fa fa-trash" @click="supprimer_ligne_sous_formulaire('remplacement_supplementaire', index_remplacement)"></span>
									</td>
								</tr>
								</tbody>
							</table>
							<span style="font-style: italic;color: gray;cursor: pointer;" @click="ajouter_ligne_sous_formulaire('remplacement_supplementaire')">Ajouter une ligne +</span>
						</div>
					</div>

					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" @click="modale_ajout_sous_formulaire = false">Fermer</button>
						<button type="button" class="btn btn-primary" @click="enregistrer_sous_formulaire">Ajouter</button>
					</div>

				</div>
			</div>
		</div>
	</transition>
</template>

<!-- Modal modification du formulaire -->
<div class="modal fade" id="modal_modification_formulaire" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Gestion du formulaire</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body css_form">
				<traduction-table  categorie="12" :filtrage_index="formulaire.index_traduction+'.'"></traduction-table>
				<div class="row">
					<div class="col-sm-6">
						<label for="nom_formulaire">Forcer l'utilisation du formulaire paramétrable</label>

					</div>
					<div class="col-sm-6">
						<select name="surcharger_la_vue" id="surcharger_la_vue" v-model="formulaire.surcharger_la_vue">
							<option v-for="valeur in $root.valeurs_listes_formatees[14].standard" value="valeur.id_valeur" v-html="valeur.valeur"></option>
						</select>
					</div>
				</div>

				<div class="row">
					<div class="col-sm-6">
						Data Vuejs
					</div>
					<div class="col-sm-6">
						<textarea name="vuejs_data" v-model="formulaire.vuejs_data"></textarea>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-6">
						Méthodes Vuejs
					</div>
					<div class="col-sm-6">
						<textarea name="vuejs_methods" v-model="formulaire.vuejs_methods"></textarea>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
				<button type="button" class="btn btn-primary" @click="enregistrer_formulaire">Enregistrer</button>
			</div>
		</div>
	</div>
</div>


<!-- Modal ajout élément -->
<div class="modal fade" id="modal_modification_champ" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Gestion des formulaires</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form action="#" method="post" class="css_form" id="formulaire_champ_libre">
					{{ csrf_field() }}
					<div class="row">
						<div class="col-sm-12 css_form_ligne_titre">Informations générales</div>
					</div>
					<div class="row">
						<div class="col-sm-3">
							Nom sql
						</div>
						<div class="col-sm-9">
							<select name="nom_sql" id="nom_sql">
								@foreach($champs_libres as $champ)
								<option value="{{ $champ['nom_sql'] }}">{{ $champ['nom'] }}</option>
								@endforeach
							</select>
						</div>
						<div class="col-sm-4">Taille avant le libellé</div>
						<div class="col-sm-2">
							<select name="taille_avant" id="taille_avant">
								@for($i=0; $i<=12; $i++)
								<option value="{{ $i }}">{{ $i }}</option>
								@endfor
							</select>
						</div>
						<div class="col-sm-4">Taille après le champ</div>
						<div class="col-sm-2">
							<select name="taille_apres" id="taille_apres">
								@for($i=0; $i<=12; $i++)
								<option value="{{ $i }}">{{ $i }}</option>
								@endfor
							</select>
						</div>
						<div class="col-sm-4">Taille du libellé</div>
						<div class="col-sm-2">
							<select name="taille_libelle" id="taille_libelle">
								@for($i=0; $i<=12; $i++)
								@if($i == 2)
								<option value="{{ $i }}" selected="">{{ $i }}</option>
								@else
								<option value="{{ $i }}">{{ $i }}</option>
								@endif
								@endfor
							</select>
						</div>
						<div class="col-sm-4">Taille du champ</div>
						<div class="col-sm-2">
							<select name="taille_champ" id="taille_champ">
								@for($i=0; $i<=12; $i++)
								@if($i == 4)
								<option value="{{ $i }}" selected="">{{ $i }}</option>
								@else
								<option value="{{ $i }}">{{ $i }}</option>
								@endif
								@endfor
							</select>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
				<button type="button" class="btn btn-primary" @click="enregistrer">Enregistrer</button>
			</div>
		</div>
	</div>
</div>

<!-- Modal ajout élément -->
<div class="modal fade" id="modal_suppression" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Suppression du formulaire</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				Êtes-vous certain de vouloir supprimer le formulaire ?
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
				<button type="button" class="btn btn-danger" @click="supprimer_formulaire_valide">Supprimer</button>
			</div>
		</div>
	</div>
</div>

<!-- Modal ajout/édition utilisateur -->
<div class="modal fade" id="modal_condition_d_apparition" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Conditions d'apparition</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-sm-6">
						<template v-for="champ_modale in champs_libres">
							<p @click="recuperer_les_options(champ_modale)" v-if="champ_modale.type == 1 ||champ_modale.type == 20">@{{ champ_modale.nom }}</p>
						</template>
					</div>
					<div class="col-sm-6" >
						<h4><small>@{{ nom_sql_du_select }}</small></h4>
						<hr v-if="nom_sql_du_select != ''" style="width: 100%;">
						<template v-for="option in options">
							<p @click="ajouter_condition(option)"> - @{{ option }}</p>
						</template>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" @click="fermer_modale" data-dismiss="modal">Fermer</button>
				<button type="button" class="btn btn-primary" @click="enregistrer_conditions">Enregistrer</button>
			</div>
		</div>
	</div>
</div>

<template v-if="modale_condition_vuejs">
	<transition name="modal" >
		<div class="modal-mask">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Conditions js</h5>
						<button type="button" class="close" @click="modale_condition_vuejs = false" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>

					<div class="modal-body css_form">
						<div class="row">
							<div class="col-sm-2 d-flex" style="flex-direction: column;">
								Affichage
								<span>
									<input id="presence_dom" type="checkbox"
										   :true-value="1"
										   :false-value="0"
										   @change="enregistre_un_champ(champ_condition_vuejs)" v-model="champ_condition_vuejs.condition_affichage_en_v_show">
									<label for="presence_dom">Présence dans le DOM ?</label>
								</span>
							</div>
							<div class="col-sm-10">
								<textarea @change="enregistre_un_champ(champ_condition_vuejs)"  v-model="champ_condition_vuejs.condition_affichage_v_if"></textarea>
							</div>
						</div>
						<div class="row" v-if="champ_libre_condition_vuejs.obligatoire != 1 && (champ_condition_vuejs.type_champ == 0 || champ_condition_vuejs.type_champ == null)">
							<div class="col-sm-2">Obligatoire</div>
							<div class="col-sm-10">
								<textarea @change="enregistre_un_champ(champ_condition_vuejs)"  v-model="champ_condition_vuejs.condition_obligatoire"></textarea>
							</div>
						</div>
						<div class="row" v-if="champ_libre_condition_vuejs.lecture_seule != 1 && (champ_condition_vuejs.type_champ == 0 || champ_condition_vuejs.type_champ == null)">
							<div class="col-sm-2">Lecture seule</div>
							<div class="col-sm-10">
								<textarea @change="enregistre_un_champ(champ_condition_vuejs)"  v-model="champ_condition_vuejs.condition_lecture_seule"></textarea>
							</div>
						</div>
					</div>

					<div class="modal-footer">
					</div>
				</div>
			</div>
		</div>
	</transition>
</template>


@endsection
@section('scripts')
<script>
	$( ".sortable" ).sortable({

		items: 'tr, .js_champ',
		update: function() {

			vue_instance.enregistrer();
		},
	});
	$( "#sortable" ).disableSelection();

</script>

<script>
	require.config({ paths: { 'vs': 'https://unpkg.com/monaco-editor@latest/min/vs' }});
	window.MonacoEnvironment = { getWorkerUrl: () => proxy };

	let proxy = URL.createObjectURL(new Blob([`
		self.MonacoEnvironment = {
			baseUrl: 'https://unpkg.com/monaco-editor@latest/min/'
		};
		importScripts('https://unpkg.com/monaco-editor@latest/min/vs/base/worker/workerMain.js');
		`], { type: 'text/javascript' }));
	require(["vs/editor/editor.main"], function () {});
	</script>

	<script>
		$(document).on("click", ".js_toggle_bloc_edition_html", function() {
			$(this).toggleClass('deploy')
			$(this).parent().parent().next('.js_bloc_edition_html').slideToggle(350);
		});
	</script>

	<script type="text/javascript">

		// On ajoute les bloc HTML si il y en a
		vue_instance.champs_libres.forEach(function(champ){

			// C'est un bloc HTML
			if (champ.type_champ == 1) {

				var id_editeur = champ.id_editeur;

				// On prépare editeurs_html pour les prochains blocs ajouté
				if (champ.id_editeur > vue_instance.editeurs_html)
					vue_instance.editeurs_html = champ.id_editeur;

				setTimeout(function(){
					require(["vs/editor/editor.main"], function () {
						let editor = monaco.editor.create(document.getElementById('js_html_edition_'+id_editeur), {
							value: [
							champ.valeur_html
							].join('\n'),
							language: 'html',
							theme: 'vs-dark'
						});
						editor.getModel().onDidChangeContent((event) => {

						 	vue_instance.champs_libres.forEach(function(champ){

							  	// C'est un bloc HTML
							  	if (champ.type_champ == 1) {

							  		// C'est le bon champ
							  		if (champ.id_editeur == id_editeur) {

							  			champ['valeur_html'] = editor.getValue();
										vue_instance.$forceUpdate();
							  		}
							  	}
							});
						});
					});
				}, 300);
			}
		});

	</script>
	@endsection

	@section('donnees_pour_vuejs_data')
	type_element: '{{$type_element}}',
	champs_libres: {!! $les_champs !!},
	modele_champs_libres: {!! $champs_libres !!},
	formulaire: {!! $formulaire !!},
	editeurs_html: 0,
	champ: {},
	options: {},
	editor: [],
	utilisateurs: {!! $utilisateurs !!},
	utilisateurs_champ: {},
	vues_ajoutables: {!! json_encode($vues_ajoutables) !!},
	nom_sql_du_select: "",
	nom_sql_du_champ_libre: "",
	eden_sous_formulaire: {!! modele_par_defaut('eden_sous_formulaire') !!},
	modale_ajout_sous_formulaire: false,
	type_elements_pour_sous_formulaire: {!! $type_elements_pour_sous_formulaire !!},
    tooltip_nom_donnee_different: false,
    tooltip_type_element_remplacement: false,
    modale_condition_vuejs: false,
    champ_condition_vuejs: {},
	les_conditions: [

	@if(!empty($formulaire->type_element))
		@foreach(table_libre($formulaire->type_element)->champs_libres()->where('afficher_sur_formulaire', 1)->orderBy('ordre')->get() as $le_champ)
			@if($le_champ['type'] == 20 || $le_champ['type'] == 1)
				{{$le_champ['nom_sql']}} = [],
			@endif
		@endforeach
	@else
		@foreach(table_libre($type_element)->champs_libres()->where('afficher_sur_formulaire', 1)->orderBy('ordre')->get() as $le_champ)
			@if($le_champ['type'] == 20 || $le_champ['type'] == 1)
				{{$le_champ['nom_sql']}} = [],
			@endif
		@endforeach
	@endif
	],
	@endsection

	<script>
		@section('donnees_pour_vuejs_methods')

		ajouter_condition: function(option) {

			this.les_conditions[this.nom_sql_du_select].push(option);
		},

		enregistrer_conditions: function(event){

			$.post({

				url: "{{ URL::to("/eden/parametrage/formulaire/ajouter_conditions") }}",
				dataType: "json",
				data: {

					nom_formulaire: champ.nom_formulaire,
					nom_sql_champ_libre: vue_instance.nom_sql_du_champ_libre,
					les_conditions: vue_instance.les_conditions,
				}
			});
		},

		recuperer_les_options: function(champ){

			vue_instance.nom_sql_du_select = champ.nom_sql;

			$.post({

				url: "{{ URL::to("eden/parametrage/formulaire/obtenir_options") }}",
				dataType: "json",
				data: {

					type_element: champ.type_element,
					nom_sql: champ.nom_sql,
				}
			}, function(data) {
				vue_instance.options = data;
			});

		},


		afficher_modale: function(nom_sql_champ_libre) {

			$('#modal_condition_d_apparition').modal('show');
			vue_instance.nom_sql_du_champ_libre = nom_sql_champ_libre;
		},

		fermer_modale: function(event) {

			$('#modal_condition_d_apparition').modal('hide');
		},

		modifier_champ: function(champ) {

			this.champ = champ;
			$('#modal_modification_champ').modal('show');
		},

		supprimer_formulaire: function(event) {

			$('#modal_suppression ').modal('show');
		},

		supprimer_formulaire_valide: function(event) {

			// $('#modal_suppression ').modal('hide');

			$.get({

				url: "{{ URL::to('/eden/parametrage/formulaire/supprimer') }}/{{$formulaire->id}}",
				dataType: "json",
			}).done(async function(donnees) {

				$('#modal_suppression ').modal('hide');

				// console.log(donnees);

				if(donnees == "") {

					window.location = "{{ URL::to("eden/parametrage/table_libre/zoom/".$type_element) }}";
				}
				else{

					await erreur(donnees);
				}
			});
		},

		modifier_formulaire: function() {

			$('#modal_modification_formulaire').modal('show');
		},
		ajout_bloc_html: function() {
			loading(true);
			vue_instance.editeurs_html++;
			var id_editeur = vue_instance.editeurs_html;
			var editeur_tmp;
			var champ_html = {};
			champ_html['id_editeur'] = id_editeur;
			champ_html['nom_sql'] = 'Intégration bloc html '+id_editeur;
			champ_html['type_champ'] = 1;
			champ_html['type_element'] = '{{$type_element}}';
			champ_html['nom_formulaire'] = '{{ $nom_formulaire }}';
			champ_html['taille_avant'] = 0;
			champ_html['taille_apres'] = 0;
			champ_html['taille_champ'] = 12;
			champ_html['taille_libelle'] = 0;
			champ_html['taille_total'] = 12;
			vue_instance.champs_libres.push(champ_html);
			vue_instance.$forceUpdate();
			index_max_champs_libres = vue_instance.champs_libres.length - 1;
			setTimeout(function(){
				require(["vs/editor/editor.main"], function () {
					let editor = monaco.editor.create(document.getElementById('js_html_edition_'+vue_instance.editeurs_html), {
						value: [
						'<div class="css_test_easydev_edit_html">',
						'\tHello ⌈ Eden ⌋',
						'</div>'
						].join('\n'),
						language: 'html',
						theme: 'vs-dark'
					});
					editor.getModel().onDidChangeContent((event) => {

					 	vue_instance.champs_libres.forEach(function(champ){

						  	// C'est un bloc HTML
						  	if (champ.type_champ == 1) {

						  		// C'est le bon champ
						  		if (champ.id_editeur == id_editeur) {

						  			champ['valeur_html'] = editor.getValue();
									vue_instance.$forceUpdate();
						  		}
						  	}
						});
					});
					vue_instance.champs_libres[index_max_champs_libres]['valeur_html'] = editor.getValue();
					vue_instance.$forceUpdate();
					vue_instance.champs_libres.forEach((el, index) => {
					  if (el.ordre !== index) {
					  		el.ordre = index+1;
					 	}
					});
					$.post({

						url: "{{ URL::to("eden/parametrage/formulaire") }}",
						dataType: "json",
						data: {
							type_element: '{{$type_element}}',
							nom_formulaire: '{{ $nom_formulaire }}',
							nom_sql: vue_instance.champs_libres[index_max_champs_libres]['nom_sql'],
							id_editeur: vue_instance.champs_libres[index_max_champs_libres]['id_editeur'],
							valeur_html: vue_instance.champs_libres[index_max_champs_libres]['valeur_html'],
							ordre: vue_instance.champs_libres[index_max_champs_libres]['ordre'],
							type_champ: 1,
							taille_avant: 0,
							taille_apres: 0,
							taille_champ: 12,
							taille_libelle: 0,
						}
					});
					// On calcul les % des 4 parties
					vue_instance.champs_libres.forEach(function(champ){

						if(champ.type_champ !== 2) {

							var taille_total = champ.taille_avant + champ.taille_apres + champ.taille_champ + champ.taille_libelle;

							if (taille_total > 12)
								taille_total = 12;

							champ.pourcentage_avant = (champ.taille_avant * 100) / taille_total.toFixed(2);
							champ.pourcentage_apres = (champ.taille_apres * 100) / taille_total.toFixed(2);
							champ.pourcentage_champ = (champ.taille_champ * 100) / taille_total.toFixed(2);
							champ.pourcentage_libelle = (champ.taille_libelle * 100) / taille_total.toFixed(2);
							champ.taille_total = taille_total;
						}
					});
				});
				loading(false);
			}, 300);
		},

		ajout_vue: function(vue_selectionnee) {
			loading(true);

            let ordre = 0;

            if (vue_instance.champs_libres.length > 0) {
                index_max_champs_libres = vue_instance.champs_libres.length - 1;
                ordre = vue_instance.champs_libres[index_max_champs_libres]['ordre']
            }

			//enregistre
			$.post({

				url: "{{ URL::to("eden/parametrage/formulaire") }}",
				dataType: "json",
				data: {
					type_element: '{{$type_element}}',
					nom_formulaire: '{{ $nom_formulaire }}',
					nom_vue: vue_selectionnee.nom_fichier,
					type_vue: vue_selectionnee.type_vue,
					type_champ: 2,
					taille_avant: 0,
					taille_apres: 0,
					taille_champ: 0,
					taille_libelle: 0,
					ordre: ordre
				}
			}).done(async function(donnees) {

				if(donnees.retour !== true) {

					await erreur(donnees.retour);
					return;
				}
				else{

					vue_instance.champs_libres.push(donnees.nouveau_champ);
					vue_instance.vues_ajoutables.splice(vue_instance.vues_ajoutables.indexOf(vue_selectionnee), 1);
					loading(false);
				}
			});


		},

		enregistre_un_champ: function(champ) {
			loading(true);

			$.post({

				url: "{{ URL::to("eden/parametrage/formulaire/enregistre_un_champ") }}",
				dataType: "json",
				data: {
					champ: champ,
				}
			});

			if(vue_instance.editeurs_html > 0){

				models_monaco = monaco.editor.getModels();
				models_monaco.forEach((editor) => {
					editor.dispose();
				});
			}

			// On calcul les % des 4 parties
			vue_instance.champs_libres.forEach(function(champ){

				if(champ.type_champ !== 2) {

					var taille_total = champ.taille_avant + champ.taille_apres + champ.taille_champ + champ.taille_libelle;

					if (taille_total > 12)
						taille_total = 12;

					champ.pourcentage_avant = (champ.taille_avant * 100) / taille_total.toFixed(2);
					champ.pourcentage_apres = (champ.taille_apres * 100) / taille_total.toFixed(2);
					champ.pourcentage_champ = (champ.taille_champ * 100) / taille_total.toFixed(2);
					champ.pourcentage_libelle = (champ.taille_libelle * 100) / taille_total.toFixed(2);
					champ.taille_total = taille_total;
				}
			});

			// On ajoute les bloc HTML si il y en a
			vue_instance.champs_libres.forEach(function(champ){

				// C'est un bloc HTML
				if (champ.type_champ == 1) {

					var id_editeur = champ.id_editeur;

					// On prépare editeurs_html pour les prochains blocs ajouté
					if (champ.id_editeur > vue_instance.editeurs_html)
						vue_instance.editeurs_html = champ.id_editeur;

					setTimeout(function(){
						require(["vs/editor/editor.main"], function () {
							let editor = monaco.editor.create(document.getElementById('js_html_edition_'+id_editeur), {
								value: [
								champ.valeur_html
								].join('\n'),
								language: 'html',
								theme: 'vs-dark'
							});
							editor.getModel().onDidChangeContent((event) => {

							 	vue_instance.champs_libres.forEach(function(champ){

								  	// C'est un bloc HTML
								  	if (champ.type_champ == 1) {

								  		// C'est le bon champ
								  		if (champ.id_editeur == id_editeur) {

								  			champ['valeur_html'] = editor.getValue();
											vue_instance.$forceUpdate();
								  		}
								  	}
								});
							});
						});
					}, 300);
				}
			});

			loading(false);
		},

		enregistrer_formulaire: function() {

			// console.log('enregistrer_formulaire');

			$('#modal_modification_formulaire').modal('hide');
			var formulaire = vue_instance.formulaire;
		// on enregistre
		$.post({

			url: "{{ route("parametrage.formulaire.modifier") }}",
			dataType: "json",
			data: {
				id_du_formulaire: formulaire['id'],
				nom_formulaire: formulaire['nom_formulaire'],
				titre_formulaire: formulaire['titre_formulaire'],
				vuejs_data: formulaire['vuejs_data'],
				vuejs_methods: formulaire['vuejs_methods'],
				surcharger_la_vue: formulaire['surcharger_la_vue'],
			}
		});
	},

	enregistrer: function() {

		loading(true);

		nom_sql = $("#nom_sql").val();
		taille_libelle = $("#taille_libelle").val();
		taille_champ = $("#taille_champ").val();
		taille_apres = $("#taille_apres").val();
		taille_avant = $("#taille_avant").val();

		$('#modal_modification_champ').modal('hide');
		// on enregistre
		$.post({

			url: "{{ URL::to("eden/parametrage/formulaire") }}",
			dataType: "json",
			data: {
				type_element: '{{$type_element}}',
				taille_avant: parseInt(taille_avant),
				taille_libelle: parseInt(taille_libelle),
				taille_champ: parseInt(taille_champ),
				taille_apres: parseInt(taille_apres),
				nom_sql: nom_sql,
				nom_formulaire: '{{ $nom_formulaire }}',
			}
		}).done(async (donnees) => {

			if(donnees.retour !== true) {

				await erreur(donnees.retour);
				return;
			}
			else{

				this.champs_libres.push(donnees.nouveau_champ);

			}

			loading(false);
		});

		if(vue_instance.editeurs_html > 0){

				models_monaco = monaco.editor.getModels();
				models_monaco.forEach((editor) => {
					editor.dispose();
				});
			}

			// On calcul les % des 4 parties
			vue_instance.champs_libres.forEach(function(champ){

				var taille_total = champ.taille_avant+champ.taille_apres+champ.taille_champ+champ.taille_libelle;

				if (taille_total > 12)
					taille_total = 12;

				champ.pourcentage_avant = (champ.taille_avant*100)/taille_total.toFixed(2);
				champ.pourcentage_apres = (champ.taille_apres*100)/taille_total.toFixed(2);
				champ.pourcentage_champ = (champ.taille_champ*100)/taille_total.toFixed(2);
				champ.pourcentage_libelle = (champ.taille_libelle*100)/taille_total.toFixed(2);
				champ.taille_total = taille_total;
			});

			// On ajoute les bloc HTML si il y en a
			vue_instance.champs_libres.forEach(function(champ){

				// C'est un bloc HTML
				if (champ.type_champ == 1) {

					var id_editeur = champ.id_editeur;

					// On prépare editeurs_html pour les prochains blocs ajouté
					if (champ.id_editeur > vue_instance.editeurs_html)
						vue_instance.editeurs_html = champ.id_editeur;

					setTimeout(function(){
						require(["vs/editor/editor.main"], function () {
							let editor = monaco.editor.create(document.getElementById('js_html_edition_'+id_editeur), {
								value: [
								champ.valeur_html
								].join('\n'),
								language: 'html',
								theme: 'vs-dark'
							});
							editor.getModel().onDidChangeContent((event) => {

							 	vue_instance.champs_libres.forEach(function(champ){

								  	// C'est un bloc HTML
								  	if (champ.type_champ == 1) {

								  		// C'est le bon champ
								  		if (champ.id_editeur == id_editeur) {

								  			champ['valeur_html'] = editor.getValue();
											vue_instance.$forceUpdate();
								  		}
								  	}
								});
							});
						});
					}, 300);
				}
			});

	},

	supprimer_le_champ: function(champ) {

		loading(true);

		$.post({

			url: "{{ URL::to("eden/parametrage/formulaire/supprimer") }}",
			dataType: "json",
			data: {
				champ: champ,
			}
		}).done(async function(donnees) {

			if(donnees.retour !== true) {

				await erreur(donnees.retour);
				return;
			}
			else {

				for(index = 0;index < vue_instance.champs_libres.length;index++){

					if (vue_instance.champs_libres[index] == champ) {

						vue_instance.champs_libres.splice(index, 1);
					}
				}

				if(donnees.nouvelle_vue_selectionnable != null){

					vue_instance.vues_ajoutables.push(donnees.nouvelle_vue_selectionnable);
				}
				vue_instance.champs_libres = donnees.les_champs;
			}
			loading(false);

		});
	},

	ajout_sous_formulaire: function(champ = false){

		var vue_composant = this;

		vue_composant.tooltip_nom_donnee_different = false;
		vue_composant.tooltip_type_element_remplacement = false;

		if(champ === false) {
			vue_composant.eden_sous_formulaire = {!! modele_par_defaut("eden_sous_formulaire") !!};
			vue_composant.modale_ajout_sous_formulaire = true;
			return;
		}

		loading(true)
		$.get({
			url: '{{ URL::to('/eden/parametrage/formulaire/sous_formulaire/recuperer') }}/' + champ.nom_sous_formulaire,
		}).done(function(sous_formulaire){

			loading(false)

			var nouvelle_data = [];

			for (const [nom, valeur] of Object.entries(JSON.parse(sous_formulaire.data_vue))) {

				nouvelle_data.push({nom: nom, valeur: valeur});

			}

			sous_formulaire.id_champ = champ.id;
			sous_formulaire.nom_affichage_sous_formulaire = vue_composant.$root.traduction("formulaire." + champ.nom_formulaire + ".sous_formulaire." + champ.nom_sous_formulaire + ".titre");
			sous_formulaire.data_vue = nouvelle_data;
			sous_formulaire.remplacement_supplementaire = JSON.parse(sous_formulaire.remplacement_supplementaire);

			vue_composant.eden_sous_formulaire = sous_formulaire;
			vue_composant.$forceUpdate();
			vue_composant.modale_ajout_sous_formulaire = true;

		});

	},

	enregistrer_sous_formulaire: function(){

		var vue_composant = this;

		var sous_formulaire = vue_composant.eden_sous_formulaire;

		sous_formulaire.formulaire_id = {{ $formulaire->id }};
		sous_formulaire.type_element = '{{ $type_element }}';

		$.post({
			url: "{{ URL::to('/eden/parametrage/formulaire/sous_formulaire/enregistrer') }}/{{$formulaire->id}}",
			dataType: "json",
			data: {
				sous_formulaire: sous_formulaire,
			}
		}).done(async function(retour){

			if(retour.retour !== true) {
				await erreur(retour.retour);
				return;
			}

			if(retour.hasOwnProperty('nouveau_champ'))
				vue_instance.champs_libres.push(retour.nouveau_champ);

			vue_composant.modale_ajout_sous_formulaire = false;
			toastr.success("Sous-formulaire ajouté !");

		});

	},

	changement_type_element_sous_formulaire: function(){

		var vue_composant = this;

		for (const [type_element_sous_formulaire, nom_sql_champ] of Object.entries(vue_composant.type_elements_pour_sous_formulaire)) {

			if(vue_composant.eden_sous_formulaire.type_element_enfant == type_element_sous_formulaire){

				vue_composant.eden_sous_formulaire.champ_liaison = nom_sql_champ;
				vue_composant.eden_sous_formulaire.nom_affichage_sous_formulaire = vue_composant.$root.traduction("tables_libres." + vue_composant.eden_sous_formulaire.type_element_enfant + ".nom_table");
				return;

			}

		}

	},

	ajouter_ligne_sous_formulaire: function(champ){

		var vue_composant = this;

		if(champ == "remplacement_supplementaire")
			vue_composant.eden_sous_formulaire.remplacement_supplementaire.push(["", ""]);
		else
			vue_composant.eden_sous_formulaire.data_vue.push({nom: "", valeur: ""});

	},

	supprimer_ligne_sous_formulaire: function(champ, index){

		var vue_composant = this;

		vue_composant.eden_sous_formulaire[champ].splice(index, 1);

	},

	calcul_nom_sql: function() {

		var vue_contexte = this;

		if(vue_contexte.champ.id_cl == undefined){

			var nom_sql = this.eden_sous_formulaire.champ_liaison;

			// on crée le nom_sql
			var accents = [

				/[\300-\306]/g, /[\340-\346]/g, // A, a
				/[\310-\313]/g, /[\350-\353]/g, // E, e
				/[\314-\317]/g, /[\354-\357]/g, // I, i
				/[\322-\330]/g, /[\362-\370]/g, // O, o
				/[\331-\334]/g, /[\371-\374]/g, // U, u
				/[\321]/g, /[\361]/g, // N, n
				/[\307]/g, /[\347]/g, // C, c

			];

			var sans_accents =['A','a','E','e','I','i','O','o','U','u','N','n','C','c'];

			for(var i = 0; i < accents.length; i++){

				nom_sql = nom_sql.replace(accents[i], sans_accents[i]);

			}

			nom_sql = nom_sql.toLowerCase();

			// autres caractères spéciaux
			var a_remplacer = [/[\41-\57]/g, /[\72-\100]/g, /[\133-\140]/g, /[\173-\176]/g, /¤/g, /£/g, /§/g, /µ/g, /¨/g, /;/g, /°/g,/ /g,/’/g,/²/g,/€/g];

			for(var n = 0; n < a_remplacer.length; n++){

				nom_sql = nom_sql.replace(a_remplacer[n], "_");

			}

			vue_contexte.eden_sous_formulaire.champ_liaison = nom_sql;

		}

	},

	affichage_modale_condition : function(champ){

		this.champ_condition_vuejs = champ;
		this.modale_condition_vuejs = true;
	},

	condition_presente : function(champ){

		if((champ.condition_affichage_v_if != null && champ.condition_affichage_v_if != 1 && champ.condition_affichage_v_if != '') ||
			(champ.condition_obligatoire != null && champ.condition_obligatoire !== 1 && champ.condition_obligatoire != '') ||
			(champ.condition_lecture_seule != null && champ.condition_lecture_seule !== 1 && champ.condition_lecture_seule != ''))
			return true;

		return false;
	},

	modification_ordre_champ : function(){

		var champs_libres_ordres = {};

		for(ordre in this.champs_libres){

			var champ_libre = this.champs_libres[ordre];

			champs_libres_ordres[champ_libre.id] = ordre;
		}

		$.post({

			url: "{{ URL::to("eden/parametrage/formulaire/modifier_ordre") }}",
			dataType: "json",
			data: {
				nom_formulaire: this.formulaire.nom_formulaire,
				type_element: this.formulaire.type_element,
				ordres: champs_libres_ordres,
			}
		});
	},

	@endsection

	@push('donnees_pour_vuejs_computed')

		champ_libre_condition_vuejs : function(){

			var champ_libre = {};

			for(champ of this.modele_champs_libres){

				if(champ.nom_sql == this.champ_condition_vuejs.nom_sql)
					champ_libre = champ;
			}

			return champ_libre;
		},

		champs_libres_affichage : function() {

			var champs_a_retourner = this.modele_champs_libres.map((x) => {
				return {
					name : `${x.nom} (${x.nom_sql})`,
					id: `#${x.type_element}.${x.nom_sql}#`,
				}
			});

			return champs_a_retourner;

		},
	@endpush
</script>

@push('scripts')
<script>

	$(document).ready(function(){

		$("a").on('click', function(event) {

			if (this.hash !== "") {

				event.preventDefault();
				var hash = this.hash;
				$('html, body').animate({ scrollTop: $(hash).offset().top - 70}, 800, function(){

					window.location.hash = hash;
				});
			}
			$(hash).addClass('js_scroll');
			setTimeout(function(){
				$(hash).removeClass('js_scroll');
			}, 3000);
		});
	});

</script>
@endpush
