<span v-if="article_sur_document.id > 0" class="mb-1 css_btn_action_article_document" @click="affichage_historique_ligne(article_sur_document.id)" :title="traduction('composant.historique.titre')">
    <i class="fa fa-history"></i>
</span>

@push('modales')
    <template v-if="modale_historique_lignes">
        <transition name="modal">
            <div class="modal-mask">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <historique ref="historique" :type_element="$root.type_element+'_lignes'" :element_id="ligne_id_historique"></historique>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="modale_historique_lignes = false">@traduction('interface.modales.fermer')</button>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </template>
@endpush

@push('donnees_pour_vuejs_data')
    modale_historique_lignes : false,
    ligne_id_historique : false,
@endpush

@push('donnees_pour_vuejs_methods')
    affichage_historique_ligne : function(ligne_id){
        this.ligne_id_historique = ligne_id;
        this.modale_historique_lignes = true;
    },
@endpush