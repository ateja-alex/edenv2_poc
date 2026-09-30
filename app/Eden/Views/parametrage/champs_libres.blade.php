@extends('eden::templates.template')

@section('title') Champs libres @stop

@section('content')

	<div class="content-wrapper" >
		<div id="base-content" class="container-fluid">

			@if($erreur_droit != null)
				<div class="alert alert-danger">{{ $erreur_droit }}</div>
			@endif
			@if($erreur_droit_traduction !=null)
				<div class="alert alert-danger">{{ $erreur_droit_traduction }}</div>
			@endif

			@include('eden::includes.fil_ariane', ['fil_ariane' => array(
				array('route' => 'parametrage.index', 'nom' => 'Paramétrage'),
				array('route' => 'parametrage.table_libre.principales', 'nom' => 'Elements paramétrables'),
				array('route' => 'parametrage.table_libre.zoom','arguments' => [table_libre($type_element)->type_element] , 'nom' => table_libre($type_element)->element),
				array('nom' => 'Champs')
			)])

			<div class="row">
				<div class="col-md-12">
					<div class="card mb-3">
						<div class="card-header d-flex align-items-center">
							<h4>
								Champs libres
							</h4>
							@if(table_libre($type_element)->vue_sql !=1)
								<enrichissement :type_element="type_element" class="css_ajouter_element ml-auto mr-2">
									<template v-slot:bouton="{parametrage_modale}">
										<span class="css_action_icon" @click="parametrage_modale()" :title="traduction('composant.enrichissement')" data-toggle="tooltip">
											<svg style="margin-top: -2px;" fill="white"  viewBox="0 0 30 30" width="20" height="20"><path d="M13.95 6.805l.654 3.06c.593 2.773 2.759 4.939 5.532 5.532l3.06.654c1.024.219 1.024 1.68 0 1.899l-3.06.654c-2.773.593-4.939 2.759-5.532 5.532l-.654 3.06c-.219 1.024-1.68 1.024-1.899 0l-.654-3.06c-.593-2.773-2.759-4.939-5.532-5.532l-3.06-.654c-1.024-.219-1.024-1.68 0-1.899l3.06-.654c2.773-.593 4.939-2.759 5.532-5.532l.654-3.06C12.269 5.781 13.731 5.781 13.95 6.805zM23.641 2.525l.588 2.119c.152.547.58.975 1.127 1.127l2.119.588c.65.18.65 1.102 0 1.282l-2.119.588c-.547.152-.975.58-1.127 1.127l-.588 2.119c-.18.65-1.102.65-1.282 0l-.588-2.119c-.152-.547-.58-.975-1.127-1.127l-2.119-.588c-.65-.18-.65-1.102 0-1.282l2.119-.588c.547-.152.975-.58 1.127-1.127l.588-2.119C22.539 1.875 23.461 1.875 23.641 2.525z"/></svg>
										</span>
									</template>
								</enrichissement>
								<span data-toggle="tooltip" data-placement="left" title="" class="css_ajouter_element mr-2" data-original-title="Modifier les affichages" @click="modifier_chaine_affichage" ><i class="css_action_icon fa fa-fw fa-pencil" aria-hidden="true"></i></span>
								<span data-toggle="tooltip" data-placement="left" title="" class="css_ajouter_element" data-original-title="Nouveau champ libre" @click="ajouter"><i class="css_action_icon fa fa-fw fa-plus-square" aria-hidden="true"></i></span>
								<input  class='css_input_recherche_liste' style="padding-left: 5px" type="text" name="recherche_champs_libres" v-model="recherche_champs_libres" placeholder="Recherche"/>
								<div class="css_btn_recherche_liste">
									<i class="css_action_icon fa fa-search" aria-hidden="true"></i>
								</div>
							@else
								<div class="ml-auto badge badge-primary mr-2">Vue SQL</div>
								<input  data-placement="left" class='css_input_recherche_liste' style="padding-left: 5px" type="text" name="recherche_champs_libres" v-model="recherche_champs_libres" placeholder="Recherche"/>
								<div class="css_btn_recherche_liste">
									<i class="css_action_icon fa fa-search" aria-hidden="true"></i>
								</div>
							@endif

							<a style="margin-left: 10px;" target="_blank" href="eden/parametrage/traduction?categorie=1&recherche=champs_libres.{{$type_element}}." data-toggle="tooltip" data-placement="left" title="" class="css_ajouter_element mr-3" data-original-title="Affichage traduction global">
								<i class="css_action_icon fas fa-flag" aria-hidden="true"></i>
							</a>
						</div>
						<div class="card-body">
							<div class="table-responsive">
								<table class="table table-bordered table-hover" id="liste_champs_libres" width="100%" cellspacing="0">
									<thead>
										<tr>
											<th scope="col">#</th>
											<th scope="col" colspan>Nom [Nom_sql]</th>
											<th scope="col">Obligatoire</th>
											<th scope="col">Recherche</th>
											<th scope="col">Afficher sur formulaire</th>
											<th scope="col">Modifier en masse</th>
											<th scope="col">Modification post validation</th>
											<th scope="col">Responsive</th>
											<th scope="col">Index</th>
											<th scope="col">Valeur par défaut</th>
										</tr>
									</thead>
									<tbody>
										<tr v-for="(champ_libre, key,index) in champs_libres_recherche" :key="champ_libre.nom+'_'+key">
											<td>
												<span class="css_lien_element css__lien" @click="modifier" :nom_sql="champ_libre.nom_sql">
													@{{ champ_libre.id_cl }}
												</span>
											</td>
											<td>
												<p v-if="langue == 1">
													<div style="display: flex;justify-content: space-between;align-items: center;">
														<b>@{{ traduction(champ_libre.index_traduction,'nom') }}</b>
                                                        <span class="fas fa-cogs" v-if="champ_libre.champ_systeme == 1" title="Champ système"></span>
                                                        <template v-if="champ_libre.type == 10">
															<a class="css_pointer" v-if="types_elements_existants.includes(champ_libre.table_pivot)" target="_blank" :href="'eden/parametrage/table_libre/zoom/'+champ_libre.table_pivot">
																<i class="fas fa-table" style="font-size: 17px;" :title="'Table libre pivot : '+traduction('tables_libres.'+champ_libre.table_pivot+'.nom_table')+' ('+champ_libre.table_pivot+')'"></i>
															</a>
															<span class="css_pointer" style="position:relative;" v-else @click="creation_table_libre_pivot(champ_libre.table_pivot)" :title="'Créer la table libre pour la table pivot '+champ_libre.table_pivot">
																<i class="fas fa-table" style="font-size: 17px;"></i>
																<i class="fas fa-plus" style="position: absolute;background: var(--background_navbar);border-radius: 20px;padding: 3px;font-size: 7px;color: white;left: 10px;top: -5px;"></i>
															</span>
														</template>
													</div>
													[@{{champ_libre.nom_sql }}, @{{ types_champs_libres[champ_libre.type] }} <span v-if="champ_libre.type == 10">(@{{types_champs_libres[champ_libre.type_reference]}})</span>]</p>
												<span class="css__lien" v-show="champ_libre.type == 1 || champ_libre.type_reference == 1" @click="champs_libre_modifier_liste_libre(champ_libre)">Modifier la liste</span>
												<span class="css__lien" v-show="champ_libre.type == 20 && listes_editables.includes(champ_libre.liste_choix)" @click="champs_libre_modifier_liste_preenregistree(champ_libre)">Modifier la liste</span>
											</td>
											<td>
												<div class="badge badge-success" @if(table_libre($type_element)->vue_sql !=1) @click="changement_etat($event,'obligatoire',champ_libre.id_cl)" @endif :obligatoire="1" v-show="champ_libre.obligatoire == 1">Obligatoire</div>
												<div class="badge badge-danger" @if(table_libre($type_element)->vue_sql !=1) @click="changement_etat($event,'obligatoire',champ_libre.id_cl)" @endif :obligatoire="0" v-show="champ_libre.obligatoire == 0">Obligatoire</div>
											</td>
											<td>
												<div class="badge badge-success" @if(table_libre($type_element)->vue_sql !=1) @click="changement_etat($event,'recherche',champ_libre.id_cl)" @endif :recherche="1" v-show="champ_libre.recherche == 1">Recherche</div>
												<div class="badge badge-danger" @if(table_libre($type_element)->vue_sql !=1) @click="changement_etat($event,'recherche',champ_libre.id_cl)" @endif :recherche="0" v-show="champ_libre.recherche == 0">Recherche</div>
											</td>
											<td>
												<div class="badge badge-success" @if(table_libre($type_element)->vue_sql !=1) @click="changement_etat($event,'afficher_sur_formulaire',champ_libre.id_cl)" @endif :afficher_sur_formulaire="1" v-show="champ_libre.afficher_sur_formulaire == 1">Formulaire</div>
												<div class="badge badge-danger"  @if(table_libre($type_element)->vue_sql !=1) @click="changement_etat($event,'afficher_sur_formulaire',champ_libre.id_cl)" @endif :afficher_sur_formulaire="0" v-show="champ_libre.afficher_sur_formulaire == 0 || champ_libre.afficher_sur_formulaire == null">Formulaire</div>
											</td>
											<td>
												<div class="badge badge-success" @if(table_libre($type_element)->vue_sql !=1) @click="changement_etat($event,'modifier_en_masse',champ_libre.id_cl)" @endif :modifier_en_masse="1" v-show="champ_libre.modifier_en_masse == 1">Modifier en masse</div>
												<div class="badge badge-danger"  @if(table_libre($type_element)->vue_sql !=1) @click="changement_etat($event,'modifier_en_masse',champ_libre.id_cl)" @endif :modifier_en_masse="0" v-show="champ_libre.modifier_en_masse == 0 || champ_libre.modifier_en_masse == null">Modifier en masse</div>
											</td>
											<td>
												<div class="badge badge-success" @if(table_libre($type_element)->vue_sql !=1) @click="changement_etat($event,'modification_post_validation',champ_libre.id_cl)" @endif :modification_post_validation="1" v-show="champ_libre.modification_post_validation == 1">Modif post validation</div>
												<div class="badge badge-danger"  @if(table_libre($type_element)->vue_sql !=1) @click="changement_etat($event,'modification_post_validation',champ_libre.id_cl)" @endif :modification_post_validation="0" v-show="champ_libre.modification_post_validation == 0 || champ_libre.modification_post_validation == null">Modif post validation</div>
											</td>
											<td>
												<div class="badge badge-success" @if(table_libre($type_element)->vue_sql !=1) @click="changement_etat($event,'afficher_en_responsive_dans_listes',champ_libre.id_cl)" @endif :afficher_en_responsive_dans_listes="1" v-show="champ_libre.afficher_en_responsive_dans_listes == 1">Responsive</div>
												<div class="badge badge-danger"  @if(table_libre($type_element)->vue_sql !=1) @click="changement_etat($event,'afficher_en_responsive_dans_listes',champ_libre.id_cl)" @endif :afficher_en_responsive_dans_listes="0" v-show="champ_libre.afficher_en_responsive_dans_listes == 0 || champ_libre.afficher_en_responsive_dans_listes == null">Responsive</div>
											</td>
											<td>
												<div class="badge badge-success" @if(table_libre($type_element)->vue_sql !=1) @click="changement_etat($event,'index',champ_libre.id_cl)" @endif :index="1" v-show="champ_libre.index == 1">Index</div>
												<div class="badge badge-danger"  @if(table_libre($type_element)->vue_sql !=1) @click="changement_etat($event,'index',champ_libre.id_cl)" @endif :index="0" v-show="champ_libre.index == 0 || champ_libre.index == null">Index</div>
											</td>
											<td>@{{ champ_libre.valeur_defaut }}</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Modal ajout élément -->
    @include('eden::parametrage.include.modal_ajout_champ_libre')

	@include('eden::parametrage.include.modal_champ_libre_liste_libre', ['vue_sql' => table_libre($type_element)->vue_sql])

    <!-- Modal liste formatée -->
    @include('eden::parametrage.include.modal_champ_libre_liste_preenregistree')

	<!-- Modal de gestion des chaines d'affichage -->
	@include('eden::parametrage.include.modal_chaine_affichage')


