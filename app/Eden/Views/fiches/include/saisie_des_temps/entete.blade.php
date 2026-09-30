<div class="row entete">
    <div class="col-md-12">
        <div class="card mb-3">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4> @traduction('interface.saisie_des_temps.titre') </h4>

                    <div v-if="informations_dates.statut_saisie_terminee != null" class="bloc_statut">
                        <span class="texte">
                            <span v-html="$root.traduction('interface.saisie_des_temps.statut_saisie_terminee.'+informations_dates.statut_saisie_terminee)"></span>
                            <span v-if="informations_dates.statut_saisie_validee > 0" v-html="$root.traduction('interface.saisie_des_temps.statut_saisie_validee.'+informations_dates.statut_saisie_validee)"></span>
                        </span>
                        <template v-if="$root.moi.id == feuille_de_temps.utilisateur_id || (informations_dates.validateurs != null && informations_dates.validateurs.includes($root.moi.id))">
                            <span v-if="informations_dates.statut_saisie_terminee > 0 && !(informations_dates.statut_saisie_validee > 0)" class="bouton annulation" @click="changer_statut_saisie(0,'terminee')">
                                @traduction('interface.saisie_des_temps.actions.annuler_saisie_terminee')
                            </span>
                            <span v-if="informations_dates.statut_saisie_terminee < 2" class="bouton validation" @click="changer_statut_saisie(2,'terminee')">
                                @traduction('interface.saisie_des_temps.actions.terminer_saisie')
                            </span>
                        </template>
                        <template v-if="informations_dates.statut_saisie_terminee == 2 && informations_dates.validateurs != null && informations_dates.validateurs.includes($root.moi.id)">
                            <span v-if="informations_dates.statut_saisie_validee > 0" class="bouton annulation" @click="changer_statut_saisie(0,'validee')">
                                @traduction('interface.saisie_des_temps.actions.annuler_saisie_validee')
                            </span>
                            <span v-if="informations_dates.statut_saisie_validee < 2" class="bouton validation" @click="changer_statut_saisie(2,'validee')">
                                @traduction('interface.saisie_des_temps.actions.valider_saisie')
                            </span>
                        </template>
                    </div>

                    <div class="d-flex align-items-center css_form options">

                        <select v-model="mode_affichage" class="selection_affichage" @change="charger_dates">
                            <option v-for="valeur in $root.valeurs_listes_formatees[631]" v-if="valeur.desactivee != 1 && valeur.id_valeur != 0" :value="valeur.id_valeur">@{{ valeur.valeur }}</option>
                        </select>

                        <div class="selection_dates">

                            <span @click="changement_date(-1)">
                                <i class="css_action_icon mineur fas fa-backward"></i>
                            </span>

                            <div class="date">
                                <h4 @click="choix_dates_en_cours = !(choix_dates_en_cours)" class="css_pointer">
                                    <span v-html="informations_dates.affichage_date"></span>
                                    <span v-if="choix_dates_en_cours"><i class="fas fa-chevron-up"></i></span>
                                    <span v-else ><i class="fas fa-chevron-down"></i></span>
                                </h4>

                                <div v-show="choix_dates_en_cours" class="selecteur_datepicker">
                                    <div class="selection_datepicker"></div>
                                </div>
                            </div>

                            <span @click="changement_date(1)">
                                <i class="css_action_icon mineur fas fa-forward"></i>
                            </span>

                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body css_form choix_element">

                <div class="type_element" v-if="informations_dates.statut_saisie_terminee == 0">
                    <div class="col-md-2">
                        @traduction('interface.saisie_des_temps.entete.choix_element')
                    </div>
                    <div class="col-md-4">
                        {!! management('feuille_de_temps')->champ('type_element')->cree() !!}
                    </div>
                    <div class="col-md-4">
                        {!! management('feuille_de_temps')->champ('element_id')->cree() !!}
                    </div>
                </div>
                <div class="utilisateur">
                    <div class="col-md-2">
                        @traduction('interface.saisie_des_temps.utilisateur') :
                    </div>
                    <div class="col-md-4">
                        <template v-if="utilisateurs_disponibles == false && !this.$root.intranet">

                            {!! management('feuille_de_temps')->champ('utilisateur_id')->cree() !!}
                        </template>
                        <template v-else-if="utilisateurs_disponibles.length > 1 && !this.$root.intranet">
                            @php
                                $management_champ = management('feuille_de_temps')->champ('utilisateur_id');
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

        </div>
    </div>
