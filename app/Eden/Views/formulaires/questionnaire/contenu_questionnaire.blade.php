<template v-if="type_element">
    <div class="row">
        <div class="col-sm-4">
            {!! management('questionnaire')->champ('sujet')->nom_vue() !!}
        </div>
        <div class="col-sm-8">
            <champ-publipostage :type_element="type_element"
                :modele="questionnaire" nom_sql="sujet" ></champ-publipostage>
            <input type="hidden" name="sujet" :value="questionnaire.sujet">
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            {!! management('questionnaire')->champ('corps_du_mail')->nom_vue() !!}
        </div>
        <div class="col-sm-8">
            <champ-publipostage :type_element="type_element"
                :modele="questionnaire" nom_sql="corps_du_mail" type_champ="textarea-wysiwyg-vue"
                :variables_par_type_contexte="variables_publipostage_questionnaire"
                ></champ-publipostage>
            <input type="hidden" name="corps_du_mail" :value="questionnaire.corps_du_mail">
        </div>
    </div>
</template>


@push('donnees_pour_vuejs_data')
    tables_libres : {!! \App\Eden\Models\Table_libre::get() !!},
    variables_publipostage_questionnaire : [{
        titre : this.$root.traduction('tables_libres.questionnaire.nom_table'),
        id: 'valeur_contexte',
        valeur : 'type_element',
        elements : [
            {
                nom_sql : '#lien_questionnaire',
                titre : this.$root.traduction('champs_libres.url_raccourcie.lien_origine.nom'),
                valeur : "\{\{#lien_questionnaire\}\}",
            }
        ],
    }],
@endpush

@push('donnees_pour_vuejs_computed')
    type_element : function(){
        return this.tables_libres.find(t => t.id == this.questionnaire.type_element_id)?.type_element ?? null;
    },
@endpush