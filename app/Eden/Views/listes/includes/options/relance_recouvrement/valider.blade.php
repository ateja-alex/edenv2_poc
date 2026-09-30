<a 	class="btn btn-mini btn-xs btn-default css_icone_option_dans_liste css_btn_action_theme"
       @click="validation_relance(ligne.element)">
    <span
        class="fa fa-check"
        data-toggle="tooltip"
        :title="$root.traduction('interface.listes.marquer_comme_fait')">
    </span>
</a>

@push('donnees_pour_vuejs_methods')

    validation_relance : function(element){

        $.ajax({
            method: 'POST',
            url: '/eden/element/relance_recouvrement/'+element.id+'/enregistrer',
            dataType: 'json',
            data: { a_faire: 0 }
        }).done((donnees) => {

            if(donnees.retour !== true) {

                erreur(donnees.retour);
                return;
            }

            toastr.success(this.$root.traduction('interface.relance_recouvrement.valider'));
            this.actualisation_filtres();

        });
    },

@endpush