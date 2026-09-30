<div v-if="article_sur_document.affichage_tarif_force === true">
    @traduction('document.colonnes.designation.tarif_force.titre') :

@if(!empty($recapitulatif))

    <span class="css_input_article_document">@{{ article_sur_document.tarif_force | montant }}</span>

@else

    <champ-montant
            :modele="article_sur_document"
            nom_sql="tarif_force"
            :valeur_non_vide="true">
    </champ-montant>

    <input type="hidden" name="prix_achat_force" v-model="article_sur_document.prix_achat_force">
@endif
</div>

@if(empty($recapitulatif))

    @push('donnees_pour_vuejs_mounted')

        this.$on('maj_champ_montant',(donnees) => {

            if(donnees.nom_sql != 'tarif_force')
                return;

            this.modification_prix_de_vente(donnees.modele);
        });
    @endpush

    @push('donnees_pour_vuejs_methods')

        afficher_tarif_force : function(article_sur_document, affichage_tarif_force = undefined, mise_a_jour_total = true){

            var vue_instance = this;

            if(affichage_tarif_force != undefined)
                article_sur_document.affichage_tarif_force = affichage_tarif_force;
            else if(article_sur_document.affichage_tarif_force === true || article_sur_document.affichage_tarif_force === false)
                article_sur_document.affichage_tarif_force = !article_sur_document.affichage_tarif_force;
            else
                article_sur_document.affichage_tarif_force = true;

            if(article_sur_document.affichage_tarif_force === false)
                article_sur_document.tarif_force = null;
            else{

                if(article_sur_document.modele.tarif_force == undefined || article_sur_document.modele.tarif_force == null || isNaN(article_sur_document.modele.tarif_force)){
                    article_sur_document.tarif_force = article_sur_document.tarif;
                }
                else
                    article_sur_document.tarif_force = article_sur_document.modele.tarif_force;

            }

            if(mise_a_jour_total){
                vue_instance.modification_prix_de_vente(article_sur_document);
                vue_instance.$forceUpdate();
            }

        },

    @endpush

@endif
