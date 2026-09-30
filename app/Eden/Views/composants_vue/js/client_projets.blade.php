const client_projets = Vue.component('client_projets', {
    template: ` <div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card mb-3">
                                <div class="card-header js_fermeture_bloc">
                                    <h4 class="d-flex align-items-center">
                                        @traduction('composants.client_projets.titre')
                                        <span @click="projet_creer" class="css_ajouter_element ml-auto dropleft " data-toggle="tooltip" data-placement="top" :title="$root.traduction('composants.client_projets.nouveau_projet')">
                                            <i class="css_action_icon secondaire fa fa-fw fa-plus-square"></i>
                                        </span>

                                        <span v-if="afficher_par_defaut && afficher_par_defaut == true" class="ml-2 css_toggle_card_panel" style="float: right; margin-top: 3px; cursor: pointer;">
                                            <span class="fa fa-chevron-up"></span>
                                        </span>

                                        <span v-else-if="afficher_par_defaut && afficher_par_defaut == false" class="ml-2 css_toggle_card_panel" style="float: right; margin-top: 3px; cursor: pointer;">
                                            <span class="fa fa-chevron-down"></span>
                                        </span>

                                    </h4>
                                </div>
                                <div class="card-body" v-if="afficher_par_defaut === true">

                                    <div class="row">
                                        <div class="col-sm-12" v-show="projets.length == 0">
                                            @traduction('composants.client_projets.aucun')
                                        </div>
                                        <div class="col-sm-3" v-for="projet in projets">
                                            <div class="css_fiche_document">
                                                <i class="fa fa-fw fa-folder-open"></i><br/>
                                                <span class="css__lien" @click="projet_modifier(projet.id)">@{{ projet.nom }}</span><br/>
                                                <span>@traduction('composants.client_projets.nom_projet')@{{ projet.id }}<br/></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="modale_liste_projet" v-if="modal_ajout_projet">
                        <transition name="modal">
                            <div class="modal-mask" style="position: fixed;z-index: 1059;" id="modal_ajout_projet">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">@traduction('composants.client_projets.titre_modal')</h5>
                                            <button type="button" class="close" @click="modal_ajout_projet = false" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <input type="hidden" name="id" v-model="projet_id" />

                                        <div class="modal-body">
                                            <formulaire ref="formulaire" nom_formulaire="projet"></formulaire>
                                        </div>
                                        <div class="modal-footer">
                                            <a :href="'eden/fiche/projet/'+projet_id+'/afficher'" class="btn btn-default" v-show="projet_id != ''">@traduction('composants.client_projets.afficher')</a>
                                            <button type="button" class="btn btn-danger" @click="projet_supprimer" v-show="projet_id != ''">@traduction('composants.client_projets.supprimer')</button>
                                            <button type="button" class="btn btn-primary" @click="projet_enregistrer">@traduction('composants.client_projets.enregistrer')</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </transition>
                    </div>
                </div>


                `,
                props:{

                    afficher_par_defaut:"",
                    element_standard:"",

                },
                data: function(){

                    return {

                        projets: {},
                        projet_id: '',
                        projets_pour_affichage: {},
                        modal_ajout_projet : false,

                        @yield('donnees_pour_vuejs_data')
                        @stack('donnees_pour_vuejs_data')

                    }
                },

                computed:{

                    @yield('donnees_pour_vuejs_computed')
                    @stack('donnees_pour_vuejs_computed')

                    element_id: function() {
                        return this.$root.element_id;
                    },

                    type_element: function() {
                        return this.$root.type_element;
                    },

                    adresse_email: function() {
                        return this.$root.client.adresse_email;
                    },

                },

                methods:{

                    <!-- projet creer-->

                    projet_creer: function() {

                        var vue_composant = this;

                        vue_composant.projet_id = '';

                        vue_composant.$once('formulaire_charger',function(){
                            vue_composant.$refs.formulaire.element.client_id = vue_composant.element_id;
                        });

                        vue_composant.modal_ajout_projet = true;
                    },


                    <!-- projet modif-->

                    projet_modifier: function(id) {

                        var vue_composant = this;

                        $.each(vue_composant.projets, function(osef, projet) {

                            if(projet.id == id) {

                                vue_composant.$once('formulaire_charger',function(){
                                    vue_composant.$refs.formulaire.element = projet;
                                });

                                vue_composant.projet_id = projet.id;
                            }
                        });

                        vue_composant.modal_ajout_projet = true;
                    },

                    <!-- projet enregistrer-->

                    projet_enregistrer: async function() {

                        var component = this;

                        // On afficher le loader
                        loading(true);

                        var donnees = await component.$refs.formulaire.enregistrer({
                            client_id : this.element_id
                        });

                        if(donnees.retour === true) {
                            component.modal_ajout_projet = false;
                            component.actualiser();
                        }

                        loading(false);

                    },

                    <!-- projet supprimer-->
                    projet_supprimer: async function() {

                        var vue_composant = this;

                        if(!await confirm_eden(vue_composant.$root.traduction('composants.client_projets.confirmation_suppression')))
                            return false;

                        loading(true);


                        // on fait un appel ajax pour supprimer
                        $.get({

                            url: "eden/element/projet/"+vue_composant.projet_id+"/supprimer",
                            dataType: "json",
                            method: 'GET'
                        }).done(async function(donnees) {

                            if(donnees.retour !== true) {

                                loading(false);

                                await erreur(donnees.retour);
                                return;
                            }

                            vue_composant.modal_ajout_projet = false;

                            // on actualise la liste
                            vue_composant.actualiser();
                        });
                    },


                    // Charge le contenu de l'élément
                    actualiser: function() {

                        var vue_composant = this;

                        loading(true);

                        $.get({

                            url: 'eden/fiche/client/'+vue_composant.element_id+'/projets',
                            dataType: "json"
                        }).done(function(projets) {

                            // On retire le loader
                            loading(false);

                            vue_composant.projets = projets;
                        });

                    },

                    @yield('donnees_pour_vuejs_methods')
                    @stack('donnees_pour_vuejs_methods')

                },

                mounted: function() {

                    $('#stack_modales_composants').append($('#modale_liste_projet'));

                    this.actualiser();

                },

                watch: {
                    element_id: function(nouvelle_valeur, ancienne_valeur) {

                        this.actualiser();

                    },

                }
            });
