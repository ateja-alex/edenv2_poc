<div class="row">
    <input type="hidden" name="type_element" v-model="parametrage_mappage_sharepoint.type_element">
    <div class="col-sm-2">@traduction('champs_libres.parametrage_mappage_sharepoint.type_element.nom')</div>
    <div class="col-sm-4">
        <select-table-libre 
            @changement_select_table_libre="changement_select_table_libre($event)"
            :tables_libres="tables_libres" :type_element="parametrage_mappage_sharepoint.type_element" nom="type_element_sharepoint"></select-table-libre>
    </div>
    <div class="col-sm-2">@traduction('champs_libres.parametrage_mappage_sharepoint.nom_dossier.nom')</div>
    <div class="col-sm-4">
        <input type="text" name="nom_dossier" v-model="parametrage_mappage_sharepoint.nom_dossier">
    </div>
</div>

@push('donnees_pour_vuejs_data')
    tables_libres : {!! collect(\App\Eden\Models\Table_libre::whereNull('table_systeme')->orWhere('table_systeme',0)->get()) !!},
@endpush
@push('donnees_pour_vuejs_methods')

    generer_nom_dossier_sharepoint: async function() {

        var table_libre = await this.$root.modele_table_libre(this.parametrage_mappage_sharepoint.type_element)
        this.parametrage_mappage_sharepoint.nom_dossier = table_libre.nom_table;
    },

    changement_select_table_libre : function(table_libre){
        this.parametrage_mappage_sharepoint.type_element = table_libre == null ? null : table_libre.type_element;

        if(this.parametrage_mappage_sharepoint.type_element)
            this.generer_nom_dossier_sharepoint();
    },
@endpush