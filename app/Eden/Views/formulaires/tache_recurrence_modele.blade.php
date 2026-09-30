<div class="recurrence_date_de_debut">
    @champ('tache_recurrence_modele', 'nom', 1, 4)
</div>

<div class="infos_recurrence">

    <i class="fas fa-sync infos_recurrence_icone"></i>
    <div class="infos_recurrence_conteneur_champs">
        <div class="infos_recurrence_type_frequence">
            <span>@traduction('formulaire.tache.recurrence.repeter_chaque')</span>
            {!! management('tache_recurrence_modele')->champ('frequence')->attr('style', 'margin: 0 20px 0 10px;width:50px;')->attr('min', 1)->cree() !!}
            {!! management('tache_recurrence_modele')->champ('type_frequence')->cree() !!}
        </div>
        <div>
            <div class="recurrence_badges_jours" v-if="tache_recurrence_modele.type_frequence == 2">
                {!! management('tache_recurrence_modele')->champ('jours_concernes')->cree() !!}
            </div>
            <div class="infos_recurrence_jours_concernes" v-if="tache_recurrence_modele.type_frequence == 3 || tache_recurrence_modele.type_frequence == 4">
                <div class="infos_recurrence_hebdomadaire">
                    <input type="radio" class="infos_recurrence_jour_concerne" id="jour_concerne_absolu" name="jour_concerne"
                           v-model="jour_concerne" value="0">
                    <label for="jour_concerne_absolu">@traduction('formulaire.tache.recurrence.le') jour de la tâche</label>
                </div>
                <div class="infos_recurrence_annuel">

                    <input type="radio" class="infos_recurrence_jour_concerne" id="jour_concerne_relatif" name="jour_concerne" v-model="jour_concerne" value="1">
                    <label for="jour_concerne_relatif">@traduction('formulaire.tache.recurrence.le')</label>
                    <select v-model="tache_recurrence_modele.frequence_jour_concerne" name="frequence_jour_concerne" @change="jour_concerne = 1">
                        <option v-for="valeur in $root.valeurs_listes_formatees[13]" :value="valeur.id_valeur">@{{ valeur.valeur }}</option>
                    </select>
                    <select v-model="jours_concernes" name="jours_concernes[]">
                        <option v-for="valeur in $root.valeurs_listes_formatees[12]" :value="valeur.id_valeur">@{{ valeur.valeur }}</option>
                    </select>
                </div>
            </div>
            <div class="infos_recurrence_recap">
                <span> @{{ affichage_phrase_recurrence(true) }} </span>
            </div>
        </div>
    </div>
</div>

<div class="recurrence_date_de_debut">
    <div class="col-sm-1">
        {!! management('tache_recurrence_modele')->champ('date_de_fin')->nom() !!}
    </div>
    <div class="col-sm-1">
        <label class="switch">
            <input type="checkbox" v-model="gestion_date_de_fin">
            <span class="slider round"></span>
        </label>
    </div>
    <div class="col-sm-4" v-if="gestion_date_de_fin" style="display: flex;align-items: center;gap: 10px;">
        <input v-model="valeur_date_de_fin" type="number" @wheel.prevent @keydown.up.prevent @keydown.down.prevent/>
        <select v-model="option_date_de_fin">
            <option v-for="option in $root.valeurs_listes_formatees[10]" v-if="option.id_valeur != 0" :value="option.id_valeur">@{{ option.valeur }}</option>
        </select>
    </div>

    <input type="hidden" name="date_de_fin" v-model="date_de_fin">
</div>

@push('donnees_pour_vuejs_data')

    jour_concerne: 0,
    jours_concernes: 1,
    gestion_date_de_fin: false,
    valeur_date_de_fin: '',
    option_date_de_fin: 1,
    options_dates : {
        1 : 'date',
        2 : 'week',
        3 : 'month',
        4 : 'year',
    },
@endpush

@push('donnees_pour_vuejs_mounted')

    if((this.tache_recurrence_modele.type_frequence == 3 || this.tache_recurrence_modele.type_frequence == 4) &&
        Object.values(this.tache_recurrence_modele.jours_concernes).length > 0)
        this.jours_concernes = Object.keys(this.tache_recurrence_modele.jours_concernes)[0];

    if(this.tache_recurrence_modele.date_de_fin != null && this.tache_recurrence_modele.date_de_fin != ''){
        var date_fin = this.tache_recurrence_modele.date_de_fin.split(' ');
        this.valeur_date_de_fin = date_fin[0];
        this.option_date_de_fin = Object.keys(this.options_dates).find(cle => this.options_dates[cle] === date_fin[1]);
        this.gestion_date_de_fin = true;
    }
@endpush

@push('donnees_pour_vuejs_methods')

    affichage_phrase_recurrence: function() {

        let phrase_recurrence = vue_instance.traduction('formulaire.tache.recurrence.a_lieu');
        let jours_a_afficher = [];

        if(this.tache_recurrence_modele.type_frequence == 2){

            phrase_recurrence += ' ' + vue_instance.traduction('formulaire.tache.recurrence.chaque') + ' ';

            let jours_autorises = this.tache_recurrence_modele.jours_concernes.toSorted();
            jours_autorises.forEach((jour) => {

                phrase_recurrence += this.$root.recuperer_valeur_liste_formatee(12,jour).valeur + ', ';
            });
        }

        phrase_recurrence += ' ' + vue_instance.traduction('formulaire.tache.recurrence.tous_les') + ' ';
        phrase_recurrence += this.tache_recurrence_modele.frequence != 1 ? this.tache_recurrence_modele.frequence + ' ' : '';
        phrase_recurrence += this.$root.recuperer_valeur_liste_formatee(10,this.tache_recurrence_modele.type_frequence).valeur + '.';

        return phrase_recurrence;
    },
@endpush

@push('donnees_pour_vuejs_computed')

    date_de_fin : function(){

        if(this.gestion_date_de_fin && this.valeur_date_de_fin != null && this.valeur_date_de_fin != '')
            return this.valeur_date_de_fin+' '+this.options_dates[this.option_date_de_fin];

        return null;
    },
@endpush