</div>

@push('donnees_pour_vuejs_data')
    choix_dates_en_cours : false,
    utilisateur_a_valider : [],
@endpush

@push('donnees_pour_vuejs_computed')

    utilisateurs_disponibles : function(){

        @if(empty(fiche('saisie_des_temps')->structure_fiche()['options']['restriction_utilisateurs']))
            return false;
        @else

            if(this.$root.moi.type_utilisateur == 2)
                return false;

            var utilisateurs_disponibles = structuredClone(this.utilisateur_a_valider);

            utilisateurs_disponibles.push(this.$root.moi.id);

            return utilisateurs_disponibles;
        @endif
    },
@endpush

@push('donnees_pour_vuejs_mounted')

    var component = this;

    this.$on('changement',async () => {

        await $(".selection_datepicker").datepicker('remove');

        var langue = this.$root.moi.langue !== null ? this.$root.moi.langue : 'fr';

        var jours_desactives = {!! collect($structure['options']['jours']) !!};

        if (this.mode_affichage == 3) {

            $(".selection_datepicker").datepicker({
                viewMode: "months",
                minViewMode: "months",
                language: langue
            });

        } else {
            $(".selection_datepicker").datepicker({
                language: langue,
                beforeShowDay: (date) => {
                    var jour_date = date.getDay() == 0 ? 6 : date.getDay() - 1;
                    return jours_desactives.includes(jour_date.toString());
                },
            })
        }

        var date = new Date(this.feuille_de_temps.date);

        $(".selection_datepicker").datepicker("setDate",date);

        $(".selection_datepicker").on("changeDate", function (e) {

            var date = $(this).datepicker('getDate');
            date = $.datepicker.formatDate("yy-mm-dd", date);

            if(component.feuille_de_temps.date == date)
                return;

            component.feuille_de_temps.date = date;
            component.charger_dates();
            component.choix_dates_en_cours = false;
        });
    });
@endpush

@push('donnees_pour_vuejs_methods')
    changement_date: async function(indicateur){

        var changement = this.mode_affichage == 2 ? 7 : 1;

        changement *= indicateur;

        date = new Date(this.feuille_de_temps.date);

        if(this.mode_affichage == 3)
            date.setMonth(date.getMonth()+changement);
        else
            date.setDate(date.getDate()+changement);

        var jours_desactives = {!! collect($structure['options']['jours']) !!};

        var jour_date = date.getDay() == 0 ? 6 : date.getDay() - 1;

        while(!jours_desactives.includes(jour_date.toString())){

            date.setDate(date.getDate()+ 1 * indicateur);

            jour_date = date.getDay() == 0 ? 6 : date.getDay() - 1;

        }

        this.feuille_de_temps.date = date.getFullYear()+'-'+(1+date.getMonth()).toString().padStart(2, '0')+'-'+date.getDate().toString().padStart(2, '0');

        this.charger_dates();
    },

    changer_statut_saisie : function(nouveau_statut,type){

        loading(true);

        $.post({
            url: 'eden/saisie_des_temps/changer_statut_saisie/'+type,
            data : {
                parametres : this.feuille_de_temps,
                nouveau_statut : nouveau_statut,
                date_debut: this.informations_dates.debut,
                date_fin: this.informations_dates.fin,
            }
        }).done((donnees) => {

            loading(false);

            if(donnees.retour !== true) {

                erreur(donnees.message);
                this.enregistrement_en_cours = null;
                return false;
            }

            this.informations_dates.feuille_de_temps_periode_terminee = donnees.informations.feuille_de_temps_periode_terminee;
            this.informations_dates.statut_saisie_terminee = donnees.informations.statut_saisie_terminee;

            if(donnees.informations.feuille_de_temps_periode_validee != null){
                this.informations_dates.feuille_de_temps_periode_validee = donnees.informations.feuille_de_temps_periode_validee;
                this.informations_dates.statut_saisie_validee = donnees.informations.statut_saisie_validee;
            }

            this.$emit('changement_valeur');
        });
    },
@endpush