{{-- PU --}}
<div class="cellule_document_colonne_article">

    @php
        $edition_tarif = $edition_ligne;

        if(\Illuminate\Support\Str::contains($management->_type_element,'_vente') && fonctionnalite('modification_prix_unitaire_vente') != 0)
            $edition_tarif = 'false';
    @endphp

    <champ-montant v-if="{{ $edition_tarif }}"
            class_input="css_input_article_document"
            :style_input="(article_sur_document.modele && article_sur_document.modele.type_article && article_sur_document.modele.type_article==1) || utilisation_devise_etrangere(false,'tarif') ? 'background: #f4f4f4;color: #a4a4a4;' : ''"
            :modele="article_sur_document"
            nom_sql="tarif"
            :valeur_non_vide="true"
            :lecture_seule="((article_sur_document.modele && article_sur_document.modele.type_article && article_sur_document.modele.type_article==1) || utilisation_devise_etrangere(false,'tarif')) @if(!empty(moi_extranet())) || disable_champs_extranet @endif">
    </champ-montant>
    <span v-else class="css_lecture_ligne css_prix_ligne_article_document css_input_article_document">@{{article_sur_document.tarif | montant}}</span>


    <!-- nomenclature -->
    <template v-for="(article_nomenclature, nomenclature_index) in article_sur_document.nomenclature" v-if="article_sur_document.afficher_nomenclature">
        @if($articles_modifiables === true && empty($recapitulatif))

            <champ-montant
                    class_input="css_input_article_document"
                    :style_input="!(article_nomenclature.type_article != 1 && article_sur_document.type_article != 3 &&  !utilisation_devise_etrangere(false,'tarif')) ? 'background: #f4f4f4;color: #a4a4a4;' : ''"
                    :modele="article_nomenclature"
                    nom_sql="tarif"
                    :valeur_non_vide="true"
                    :lecture_seule="!(article_nomenclature.type_article != 1 && article_sur_document.type_article != 3 && !utilisation_devise_etrangere(false,'tarif'))">
            </champ-montant>

        @else
            @{{ article_nomenclature.tarif | montant}}
        @endif

        <span :class="{css_retour_ligne_apres_element: article_nomenclature.nomenclature && sous_nomenclature_index == article_nomenclature.nomenclature.length - 1 && article_nomenclature.modele.type_article == 1}" v-for="(sous_nomenclature, sous_nomenclature_index) in article_nomenclature.nomenclature" v-if="article_nomenclature.afficher_nomenclature">

            @if($articles_modifiables === true && empty($recapitulatif))

                <champ-montant
                        class_input="css_input_article_document"
                        :style_input="!(sous_nomenclature.type_article != 1 && !utilisation_devise_etrangere(false,'tarif')) ? 'background: #f4f4f4;color: #a4a4a4;' : ''"
                        :modele="sous_nomenclature"
                        nom_sql="tarif"
                        :valeur_non_vide="true"
                        :lecture_seule="!(sous_nomenclature.type_article != 1 && article_sur_document.type_article != 3 && article_nomenclature.type_article != 3 && !utilisation_devise_etrangere(false,'tarif'))">
                </champ-montant>
            @else
                @{{ sous_nomenclature.tarif | montant}}
            @endif

        </span>
        <span :class="{css_retour_ligne_apres_element: article_nomenclature.nomenclature && article_nomenclature.nomenclature.length == 0 && article_nomenclature.modele.type_article == 1}" v-if="article_nomenclature.afficher_nomenclature && article_nomenclature.modele.type_article == 1"></span>

    </template>
</div>

@if(empty($recapitulatif))

@push('donnees_pour_vuejs_methods')

    changement_tarif(element){

        if(this.utilisation_devise_etrangere())
            this.calcul_montant_devise_par_montant(element,'tarif');

        this.modification_prix_de_vente(element)
    },

@endpush

@push('donnees_pour_vuejs_mounted')

    this.$on('maj_champ_montant',(donnees) => {

        if(donnees.nom_sql != 'tarif')
            return;

        this.changement_tarif(donnees.modele);
    });
@endpush

@endif
