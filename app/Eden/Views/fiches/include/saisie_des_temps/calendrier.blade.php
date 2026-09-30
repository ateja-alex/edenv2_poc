<affichage-calendrier ref="calendrier"
                      :afficher_les_dates="false"
                      :afficher_les_filtres="false"
                      :afficher_mode_affichage="false"
                      :filtres_pour_fiche="{utilisateur:feuille_de_temps.utilisateur_id}"
>
</affichage-calendrier>

@push('donnees_pour_vuejs_watch')
    'feuille_de_temps.date' : function(){
        this.$refs.calendrier.met_a_jour_les_dates(this.feuille_de_temps.date);
    },

    'mode_affichage' : function(){
        this.mise_a_jour_mode_affichage();
    },
@endpush

@push('donnees_pour_vuejs_methods')

    mise_a_jour_mode_affichage : function(){

        var jours_desactives = {!! collect($structure['options']['jours']) !!};

        var affichage_semaine = jours_desactives[6] ? 'semaine_7j' : jours_desactives[5] ? 'semaine_6j' : 'semaine_5j';

        var type_date = {
            1: 'jour',
            2: affichage_semaine,
            3: 'mois',
        };

        this.$refs.calendrier.type_affichage_calendrier = type_date[this.mode_affichage];

        this.$refs.calendrier.met_a_jour_les_dates(this.feuille_de_temps.date);
    },
@endpush

@push('donnees_pour_vuejs_mounted')
    this.mise_a_jour_mode_affichage();
@endpush