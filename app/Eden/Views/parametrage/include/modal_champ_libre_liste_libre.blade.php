<template v-if="modal_liste_libre">
    <transition name="modal">
        <div class="modal-mask parametrage_liste_libre">
            <div class="modal-dialog modal-xl" role="document">
                <div class="sections_modales">
                    <div class="section_modale">
                        <span :class="(informations_modale_liste_libre.onglet == 'valeurs' ? 'section_active' : '')+' nom_section'"
                            @click="informations_modale_liste_libre.onglet = 'valeurs'">
                            Valeurs
                        </span>
                        <div class="contenu_section modal-content" v-show="informations_modale_liste_libre.onglet == 'valeurs'">
                            <div class="modal-header">
                                <h5 class="modal-title">Gestion des listes</h5>
                                <button type="button" class="close" @click="modal_liste_libre = false" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>

                            <div class="modal-body">
                                <form action="#" method="post" class="css_form" id="formulaire_champ_libre_liste_libre">
                                    {{ csrf_field() }}
                                    <div class="row" style="font-weight: bold;">
                                        <div class="col-sm-1" style="padding-left: 40px;">#</div>
                                        <div class="col-sm-1">Background</div>
                                        <div class="col-sm-1">Police</div>
                                        <div class="col-sm-3">Valeur</div>
                                        <div class="col-sm-3">Catégorie</div>
                                        <div class="col-sm-1">Aperçu</div>
                                        <div class="col-sm-1">Icone</div>
                                        <div class="col-sm-1">Options</div>
                                    </div>
                                    <div class="js_parametrage_valeur_liste_libre">
                                        <div class="row" v-for="(valeur_liste_libre,index) in liste_libre.valeurs" :key="index" :ordre="valeur_liste_libre.ordre">
                                            <div class="col-sm-1">
                                                <i class="fas fa-align-justify" style="margin-right:10px"></i>
                                                @{{ valeur_liste_libre.id_valeur }}
                                            </div>
                                            <div class="col-sm-1" style="display: inline-flex">
                                                <i style="position: relative;top: 7px;margin-right: 7px;" class="fas fa-brush" ></i>
                                                <input type="color" style="width:40px;" v-model="valeur_liste_libre.couleur_fond" />
                                            </div>
                                            <div class="col-sm-1" style="display: inline-flex">
                                                <i style="position: relative;top: 7px;margin-right: 7px;" class="fas fa-pen-nib" ></i>
                                                <input type="color" style="width:40px;" v-model="valeur_liste_libre.couleur_police"/>
                                            </div>
                                            <div class="col-sm-3" v-if="valeur_liste_libre.nouvelle_valeur === true">
                                                <input type="text" v-model="valeur_liste_libre.valeur"/>
                                            </div>
                                            <div class="col-sm-3" v-else>
                                                <traduction-element :index_traduction="valeur_liste_libre.index_traduction" champ="nom"></traduction-element>
                                            </div>
                                            <div class="col-sm-3" v-if="valeur_liste_libre.nouvelle_valeur === true">
                                                <input type="text" v-model="valeur_liste_libre.categorie"/>
                                            </div>
                                            <div class="col-sm-3" v-else>
                                                <traduction-element :index_traduction="valeur_liste_libre.index_traduction" champ="categorie"></traduction-element>
                                            </div>
                                            <div class="col-sm-1">
                                                <span class="badge badge-default" v-if="valeur_liste_libre.nouvelle_valeur === true" :style="'background: '+valeur_liste_libre.couleur_fond+';color: '+valeur_liste_libre.couleur_police">@{{valeur_liste_libre.valeur}}</span>
                                                <span class="badge badge-default" v-else :style="'background: '+valeur_liste_libre.couleur_fond+';color: '+valeur_liste_libre.couleur_police">@{{traduction(valeur_liste_libre.index_traduction,'nom')}}</span> x@{{valeur_liste_libre.utilisations}}
                                            </div>
                                            <div class="col-sm-1">
                                                <button type="button" class="btn btn-primary iconpicker-component" style="position:relative;">
                                                    <i :class="valeur_liste_libre.icone"></i>
                                                    <span v-if="valeur_liste_libre.icone" @click="valeur_liste_libre.icone = ''" class="fa fa-times btn-primary" style="position:absolute;border-radius:10px;top:-10px;right:-10px;padding: 2px 5px;"></span>
                                                </button>
                                                <button type="button" class="icp icp-dd btn btn-primary dropdown-toggle liste_libre" data-selected="fa-car" data-toggle="dropdown" :id="'liste_libre_'+index" >
                                                    <span class="caret"></span>
                                                    <span class="sr-only">Icone</span>
                                                </button>
                                                <div class="dropdown-menu"></div>
                                            </div>
                                            <div class="col-sm-1">
                                                <span class="css_ajouter_element mr-3" data-toggle="tooltip" data-placement="top" title="Supprimer la valeur" @click="supprimer_valeur_liste(index)">
                                                    <i class="css_action_icon fas fa-trash"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row" v-show="liste_libre.length == 0">
                                        <div class="col-sm-6">Aucune valeur</div>
                                    </div>
                                    <div class="row" >
                                        <div class="col-sm-2">Ajouter :</div>
                                        <div class="col-sm-6">
                                            <input type="text" v-model="nouvelle_valeur" />
                                        </div>
                                        <div class="col-sm-1">
                                            <span class="css_ajouter_element mr-3" data-toggle="tooltip" data-placement="top" title="Ajouter la valeur" @click="ajouter_valeur_liste_libre">
                                                <i class="css_action_icon fas fa-plus"></i>
                                            </span>
                                        </div>
                                    </div>

                                </form>

                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" @click="modal_liste_libre = false">Fermer</button>
                                <a type="button" class="btn btn-success" :href="'eden/parametrage/traduction?categorie=8&recherche='+index_traduction_valeurs_liste" target="_blank">Traduire en masse</a>
                                @if(empty($vue_sql))
									<button type="button" class="btn btn-primary" @click="enregistrer_valeurs_liste_libre">Enregistrer les valeurs</button>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="section_modale">
                        <span :class="(informations_modale_liste_libre.onglet == 'liaison_valeurs' ? 'section_active' : '')+' nom_section'"
                             @click="informations_modale_liste_libre.onglet = 'liaison_valeurs';recuperation_valeurs_liaisons()">
                          Liaisons des valeurs
                        </span>

                        <div class="contenu_section modal-content" v-show="informations_modale_liste_libre.onglet == 'liaison_valeurs'">
                            <div class="modal-header">
                                <h5 class="modal-title">Gestion des liaisons</h5>
                                <button type="button" class="close" @click="modal_liste_libre = false" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>

                            <div class="modal-body">
                                <form action="#" method="post" class="css_form">
                                    <div class="row">
                                        <div class="col-sm-2">
                                            Champ liste libre parent
                                        </div>
                                        <div class="col-sm-10" style="display:inline-flex">
                                            <select style="width: unset;" :disabled="selection_liaison_effectue" @change="recuperation_valeurs_liaisons" v-model="champ_libre_liste.champ_liste_libre_parent">
                                                <option :value="champ_libre_disponible.nom_sql" v-for="champ_libre_disponible in champs_libres_liaisons_disponible" v-html="traduction(champ_libre_disponible.index_traduction,'nom')+' ('+champ_libre_disponible.nom_sql+')'"></option>
                                            </select>
                                            <span class="bouton_suppression_liaison" @click="suppression_liaisons" v-if="champ_libre_liste.champ_liste_libre_parent != null">
                                                <i class="fas fa-times"></i>
                                                Supprimer la liaison
                                            </span>
                                        </div>
                                    </div>
                                    <div class="row" v-if="champ_libre_liste.champ_liste_libre_parent !== null">
                                        <div class="col-sm-12">
                                            <table class="table table-bordered">
                                                <thead>
                                                    <th>Valeurs</th>
                                                    <th style="width: 75%;">Liaisons</th>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="valeur in valeurs_pour_liaisons">
                                                        <td>@{{ traduction(valeur.index_traduction,'nom') }}</td>
                                                        <td style="display: flex;flex-wrap: wrap;gap: 5px;align-items: center;">
                                                            <select @change="ajout_liaison($event,valeur)" style="width: 260px;">
                                                                <option value="">Ajouter une des valeurs suivantes :</option>
                                                                <option v-if="champ_libre_liste.champ_liste_libre_liaisons[valeur.index_traduction] == undefined || !champ_libre_liste.champ_liste_libre_liaisons[valeur.index_traduction].includes(valeur_liaison.index_traduction)" :value="valeur_liaison.index_traduction" v-for="valeur_liaison in valeurs_liste_libre_liaison">
                                                                    @{{ traduction(valeur_liaison.index_traduction,'nom') }}
                                                                </option>
                                                            </select>
                                                            <span class="css_lien_selection_element" v-for="(valeur_selectionne,index_valeur_selectionne) in champ_libre_liste.champ_liste_libre_liaisons[valeur.index_traduction]">
                                                                @if(empty($vue_sql))
                                                                    <span @click="suppression_liaison(champ_libre_liste.champ_liste_libre_liaisons[valeur.index_traduction],index_valeur_selectionne)" style="padding-right: 10px;border-right: 1px solid #999;color: #999;cursor: pointer;">
                                                                        <i class="fas fa-times"></i>
                                                                    </span>
                                                                @endif
                                                                <span style="margin-left: 5px;" v-html="traduction(valeur_selectionne,'nom')">
                                                                </span>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" @click="modal_liste_libre = false">Fermer</button>
                                @if(empty($vue_sql))
                                    <button type="button" class="btn btn-primary" v-if="champ_libre_liste.champ_liste_libre_parent != null" @click="enregistrer_liaisons" >Enregistrer les liaisons</button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>

