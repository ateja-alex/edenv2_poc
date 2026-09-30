<div id="planning_filtres">
    <filtres ref="filtres" :appliquer_recherche_avancee="false"  :valeurs_filtres="valeurs_filtres" :filtres="filtres_planning"></filtres>
</div>

<div class="dropdown">
    <span class="fa fa-calendar dropdown-toggle css_dropdown_sans_fleche_vers_le_bas css_pointer" id="dropdown_planning_semaines" data-toggle="dropdown" aria-expanded="false"></span>
    <span class="badge badge-warning css_badge_sur_filtre_et_option" style="cursor: auto;">@{{ nombre_de_semaines }}</span>
    <div class="dropdown-menu dropdown-menu-right">
        <label class="dropdown-item">
            @traduction('composant.planning.nombre_semaines_affiches')
        </label>
        <label class="dropdown-item" style="display: flex;align-items: center;gap: 10px;">
            <input min="1" max="5" type="range" v-model="nombre_de_semaines" @change="changer_semaine()">
            <span v-html="nombre_de_semaines+' '+$root.traduction('composant.planning.semaine'+(nombre_de_semaines> 1 ?'s' : ''))"></span>
        </label>
        <div class="dropdown-item">
            @traduction('composant.calendrier.type_taches_affichees.titre')
        </div>
        <div class="dropdown-item" v-for="type_possible in type_taches_affichees_possibles">
            <input class="calendrier_input_type_tache" type="radio" :id="'planning_type_tache_' + type_possible" :value="type_possible" v-model="type_taches_affichees" @change="actualisation()">
            <label class="calendrier_label_type_tache" :for="'planning_type_tache_' + type_possible" v-html="$root.traduction('composant.calendrier.type_taches_affichees.' + type_possible)"></label>
        </div>
    </div>
</div>


<span class="css__lien"  @click="changer_semaine(dates.entete.semaine_precedente)"><i class="fa fa-fw fa-chevron-left"></i></span>
<span class="dropdown" v-clique_en_dehors="{func: () => { choix_dates_en_cours = false; }}">
	<span @click="choix_dates_en_cours = !(choix_dates_en_cours)" style="cursor:pointer;">
		<h5 v-if="nombre_de_semaines == 1" v-html="$root.traduction('composant.affichage_calendrier.semaine_du', null, [dates.numero_semaine_voulue, dates.entete.lundi, dates.entete.vendredi])"></h5>
		<h5 v-else>@traduction('composant.affichage_calendrier.du') @{{ dates.entete.lundi }} @traduction('composant.affichage_calendrier.au') @{{dates.entete.vendredi}}</h5>
	</span>
	<div v-show="choix_dates_en_cours" style="position: absolute;background-color: #f9f9f9;min-width: 100%;box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);padding: 12px 16px;z-index: 1059;">
        <component :is="composant_date"></component>
	</div>
</span>
<span class="css__lien" @click="changer_semaine(dates.entete.semaine_suivante)"><i class="fa fa-fw fa-chevron-right"></i></span>


@push('donnees_pour_vuejs_props')

    filtres_pour_fiche: {},
@endpush

@push('donnees_pour_vuejs_methods')

    changer_semaine : async function(date_voulue = null){

        if(date_voulue !== null)
            this.$emit('changement_semaine_voulue',date_voulue);

        await this.actualisation();

        let date_debut = moment(this.$root.$refs.planning.dates.plage_de_dates_recuperation_taches.debut, 'YYYY-MM-DD');
        let date_fin = moment(this.$root.$refs.planning.dates.plage_de_dates_recuperation_taches.fin, 'YYYY-MM-DD');

        let dates = [date_debut.toDate()];
        let difference = date_fin.diff(date_debut, 'days');

        for(let i = 0; i < difference; i++){
            date_debut.add(1, 'd');
            dates.push(date_debut.toDate());
        }

        $("#selection_datepicker_planning").datepicker('setDates', dates);
    },
@endpush

@push('donnees_pour_vuejs_data')
    valeurs_filtres: [],
    type_taches_affichees_possibles: [
        'les_deux',
        'tache',
        'conges'
    ],
	choix_dates_en_cours: false,
	composant_date: {
        template: '<div id="selection_datepicker_planning"></div>',
        name: 'date',
        props:{
            semaine_voulue : null,
        },
        mounted : function(){

            var langue = this.$root.moi.langue != null ? this.$root.moi.langue : 'fr';
            var vue_instance = this;

            $("#selection_datepicker_planning").datepicker({
                language: langue,
                todayHighlight: true,
            });

            let date_debut = moment(this.$root.$refs.planning.dates.plage_de_dates_recuperation_taches.debut, 'YYYY-MM-DD');
            let date_fin = moment(this.$root.$refs.planning.dates.plage_de_dates_recuperation_taches.fin, 'YYYY-MM-DD');

            let dates = [date_debut.toDate()];

            for(let i = 0; i < date_fin.diff(date_debut, 'days'); i++){
                date_debut.add(1, 'd');
                dates.push(date_debut.toDate());
            }

            $("#selection_datepicker_planning").datepicker('setDates', dates);

            $("#selection_datepicker_planning").on("changeDate", function(e){

                if(e.dates.length > 1)
                    return;

                var date = $(this).datepicker('getDate');
                date = $.datepicker.formatDate("yy-mm-dd", date);

                vue_instance.$root.$refs.planning.changer_semaine(date);
            });
        },
    },
@endpush
