<template v-if="paiements_non_rattache.length">
    <div class="row">
        <div class="col-sm-12 css_form_ligne_titre">@traduction('document.blocs.paiement.rattacher')</div>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover" width="100%" cellspacing="0">
            <thead>
            <tr>
                <td></td>
                <td>#</td>
                <td>@traduction('document.blocs.paiement.date')</td>
                <td>@traduction('document.blocs.paiement.titre_paiement')</td>
                <td>@traduction('document.blocs.paiement.montant')</td>
            </tr>
            </thead>
            <tbody>
            <tr v-for="paiement_non_rattache in paiements_non_rattache">
                <td style="cursor:pointer;text-align:center;">
                    <a @click="rattache_paiement(paiement_non_rattache.id)">
                        <i class="fas fa-arrow-alt-circle-right"></i>
                    </a>
                </td>
                <td>@{{ paiement_non_rattache.id }}</td>
                <td>@{{ paiement_non_rattache.date | date }}</td>
                <td>@{{ paiement_non_rattache.titre }}</td>
                <td>@{{ paiement_non_rattache.montant | montant}}</td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

@push('donnees_pour_vuejs_data')
    paiements_non_rattache : [],
    types_documents : {!! collect(\App\Eden\Variables::$documents_gescom) !!},
@endpush

@push('donnees_pour_vuejs_mounted')

    if(this.$parent && this.$parent.$parent && this.$parent.$parent.filtres_pour_fiche &&
        this.$parent.$parent.filtres_pour_fiche.type_element && this.$parent.$parent.filtres_pour_fiche.type_element.elements_ids){

        var type_element = this.$parent.$parent.filtres_pour_fiche.type_element.elements_ids[0].type_element;
        var element_id = this.$parent.$parent.filtres_pour_fiche.type_element.elements_ids[0].id;

        if(this.types_documents.includes(type_element)){

            $.ajax({
                url: "eden/document/paiement/paiements_non_rattaches/"+type_element+"/"+element_id,
                dataType: "json",
                method: "post",
            }).done(async (donnees) => {
                this.paiements_non_rattache = donnees;
            });
        }
    }
@endpush

@push('donnees_pour_vuejs_methods')

    rattache_paiement: async function(id_paiement) {

        if(!await confirm_eden())
            return;

        var vue_liste = this.$parent.$parent;

        var type_element = vue_liste.filtres_pour_fiche.type_element.elements_ids[0].type_element;
        var element_id = vue_liste.filtres_pour_fiche.type_element.elements_ids[0].id;

        // on enregistre les infos du champ libre
        $.post({

            url: "eden/document/paiement/ajouter_existant/"+type_element+"/"+element_id,
            dataType: "json",
            method: "post",
            data: {
                id_paiement : id_paiement,
            }
        }).done(async (donnees) => {

            if(donnees.retour !== true){
                await erreur(donnees.retour);
                return;
            }

            info(this.$root.traduction('document.blocs.paiement.succes_rattachement'));
            vue_liste.retour_a_la_liste();

            vue_liste.actualisation_filtres();
        });
    },

@endpush