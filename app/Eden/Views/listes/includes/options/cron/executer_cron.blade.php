<span class="btn btn-mini btn-xs btn-default css_icone_option_dans_liste css_btn_action_theme"
      @click="executer_cron(ligne.element.id)">
    <span class="fa fa-play" data-toggle="tooltip" :title="$root.traduction('interface.listes.executer_cron')">
    </span>
</span>

@push('donnees_pour_vuejs_methods')

    executer_cron: function(id_cron) {

        var vue_instance = this;
        loading(true);

        $.ajax({

            url: "/eden/fiche/cron/"+id_cron+"/executer_cron",
            dataType: "json"
        }).done(async (donnees) => {

            loading(false);
            if(donnees.succes !== true) {

                await alerte_eden(donnees.message);
                return;
            }

            this.actualisation_filtres();
            toastr.success(this.$root.traduction('interface.listes.cron_execute'));
        });
    },
@endpush
