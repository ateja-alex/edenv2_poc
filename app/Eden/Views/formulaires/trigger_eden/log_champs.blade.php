<div class="row" v-if="trigger_eden.enregistrement_log == 1">
    <div class="col-sm-6">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>
                        <span style="display: flex;align-items: center;justify-content: space-between;">
                            @traduction('interface.trigger_eden.log_champs.champ')
                            <i class="fas fa-plus" @click="log_champs.push({champ : null})"></i>
                        </span>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(log_champ,index) in log_champs" v-if="champs_libres.length > 0">
                    <td>
                        <select-champs-libres :champs_libres="champs_libres"
                                          :type_element_origine="type_element_trigger"
                                          :type_element="type_element_trigger"
                                          :nom_sql="log_champ.champ"
                                          @changement_select_champs_libres="changement_select_champs_libres($event,index)">
                        </select-champs-libres>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<input type="hidden" name="log_champs" :value="JSON.stringify(log_champs.map(log_champ => log_champ.champ))">

@push('donnees_pour_vuejs_data')
    log_champs : [],
    champs_libres : [],
    tables_libres : {!! \App\Eden\Models\Table_libre::get() !!},
@endpush

@push('donnees_pour_vuejs_mounted')

    if(this.trigger_eden.id > 0){
        this.chargement_champs();
        this.chargement_log_champs();
    }
@endpush

@push('donnees_pour_vuejs_computed')
    type_element_trigger :function(){

        if(!(this.trigger_eden.type_element_concerne_id > 0))
            return null;

        return this.tables_libres.filter(table_libre => table_libre.id == this.trigger_eden.type_element_concerne_id)[0].type_element;
    },
@endpush

@push('donnees_pour_vuejs_watch')

    'trigger_eden.type_element_concerne_id' : function(){
        this.chargement_champs();
    },
@endpush

@push('donnees_pour_vuejs_methods')

    changement_select_champs_libres : function(valeur,index_champ){

        if(valeur.nom_sql == null)
            this.log_champs.splice(index_champ,1);
        else
            this.log_champs[index_champ].champ = valeur.nom_sql;
    },

    chargement_champs : function(){

        if(this.type_element_trigger == null)
            return;

        $.ajax({
            url : 'eden/champs/valeurs/'+this.type_element_trigger,
            dataType : 'json'
        }).done((champs_libres) => {
            this.champs_libres = [{
                type_element : this.type_element_trigger,
                index_traduction : 'tables_libres.'+this.type_element_trigger+'.nom_table',
                champs_libres : champs_libres
            }];
        });
    },

    chargement_log_champs : function(){

        $.post({
            url: "{{route('base_eden.element.recuperer_liste','trigger_eden_log_champ')}}",
            dataType: "json",
            data:{
                filtrage:[
                    {
                        champ : 'trigger_eden_id',
                        condition : 'where',
                        valeur : this.trigger_eden.id
                    },
                ]
            }
        }).done((elements) => {
            this.log_champs = elements;
        });
    },
@endpush