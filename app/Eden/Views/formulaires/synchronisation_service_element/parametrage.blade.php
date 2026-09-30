<div class="row">
    <div class="col-sm-2">
        {!! management("synchronisation_service_element")->champ('type_externe')->nom_vue() !!}
    </div>
    <div class="col-sm-4 d-flex">
        <select v-model="synchronisation_service_element.type_externe" name="type_externe">
            <option value="" v-html="$root.traduction('interface.formulaires.synchronisation_service_champs.choisir')"></option>
            <option v-for="table_externe in tables_externes_type_synchro" :value="table_externe.id" v-html="table_externe.nom +' ('+table_externe.id+')'"></option>
        </select>
        <a v-if="synchronisation_service_element.type_externe != null && synchronisation_service_element.type_externe != ''" 
            class="css_action_icon" :href="table_externe_selectionnee ? table_externe_selectionnee.disponibilites.find(d => d.type_synchronisation == synchronisation_service_element.type_synchronisation)?.reference_url : ''" target="_blank">
            <i class="fas fa-external-link-alt"></i>
        </a>
    </div>
</div>
<div class="row">
    <div class="col-sm-2">
        {!! management("synchronisation_service_element")->champ('type_element')->nom_vue() !!}
    </div>
    <div class="col-sm-4">
        <select-table-libre
            :tables_libres="tables_libres" 
            :type_element="synchronisation_service_element.type_element" 
            @changement_select_table_libre="synchronisation_service_element.type_element = ($event == null ? null : $event.type_element)">
        </select-table-libre>
        <input type="hidden" name="type_element" v-model="synchronisation_service_element.type_element">
    </div>
</div>
<template v-if="synchronisation_service_element.type_synchronisation == 3">
    <div class="row">
        <div class="col-sm-2">
            {!! management("synchronisation_service_element")->champ('type_element_destination')->nom_vue() !!}
        </div>
        <div class="col-sm-4">
            <select-table-libre
                :tables_libres="tables_libres" 
                :type_element="synchronisation_service_element.type_element_destination" 
                @changement_select_table_libre="synchronisation_service_element.type_element_destination = ($event == null ? null : $event.type_element);charger_champs_libres_destination(true);">
            </select-table-libre>
            <input type="hidden" name="type_element_destination" v-model="synchronisation_service_element.type_element_destination">
        </div>
    </div>
    <div class="row" v-if="synchronisation_service_element.type_element_destination && champs_libres_destination.length > 0">
        <div class="col-sm-2">
            {!! management("synchronisation_service_element")->champ('champ_element_destination')->nom_vue() !!}
        </div>
        <div class="col-sm-4">
            <select-champs-libres
                :champs_libres="[{
                    type_element : synchronisation_service_element.type_element_destination,
                    index_traduction : 'tables_libres.'+synchronisation_service_element.type_element_destination+'.nom_table',
                    champs_libres : champs_libres_destination
                }]"
                :type_element_origine="synchronisation_service_element.type_element_destination"
                :type_element="synchronisation_service_element.type_element_destination"
                :nom_sql="synchronisation_service_element.champ_element_destination"
                @changement_select_champs_libres="synchronisation_service_element.champ_element_destination = $event ? $event.nom_sql : null">
            </select-champs-libres>
            <input type="hidden" name="champ_element_destination" v-model="synchronisation_service_element.champ_element_destination">
        </div>
    </div>
    <div class="row">
        <div class="col-sm-2">
            {!! management("synchronisation_service_element")->champ('delai_lecture')->nom_vue() !!}
        </div>
        <div class="col-sm-4">
            <input type="number" min="0" name="delai_lecture" v-model="synchronisation_service_element.delai_lecture">
        </div>
    </div>
    <div class="row" v-if="synchronisation_service_element.champ_element_destination">
        <div class="col-sm-2">
            {!! management("synchronisation_service_element")->champ('suppression_elements_absents')->nom_vue() !!}
        </div>
        <div class="col-sm-4">
            <champ-liste-toggle
                :modele="synchronisation_service_element"
                nom_sql="suppression_elements_absents"
                name="suppression_elements_absents">
            </champ-liste-toggle>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-2">
            {!! management("synchronisation_service_element")->champ('cron_actif')->nom_vue() !!}
        </div>
        <div class="col-sm-4">
            <champ-liste-toggle
                :modele="synchronisation_service_element"
                nom_sql="cron_actif"
                name="cron_actif">
            </champ-liste-toggle>
        </div>
    </div>
</template>
<div class="row" v-if="synchronisation_service_element.type_element && [0, 3].includes(synchronisation_service_element.type_synchronisation)">
    <div class="col-sm-2">
        @traduction('interface.formulaires.synchronisation_service_element.filtrage')
    </div>
    <div class="col-sm-10">
        <recherche-avancee ref="recherche_avancee" 
            :enregistrement_desactive="true"
            :bloc_unitaire="true"
            :parametres_recherche_avancee="{
                type_element : synchronisation_service_element.type_element,
                type : 'synchronisation_service_element', 
                id_cible : synchronisation_service_element.id
            }"></recherche-avancee>
    </div>
    <input type="hidden" v-if="filtrage !== false" name="filtrage" :value="JSON.stringify(filtrage)">
