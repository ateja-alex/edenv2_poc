<template v-if="type_element != null">
    <div class="row">
        <div class="col-md-4">
            {!! management('modele_email')->champ('sujet_modele')->nom_vue() !!}
        </div>
        <div class="col-md-8">
            <champ-publipostage :type_element="type_element"
                :modele="modele_email" nom_sql="sujet_modele" name="sujet_modele"></champ-publipostage>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            {!! management('modele_email')->champ('modele')->nom_vue() !!}
        </div>
        <div class="col-md-8">
            <champ-publipostage :type_element="type_element"
                :modele="modele_email" nom_sql="modele" name="modele" type_champ="textarea-wysiwyg-vue"></champ-publipostage>
        </div>
    </div>
</template>

@push('donnees_pour_vuejs_data')
    tables_libres : {!! \App\Eden\Models\Table_libre::whereNull('table_systeme')->orWhere('table_systeme',0)->get() !!},
@endpush

@push('donnees_pour_vuejs_computed')
    type_element: function(){
        return this.tables_libres.find(table => table.id == this.modele_email.type_element_id)?.type_element ?? null;
    },
@endpush