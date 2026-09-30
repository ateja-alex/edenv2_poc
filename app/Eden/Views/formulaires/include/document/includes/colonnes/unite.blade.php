{{-- Unité --}}
<div class="cellule_document_colonne_article">

    @if(fonctionnalite('utiliser_conditionnement'))

        <select v-if="{{ $edition_ligne }}" v-model="article_sur_document.conditionnement" :disabled="article_sur_document.modele && article_sur_document.modele.type_article == 1" @change="changement_total(article_sur_document)" class="css_input_article_document">

            <template v-for="(conditionnement,index) in article_sur_document.conditionnement_possible">
                <option :value="index">@{{ conditionnement.affichage }}</option>
            </template>

        </select>

        <span v-else class="css_lecture_ligne css_prix_ligne_article_document_nomenclature">@{{ article_sur_document.conditionnement_possible && article_sur_document.conditionnement_possible[article_sur_document.conditionnement ?? 0] != undefined ? article_sur_document.conditionnement_possible[article_sur_document.conditionnement ?? 0].affichage : '' }}</span>

    @else
        <template v-if="article_sur_document.conditionnement_possible && article_sur_document.conditionnement_possible[0] != undefined">

            <div class="css_prix_ligne_article_document_nomenclature">
                @{{ article_sur_document.conditionnement_possible[0].affichage }}
            </div>
        </template>
        <template v-else>
            @traduction('document.colonnes.unite.titre')
        </template>

    @endif

    <template v-for="(article_nomenclature, nomenclature_index) in article_sur_document.nomenclature" v-if="article_sur_document.afficher_nomenclature">

        @if($articles_modifiables === true && empty($recapitulatif))
        <select v-model="article_nomenclature.conditionnement" :disabled="article_nomenclature.type_article == 1" :class="{css_champ_readonly: contenu_produit_assemble(article_sur_document)}" :readonly="contenu_produit_assemble(article_sur_document)" @change="changement_total(article_nomenclature, article_sur_document)" class="css_input_article_document ">

            <template v-for="(conditionnement,index) in article_nomenclature.conditionnement_possible">
                <option :value="index">@{{ conditionnement.affichage }}</option>
            </template>

        </select>
        @else
            <template v-if="article_nomenclature.conditionnement_possible[article_nomenclature.conditionnement] != undefined">
                @{{ article_nomenclature.conditionnement_possible[article_nomenclature.conditionnement ?? 0].affichage }}
            </template>
        @endif

        <span :class="{css_retour_ligne_apres_element: article_nomenclature.nomenclature && sous_nomenclature_index == article_nomenclature.nomenclature.length - 1 && article_nomenclature.modele.type_article == 1}" v-for="(sous_nomenclature, sous_nomenclature_index) in article_nomenclature.nomenclature" v-if="article_nomenclature.afficher_nomenclature">

            @if($articles_modifiables === true && empty($recapitulatif))
            <select v-model="sous_nomenclature.conditionnement" :disabled="sous_nomenclature.type_article == 1" :class="{css_champ_readonly: contenu_produit_assemble(article_nomenclature, article_sur_document)}" :readonly="contenu_produit_assemble(article_nomenclature, article_sur_document)" @change="changement_total(sous_nomenclature,article_sur_document)" class="css_input_article_document ">

                <template v-for="(conditionnement,index) in sous_nomenclature.conditionnement_possible">
                    <option :value="index">@{{ conditionnement.affichage }}</option>
                </template>

            </select>
            @else
                <template v-if="sous_nomenclature.conditionnement_possible[sous_nomenclature.conditionnement] != undefined">
                    @{{ sous_nomenclature.conditionnement_possible[sous_nomenclature.conditionnement ?? 0].affichage }}
                </template>
            @endif

        </span>
        <span :class="{css_retour_ligne_apres_element: article_nomenclature.nomenclature && article_nomenclature.nomenclature.length == 0 && article_nomenclature.modele.type_article == 1}" v-if="article_nomenclature.afficher_nomenclature && article_nomenclature.modele.type_article == 1"></span>
    </template>
</div>
