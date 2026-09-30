@php 
    $management = management($type_element); 
    $champ_valeur_dur = $management->champ_valeur_dur;
    $filtres_valeur_final = $management->filtres_valeur_final;
@endphp
<div class="row">
    <div class="col-sm-2">
        @traduction('interface.parametrage_lien_champ.type_de_valeur')
    </div>
    <div class="col-sm-4">
        <select v-model="type_valeur" @change="changement_type_valeur">
            <option value="champ_dur" v-html="$root.traduction('champs_libres.'+type_element+'.'+champ_valeur_dur+'.nom')"></option>
            <option value="champ" v-html="$root.traduction('champs_libres.'+type_element+'.lien_champ.nom')"></option>
            <option v-if="!(this[type_element].type_element_id > 0)" value="parametrage_existant" v-html="$root.traduction('champs_libres.'+type_element+'.parametrage_existant.nom')"></option>
        </select>
    </div>
    <template v-if="type_valeur == 'champ_dur'">
        @yield('champ_valeur_dur')
    </template>
</div>
<template v-if="type_valeur == 'champ'">
    <div class="row">
        <div class="col-sm-2">
            <span v-html="$root.traduction('champs_libres.'+type_element+'.lien_champ.nom')"></span>
        </div>
        <div class="col-sm-10">
            <parametrage-lien-champ ref="parametrage_lien_champ"
                :lien_champ="this[type_element].lien_champ"
                @changement_lien_champ="changement_lien_champ($event)"
                @changement_filtrages="filtrages = $event"
                :type_element="type_element_lien"
                :filtres_valeur_final="filtres_valeur_final"
                :recherches_avancees="recherches_avancees"></parametrage-lien-champ>
        </div>
    </div>
</template>
<template v-else-if="type_valeur == 'parametrage_existant'">
    <div class="row">
        <div class="col-sm-2">
            <span v-html="$root.traduction('champs_libres.'+type_element+'.parametrage_existant.nom')"></span>
        </div>
        <div class="col-sm-10" style="display: flex;flex-wrap: wrap;gap: 10px;flex-direction: row;">
            <select v-model="parametrage_existant.type">
                <option value="type_element" v-if="this[this.type_element].type_element_id !== undefined" v-html="'Type élément '+$root.traduction('tables_libres.'+type_element_lien+'.nom_table')+' ('+type_element_lien+')'"></option>
                <option value="modele_email" v-if="this[this.type_element].modele_email_id !== undefined">Modéle email</option>
                <option value="notification_manuelle" v-if="this[this.type_element].notification_manuelle_id !== undefined">Notification manuelle</option>
            </select>
            <champ-selection-element v-if="['modele_email', 'notification_manuelle'].includes(parametrage_existant.type)" 
                :type_element="parametrage_existant.type" 
                nom_sql="valeur" :modele="parametrage_existant"
                :filtrage="filtrage_parametrage_existant">
            </champ-selection-element>
        </div>
    </div>
</template>

<input type="hidden" name="adresse_mail" :value="type_valeur == 'champ_dur' ? this[type_element][champ_valeur_dur] : null">
<input type="hidden" name="lien_champ" :value="type_valeur == 'champ' ? this[type_element].lien_champ : null">
<input type="hidden" name="parametrage_existant" :value="type_valeur == 'parametrage_existant' ? this[type_element].parametrage_existant : null">

<input type="hidden" v-for="filtrage in filtrages" name="filtrages[]" :value="JSON.stringify(filtrage)">

@push('donnees_pour_vuejs_data')
    tables_libres : {!! \App\Eden\Models\Table_libre::get() !!},
    type_valeur : 'champ_dur',

    parametrage_existant : {
        type : 'type_element',
        valeur : null,
    },

    recherches_avancees : [],
    filtrages: [],

    notification_manuelle : {},
    modele_email : {},

    type_element : '{{$type_element}}',
    champ_valeur_dur : '{{$champ_valeur_dur}}',
    filtres_valeur_final : {!! collect($filtres_valeur_final) !!},
@endpush

@push('donnees_pour_vuejs_methods')

    changement_type_valeur : function(){

        if(this.type_valeur != 'parametrage_existant')
            this[this.type_element].parametrage_existant = null;
        
        if(this.type_valeur != 'champ'){
            this[this.type_element].lien_champ = null;
            this.filtrages = [];
        }
        
        if(this.type_valeur != 'champ_dur')
            this[this.type_element][this.champ_valeur_dur] = null;

        if(this.type_valeur == 'parametrage_existant')
            this.parametrage_existant = {
                type : 'type_element',
                valeur : null,
            };
    },

    changement_lien_champ : function(lien_champ){
        this[this.type_element].lien_champ = lien_champ;
    },
    
@endpush

@push('donnees_pour_vuejs_mounted')

    this.$root.$on('selection-element',(parametres) => {
        if(parametres.nom_champ == 'notification_manuelle_id')
            this.notification_manuelle = parametres.element;
        else if(parametres.nom_champ == 'modele_email_id')
            this.modele_email = parametres.element;
    });

    this.$root.$on('suppression-selection-element', (parametres) => {
        if(parametres.nom_champ == 'notification_manuelle_id')
            this.notification_manuelle = {};
        else if(parametres.nom_champ == 'modele_email_id')
            this.modele_email = {};
    });

    if(this[this.type_element].lien_champ != null 
        && this[this.type_element].lien_champ != ''){
        this.recherches_avancees = await $.ajax({
            url : 'eden/recherche_avancee/'+this.type_element+'_'+this[this.type_element].id+'/recherche_type',
            dataType : 'json',
        }); 
        this.type_valeur = 'champ';
    }
    else if(this[this.type_element].parametrage_existant != null 
        && this[this.type_element].parametrage_existant != ''){
        this.type_valeur = 'parametrage_existant';
        this.parametrage_existant = {
            type : this[this.type_element].parametrage_existant.split('.')[0],
            valeur : this[this.type_element].parametrage_existant.split('.')[1] ?? null,
        };
    }
@endpush

@push('donnees_pour_vuejs_computed')

    type_element_id(){
        return this.notification_manuelle.type_element_id ?? this.modele_email.type_element_id ?? this[this.type_element].type_element_id ?? 0;
    },

    type_element_lien(){
        return this.tables_libres.find(t => t.id == this.type_element_id)?.type_element ?? null;
    },

    filtrage_parametrage_existant : function(){

        var filtrage = [{champ:'type_element_id',condition:'where',valeur:this.type_element_id}];

        if(this[this.type_element][this.parametrage_existant.type+'_id'] != null)
            filtrage.push({champ:'id',condition:'where',symbole:'!=',valeur:this[this.type_element][this.parametrage_existant.type+'_id']});
    
        if(this.parametrage_existant.type == 'notification_manuelle')
            filtrage.push({champ:'type_notification',condition:'where',valeur:this.type_element.includes('erp') ? 1 : 2});
        
        return filtrage;
    },
@endpush

@push('donnees_pour_vuejs_watch')

    parametrage_existant : function(){

        if((this.parametrage_existant.type != 'type_element' && this.parametrage_existant.valeur == null))
            this[this.type_element].parametrage_existant = null;
        else
            this[this.type_element].parametrage_existant = this.parametrage_existant.type+(this.parametrage_existant.valeur != null ? '.'+this.parametrage_existant.valeur : '');
    },

@endpush