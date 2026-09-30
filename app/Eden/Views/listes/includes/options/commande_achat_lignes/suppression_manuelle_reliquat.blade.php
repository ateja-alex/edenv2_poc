<div v-if="ligne.element.recue != 1 || ligne.element.suppression_manuelle_reliquat == 1" 
    :title="$root.traduction('document.suppression_manuelle_reliquat.etat_'+(ligne.element.suppression_manuelle_reliquat == 1 ? 0 : 1))" 
    class="btn btn-mini btn-xs btn-default css_icone_option_dans_liste suppression_manuelle_reliquat"
    @click="suppression_manuelle_reliquat(ligne.element)">
    <i class="fas fa-truck-loading"></i>
    <i v-if="ligne.element.suppression_manuelle_reliquat == 1" class="fa fa-undo"></i>
    <i v-else class="fa fa-times"></i>
</div>

@push('donnees_pour_vuejs_methods')

    /**
    *
    * Permet d'enregistrer la réception d'une ligne de commande fournisseur
    *
    */
    suppression_manuelle_reliquat: function(element){

        loading(true);

        var etat = element.suppression_manuelle_reliquat == 1 ? 0 : 1;

        // on fait un appel ajax pour enregistrer
        $.post({

            url: "eden/document/achat/commande/suppression_manuelle_reliquat",
            dataType: "json",
            data: {
                ids_lignes: [element.id],
                suppression_manuelle_reliquat: etat
            }
        }).done(async (donnees) => {

            loading(false);

            if (donnees.succes !== true) {
                await erreur(donnees.succes);
                return;
            }

            toastr.success(this.$root.traduction('document.actions.suppression_manuelle_reliquat.retour_etat_'+etat));
            this.actualisation_filtres();
        });


    },
@endpush