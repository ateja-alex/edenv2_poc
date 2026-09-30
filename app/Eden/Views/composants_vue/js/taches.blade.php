const liste_taches = Vue.component('taches', {
    template: ` <div>
                     <div class="row">
                        <div class="col-md-12">
                            <div class="card mb-3">
                                <div class="card-header js_fermeture_bloc">
                                    <h4 class="d-flex align-items-center">
                                        @traduction('composant.taches.titre')
                                        <div class="ml-auto css_ajouter_element">
                                            <i class="css_action_icon secondaire fas fa-plus-square" @click="creer()" data-toggle="tooltip" data-placement="left" :title="$root.traduction('composant.taches.ajouter')"></i>
                                        </div>
                                        <span v-if="afficher_par_defaut == true" class="ml-2 css_toggle_card_panel">
                                            <span class="fa fa-chevron-up"></span>
                                        </span>

                                        <span v-else-if="afficher_par_defaut == false" class="ml-2 css_toggle_card_panel">
                                            <span class="fa fa-chevron-down"></span>
                                        </span>
                                    </h4>
                                </div>

                                <div class="card-body" v-if="afficher_par_defaut === true">
                                    <span class="badge" :class="{'badge-success': mes_taches_seulement === true, 'badge-default': mes_taches_seulement === false}" @click="mes_taches_seulement = !mes_taches_seulement">
                                        @traduction('composant.taches.seulement_mes_taches')
                                    </span>

                                    <span class="badge" :class="{'badge-success': taches_en_cours_seulement === true, 'badge-default': taches_en_cours_seulement === false}" @click="taches_en_cours_seulement = !taches_en_cours_seulement">
                                        @traduction('composant.taches.taches_en_cours_seulement')
                                    </span>

                                    <br/>
                                    <br/>

                                    <div class="row">

                                        <div class="col-sm-12" v-if="Object.keys(taches).length == 0">
                                            @traduction('composant.taches.aucune_tache_1')
                                            <span v-text="this.$root.type_element"></span>
                                            @traduction('composant.taches.aucune_tache_2')
                                        </div>

                                        <div class="col-sm-12" v-for="tache in taches" v-show="(mes_taches_seulement === false || tache.affectation == $root.moi.id) && (tache.terminee != 1 || taches_en_cours_seulement === false)">
                                            <div class="css_fiche_tache">
                                                <i class="fa fa-fw fa-check" :style="tache.terminee != 1 ? 'color: #aaa;' : 'color: #3d9c18;'" v-show="tache.terminee != 1"></i>
                                                <span @click="terminee(tache.id,1)"  style="cursor: pointer;">
                                                    <template v-if="tache.terminee != 1">@{{ tache.titre }}</template>
                                                    <s v-else>@{{ tache.titre }}</s>
                                                </span>

                                                <span class="badge badge-default" @click="modifier(tache)" style="float: right"><span class="fa fa-search"></span></span>

                                                <span class="badge badge-success" style="float: right" v-if="tache.affectation == $root.moi.id">
                                                    @traduction('composant.taches.pour_moi')
                                                </span>
                                                <span class="badge" style="float: right" v-else>
                                                    @{{ tache.affectation | affiche_utilisateur }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>


                    <!-- Modal ajout tache -->
                    <div id="modales_composant_tache">
                        <template v-if="modale_ajout_tache">
                            <transition name="modal" >
                                <div class="modal-mask">
                                    <div class="modal-dialog modal-lg" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">@traduction('composant.taches.titre_modal_ajout')</h5>
                                                <button type="button" class="close" @click="modale_ajout_tache = false" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>

                                            <div class="modal-body">
                                                <formulaire ref="formulaire" nom_formulaire="tache" ></formulaire>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-success" @click="terminee(tache.id,1)" v-if="tache.id > 0 && tache.terminee != 1">@traduction('composant.taches.terminee')</button>

                                                <button type="button" class="btn btn-warning" @click="terminee(tache.id,0)" v-if="tache.id > 0 && tache.terminee == 1">@traduction('composant.taches.annuler_terminee')</button>

                                                <button type="button" class="btn btn-danger" @click="supprimer" v-if="tache.id > 0">@traduction('composant.taches.supprimer')</button>

                                                <button type="button" class="btn btn-primary" @click="enregistrer">@traduction('composant.taches.enregistrer')</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </transition>
                        </template>
                    </div>

                </div>


                `,
                props:{

                    afficher_par_defaut:"",
                    type_element : '',
                    element_id : 0,

                },
                data: function(){

                    return {

                        taches:{},
                        taches_multiples: [],
                        taches_en_cours_seulement: false,
                        mes_taches_seulement: false,
                        modale_ajout_tache: false,
                        cle_formulaire: 0,

                        @yield('donnees_pour_vuejs_data')
                        @stack('donnees_pour_vuejs_data')

                    }

                },

                computed:{

                    @yield('donnees_pour_vuejs_computed')
                    @stack('donnees_pour_vuejs_computed')

                    tache: function() {

                        this.cle_formulaire;

                        if(!this.$refs.formulaire)
                            return {};

                        return this.$refs.formulaire.element;
                    },

                },

                methods:{

                    // Charge la liste des tâches associées à l'élément
                    actualiser: function() {

                        loading(true);

                        $.get({

                            url: 'eden/fiche/' + this.type_element + '/' + this.element_id + '/taches',
                            dataType: "json"
                        }).done((donnees) => {

                            // On retire le loader
                            loading(false);

                            this.taches = donnees.taches;

                        });
                    },

                    modifier: function(tache) {

                        this.$once('formulaire_charger',() => {
                            this.$refs.formulaire.element = tache;
                            this.cle_formulaire++;
                        });

                        this.modale_ajout_tache = true;
                    },

                    creer: function() {

                        this.$once('formulaire_charger',() => {

                            if(this.type_element == 'client')
                                this.$refs.formulaire.element[this.type_element+'_id'] = this.element_id;

                            this.cle_formulaire++;
                        });

                        this.modale_ajout_tache = true;
                    },

                    enregistrer: async function() {

                        // On afficher le loader
                        loading(true);

                        var donnees = await this.$refs.formulaire.enregistrer({type_element: this.type_element,element_id: this.element_id});

                        loading(false);

                        this.actualiser();

                        this.modale_ajout_tache = false;

                    },

                    supprimer: async function() {

                        if(!await confirm_eden(this.$root.traduction('composant.taches.confirmation_suppression')))
                            return false;

                        loading(true);

                        // on fait un appel ajax pour supprimer
                        $.get({

                            url: "eden/element/tache/"+this.tache.id+"/supprimer",
                            dataType: "json",
                            method: 'GET'
                        }).done(async (donnees) => {

                            if(donnees.retour !== true) {

                                loading(false);

                                await erreur(donnees.retour);
                                return;
                            }

                            this.modale_ajout_tache = false;

                            // on actualise la liste
                            this.actualiser();
                        });
                    },

                    terminee: function(tache_id,statut_terminee) {

                        loading(true);

                        // on enregistre la modification
                        $.post({

                            url: "eden/element/tache/"+tache_id+"/enregistrer",
                            dataType: "json",
                            method: 'POST',
                            data: {
                                terminee: statut_terminee
                            }
                        }).done((donnees) => {

                            // On retire le loader
                            loading(false);

                            this.modale_ajout_tache = false;

                            this.actualiser();
                        });

                    },

                    @yield('donnees_pour_vuejs_methods')
                    @stack('donnees_pour_vuejs_methods')

                },

                mounted: function() {

                    $('#stack_modales_composants').append($('#modales_composant_tache'));

                    this.actualiser();
                },

                watch: {
                    element_id: function(nouvelle_valeur, ancienne_valeur) {

                        this.actualiser();

                    }

                }
});
