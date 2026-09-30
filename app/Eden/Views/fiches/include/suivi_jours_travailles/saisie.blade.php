<div class="saisie">
    <div v-for="jour_travaille in jours_travailles"  class="bloc_jour">
        <div class="entete_jour">
            <span :class="'jour '+((jour_travaille.non_travaille && (!jour_travaille.jour_travaille || !jour_travaille.jour_travaille.id)) || jour_travaille.hors_borne ? 'non_travaille' : '')">
                <span v-html="jour_travaille.affichage"></span>
            </span>
            <div class="suppression_jour"  v-if="jour_travaille.non_travaille && jour_travaille.jour_travaille && jour_travaille.jour_travaille.id && (statut_saisie == 0 && jour_travaille.statut == 0)" @click="suppression_jour(jour_travaille)">
                <i class="fas fa-times"></i>
            </div>
            <div class="ajout_jour"  v-if="jour_travaille.non_travaille && (!jour_travaille.jour_travaille || !jour_travaille.jour_travaille.id) && !jour_travaille.hors_borne && (statut_saisie == 0 && jour_travaille.statut == 0)" @click="ajout_non_travaille(jour_travaille)">
                <i class="fas fa-plus"></i>
            </div>
            <div class="statuts_jours">
                <i class="far fa-calendar-times" v-if="jour_travaille.jour_ferie !== false" :title="jour_travaille.jour_ferie.map((jour) => jour.chaine_affichage).join(', ')"></i>
                <i class="fas fa-lock" v-if="jour_travaille.jour_travaille.statut == 1" title="saisie du jour terminée"></i>
                <i class="fas fa-check" v-if="jour_travaille.jour_travaille.statut == 2" title="saisie du jour validé"></i>
            </div>
        </div>
        <div v-if="(chargement !== false && chargement !== 1) || !(parametres.utilisateur_id > 0)" style="display: flex;align-items: center;justify-content: center;">
            <img style="width: 35px;height: 35px;" src="/eden/images/ajax_loader.gif" />
        </div>
        <template v-else>
            <div class="corps_non_travaille" v-if="(jour_travaille.non_travaille && (!jour_travaille.jour_travaille || !jour_travaille.jour_travaille.id)) || jour_travaille.hors_borne"
                 @mouseover="ajout_hors_zone = jour_travaille.date"
                 @mouseleave="ajout_hors_zone = null"
                >
                <div v-if="!jour_travaille.hors_borne && ajout_hors_zone == jour_travaille.date && (statut_saisie == 0 && jour_travaille.statut == 0)" class="ajout" @click="ajout_non_travaille(jour_travaille)">
                    <i class="fas fa-plus"></i>
                </div>
                <div v-else class="indisponible"></div>
            </div>
            <bloc-suivi-jours-travailles v-else
                 :jour_travaille="jour_travaille"
                 :parametres="parametres"
                 v-on:enregistrement_valeur="enregistrement_en_cours = 1"
                 v-on:fin_enregistrement_valeur="fin_enregistrement()"
                 ref="bloc_saisie" :key="jour_travaille.date"
            >
            </bloc-suivi-jours-travailles>
        </template>
    </div>
</div>

@push('donnees_pour_vuejs_data')
    jours_travailles : [],
    chargement:1,
    ajout_hors_zone:null,
    modele_par_defaut_jour_travaille : {!! modele_par_defaut('jour_travaille') !!},
@endpush

@push('donnees_pour_vuejs_mounted')
    this.initialisation();
@endpush

@push('donnees_pour_vuejs_methods')

    initialisation : function(){

        $.post({
            url:'{{route('suivi_jours_travailles.initialisation', [], false)}}',
            dataType:'json'
        }).done((donnees) => {

            this.chargement = false;
            this.jours_travailles = donnees.jours_travailles;
            this.parametres = donnees.parametres;
            this.affichage_date = donnees.affichage_date;
            this.utilisateurs_disponibles = donnees.utilisateurs_disponibles;
            this.validateurs = donnees.validateurs;
            this.statut_saisie = donnees.statut_saisie;
        });
    },

    actualisation : function(){

        if(this.chargement !== false && this.chargement !== 1)
            this.chargement.abort();

        this.chargement = $.post({
            url:'{{route('suivi_jours_travailles.actualisation', [], false)}}',
            dataType:'json',
            data:this.parametres
        }).done((donnees) => {
            this.chargement = false;
            this.jours_travailles = donnees.jours_travailles;
            this.parametres = donnees.parametres;
            this.affichage_date = donnees.affichage_date;
            this.statut_saisie = donnees.statut_saisie;
            this.validateurs = donnees.validateurs;
        });
    },

    ajout_non_travaille : function(jour_travaille){

        this.ajout_hors_zone = null;

        var url = '{{ route('base_eden.element.creer','jour_travaille', false) }}';

        var donnees= {
            matin: 1,
            apres_midi: 1,
            repos: 1,
            total_jours:1,
            date: jour_travaille.date,
            utilisateur_id: this.parametres.utilisateur_id,
            ferie: jour_travaille.jour_ferie !== false ? 1 : 0
        }

        this.enregistrement_en_cours = 1;

        $.post({
            url: url,
            dataType: "json",
            data: donnees
        }).done((donnees) => {
            this.$set(jour_travaille,'jour_travaille',donnees.element);
            this.fin_enregistrement();
        });
    },

    suppression_jour : function(jour_travaille){

        this.enregistrement_en_cours = 1;

        $.ajax({
            url: 'eden/element/jour_travaille/'+jour_travaille.jour_travaille.id+'/supprimer',
            dataType: "json"
        }).done(() => {
            this.$set(jour_travaille,'jour_travaille',structuredClone(this.modele_par_defaut_jour_travaille));
            this.fin_enregistrement();
        });
    },

    fin_enregistrement : function(){
        this.enregistrement_en_cours = 2;
        setTimeout(() => { this.enregistrement_en_cours = 0},1000);
    },
@endpush