@endsection

@include('eden::parametrage.include.js_calcul_nom_sql')

@section('donnees_pour_vuejs_data')
	champs_libres: {!! $champs_libres !!},
	types_champs_libres: {!! $types_champs_libres !!},
	listes_editables: {!! $listes_editables!!},
	table_libre_initiale: {!! $table_libre !!},
	fonctionnalite_google: {!! fonctionnalite('google_utiliser_connexion') ? 'true' : 'false' !!},
	table_libre_modification: {},
	modal_ajout_element: false,
	modal_chaine_affichage: false,
	modal_champ_libre_liste_preenregistree: false,
	champ_libre: {},
	recherche_champs_libres: '',
	liste_libre_preenregistree : [],
	type_element: '{!! $type_element !!}',
	langue: 1,
			ancienne_langue: 1,
			langues_telechargees: [],
			donnees_par_langue: [],
			donnees_par_langue: [],
			donnees_traduites: [],
	profils : {!! collect($profils) !!},
	liste_pour_valeur_par_defaut : {},
	format_affichage_champ: 0,
	aide_parametrage: {
		aide_affichage_recherche : false ,
		aide_affichage_fiche_type : false ,
		aide_affichage_liste : false ,
		aide_affichage_select : false ,
        aide_affichage_extranet : false ,
        aide_affichage_kanban : false ,
		aide_affichage_dedoublonnage : false,
		aide_affichage_planning : false,
		aide_affichage_calendrier : false,
	},
	tooltip_numerotation_automatique: false,
	cle_composant_traduction:0,
	document_gescom: {!! collect(App\Eden\Variables::$documents_gescom) !!},
    type_element_dynamique_a_ajouter: '',
	type_utilisateur: {{ moi()->type_utilisateur }},
	types_elements_existants : {!! \App\Eden\Models\Table_libre::get()->pluck('type_element') !!},
