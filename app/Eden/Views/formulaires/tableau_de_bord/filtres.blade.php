<div class="row">
    <div class="col-md-2">
        Filtres
    </div>
    <div class="col-md-10">
        <table class="table table-bordered table-hover" style="width:100%">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th>Type</th>
                    <th><i class="fa fa-plus" @click="ajout_filtre()"></i></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(filtre, index) in filtres" :key="index">
                    <td v-html="filtre.id"></td>
                    <td>
                        <input type="text" v-if="filtre.nouveau" v-model="filtre.nom"/>
                        <traduction-element :index_traduction="filtre.index_traduction+'.nom'" v-else></traduction-element>
                    </td>
                    <td>
                        <div class="row">
                            <div class="col-sm-12">
                                <select v-model="filtre.type" :disabled="filtre.nouveau !== true">
                                    <option v-for="type_filtre in types_filtres" :value="type_filtre" v-html="$root.traduction(type_filtre)"></option>
                                </select>
                            </div>
                        </div>
                        <div class="row" v-if="filtre.type == 'filtre-recherche-element'">
                            <div class="col-sm-12">
                                <span v-if="filtre.nouveau !== true" v-html="$root.traduction('tables_libres.'+filtre.type_element_ajax+'.nom_table') + ' ('+filtre.type_element_ajax+')'" ></span>
                                <select-table-libre v-else
                                    :tables_libres="tables_libres" 
                                    :type_element="filtre.type_element_ajax"
                                    @changement_select_table_libre="$set(filtre,'type_element_ajax',($event == null ? null : $event.type_element))">
                                </select-table-libre>
                            </div>
                        </div>
                    </td>
                    <td>
                        <i class="fa fa-trash" @click="filtres.splice(index, 1)"></i>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<input type="hidden" name="filtres" :value="JSON.stringify(filtres)"/>

@push('donnees_pour_vuejs_data')
    types_filtres : [
        'filtre-texte',
        'filtre-date',
        'filtre-montant',
        'filtre-recherche-element'
    ],
    filtres : [],
    tables_libres : {!! collect(\App\Eden\Models\Table_libre::whereNull('table_systeme')->orWhere('table_systeme',0)->get()) !!},
    max_id : 0,
@endpush

@push('donnees_pour_vuejs_mounted')
    if(this.tableau_de_bord.id > 0){

        this.filtres = JSON.parse(this.tableau_de_bord.filtres) ?? [];

        this.max_id = this.filtres.reduce(function(max, filtre) {
            return filtre.id > max ? filtre.id : max;
        }, 0);

        $.post({
            url : 'eden/elements/traduction_valeur',
            dataType : 'json',
            data : {
                filtrage:[
                    {
                        champ : 'index',
                        condition : 'whereLike',
                        valeur : 'tableau_de_bord.'+this.tableau_de_bord.id+'.filtres.%'
                    },
                    {
                        champ : 'langue',
                        condition : 'where',
                        valeur : 'fr'
                    }
                ]
            }
        }).done((traductions) => {

            for(traduction of traductions){

                if(this.$root.traductions_valeurs[traduction.index] != traduction.traduction_specifique)
                    this.$set(this.$root.traductions_valeurs,traduction.index,traduction.traduction_specifique);
            }
        });
    }
@endpush

@push('donnees_pour_vuejs_methods')
    ajout_filtre : function(){

        var max_id = this.filtres.reduce(function(max, filtre) {
            return filtre.id > max ? filtre.id : max;
        }, 0);

        if(max_id < this.max_id)
            max_id = this.max_id;

        this.filtres.push({
            id : max_id + 1,
            nom : '',
            type : 'filtre-texte',
            nouveau : true
        });
    }
@endpush