<span v-if="ligne.element.type_synchronisation == 2 && !ligne.element.desactive"
      class="btn btn-mini btn-xs btn-default css_icone_option_dans_liste css_btn_action_theme"
      @click="lancer_synchronisation_service(ligne.element.id)">
    <span class="fa fa-play" data-toggle="tooltip" :title="$root.traduction('interface.listes.lancer_synchronisation')">
    </span>
</span>

@push('donnees_pour_vuejs_methods')

    lancer_synchronisation_service: function(id_element) {

        loading(true);

        $.ajax({

            url: "{{ route('eden_cron.synchronisation_service', '__ID__') }}".replace('__ID__', id_element),
            dataType: "json"
        }).done(async (donnees) => {

            loading(false);

            if(donnees.succes !== true) {

                await alerte_eden(donnees.message);
                return;
            }

            this.actualisation_filtres();
            toastr.success(donnees.message);
        }).fail(() => loading(false));
    },
@endpush
