<a v-if="ligne.element.statut_envoi == 3"
   @click="relancer_envoi_cycle_de_vie(ligne.element.id)"
   style="cursor:pointer"> <span class="btn btn-mini btn-xs btn-default css_icone_option_dans_liste">
        <span class="fas fa-redo"
              data-toggle="tooltip"
              :title="$root.traduction('interface.listes.relancer_envoi_cycle_de_vie')">
        </span>
    </span>
</a>

@push('donnees_pour_vuejs_methods')

    relancer_envoi_cycle_de_vie: async function(id_element){

        loading(true);

        var url = "{{ route('base_eden.element.enregistrer', [$type_element_options, '__ID__']) }}".replace('__ID__', id_element);

        $.post({
            url: url,
            data: { statut_envoi: 1, fichier_cdar: '', motif_erreur_envoi: '' },
            dataType: 'json',
        }).done(async (donnees) => {

            loading(false);

            if(donnees.retour !== true){
                await erreur(donnees.retour);
                return;
            }

            this.actualisation_filtres();
        }).fail(() => loading(false));
    },
@endpush
