<button class="css_action_icon secondaire fa fa-fw fa-file-pdf" @click="imprimer()" v-if="type_affichage_calendrier != 'mois'" :title="$root.traduction('interface.planning.imprimer')" data-toggle="tooltip"></button>
<button class="css_action_icon secondaire fa fa-fw fa-envelope" @click="envoyer_par_mail()" v-if="type_affichage_calendrier != 'mois'" :title="$root.traduction('interface.planning.envoyer_par_mail')" data-toggle="tooltip"></button>

@push('donnees_pour_vuejs_methods')

    imprimer: function() {

        loading(true);

        $.get({
            url : '/eden/calendrier/imprimer/' + this.date_debut.format_us,
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
            url : '/eden/calendrier/envoyer_par_mail/' + this.date_debut.format_us,
            dataType: "json",
        }).done((donnees) => {

            loading(false);

            if(donnees.retour !== true) {
                alerte_eden(donnees.retour);
                return;
            }

            toastr.success(this.$root.traduction('messages.js.calendrier.mail_envoye'));
        });
    },
@endpush