<div class="row">
    <div class="col-sm-2">@traduction('champs_libres.profil_droits_element.type_element.nom')</div>
    <div class="col-sm-4">
        <select-table-libre 
            @changement_select_table_libre="changement_select_table_libre($event)"
            :tables_libres="tables_libres" :type_element="profil_droits_element.type_element"></select-table-libre>
        <input type="hidden" name="type_element" v-model="profil_droits_element.type_element">
    </div>
    <template v-if="champs_libres.length > 0">
        <div class="col-sm-2">@traduction('champs_libres.profil_droits_element.nom_sql.nom')</div>
        <div class="col-sm-4">
            <select-champs-libres :champs_libres="champs_libres"
                  :type_element_origine="profil_droits_element.type_element"
                  :type_element="profil_droits_element.type_element"
                  :nom_sql="profil_droits_element.nom_sql"
                  @changement_select_champs_libres="profil_droits_element.nom_sql = $event.nom_sql;">
                  >
            </select-champs-libres>
            <input type="hidden" name="nom_sql" v-model="profil_droits_element.nom_sql">
        </div>
    </template>
</div>

@push('donnees_pour_vuejs_data')
    tables_libres : {!! collect(\App\Eden\Models\Table_libre::whereNull('table_systeme')->orWhere('table_systeme',0)->get()) !!},
    champs_libres : [],
@endpush

@push('donnees_pour_vuejs_methods')

    chargement_champs_libres : function(){

        var type_ajax = this.$root.profil.extranet == 1 ? 'contact' : 'utilisateur';

        this.champs_libres = [];

        if(this.profil_droits_element.type_element == null || this.profil_droits_element.type_element == '')
            return;

        $.ajax({
            url: 'eden/champs/valeurs/'+this.profil_droits_element.type_element,
            dataType:'json'
        }).done((champs_libres) => {

            champs_libres = champs_libres.filter((champ_libre) => {
                return [10,42].includes(champ_libre.type) && champ_libre.type_element_ajax == type_ajax;
            });

            this.champs_libres.push({
                champs_libres : champs_libres,
                type_element : this.profil_droits_element.type_element,
                index_traduction : 'tables_libres.'+this.profil_droits_element.type_element+'.nom_table'
            });
        });
    },

    changement_select_table_libre : function(table_libre){
        this.profil_droits_element.type_element = table_libre == null ? null : table_libre.type_element;
        this.profil_droits_element.nom_sql = null;
        this.chargement_champs_libres();
    },
@endpush

@push('donnees_pour_vuejs_mounted')

    this.chargement_champs_libres();
@endpush



