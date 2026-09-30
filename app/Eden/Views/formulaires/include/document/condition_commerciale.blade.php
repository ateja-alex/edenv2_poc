@php $condition_commerciale = modele('condition_commerciale')->get()->isNotEmpty();@endphp

@push('donnees_pour_vuejs_methods')

    charger_conditions_commerciales : function(){

        var ids_articles = this.articles_du_document.filter(article => article.article_id).map(article => article.article_id);

        $.post({
            url : '{{route('document.conditions_commerciales')}}',
            dataType: 'json',
            data : {
                ids : ids_articles,
                parametres : {
                    date_document: this.document.date,
                    catalogue_groupement_id: this.document.catalogue_groupement_id,
                    @if($management->est_une_vente())
                        client_id: this.document.client_id,
                    @else
                        fournisseur_id: this.document.fournisseur_id,
                    @endif
                }
            }
        }).done((conditions_commerciales) => {

            for(article of this.articles_du_document){

                if(article.article_id)
                    article.conditions_commerciales =
                        conditions_commerciales.filter(condition => condition.article_id == article.article_id)[0].conditions_commerciales;
            }
        });
    },

    gestion_condition_commerciale : function(article){

        if(!article.conditions_commerciales || article.conditions_commerciales.length <= 1)
            return;

        var condition_a_appliquer = null;

        for(condition_commerciale of article.conditions_commerciales){

            condition_commerciale.palier_quantite = parseFloat(condition_commerciale.palier_quantite);

            if(condition_commerciale.conditionnement == article.conditionnement &&
            condition_commerciale.palier_quantite <= article.quantite &&
            (condition_a_appliquer == null || condition_commerciale.palier_quantite > condition_a_appliquer.palier_quantite))
                condition_a_appliquer = structuredClone(condition_commerciale);
        }
        
        if(condition_a_appliquer == null && article.conditionnement > 0){

            var conditions_defaut = article.conditions_commerciales.filter(condition =>
            condition.palier_quantite == 1 && condition.conditionnement == 0);

            condition_a_appliquer = conditions_defaut.length > 0 ? structuredClone(conditions_defaut[0]) : null;

            var conditionnement = article.conditionnement_possible[article.conditionnement];

            if(condition_a_appliquer != null){

                for(cle of ['tarif','prix_achat','tarif_force','prix_achat_force']){
                    if(condition_a_appliquer[cle] != null)
                    condition_a_appliquer[cle] *= conditionnement.quantite;
                }
            }
        }
        
        if(condition_a_appliquer == null)
            return;

        for(cle in condition_a_appliquer){

            if(!['id','modifie_le','modifie_par','inactif','cree_le','cree_par','cle_externe',
            'catalogue_tarif_id','client_id','famille_id','article_id','palier_quantite',
            'conditionnement','chaine_affichage','chaine_tags_recherche'].includes(cle) &&
            condition_a_appliquer[cle] != article[cle] && condition_a_appliquer[cle] !== null)
                article[cle] = condition_a_appliquer[cle];
        }

        @if($management->est_un_achat())
            article.tarif = condition_a_appliquer.prix_achat;
        @endif

        this.arrondi_prix_article_depuis_fonctionnalite(article);
        this.changement_tarif(article);
    },

    mise_a_jour_conditions_commerciales : async function(){

        if(this.articles_du_document.length == 0 || !await confirm_eden(this.$root.traduction('document.condition_commerciale.confirmation_maj_conditions')))
            return;

        this.document_mise_a_jour();
    },
@endpush

@push('donnees_pour_vuejs_mounted')
    @if($management->existe() && $condition_commerciale)
        this.charger_conditions_commerciales();
    @endif
@endpush

@push('donnees_pour_vuejs_watch')

    'document.date' : function(){
        this.recalcule_eco_contribution();

        @if($condition_commerciale)
            this.mise_a_jour_conditions_commerciales();
        @endif
    },

    @if($condition_commerciale)

        'document.catalogue_groupement_id' : function(valeur){
            if(valeur > 0)
                this.mise_a_jour_conditions_commerciales();
        },

    @endif

@endpush