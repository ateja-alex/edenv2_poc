<span  class="mb-1 css_btn_action_article_document fa fa-comment-dots" v-if="article_sur_document.description == undefined"
       @click="ajouter_description_article_avec_ouverture_modale(article_sur_document,article_index);"
       style="cursor:pointer; margin-bottom: 0px !important; margin-right: 2px;"
       :title="traduction('interface.document.tableau_des_articles.ajouter_description')">
</span>