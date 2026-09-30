<template v-if="modale_impacts_documents">
    <transition name="modal">
        <div class="modal-mask">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">@traduction('interface.article_categorie_comptable.confirmation_enregistrement.impacts')</h5>
                        <button type="button" class="close" @click="modale_impacts_documents = false" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="height: 100px;">
                        <h6>@traduction('interface.article_categorie_comptable.confirmation_enregistrement.que_faire')</h6>
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>@traduction('champs_libres.facture_vente.reference_document.nom')</th>
                                    <th>@traduction('champs_libres.facture_vente.montant_document_ht.nom')</th>
                                    <th>@traduction('champs_libres.facture_vente.montant_document_ttc.nom')</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="document in impacts_documents">
                                    <td><a :href="'eden/document/'+document.type_element+'/'+document.id" v-html="document.reference_document"></a></td>
                                    <td>@{{ document.montant_document_ht | montant}}</td>
                                    <td>@{{ document.montant_document_ttc | montant}}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" @click="modale_impacts_documents = false;promise_enregistrement({retour : false})" class="btn btn-secondary css_pointer">
                            @traduction('interface.article_categorie_comptable.confirmation_enregistrement.annuler')
                        </button>
                        <button type="button" @click="enregistrement_post_impact" class="btn btn-primary css_pointer">
                            @traduction('interface.article_categorie_comptable.confirmation_enregistrement.enregistrer')
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>

@push('donnees_pour_vuejs_methods')

    confirmation_enregistrement : async function(informations){

        if(informations.filter(information => information.name == 'validation_enregistrement' && information.value).length == 1)
            return true;

        if(!(this.article_categorie_comptable.id > 0))
            return true;

        var impacts_documents = await $.post({
            url : 'eden/article_categorie_comptable/'+this.article_categorie_comptable.id+'/verification_impacts_documents',
            dataType : 'json',
            data : informations
        });

        if(impacts_documents === false || impacts_documents.length == 0)
            return true;

        this.impacts_documents = impacts_documents;
        this.modale_impacts_documents = true;

        loading(false);

        return await new Promise(resolve => {
            this.promise_enregistrement = resolve;
        });
    },

    enregistrement_post_impact : async function(){

        this.modale_impacts_documents = false;

        loading(true);

        var donnees = await this.$parent.enregistrer({validation_enregistrement : true});

        this.promise_enregistrement(donnees);
    },
@endpush

@push('donnees_pour_vuejs_data')
    modale_impacts_documents : false,
    impacts_documents : [],
    promise_enregistrement :null,
@endpush