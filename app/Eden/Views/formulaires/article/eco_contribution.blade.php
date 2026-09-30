<template v-if="article.type_article != 1">
    <div class="row">
        @champ('article','categorie_eco_contribution_id',2,4)
        <template v-if="article.categorie_eco_contribution_id > 0">
            @champ('article','type_application_eco_contribution',2,4)
        </template>
    </div>

    <div v-if="eco_contribution != null && eco_contribution.unite != null && eco_contribution.unite !=''">
        <div class="row">
            <div class="col-sm-2">
                {!! management('article')->champ('quantite_unite_eco_contribution')->nom_vue() !!}
            </div>
            <div class="col-sm-4">
                <span style="display: flex;gap: 10px;">
                    {!! management('article')->champ('quantite_unite_eco_contribution')->cree() !!}
                    @{{ eco_contribution.unite }}
                </span>
                <span v-if="tarif_par_quantite > 0">
                    @traduction('interface.article.eco_contribution.valeur_actuelle') : @{{ tarif_par_quantite }} €
                </span>
            </div>
        </div>
    </div>
    <span v-else>
        @traduction('interface.article.eco_contribution.valeur_actuelle') : @{{ montant_en_cours }} €
    </span>
</template>
<template v-else>
    <div>
        @traduction('interface.article.eco_contribution.valeur_actuelle') :
    </div>
    <div> @traduction('interface.article.eco_contribution.inclue') : @{{  Number.parseFloat(valeur_eco_contribution[0]).toFixed(2) }} €</div>
    <div> @traduction('interface.article.eco_contribution.en_sus') : @{{ Number.parseFloat(valeur_eco_contribution[1]).toFixed(2) }} €</div>
</template>

@push('donnees_pour_vuejs_data')
    eco_contribution: null,
    montants_ecocontribution: {},
    cle_montant:0,
    valeur_eco_contribution : [0,0],
@endpush

@push('donnees_pour_vuejs_mounted')
    this.$root.$on('selection-element',(parametres) => {

        if(parametres.nom_champ == 'categorie_eco_contribution_id')
            this.eco_contribution = parametres.element;
    });

    if(this.article.type_article == 1 && this.article.id > 0){

        $.ajax({
            url: '{{URL::to('eden/article/')}}/'+this.article.id+'/eco_contribution',
            dataType: 'json',
        }).done((retour) => {
            this.valeur_eco_contribution = retour.eco_contribution;
        });
    }
@endpush

@push('donnees_pour_vuejs_computed')

    montant_en_cours : function(){

        this.cle_montant;
        if(this.eco_contribution == null || this.montants_ecocontribution[this.eco_contribution.id] == null)
            return 0;

        aujourdhui = new Date();

        for(montant of this.montants_ecocontribution[this.eco_contribution.id]){

            date_debut = new Date(montant.date_debut);
            date_fin = new Date(montant.date_fin);

            if(aujourdhui.getTime() >= date_debut.getTime() && aujourdhui.getTime() <= date_fin.getTime()){

                var tarif = Number.parseFloat(montant.montant).toFixed(2);

                return tarif < 0.01 ? 0.01 : tarif;
            }
        }

        return 0;
    },

    tarif_par_quantite : function(){

        if(this.article.quantite_unite_eco_contribution == 0 || this.article.quantite_unite_eco_contribution == null || this.montant_en_cours == 0)
            return 0;

        var tarif = Math.round(this.article.quantite_unite_eco_contribution * this.montant_en_cours *100)/100;

        return tarif < 0.01 ? 0.01 : tarif;
    },
@endpush

@push('donnees_pour_vuejs_watch')
    'article.categorie_eco_contribution_id' : function(){

        if(!(this.article.categorie_eco_contribution_id > 0))
            this.eco_contribution = null;
    },

    'eco_contribution' : function(){

        if(this.eco_contribution != null && this.montants_ecocontribution[this.eco_contribution.id] == null){

            $.post({
                url: '{{route('base_eden.element.rechercher_avec_requete','montant_eco_contribution')}}',
                dataType: 'json',
                data: {
                    donnees : {
                        nom_sql : 'categorie_eco_contribution_id',
                        valeur : this.eco_contribution.id
                    }
                }
            }).done((retour) => {
                this.montants_ecocontribution[this.eco_contribution.id] = retour.retour;
                this.cle_montant++;
                this.$forceUpdate();
            });
        }

    },

@endpush