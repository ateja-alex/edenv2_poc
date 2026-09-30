{{-- Numéro de série --}}
<template v-if="colonnes_articles.designation.sous_colonnes != undefined && colonnes_articles.designation.sous_colonnes.numero_de_serie != undefined">
    <label for="" class="cellule_document_colonne_article w-100" v-if="article_sur_document.modele && article_sur_document.modele.type_numero_de_serie == 1 ">
        <span>@traduction('document.colonnes.designation.numero_serie.unique')</span>
        @if($articles_modifiables === true && empty($recapitulatif))
            <input type="text" class="css_input_article_document" v-model="article_sur_document.numero_de_serie">
        @else
            <br/>@{{article_sur_document.numero_de_serie}}
        @endif


    </label>
    <label for="" class="cellule_document_colonne_article w-100" v-if="article_sur_document.modele && article_sur_document.modele.type_numero_de_serie == 2">
        <span>@traduction('document.colonnes.designation.numero_serie.multiple')</span>
        @if($articles_modifiables === true && empty($recapitulatif))
            <input type="text" class="css_input_article_document" v-model="article_sur_document.numero_de_serie">
        @else
            <br/>@{{article_sur_document.numero_de_serie}}
        @endif


    </label>
</template>