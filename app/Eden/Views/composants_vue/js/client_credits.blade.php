const client_credits = Vue.component('client_credits', {
    template: ` <div>

                    <div class="card mb-3">
                        <div class="card-header js_fermeture_bloc">

                            <span v-if="afficher_par_defaut && afficher_par_defaut == true" class="css_toggle_card_panel">
                                <span class="fa fa-chevron-up"></span>
                            </span>

                            <span v-else-if="afficher_par_defaut && afficher_par_defaut == false" class="css_toggle_card_panel">
                                <span class="fa fa-chevron-down"></span>
                            </span>

                            <h4>
                                @traduction('composants.client_credits.titre')

                                <span class="css_indicateur">
                                    <span>
                                        @traduction('composants.client_credits.solde') :
                                    </span> @{{ solde_credit }}
                                </span>

                                <span class="css__lien" style="margin-left: 50px;" @click="modale_ajouter_credit_au_client()">
                                    @traduction('composants.client_credits.ajouter')
                                </span>
                            </h4>
                        </div>
                        <div class="card-body" v-if="afficher_par_defaut === true">

                            <div class="row">
                                <div class="col-sm-2">
                                    <b>@traduction('composants.client_credits.date')</b>
                                </div>
                                <div class="col-sm-6">
                                    <b>@traduction('composants.client_credits.origine')</b>
                                </div>
                                <div class="col-sm-2">
                                    <b>@traduction('composants.client_credits.nombre')</b>
                                </div>
                                <div class="col-sm-2">
                                    <b>@traduction('composants.client_credits.options')</b>
                                </div>
                            </div>
                            <div class="row" v-for="(credit, index) in credits">
                                <div class="col-sm-2">
                                    @{{ credit.date | date }}
                                </div>
                                <div class="col-sm-6">
                                    <span v-show="credit.element_origine !== false">
                                        <span v-html="credit.element_origine"></span>
                                        (@{{ credit.origine }})
                                    </span>
                                    <span v-show="credit.element_origine === false">@{{ credit.origine }}</span>
                                </div>
                                <div class="col-sm-2" v-html="$options.filters.nombre_couleur(credit.nombre)"></div>
                                <div class="col-sm-2">
                                    <span class="css__lien" @click="supprimer_credit_du_client(credit, index)">@traduction('composants.client_credits.supprimer')</span>
                                </div>
                            </div>


                        </div>
                    </div>

                    <div id="modale_client_credit">

                        <!-- modale pour ajouter des crédits -->
                        <template v-if="modal_ajout_credit_au_client">
                            <transition name="modal">
                                <div class="modal-mask" style="position: fixed;z-index: 1059;" >
                                    <div class="modal-dialog modal-lg" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">@traduction('composants.client_credits.titre_modal')</h5>
                                                <button type="button" class="close" @click="modal_ajout_credit_au_client = false" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>

                                            <div class="modal-body">
                                                <formulaire ref="formulaire" nom_formulaire="credit"></formulaire>
                                            </div>

                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-primary" @click="ajouter_credit_au_client">@traduction('composants.client_credits.enregistrer')</button>
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
                    element_standard:"",

                },
                data: function(){

                    return {

                        credits: {},
                        solde_credit: 0,
                        modal_ajout_credit_au_client: false,

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

                },

                filters: {

                    date: function (value) {

                        if (!value)
                            return '';

                        return moment(String(value)).format('DD/MM/YYYY')

                    },

                    nombre_couleur: function (nombre) {

                        if (!nombre)
                            return '';

                        if(nombre >= 0) {

                            return '<span style="color: #669e24;">'+nombre+'</span>';
                        }
                        else {

                            return '<span style="color: #ed6f56;">'+nombre+'</span>';
                        }
                    },
                },

                methods:{

                    supprimer_credit_du_client: function(credit, index) {

                        loading(true);
                        var vue_composant = this;

                        $.get({

                            url: 'eden/element/credit/'+credit.id+'/supprimer',
                            dataType: "json",
                        }).done(async function(donnees) {

                            // On retire le loader
                            loading(false);

                            if(donnees.retour !== true) {

                                await erreur(donnees.retour);
                                return;
                            }

                            // on reload la liste des credits
                            vue_composant.actualiser();
                        });
                    },

                    modale_ajouter_credit_au_client: function() {

                        var instance = this;

                        instance.$once('formulaire_charger',function(){
                            instance.$refs.formulaire.element.client_id = instance.element_id;
                        });

                        instance.modal_ajout_credit_au_client = true;
                    },

                    ajouter_credit_au_client: async function() {

                        var component = this;

                        // On afficher le loader
                        loading(true);

                        var donnees = await component.$refs.formulaire.enregistrer();

                        if(donnees.retour === true) {
                            component.actualiser();
                            component.modal_ajout_credit_au_client = false;
                        }

                        loading(false);
                    },

                    // Charge le contenu de l'élément
                    actualiser: function() {

                        var vue_composant = this;

                        loading(true);

                        $.get({

                            url: "/eden/fiche/client/"+vue_composant.element_id+"/credits",
                            dataType: "json"
                        }).done(function(info_credits) {

                            // On retire le loader
                            loading(false);

                            vue_composant.credits = info_credits.credits;
                            vue_composant.solde_credit = info_credits.indicateurs.nombre_credits;

                        });
                    },

                    @yield('donnees_pour_vuejs_methods')
                    @stack('donnees_pour_vuejs_methods')

                },

                mounted: function() {

                    $('#stack_modales_composants').append($('#modale_client_credit'));

                    this.actualiser();

                },

                watch: {
                    element_id: function(nouvelle_valeur, ancienne_valeur) {

                        this.actualiser();

                    }

                }
            });
