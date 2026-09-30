<modification-en-masse
    ref="modification_en_masse"
    :ids_elements="liste.lignes_selectionnees"
    :type_element="type_element"
    @modification_terminee="apres_modification_en_masse()">
</modification-en-masse>

@push('donnees_pour_vuejs_methods')

    apres_modification_en_masse: function(retour) {
        this.deselectionner_toutes_les_lignes();
        this.actualisation_filtres();
	},

@endpush