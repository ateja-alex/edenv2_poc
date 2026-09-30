{{-- Dossier --}}
<div class="col-md-2 droppable" v-for="(dossier, index) in dossiers" :draggable="moi_extranet === null ? true : false" @dblclick="deplacement_dans_un_dossier(dossier.id)"
     @dragstart="startDrag($event, dossier,'dossier')"  @dragend="endDrag()"
     @drop="onDrop($event,dossier)"  @dragover.prevent @dragenter.prevent
     @mouseenter="element_affichage_options = 'dossier_' + dossier.id;" @mouseleave="element_affichage_options = null;">
    <div class="css_block_element_biblio">
        <span v-if="element_affichage_options == 'dossier_' + dossier.id && moi_extranet === null" style="position: absolute;z-index: 2;top: -10px;right: -10px;">
            <vue-custom-tooltip position="is-left" :label="dossier.disponible_extranet == true ? $root.traduction('interface.bibliotheque.rendre_indisponible_extranet') : $root.traduction('interface.bibliotheque.rendre_disponible_extranet')" >
                <span class="bulle_option" @click="dispo_extranet(dossier.disponible_extranet == true ? 0:1, 'dossier_bibliotheque', dossier.id, index)">
                    <i :class="dossier.disponible_extranet == true ? 'fas fa-lock-open' : 'fas fa-lock'"></i>
                </span>
            </vue-custom-tooltip>

        </span>
        <div class="css_apercu_biblio">
            <span class="fas fa-folder-open dossier"></span>
            <span class="css_nom_dossier_biblio">@{{dossier.nom}}</span>
        </div>
        <div class="css_desc_biblio">
            <span class="css__lien css_nom_fichier_biblio">
                @{{dossier.nom}}
            </span>
            <span class="css_info_biblio">
                <i class="fab fa-google-drive" v-if="dossier.gdrive"></i>
                @traduction('interface.bibliotheque.dossier')
            </span>
        </div>  
    </div>
</div>