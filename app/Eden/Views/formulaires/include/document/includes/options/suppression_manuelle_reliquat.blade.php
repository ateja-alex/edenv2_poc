<div v-if="article_sur_document.id > 0 && (article_sur_document.reliquat_reception != 0 || article_sur_document.suppression_manuelle_reliquat == 1)" 
    :title="traduction('document.suppression_manuelle_reliquat.etat_'+(article_sur_document.suppression_manuelle_reliquat == 1 ? 0 : 1))" 
    class="mb-1 css_btn_action_article_document suppression_manuelle_reliquat"
    @click="suppression_manuelle_reliquat([article_sur_document], article_sur_document.suppression_manuelle_reliquat == 1 ? 0 : 1)">
    <i class="fas fa-truck-loading"></i>
    <i v-if="article_sur_document.suppression_manuelle_reliquat == 1" class="fa fa-undo"></i>
    <i v-else class="fa fa-times"></i>
</div>

@push('donnees_pour_vuejs_methods')

    suppression_manuelle_reliquat : function(articles,etat, selection_lignes = false){

        loading(true);

        $.post({
            url : '{{ route("document.achat.commande.suppression_manuelle_reliquat") }}',
            data : {
                ids_lignes : articles.map(article => article.id),
                suppression_manuelle_reliquat : etat,
                id_document : this.document.id,
            },
            dataType : 'json'
        }).done((retour) => {

            if(!retour.succes)
                alert(retour.message);
            else
                toastr.success(this.traduction('document.actions.suppression_manuelle_reliquat.retour_etat_'+etat));

            loading(false);
            this.operations_document_post_modification(retour);

            if(selection_lignes)
                this.lignes_selectionnes = [];
        });
    },

@endpush