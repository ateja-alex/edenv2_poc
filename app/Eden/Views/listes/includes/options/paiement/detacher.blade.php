<span v-if="documents_gescom.includes(ligne.element.type_element) && ligne.element.id_document > 0" class="btn btn-mini btn-xs btn-default css_icone_option_dans_liste css_btn_action_theme">
    <span class="fas fa-arrow-alt-circle-left" @click="detacher(ligne.element.id)"
          data-toggle="tooltip"
          :title="$root.traduction('interface.listes.paiement.detacher')" >
    </span>
</span>

@push('donnees_pour_vuejs_data')
    documents_gescom : {!! collect(\App\Eden\Variables::$documents_gescom) !!},
@endpush

@push('donnees_pour_vuejs_methods')

    detacher : async function(paiement_id){

        if(!await confirm_eden(this.$root.traduction('document.blocs.paiement.valider_detachement')))
            return

        loading(true);

        // on detache l'élément
        $.ajax({

            url: "/eden/document/paiement/detacher/"+paiement_id,
            dataType: "json"
        }).done(async (donnees) => {

            loading(false);

            if(donnees.retour !== true) {

                await erreur(donnees.retour);
                return;
            }

            this.actualisation_filtres();
        });
    },

@endpush