@push('donnees_pour_vuejs_data')

    modal_liste_libre: false,
    liste_libre: [],
    nouvelle_valeur: '',
    affichage_liaison: false,
    champ_libre_liste: {},
    informations_modale_liste_libre: {
        onglet : 'valeurs',
    },
    valeurs_liste_libre_liaison: {},
    cle_rechargement : 0,

@endpush

@push('donnees_pour_vuejs_methods')

	supprimer_valeur_liste(index) {

		var vue_instance = this;
		var ordre_valeur_supprimer = vue_instance.liste_libre.valeurs[index].ordre;

		$.each(vue_instance.liste_libre.valeurs,function(index,liste_libre_valeur){
			if(liste_libre_valeur.ordre > ordre_valeur_supprimer)
				liste_libre_valeur.ordre--;
		});

		vue_instance.liste_libre.valeurs.splice(index,1);

		vue_instance.$forceUpdate();

	},
	
	gestion_ordre_valeurs_liste_libre(){

		$('.js_parametrage_valeur_liste_libre').sortable({

			update: function( event, ui ) {

				var position=$(ui.position.top);
				var position_origine=$(ui.originalPosition.top);
				var difference=position_origine[0]-position[0];

				var ancienne_position = $(ui.item).attr('ordre');
				var nouvelle_position;

				if(difference>0){

					nouvelle_position = $(ui.item).next().attr('ordre');

					$.each(vue_instance.liste_libre.valeurs,function(index,valeur_liste_libre){

						if(valeur_liste_libre.ordre < ancienne_position && valeur_liste_libre.ordre >= nouvelle_position){
							valeur_liste_libre.ordre++;
						}

						else if(valeur_liste_libre.ordre == ancienne_position)
							valeur_liste_libre.ordre = parseInt(nouvelle_position);
					});

				}
				else{

					nouvelle_position = $(ui.item).prev().attr('ordre');

					$.each(vue_instance.liste_libre.valeurs,function(index,valeur_liste_libre){

						if(valeur_liste_libre.ordre > ancienne_position && valeur_liste_libre.ordre <= nouvelle_position){
							valeur_liste_libre.ordre--;
						}

						else if(valeur_liste_libre.ordre == ancienne_position)
							valeur_liste_libre.ordre = parseInt(nouvelle_position);
					});

				}

				vue_instance.$forceUpdate();
			}

		});

	},
	
	champs_libre_modifier_liste_libre(champ_libre) {

        var vue_contexte = this;

		vue_contexte.champ_libre_liste = champ_libre;

		loading(true);

		vue_contexte.modal_liste_libre = true;

		vue_contexte.liste_libre = [];
		// on enregistre les infos du champ libre
		$.ajax({

			url: "{{ URL::to("eden/parametrage/champ_libre/valeurs_liste/") }}/"+champ_libre.id_cl,
			dataType: "json"
		}).done(function(donnees) {

			setTimeout(function() {
				iconpicker(vue_contexte);

				loading(false);
			}, 500);

            vue_contexte.informations_modale_liste_libre.onglet = 'valeurs';

			vue_contexte.gestion_ordre_valeurs_liste_libre();

			vue_contexte.liste_libre = donnees;

			vue_contexte.$forceUpdate();

			loading(false);

		});

		return true;

	},

    recuperation_valeurs_liaisons : function(){

        var vue_contexte = this;

        if(vue_contexte.champ_liste_libre_parent == null)
            return;

        loading(true);

        // on enregistre les infos du champ libre
		$.ajax({

			url: "{{ URL::to("eden/parametrage/champ_libre/valeurs_liste/") }}/"+vue_contexte.champ_liste_libre_parent.id_cl,
			dataType: "json"
		}).done(function(donnees) {

            vue_contexte.valeurs_liste_libre_liaison = donnees.valeurs;

			vue_contexte.$forceUpdate();

            loading(false);
        });
    },

    enregistrer_valeurs_liste_libre(){

		loading(true);

		var vue_contexte = this;

		// on enregistre les infos du champ libre
		$.post({

			url: "{{ URL::to("eden/parametrage/champ_libre/valeurs_liste") }}/"+vue_contexte.champ_libre_liste.id_cl+'/enregistrer',
			dataType: "json",
			data: {
				valeurs : JSON.stringify(vue_contexte.liste_libre.valeurs),
			},
		}).done(async function(donnees) {

			loading(false);

			if(donnees.erreur) {
				await alerte_eden(donnees.erreur);
				return;
			}

			$.each(vue_contexte.liste_libre.valeurs,function(index,liste_libre_valeur){

				if(donnees.nouveaux_id_valeur[liste_libre_valeur.ordre] != undefined){

					liste_libre_valeur.id_valeur = donnees.nouveaux_id_valeur[liste_libre_valeur.ordre].id_valeur;
					liste_libre_valeur.index_traduction = donnees.nouveaux_id_valeur[liste_libre_valeur.ordre].index_traduction;
					liste_libre_valeur.nouvelle_valeur = false;
				}

			});

			vue_contexte.mise_a_jour_traductions_valeurs();

			vue_contexte.$forceUpdate();

			info('Enregistrement effectué avec succés');
		});

	},

    ajouter_valeur_liste_libre(){

		var vue_instance = this;

		vue_instance.liste_libre.valeurs.push({
			id_cl:vue_instance.champ_libre_liste.id_cl,
			categorie:'',
			couleur:null,
			couleur_fond:'',
			couleur_police:'',
			ordre:(vue_instance.liste_libre.valeurs.length + 1),
			valeur: vue_instance.nouvelle_valeur,
			nouvelle_valeur: true,
		});

		vue_instance.nouvelle_valeur = '';

		vue_instance.charger_picker();
	},

    ajout_liaison: function(event,valeur){

        var valeur_liaison = $(event.target).val();

        if(this.champ_libre_liste.champ_liste_libre_liaisons[valeur.index_traduction] == undefined)
            this.champ_libre_liste.champ_liste_libre_liaisons[valeur.index_traduction] = [];

        this.champ_libre_liste.champ_liste_libre_liaisons[valeur.index_traduction].push(valeur_liaison);

        $(event.target).val('');

        this.$forceUpdate();
        this.cle_rechargement++;
    },

    suppression_liaison: function(liaisons,index_valeur_selectionne){

        liaisons.splice(index_valeur_selectionne,1);
        this.$forceUpdate();
        this.cle_rechargement++;
    },

    enregistrer_liaisons : function(){

        loading(true);

		var vue_contexte = this;

		// on enregistre les infos du champ libre
		$.post({

			url: "{{ URL::to("eden/parametrage/champ_libre/valeurs_liste/enregistrer_liaisons") }}",
			dataType: "json",
			data: vue_contexte.champ_libre_liste,
		}).done(async function(donnees) {

			loading(false);

			if(donnees.erreur) {

                await alerte_eden(donnees.erreur);
				return;
			}

			vue_contexte.$forceUpdate();

			info('Enregistrement effectué avec succés');
		});
    },

    suppression_liaisons: async function(){

        if(!await confirm_eden())
            return;

        loading(true);

		var vue_contexte = this;

        vue_contexte.champ_libre_liste.champ_liste_libre_parent = null;
        vue_contexte.champ_libre_liste.champ_liste_libre_liaisons = {};

		// on enregistre les infos du champ libre
		$.post({

			url: "{{ URL::to("eden/parametrage/champ_libre/valeurs_liste/supprimer_liaisons") }}",
			dataType: "json",
			data: vue_contexte.champ_libre_liste,
		}).done(async function(donnees) {

			loading(false);

			if(donnees.erreur) {
				await alert(donnees.erreur);
				return;
			}

			vue_contexte.$forceUpdate();

			info('Suppression effectué avec succés');
		});
    },
