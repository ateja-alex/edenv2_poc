<div class="entete">
    <h4>@traduction('composant.suivi_jours_travailles.titre')</h4>
    <div class="actions">
        <div class="gestion_dates">
            <span class="css_action_icon secondaire" @click="changement_periode(-1)" > &lt;&lt; </span>
            <span class="dropdown">
                <span class="titre" @click="choix_date">
                    <h5 v-html="affichage_date"></h5>
                    <i :class="'fas fa-chevron-'+(choix_date_en_cours ? 'up' : 'down')"></i>
                </span>
                <div v-if="choix_date_en_cours" class="dropdown-menu">
                    <div ref="calendrier_selection"></div>
                </div>
            </span>
            <span class="css_action_icon secondaire" @click="changement_periode(1)" > &gt;&gt; </span>
            <span class="css_action_icon secondaire" @click="changement_date_debut($root.aujourdhui)"><i class="fas fa-calendar-day"></i></span>
            <select class="type_affichage" @change="changement_date_debut(parametres.date_debut)" v-model="parametres.type_affichage">
                <option value="hebdomadaire">@{{ $root.traduction('composant.suivi_jours_travailles.hebdomadaire')}}</option>
                <option value="mensuel">@{{ $root.traduction('composant.suivi_jours_travailles.mensuel')}}</option>
            </select>
            <div class="enregistrement_en_cours">
                <img src="/eden/images/loader.svg" v-show="enregistrement_en_cours == 1" />
                <i class="fas fa-check" v-show="enregistrement_en_cours == 2" ></i>
            </div>
        </div>
        <div :class="'bloc_statut '+(enregistrement_en_cours == 1 ? 'enregistrement_en_cours' : '')" v-if="parametres.utilisateur_id > 0">
            <span class="texte" v-if="statut_saisie > 0">
                <span v-html="$root.traduction('composant.suivi_jours_travailles.statut_saisie.'+statut_saisie)"></span>
            </span>
            <template v-if="$root.moi.id == parametres.utilisateur_id || (validateurs != null && validateurs.includes($root.moi.id))">
                <span v-if="statut_saisie == 1" class="bouton annulation" @click="changer_statut_saisie(0)">
                    @traduction('composant.suivi_jours_travailles.actions.annuler_saisie_terminee')
                </span>
                <span v-if="statut_saisie == 0" class="bouton validation" @click="changer_statut_saisie(1)">
                    @traduction('composant.suivi_jours_travailles.actions.terminer_saisie')
                </span>
            </template>
            <template v-if="validateurs.includes($root.moi.id)">
                <span v-if="statut_saisie == 2" class="bouton annulation" @click="changer_statut_saisie(1)">
                    @traduction('composant.suivi_jours_travailles.actions.annuler_saisie_validee')
                </span>
                <span v-if="statut_saisie == 1" class="bouton validation" @click="changer_statut_saisie(2)">
                    @traduction('composant.suivi_jours_travailles.actions.valider_saisie')
                </span>
            </template>
        </div>
        <div class="utilisateur">
            <template v-if="utilisateurs_disponibles == false && !this.$root.intranet">

                {!! management('jour_travaille')->champ('utilisateur_id')
                        ->vmodel(true,'parametres')->cree() !!}
            </template>
            <template v-else-if="utilisateurs_disponibles.length > 1 && !this.$root.intranet">
                @php
                    $management_champ = management('jour_travaille')->champ('utilisateur_id')->vmodel(true,'parametres');
                    $management_champ->modele = clone $management_champ->modele;
                    $management_champ->filtrage('[{\"champ\":\"id\",\"condition_ou\":false,\"condition\":\"WhereIn\",\"symbole\":\"\",\"valeur\":utilisateurs_disponibles.join(",")}]');
                @endphp

                {!! $management_champ->cree() !!}
            </template>
            <template v-else>
                @{{ $root.moi.prenom+' '+$root.moi.nom }}
            </template>
        </div>
    </div>
</div>

@push('donnees_pour_vuejs_data')
    parametres : {
        type_affichage: 'hebdomadaire',
        date_debut : null,
        date_fin : null,
        utilisateur_id : this.$root.moi.id,
    },
    affichage_date:'',
    choix_date_en_cours : false,
    validateurs : [],
    utilisateurs_disponibles : [this.$root.moi.id],
    enregistrement_en_cours : 0,
    statut_saisie: 0,
@endpush

