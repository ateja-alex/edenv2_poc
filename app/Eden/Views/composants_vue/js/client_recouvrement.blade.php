const client_recouvrement = Vue.component('client_recouvrement', {
    template: ` <div>
                    
                    <div>
                        <div class="card mb-3">
                            <div class="card-header">
                                <h4>
                                    @traduction('composants.client_recouvrement.titre')

                                    <span class="css_indicateur">@{{ $options.filters.montant(solde_encours) }} {{ config('maquette.devise_application_symbole') }}<br>
                                        @traduction('composants.client_recouvrement.encours')
                                    </span>
                                </h4>
                            </div>
                            <div class="card-body">
                                
                                <div class="row">
                                    <div class="col-sm-3">
                                        <b>@traduction('composants.client_recouvrement.date')</b>
                                    </div>
                                    <div class="col-sm-3">
                                        <b>@traduction('composants.client_recouvrement.relance')</b>
                                    </div>
                                    <div class="col-sm-3">
                                        <b>@traduction('composants.client_recouvrement.document')</b>
                                    </div>
                                    <div class="col-sm-3">
                                        <b>@traduction('composants.client_recouvrement.commentaire')</b>
                                    </div>
                                </div>

                                <div class="row" v-for="relance in relances">
                                    <div class="col-sm-3">
                                        @{{ relance.date | date }}
                                    </div>
                                    <div class="col-sm-3" v-text="relance.type"></div>
                                    <div class="col-sm-3" v-html="relance.facture"></div>
                                    <div class="col-sm-3"></div>
                                </div>

                                <div class="row" v-for="commentaire in commentaires_relance">
                                    <div class="col-sm-3">-</div>
                                    <div class="col-sm-3">@traduction('composants.client_recouvrement.commentaire')</div>
                                    <div class="col-sm-3" v-html="commentaire.facture"></div>
                                    <div class="col-sm-3" v-text="commentaire.commentaires_recouvrement"></div>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>


                `,
                props:{

                    afficher_par_defaut:"",
                    element_standard:"",

                },
                data: function(){

                    return {

                        relances: {},
                        solde_encours:0,
                        commentaires_relance: {},

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

                methods:{

                    // Charge le contenu de l'élément
                    actualiser: function() {

                        var vue_composant = this;
                        
                        loading(true);
                        
                        $.get({

                            url: "/eden/fiche/client/"+vue_composant.element_id+"/recupere_recouvrement",
                            dataType: "json"
                        }).done(function(retour) {

                            //console.log(retour)
                            
                            // On retire le loader
                            loading(false);
                            
                            vue_composant.solde_encours = retour.solde_encours;
                            vue_composant.relances = retour.relances;
                            vue_composant.commentaires_relance = retour.commentaires_relance;

                        });
                    },

                    @yield('donnees_pour_vuejs_methods')
                    @stack('donnees_pour_vuejs_methods')

                },  

                filters: {

                    montant: function (nombre) {

                        if (nombre === null || nombre == undefined || nombre == '')
                            return '0,00';

                        return parseFloat(nombre).toFixed(2).replace('.', ',');
                    },

                },
    
                mounted: function() {

                    this.actualiser();

                },

                watch: {
                    element_id: function(nouvelle_valeur, ancienne_valeur) {

                        this.actualiser();

                    }

                }
            });