@endsection

@section('donnees_pour_vuejs_methods')

	in_array(needle, haystack) {

		if(haystack == null)
			return;

	    var length = haystack.length;
	    for(var i = 0; i < length; i++) {
	        if(haystack[i] == needle) return true;
	    }
	    return false;
	},

	change_langue: function(langue) {

		this.langue = langue;
		var champs_libres = this.champs_libres;

		$.post({

			url: '{{ URL::to("/eden/recuperer/nom_champ") }}/'+this.type_element+'/'+langue,
			dataType: "json",
			data: {
				type_element: this.type_element,
				langue: langue,
			},
		}).done(function(array_nom) {

			for(var index = 0; index < $(array_nom["liste_nom"]).length; index++){

				if(array_nom["liste_nom"][index] !== null)
					champs_libres[index].nom = array_nom["liste_nom"][index];

				else
					champs_libres[index].nom = "";
			}
		});
	},

	modifier_nom_langue: function(nom_sql,nouveau_nom) {

		var type_element = this.type_element;
		var langue = this.langue;

		$.post({

			url: '{{ URL::to("/eden/changer/nom_champ") }}/'+type_element+'/'+nom_sql +'/'+langue+'/'+nouveau_nom,
			dataType: "json",
			data: {
				type_element: this.type_element,
				langue: langue,
				nom_sql: nom_sql,
				nouveau_nom: nouveau_nom,
			},
		});
	},

	ajouter() {

		this.modal_ajout_element = true;

		this.champ_libre = {

			type: 0,
            type_reference: 0,
			obligatoire: 0,
			unique : 0,
			unique_entite : 0,
			recherche: 0,
			lecture_seule: 0,
			liste_choix: "",
			badge_cliquable : 0,
			contenu : [],
			nom_sql : '',
			format_champ : '',
	        type_fichier: '',
			colonnes_tableau : {},
			nom_colonne : '',
			creation_table_libre_pivot: 0,
		};

		vue_instance.charger_valeur_liste_pour_valeur_par_defaut();
		vue_instance.charger_picker();
		vue_instance.$forceUpdate();

	},
	modifier(event) {

		var event = $(event.target);

		var vue_contexte = this;


		// on récupère les infos du champ libre
		$.ajax({

			url: "{{ URL::to("eden/parametrage/champ_libre/$type_element") }}/"+event.attr('nom_sql'),
			dataType: "json"
		}).done(async (champ_libre) => {

			var valeurs = champ_libre;
			if([4, 5, 8].includes(valeurs.type) && valeurs.valeur_defaut){

				valeurs.valeur_defaut_oui_non = valeurs.type == 8 ? "#maintenant#" : "#aujourdhui#";
				var valeur_defaut_tmp = valeurs.valeur_defaut.replaceAll('#','').split('+');
				var valeur_defaut = [];
				if(valeur_defaut_tmp[1] !== undefined){
					var valeur_defaut = valeur_defaut_tmp[1].split(' ');
				}
				if(valeur_defaut_tmp[2] !== undefined){
					valeur_defaut = valeur_defaut.concat(valeur_defaut_tmp[2].split(' '));
				}
				if(valeur_defaut[2] !== undefined && valeur_defaut[3] !== undefined){
					valeurs.valeur_defaut_ajout_quantite_1 = valeur_defaut[0];
					valeurs.valeur_defaut_ajout_unite_1 = valeur_defaut[1];
					valeurs.valeur_defaut_ajout_quantite_2 = valeur_defaut[2];
					valeurs.valeur_defaut_ajout_unite_2 = valeur_defaut[3];
				}
				else if(valeur_defaut[0] !== undefined && valeur_defaut[1] !== undefined){

					if(['hours','minutes','minutes','personnalise'].includes(valeur_defaut[1])){
						valeurs.valeur_defaut_ajout_quantite_1 = valeur_defaut[0];
						valeurs.valeur_defaut_ajout_unite_1 = valeur_defaut[1];
					}
					else{
						valeurs.valeur_defaut_ajout_quantite_2 = valeur_defaut[0];
						valeurs.valeur_defaut_ajout_unite_2 = valeur_defaut[1];
					}
				}

			} else {
				valeurs.valeur_defaut_oui_non = "";
			}
			vue_contexte.champ_libre = valeurs;

			if(champ_libre.contenu && champ_libre.contenu.length > 0){
				if(champ_libre.contenu[0] == 'icone')
					vue_contexte.format_affichage_champ = 2;
				else
					vue_contexte.format_affichage_champ = 1;
			}
			else
				vue_contexte.format_affichage_champ = 0;

			if((champ_libre.type == 13 || champ_libre.type_reference == 13) && !champ_libre.contenu)
				champ_libre.contenu = ['fa fa-star','#fbd71c','#aaaaaa'];

	 		vue_contexte.charger_valeur_liste_pour_valeur_par_defaut();

			vue_contexte.charger_picker();

			vue_contexte.cle_composant_traduction++;

			this.modal_ajout_element = true;

			await this.chargement_filtres();

			await vue_contexte.$refs.traduction_table !== undefined;

			vue_contexte.$refs.traduction_table.charger_traductions();

		});
	},

	modifier_chaine_affichage() {

		this.modal_chaine_affichage = true;

		this.table_libre_modification = structuredClone(this.table_libre_initiale);
	},

	enregistrer_chaine_affichage() {

		loading(true)

		var data = {
			table_libre : this.table_libre_modification
		}

		// on enregistre les infos des chaines d'affichage
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

			this.modal_chaine_affichage = false;

			this.table_libre_initiale = this.table_libre_modification;

		});
	},

	enregistrer() {

		this.modal_ajout_element = true;

		var vue_contexte = this;

        this.$forceUpdate();

		loading(true);
		
        setTimeout(() => {

            // on enregistre les infos du champ libre
            $.post({

            url: "{{ URL::to("eden/parametrage/champ_libre/$type_element") }}/"+$('input[name=nom_sql]').val()+'/enregistrer',
            dataType: "json",
            data: $('#formulaire_champ_libre').serialize()
            }).done(async (donnees) => {

            	loading(false);

            	if(donnees.retour !== true) {

					await erreur(donnees.retour);
					return;
				}

				vue_contexte.modal_ajout_element = false;

				if(this.champ_libre.creation_table_libre_pivot == 1)
					this.types_elements_existants.push(this.champ_libre.table_pivot);

				vue_contexte.champs_libres = donnees.champs_libres;

				vue_contexte.mise_a_jour_traductions_valeurs();
			});
		}, 2000);
	},

	supprimer() {

		var vue_contexte = this;

		loading(true);

		// on enregistre les infos du champ libre
		$.ajax({

			url: "{{ URL::to("eden/parametrage/champ_libre/$type_element") }}/"+$('input[name=nom_sql]').val()+'/supprimer',
			dataType: "json"
		}).done(async function(donnees) {

			loading(false);

			vue_contexte.champs_libres = donnees.champs_libres;

			if(donnees.erreur){

				await alerte_eden(donnees.erreur);

			}

			vue_contexte.modal_ajout_element = false;
		});

		return false;
	},

	champs_libre_modifier_liste_preenregistree(champ_libre) {

		this.champ_libre_liste = champ_libre;

		var nom_sql = champ_libre.nom_sql;

		var liste_choix = champ_libre.liste_choix;

		this.modal_champ_libre_liste_preenregistree = true;


		loading(true);

		var vue_contexte = this;

		$.ajax({

			url: "{{ URL::to("eden/parametrage/champ_libre/valeurs_liste_preenregistree/") }}/" +this.type_element +'/'+ nom_sql +'/'+ liste_choix,
			dataType: "json"
		}).done(async function(donnees) {

			vue_contexte.liste_libre_preenregistree= donnees.valeurs;

			setTimeout(function() {
				iconpicker(vue_contexte);

				loading(false);
			}, 500);

		});

	},

	enregistrer_liste_libre_preenregistree() {


		loading(true);


		var vue_contexte = this;

		// on enregistre les infos du champ libre
		$.post({

			url: "{{ URL::route('parametrage.champ_libre.valeurs_liste_preenregistree.enregistrer') }}",
			dataType: "json",
			data: {liste : vue_contexte.liste_libre_preenregistree}
		}).done(function(donnees) {

			if(donnees.retour) {

				loading(false);
				vue_contexte.modal_champ_libre_liste_preenregistree = false;

			}
		});

	},

	changement_etat(event,parametre,id_cl) {

		var event = $(event.target);
		var vue_contexte = this;

		// on enregistre les infos du champ libre
		$.post({
			url: "{{ URL::to("eden/parametrage/champ_libre/$type_element/") }}/"+id_cl+'/changement_etat',
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

			vue_contexte.champs_libres = donnees.champs_libres;

		});
	},

	verification_HTML_titre(type) {

		if(type < 0){
			return false;
		}

		return true;
	},

	ajout_colonne(){

		var nom_sql = vue_instance.champ_libre.nom_colonne;

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

		var indicateur = 1;

		while(vue_instance.champ_libre.colonnes_tableau[nom_sql] !== undefined){

			nom_sql = nom_sql.replace('_'+(indicateur-1),'');

			nom_sql += '_'+indicateur;

			indicateur++;

		}

		vue_instance.champ_libre.colonnes_tableau[nom_sql] = vue_instance.champ_libre.nom_colonne;

		vue_instance.$forceUpdate();

	},

	supprimer_colonne(index){

		delete this.champ_libre.colonnes_tableau[index];

		this.$forceUpdate();
	},

	charger_valeur_liste_pour_valeur_par_defaut(){

		var vue_instance = this;

		vue_instance.liste_pour_valeur_par_defaut = {};

		if((this.champ_libre.type == 20 || this.champ_libre.type_reference == 20) && this.champ_libre.liste_choix!=undefined){

			var valeurs = this.valeurs_listes_formatees[vue_instance.champ_libre.liste_choix];

			if(valeurs.standard){

				if(valeurs[this.champ_libre.type_element+'.'+this.champ_libre.nom_sql])
					valeurs = valeurs[this.champ_libre.type_element+'.'+this.champ_libre.nom_sql];
				else
					valeurs = valeurs.standard;
			}

            for(valeur of Object.values(valeurs)){
                vue_instance.$set(vue_instance.liste_pour_valeur_par_defaut,valeur.id_valeur,valeur.valeur);
            }
		}

		else if(this.champ_libre.type == 1 || this.champ_libre.type_reference == 1){

            id_liste_choix = this.champ_libre.liste_choix > 0 ? this.champ_libre.liste_choix : this.champ_libre.id_cl;

            for(index in this.valeurs_listes_libres[id_liste_choix]){

                if(index != 'categorie' && index != 'liaisons'){

                    for(valeur of this.valeurs_listes_libres[id_liste_choix][index]){
                        vue_instance.$set(vue_instance.liste_pour_valeur_par_defaut,valeur.id_valeur,valeur.valeur);
                    }
                }
            }

		}
	},

	charger_picker(){

		var vue_contexte = this;
		setTimeout(function() {
			iconpicker(vue_contexte);
		}, 500);
	},

	verifie_valeur_ajoutee_possible : function(){

		var vue_composant = this;

		if(vue_composant.champ_libre.valeur_defaut_ajout_quantite_1 < 0)
			vue_composant.champ_libre.valeur_defaut_ajout_quantite_1 = 0;
		else if(vue_composant.champ_libre.valeur_defaut_ajout_unite_1 != undefined){

			if(vue_composant.champ_libre.valeur_defaut_ajout_unite_1 == 'hours' && vue_composant.champ_libre.valeur_defaut_ajout_quantite_1 > 24)
				vue_composant.champ_libre.valeur_defaut_ajout_quantite_1 = 24;
			if((vue_composant.champ_libre.valeur_defaut_ajout_unite_1 == 'minutes' || vue_composant.champ_libre.valeur_defaut_ajout_unite_1 == 'seconds') && vue_composant.champ_libre.valeur_defaut_ajout_quantite_1 > 60)
				vue_composant.champ_libre.valeur_defaut_ajout_quantite_1 = 60;

		}

		if(vue_composant.champ_libre.valeur_defaut_ajout_quantite_2 < 0)
			vue_composant.champ_libre.valeur_defaut_ajout_quantite_2 = 0;

	},

    ajouter_type_element_dynamique_possible: function(){

        if(this.type_element_dynamique_a_ajouter == '')
            return;

        if(this.champ_libre.contenu == "" || this.champ_libre.contenu == null)
            this.champ_libre.contenu = [];

        // On vérifie que le type élément n'est pas déjà présent
        var existe_deja = false;
        var vue_instance = this;

        this.champ_libre.contenu.forEach(function(contenu_item){

            if(contenu_item.type_element == vue_instance.type_element_dynamique_a_ajouter)
                existe_deja = true;
        });

        if(existe_deja)
            return;

        var nouveau_type_element = {'type_element': this.type_element_dynamique_a_ajouter, 'valeur': true};

        this.champ_libre.contenu.push(nouveau_type_element);
    },

    supprimer_type_element_dynamique_possible: function(type_element){

        var index_a_splice = null;
        var index_boucle = 0;

        this.champ_libre.contenu.forEach(function(contenu_item){

            if(contenu_item.type_element == type_element)
                index_a_splice = index_boucle;

            index_boucle++;
        });

        if(index_a_splice == null)
            return;

        this.champ_libre.contenu.splice(index_a_splice,1);
    },

	changement_type_champ: function(){

		var vue_instance = this;

        this.champ_libre.type_reference = undefined;

		if(this.champ_libre.type == 21){

			$.post({

				url: '{{URL::to('/eden/parametrage/champ_libre/types_elements_dynamiques')}}',
				dataType: "json"

			}).done(function(types_elements){

				vue_instance.champ_libre.types_elements_a_proposer = types_elements;
				vue_instance.$forceUpdate();
			});
		}
		else if(this.champ_libre.type == 13)
			this.$set(this.champ_libre,'contenu',['fa fa-star','#fbd71c','#aaaaaa']);
	},

	changement_type_reference: function(){

		if(this.champ_libre.type_reference == 13)
			this.$set(this.champ_libre,'contenu',['fa fa-star','#fbd71c','#aaaaaa']);
	},

	creation_table_libre_pivot : async function(table_pivot){

		if(!await confirm_eden())
			return false;

		loading(true);

		$.post({

			url: '{{URL::to('/eden/parametrage/champ_libre/creation_table_libre_pivot')}}',
			dataType: "json",
			data : {
				table_pivot : table_pivot,
			}
		}).done(() => {
			this.types_elements_existants.push(table_pivot);

			loading(false);

			toastr.success('Création de la table libre à partir de la table pivot effectuée avec succés');
		});
	},

	changement_format : function(){

		if(this.champ_libre.format_champ == 'url')
			this.charger_picker();
		else if(['adresse','code_postal'].includes(this.champ_libre.format_champ))
			this.$set(this.champ_libre,'contenu',
				this.champ_libre.format_champ == 'adresse' ?
				{'adresse' : null,'numero_rue' : null,'nom_rue': null,'ville': null,'region': null,'departement': null,'pays': null,'code_postal' : null}
				:
				{region : null,ville:null});
	},
