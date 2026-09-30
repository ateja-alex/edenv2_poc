@push('modales')

    <template v-if="modale_previsualisation_pdf">
        <transition name="modal" >
            <div class="modal-mask">
                <div class="modal-dialog modal-lg" style="height:93vh">
                    <div class="modal-content" style="height: 100%;">

                        <div class="modal-header">
                            <h5 class="modal-title">@traduction('document.modale_previsualisation_pdf_pre_validation.titre')</h5>
                        </div>

                        <iframe class="modal-body" :src="chemin_pdf" style="padding: unset;max-height:unset;" width='100%' height='550' align='middle'></iframe>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="modale_previsualisation_pdf = false">@traduction('interface.modales.fermer')</button>
                            <a onclick="loading(true)" href="{{ route('document.valider', [$management->_type_element, $management->modele->id]) }}" type="button" class="btn btn-primary">@traduction('interface.modales.valider')</a>
                        </div>

                    </div>
                </div>
            </div>
        </transition>
    </template>
@endpush

@push('donnees_pour_vuejs_data')
    modale_previsualisation_pdf : false,
    chemin_pdf : '',
@endpush