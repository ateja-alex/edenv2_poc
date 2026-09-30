<span class="btn btn-mini btn-xs btn-default css_icone_option_dans_liste css_btn_action_theme">
    <span class="fa fa-times" data-toggle="tooltip"
          :title="$root.traduction('interface.listes.refuser')"
        @click="ouverture_modale_refus_approbation(ligne.element)">
    </span>
</span>

@push('modales')
<!-- modale refus d'approbation -->
<template v-if="modal_refuser_approbation">
    <transition name="modal" >
        <div class="modal-mask">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">@traduction('interface.listes.refuser_une_approbation')</h5>
                    </div>
                    <div class="modal-body css_form">
                        <div class="row">
                            <div class="col-md-3">@traduction('interface.listes.commentaire_refus_approbation')</div>
                            <div class="col-md-12"><input type="text" v-model="commentaire_refus" /></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="modal_refuser_approbation = false">@traduction('interface.listes.fermer')</button>
                        <button type="button" class="btn btn-primary" @click="enregistrer_refus_approbation()">@traduction('interface.listes.enregistrer')</button>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>
@endpush

@push('donnees_pour_vuejs_data')
    modele_approbation : null,
    commentaire_refus : '',
    modal_refuser_approbation : false,
@endpush

@push('donnees_pour_vuejs_methods')

    ouverture_modale_refus_approbation : function(element){


        this.commentaire_refus = '';
        this.modele_approbation = element;
        this.modal_refuser_approbation = true;
    },

    enregistrer_refus_approbation: function(){

        loading(true);

        // on fait un appel ajax valider ou non l'action
        $.post({

            url: "eden/element/"+this.modele_approbation.type_element+"/"+this.modele_approbation.element_id+"/refuser_action",
            dataType: "json",
            data:{
                element_id: this.modele_approbation.element_id,
                type_element: this.modele_approbation.type_element,
                approbation_id: this.modele_approbation.id,
                commentaire_refus: this.commentaire_refus,
            },
            method: 'post'
        }).done(async (donnees) => {

            if(donnees.succes !== true)
                await alerte_eden(donnees.succes);
            else{
                this.modal_refuser_approbation = false;
                this.actualisation_filtres();
            }

            loading(false);
        });

    },
@endpush