<template v-if="type_taches_a_gerer === 'rdv'">
    <template v-if="tache.participant">
        {!! formulaire('tache_formulaire_participants') !!}
    </template>
    <template v-else>
        {!! formulaire('formulaire_2_tache') !!}
    </template>
</template>
<template v-else>
    {!! formulaire('formulaire_1_tache') !!}  
</template>

<div class="formulaire_eden">
    <div class="col-sm-12" v-if="tache.type_element != '' && tache.type_element != null && tache.id">
        <div class="col-sm-2" style="text-transform: capitalize;">
            @{{ tache.type_element  }}
        </div>
        <div class="col-sm-10" style="display: flex">
            <champ-selection-element :readonly="true" :modele="tache" type_element_origine="tache" :type_element="tache.type_element" :nom_sql="'element_id'"></champ-selection-element>
        </div>
    </div>
</div>