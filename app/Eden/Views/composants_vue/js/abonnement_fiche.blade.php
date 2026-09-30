<script>
const abonnement_fiche = Vue.component('abonnement-fiche', {
    template: `<div>
                    <div>
                        <div class="card mb-3">
                            <div class="card-header">
                                <h4>
                                    @traduction('composant.abonnement_fiche.abonnement_a_la_fiche')
                                </h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <template v-for="utilisateur in utilisateurs">
                                        <div class="col-md-2" >
                                            <div class="row" style="margin: 5px;" @click="modifier_abonnement(utilisateur)">
                                                <div class="col-md-12" :style="utilisateur.abonne_fiche ? {'text-align' : 'center','background' : 'var(--background_navbar)','cursor': 'pointer','color': 'white'} : {'text-align' : 'center','background' : 'gainsboro','cursor': 'pointer'}">
                                                    <div class="css_img_utilisateur" style="padding-top:5px">
                                                        <img style="display: block; width: 100%;height: auto;" :src="'storage/'+utilisateur.avatar" v-if="utilisateur.avatar != null">
                                                        <img style="display: block; width: 100%;height: auto;" src="eden/images/no_avatar.jpg" v-if="utilisateur.avatar == null">
                                                    </div>
                                                    @{{ utilisateur.prenom }} @{{ utilisateur.nom }}
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`,
                props:{

                    element_id:0,
                    type_element:0,
                },
                data: function(){

                    return {

                        utilisateurs:{},

                    }

                },
                methods:{

                    charge_donnees: function(){

                        var vue_composant = this;
                        
                        loading(true);
                        
                        $.get({

                            url: 'eden/fiche/' + vue_composant.$root.type_element + '/' + vue_composant.$root.element_id + '/recuperer_utilisateurs_abonnees',
                            dataType: "json"

                        }).done(function(utilisateurs) {
                            
                            // On retire le loader
                            loading(false);
                            
                            vue_composant.utilisateurs = utilisateurs;
                        });

                    },

                    modifier_abonnement: function(utilisateur){

                        var vue_composant = this;

                        loading(true);

                        $.post({

                            url: 'eden/fiche/ajouter_supprimer_abonnement_fiche',
                            dataType: "json",
                            data: {
                                type_element: vue_composant.$root.type_element,
                                element_id: vue_composant.$root.element_id,
                                utilisateur: utilisateur,
                            }

                        }).done(function(utilisateurs) {
                            
                            vue_composant.charge_donnees()
                        });
                    }

                } ,
	
                mounted: function() {
                    
                    this.charge_donnees();
                },

                watch: {

                    // client_id: function(nouvelle_valeur, ancienne_valeur) {

                    //     this.charge_donnees();

                    // }

                },
            });
</script>