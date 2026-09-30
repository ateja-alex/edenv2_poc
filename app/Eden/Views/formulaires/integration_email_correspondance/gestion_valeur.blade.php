<div class="row">
    <div class="col-sm-2">
        @traduction('interface.integration_email.gestion_valeur.gestion_pieces_jointes')
    </div>
    <div class="col-sm-4">
        <label class="switch">
            <input type="checkbox" v-model="gestion_pieces_jointes">
            <span class="slider round"></span>
        </label>
    </div>
</div>
<div class="row" v-if="gestion_pieces_jointes && champs_libres.length > 0 && champs_libres[0].champs_libres != null">
    <input type="hidden" name="valeur" value="#pieces_jointes#">
    <div class="col-sm-2">
        {!! management('integration_email_correspondance')->champ('champ_eden')->nom_vue() !!}
    </div>
    <div class="col-sm-4">
        <select v-model="integration_email_correspondance.champ_eden" name="champ_eden">
            <optgroup :label="$root.traduction('interface.champs_libres')">
                <option v-for="champ_libre in champs_libres[0].champs_libres.filter(champ_libre => [7,15].includes(champ_libre.type))"
                        :value="champ_libre.nom_sql"
                        v-html="$root.traduction(champ_libre.index_traduction+'.nom')"></option>
            </optgroup>
            <option value="bloc_piece_jointe" v-html="$root.traduction('interface.integration_email.gestion_valeur.bloc_piece_jointe')"></option>
            <option value="bloc_piece_jointe_parent" v-if="integration_email_correspondance.mail_reponse"
            v-html="$root.traduction('interface.integration_email.gestion_valeur.bloc_piece_jointe_parent')"></option>
        </select>
    </div>
</div>
<template v-else>
    <div class="row">
        <div class="col-sm-2">
            {!! management('integration_email_correspondance')->champ('champ_eden')->nom_vue() !!}
        </div>
        <div class="col-sm-4">
            <select-champs-libres :champs_libres="champs_libres"
                  :type_element_origine="type_element"
                  :type_element="type_element"
                  :nom_sql="integration_email_correspondance.champ_eden"
                  @changement_select_champs_libres="changement_select_champs_libres($event,'champ_eden')">
            </select-champs-libres>
            <input type="hidden" name="champ_eden" v-model="integration_email_correspondance.champ_eden">
        </div>
    </div>
    <div class="row" v-if="champ_libre != null && champ_libre.type == 42">
        <div class="col-sm-2">
            {!! management('integration_email_correspondance')->champ('champ_correspondance_type_42')->nom_vue() !!}
        </div>
        <div class="col-sm-4">
            <select-champs-libres :champs_libres="champs_libres_42"
                  :type_element_origine="champ_libre.type_element_ajax"
                  :type_element="champ_libre.type_element_ajax"
                  :nom_sql="integration_email_correspondance.champ_correspondance_type_42"
                  @changement_select_champs_libres="changement_select_champs_libres($event,'champ_correspondance_type_42')">
            </select-champs-libres>
            <input type="hidden" name="champ_correspondance_type_42" v-model="integration_email_correspondance.champ_correspondance_type_42">
        </div>
    </div>
    <div class="row">
        <div class="col-sm-2">
            {!! management('integration_email_correspondance')->champ('valeur')->nom_vue() !!}
        </div>
        <div class="col-sm-4">
            <input-parametrage
                    :type_utilisateur="$root.moi.type_utilisateur"
                   at_custom="#" name="valeur"
                   :vmodel="integration_email_correspondance"
                   :donnees="valeurs_mails_parametrage">
            </input-parametrage>
        </div>
    </div>
</template>

@push('donnees_pour_vuejs_data')
    champs_libres : [],
    champs_libres_42 : [],
    valeurs_mails : ['adresse_email','nom_de_domaine','contenu','contenu_texte','sujet','date','message_id','from','cc','bcc','to','compte_email_id'],
    gestion_pieces_jointes : false,
@endpush

@push('donnees_pour_vuejs_mounted')

    if(this.integration_email_correspondance.valeur == '#pieces_jointes#')
        this.gestion_pieces_jointes = true;

    this.chargement_champs_libres(true);
@endpush

@push('donnees_pour_vuejs_watch')

    'integration_email_correspondance.mail_reponse' : function(){
        this.chargement_champs_libres();
    },
@endpush

@push('donnees_pour_vuejs_methods')

    changement_select_champs_libres : function(valeur, nom_champ){
        this.integration_email_correspondance[nom_champ] = valeur.nom_sql;

        if(nom_champ == 'champ_eden' && this.champ_libre && this.champ_libre.type == 42)
            this.chargement_champs_libres_42();
    },

    chargement_champs_libres : function(initialisation = false){
        $.ajax({
            url : 'eden/champs/valeurs/'+this.type_element,
            dataType : 'json'
        }).done((champs) => {
            this.champs_libres = [{
                type_element : this.type_element,
                index_traduction : 'tables_libres.'+this.type_element+'.nom_table',
                champs_libres : champs
            }];

            if(initialisation && this.champ_libre && this.champ_libre.type == 42)
                this.chargement_champs_libres_42();
        });
    },

    chargement_champs_libres_42 : function(){
        $.ajax({
            url : 'eden/champs/valeurs/'+this.champ_libre.type_element_ajax,
            dataType : 'json'
        }).done((champs) => {
            this.champs_libres_42 = [{
                type_element : this.champ_libre.type_element_ajax,
                index_traduction : 'tables_libres.'+this.champ_libre.type_element_ajax+'.nom_table',
                champs_libres : champs
            }];
        });
    },

@endpush

@push('donnees_pour_vuejs_computed')

    type_element : function(){

        if(!this.$root.integration_email)
            return null;

        return this.integration_email_correspondance.mail_reponse == 1 ? this.integration_email.table_gestion_reponse : this.integration_email.type_element;
    },

    integration_email : function(){
        return this.$root.integration_email;
    },

    champ_libre : function(){

        if(this.champs_libres.length == 0)
            return null;

        return this.champs_libres[0].champs_libres.filter(champ_libre => champ_libre.nom_sql == this.integration_email_correspondance.champ_eden)[0] ?? null;
    },

    valeurs_mails_parametrage : function(){
        return this.valeurs_mails.map((valeur) => {
            return {
                id : '#'+valeur+'#',
                name: this.$root.traduction('interface.integration_email.gestion_valeur.valeurs_mails.'+valeur)
            }
        });
    },
@endpush