@endpush

@push('donnees_pour_vuejs_computed')

	index_traduction_valeurs_liste:function(){

		var vue_instance = this;

		var index_traduction_valeurs_liste = null;

		if(vue_instance.liste_libre.valeurs == undefined)
			return '';

		vue_instance.liste_libre.valeurs.forEach(function(valeur){
			if(index_traduction_valeurs_liste == null && valeur.index_traduction != undefined && valeur.index_traduction != null){
				index_traduction_valeurs_liste = valeur.index_traduction;
				index_traduction_valeurs_liste = index_traduction_valeurs_liste.split('.');

				index_traduction_valeurs_liste.splice(3,2);

				index_traduction_valeurs_liste = index_traduction_valeurs_liste.join('.');
			}
		});

		return index_traduction_valeurs_liste;

	},

    champs_libres_liaisons_disponible:function(){

        var vue_instance = this;

        if(Object.keys(vue_instance.champ_libre_liste).length == 0)
            return [];

        var champs_libres_liaisons_disponible = [];

        vue_instance.champs_libres.forEach(function(champ_libre){
            if((champ_libre.type == 1 || champ_libre.type == 12) && champ_libre.nom_sql != vue_instance.champ_libre_liste.nom_sql)
                champs_libres_liaisons_disponible.push(champ_libre);
        });

        return champs_libres_liaisons_disponible;
    },

    champ_liste_libre_parent:function(){

        var vue_contexte = this;

        if(vue_contexte.champ_libre_liste.champ_liste_libre_parent == null)
            return null;

        var champ_liste_libre_parent = null;

        $.each(vue_contexte.champs_libres,function(index,champ_libre){
            if(champ_libre.nom_sql == vue_contexte.champ_libre_liste.champ_liste_libre_parent)
                champ_liste_libre_parent = champ_libre;
        });

        return champ_liste_libre_parent;
    },

    selection_liaison_effectue : function(){

        var vue_contexte = this;

        vue_contexte.cle_rechargement;

        var selection_liaison_effectue = false;

        $.each(vue_contexte.champ_libre_liste.champ_liste_libre_liaisons,function(index,valeurs){

            if(selection_liaison_effectue === false && valeurs.length > 0)
                selection_liaison_effectue = true;
        });

        return selection_liaison_effectue;
    },

    valeurs_pour_liaisons : function(){

        if(!this.liste_libre.valeurs)
            return [];
        
        return this.liste_libre.valeurs.filter((valeur) => !valeur.nouvelle_valeur);
    },
@endpush
