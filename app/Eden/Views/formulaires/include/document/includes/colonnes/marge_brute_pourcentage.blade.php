@if($colonnes_articles['marge_brute_pourcentage']['marge_pourcentage_calculable'] == true)
    
    <div class="cellule_document_colonne_article">

        <champ-montant v-if="{{ $edition_ligne }}"
                class_input="css_input_article_document"
                :modele="article_sur_document"
                nom_sql="marge_brute_pourcentage"
                :valeur_non_vide="true"
                :lecture_seule="article_sur_document.modele && (article_sur_document.modele.type_article == 1 || article_sur_document.modele.type_article == 3)">
        </champ-montant>
        <span v-else class="css_lecture_ligne css_prix_ligne_article_document css_input_article_document">@{{article_sur_document.marge_brute_pourcentage}}</span>
        <template v-for="(article_nomenclature, nomenclature_index) in article_sur_document.nomenclature" v-if="article_sur_document.afficher_nomenclature">

            @if($articles_modifiables === true && empty($recapitulatif))
                <champ-montant
                        class_input="css_input_article_document"
                        :modele="article_nomenclature"
                        nom_sql="marge_brute_pourcentage"
                        :valeur_non_vide="true"
                        :lecture_seule="article_nomenclature.type_article == 1 || article_nomenclature.type_article == 3">
                </champ-montant>
            @else
                @{{article_nomenclature.marge_brute_pourcentage}}
            @endif

            <span :class="{css_retour_ligne_apres_element: article_nomenclature.nomenclature && sous_nomenclature_index == article_nomenclature.nomenclature.length - 1 && article_nomenclature.modele.type_article == 1}" v-for="(sous_nomenclature, sous_nomenclature_index) in article_nomenclature.nomenclature" v-if="article_nomenclature.afficher_nomenclature">

                @if($articles_modifiables === true && empty($recapitulatif))
                    <champ-montant
                            :class_input="'css_input_article_document '+(contenu_produit_assemble(article_nomenclature, article_sur_document) ? 'css_champ_readonly' : '')"
                            :modele="sous_nomenclature"
                            nom_sql="marge_brute_pourcentage"
                            style_input="color:#606060;"
                            :valeur_non_vide="true"
                            :lecture_seule="contenu_produit_assemble(article_nomenclature, article_sur_document)">
                    </champ-montant>
                @else
                    @{{sous_nomenclature.marge_brute_pourcentage}}
                @endif

            </span>
            <span :class="{css_retour_ligne_apres_element: article_nomenclature.nomenclature && article_nomenclature.nomenclature.length == 0 && article_nomenclature.modele.type_article == 1}" v-if="article_nomenclature.afficher_nomenclature && article_nomenclature.modele.type_article == 1"></span>


        </template>
    </div>
@else
    {{-- Marge brute % --}}
    <div class="cellule_document_colonne_article">
        <span class="css_prix_ligne_article_document">
        @{{article_sur_document.marge_brute_pourcentage}}
        </span>
    </div>
@endif
@if(empty($recapitulatif))
    @push('donnees_pour_vuejs_mounted')

        this.$on('maj_champ_montant',(donnees) => {

            if(donnees.nom_sql != 'marge_brute_pourcentage')
                return;

            this.corrige_virgule(donnees.modele,'marge_brute_pourcentage');
            this.mise_a_jour_marge_brute_pourcentage(donnees.modele);
        });
    @endpush
@endif