<template v-if="modale_transformer_pourcentage">
    <transition name="modal">
        <div class="modal-mask">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">@traduction('document.actions.transformations_possibles.transformation_devis_facture')</h5>
                        <button type="button" class="close" @click="modale_transformer_pourcentage = false" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('document.transformer', ['type_element' => $management->_type_element, 'id' => $management->modele->id, 'type_element_transformation' => 'facture_vente']) }} "
                          method="post">

                        <div class="modal-body">
                            <span style="font-size:14px;">@traduction('document.actions.transformations_possibles.transformation_devis_facture_a_hauteur_de')</span>
                            <input type="number" name="pourcentage_facture" class="form-control" style="width:70px;display: inline;" value="100" max="100" min="0" @wheel.prevent @keydown.up.prevent @keydown.down.prevent>
                            <span style="font-size:16px;">%</span>
                        </div>
                        <div class="modal-footer">

                            <button type="button" class="btn btn-secondary" @click="modale_transformer_pourcentage = false">@traduction('interface.modales.annuler')</button>
                            <button role="submit" class="btn btn-secondary" onClick="loading()">@traduction('document.actions.transformations_possibles.transformation_facture')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </transition>
</template>

@push('donnees_pour_vuejs_data')

    modale_transformer_pourcentage : false,
@endpush
