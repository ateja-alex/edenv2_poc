<formulaire-fiche ref="formulaire_edition_element" :route="'{{ route('base_eden.element.enregistrer', ['article', $article->id]) }}'" :type_element="'{{ $management_element->_type_element }}'" :element_id="{{ $management_element->modele->id }}">
    <template slot="titre">@{{ article.designation }}</template>
</formulaire-fiche>