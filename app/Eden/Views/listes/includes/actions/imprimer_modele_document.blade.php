@extends('eden::listes.includes.actions.action_en_masse_generique',['action' => 'imprimer_modele'])

@section('texte_modale_imprimer_modele')

    <form method="post" ref="formulaire_impressions_modele" action="{{ route('base_eden.element.imprimer_en_masse', [], false) }}" target="_blank">
        <input type="hidden" name="ids_elements" :value="JSON.stringify(ids_elements)"/>
        <input type="hidden" name="type_element" :value="this.liste.type_element" />

        <select v-model="modele_pdf_selectionne" name="modele_de_document">

            <option v-for="modele_de_document in modeles_de_document" :value="modele_de_document.id">
                @{{modele_de_document.nom}}
            </option>
        </select>

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
    modeles_de_document : [],
    pdf_separe : false,
    modele_pdf_selectionne:false,
    ids_elements:[],
@endpush

@push('donnees_pour_vuejs_mounted')

    $.post({
        url : '{{route('base_eden.element.rechercher_avec_requete',['modele_de_document'], false)}}',
        dataType : 'json',
        data : {
            donnees : {
                nom_sql : 'type_element_autres',
                valeur : this.liste.type_element
            }
        }
    }).done((donnees) => {
        this.modeles_de_document = donnees.retour;
    });
@endpush

@push('donnees_pour_vuejs_methods')

    imprimer_modele_elements_selectionnes: function() {

        var composant = this;

        // on va chercher les ID des éléments sélectionnés
        var ids = composant.liste.lignes_selectionnees;

        if(ids.length == 0)
            return;

        this.ids_elements = ids;

        this.$nextTick(() => {
            this.$refs.formulaire_impressions_modele.submit();

            this.modale_imprimer_modele = false;
        });
    },

    imprimer_modele_tous_elements: async function() {

        var composant = this;

        // on récupère les ids...
        loading(true);

        // on va chercher la liste d'ids
        await composant.actualisation_filtres(true);

        var ids = composant.liste.ids;

        loading(false);

        if(ids.length == 0){

            this.modale_imprimer_modele = false;

            return
        }

        this.ids_elements = ids;

        this.$nextTick(() => {
            this.$refs.formulaire_impressions_modele.submit();

            this.modale_imprimer_modele = false;
        });
    },

@endpush
