@push('donnees_pour_vuejs_methods')
    gestion_categorie_comptable : async function(categorie_comptable_id){

        if(categorie_comptable_id == null)
            return;

        if(this.$refs.formulaire.champs_formulaire.filter(champ => champ.nom_sql == 'categorie_comptable_id').length == 0)
            return;

        var articles_du_document = this.articles_du_document.
            filter(article => article.type_ligne == null);

        if((this.document.categorie_comptable_id != null && this.document.categorie_comptable_id != '' && this.document.categorie_comptable_id != categorie_comptable_id)
            || articles_du_document.length > 0){

            if(!await confirm_eden(this.$root.traduction('document.categorie_comptable.confirmation_maj')))
                return;
        }

        this.document.categorie_comptable_id = categorie_comptable_id;

        @if($fonctionnalite_colonnes['categorie_comptable_article_id'] ?? false)
            if(articles_du_document.length == 0)
                return;

            this.categorie_comptable_defaut();
        @endif
    },

    categorie_comptable_defaut : function(){

        var categorie_comptable_id = this.document.categorie_comptable_id ??
            (
                this.type_element.includes('vente') ?
                this.client.modele.categorie_comptable_id :
                this.fournisseur.modele.categorie_comptable_id
            );

        var articles_ids = this.articles_du_document.filter(article => article.type_ligne == null)
            .map(article => article.article_id);

        if(categorie_comptable_id == null || articles_ids.length == 0)
            return;

        $.post({
            url :'eden/elements/categorie_comptable_article',
            dataType:'json',
            data: {
                filtrage:[
                    {
                        champ : 'categorie_comptable_id',
                        condition : 'where',
                        valeur : categorie_comptable_id
                    },
                    {
                        champ : 'article_id',
                        condition : 'whereIn',
                        valeur : articles_ids
                    }
                ]
            }
        }).done((donnees) => {

            for(article of this.articles_du_document){

                var categorie_comptable_defaut = donnees.filter(donnee => donnee.article_id == article.article_id)[0] ?? null;

                if(categorie_comptable_defaut != null && article.categorie_comptable_article_id == categorie_comptable_defaut.id)
                    article.categorie_comptable_article_id = null;

                this.$set(article,'categorie_comptable_article_defaut',categorie_comptable_defaut);

                if(categorie_comptable_defaut != null && article.categorie_comptable_article_id == null){
                    if(this.type_element.includes('vente'))
                        article.tva = categorie_comptable_defaut.taux_tva;
                    else
                        article.tva = categorie_comptable_defaut.taux_tva_achat;
                }
            }

            this.mise_a_jour_total_document_vue();
        });
    },

@endpush

@push('donnees_pour_vuejs_data')
    categorie_comptable : {},
@endpush

@push('donnees_pour_vuejs_mounted')

    @if($articles_modifiables === true)
        this.$nextTick(() => {

            this.$on('selection-element',(parametres) => {

                if(parametres.nom_champ == 'categorie_comptable_article_id'){

                    if(this.type_element.includes('vente'))
                        parametres.modele.tva = parametres.element.taux_tva;
                    else
                        parametres.modele.tva = parametres.element.taux_tva_achat;

                    this.mise_a_jour_total_document_vue();
                }
                else if(parametres.nom_champ == 'categorie_comptable_id')
                    this.categorie_comptable_defaut();
            });

            this.$on('suppression-selection-element',(parametres) => {

                if(parametres.nom_champ == 'categorie_comptable_article_id'){

                    if(this.type_element.includes('vente'))
                        parametres.modele.tva = parametres.modele.categorie_comptable_article_defaut ? parametres.modele.categorie_comptable_article_defaut.taux_tva : 0;
                    else
                        parametres.modele.tva = parametres.modele.categorie_comptable_article_defaut ? parametres.modele.categorie_comptable_article_defaut.taux_tva_achat : 0;

                    this.mise_a_jour_total_document_vue();
                }
                else if(parametres.nom_champ == 'categorie_comptable_id')
                    this.categorie_comptable_defaut();
            });

        });
    @endif

    this.categorie_comptable_defaut();
@endpush