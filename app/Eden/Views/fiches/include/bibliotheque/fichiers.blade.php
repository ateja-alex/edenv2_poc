
{{-- Fichier --}}
<div class="col-md-2" v-for="(fichier,index) in fichiers" :draggable="moi_extranet === null ? true : false" @dragend="endDrag()" @dragstart='startDrag($event, fichier,"fichier")'>
    <div class="css_block_element_biblio" @mouseenter="element_affichage_options = 'fichier_' + fichier.id;"
         @mouseleave="element_affichage_options = null;" @dblclick="modal_previsualisation_fichier(fichier)">
        <span v-if="element_affichage_options == 'fichier_' + fichier.id && moi_extranet === null" style="position: absolute;z-index: 2;top: -10px;right: -10px;">
            <vue-custom-tooltip position="is-left" :label="fichier.disponible_extranet == true ? $root.traduction('interface.bibliotheque.rendre_indisponible_extranet') : $root.traduction('interface.bibliotheque.rendre_disponible_extranet')" >
                <span class="bulle_option" @click="dispo_extranet(fichier.disponible_extranet == true ? 0:1, 'fichier_bibliotheque', fichier.id, index)">
                    <i :class="fichier.disponible_extranet == true ? 'fas fa-lock' : 'fas fa-lock-open'"></i>
                </span>
            </vue-custom-tooltip>
        </span>
        <div class="css_apercu_biblio">
            <img :src="'storage/'+fichier.chemin" v-if="fichier.image === true" />
            <img :src="fichier.miniature" v-else-if="fichier.gdrive && fichier.miniature" />
            <template v-else>
                <span class="fa fa-file"></span>
                @{{fichier.type}}
            </template>

            <div class="boutons"
                 style="position:absolute;float:right;opacity:0"
                 onmouseover="$(this).css('opacity',1);"
                 onmouseout="$(this).css('opacity',0);">

                <button
                    @click="modal_previsualisation_fichier(fichier)"
                    style="font-size:11px;padding:5px;cursor:pointer;margin-left:10%;width:80%;"
                    v-if="!fichier.gdrive"
                    class="btn btn-sm btn-primary">@traduction('interface.bibliotheque.boutons.informations')</button>

                <a
                    :href="fichier.chemin"
                    target="_blank"
                    v-if="fichier.gdrive || fichier.sharepoint"
                    style="font-size:11px;padding:5px;cursor:pointer;margin-left:10%;min-width:100px;width:80%;"
                    class="btn btn-sm btn-success">@traduction('interface.bibliotheque.boutons.ouvrir')</a>

                <a
                    :href="'../storage/'+fichier.chemin"
                    target="_blank"
                    v-if="!fichier.sharepoint && !fichier.gdrive && (fichier.extension === 'jpg' || fichier.extension === 'jpeg' || fichier.extension === 'png' || fichier.extension === 'pdf' || fichier.extension === 'webp' || fichier.extension === 'gif')"
                    style="font-size:11px;padding:5px;cursor:pointer;margin-left:10%;width:80%;"
                    class="btn btn-sm btn-warning">@traduction('interface.bibliotheque.boutons.apercu')</a>


                <a  v-if='element_piece_jointe'
                    :href="'{{ URL::to('/eden/fiche')}}/'+type_element+'/'+id_element+'{{('/telecharger_piece_jointe')}}/'+fichier.id"
                    style="font-size:11px;padding:5px;cursor:pointer;margin-left:10%;width:80%;"
                    type="button" class="btn btn-sm btn-success">@traduction('interface.bibliotheque.boutons.telecharger')</a>

                <a
                    :href="'{{ URL::to('/eden/bibliotheque/fichier')}}/'+fichier.id+'{{('/telecharger')}}'"
                    style="font-size:11px;padding:5px;cursor:pointer;margin-left:10%;width:80%;"
                    v-else-if="!fichier.gdrive"
                    class="btn btn-sm btn-success">@traduction('interface.bibliotheque.boutons.telecharger')</a>

                <button
                    @click="supprimer_fichier(fichier)"
                    style="font-size:11px;padding:5px;cursor:pointer;margin-left:10%;width:80%;"
                    class="btn btn-sm btn-danger">@traduction('interface.bibliotheque.boutons.supprimer')</button>
            </div>

        </div>
        <div class="css_desc_biblio">
            <span class="css__lien css_nom_fichier_biblio">
                @{{fichier.nom_original ? fichier.nom_original : fichier.nom}}
            </span>
            <span class="css_info_biblio">
                <i class="fab fa-google-drive" v-if="fichier.gdrive"></i>
                <span v-if="fichier.dimensions != 0">
                    @{{fichier.dimensions}} - 
                </span>
                @{{fichier.poids}}
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
                @traduction('interface.bibliotheque.merci_de_patienter')
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
        <span class="fa fa-upload"></span>@traduction('interface.bibliotheque.deposez_vos_fichiers_ici')
    </div>
</div>