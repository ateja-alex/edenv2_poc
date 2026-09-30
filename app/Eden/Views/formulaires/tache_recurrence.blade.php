<div class="recurrence_date_de_debut">
    @champ('tache_recurrence', 'date_de_debut', 2, 4)
</div>

<div class="infos_recurrence">

    <i class="fas fa-sync infos_recurrence_icone"></i>
    <div class="infos_recurrence_conteneur_champs">
        <div class="infos_recurrence_type_frequence">
            <span>@traduction('formulaire.tache.recurrence.repeter_chaque')</span>
            {!! management('tache_recurrence')->champ('frequence')->attr('style', 'margin: 0 20px 0 10px;width:50px;')->attr('min', 1)->cree() !!}
            {!! management('tache_recurrence')->champ('type_frequence')->cree() !!}
        </div>
        <div>
            <div class="recurrence_badges_jours" v-if="tache_recurrence.type_frequence == 2">
                {!! management('tache_recurrence')->champ('jours_concernes')->cree() !!}
            </div>
            <div class="infos_recurrence_jours_concernes" v-if="tache_recurrence.type_frequence == 3 || tache_recurrence.type_frequence == 4">
                <div class="infos_recurrence_hebdomadaire">
                    <input type="radio" class="infos_recurrence_jour_concerne" id="jour_concerne_absolu" name="frequence_jour_concerne" v-model="tache_recurrence.frequence_jour_concerne" value="0" checked>
                    <label for="jour_concerne_absolu">@traduction('formulaire.tache.recurrence.le') @{{ recuperer_jour_mois() }}</label>
                </div>
                <div class="infos_recurrence_annuel">

                    <input type="radio" class="infos_recurrence_jour_concerne" id="jour_concerne_relatif" name="frequence_jour_concerne" v-model="tache_recurrence.frequence_jour_concerne" :value="calculer_semaine()">
                    <label for="jour_concerne_relatif">@traduction('formulaire.tache.recurrence.le') @{{ calculer_jour_mois() }}</label>
                </div>
            </div>
        </div>
        <div class="infos_recurrence_recap">
            <span> @{{ $parent.affichage_phrase_recurrence(true) }} </span>

            <div class="col-sm-2 infos_recurrence_date_de_fin" v-if="tache_recurrence.date_de_fin === null">
                <label for="date_de_fin">{!! management('tache_recurrence')->champ('date_de_fin')->nom_vue() !!}</label>
            </div>
            <div class="col-sm-4 infos_recurrence_date_de_fin">
                {!! management('tache_recurrence')->champ('date_de_fin')->cree() !!}
            </div>

            <a href="javascript:;" @click="tache_recurrence.date_de_fin = null" v-if="tache_recurrence.date_de_fin !== null">@traduction('formulaire.tache.recurrence.supprimer_date_fin')</a>
        </div>
    </div>
</div>

@push('donnees_pour_vuejs_data')

    modale_recurrence: false,
@endpush

@push('donnees_pour_vuejs_methods')

    calculer_jour_mois: function() {

        let date_tache = new Date(this.tache.date_de_debut);

        //On calcule le numéro de la semaine du mois et on récupère le nombre ordinal qui lui correspond
        semaine = this.$root.recuperer_valeur_liste_formatee(13, Math.ceil(date_tache.getDate()/7)).valeur;

        // On récupère le jour, et s'il est égal à 0 (Dimanche en js) on le transforme en 7 (Dimanche pour nous)
        let jour = date_tache.getDay();
        jour = jour == 0 ? jour = 7 : jour;
        jour = this.$root.recuperer_valeur_liste_formatee(12,jour).valeur;

        return semaine.toLowerCase() + ' ' + jour;
    },

    calculer_semaine: function() {

        let date_tache = new Date(this.tache.date_de_debut);

        return Math.ceil(date_tache.getDate()/7);
    },

    recuperer_jour_mois: function() {

        let date_tache = new Date(this.tache.date_de_debut);

        return date_tache.getDate();
    },

    date_chaine_affichage(date, avec_heures = false){

        var difference_fuseau = date.getTimezoneOffset() * 60000;
        date.setTime(date.getTime() - difference_fuseau);

        var affichage_date = '';

        if(avec_heures == true){
            affichage_date = date.toISOString().slice(0,19);
            return affichage_date.replace('T', ' ');
        }else
            affichage_date = date.toISOString().slice(0,10);

        return affichage_date;
    }

