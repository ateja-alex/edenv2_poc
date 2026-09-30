@extends('eden::listes.includes.actions.action_en_masse_generique',['action' => 'imprimer_pdf'])

@section('texte_modale_imprimer_pdf')

    <form method="post" ref="formulaire_impressions_pdf" action="{{ route('base_eden.element.imprimer_en_masse', [], false) }}" target="_blank">
        <input type="hidden" name="ids_elements" :value="JSON.stringify(ids_elements)"/>
        <input type="hidden" name="type_element" :value="this.liste.type_element" />

        <div class="row">
            <div class="col-md-2">
                @traduction('interface.listes.imprimer_modele.separer_documents')
            </div>
            <div class="col-md-2">
                <input type="checkbox" v-model="pdf_separe" name="pdf_separe">
            </div>
        </div>
    </form>
@endsection

@push('donnees_pour_vuejs_data')
    ids_elements : [],
    pdf_separe : [],
@endpush

@push('donnees_pour_vuejs_methods')

    imprimer_pdf_elements_selectionnes: function() {

        var composant = this;

        // on va chercher les ID des éléments sélectionnés
        var ids = composant.liste.lignes_selectionnees;

        if(ids.length == 0)
            return;

        this.ids_elements = ids;

        this.$nextTick(() => {
            this.$refs.formulaire_impressions_pdf.submit();

            this.modale_imprimer_pdf = false;
        });
    },

    imprimer_pdf_tous_elements: async function() {

        var composant = this;

        // on récupère les ids...
        loading(true);

        // on va chercher la liste d'ids
        await composant.actualisation_filtres(true);

        var ids = composant.liste.ids;

        loading(false);

        if(ids.length == 0){
            this.modale_imprimer_modele = false;
            return;
        }

        this.ids_elements = ids;

        this.$nextTick(() => {
            this.$refs.formulaire_impressions_pdf.submit();

            this.modale_imprimer_pdf = false;
        });
    },

@endpush
