<a v-if="ligne.element.terminee != 1" @click="validation_tache(ligne.element.id,ligne.element.client_id)">
    <span class="btn btn-mini btn-xs btn-default css_icone_option_dans_liste">
        <span class="fas fa-check" data-toggle="tooltip"
              :title="$root.traduction('interface.listes.valider_tache')">
        </span>
    </span>
</a>

@push('donnees_pour_vuejs_methods')
    /**
    *
    * Valide la tache
    *
    */
    validation_tache : function(tache_id, client_id){

        $.post({

            url: "eden/fiche/client/" + client_id + "/post/valider_tache",
            dataType: "json",
            data: {
            tache_id : tache_id,
        }
        }).done(async (donnees) => {

            if (donnees.retour !== true) {

                await erreur(donnees.retour);
                return;
            }

            // on actualise la liste
            this.actualisation_filtres();
        });

    },
@endpush