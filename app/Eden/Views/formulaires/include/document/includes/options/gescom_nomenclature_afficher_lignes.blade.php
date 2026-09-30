<span  class="mb-1 css_btn_action_article_document" 
    v-if="article_sur_document.modele && (article_sur_document.modele.type_article == 1 || article_sur_document.modele.type_article == 3)" 
    :class="{ 'fas fa-toggle-off': !article_sur_document.afficher_nomenclature, 'fas fa-toggle-on': article_sur_document.afficher_nomenclature }"
    :title="(article_sur_document.afficher_nomenclature) ? traduction('interface.document.tableau_des_articles.masquer_articles') : traduction('interface.document.tableau_des_articles.afficher_articles')" 
    @click.prevent="$set(article_sur_document,'afficher_nomenclature',!article_sur_document.afficher_nomenclature)">
</span>