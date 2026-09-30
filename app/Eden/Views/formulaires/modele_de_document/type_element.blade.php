<div class="row">
    <div class="col-sm-2">
        {!! management('modele_de_document')->champ('type_element')->nom_vue() !!}
    </div>
    <div class="col-sm-10">
        <div style="display: flex;flex-wrap: wrap;gap: 10px;">
            <div v-for="element in documents_gescom" name="modele_de_document_type_element" name_champ="type_element">
                <input id="modele_de_document_type_element_' + element.nom_table_sql" type="checkbox" :value="element.id" v-model="modele_de_document.type_element" name="type_element[]" :type-element-v-model="modele_de_document" style="display:none;"/>
                <label :for="'modele_de_document_type_element_' + element.nom_table_sql" class="badge" :class="modele_de_document.type_element.includes(element.id) ? 'badge-success' : 'badge-default'" @click="changement_checkbox(element.id)" v-html="element.nom_table+' ('+element.nom_table_sql+')'"></label>
            </div>
                <input v-if="modele_de_document.type_element.length == 0" type="hidden" name="type_element[]" />
            <template v-if="documents_gescom.length === 0">
                <label style="margin-right: 20px;">
                    <div class="badge" :class="\'badge-default\'" style="cursor: not-allowed !important">Désolé, il n\'y a pas d\'élements.</div>
                </label>
            </template>
        </div>
    </div>
</div>

@push('donnees_pour_vuejs_data')
    documents_gescom: {!! App\Eden\Models\Table_libre::whereIn('type_element', App\Eden\Variables::$documents_gescom)->get() !!},
@endpush

@push('donnees_pour_vuejs_methods')
    changement_checkbox : function(id_valeur){
        if(this.modele_de_document.type_element.includes(id_valeur)){
            this.modele_de_document.type_element = this.modele_de_document.type_element.filter(element => element != id_valeur);
        }
        else{
            this.modele_de_document.type_element.push(id_valeur);
        }
    },
@endpush