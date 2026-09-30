@if($contexte == 'fiche')
    <i class="css_action_icon primaire fa fa-fw fa-copy"
       @click="article_a_copier = {article_id :0};modale_copie_categories_comptables = true"
       :title="traduction('modules_sur_fiche.article_categorie_comptable.importer')"
       data-placement="left" data-toggle="tooltip"
    ></i>
@endif

@push('modales')
<template v-if="modale_copie_categories_comptables">
    <transition name="modal">
        <div class="modal-mask">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">@traduction('interface.listes.article_categorie_comptable.importer.modale.titre')</h5>
                        <button type="button" class="close" @click="modale_copie_categories_comptables = false" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="height: 100px;">
                        <form action="#" id="formulaire_copier_article_contenu_pack" method="post" class="css_form">
                            <div class="row">
                                <div class="col-sm-2">@traduction('interface.listes.article_categorie_comptable.importer.modale.article')</div>
                                <div class="col-sm-10">
                                    <champ-selection-element :type_element="'article'" :nom_sql="'article_id'" name="article_id" :modele="article_a_copier"></champ-selection-element>
                                </div>
                            </div>
                        </form>
                        @if($contexte == 'liste')
                            <span style="margin-top:15px;" class="btn btn-xs btn-primary js_action_lignes_selectionnees" :class="this.liste.lignes_selectionnees.length == 0 ? 'disabled ' : ''" @click="eden_copier_elements_selectionnes()">@traduction('interface.listes.accepter_tous_les_documents_selectionnes') (<span class="js_nombre_lignes_selectionnees" v-html="this.liste.lignes_selectionnees.length"></span>)</span><br/><br/>
                            <span class="btn btn-xs btn-primary" @click="eden_copier_tous_les_elements()">@traduction('interface.listes.accepter_tous_les_documents')</span><br/>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="modale_copie_categories_comptables = false">@traduction('interface.listes.fermer')</button>
                        @if($contexte == 'fiche')
                            <button type="button" class="btn btn-primary" @click="copier_categories_comptables_article([element_id])">@traduction('interface.listes.enregistrer')</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>
@endpush

@push('donnees_pour_vuejs_data')

    article_a_copier : {
        article_id :0,
    },
    modale_copie_categories_comptables : false,
@endpush
@push('donnees_pour_vuejs_methods')

    copier_categories_comptables_article: function(ids_articles) {

        loading(true);

        $.post({

            url: '/eden/article/copier_categories_comptables_article',
            dataType: "json",
            data: {
                id_article_a_copier : this.article_a_copier.article_id,
                ids_articles : ids_articles
            }
        }).done((donnees) => {

            if(donnees.retour !== true) {

                loading(false);

                erreur(donnees.retour);
                return;
            }

            this.modale_copie_categories_comptables = false;

            @if($contexte == 'fiche')
                this.$refs.liste_libre_{{$id_liste}}.actualisation_filtres();
            @else
                this.actualisation_filtres();
            @endif

            loading(false)
        });
    },

@endpush