<script>
const liste_adresses = Vue.component('liste-adresses', {
    template: `<div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card mb-3">
                                <div class="card-header js_fermeture_bloc">
                                    <h4 class="d-flex align-items-center">
                                        <span>@traduction('composant.liste_adresse.titre')</span>
                                        <span @click="adresse_creer"
                                                class="css_ajouter_element ml-auto"
                                                data-toggle="tooltip"
                                                data-placement="top"
                                                :title="$root.traduction('composant.liste_adresse.tooltip_nouvelle_adresse')">
                                            <i class="css_action_icon secondaire fas fa-plus-square fa-lg"></i>
                                        </span>

                                        <span class="ml-2 css_toggle_card_panel">
                                            <span :class="afficher_par_defaut ? 'fa fa-chevron-up' : 'fa fa-chevron-down'"></span>
                                        </span>
                                    </h4>
                                </div>

                                <div class="card-body">

                                    <div class="row">
                                        <div class="col-sm-12" v-show="adresses.length == 0">
                                            <span>@traduction('composant.liste_adresse.aucune_adresse')</span>
                                        </div>
                                        <div class="col-sm-12">
                                            <table width="100%">
                                                <template v-for="(adresse, index) in adresses">
                                                    <slot name="adresses" :adresse="adresse" :affichage_icone="affichage_icone" :retirer_icone="retirer_icone" :adresse_modifier="adresse_modifier" :adresse_geolocaliser="adresse_geolocaliser">
                                                        <tr>
                                                            <td >
                                                                <span style="cursor: pointer;"  @mouseover="affichage_icone('icone_adresse_'+adresse.id)" @mouseleave="retirer_icone('icone_adresse_'+adresse.id)" @click="adresse_modifier(adresse.id)">
                                                                    <i :id="'icone_adresse_'+adresse.id" style="display: none" class="fas fa-pen"></i>
                                                                    <span v-show="adresse.societe != '' && adresse.societe != null"><b>@{{ adresse.societe }},</b></span>
                                                                    <span v-show="adresse.nom != '' && adresse.nom != null"><b>@{{ adresse.nom }} @{{ adresse.prenom }} - @{{ adresse.ville }}</b></span>
                                                                    
                                                                </span>
                                                                <template v-if="adresse.nom === null && adresse.societe === null">
                                                                    <span style="cursor: pointer;"
                                                                            @mouseover="affichage_icone('icone_adresse_2_'+adresse.id)"
                                                                            @mouseleave="retirer_icone('icone_adresse_2_'+adresse.id)"
                                                                            @click="adresse_modifier(adresse.id)">
                                                                        <i :id="'icone_adresse_2_'+adresse.id" style="display: none" class="fas fa-pen" ></i>
                                                                        <span>@{{ adresse.adresse }}, @{{ adresse.code_postal }} @{{ adresse.ville }}</span><br/>
                                                                    </span>
                                                                </template>
                                                                <template v-else>
                                                                
                                                                    <span>@{{ adresse.adresse }}, @{{ adresse.code_postal }} @{{ adresse.ville }}</span><br/>
                                                                </template>
                                                                <span class="badge badge-default" v-if="adresse.adresse_par_defaut">
                                                                    @traduction('composant.liste_adresse.adresse_par_defaut')
                                                                </span>
                                                                <span>
                                                                    <span class="badge badge-default pull-right" v-show="adresse.type_adresse == 0 || adresse.type_adresse == null">@traduction('composant.liste_adresse.adresse_indefini')</span>
                                                                    <span style="margin-right:10px" class="badge badge-default pull-right" v-show="adresse.type_adresse == 1 || adresse.type_adresse == 3">@traduction('composant.liste_adresse.adresse_facturation')</span>
                                                                    <span class="badge badge-default pull-right" v-show="adresse.type_adresse == 2 || adresse.type_adresse == 3">@traduction('composant.liste_adresse.adresse_livraison')</span>
                                                                    <span class="badge badge-default pull-right" v-show="adresse.type_adresse == 4">@traduction('composant.liste_adresse.adresse_siege')</span>
                                                                    <br/>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span @click="adresse_geolocaliser(adresse)"><i class="css_action_icon mineur fas fa-map-marker-alt"></i></span>
                                                            </td>
                                                        </tr>
                                                    </slot>
                                                </template>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal ajout adresse -->
                    <div id="modale_liste_adresses">
                        <template v-if="modal_ajout_adresse">
                            <transition name="modal">
                                <div id="modal_ajout_adresse" class="modal-mask" style="position: fixed;z-index: 1059;" >
                                    <div class="modal-dialog modal-lg" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">@traduction('composant.liste_adresse.titre_modale')</h5>
                                                <button type="button" class="close" @click="modal_ajout_adresse = false" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <input type="hidden" name="id" v-model="adresse_id" />
                                            <div class="modal-body">
                                                <formulaire ref="formulaire" nom_formulaire="adresse"></formulaire>
                                            </div>
                                            <div class="modal-footer">
                                                <a :href="'/eden/fiche/adresse/'+adresse_id+'/afficher'" class="btn btn-default" v-show="adresse_id != '' && adresse_id != null">@traduction('composant.liste_adresse.modale_afficher')</a>
                                                <button type="button" class="btn btn-danger" @click="adresse_supprimer" v-show="adresse_id != '' && adresse_id != null">@traduction('composant.liste_adresse.modale_supprimer')</button>
                                                <button type="button" class="btn btn-primary" @click="adresse_enregistrer">@traduction('composant.liste_adresse.modale_enregistrer')</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </transition>
                        </template>
                        <!-- Modal géolocalisation adresse -->
                        <div class="modal fade" id="modal_geolocalisation_adresse" tabindex="-1" role="dialog" aria-hidden="true">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">@traduction('composant.liste_adresse.titre_modale')</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <input type="hidden" name="id" v-model="adresse_id" />
                                    
                                    <div class="modal-body">
                                        <iframe
                                            id="adresse_geolocalisation_map"
                                            width="100%"
                                            height="450"
                                            frameborder="0" 
                                            style="border:0"
                                            src="https://www.google.com/maps/embed/v1/place?key=AIzaSyAQhX93KFZwAqwfbOE6xEWlyR-xizvrEQI" allowfullscreen>
                                        </iframe>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`,
                props:{

                    afficher_par_defaut:"",
                    element_standard:"",

                },
                data: function(){

                    return {

                        adresses: {},
                        adresse_id: '',
                        nouvelle_adresse: {!! management('adresse')->modele_par_defaut() !!},
                        modal_ajout_adresse: false,

                    }

                },

                methods:{

                    //adresse creer
                    
                    adresse_geolocaliser: function(adresse) {
                        
                        var adresse_map = '';
                        
                        adresse_map = adresse.adresse;
                        
                        if(adresse.adresse_complement != '' && adresse.adresse_complement != null)
                            adresse_map += ','+adresse.adresse_complement;
                            
                        adresse_map += ','+adresse.code_postal+'+'+adresse.ville;
                        
                        $('#adresse_geolocalisation_map').attr('src', "https://www.google.com/maps/embed/v1/place?key=AIzaSyAQhX93KFZwAqwfbOE6xEWlyR-xizvrEQI&q="+adresse_map);
                        $('#modal_geolocalisation_adresse').modal('show');
                    },
                    
                    adresse_creer: function() {

                        this.$once('formulaire_charger',() => {
                            this.$refs.formulaire.element[this.$root.type_element + '_id'] = this.$root.element_id;
                        });
                        
                        this.modal_ajout_adresse = true;
                        this.adresse_id = '';
                    },
                        
                    
                    //adresse modif
                    
                    adresse_modifier: function(id) {

                        var vue_composant = this;
                        
                        $.each(vue_composant.adresses, function(osef, adresse) {
                            
                            if(adresse.id == id) {
                                
                                vue_composant.$once('formulaire_charger',function(){
                                    vue_composant.$refs.formulaire.element = adresse;
                                });
                                vue_composant.adresse_id = adresse.id;
                            }
                        });
                        
                        vue_composant.modal_ajout_adresse = true;
                    },
                        
                    
                    //adresse enregistrer		
                    adresse_enregistrer: async function() {

                        var component = this;

                        // On afficher le loader
                        loading(true);

                        var informations = {};

                        if(this.element_standard)
                            informations[this.$root.type_element+ '_id'] = this.$root.element_id;
                        else{
                            informations = {
                                type_element : this.$root.type_element,
                                element_id : this.$root.element_id
                            };
                        }

                        var donnees = await component.$refs.formulaire.enregistrer(informations);

                        if(donnees.retour === true) {
                            component.modal_ajout_adresse = false;
                            component.adresse_actualiser();
                        }

                        loading(false);
                        
                    },
                    
                    //adresse supprimer	
                    adresse_supprimer: async function() {

                        var vue_composant = this;

                        if(!await confirm_eden(vue_composant.$root.traduction('composant.liste_adresse.suppression_confirmation')))
                            return false;

                        loading(true);


                        // on fait un appel ajax pour supprimer
                        $.get({

                            url: "eden/element/adresse/" + vue_composant.$data.adresse_id + "/supprimer",
                            dataType: "json",
                            method: 'GET'
                        }).done(async function(donnees) {

                            if(donnees.retour !== true) {

                                loading(false);

                                await erreur(donnees.retour);
                                return;
                            }
                            
                            vue_composant.modal_ajout_adresse = false;

                            // on actualise la liste
                            vue_composant.adresse_actualiser();
                        });
                    },
                    
                    //adresse actualiser
                    
                    adresse_actualiser: function() {

                        var vue_composant = this;
                        
                        loading(true);
                        
                        $.get({

                            url: 'eden/fiche/' + vue_composant.$root.type_element + '/' + vue_composant.$root.element_id + '/adresses',
                            dataType: "json"
                        }).done(function(adresses) {
                            
                            // On retire le loader
                            loading(false);
                            
                            vue_composant.adresses = adresses;
                        });
                    },

                    affichage_icone(id_icon){
                    $('#'+id_icon).show();
                    },

                    retirer_icone(id_icon){
                    $('#'+id_icon).hide();
                    },

                },  
	
                mounted: function() {

                    $('#stack_modales_composants').append($('#modale_liste_adresses'));
                    
                    this.adresse_actualiser();
                },

                watch: {

                    article_id: function(nouvelle_valeur, ancienne_valeur) {

                        this.adresse_actualiser();

                    }

                }
});
</script>
