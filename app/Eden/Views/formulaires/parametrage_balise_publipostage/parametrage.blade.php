<template v-if="type_element && chargement_inital">
    <parametrage-balise ref="parametrage_balise" :type_element="type_element" :modele="parametrage_balise_publipostage" nom_sql="valeur" :recherches_avancees="recherches_avancees"></parametrage-balise>
    <input type="hidden" v-model="parametrage_balise_publipostage.valeur" name="valeur">
    <template v-if="$refs.parametrage_balise">
        <input type="hidden" v-for="filtrage in $refs.parametrage_balise.filtrages" name="filtrages[]" :value="JSON.stringify(filtrage)">
    </template>
</template>

@push('donnees_pour_vuejs_data')
    tables_libres : {!! \App\Eden\Models\Table_libre::whereNull('table_systeme')->orWhere('table_systeme',0)->get() !!},
    recherches_avancees : [],
    chargement_inital : false,
@endpush

@push('donnees_pour_vuejs_computed')
    type_element: function(){
        return this.tables_libres.find(table => table.id == this.parametrage_balise_publipostage.type_element_id)?.type_element ?? null;
    },
@endpush

@push('donnees_pour_vuejs_mounted')

    if(this.parametrage_balise_publipostage.id > 0)
        this.recherches_avancees = await $.ajax({
            url : 'eden/recherche_avancee/parametrage_balise_publipostage_'+this.parametrage_balise_publipostage.id+'/recherche_type',
            dataType : 'json',
        }); 

    this.chargement_inital = true;
@endpush