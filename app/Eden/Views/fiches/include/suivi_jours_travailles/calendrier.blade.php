<affichage-calendrier ref="calendrier"
                      :afficher_les_dates="false"
                      :afficher_les_filtres="false"
                      :afficher_mode_affichage="false"
                      :filtres_pour_fiche="{utilisateur:parametres.utilisateur_id}"
                      v-if="parametres.utilisateur_id > 0"
>
</affichage-calendrier>

@push('donnees_pour_vuejs_watch')
    'parametres.date_debut' : function(){

        this.$nextTick(() => {
            this.$refs.calendrier.met_a_jour_les_dates(this.parametres.date_debut);
        });
    },

    'parametres.type_affichage' : function(){

        this.$nextTick(() => {
            this.mise_a_jour_mode_affichage();
        });
    },
@endpush

@push('donnees_pour_vuejs_methods')

    mise_a_jour_mode_affichage : function(){

        var type_date = {
            'hebdomadaire': 'semaine_7j',
            'mensuel': 'mois',
        };

        if(this.$refs.calendrier == null)
            return;

        this.$refs.calendrier.type_affichage_calendrier = type_date[this.parametres.type_affichage];

        this.$refs.calendrier.met_a_jour_les_dates(this.parametres.date_debut);
    },
@endpush

@push('donnees_pour_vuejs_mounted')
    this.mise_a_jour_mode_affichage();
@endpush