<div class="row">
    <div class="col-sm-2">
        <span>@traduction('formulaire.tache.recurrence.titre')</span>
    </div>
    <div class="col-sm-4" style="display:flex;">
        <select :disabled="tache.participant == true" v-model="type_recurrence" @change="afficher_modale_recurrence()">
            <option v-for="modele_recurrence in modeles_recurrence" :value="modele_recurrence.id" :disabled="modele_recurrence.id == 0 && (tache.parent_id == true || tache.tache_parent == true)">@{{ modele_recurrence.nom }}</option>
        </select>
        <span class="btn_modifier_recurrence" @click="modale_recurrence = true" v-if="type_recurrence > 0 && !tache.participant"><i class="fas fa-edit"></i></span>
    </div>
</div>

<div class="row">
    <div class="col-sm-2">
    </div>
    <div class="col-sm-10">
        <span v-if="type_recurrence > 0 && tache.tache_recurrence != undefined" id="phrase_recap_recurrence">@{{ affichage_phrase_recurrence() }}</span>
    </div>
</div>

<!-- Modale sous-formulaire tache_recurrence -->
<div v-if="type_recurrence > 0" v-show="modale_recurrence">
    <transition name="modale_recurrence">
        <div class="modal-mask">
            <div class="modal-dialog modal-lg" id="modale_tache_recurrence" role="document">
                <div class="modal-content">

                    <div id="sous_formulaire_tache_recurrence">

                        @include('eden::formulaires.sous_formulaire_dynamique', ['type_element' => 'tache'])

                        @include('eden::formulaires.include.sous_formulaire_dynamique_vuejs', [
                            'nom_formulaire' => 'tache',
                            'nom_sous_formulaire' => 'tache_sous_formulaire_tache_recurrence',
                            'champ_libre' => array(),
                            'informations_type_element' => array(),
                            'type_element' => 'tache',
                            'modification_autorisee' => true
                        ])
                    </div>

                    <div class="modal-footer">
                        <div type="button" class="btn btn-secondary" @click="modale_recurrence = false">@traduction('interface.modales.fermer')</div>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</div>

@push('donnees_pour_vuejs_data')

    modale_recurrence: false,
    type_recurrence: 0,
    modeles_recurrence: [],
    fonctionnalite_valeur_date_fin: {{fonctionnalite('valeur_duree_ajoutee_recurrence_personnalisee')}},
    fonctionnalite_unite_date_fin: '{{fonctionnalite('unite_duree_ajoutee_recurrence_personnalisee')}}',
@endpush