@endsection

@push('donnees_pour_vuejs_computed')

	champs_libres_recherche : function(){

		var vue_instance = this;

		var champs_libres = this.champs_libres;
		var champs_libres_recherche = [];

		var recherche_champs_libres = this.recherche_champs_libres;

		champs_libres.forEach(function(champ_libre){

			var nom = vue_instance.traduction(champ_libre.index_traduction,'nom').toLowerCase();

			if((nom !== null && nom.indexOf(recherche_champs_libres.toLowerCase()) != -1) ||
				champ_libre.nom_sql.indexOf(recherche_champs_libres.toLowerCase()) != -1)
					champs_libres_recherche.push(champ_libre);
		});

		return champs_libres_recherche;
	},

	champs_libres_tries : function(){

		var vue_instance = this;

		var champs_libres = this.champs_libres;
		var champs_libres_tries = [];

		champs_libres.map(function(champ_libre) {
			champ_libre = {
				name : champ_libre.nom + ' (' + champ_libre.nom_sql + ')',
				id: '#'+champ_libre.nom_sql+'#',
			}

			champs_libres_tries.push(champ_libre);
		});

		champs_libres_tries.sort(function (a, b) {
			return a.name.localeCompare(b.name);
		});

		return champs_libres_tries;
	},

@endpush

