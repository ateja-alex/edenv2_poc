const messages = Vue.component('messages', {
    template: ` <div>
                    <div class="card mb-3 css_bloc_formulaire_fiche">
                        <div class="card-header">
                            <h4 class="css_titre_formulaire_fiche_element" style="width: 100%;">@traduction('composant.messages.titre')</h4>
                        </div>
                        <div class="card-body css_form">

                            <div class="liste_messages">
                                <div v-for="message in messages" v-if="(message.contenu !== null && message.contenu !== '') || (message.piece_jointe != '' && message.piece_jointe != null)">
                                    <div :style="{ textAlign : message.auteur == id_utilisateur ? 'right' : 'left' }">
                                        <div>
                                            <img alt="image" class="rounded-circle" style="max-width: 45px; max-height: 45px;" :style="{ float: message.auteur == id_utilisateur ? 'right' : 'left', marginRight: message.auteur == id_utilisateur ? '0px' : '10px', marginLeft: message.auteur == id_utilisateur ? '10px' : '0px' }" :src="message.cree_par | affiche_utilisateur_avatar" />
                                            <strong style="font-size: 16px;">@{{ message.auteur | affiche_utilisateur }}</strong><br/>
                                            <span style="font-size: 18px;">@{{ message.cree_le | datetime_relatif }}</span>
                                        </div>
                                        <div class="bloc_texte" style="padding:5px;border-radius:3px; max-width: 70%;"
                                             v-bind:class="[ message.auteur == id_utilisateur ? 'alert-warning' : 'alert-info' ]"
                                             v-bind:style="{ float : message.auteur == id_utilisateur ? 'right' : 'left' }"
                                            >

                                            <div class="ticket_client_message" v-if="modification_message.id != message.id" v-html="nl2br(message.contenu)"></div>
                                            <div class="ticket_client_message" v-else>
                                                {!! management('message')->champ('contenu')->vmodel(true,'modification_message')->cree() !!}
                                            </div>

                                            <div class="options_message" v-if="($root.moi.type_utilisateur == 2 || message.auteur == $root.moi.id) && modification_message == false">
                                                <i class="fas fa-pencil-alt" @click="mise_en_place_modification(message)"></i>
                                                <i class="fas fa-trash" @click="supprimer_message(message)"></i>
                                            </div>

                                            <div class="bouton_validation_modification" v-if="modification_message.id == message.id">
                                                <i class="fas fa-check" @click="modifier_message"></i>
                                                <i class="fas fa-times" @click="modification_message = false;"></i>
                                            </div>

                                            <div v-if="message.piece_jointe">
                                                <br>@traduction('composant.messages.piece_jointe') <a :href="'storage/'+message.piece_jointe" target="_blank" v-text="message.piece_jointe"></a>
                                                <br/>
                                                <img :src="'storage/'+message.piece_jointe" style="max-width: 500px;" v-if="(message.piece_jointe.indexOf('.png') >= 0 || message.piece_jointe.indexOf('.jpg') >= 0 || message.piece_jointe.indexOf('.jpeg') >= 0 || message.piece_jointe.indexOf('.PNG') >= 0 || message.piece_jointe.indexOf('.JPG') >= 0 || message.piece_jointe.indexOf('.JPEG') >= 0)" />
                                            </div>
                                        </div><br style="clear:both">

                                    </div>
                                    <hr>
                                </div>
                            </div>
                            <div class="bouton_reponse">
                                @traduction('composant.messages.repondre')

                                {!! management('message')->champ('contenu')->cree() !!}
                                {!! management('message')->champ('piece_jointe')->cree() !!}

                                <button type="button" class="btn btn-primary" @click="repondre">@traduction('composant.messages.repondre_action')</button>
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

                        messages: {},
                        message:{
                            type_element: this.$root.type_element,
                            element_id: this.$root.element_id,
                            auteur: this.$root.moi.id,
                            contenu: '',
                        },
                        id_utilisateur:this.$root.moi.id,
                        modification_message : false,
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

                    nl2br: function(texte) {

                        if (!texte)
                            return '';

                        return (texte + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + '<br/>' + '$2')
                    },

                    repondre : function() {

                        loading(true);
                        
                        $.ajax({

                            method: 'POST',
                            dataType: 'json',
                            data: this.message,
                            url: '{!! route('base_eden.element.creer', ['message'], false) !!}'
                        }).done((donnees) => {

                            if(donnees.retour) {

                                this.actualiser();

                                this.message = {
                        
                                    type_element: this.type_element,
                                    element_id: this.element_id,
                                    auteur: this.id_utilisateur,
                                    contenu: '',
                                };
                            }
                        });
                    },

                    // Charge le contenu de l'élément
                    actualiser: function() {

                        loading(true);
                        
                        $.get({

                            url: 'eden/fiche/'+this.type_element+'/'+this.element_id+'/messages',
                            dataType: "json"
                        }).done((messages) => {
                            
                            // On retire le loader
                            loading(false);

                            this.messages = messages;
                        });
                    },

                    modifier_message : function(){

                        loading(true);

                        $.ajax({
                            method: 'POST',
                            dataType: 'json',
                            data: {
                                contenu : this.modification_message.contenu
                            },
                            url: 'eden/element/message/'+this.modification_message.id+'/enregistrer'
                        }).done((donnees) => {

                            if(donnees.retour) {
                                //On rafraîchit les échanges
                                this.actualiser();
                                this.modification_message = false;
                                loading(false);
                            }
                        });
                    },

                    supprimer_message : async function(message){

                        if(!await confirm_eden("{{ traduction('interface.modales.confirmation_suppression') }}"))
                            return false;

                        loading(true);

                        $.ajax({
                            dataType: 'json',
                            url: 'eden/element/message/'+message.id+'/supprimer'
                        }).done((donnees) => {
                            if(donnees.retour) {
                                this.actualiser();

                                loading(false);
                            }
                        });
                    },

                    mise_en_place_modification : function(message){
                        this.modification_message = structuredClone(message);
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

                    },

                }
            });
