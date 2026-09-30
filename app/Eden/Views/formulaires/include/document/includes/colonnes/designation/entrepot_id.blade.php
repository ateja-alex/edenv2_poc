<!-- Choix entrepot pour les BL vente -->
@if(isset($colonnes_articles_enregistrement['entrepot_id']) && $colonnes_articles_enregistrement['entrepot_id'])
    @if($management->_type_element == 'bl_vente' || $management->_type_element == 'commande_vente')

        <label for="" class="cellule_document_colonne_article w-100">
            <span>@traduction('document.colonnes.designation.entrepot_id.titre')</span>
            @if($articles_modifiables === true && empty($recapitulatif))
                <select v-model="article_sur_document.entrepot_id" id="">
                    <template v-for="entrepot in valeurs_listes_formatees[8]">
                        <option :value="entrepot.id_valeur">@{{ entrepot.valeur }}</option>
                    </template>
                </select>
            @else
                <select v-model="article_sur_document.entrepot_id" id="" disabled>
                    <template v-for="entrepot in valeurs_listes_formatees[8]">
                        <option :value="entrepot.id_valeur">@{{ entrepot.valeur }}</option>
                    </template>
                </select>
            @endif
        </label>
    @endif
@endif