@push('donnees_pour_vuejs_mounted')

	var vue_contexte = this;

	vue_contexte.gestion_ordre_valeurs_liste_libre();
@endpush

@push('scripts')
    <script>

		function iconpicker(vue_contexte){

			$('.icp-dd').iconpicker({
				defaultValue: false,
				placement: 'bottomLeft',
				hideOnSelect: false,
			});

			$('.icp').on('iconpickerSelected', function (e) {
				if($(this).hasClass('liste_libre_preenregistree')) {
					var id = e.target.id;
					id = id.replace('liste_libre_preenregistree_','');
					vue_contexte.liste_libre_preenregistree[id].icone = e.iconpickerValue;
					vue_contexte.$forceUpdate();
				}
				if($(this).hasClass('liste_libre')) {

					var id = e.target.id;
					id = id.replace('liste_libre_','');
					vue_contexte.liste_libre['valeurs'][id].icone = e.iconpickerValue;
					vue_contexte.$forceUpdate();
				}
				if($(this).hasClass('format_affichage_champ')){
					var id = e.target.id;
					id = id.replace('format_affichage_champ_','');
					vue_contexte.champ_libre.contenu[id] = e.iconpickerValue;
					vue_contexte.$forceUpdate();
				}
				if($(this).hasClass('icone_url')){
					vue_contexte.champ_libre.contenu[0] = e.iconpickerValue;
					vue_contexte.$forceUpdate();

				}
			});
		}

    </script>
@endpush

@push('scripts')
	<script>
		$('.js_focus_input_recherche').focus();
	</script>
@endpush
