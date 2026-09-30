{{-- Fin de Regroupement --}}
{{-- Select & Move --}}
<div class="document_ligne_drag_drop" :style="retourne_couleur_regroupement_pour_ligne(article_sur_document, ['left', 'bottom'])">
    @if(empty($recapitulatif))
        <div class="css_flex_actions_article_document">
            <div class="mb-1 css_btn_action_article_document document_ligne_drag_drop_conteneur_coller" 
                :title="traduction('document.blocs.saisie_des_articles.actions.coller_ligne')" 
                v-if="elements_a_coller !== false" @click="coller_lignes(article_sur_document.index_article)">
                <i class="fa fa-paste"></i>
                <i class="fa fa-arrow-down"></i>
            </div>
        </div>
    @endif
</div>
<div class="document_contenu_ligne_diverse" :style="retourne_couleur_regroupement_pour_ligne(article_sur_document, ['bottom'])">
    <div class="css_sous_total_articles_document d-flex">
        <span class="document_ligne_regroupement_champs_crochet">]</span>
        <label for="" class="css_label_input_article_document w-100 d-flex">
            <span>@traduction('document.lignes_diverses.regroupement_fermeture.titre')</span>
            <span v-html="nom_regroupement(article_sur_document.regroupement_id)"></span>
        </label>
    </div>
</div>
{{-- Afficher & Supprimer --}}
<div class="document_ligne_options" :style="retourne_couleur_regroupement_pour_ligne(article_sur_document, ['bottom', 'right'])">
    @if(empty($recapitulatif))
        <div class="css_actions_article_document">
            @include('eden::formulaires.include.document_style_ligne_document')
        </div>
    @endif
</div>