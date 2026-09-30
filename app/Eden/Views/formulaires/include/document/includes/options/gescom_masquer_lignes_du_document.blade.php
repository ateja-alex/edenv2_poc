<span class="mb-1 css_btn_action_article_document" :title="traduction('interface.document.tableau_des_articles.masquer_ligne_pdf')" @click="$set(article_sur_document,'masquer_ligne', article_sur_document.masquer_ligne == 1 ? 0 : 1)">
    <span class="fa fa-eye-slash"></span>
    <span class="badge badge-danger" style="padding: 2px; position: absolute; margin-top: -19px; margin-left: 19px;" v-show="article_sur_document.masquer_ligne != 1">@traduction('document.tableau_des_articles.off')</span>
    <span class="badge badge-success" style="padding: 2px; position: absolute; margin-top: -19px; margin-left: 19px;" v-show="article_sur_document.masquer_ligne == 1">@traduction('document.tableau_des_articles.on')</span>
</span>