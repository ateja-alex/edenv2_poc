{{-- Total TTC --}}
<div class="cellule_document_colonne_article">
    <span class="css_prix_ligne_article_document css_input_article_document">
        @{{ article_sur_document.total_ttc | montant }}
    </span>
    <!-- nomenclature -->
    <template v-for="(article_nomenclature, nomenclature_index) in article_sur_document.nomenclature" v-if="article_sur_document.afficher_nomenclature">

        <span class="css_prix_ligne_article_document_nomenclature css_input_article_document" >
            @{{ article_nomenclature.total_ttc | montant }}
        </span>

        <span :class="{css_retour_ligne_apres_element: article_nomenclature.nomenclature && sous_nomenclature_index == article_nomenclature.nomenclature.length - 1 && article_nomenclature.modele.type_article == 1}" v-for="(sous_nomenclature, sous_nomenclature_index) in article_nomenclature.nomenclature" v-if="article_nomenclature.afficher_nomenclature">

            <span class="css_prix_ligne_article_document_sous_nomenclature css_input_article_document">
                @{{ sous_nomenclature.total_ttc | montant }}
            </span>

        </span>
        <span :class="{css_retour_ligne_apres_element: article_nomenclature.nomenclature && article_nomenclature.nomenclature.length == 0 && article_nomenclature.modele.type_article == 1}" v-if="article_nomenclature.afficher_nomenclature && article_nomenclature.modele.type_article == 1"></span>

    </template>
</div>