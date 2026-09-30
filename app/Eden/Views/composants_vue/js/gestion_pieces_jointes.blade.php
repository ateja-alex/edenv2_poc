const gestion_pieces_jointes = Vue.component('gestion-pieces_jointes', {
    template: ` <div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h4 class="d-flex align-items-center">
                                        <span class="mr-auto">@traduction('composant.gestion_pieces_jointes.titre')</span>
                                        @include('eden::composants_vue.js.include.gestion_pieces_jointes.boutons')
                                    </h4>
                                </div>
                                @include('eden::composants_vue.js.include.gestion_pieces_jointes.fil_ariane')
                                <div class="card-body" id="drop_zone"
                                        @drop="dropHandler"
                                        @dragover="dragOverHandler"
                                        @dragleave="dragLeaveHandler"
                                        >
                                    <div class="row" v-if="!dossier_parent || Object.keys(dossier_parent).length == 0">
                                        <div class="col-md-2" style="text-align: center;" v-if="dossier_document_parent && !chargement_en_cours" @dblclick="dossier_document_parent = null">
                                            <div class="css_bibliotheque_conteneur">
                                                <div class="css_bibliotheque_element">
                                                    <span class="fas fa-arrow-left"></span>
                                                </div>
                                                <br/>
                                                @traduction('composant.gestion_pieces_jointes.retour')
                                            </div>
                                        </div>
                                        <div class="col-md-2 droppable"
                                             v-for="dossier_document in dossiers_documents"
                                             v-if="dossier_document_parent==null"
                                             @dblclick="dossier_document_parent = dossier_document.nom">
                                            <div class="css_block_element_biblio">
                                                <div class="css_apercu_biblio">
                                                    <span class="fas fa-folder-open dossier" v-if="dossier_document.nombre_de_fichiers_enfants > 0"></span>
                                                    <span class="fas fa-folder dossier" v-else></span>
                                                    <span v-if="dossier_document.nombre_de_fichiers_enfants !== undefined" class="badge bg-danger text-light" style="font-size: 15px; position: absolute; top: 20%; left: 60%; transform: translate(-50%, -50%);">@{{ dossier_document.nombre_de_fichiers_enfants }}</span>
                                                    <span class="css_nom_dossier_biblio">@{{dossier_document.nom}}</span>
                                                </div>
                                                <div class="css_desc_biblio">
                                                    <span class="css__lien css_nom_fichier_biblio">
                                                        @{{dossier_document.nom}}
                                                    </span>
                                                    <span class="css_info_biblio">
                                                        <i class="fab fa-google-drive" v-if="dossier_document.gdrive"></i>
                                                        @traduction('composant.gestion_pieces_jointes.dossier')
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2"
                                             v-for="document in documents"
                                             v-if="document.dossier_parent == dossier_document_parent"
                                             @dblclick="modal_edition_pj(document,false)"
                                             style="text-align: center;">
                                            <div class="css_bibliotheque_conteneur">
                                                <div class="css_bibliotheque_element css_bibliotheque_fichier">
                                                    <span class="fa fa-paperclip"></span>
                                                    @if(config('gestion_pieces_jointes_extension') !== false)
                                                        @{{ document.extension }}
                                                    @endif
                                                </div>
                                                <br/>
                                                @if(config('gestion_pieces_jointes_nom') !== false)
                                                    @traduction('composant.gestion_pieces_jointes.nom') : <b>@{{ document.titre }}</b><br/>
                                                @endif
                                                @if(config('gestion_pieces_jointes_poids') !== false)
                                                        @traduction('composant.gestion_pieces_jointes.poids') : <b>@{{ document.poids }}</b><br/>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row" v-if="!dossier_document_parent && !chargement_en_cours">
                                        @include('eden::composants_vue.js.include.gestion_pieces_jointes.retour')
                                        @include('eden::composants_vue.js.include.gestion_pieces_jointes.dossiers')
                                        @include('eden::composants_vue.js.include.gestion_pieces_jointes.fichiers')
                                    </div>
                                </div>
                                <div v-show="chargement_en_cours === true">
                                    <div class="" style="text-align:center;">
                                        <img style="width: 60px;" src="<?php echo e('eden/images/ajax_loader.gif'); ?>">
                                    </div>
                                </div>
                            </div>


                        </div>
                    </div>

                    <div id="modales_gestion_pj">
                        @include('eden::composants_vue.js.include.gestion_pieces_jointes.modales')
                    </div>

                </div>


                `,
                props:{

                    afficher_par_defaut:"",
                    element_standard:"",

                },
                data: function(){

                    return {

                        fichier:[],
                        fichiers:[],
                        dossiers:[],
                        fichiers_attente:[],
                        dossier:[],
                        dossier_document_parent: null,

                        element_piece_jointe:null,

                        dossiers_documents:false,
                        documents:false,

                        pieces_jointes:[],
                        documents:[],
                        nouveau_dossier_parent:[],
                        dossier_parent:[],

                        dossier_actuel:'',

                        admin:false,
                        infos_profil:{},

                        creation_dossier_pour_fiche:1,
                        chargement_en_cours : true,
                        synchronisation_mfiles: false,
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

                        this.chargement_en_cours = true;

                        $.get({

                            url: 'eden/fiche/' + this.type_element + '/' + this.element_id + '/pieces_jointes',
                            dataType: "json",
                        }).done((retour) => {

                            // On retire le loader
                            this.chargement_en_cours = false;

                            this.synchronisation_mfiles = retour.synchronisation_mfiles;
                            this.fichiers = retour.fichiers;
                            this.dossiers = retour.dossiers;
                            this.dossiers_documents = retour.dossiers_documents;
                            this.documents = retour.documents;
                        });
                    },

                    // Charger infos profil
                    charger_infos_profils: function() {

                        $.get({

                            url: 'eden/fiche/' + this.type_element + '/' + this.element_id + '/infos_profil_pour_bibliotheque',
                            dataType: "json",

                        }).done((retour) => {

                            // On retire le loader
                            this.admin = retour.admin;
                            this.infos_profil = retour.infos_profil;
                        });
                    },

                    dragOverHandler(ev) {

                        $('#drop_zone').css('border', "2px dashed red");

                        // Prevent default behavior (Prevent file from being opened)
                        ev.preventDefault();

                    },

                    dropHandler(ev) {

                        var vue_composant = this;

                        // Prevent default behavior (Prevent file from being opened)
                        ev.preventDefault();

                        if (ev.dataTransfer.items) {
                            // Use DataTransferItemList interface to access the file(s)
                            for (var i = 0; i < ev.dataTransfer.items.length; i++) {
                                // If dropped items aren't files, reject them
                                if (ev.dataTransfer.items[i].kind === 'file') {

                                    var file = ev.dataTransfer.items[i].getAsFile();

                                    vue_composant.envoi_piece_jointe_dans_bibliotheque(file);

                                }
                            }
                        } else if(ev.dataTransfer.files.length) {
                            // Use DataTransfer interface to access the file(s)
                            for (var i = 0; i < ev.dataTransfer.files.length; i++) {

                                var file = ev.dataTransfer.files[i].getAsFile();

                                vue_composant.envoi_piece_jointe_dans_bibliotheque(file);
                            }
                        }
                    },

                    dragLeaveHandler(ev) {

                        $('#drop_zone').css('border', "");

                        // Prevent default behavior (Prevent file from being opened)
                        ev.preventDefault();
                    },

                    envoi_piece_jointe_dans_bibliotheque(file) {

                        var vue_composant = this;

                        vue_composant.chargement_en_cours = true;

                        var formData = new FormData();

                        vue_composant.fichiers_attente.push({});

                        formData.append('piece_jointe', file, file.name);

                        formData.append('dossier_parent', JSON.stringify(vue_composant.dossier_parent));

                        var xhr = new XMLHttpRequest();

                        xhr.open('POST', "/eden/fiche/"+vue_composant.type_element+"/"+vue_composant.element_id+"/post/ajoute_piece_jointe", true);

                        // Set up a handler for when the request finishes.
                        xhr.onload = async function (resultat) {

                            reponse=JSON.parse(resultat.currentTarget.response);

                            if (xhr.status === 200 && typeof reponse.retour !== 'undefined' && reponse.retour == 'gdrive') {

                                vue_composant.fichiers.push(JSON.parse(resultat.currentTarget.response));
                                vue_composant.fichiers_attente.pop();
                            }
                            else if ((xhr.status === 201 || xhr.status === 200) && (typeof reponse.retour == 'undefined' || ['mfiles','sharepoint'].includes(reponse.retour))) {

                                if(reponse.succes === false && reponse.mfiles === true) {

                                    vue_composant.message_erreur_ajout_mfiles = reponse.message;
                                    vue_composant.modale_erreur_ajout_mfiles = true;
                                    return false;
                                }
                                else if(reponse.retour == 'mfiles')
                                    toastr.success(vue_composant.$root.traduction('messages.js.mfiles.fichier_cree'));
                                else if(reponse.retour == 'sharepoint')
                                    toastr.success(vue_composant.$root.traduction('messages.js.sharepoint.fichier_cree'));

                                vue_composant.fichiers.push(JSON.parse(resultat.currentTarget.response));
                                vue_composant.fichiers_attente.pop();
                            }
                            else if(typeof reponse.retour!== 'undefined') {

                                vue_composant.fichiers_attente.pop();

                                await alerte_eden(reponse.retour);
                            }
                            else{

                                toastr.error('Une erreur est survenue !');
                            }

                            vue_composant.chargement_en_cours = false;
                        };

                        xhr.send(formData);
                    },


                    // Charge le contenu de l'élément
                    supprimer: async function(fichier) {

                        if(!await confirm_eden('Voulez-vous vraiment supprimer le fichier ?'))
                            return false;

                        var vue_composant = this;

                        vue_composant.chargement_en_cours = true;

                        $.get({

                            url: "/eden/fiche/"+vue_composant.type_element+"/"+vue_composant.element_id+"/supprimer_piece_jointe/"+fichier.id,
                            dataType: "json",
                        }).done(async function(donnees) {

                            if(donnees.succes === false && donnees.mfiles === true){

                                vue_composant.message_erreur_ajout_mfiles = donnees.message;
                                vue_composant.chargement_en_cours = false;
                                vue_composant.modale_erreur_ajout_mfiles = true;
                                return false;
                            }
                            if(donnees.retour !== true) {

                                // On retire le loader
                                vue_composant.chargement_en_cours = false;

                                await erreur(donnees.retour);
                                return;
                            }

                            vue_composant.fichiers.splice(vue_composant.fichiers.indexOf(fichier), 1);

                            $('#modal_previsualisation_fichier').modal('hide');

                            //vue_composant.actualiser();
                            vue_composant.deplacement_dans_un_dossier(vue_composant.dossier_actuel)

                        });
                    },

                    modal_gestion_dossier: function(dossier) {

                        this.dossier = dossier;
                        $('#modal_gestion_dossier').modal('show');
                        this.creation_dossier_pour_fiche = 1;
                        this.creation_dossier_confidentiel = 0;
                    },

                    supprimer_dossier: async function(dossier){

                        if(await confirm_eden('Êtes-vous certain, cela entraînera la suppression de tous les dossiers et fichiers contenus dans ce dossier ?')){

                            vue_composant = this;

                            // on enregistre les infos du champ libre
                            $.ajax({

                            url: "/eden/bibliotheque/dossier/"+dossier.id+"/supprimer",
                            dataType: "json"
                            }).done(async function(donnees) {

                            if(donnees.retour !== true) {

                            await erreur(donnees.retour);
                            return;
                            }

                            if(vue_composant.dossier_parent.dossier_parent!=null){

                                vue_composant.deplacement_dans_un_dossier(vue_composant.dossier_parent.dossier_parent.id);
                            }
                            else{
                                vue_composant.deplacement_dans_un_dossier(null);
                            }

                            return false;

                            });
                        }
                    },

                    modal_edition_pj: function(piece_jointe, element_piece_jointe = true) {

                        //console.log(piece_jointe)

                        if(piece_jointe.element_id !== undefined)
                            element_piece_jointe = true;

                        this.fichier = piece_jointe;
                        this.element_piece_jointe = element_piece_jointe;

                        $('#modal_previsualisation_fichier').modal('show');
                    },

                    deplacement_dans_un_dossier: function (id_dossier, retour = false){

                        this.dossier_actuel = id_dossier;

                        dossier_parent = retour === true ? this.dossier_parent.dossier_parent : this.dossier_parent;

                        this.chargement_en_cours = true;

                        if(id_dossier == null)
                            id_dossier = 0

                        type_element = this.type_element;

                        id_element = this.element_id;

                        $.post({

                            url: "/eden/fiche/recuperer_bibliotheque_parametrer",
                            dataType: "json",
                            data:   {
                                'dossier_parent_id': id_dossier,
                                'type_element': type_element,
                                'dossier_parent': dossier_parent,
                                'id_element' : id_element,
                            }

                        }).done(async (donnees) => {

                            if(donnees.retour !== true) {

                                await erreur(donnees.retour);
                                return;
                            }

                            this.dossiers = donnees.dossiers;
                            this.fichiers = donnees.pieces_jointes;
                            this.documents = donnees.documents;
                            this.dossier_parent = donnees.nouveau_dossier_parent;
                            this.reinitialisation_chemin();

                            this.chargement_en_cours = false;
                        });
                    },

                    reinitialisation_chemin(){

                        vue_composant =  this;
                        document.getElementById("chemin_accces").innerHTML = null;
                        this.creation_chemin(vue_composant.dossier_parent);

                    },


                    creation_chemin(dossier){

                        if(dossier != null) {

                            this.creation_chemin(dossier.dossier_parent);

                            document.getElementById("chemin_accces").innerHTML +='/ <span class="css__lien js_dossier_bibliotheque" id_dossier="'+dossier.id+'" id="lien_chemin_dossier_'+dossier.id+'" >'+dossier.nom+'</span> ';

                        }
                    },


                    enregistrer_dossier() {

                        var vue_composant = this;

                        vue_composant.chargement_en_cours = true;

                        var dossier  = this.dossier;

                        dossier.dossier_parent = vue_composant.dossier_parent;

                        dossier.type_element = vue_composant.type_element;

                        dossier.id_element = vue_composant.element_id;

                        if(dossier.dossier_parent!= null && dossier.dossier_parent.element_id != null){

                            vue_composant.creation_dossier_pour_fiche = 1;
                            vue_composant.creation_dossier_confidentiel = 0;
                        }

                        dossier.creation_dossier_pour_fiche = vue_composant.creation_dossier_pour_fiche;
                        dossier.creation_dossier_confidentiel = vue_composant.creation_dossier_confidentiel;

                        // on enregistre les infos du champ libre
                        $.post({

                                url: "{{ route('bibliotheque.ajouter_dossier', [], false) }}",
                                dataType: "json",
                                data:   {
                                'dossier': dossier,
                                }
                        }).done(async function(donnees) {

                            vue_composant.chargement_en_cours = false;

                            if(donnees.retour !== true) {

                                await erreur(donnees.retour);
                                return;
                            }

                            $('#modal_gestion_dossier').modal('hide');

                            vue_composant.dossiers= donnees.dossiers;
                        });

                    },

                    modifier_dossier(){

                        var vue_composant = this;

                        vue_composant.chargement_en_cours = true;

                        var dossier  = this.dossier;

                        dossier.type_element = vue_composant.type_element;

                        dossier.id_element = vue_composant.element_id;

                        // on enregistre les infos du champ libre
                        $.post({

                        url: "{{ route('bibliotheque.modifier_dossier', [], false) }}",
                        dataType: "json",
                        data:   {
                        'dossier': dossier,
                        }
                        }).done(async function(donnees) {

                            vue_composant.chargement_en_cours = false;

                            if(donnees.retour !== true) {

                            await erreur(donnees.retour);
                            return;
                            }

                            $('#modal_gestion_dossier').modal('hide');

                            vue_composant.dossiers= donnees.dossiers;

                            vue_composant.reinitialisation_chemin();

                        });
                    },

                    toggle_0_1: function(valeur) {

                        if(valeur == 1)
                            valeur = 0;
                        else
                            valeur = 1;

                        return valeur;
                    },

                    @yield('donnees_pour_vuejs_methods')
                    @stack('donnees_pour_vuejs_methods')

                },

                mounted: function() {

                    $('#stack_modales_composants').append($('#modales_gestion_pj'));

                    this.charger_infos_profils();
                    this.actualiser();
                },

                watch: {
                    element_id: function(nouvelle_valeur, ancienne_valeur) {

                        this.actualiser();
                    }
                }
});
