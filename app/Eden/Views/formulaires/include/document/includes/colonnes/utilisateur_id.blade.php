<div class="cellule_document_colonne_article">

    <template v-if="{{ $edition_ligne }}">
        @php
            $champ_utilisateur = management($management->_type_element.'_lignes')->champ('utilisateur_id')
            ->vmodel(true,'article_sur_document');
        @endphp

        {!! $champ_utilisateur->cree() !!}
    </template>

    <span v-else class="css_lecture_ligne css_input_article_document" v-html="$root.libelle_element_lecture('utilisateur', article_sur_document.utilisateur_id)"></span>
</div>
