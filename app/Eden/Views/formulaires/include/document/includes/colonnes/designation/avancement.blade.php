<!-- Avancement -->
<div class="css_facture_article_avancement cellule_document_colonne_article w-100" v-if="document.type_facture == 1 || document.type_facture == 2">
    <span>@traduction('document.colonnes.designation.avancement.titre')</span>

    @if($articles_modifiables === true && empty($recapitulatif))
        <div class="row">
            <div class="col-md-2">@traduction('interface.modales.precedent')</div>
            <div class="col-md-1">
                <champ-montant
                        class_input="css_input_article_document"
                        :modele="article_sur_document"
                        nom_sql="avancement_precedent"
                        :valeur_non_vide="true"
                        @if(!fonctionnalite('gescom_avancement_pouvoir_modifier_pourcentage_precedent')) :lecture_seule="true" @endif>
                </champ-montant>
            </div>
            <div class="col-md-1">%</div>
            <div class="col-md-2">@traduction('document.colonnes.designation.avancement.actuel')</div>
            <div class="col-md-1">
                <champ-montant
                        class_input="css_input_article_document"
                        :modele="article_sur_document"
                        nom_sql="avancement_actuel"
                        :valeur_non_vide="true"
                        :lecture_seule="document.type_facture == 2">
                </champ-montant>
            </div>
            <div class="col-md-1">%</div>
        </div>
    @else
        <div class="row">
            <div class="col-md-4">Précédent : @{{ article_sur_document.avancement_precedent }}%</div>
            <div class="col-md-4">Actuel : @{{ article_sur_document.avancement_actuel }}%</div>
        </div>
    @endif
</div>

@if(empty($recapitulatif))

    @push('donnees_pour_vuejs_mounted')

        this.$on('maj_champ_montant',(donnees) => {

            if(donnees.nom_sql == 'avancement_precedent')
                this.mise_a_jour_total_document_vue();
            else if(donnees.nom_sql == 'avancement_actuel')
                this.verification_valeur_avancement(donnees.modele);
        });
    @endpush

    @push('donnees_pour_vuejs_methods')

        verification_valeur_avancement(article){

            if(article.avancement_actuel > 100)
                article.avancement_actuel = 100;
            @if(!fonctionnalite('gescom_avancement_accepter_reduction_avancement_actuel'))

                if(article.avancement_precedent == null)
                    article.avancement_precedent = 0;

                if(isNaN(article.avancement_precedent))
                    article.avancement_precedent = 0;

                if(article.avancement_actuel < article.avancement_precedent)
                    article.avancement_actuel = article.avancement_precedent;
            @endif

            this.mise_a_jour_total_document_vue();
        },

    @endpush

@endif
