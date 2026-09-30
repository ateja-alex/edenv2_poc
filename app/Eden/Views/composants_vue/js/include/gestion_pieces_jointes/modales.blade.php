<!-- Modal ajout dossier -->
<div class="modal fade" id="modal_gestion_dossier" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@traduction('composant.gestion_pieces_jointes.titre_modal')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body css_form">
                <div class="row">
                    <div class="col-sm-12 css_form_ligne_titre">@traduction('composant.gestion_pieces_jointes.sous_titre')</div>
                </div>
                <div class="row">
                    <div class="col-sm-2">@traduction('composant.gestion_pieces_jointes.nom')</div>
                    <div class="col-sm-10"><input type="text" v-model="dossier.nom" /></div>
                </div>
                <div class="row" v-if="dossier.id == null && (dossier_parent==null || dossier_parent.element_id == null ) && [1,2].includes($root.moi.type_utilisateur)">
                    <div class="col-sm-5">@traduction('composant.gestion_pieces_jointes.creation_dossier_pour_fiche')</div>
                    <div class="col-sm-7">
                        <label class="switch"><input type="checkbox" :checked="creation_dossier_pour_fiche" @click="creation_dossier_pour_fiche = toggle_0_1(creation_dossier_pour_fiche)"><span class="slider round"></span></label>
                    </div>
                </div>
                @if(parametre('type_synchro_bibliotheque') != 'gdrive')
                    <div class="row">
                        <div class="col-sm-5">@traduction('composant.gestion_pieces_jointes.confidentiel')</div>
                        <div class="col-sm-7">
                            <label class="switch"><input type="checkbox" @click="creation_dossier_confidentiel = toggle_0_1(creation_dossier_confidentiel)" v-model="dossier.confidentiel"><span class="slider round"></span></label>
                        </div>
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" @click="dossier.id !=null ? modifier_dossier() : enregistrer_dossier()">@traduction('composant.gestion_pieces_jointes.enregistrer')</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@traduction('composant.gestion_pieces_jointes.annuler')</button>
            </div>

        </div>
    </div>
</div>

