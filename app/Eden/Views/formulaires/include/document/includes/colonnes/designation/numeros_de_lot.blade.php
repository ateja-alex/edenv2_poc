{{-- Numéro de lot --}}
<template v-for="(article_lot, lot_index) in article_sur_document.numeros_de_lot">
    <div class="cellule_document_colonne_article w-100 document_conteneur_numeros_lot">
        <span>@traduction('document.colonnes.designation.numeros_lot.titre') @{{lot_index + 1}}</span>
        <span class="css_ajouter_ligne_nomenclature" @click="article_sur_document.numeros_de_lot.splice(lot_index, 1)">
            <i class="far fa-trash-alt"></i> 
            @traduction('document.colonnes.designation.numeros_lot.supprimer')
        </span>

        <!-- numéro de lot -->
        @if($articles_modifiables === true && empty($recapitulatif))
            <div class="row">
                <div class="col-md-6">
                    @traduction('document.colonnes.designation.numeros_lot.numero_lot')
                </div>
                <div class="col-md-6">
                    <input type="text" class="css_input_article_document" v-model="article_lot.numero_de_lot" 
                        :placeholder="traduction('document.colonnes.designation.numeros_lot.numero_lot')" />
                </div>
            </div>

        @else
            @traduction('document.colonnes.designation.numeros_lot.numero') : @{{ article_lot.numero_de_lot }}
        @endif

        <!-- peremption -->
        @if($articles_modifiables === true && empty($recapitulatif))
            <div class="row">
                <div class="col-md-6">
                    @traduction('document.colonnes.designation.numeros_lot.date_peremption')
                </div>
                <div class="col-md-6">
                    <input type="date" class="css_input_article_document" name="date" v-model="article_lot.peremption" 
                        :placeholder="traduction('document.colonnes.designation.numeros_lot.date_peremption')">
                </div>
            </div>
        @else
            @traduction('document.colonnes.designation.numeros_lot.date_peremption') : @{{ article_lot.peremption }}
        @endif

        <!-- quantite -->
        @if($articles_modifiables === true && empty($recapitulatif))
            <div class="row">
                <div class="col-md-6">
                    @traduction('document.colonnes.designation.numeros_lot.quantite')
                </div>
                <div class="col-md-6">
                    <input type="text" class="css_input_article_document" v-model="article_lot.quantite" 
                        :placeholder="traduction('document.colonnes.designation.numeros_lot.quantite')" />
                </div>
            </div>

        @else
            @traduction('document.colonnes.designation.numeros_lot.quantite') : @{{ article_lot.quantite }}
        @endif
    </div>
</template>