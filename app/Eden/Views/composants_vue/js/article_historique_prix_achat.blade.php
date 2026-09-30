const article_historique_prix_achat = Vue.component('article_historique_prix_achat', {
    template: ` <div>

                    <div class="card mb-3">
                        <div class="card-header">
                            <h4>@traduction('composant.article_historique_prix_achat.titre')</h4>
                        </div>
                        <div class="card-body">
                            
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <th>@traduction('composant.article_historique_prix_achat.fournisseur')</th>
                                    <th>@traduction('composant.article_historique_prix_achat.tarif')</th>
                                    <th>@traduction('composant.article_historique_prix_achat.date')</th>
                                    <th>@traduction('composant.article_historique_prix_achat.facture')</th>
                                </thead>
                                <tbody>
                                    
                                    <tr v-for="historique in historique_prix_achat">
                                        <td>
                                            <a class="css__lien" :href="'/eden/fiche/fournisseur/' + historique.fournisseur_id_ligne">
                                                @{{ historique.fournisseur }}
                                            </a>
                                        </td>
                                        <td>
                                            @{{ historique.montant }} {!! maquette('devise_application_symbole') !!}
                                        </td>
                                        <td>
                                            @{{ historique.date }}
                                        </td>
                                        <td>
                                            <a :href="'/eden/document/facture_achat/' + historique.id" class="css__lien">
                                                @{{ historique.ref_facture }}
                                            </a>
                                        </td>
                                    </tr>
                                    
                                </tbody>
                            </table>

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

                        historique_prix_achat:[],

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

                            url: 'eden/fiche/' + vue_composant.type_element + '/' + vue_composant.element_id + '/historique_prix_achat',
                            dataType: "json"
                        }).done(function(historique_prix_achat) {

                            //console.log(historique_prix_achat)
                            
                            // On retire le loader
                            loading(false);
                            
                            vue_composant.historique_prix_achat = historique_prix_achat;
                        });
                    },
                    

                    @yield('donnees_pour_vuejs_methods')
                    @stack('donnees_pour_vuejs_methods')

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