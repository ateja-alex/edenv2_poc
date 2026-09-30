<div class="row">
    <div class="col-sm-4">
        @traduction('champs_libres.transformation_document_temps_modele.type_document.nom')
    </div>
    <div class="col-sm-8">
        <select name="type_document" v-model="transformation_document_temps_modele.type_document">
            <option v-for='document in documents_gescom_vente' :value='document.id'>@{{ document.nom_table }}</option>
        </select>
    </div>
</div>

@push('donnees_pour_vuejs_data')
    documents_gescom_vente: {!! App\Eden\Models\Table_libre::whereIn('type_element', App\Eden\Variables::$documents_vente_gescom)->get() !!},
@endpush