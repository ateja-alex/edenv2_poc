{{-- Total HT --}}
<div class="cellule_document_colonne_article" >
    <span class="eco_contribution_inclu_bloc">
        @php
            $edition_total = fonctionnalite('modification_total_hors_taxe_article') === true ? $edition_ligne : 'false';
        @endphp

        <champ-montant v-if="{{ $edition_total }}"
                class_input="css_input_article_document"
                :modele="article_sur_document"
                :valeur_non_vide="true"
                nom_sql="total">
        </champ-montant>
        <span v-else class="css_lecture_ligne css_prix_ligne_article_document css_input_article_document">@{{ article_sur_document.total | montant }}</span>
        <template v-if="eco_contribution_active">
            <span class="eco_contribution_inclu" v-if="calcul_eco_contribution(article_sur_document) > 0" v-html="traduction('document.eco_contribution_inclus',null,[$options.filters.montant(calcul_eco_contribution(article_sur_document))])">
            </span>
        </template>
    </span>
    <!-- nomenclature -->
    <template v-for="(article_nomenclature, nomenclature_index) in article_sur_document.nomenclature" v-if="article_sur_document.afficher_nomenclature">

        <span class="eco_contribution_inclu_bloc">
            @if(fonctionnalite('modification_total_hors_taxe_article') === true)
                <champ-montant
                        class_input="css_input_article_document"
                        :modele="article_nomenclature"
                        :valeur_non_vide="true"
                        nom_sql="total">
                </champ-montant>
            @else
                <span class="css_prix_ligne_article_document_nomenclature css_input_article_document">
                    @{{ article_nomenclature.total | montant }}
                </span>

            @endif

            <template v-if="eco_contribution_active">
                <span class="eco_contribution_inclu" v-if="calcul_eco_contribution(article_nomenclature) > 0" v-html="traduction('document.eco_contribution_inclus',null,[$options.filters.montant(calcul_eco_contribution(article_nomenclature))])"></span>
            </template>
        </span>

        <span :class="{css_retour_ligne_apres_element: article_nomenclature.nomenclature && sous_nomenclature_index == article_nomenclature.nomenclature.length - 1 && article_nomenclature.modele.type_article == 1}" v-for="(sous_nomenclature, sous_nomenclature_index) in article_nomenclature.nomenclature" v-if="article_nomenclature.afficher_nomenclature">

            <span class="eco_contribution_inclu_bloc">
                @if(fonctionnalite('modification_total_hors_taxe_article') === true)
                    <champ-montant
                            class_input="css_input_article_document"
                            :modele="sous_nomenclature"
                            :valeur_non_vide="true"
                            nom_sql="total">
                    </champ-montant>
                @else
                    <span class="css_prix_ligne_article_document_sous_nomenclature css_input_article_document">
                        @{{ sous_nomenclature.total | montant }}
                    </span>
                @endif

                <template v-if="eco_contribution_active">
                    <span class="eco_contribution_inclu" v-if="calcul_eco_contribution(sous_nomenclature) > 0" v-html="traduction('document.eco_contribution_inclus',null,[$options.filters.montant(calcul_eco_contribution(sous_nomenclature))])"></span>
                </template>
            </span>
        </span>

        <span :class="{css_retour_ligne_apres_element: article_nomenclature.nomenclature && article_nomenclature.nomenclature.length == 0 && article_nomenclature.modele.type_article == 1}" v-if="article_nomenclature.afficher_nomenclature && article_nomenclature.modele.type_article == 1"></span>
    </template>
</div>

@if(empty($recapitulatif))
    @push('donnees_pour_vuejs_mounted')

        this.$on('maj_champ_montant',(donnees) => {

            if(donnees.nom_sql != 'total')
                return;

            this.corrige_virgule(donnees.modele,'total');
            this.mise_a_jour_remise_document_vue(donnees.modele);
        });
    @endpush
@endif