@endpush

@push('donnees_pour_vuejs_watch')

    'tache.date_de_debut': function(nouvelle_valeur, ancienne_valeur){

        let date = new Date(nouvelle_valeur);
        let jour = date.getDay() == 0 ? 7 : date.getDay();
        // Si on est sur une récurrence hebdomadaire, on ajoute aux jours_concernés le jour de la nouvelle date de début
        if(this.tache.tache_recurrence.type_frequence == 2 && !this.tache.tache_recurrence.jours_concernes.includes(jour))
            this.tache.tache_recurrence.jours_concernes.push(jour);

        if(this.tache.parent_id == undefined || this.tache.parent_id == "")
            this.tache.tache_recurrence.date_de_debut = this.date_chaine_affichage(date, false);
    },

    'tache.tache_recurrence.date_de_debut': function(nouvelle_valeur,ancienne_valeur){

        if(this.tache.parent_id != undefined && this.tache.parent_id != "")
            return;

        let date = new Date(nouvelle_valeur);
        let date_debut_tache = new Date(this.tache.date_de_debut);
        let date_fin_tache = new Date(this.tache.date_de_fin);

        //On reprend l'heure de la date de début
        date.setHours(date_debut_tache.getHours());
        date.setMinutes(date_debut_tache.getMinutes())
        date.setSeconds(date_debut_tache.getSeconds());

        this.tache.date_de_debut = this.date_chaine_affichage(date, true);

        //On calcule la différence en jours
        let difference_debut_fin = date_fin_tache.getTime() - date.getTime();

        //On ajoute la différence pour calculer la date de fin
        date.setTime(date.getTime() + difference_debut_fin);

        this.tache.date_de_fin = this.date_chaine_affichage(date, true);

        if(ancienne_valeur == undefined)
            return;
    
        let ancien_debut_recurrence = new Date(ancienne_valeur);
        let nouveau_debut_recurrence = new Date(nouvelle_valeur);
        let date_fin_recurrence = new Date(this.tache.tache_recurrence.date_de_fin);

        let difference_debut_fin_recurrence = date_fin_recurrence.getTime() - ancien_debut_recurrence.getTime();
        nouveau_debut_recurrence.setTime(nouveau_debut_recurrence.getTime() + difference_debut_fin_recurrence);

        this.tache.tache_recurrence.date_de_fin = this.date_chaine_affichage(nouveau_debut_recurrence);
    },

    'tache.tache_recurrence.jours_concernes': {
        handler(nouvelle_valeur, ancienne_valeur) {

            if(!this.tache.tache_recurrence.jours_concernes)
                this.tache.tache_recurrence.jours_concernes = [];

            let date_de_debut = new Date(this.tache.date_de_debut);
            let date_de_fin = new Date(this.tache.date_de_fin);

            let jour = date_de_debut.getDay() == 0 ? 7 : date_de_debut.getDay();

            if(this.tache.tache_recurrence.jours_concernes.length === 0)
                this.tache.tache_recurrence.jours_concernes.push(jour);

            if(this.tache.tache_recurrence.jours_concernes.length === 1){

                date_de_debut.setDate(date_de_debut.getDate() + (this.tache.tache_recurrence.jours_concernes[0] - jour));
                date_de_fin.setDate(date_de_fin.getDate() + (this.tache.tache_recurrence.jours_concernes[0] - jour));
    
                this.tache.date_de_debut = this.date_chaine_affichage(date_de_debut, true);
            }
        },
        deep: true
    },
@endpush