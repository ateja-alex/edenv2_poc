<div class="row">
    <div class="col-sm-4">
        {{management('parametrage_chronometre')->champ('type_element')->nom()}}
    </div>
    <div class="col-sm-8">
        <div class="css_champ_obligatoire">
            <select-table-libre 
                @changement_select_table_libre="changement_select_table_libre($event)"
                :tables_libres="tables_libres" :type_element="parametrage_chronometre.type_element"></select-table-libre>
            <input type="hidden" name="type_element" v-model="parametrage_chronometre.type_element">
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-4">
        {{management('parametrage_chronometre')->champ('champ_libre_correspondance_temps')->nom()}}
    </div>
    <div class="col-sm-8">
        <select-champs-libres :champs_libres="champs_libres"
                              :type_element_origine="parametrage_chronometre.type_element"
                              :type_element="parametrage_chronometre.type_element"
                              :nom_sql="parametrage_chronometre.champ_libre_correspondance_temps"
                              @changement_select_champs_libres="parametrage_chronometre.champ_libre_correspondance_temps = $event.nom_sql;">
        </select-champs-libres>
        <input type="hidden" name="champ_libre_correspondance_temps" v-model="parametrage_chronometre.champ_libre_correspondance_temps">
    </div>
</div>
<div class="row">
    @champ('parametrage_chronometre','type_saisie',4,8)
</div>

@push('donnees_pour_vuejs_data')
    tables_libres : {!! collect(\App\Eden\Models\Table_libre::whereNull('table_systeme')->orWhere('table_systeme',0)->get()) !!},
    champs_libres : [],
@endpush

@push('donnees_pour_vuejs_methods')
    chargement_champs_libres : function(){

        this.champs_libres = [];

        if(this.parametrage_chronometre.type_element == null || this.parametrage_chronometre.type_element == '')
            return;

        $.ajax({
            url: 'eden/champs/valeurs/'+this.parametrage_chronometre.type_element,
            dataType:'json'
        }).done((champs_libres) => {

            champs_libres = champs_libres.filter((champ_libre) => {
                return [2,3].includes(champ_libre.type);
            });

            this.champs_libres.push({
                champs_libres : champs_libres,
                type_element : this.parametrage_chronometre.type_element,
                index_traduction : 'tables_libres.'+this.parametrage_chronometre.type_element+'.nom_table',
            });
        });
    },

    changement_select_table_libre : function(table_libre){
        this.parametrage_chronometre.type_element = table_libre == null ? null : table_libre.type_element;
        this.parametrage_chronometre.champ_libre_correspondance_temps = null;
        this.chargement_champs_libres();
    },
@endpush

@push('donnees_pour_vuejs_mounted')
    this.chargement_champs_libres();
@endpush