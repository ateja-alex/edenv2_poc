<span class="mb-1 css_btn_action_article_document" :title="traduction('interface.document.tableau_des_articles.afficher_photo_pdf')" @click="if(article_sur_document.afficher_photo == 1) article_sur_document.afficher_photo = 0; else article_sur_document.afficher_photo = 1;">
    <span class="fa fa-image"></span>
    <span class="badge badge-danger" style="padding: 2px; position: absolute; margin-top: -19px; margin-left: 19px;" v-show="article_sur_document.afficher_photo != 1">@traduction('document.tableau_des_articles.off')</span>
    <span class="badge badge-success" style="padding: 2px; position: absolute; margin-top: -19px; margin-left: 19px;" v-show="article_sur_document.afficher_photo == 1">@traduction('document.tableau_des_articles.on')</span>
</span>