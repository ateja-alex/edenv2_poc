<input type="hidden" name="type_element" v-model="integration_email.type_element">
<input type="hidden" name="table_gestion_reponse" v-model="integration_email.table_gestion_reponse">
<input type="hidden" name="champ_gestion_reponse" v-model="integration_email.champ_gestion_reponse">
<div class="row">
    <div class="col-sm-2">
        {!! management('integration_email')->champ('type_element')->nom_vue() !!}
    </div>
    <div class="col-sm-4">
        <select-table-libre 
            @changement_select_table_libre="changement_select_table_libre($event, 'type_element')"
            :tables_libres="tables_libres" :type_element="integration_email.type_element"></select-table-libre>
    </div>
</div>
<div class="row" v-if="integration_email.type_element">
    <div class="col-sm-2">
        {!! management('integration_email')->champ('table_gestion_reponse')->nom_vue() !!}
    </div>
    <div class="col-sm-4">
        <select-table-libre 
            @changement_select_table_libre="changement_select_table_libre($event, 'table_gestion_reponse')"
            :tables_libres="tables_libres_reponse" :type_element="integration_email.table_gestion_reponse"></select-table-libre>
    </div>
</div>
<div class="row" v-if="integration_email.table_gestion_reponse != null && integration_email.type_element">
    <template v-if="champs_libres_reponse.length > 1">
        <div class="col-sm-2">
            {!! management('integration_email')->champ('table_gestion_reponse')->nom_vue() !!}
        </div>
        <div class="col-sm-4">
            <select-champs-libres :champs_libres="[{
                    type_element : integration_email.table_gestion_reponse,
                    index_traduction : 'tables_libres.'+integration_email.table_gestion_reponse+'.nom_table',
                    champs_libres : champs_libres_reponse
                    }]"
                  :type_element_origine="integration_email.table_gestion_reponse"
                  :type_element="integration_email.table_gestion_reponse"
                  :nom_sql="integration_email.champ_gestion_reponse"
                  @changement_select_champs_libres="integration_email.champ_gestion_reponse = $event.nom_sql;">
            </select-champs-libres>
        </div>
    </template>
    <div class="col-sm-2">
        {!! management('integration_email')->champ('balise_gestion_reponse')->nom_vue() !!}
    </div>
    <div class="col-sm-4">
        <input-parametrage
                :type_utilisateur="$root.moi.type_utilisateur"
               at_custom="#" name="balise_gestion_reponse"
               :vmodel="integration_email"
               :donnees="champs_libres">
        </input-parametrage>
    </div>
</div>

@push('donnees_pour_vuejs_data')
    tables_libres : {!! collect(\App\Eden\Models\Table_libre::whereNull('table_systeme')->orWhere('table_systeme',0)->get()) !!},
    champs_libres : [],
    tables_libres_reponse : [],
    champs_libres_reponse : [],
@endpush

@push('donnees_pour_vuejs_mounted')

    this.chargement_champs_libres();
    this.chargement_table_reponse();
    this.chargement_champs_libres_reponse();
@endpush

@push('donnees_pour_vuejs_methods')
    changement_select_table_libre : function(table, nom){
        this.integration_email[nom] = table == null ? null : table.type_element;

        if(nom == 'type_element'){
            this.integration_email.table_gestion_reponse = null;
            this.integration_email.balise_gestion_reponse = null;
            this.chargement_table_reponse();
            this.chargement_champs_libres();
        }
        else{
            this.integration_email.champ_gestion_reponse = null;
            this.chargement_champs_libres_reponse();
        }
    },
    chargement_table_reponse : function(){

        if(this.integration_email.type_element == null)
            return;

        $.ajax({
            url : 'eden/champs/tables_jointes/'+this.integration_email.type_element,
            dataType : 'json'
        }).done((types_elements) => {
            this.tables_libres_reponse = types_elements;
        });
    },

    chargement_champs_libres : function(){

        if(this.integration_email.type_element == null)
            return;

        $.ajax({
            url : 'eden/champs/valeurs/'+this.integration_email.type_element,
            dataType : 'json'
        }).done((champs) => {
            this.champs_libres = champs.map(c => {
                return {
                    id : '#'+c.nom_sql+'#',
                    name : this.$root.traduction(c.index_traduction+'.nom') + ' ('+c.nom_sql+')',
                }
            });
        });
    },

    chargement_champs_libres_reponse : function(){

        if(this.integration_email.table_gestion_reponse == null)
            return;

        $.ajax({
            url : 'eden/champs/valeurs/'+this.integration_email.table_gestion_reponse,
            dataType : 'json'
        }).done((champs) => {
            this.champs_libres_reponse = champs.filter(champ => champ.type == 42 && champ.type_element_ajax == this.integration_email.type_element);

            if(this.champs_libres_reponse.length == 1)
                this.integration_email.champ_gestion_reponse = this.champs_libres_reponse[0].nom_sql;
        });
    },
@endpush