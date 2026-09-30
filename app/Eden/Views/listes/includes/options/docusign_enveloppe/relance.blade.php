<span v-if="[6,7].includes(ligne.element.statut)" class="btn btn-mini btn-xs btn-default css_icone_option_dans_liste css_btn_action_theme">
    <span class="fas fa-redo"
          @click="relance_signature(ligne.element.id)"
          data-toggle="tooltip"
          :title="$root.traduction('interface.listes.docusign_enveloppe.relance')"
          :id_element="ligne.element.id">
    </span>
</span>

@push('donnees_pour_vuejs_methods')
    relance_signature: async function(id_element) {

        if(!await confirm_eden())
            return;

        loading(true);
        
        $.post({
            url: '/eden/docusign/relance_signature/'+id_element,
            dataType:'json'
        }).done((retour) => {
            
            if(retour !== true)
                toastr.error(this.$root.traduction('messages.js.docusign_enveloppe.relance_signature.erreur'));
            else
                toastr.success(this.$root.traduction('messages.js.docusign_enveloppe.relance_signature.succes'));

            loading(false);
        })
    },
@endpush