@push('donnees_pour_vuejs_methods')

    choix_date : function(){

        this.choix_date_en_cours = !(this.choix_date_en_cours);

        if(this.choix_date_en_cours)
            this.$nextTick(() => this.mise_en_place_calendrier());
    },

    mise_en_place_calendrier : function(){
        var calendrier_selection = this.$refs.calendrier_selection;

        var langue = this.$root.moi.langue !== null ? this.$root.moi.langue : 'fr';

        var datepicker = {
            language: langue,
            todayHighlight: true
        };

        if (this.parametres.type_affichage == 'mensuel'){
            datepicker.viewMode = "months";
            datepicker.minViewMode = "months";
        }

        $(calendrier_selection).datepicker(datepicker);

        var date_debut = moment(this.parametres.date_debut, 'YYYY-MM-DD');

        if(this.parametres.type_affichage == 'hebdomadaire'){
            let dates = [date_debut.toDate()];

            for(let i = 0; i<=5;i++){
                date_debut.add(1, 'd');
                dates.push(date_debut.toDate());
            }

            $(calendrier_selection).datepicker('setDates', dates);
        }
        else
            $(calendrier_selection).datepicker('setDate', date_debut.toDate());

        var component = this;

        $(calendrier_selection).on("changeDate", function (e) {
            if(e.dates.length > 1)
                return;

            var date = $(this).datepicker('getDate');
            date = $.datepicker.formatDate("yy-mm-dd", date)
            component.changement_date_debut(date);
        });
    },

    changement_date_debut : function(date){

        if(this.parametres.type_affichage == 'hebdomadaire'){
            date = new Date(date);
            var jour = date.getDay();
            var difference = date.getDate() - jour + (jour == 0 ? -6 : 1);
            var date_debut = new Date(structuredClone(date).setDate(difference));
            var date_fin = new Date(date.setDate(difference + 6));
        }
        else{
            date = new Date(date);
            var date_debut = new Date(date.getFullYear(),date.getMonth());
            var date_fin = new Date(date_debut.getFullYear(),date_debut.getMonth()+1,0);
        }

        this.choix_date_en_cours = false;

        this.parametres.date_debut = this.$root.formate_date(date_debut).en;
        this.parametres.date_fin = this.$root.formate_date(date_fin).en;
        this.actualisation();
    },

    changement_periode : function(quotient){

        var date = new Date(this.parametres.date_debut);

        if(this.parametres.type_affichage == 'mensuel')
            date = new Date(date.setMonth(date.getMonth()+(1*quotient)));
        else
            date = new Date(date.setDate(date.getDate()+(7*quotient)));

        this.changement_date_debut(this.$root.formate_date(date).en);
    },

    changer_statut_saisie : function(statut){

        var commentaires_vides = this.jours_travailles.filter(
            (jour_travaille) => jour_travaille.jour_travaille.id > 0
            && (jour_travaille.non_travaille || jour_travaille.jour_travaille.repos != 1 || jour_travaille.jour_travaille.matin != 1 || jour_travaille.jour_travaille.apres_midi != 1)
            && (jour_travaille.jour_travaille.commentaire == null || jour_travaille.jour_travaille.commentaire == '')
        );

        if(commentaires_vides.length > 0){
            toastr.error(this.$root.traduction('composant.suivi_jours_travailles.commentaires_non_remplis'));

            var dates = commentaires_vides.map((commentaire) => commentaire.date);

            for(bloc_saisie of this.$refs.bloc_saisie){

                if(dates.includes(bloc_saisie.$vnode.key)){

                    var commentaire = bloc_saisie.$refs.commentaire;
                    commentaire.classList.add("non_rempli");
                    $(commentaire).find("textarea").on('input',() => {
                        commentaire.classList.remove("non_rempli");
                    });
                }
            }

            return;
        }

        this.enregistrement_en_cours = 1;

        $.post({
            url:'{{route('suivi_jours_travailles.changer_statut_saisie', [], false)}}',
            dataType:'json',
            data:{
                parametres : this.parametres,
                nouveau_statut : statut,
            }
        }).done((donnees) => {
            this.jours_travailles = donnees.jours_travailles;
            this.statut_saisie = statut;
            this.fin_enregistrement();
        });
    },
@endpush

@push('donnees_pour_vuejs_mounted')

    this.$root.$on('selection-element',(parametres) => {

        if(parametres.nom_champ == 'utilisateur_id')
            this.actualisation();
    });
@endpush
