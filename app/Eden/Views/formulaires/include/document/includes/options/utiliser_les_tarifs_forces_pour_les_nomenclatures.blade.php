<span   class="mb-1 css_btn_action_article_document fa fa-usd"
        v-if="article_sur_document.modele && (article_sur_document.modele.type_article == 1 || article_sur_document.modele.type_article == 3)"
        style="cursor:pointer;margin-bottom: 0px !important; margin-right: 2px;"
        :title="traduction('interface.document.tableau_des_articles.appliquer_tarif_force')"
        @click="afficher_tarif_force(article_sur_document)"></span>
