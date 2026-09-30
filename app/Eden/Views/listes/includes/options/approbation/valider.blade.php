<span class="btn btn-mini btn-xs btn-default css_icone_option_dans_liste css_btn_action_theme">
    <span class="fa fa-check"
          data-toggle="tooltip"
          @click="valider_action(ligne.element.element_id,ligne.element.type_element,ligne.element.action,ligne.element.id)"
          :title="$root.traduction('interface.listes.valider')">
    </span>
</span>

@push('donnees_pour_vuejs_methods')
    valider_action: function(element_id, type_element, action, approbation_id){

        loading(true);

        // on fait un appel ajax valider ou non l'action
        $.post({

            url: "eden/element/"+type_element+"/"+element_id+"/valider_action",
            dataType: "json",
            data:{
                element_id: element_id,
                type_element: type_element,
                action: action,
                approbation_id: approbation_id,
            },
            method: 'post'
        }).done(async (donnees) => {

            if(donnees.succes !== true)
                await alerte_eden(donnees.succes);
            else
                this.actualisation_filtres();

            loading(false);
        });
    },
@endpush