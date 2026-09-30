{{-- Disponibilité --}}
<template v-if="colonnes_articles.designation.sous_colonnes != undefined && colonnes_articles.designation.sous_colonnes.disponibilite != undefined">
    @if($articles_modifiables === true && empty($recapitulatif))
    <span class="css_ajouter_ligne_nomenclature" v-if="article_sur_document.disponibilite == undefined" @click="ajouter_disponibilite_article(article_sur_document)">@traduction('document.colonnes.designation.disponibilite.ajouter')</span>
    @endif
    <label for="" class="cellule_document_colonne_article w-100" v-if="article_sur_document.disponibilite != undefined">
        <span>@traduction('document.colonnes.designation.disponibilite.titre')</span>
        @if($articles_modifiables === true && empty($recapitulatif))
            <span class="css_ajouter_ligne_nomenclature" v-if="article_sur_document.disponibilite != undefined" @click="masquer_disponibilite_sur_document(article_sur_document)"><i class="far fa-trash-alt"></i> @traduction('document.colonnes.designation.disponibilite.supprimer')</span>
            <input type="text" class="css_input_article_document" v-model="article_sur_document.disponibilite">
        @else
            <br/>@{{article_sur_document.disponibilite}}
        @endif
    </label>
</template>