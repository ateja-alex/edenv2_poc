<!-- description -->
<div class="document_description_ligne w-100" v-show="article_sur_document.description != undefined">

    <span>@traduction('document.colonnes.designation.description.titre')</span>
    
    <span
        v-if="{{ $edition_ligne }}"
        class="css_ajouter_ligne_nomenclature"
        v-if="article_sur_document.description != undefined"
        @click="masquer_description_sur_document(article_sur_document)"
    >
        <i
            class="far fa-trash-alt"
            data-toggle="tooltip"
            data-position="top"
            data-container=".cellule_designation_article"
            data-boundary="window"
            :data-original-title="traduction('document.colonnes.designation.description.supprimer')"
        ></i>
    </span>

    @if(fonctionnalite('description_wysiwyg_documents') === true)

        <span v-if="{{ $edition_ligne }}" @click="article_modification_description = article_sur_document;modale_description_ligne = true;" class="fa fa-pen"></span>
        <div v-if="article_sur_document.description" v-html="article_sur_document.description"></div>
    @else
        <textarea :style="!{{ $edition_ligne }} ? 'border: none;background: transparent;' : ''" name="" id="" cols="30" rows="4" v-model="article_sur_document.description"></textarea>
    @endif
</div>

@if(empty($recapitulatif) && fonctionnalite('description_wysiwyg_documents') === true)

    @push('donnees_pour_vuejs_data')
        article_modification_description : {},
        modale_description_ligne: false,
    @endpush

    @push('modales')
        <!-- modale description wysiwyg article -->
        <template v-if="modale_description_ligne">
            <transition name="modal" >
                <div class="modal-mask">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">@traduction('document.colonnes.designation.description.description_article')</h5>
                                <button type="button" class="close" @click="modale_description_ligne = false;">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <textarea-wysiwyg-vue gestion_mise_a_jour_valeur="Change Undo Redo" :modele="article_modification_description" nom_sql="description"></textarea-wysiwyg-vue>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" @click="modale_description_ligne = false;">@traduction('interface.modales.fermer')</button>
                            </div>
                        </div>
                    </div>
                </div>
            </transition>
        </template>
    @endpush

@endif

@push('donnees_pour_vuejs_methods')
    ajouter_description_article(article) {

		article.description = "";

		vue_instance.$forceUpdate();
	},
	ajouter_description_article_avec_ouverture_modale(article, article_index) {

		this.article_modification_description = article;

		this.modale_description_ligne = true;

		article.description = "";

		vue_instance.$forceUpdate();
	},
    masquer_description_sur_document(article) {

		article.description = null;

		vue_instance.$forceUpdate();
	},
@endpush