</div>
<input type="hidden" v-else name="filtrage" :value="null">
<div class="row" v-if="synchronisation_service_element.type_synchronisation == 1 && synchronisation_service_element.id > 0">
    <div class="col-sm-2">
        @traduction('interface.formulaires.synchronisation_service_element.url_webhook')
    </div>
    <div class="col-sm-4">
        <input type="text" disabled :value="url_webhook">
        @traduction('interface.formulaires.synchronisation_service_element.attention_webhook')
    </div>
</div>
<div class="row" v-else-if="(synchronisation_service_element.type_synchronisation == 2 || (synchronisation_service_element.type_synchronisation == 3 && synchronisation_service_element.cron_actif == 1)) && synchronisation_service_element.id > 0">
    <div class="col-sm-2">
        @traduction('interface.formulaires.synchronisation_service_element.url_cron')
    </div>
    <div class="col-sm-4">
        <input type="text" disabled :value="url_cron">
        @traduction('interface.formulaires.synchronisation_service_element.attention_cron')
    </div>
</div>

@push('donnees_pour_vuejs_data')
    tables_libres : {!! \App\Eden\Models\Table_libre::get() !!},
    tables_externes : [],
    synchronisation_service : null,
    filtrage: false,
    champs_libres_destination : [],
@endpush

@push('donnees_pour_vuejs_mounted')
    this.charger_champs_libres_destination();
@endpush

@push('donnees_pour_vuejs_computed')
    url_webhook(){
        if(this.synchronisation_service_element.type_synchronisation == 1 && this.synchronisation_service_element.id > 0)
            return window.location.origin + '/api/synchronisation_service/' + this.synchronisation_service_element.id;
        
        return null;
    },

    url_cron(){
        if(this.synchronisation_service_element.id > 0 && (this.synchronisation_service_element.type_synchronisation == 2 || (this.synchronisation_service_element.type_synchronisation == 3 && this.synchronisation_service_element.cron_actif == 1)))
            return window.location.origin + '/eden/cron/synchronisation_service/' + this.synchronisation_service_element.id;

        return null;
    },

    tables_externes_type_synchro : function(){
        return this.tables_externes.filter(table_externe => table_externe.disponibilites.find(d => d.type_synchronisation == this.synchronisation_service_element.type_synchronisation) != null);
    },

    table_externe_selectionnee : function(){

        if(this.tables_externes.length == 0 || this.synchronisation_service_element.type_externe == null)
            return null;

        return this.tables_externes.find(table_externe => table_externe.id == this.synchronisation_service_element.type_externe);
    },
@endpush

@push('donnees_pour_vuejs_mounted')
    this.$root.$on('selection-element',(parametres) => {
        if(parametres.nom_sql == "synchronisation_service_id"){
            this.synchronisation_service = parametres.element;
            this.chargement_tables_externes();
        }
    });

    if(this.synchronisation_service_element.synchronisation_service_id != null)
        this.synchronisation_service = await $.ajax({
            url : "/eden/element/synchronisation_service/" + this.synchronisation_service_element.synchronisation_service_id,
            dataType:'json'
        });
    else if(this.sous_formulaire)
        this.synchronisation_service = this.$parent.synchronisation_service;

    this.$on('changement_recherche_avancee', (parametres) => {
        if(parametres.actualisation)
            this.filtrage = parametres.recherche_avancee.structure;
    });

    this.chargement_tables_externes();
@endpush

@push('donnees_pour_vuejs_methods')
    chargement_tables_externes : async function(){

        if(this.synchronisation_service == null)
            return;

        this.tables_externes = await $.post({
            url: 'eden/synchronisation_service/tables_externes/'+this.synchronisation_service.id,
            dataType:'json'
        });
    },

    charger_champs_libres_destination : async function(modification = false){

        if (this.synchronisation_service_element.type_element_destination == null || this.synchronisation_service_element.type_element_destination == '') { 
            this.champs_libres_destination = []; 
            return; 
        }

        this.champs_libres_destination = (await $.ajax({
            url: '/eden/champs/valeurs/'+this.synchronisation_service_element.type_element_destination,
            dataType: "json"
        })).filter((champs_libres) => {
            return champs_libres.type == 42 && champs_libres.type_element_ajax == this.synchronisation_service_element.type_element;
        });

        if(modification && this.champs_libres_destination.length == 1 && this.synchronisation_service_element.champ_element_destination == null)
            this.synchronisation_service_element.champ_element_destination = this.champs_libres_destination[0].nom_sql;
    },
@endpush

