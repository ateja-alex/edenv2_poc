<a @click="element_id_historique = ligne.element.id; modale_historique = true;">
    <span class="btn btn-mini btn-xs btn-default css_icone_option_dans_liste">
        <span class="fas fa-history" data-toggle="tooltip"
              :title="$root.traduction('interface.listes.historique')">
        </span>
    </span>
</a>

@push('modales')
    <template v-if="modale_historique">
        <transition name="modal">
            <div class="modal-mask">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">@traduction('interface.listes.historique')</h5>
                            <button type="button" class="close" @click="element_id_historique = null; modale_historique = false;">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <historique ref="historique" :type_element="type_element" :element_id="element_id_historique"></historique>
                        </div>
                        <div class="modal-footer">
                            <div class="btn btn-primary" @click="element_id_historique = null; modale_historique = false;">@traduction('interface.listes.fermer')</div>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </template>
@endpush

@push('donnees_pour_vuejs_data')
    modale_historique:false,
    element_id_historique : null,
@endpush