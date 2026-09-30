<div class="cellule_document_colonne_article" v-if="eco_contribution_active">

    <span class="css_prix_ligne_article_document css_input_article_document">
        <template v-if="calcul_eco_contribution(article_sur_document,1) > 0">
            @{{ calcul_eco_contribution(article_sur_document,1) | montant }}
        </template>
    </span>

    <template v-for="(article_nomenclature, nomenclature_index) in article_sur_document.nomenclature" v-if="article_sur_document.afficher_nomenclature">

        <span class="css_prix_ligne_article_document_nomenclature css_input_article_document">
            <template v-if="calcul_eco_contribution(article_nomenclature,1) > 0">
                @{{ calcul_eco_contribution(article_nomenclature,1) | montant }}
            </template>
        </span>

        <span :class="{css_retour_ligne_apres_element: article_nomenclature.nomenclature && sous_nomenclature_index == article_nomenclature.nomenclature.length - 1 && article_nomenclature.modele.type_article == 1}" v-for="(sous_nomenclature, sous_nomenclature_index) in article_nomenclature.nomenclature" v-if="article_nomenclature.afficher_nomenclature">

            <span class="css_prix_ligne_article_document_sous_nomenclature css_input_article_document">
                <template v-if="calcul_eco_contribution(sous_nomenclature,1) > 0">
                    @{{ calcul_eco_contribution(sous_nomenclature,1) | montant }}
                </template>
            </span>
        </span>

        <span :class="{css_retour_ligne_apres_element: article_nomenclature.nomenclature && article_nomenclature.nomenclature.length == 0 && article_nomenclature.modele.type_article == 1}" v-if="article_nomenclature.afficher_nomenclature && article_nomenclature.modele.type_article == 1"></span>

    </template>
</div>

@push('donnees_pour_vuejs_data')
    requete_calcul_eco_contribution: false,
@endpush

@push('donnees_pour_vuejs_mounted')

    if(this.client != null && this.client.eco_contribution == 1)
        this.eco_contribution_active = true;
@endpush

@push('donnees_pour_vuejs_methods')

    calcul_eco_contribution : function(article,type = 0,unitaire = false){

        if(article.type_ligne != null)
            return;

        var quantite = unitaire ? 1 : article.quantite;

        if(article.modele.type_article != 1){

            var application = article.application_eco_contribution != 1 ? 0 : 1;

            if(type != application)
                return 0;

            var quantite_unite = article.quantite_unite_eco_contribution ? article.quantite_unite_eco_contribution : 1;

            var tarif = article.tarif_eco_contribution ?? 0;

            var valeur = tarif * quantite_unite;

            valeur = valeur < 0.01 && valeur > 0 ? 0.01 : valeur;

            return (Math.round(valeur * 100 ) / 100) * quantite;
        }

        var montant = 0;

        for(nomenclature of article.nomenclature){

            montant += this.calcul_eco_contribution(nomenclature,type);
        }

        return montant * quantite;
    },

    recalcule_eco_contribution : function(maj_globale = false){

        var maj_eco_contribution = function(articles,eco_contributions){

            for(article of articles){

                if(article.type_ligne)
                    continue;

                var article_id = article.modele.id;

                if(article.modele.type_article == 1)
                    maj_eco_contribution(article.nomenclature,eco_contributions);
                else if(eco_contributions[article_id] != null && Object.values(eco_contributions[article_id]).length > 0){
                    article.categorie_eco_contribution_id = eco_contributions[article_id].categorie_eco_contribution_id;
                    article.application_eco_contribution = eco_contributions[article_id].application_eco_contribution;
                    article.tarif_eco_contribution = eco_contributions[article_id].tarif_eco_contribution;
                    article.quantite_unite_eco_contribution = eco_contributions[article_id].quantite_unite_eco_contribution;
                }
                else{
                    article.categorie_eco_contribution_id = null;
                    article.application_eco_contribution = null;
                    article.tarif_eco_contribution = 0;
                    article.quantite_unite_eco_contribution = 1;
                }

            }
        };

        if(this.eco_contribution_active == false){

            maj_eco_contribution(this.articles_du_document,[]);

            if(maj_globale)
                this.mise_a_jour_total_document_vue('document_mise_a_jour');

            return;
        }

        if(this.requete_calcul_eco_contribution !== false)
            this.requete_calcul_eco_contribution.abort();

        // On récupére les id des articles
        recuperer_articles = function(articles,articles_id){

            for(article of articles){

                if(article.type_ligne == null){

                    if(article.modele.type_article == 1)
                        articles_id = recuperer_articles(article.nomenclature,articles_id)
                    else
                        articles_id.push({
                            article_id : article.modele.id,
                            type_element_source : article.type_element_source,
                            id_element_source : article.id_element_source,
                        });
                }
            }

            return articles_id;
        };

        var articles = recuperer_articles(this.articles_du_document,[]);

        if(articles.length == 0){

            if(maj_globale)
                this.mise_a_jour_total_document_vue('document_mise_a_jour');

            return;
        }

        this.requete_calcul_eco_contribution = $.post({
            url : '{{route('document.mise_a_jour_eco_contribution')}}',
            dataType: 'json',
            data : {
                articles: articles,
                document: this.document,
                type_element: this.type_element
            }
        }).done((donnees) => {

            this.requete_calcul_eco_contribution = false;

            if(donnees.maj_possible !== true){

                if(maj_globale)
                    this.mise_a_jour_total_document_vue('document_mise_a_jour');

                return;
            }

            maj_eco_contribution(this.articles_du_document,donnees.eco_contributions_par_article);

            this.mise_a_jour_total_document_vue('document_mise_a_jour');
        });

    },

@endpush

@push('donnees_pour_vuejs_watch')

    'client.eco_contribution' : function(){

        this.eco_contribution_active = this.client.eco_contribution == 1;

        this.recalcule_eco_contribution(true);
    },
@endpush