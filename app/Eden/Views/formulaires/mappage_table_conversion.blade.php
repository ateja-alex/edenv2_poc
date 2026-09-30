<div class="row">

    <input type="hidden" v-model="mappage_table_conversion.type_element_depart" name="type_element_depart">
    <div class="col-sm-2">@traduction('champs_libres.mappage_table_conversion.type_element_depart.nom')</div>
    <div class="col-sm-4">
        <select-table-libre 
            @changement_select_table_libre="mappage_table_conversion.type_element_depart = ($event == null ? null : $event.type_element)"
            :tables_libres="tables_libres" :type_element="mappage_table_conversion.type_element_depart" nom="type_element_depart"></select-table-libre>
    </div>

    <input type="hidden" v-model="mappage_table_conversion.type_element_arrivee" name="type_element_arrivee">
    <div class="col-sm-2">@traduction('champs_libres.mappage_table_conversion.type_element_arrivee.nom')</div>
    <div class="col-sm-4">
        <select-table-libre 
            @changement_select_table_libre="mappage_table_conversion.type_element_arrivee = ($event == null ? null : $event.type_element)"
            :tables_libres="tables_libres" :type_element="mappage_table_conversion.type_element_arrivee"></select-table-libre>
    </div>
</div>
<div class="row">

    <div class="col-sm-2">@traduction('champs_libres.mappage_table_conversion.creation_auto.nom')</div>
    <div class="col-sm-4">
        {!! management('mappage_table_conversion')->champ('creation_auto')->cree() !!}
    </div>
    <div class="col-sm-2">@traduction('champs_libres.mappage_table_conversion.conversion_unique.nom')</div>
    <div class="col-sm-4">
        {!! management('mappage_table_conversion')->champ('conversion_unique')->cree() !!}
    </div>
</div>
<div class="row">
    <div class="col-sm-2">@traduction('champs_libres.mappage_table_conversion.nom_formulaire.nom')</div>
    <div class="col-sm-4">
        {!! management('mappage_table_conversion')->champ('nom_formulaire')->cree() !!}
    </div>
</div>

@push('donnees_pour_vuejs_data')
    tables_libres : {!! collect(\App\Eden\Models\Table_libre::whereNull('table_systeme')->orWhere('table_systeme',0)->get()) !!},
@endpush