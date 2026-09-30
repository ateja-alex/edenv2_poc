<div class="cellule_document_colonne_article w-100">

    <span class="css_prix_ligne_article_document css_input_article_document document_colonne_categorie_comptable">

        <template v-if="{{ $edition_ligne }}">

            <div v-if="!(article_sur_document.categorie_comptable_article_id > 0) && article_sur_document.categorie_comptable_article_defaut != null">
                <champ-selection-element type_element_origine="{{$management->_type_element.'_lignes'}}"
                        :lecture_seule="true" type_element="categorie_comptable_article"  :desactiver_creation_a_la_volee="true"
                        nom_sql="id" :modele="article_sur_document.categorie_comptable_article_defaut"></champ-selection-element>
            </div>

            @php
                $champ_categorie_comptable = management($management->_type_element.'_lignes')->champ('categorie_comptable_article_id')
                ->vmodel(true,'article_sur_document');

                    $champ_categorie_comptable->modele = clone $champ_categorie_comptable->modele;

                    $champ_categorie_comptable->modele->desactiver_creation_a_la_volee = true;
                    $champ_categorie_comptable->filtrage("[
                    {\"champ\":\"article_id\",\"condition_ou\":false,\"condition\":\"Where\",\"symbole\":\"=\",\"valeur\":article_sur_document.article_id },
                    {\"champ\":\"id\",\"condition_ou\":false,\"condition\":\"Where\",\"symbole\":\"!=\",\"valeur\":article_sur_document.categorie_comptable_article_defaut != null ? article_sur_document.categorie_comptable_article_defaut.id : 0 }
                ]");
            @endphp

            {!! $champ_categorie_comptable->cree() !!}

        </template>

        <span v-else class="css_lecture_ligne" v-html="$root.libelle_element_lecture('categorie_comptable_article', article_sur_document.categorie_comptable_article_id > 0 ? article_sur_document.categorie_comptable_article_id : (article_sur_document.categorie_comptable_article_defaut != null ? article_sur_document.categorie_comptable_article_defaut.id : null))"></span>
    </span>
</div>
