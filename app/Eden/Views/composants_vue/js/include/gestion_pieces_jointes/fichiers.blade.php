{{-- Fichier --}}
<div class="col-md-2" v-for="fichier in fichiers" draggable
    @dragend="endDrag()"
    @dragstart='startDrag($event, fichier,"fichier")' >
    <div class="css_block_element_biblio"
         @dblclick="modal_previsualisation_fichier(fichier)"
         >
        <div class="css_apercu_biblio">
            <img :src="'storage/'+fichier.chemin" v-if="(fichier.stockage_externe == 0 || fichier.stockage_externe === null) && (fichier.extension.toLowerCase() === 'jpg' || fichier.extension.toLowerCase() === 'jpeg' || fichier.extension.toLowerCase() === 'png')" />
            <img :src="fichier.miniature" v-else-if="(fichier.gdrive && fichier.miniature) || (fichier.extension === 'pdf' && fichier.miniature)" />
            <template v-else-if="fichier.stockage_externe > 0 && fichier.extension.toLowerCase() !== 'jpg' && fichier.extension.toLowerCase() !== 'jpeg' && fichier.extension.toLowerCase() !== 'png'">
                <span class="fa fa-image"></span>
                @{{ fichier.extension }}
            </template>
            <template v-else>
                <span class="fa fa-file"></span>
                @{{ fichier.extension }}
            </template>

            <div class="boutons"
                 style="position:absolute;float:right;opacity:0"
                 onmouseover="$(this).css('opacity',1);"
                 onmouseout="$(this).css('opacity',0);">

                <span
                    @click="modal_edition_pj(fichier)"
                    style="font-size:11px;padding:5px;cursor:pointer;margin-left:10%;width:80%;"
                    v-if="!fichier.gdrive"
                    class="btn btn-sm btn-primary">@traduction('composant.gestion_pieces_jointes.informations')</span>

                <a
                    :href="fichier.chemin"
                    target="_blank"
                    v-if="fichier.gdrive"
                    style="font-size:11px;padding:5px;cursor:pointer;margin-left:10%;min-width:100px;width:80%;"
                    class="btn btn-sm btn-success">@traduction('composant.gestion_pieces_jointes.ouvrir')</a>

                <a
                    :href="fichier.chemin"
                    target="_blank"
                    v-if="fichier.sharepoint"
                    style="font-size:11px;padding:5px;cursor:pointer;margin-left:10%;min-width:100px;width:80%;"
                    class="btn btn-sm btn-success">@traduction('composant.gestion_pieces_jointes.ouvrir')</a>

                <a
                    :href="'../storage/'+fichier.chemin"
                    target="_blank"
                    v-if="!fichier.sharepoint && !fichier.gdrive && fichier.stockage_externe != 2 && (fichier.extension.toLowerCase() === 'jpg' || fichier.extension.toLowerCase() === 'jpeg' || fichier.extension.toLowerCase() === 'png' || fichier.extension.toLowerCase() === 'pdf' || fichier.extension.toLowerCase() === 'webp' || fichier.extension.toLowerCase() === 'gif')"
                    style="font-size:11px;padding:5px;cursor:pointer;margin-left:10%;width:80%;"
                    class="btn btn-sm btn-warning">@traduction('composant.gestion_pieces_jointes.apercu')
                </a>

                <a  v-if='fichier.element_id != undefined'
                    :href="'/eden/fiche/'+type_element+'/'+element_id+'/telecharger_piece_jointe/'+fichier.id"
                    style="font-size:11px;padding:5px;cursor:pointer;margin-left:10%;width:80%;"
                    type="button" class="btn btn-sm btn-success">@traduction('composant.gestion_pieces_jointes.telecharger')</a>

                <a
                    :href="'/eden/bibliotheque/fichier/'+fichier.id+'/telecharger'"
                    style="font-size:11px;padding:5px;cursor:pointer;margin-left:10%;width:80%;"
                    v-else-if="!fichier.gdrive"
                    class="btn btn-sm btn-success">@traduction('composant.gestion_pieces_jointes.telecharger')</a>

                <span
                    @click="supprimer(fichier)"
                    v-if="suppression_fichier(fichier)"
                    style="font-size:11px;padding:5px;cursor:pointer;margin-left:10%;width:80%;"
                    class="btn btn-sm btn-danger">
                        @traduction('composant.gestion_pieces_jointes.supprimer')
                </span>
            </div>
        </div>
        <div class="css_desc_biblio">
            <span class="css__lien css_nom_fichier_biblio">
                @{{ fichier.nom}}
            </span>
            <span class="css_info_biblio">
                <i class="fab fa-google-drive" v-if="fichier.gdrive"></i>
                <span v-if="fichier.dimensions != 0 && fichier.dimensions != null">
                    @{{ fichier.dimensions }} - 
                </span>
                @{{ fichier.poids }} - @{{ fichier.cree_le }}

            </span>
        </div>
    </div>
</div>

{{-- Upload fichier --}}
<div class="col-md-2" v-for="fichier in fichiers_attente">
    <div class="css_block_element_biblio" @dblclick="modal_previsualisation_fichier(fichier)">
        <div class="css_apercu_biblio">
            <span class="fa fa-file upload"></span>
            ?
        </div>
        <div class="css_desc_biblio">
            <span class="css__lien css_nom_fichier_biblio">
                @traduction('composant.gestion_pieces_jointes.patientez')
            </span>
            <span class="css_info_biblio">
                ?
            </span>
        </div>  
    </div>
</div>

{{-- Si aucun fichier --}}
<div class="col-md-12" v-if="fichiers.length == 0" style="text-align: center;">
    <div style="margin: 5px; padding: 5px;" >
        <span class="fa fa-upload"></span>
        @traduction('composant.gestion_pieces_jointes.deposez_vos_fichiers_ici')
    </div>
</div>

@push('donnees_pour_vuejs_methods')

    suppression_fichier : function(fichier){

        if(this.$root.moi.id > 0)
            return this.admin || this.$root.moi.id > 0 || (fichier.type_element_createur == 'utilisateur' && fichier.element_id_createur == this.$root.moi.id);
        else if(this.$root.moi_extranet.id > 0)
            return fichier.type_element_createur == 'contact' && fichier.element_id_createur == this.$root.moi_extranet.contact_selectionne.id

        return false;
    },
@endpush
