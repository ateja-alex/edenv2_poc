<div id="btn_imprimer_pdf">
    <span class="css_action_icon secondaire fa fa-file-pdf"
       v-if="this.dates.plage_de_dates != undefined"
       @click="affichage_impression_possibles = !affichage_impression_possibles"
      v-clique_en_dehors="{ func: () => {affichage_impression_possibles = false}}"
       :title="$root.traduction('interface.planning.imprimer')" data-toggle="tooltip"></span>
    <div class="dropdown-menu" v-show="affichage_impression_possibles">
        <span class="css_pointer" @click="imprimer()">@traduction('interface.planning.impression.individuel')</span>
        <span class="css_pointer" @click="imprimer('global')">@traduction('interface.planning.impression.equipe')</span>
    </div>
</div>

<button id="btn_envoyer_par_mail_pdf" class="css_action_icon secondaire fa fa-fw fa-envelope"
        @click="envoyer_par_mail()" :title="$root.traduction('interface.planning.envoyer_par_mail')"
        data-toggle="tooltip"></button>

@push('donnees_pour_vuejs_data')
    affichage_impression_possibles : false,
@endpush

@push('donnees_pour_vuejs_methods')

    imprimer: function(type = null) {

        this.affichage_impression_possibles = false;

        loading(true);

        $.get({
            url : '/eden/planning/imprimer/' + this.dates.plage_de_dates.debut_de_semaine+(type !== null ? '?type='+type : ''),
            dataType: "json",
        }).done((donnees) => {

            loading(false);

            if(donnees.succes !== true) {
                toastr.error(donnees.message);
                return;
            }

            window.open(donnees.url, '_blank');
        });
    },

    envoyer_par_mail: function() {

        loading(true);

        $.get({
            url : '/eden/planning/envoyer_par_mail/' + this.dates.plage_de_dates.debut_de_semaine,
            dataType: "json",
        }).done((donnees) => {

            loading(false);

            if(donnees.retour !== true) {
                alerte_eden(donnees.retour);
                return;
            }

            toastr.success(this.$root.traduction('messages.js.planning.mail_envoye'));
        });
    },

@endpush
