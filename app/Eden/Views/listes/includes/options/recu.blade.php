<a v-if="ligne.element.recue != 1 && ligne.element.recu != 1" @click="validation_reception(ligne.element)">
    <span class="btn btn-mini btn-xs btn-default css_icone_option_dans_liste">
        <span class="fas fa-check" data-toggle="tooltip"
              :title="$root.traduction('interface.listes.valider_reception')">
        </span>
    </span>
</a>

@push('donnees_pour_vuejs_methods')

    /**
    *
    * Permet d'enregistrer la réception d'une ligne de commande fournisseur
    *
    */
    validation_reception: async function(element){

        loading(true);

        if(element.a_livrer_chez_client == 1){

            if(!await confirm_eden(this.$root.traduction('interface.listes.valider_livraison_chez_client'))) {
                loading(false);
                return;
            }
        }

        // on fait un appel ajax pour enregistrer
        $.post({

            url: "eden/document/achat/commande/reception_totale_ligne/" + element.id,
            dataType: "json"
        }).done(async (donnees) => {

            loading(false);

            if (donnees.retour !== true) {

                await erreur(donnees.retour);
                return;
            }

            // on actualise la liste
            this.actualisation_filtres();
        });


    },
@endpush