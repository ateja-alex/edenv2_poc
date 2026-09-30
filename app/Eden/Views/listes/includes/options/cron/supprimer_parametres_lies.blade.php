<span class="btn btn-mini btn-xs btn-default css_icone_option_dans_liste css_btn_action_theme"
      @click="supprimer_parametres_lies(ligne.element.id)">
    <span class="fa fa-trash" data-toggle="tooltip" :title="$root.traduction('interface.listes.supprimer_parametres_lies')">
    </span>
</span>

@push('donnees_pour_vuejs_methods')

    supprimer_parametres_lies: function(id_cron) {

        var vue_instance = this;
        loading(true);

        $.ajax({

            url: "{{ url('/eden/fiche/') }}/cron/"+id_cron+"/supprimer_parametres_lies",
            dataType: "json"
        }).done(async (donnees) => {

            loading(false);
            if(donnees.succes !== true) {

                await alerte_eden(donnees.message);
                return;
            }

            this.actualisation_filtres();
            toastr.success(this.$root.traduction('interface.listes.suppression_parametres'));
        });
    },
@endpush