<!-- Modal ajout élément -->
<div class="modal fade" id="modal_previsualisation_fichier" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@traduction('composant.gestion_pieces_jointes.titre_modal_apercu')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body css_form">
                <div class="row">
                    <div class="col-sm-12 css_form_ligne_titre">@traduction('composant.gestion_pieces_jointes.sous_titre')</div>
                </div>
                <template v-if="element_piece_jointe != undefined && element_piece_jointe == true">
                    <div class="row">
                        <div class="col-sm-2">@traduction('composant.gestion_pieces_jointes.nom')</div>
                        <div class="col-sm-10"><input type="text" disabled="true" v-model="fichier.titre" /></div>
                    </div>
                    <div class="row">
                        <div class="col-sm-2">@traduction('composant.gestion_pieces_jointes.nom_original')</div>
                        <div class="col-sm-10"><input type="text" disabled="true" v-model="fichier.nom" /></div>
                    </div>
                    <div class="row">
                        <div class="col-sm-2">@traduction('composant.gestion_pieces_jointes.dimensions')</div>
                        <div class="col-sm-10"><input type="text" disabled="true" v-model="fichier.dimensions" /></div>
                    </div>
                    <div class="row">
                        <div class="col-sm-2">@traduction('composant.gestion_pieces_jointes.cree_le')</div>
                        <div class="col-sm-10"><input type="text" disabled="true" v-model="fichier.cree_le" /></div>
                    </div>
                    <div class="row">
                        <div class="col-sm-2">@traduction('composant.gestion_pieces_jointes.extension')</div>
                        <div class="col-sm-10"><input type="text" disabled="true" v-model="fichier.extension" /></div>
                    </div>
                    <div class="row">
                        <div class="col-sm-2">@traduction('composant.gestion_pieces_jointes.poids')</div>
                        <div class="col-sm-10"><input type="text" disabled="true" v-model="fichier.poids" /></div>
                    </div>
                    <div class="row" v-if="fichier.stockage_externe != 2">
                        <div class="col-sm-2">@traduction('composant.gestion_pieces_jointes.url')</div>
                        <div class="col-sm-10">
                            <input type="text" disabled="true" v-if="fichier.sharepoint || fichier.stockage_externe == 2" :value="fichier.chemin" />
                            <input type="text" disabled="true" v-else :value="'/storage/'+fichier.chemin" />
                        </div>
                    </div>
                </template>
                <template v-else>
                    <div class="row">
                        <div class="col-sm-2">@traduction('composant.gestion_pieces_jointes.nom')</div>
                        <div class="col-sm-10"><input type="text" disabled="true" v-model="fichier.chemin" /></div>
                    </div>
                    <div class="row">
                        <div class="col-sm-2">@traduction('composant.gestion_pieces_jointes.nom_original')</div>
                        <div class="col-sm-10"><input type="text" disabled="true" v-model="fichier.nom_original" /></div>
                    </div>
                    <div class="row">
                        <div class="col-sm-2">@traduction('composant.gestion_pieces_jointes.dimensions')</div>
                        <div class="col-sm-10"><input type="text" disabled="true" v-model="fichier.dimensions" /></div>
                    </div>
                    <div class="row">
                        <div class="col-sm-2">@traduction('composant.gestion_pieces_jointes.extension')</div>
                        <div class="col-sm-10"><input type="text" disabled="true" v-model="fichier.type" /></div>
                    </div>
                    <div class="row">
                        <div class="col-sm-2">@traduction('composant.gestion_pieces_jointes.poids')</div>
                        <div class="col-sm-10"><input type="text" disabled="true" v-model="fichier.poids" /></div>
                    </div>
                    <div class="row">
                        <div class="col-sm-2">@traduction('composant.gestion_pieces_jointes.url')</div>
                        <div class="col-sm-10">
                            <input type="text" disabled="true" v-if="fichier.sharepoint" :value="fichier.chemin" />
                            <input v-else type="text" disabled="true" :value="'/storage/'+fichier.chemin" />
                        </div>
                    </div>
                </template>
            </div>
            <div class="modal-footer">
                <a :href="'/storage/'+fichier.chemin" target="_blank" type="button" class="btn btn-primary" v-if="fichier.url_sur_serveur != undefined">@traduction('composant.gestion_pieces_jointes.telecharger')</a>
                <a :href="'/eden/element/'+fichier.type_element+'/'+fichier.element_id+'/afficher_pdf'" target="_blank" type="button" class="btn btn-primary" v-else-if="!fichier.sharepoint && fichier.stockage_externe != 2">@traduction('composant.gestion_pieces_jointes.telecharger')</a>
                <button type="button" class="btn btn-danger" @click="supprimer(fichier)" >@traduction('composant.gestion_pieces_jointes.supprimer')</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@traduction('composant.gestion_pieces_jointes.fermer')</button>
            </div>

        </div>
    </div>
</div>

<!-- Modale erreur ajout pièce jointe M-Files -->
<transition name="modal" v-if="modale_erreur_ajout_mfiles">
    <div class="modal-mask">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">M-Files : erreur ajout de pièce jointe</h5>
                </div>

                <div class="modal-body css_form js_selection_element" >
                    <span v-html="message_erreur_ajout_mfiles"></span>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="modale_erreur_ajout_mfiles = false">@traduction('interface.modales.fermer')</button>
                </div>
            </div>
        </div>
    </div>

</transition>

@push('donnees_pour_vuejs_data')

    modale_erreur_ajout_mfiles : false,
    message_erreur_ajout_mfiles : '',
@endpush

@push('donnees_pour_vuejs_methods')

    modal_previsualisation_fichier: function(fichier) {

        this.fichier = fichier;
        $('#modal_previsualisation_fichier').modal('show');
    },

    supprimer_fichier : function(fichier)  {

        vue_contexte = this;

        // on enregistre les infos du champ libre
        $.ajax({

            url: "/eden/bibliotheque/fichier/"+fichier.id+"/supprimer",
            dataType: "json"

        }).done(async function(donnees) {

            if(donnees.retour !== true) {

                await erreur(donnees.retour);
                return;
            }

            vue_contexte.fichiers.splice(vue_contexte.fichiers.indexOf(fichier), 1);

            $('#modal_previsualisation_fichier').modal('hide');
        });

        return false;

    },

@endpush

@push('donnees_pour_vuejs_data')

    fichier: {},

@endpush
