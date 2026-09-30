{{-- Dossier --}}
<div class="col-md-2 droppable" v-for="dossier in dossiers" draggable @dblclick="deplacement_dans_un_dossier(dossier.id)"
     @dragstart='startDrag($event, dossier,"dossier")'  @dragend="endDrag()"
     @drop='onDrop($event,dossier)'  @dragover.prevent @dragenter.prevent>
    <div class="css_block_element_biblio">
        <div class="css_apercu_biblio">
            <span class="fas fa-folder-open dossier" v-if="dossier.nombre_de_fichiers_enfants > 0"></span>
            <span class="fas fa-folder dossier" v-else></span>
            <span v-if="dossier.nombre_de_fichiers_enfants !== undefined" class="badge bg-danger text-light" style="font-size: 15px; position: absolute; top: 20%; left: 60%; transform: translate(-50%, -50%);">@{{ dossier.nombre_de_fichiers_enfants }}</span>
            <span class="css_nom_dossier_biblio">@{{dossier.nom}}</span>
        </div>
        <div class="css_desc_biblio">
            <span class="css__lien css_nom_fichier_biblio">
                @{{dossier.nom}}
            </span>
            <span class="css_info_biblio">
                <i class="fab fa-google-drive" v-if="dossier.gdrive"></i>
                @traduction('composant.gestion_pieces_jointes.dossier')
            </span>
        </div>  
    </div>
</div>