@push('donnees_pour_vuejs_methods')

    affichage_phrase_recurrence: function(affichage_modale = false) {

        let phrase_recurrence = vue_instance.traduction('formulaire.tache.recurrence.a_lieu');
        let jours_a_afficher = [];

        if(this.tache.tache_recurrence.type_frequence == 2){

            phrase_recurrence += ' ' + vue_instance.traduction('formulaire.tache.recurrence.chaque') + ' ';

            let jours_autorises = this.tache.tache_recurrence.jours_concernes.toSorted();
            jours_autorises.forEach((jour) => {

                    phrase_recurrence += this.$root.recuperer_valeur_liste_formatee(12,jour).valeur + ', ';
            });
        }

        phrase_recurrence += ' ' + vue_instance.traduction('formulaire.tache.recurrence.tous_les') + ' ';
        phrase_recurrence += this.tache.tache_recurrence.frequence != 1 ? this.tache.tache_recurrence.frequence + ' ' : '';
        phrase_recurrence += this.$root.recuperer_valeur_liste_formatee(10,this.tache.tache_recurrence.type_frequence).valeur;

        if(this.tache.tache_recurrence.date_de_fin != null){
            phrase_recurrence += " " + vue_instance.traduction('formulaire.tache.recurrence.jusqu_au') + " : "

            if(affichage_modale === false)
                phrase_recurrence += new Date(this.tache.tache_recurrence.date_de_fin).toLocaleDateString("fr");
        }
        else
            phrase_recurrence += '.'

        return phrase_recurrence;
    },

    afficher_modale_recurrence : async function(){

        if(this.type_recurrence == 0)
            return;

        await this.$nextTick();

        let modele_recurrence = false;

        this.modeles_recurrence.forEach((modele) => {

            if(modele.id == this.type_recurrence)
                modele_recurrence = modele;
        });

        let date_de_debut = new Date(this.tache.tache_recurrence.date_de_debut);

        var jour = date_de_debut.getDay();

        jour = jour == 0 ? 7 : jour;

        let date_de_fin = structuredClone(date_de_debut);

        if(modele_recurrence.date_de_fin != '' && modele_recurrence.date_de_fin != null){

            date_de_fin_modele = modele_recurrence.date_de_fin.split(' ');

            this.fonctionnalite_valeur_date_fin = date_de_fin_modele[0];
            this.fonctionnalite_unite_date_fin = date_de_fin_modele[1];
        }

        if(['day', 'week'].includes(this.fonctionnalite_unite_date_fin)){
            this.fonctionnalite_unite_date_fin = 'date';
            this.fonctionnalite_valeur_date_fin = this.fonctionnalite_valeur_date_fin == 'week' ?
                7 * this.fonctionnalite_valeur_date_fin : this.fonctionnalite_valeur_date_fin;
        }

        this.fonctionnalite_unite_date_fin = this.fonctionnalite_unite_date_fin == 'year' ? 'fullYear' : this.fonctionnalite_unite_date_fin;

        this.fonctionnalite_unite_date_fin = this.fonctionnalite_unite_date_fin.charAt(0).toUpperCase() + this.fonctionnalite_unite_date_fin.slice(1);

        try{

            date_de_fin['set'+this.fonctionnalite_unite_date_fin](date_de_fin['get'+this.fonctionnalite_unite_date_fin]() + parseInt(this.fonctionnalite_valeur_date_fin));
        }
        catch(e){

            date_de_fin.setMonth(date_de_fin.getMonth() + 6)
        }

        this.tache.tache_recurrence.date_de_fin = date_de_fin.toISOString().slice(0,10);

        if(modele_recurrence.id == this.modeles_recurrence[this.modeles_recurrence.length - 1].id){

            this.$set(this.tache.tache_recurrence, 'type_frequence', 1);
            this.$set(this.tache.tache_recurrence, 'frequence', 1);
        }
        else if(modele_recurrence !== false){

            let champs_a_reprendre = ['frequence','frequence_jour_concerne','jours_concernes','type_frequence'];

            champs_a_reprendre.forEach((nom_champ) => {

                this.$set(this.tache.tache_recurrence, nom_champ, modele_recurrence[nom_champ]);
            });

            if(this.tache.tache_recurrence.type_frequence == 2 && !Array.isArray(this.tache.tache_recurrence.jours_concernes))
                this.$set(this.tache.tache_recurrence,"jours_concernes",[jour]);

        }

        this.modale_recurrence = true;
    },
@endpush

@push('donnees_pour_vuejs_mounted')
    await $.post({
        url: "{{ URL::to('/eden/element/tache_recurrence_modele/recuperer_tous_les_elements_ajax') }}",
        dataType: "json",
        data: {
            'valeurs_champs_multiselection': true,
        }
    }).done((donnees) => {

        let modeles_recurrence = donnees;
        modeles_recurrence.unshift({nom : 'Ne pas répéter', id : 0});
        modeles_recurrence.push({nom : 'Personnalisée', id : modeles_recurrence.length });

        this.$set(this, 'modeles_recurrence', donnees);
    });

    if(this.tache != undefined && this.tache.id > 0 && this.tache.parent_id) {

        $.ajax({

            url: "eden/calendrier/" + this.tache.id + "/recuperer_recurrence",
        }).done((donnees) => {

            if(donnees.succes === true){

                const recurrence = donnees.recurrence;
                this.$set(this.tache, 'tache_recurrence', recurrence);

                this.modeles_recurrence.forEach((modele) => {

                    if(modele.type_frequence == recurrence.type_frequence && modele.frequence == recurrence.frequence && 
                        modele.frequence_jour_concerne == recurrence.frequence_jour_concerne && 
                        JSON.stringify(modele.jours_concernes) == JSON.stringify(recurrence.jours_concernes)){
                        
                        this.$set(this, 'type_recurrence', modele.id);
                    }
                });

                if(!this.type_recurrence && recurrence)
                    this.$set(this, 'type_recurrence', this.modeles_recurrence[this.modeles_recurrence.length - 1].id);

                this.$set(this, 'modale_recurrence', false);
            }
        });
    }
@endpush