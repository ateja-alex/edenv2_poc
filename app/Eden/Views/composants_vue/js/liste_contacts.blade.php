<script>
const liste_contacts = Vue.component('liste-contacts', {
    template: `<div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card mb-3">
                                <div class="card-header js_fermeture_bloc">
                                    <h4 class="d-flex align-items-center">
                                        <span>@traduction('composant.liste_contacts.titre')</span>
                                        <span @click="contact_creer"
                                                class="css_ajouter_element ml-auto"
                                                data-toggle="tooltip"
                                                data-placement="top"
                                                :title="$root.traduction('composant.liste_contacts.nouveau_contact')">
                                            <i class="css_action_icon secondaire fas fa-plus-square fa-lg"></i>
                                        </span>

                                                <span v-if="afficher_par_defaut && afficher_par_defaut == true" class="ml-2 css_toggle_card_panel">
                                                    <span class="fa fa-chevron-up"></span>
                                                </span>

                                                <span v-else-if="afficher_par_defaut && afficher_par_defaut == false" class="ml-2 css_toggle_card_panel">
                                                    <span class="fa fa-chevron-down"></span>
                                                </span>


                                    </h4>
                                </div>
                                <div class="card-body" v-show="afficher_par_defaut">
                                    <div class="row">
                                        <div class="col-sm-12" v-show="contacts.length == 0">
                                            <span>@traduction('composant.liste_contacts.aucun_contact')</span>
                                        </div>
                                        <div class="col-sm-12" style="font-size:12px;">
                                            <table width="100%" >
                                                <template v-for="contact in contacts" v-if="contact.statut === 0">
                                                  <slot name="contacts_prioritaires" :contacts_prioritaires="contacts_prioritaires" :contact="contact" :contact_modifier="contact_modifier" :affichage_icone="affichage_icone" :retirer_icone="retirer_icone" :contact_indisponible="contact_indisponible">
                                                        <tr v-show="!contacts_prioritaires || (contact.contact_prioritaire === 1 && contacts_prioritaires)">
                                                            <td width="75%" >
                                                                <div style="cursor: pointer"
                                                                        @click="contact_modifier(contact.id)"
                                                                        @mouseover="affichage_icone('icone_contact_'+contact.id)"
                                                                        @mouseleave="retirer_icone('icone_contact_'+contact.id)">
                                                                    <span v-show="contact.npai == 1" class="badge badge-warning">@traduction('composant.liste_contacts.npai')</span>
                                                                    <i :id="'icone_contact_'+contact.id" style="display: none" class="fas fa-pen"></i>
                                                                    <b v-if="contact.civilite != null && contact.civilite != 0">@{{ $root.traduction('valeurs_listes_formatees.39.valeur_' + contact.civilite) }}</b>
                                                                    <b v-if="contact.prenom == null && contact.nom == null">@traduction('composant.liste_contacts.non_specifie')</b>
                                                                    <b v-else> @{{ contact.prenom }} @{{ contact.nom }} </b>
                                                                    <a @click="(event) => event.stopPropagation()" :href="url_usurpation_extranet(contact)" target="_blank" v-if="contact.utilisateur_extranet_id > 0">
                                                                        <span :title="$root.traduction('composant.liste_contacts.acces_extranet')" style="background: rgb(42, 181, 48); color: rgb(255, 255, 255);" class="badge badge-default">
                                                                            <i class="fas fa-external-link-alt"></i>
                                                                        </span>
                                                                    </a>
                                                                    <i class="fas fa-star" style="color: #FFD43B;" v-if="contact.contact_prioritaire"></i>
                                                                </div>
                                                                <div v-show="contact.poste != ''" >@{{ contact.valeur_affichage.poste }}</div>
                                                                <span class="badge badge-danger" v-if="contact_indisponible(contact)">
                                                                    @traduction('interface.indisponibilite.badge')
                                                                </span>
                                                            </td>
                                                            <td width="25%">
                                                                <a class="js_encoyer_mail" href="#" @click.prevent="envoyer_par_email(contact.id);" v-show="contact.adresse_email != null"><nobr><i class="fa fa-at" style="color: black"></i> @{{ contact.adresse_email }}</nobr><br/></a>
                                                                <a :href="'tel:'+contact.telephone" v-show="contact.telephone != null" style="margin-right: 10px;" ><i class="fa fa-phone" style="color: black"></i> @{{ contact.telephone }}<br/></a>
                                                                <a :href="'tel:'+contact.telephone_portable" v-show="contact.telephone_portable != null" style="margin-right: 10px;" ><i class="fa fa-mobile-alt" style="color: black"></i> @{{ contact.telephone_portable }}<br/></a>
                                                            </td>
                                                        </tr>
                                                    <br>
                                                  </slot>
                                                </template>
                                                <template v-for="contact in contacts" v-if="contact.statut === 1">
                                                  <slot name="contacts" :contacts_prioritaires="contacts_prioritaires" :contact="contact" :contact_modifier="contact_modifier" :affichage_icone="affichage_icone" :retirer_icone="retirer_icone" :contact_indisponible="contact_indisponible">
                                                        <tr v-show="!contacts_prioritaires || (contact.contact_prioritaire === 1 && contacts_prioritaires)">
                                                            <td width="75%" >
                                                                <div style="cursor: pointer"
                                                                        @click="contact_modifier(contact.id)"
                                                                        @mouseover="affichage_icone('icone_contact_'+contact.id)"
                                                                        @mouseleave="retirer_icone('icone_contact_'+contact.id)">
                                                                    <span v-show="contact.npai == 1" class="badge badge-warning">@traduction('composant.liste_contacts.npai')</span>
                                                                    <i :id="'icone_contact_'+contact.id" style="display: none" class="fas fa-pen"></i>
                                                                    <b v-if="contact.civilite != null && contact.civilite != 0">@{{ $root.traduction('valeurs_listes_formatees.39.valeur_' + contact.civilite) }}</b>
                                                                    <b v-if="contact.prenom == null && contact.nom == null">@traduction('composant.liste_contacts.non_specifie')</b>
                                                                    <b v-else style="text-decoration: line-through"> @{{ contact.prenom }} @{{ contact.nom }} </b>
                                                                    <a @click="(event) => event.stopPropagation()" :href="url_usurpation_extranet(contact)" target="_blank" v-if="contact.utilisateur_extranet_id > 0">
                                                                        <span :title="$root.traduction('composant.liste_contacts.acces_extranet')" style="background: rgb(42, 181, 48); color: rgb(255, 255, 255);" class="badge badge-default">
                                                                            <i class="fas fa-external-link-alt"></i>
                                                                        </span>
                                                                    </a>
                                                                    <i class="fas fa-star" style="color: #FFD43B;" v-if="contact.contact_prioritaire"></i>
                                                                </div>
                                                                <div v-show="contact.poste != ''" >@{{ contact.valeur_affichage.poste }}</div>
                                                            </td>
                                                            <td width="25%">
                                                                <a class="js_encoyer_mail" href="#" @click.prevent="envoyer_par_email(contact.id);" v-show="contact.adresse_email != null"><nobr><i class="fa fa-at" style="color: black"></i> @{{ contact.adresse_email }}</nobr><br/></a>
                                                                <a :href="'tel:'+contact.telephone" v-show="contact.telephone != null" style="margin-right: 10px;" ><i class="fa fa-phone" style="color: black"></i> @{{ contact.telephone }}<br/></a>
                                                                <a :href="'tel:'+contact.telephone_portable" v-show="contact.telephone_portable != null" style="margin-right: 10px;" ><i class="fa fa-mobile-alt" style="color: black"></i> @{{ contact.telephone_portable }}<br/></a>
                                                            </td>
                                                        </tr>
                                                    <br>
                                                  </slot>
                                                </template>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="modale_liste_contacts">
                        <template v-if="modal_ajout_contact">
                            <transition name="modal">
                                <div id="modal_ajout_contact" class="modal-mask" style="position: fixed;z-index: 1059;" >
                                    <div class="modal-dialog modal-lg" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">@traduction('composant.liste_contacts.titre_modal')</h5>
                                                <button type="button" class="close" @click="modal_ajout_contact = false" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <input type="hidden" name="id" v-model="contact_id" />
                                            <div class="modal-body">
                                                <formulaire ref="formulaire" nom_formulaire="contact"></formulaire>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-danger" @click="contact_supprimer" v-show="contact_id != ''">@traduction('composant.liste_contacts.supprimer')</button>
                                                @if(table_libre('contact')->fiche == 1)
                                                    <a class="btn btn-default" v-show="contact_id != ''" :href="'eden/fiche/contact/'+contact_id">@traduction('composant.liste_contacts.afficher')</a>
                                                @endif
                                                <button type="button" class="btn btn-primary" @click="contact_enregistrer">@traduction('composant.liste_contacts.enregistrer')</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </transition>
                        </template>
                    </div>
                </div>`,
                props:{

                    client_id:{},
                    afficher_par_defaut:"",
                    filtres_pour_liste:{
                        type:Object,
                        default : function(){
                            return {};
                        }
                    },
                },
                data: function(){

                    return{

                        @yield('donnees_pour_vuejs_data')
                        @stack('donnees_pour_vuejs_data')

                        contacts: {},
                        contact_id: '',
                        contacts_prioritaires: false,
                        modal_ajout_contact : false,
                        cle_formulaire:0,

                    }

                },
                methods:{

                    @yield('donnees_pour_vuejs_methods')
                    @stack('donnees_pour_vuejs_methods')

                    contact_creer: function() {

                        var vue_composant = this;

                        this.contact_id = '';
                        vue_composant.$once('formulaire_charger',function() {
                            vue_composant.$refs.formulaire.element[vue_composant.$root.type_element + '_id'] = vue_composant.client_id;
                            vue_composant.cle_formulaire++;

                            $.each(vue_composant.filtres_pour_liste, function(index,valeur){
                                vue_composant.$refs.formulaire.element[index] = valeur;
                            });
                        });
                        vue_composant.modal_ajout_contact = true;

                    },

                    afficher_contacts_prioritaire: function(event) {

                        var vue_composant = this;

                        if($(event.target).hasClass('badge-success')){
                            $.each(vue_composant.contacts, function(osef, contact) {
                                if(contact.contact_prioritaire == 0) {
                                    // console.log(contact.nom);
                                    vue_composant.$refs.formulaire.element = contact;
                                    vue_composant.contact_id = contact.id;
                                }
                            });
                            $(event.target).removeClass('badge-success').addClass('badge-default');
                        }
                    },

                    contact_modifier: function(id) {

                        var vue_composant = this;

                        $.each(vue_composant.contacts, function(osef, contact) {

                            if(contact.id == id) {

                                vue_composant.$once('formulaire_charger',function() {
                                    vue_composant.$refs.formulaire.element = structuredClone(contact);
                                    vue_composant.cle_formulaire++;
                                });
                                vue_composant.contact_id = contact.id;
                            }
                        });

                        vue_composant.modal_ajout_contact = true;
                    },

                    contact_enregistrer: async function() {

                        var component = this;

                        // On afficher le loader
                        loading(true);

                        var informations = {};

                        informations[this.$root.type_element + '_id'] = this.client_id;

                        for(champ in this.filtres_pour_liste){

                            informations[champ] = this.filtres_pour_liste[champ];
                        }

                        var donnees = await component.$refs.formulaire.enregistrer(informations);

                        if(donnees.retour === true) {
                            component.modal_ajout_contact = false;
                            component.contact_actualiser();
                        }

                        loading(false);
                    },

                    contact_supprimer: async function() {

                        var vue_composant = this;

                        if(!await confirm_eden(vue_composant.$root.traduction('composant.liste_contacts.confirmation_supprimer')))
                            return false;

                        loading(true);


                        // on fait un appel ajax pour supprimer
                        $.get({

                            url: "eden/element/contact/"+vue_composant.$data.contact_id+"/supprimer",
                            dataType: "json",
                            method: 'GET'
                        }).done(async function(donnees) {

                            if(donnees.retour !== true) {

                                loading(false);

                                await erreur(donnees.retour);
                                return;
                            }

                            vue_composant.modal_ajout_contact = false;

                            // on actualise la liste
                            vue_composant.contact_actualiser();
                        });
                    },

                    contact_actualiser: function() {

                        var vue_composant = this;

                        loading(true);

                        $.get({

                            url: 'eden/fiche/' + vue_composant.$root.type_element + '/' + vue_composant.client_id + '/contacts',
                            dataType: "json",
                            data : {
                                filtres_pour_liste : vue_composant.filtres_pour_liste,
                            }
                        }).done(function(contacts) {

                            // console.log(contacts);
                            // On retire le loader
                            loading(false);

                            vue_composant.contacts = contacts;
                        });
                    },

                    affichage_icone(id_icon){

                        $('#'+id_icon).show();
                    },

                    retirer_icone(id_icon){

                        $('#'+id_icon).hide();
                    },

                    envoyer_par_email : function (contact_id){

                        this.$root.$emit('envoie_email',{'type_element' : 'contact', 'id_element' : contact_id });
                    },

                    contact_indisponible : function(contact){

                        if(contact.chaine_affichage === null)
                            return false;

                        var indisponibilite = contact.chaine_affichage.match(/\[indisponibilite](.*?)\[\/indisponibilite]/);

                        if(indisponibilite === null)
                            return false;

                        var resultat = indisponibilite[1].split('|');

                        var date_de_debut = new Date(resultat[0]);
                        var date_de_fin = new Date(resultat[1]);

                        var date = Date.now();

                        if(date_de_debut.getTime() <= date && date <= date_de_fin.getTime())
                            return true;

                        return false;
                    },

                    url_usurpation_extranet : function(contact){
                        return `/extranet/usurpation/${contact.utilisateur_extranet_id}?retour_url=${window.location.href}`;
                    },
                },

                computed :{
                    contact : function(){
                        this.cle_formulaire;
                        if(this.$refs.formulaire !== undefined)
                            return this.$refs.formulaire.element;
                        return {};
                    }
                },

                mounted: function() {

                    @yield('donnees_pour_vuejs_mounted')
                    @stack('donnees_pour_vuejs_mounted')

                    $('#stack_modales_composants').append($('#modale_liste_contacts'));

                    this.contact_actualiser();
                },

                watch: {

                    @yield('donnees_pour_vuejs_watch')
                    @stack('donnees_pour_vuejs_watch')

                    article_id: function(nouvelle_valeur, ancienne_valeur) {

                        this.contact_actualiser();

                    }

                }
});

$('.js_filtre_contacts_prioritaires').on('click', function(){
    if($(this).hasClass('badge-success'))
        $(this).removeClass('badge-success').addClass('badge-default');
    else
    $(this).removeClass('badge-default').addClass('badge-success');
});

</script>
