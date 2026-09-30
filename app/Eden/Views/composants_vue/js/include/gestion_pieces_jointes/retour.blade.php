{{-- Back --}}
<div class="col-md-2 droppable" v-if="!_.isEmpty(dossier_parent)"  @drop='onDrop($event,dossier_parent.dossier_parent)' @dragover.prevent @dragenter.prevent
     @dblclick="dossier_parent.dossier_parent == null ? deplacement_dans_un_dossier(null) : deplacement_dans_un_dossier(dossier_parent.dossier_parent.id, true)">
    <div class="css_block_element_biblio">
        <div class="css_apercu_biblio retour">
            <span class="fas fa-arrow-left retour"></span>
            @traduction('composant.gestion_pieces_jointes.retour')
        </div> 
    </div>
</div>