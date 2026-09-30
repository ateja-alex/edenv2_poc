{{-- PU devise--}}
<div class="cellule_document_colonne_article" v-if="{{$colonnes_articles['tarif_devise']['condition_v_if']}}" >

    @php
        $edition_tarif_devise = $edition_ligne;

        if(\Illuminate\Support\Str::contains($management->_type_element,'_vente') && fonctionnalite('modification_prix_unitaire_vente') != 0)
            $edition_tarif_devise = 'false';
    @endphp

    <champ-montant v-if="{{ $edition_tarif_devise }}"
            class_input="css_input_article_document"
            :style_input="article_sur_document.modele && article_sur_document.modele.type_article && article_sur_document.modele.type_article==1 ? 'background: #f4f4f4;color: #a4a4a4;' : ''"
            :modele="article_sur_document"
            nom_sql="tarif_devise"
            :valeur_non_vide="true"
            :lecture_seule="article_sur_document.modele && article_sur_document.modele.type_article && article_sur_document.modele.type_article==1">
    </champ-montant>
    <span v-else class="css_lecture_ligne css_prix_ligne_article_document css_input_article_document">@{{article_sur_document.tarif_devise | montant(code_devise) }}</span>

    <!-- nomenclature -->
    <template v-for="(article_nomenclature, nomenclature_index) in article_sur_document.nomenclature" v-if="article_sur_document.afficher_nomenclature">
        @if($articles_modifiables === true && empty($recapitulatif))

            <champ-montant
                    class_input="css_input_article_document"
                    :style_input="!(article_nomenclature.type_article != 1 && article_nomenclature.type_article != 3) ? 'background: #f4f4f4;color: #a4a4a4;' : ''"
                    :modele="article_nomenclature"
                    nom_sql="tarif_devise"
                    :valeur_non_vide="true"
                    :lecture_seule="!(article_nomenclature.type_article != 1 && article_nomenclature.type_article != 3)"
                    :parametres_emit="{
                            article_parent : article_sur_document
                        }">
            </champ-montant>
        @else
            @{{ article_nomenclature.tarif_devise | montant(code_devise) }}
        @endif

        <span :class="{css_retour_ligne_apres_element: article_nomenclature.nomenclature && sous_nomenclature_index == article_nomenclature.nomenclature.length - 1 && article_nomenclature.modele.type_article == 1}" v-for="(sous_nomenclature, sous_nomenclature_index) in article_nomenclature.nomenclature" v-if="article_nomenclature.afficher_nomenclature">

            @if($articles_modifiables === true && empty($recapitulatif))
                <champ-montant
                        class_input="css_input_article_document"
                        :style_input="sous_nomenclature.type_article == 1 ? 'background: #f4f4f4;color: #a4a4a4;' : ''"
                        :modele="sous_nomenclature"
                        nom_sql="tarif_devise"
                        :valeur_non_vide="true"
                        :lecture_seule="sous_nomenclature.type_article == 1"
                        :parametres_emit="{
                            article_parent : article_sur_document,
                            article_nomenclature : article_nomenclature,
                        }">
                </champ-montant>
            @else
                @{{ sous_nomenclature.tarif_devise | montant(code_devise) }}
            @endif

        </span>
        <span :class="{css_retour_ligne_apres_element: article_nomenclature.nomenclature && article_nomenclature.nomenclature.length == 0 && article_nomenclature.modele.type_article == 1}" v-if="article_nomenclature.afficher_nomenclature && article_nomenclature.modele.type_article == 1"></span>

    </template>
</div>

@if(empty($recapitulatif))

    @push('donnees_pour_vuejs_mounted')

        this.$on('maj_champ_montant',(donnees) => {

            if(donnees.nom_sql != 'tarif_devise')
                return;

            this.calcul_montant_par_montant_devise(donnees.modele,donnees.article_parent,donnees.article_nomenclature);
        });
    @endpush
@endif
