<a 	class="btn btn-mini btn-xs btn-default css_icone_option_dans_liste css_btn_action_theme"
      @click="lancer_requete(ligne.element)">
    <span
        class="fas fa-play"
        data-toggle="tooltip"
        :title="$root.traduction('interface.listes.lancer_requete')">
    </span>
</a>

@push('donnees_pour_vuejs_methods')

    lancer_requete : function(element){

        $.ajax({
            url: 'eden/fiche/requete_sql_cron/'+element.id+'/lancer_requete',
            dataType: 'json',
        }).done((donnees) => {

            if(donnees.retour !== true) {
                erreur(donnees.retour);
                return;
            }

            info(this.$root.traduction('interface.requete_sql_cron.lancement_effectue'));

        });
    },
@endpush