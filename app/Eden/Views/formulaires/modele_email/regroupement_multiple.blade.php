<div class="row">
    <div class="col-md-4">
        {!! management('modele_email')->champ('regroupement_multiple')->nom_vue() !!}
    </div>
    <div class="col-md-8"> 
        <select name="regroupement_multiple" v-model="modele_email.regroupement_multiple">
            <option :value="null">Aucun regroupement</option>
            <option value="1">Tout regrouper</option>
            <option v-for="champ in champs_regroupements" :value="champ.nom_sql" 
                v-html="'Par '+$root.traduction(champ.index_traduction+'.nom')+' ('+champ.nom_sql+')'"></option>
        </select>
    </div>
</div>

@push('donnees_pour_vuejs_data')
    champs_libres : [],
    tables_libres : {!! \App\Eden\Models\Table_libre::whereNull('table_systeme')->orWhere('table_systeme',0)->get() !!},
@endpush

@push('donnees_pour_vuejs_methods')

    chargement_champs_libres : async function(){
        
        if(this.type_element == null){
            this.champs_libres = [];
            return;
        }

        if(this.champs_libres.length > 0 && this.champs_libres[0].type_element == this.type_element)
            return;

        this.champs_libres = await $.ajax({
            url : "/eden/champs/valeurs/"+this.type_element,
            dataType: 'json'
        });
    },
@endpush

@push('donnees_pour_vuejs_watch')
    type_element : function(){
        this.chargement_champs_libres();
    },
@endpush

@push('donnees_pour_vuejs_mounted')
    this.chargement_champs_libres();
@endpush

@push('donnees_pour_vuejs_computed')
    type_element: function(){
        return this.tables_libres.find(table => table.id == this.modele_email.type_element_id)?.type_element ?? null;
    },

    champs_regroupements : function(){
                
        return this.champs_libres.filter((champ) => {
            return champ.type == 42;
        });
    },
@endpush