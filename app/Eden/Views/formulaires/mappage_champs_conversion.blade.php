<div class="row">
    <div class="col-sm-2">@traduction('champs_libres.mappage_champs_conversion.mappage_table.nom')</div>
    <div class="col-sm-4">
        {!! management('mappage_champs_conversion')->champ('mappage_table')->cree() !!}
    </div>
    <div class="col-sm-2">@traduction('champs_libres.mappage_champs_conversion.champ_depart.nom')</div>
    <div class="col-sm-4">
        <select name="champ_depart" v-model="mappage_champs_conversion.champ_depart">
            <option v-for="champ in champs_type_depart" :value="champ.nom_sql">@{{ champ.nom }} (@{{ champ.nom_sql }})</option>
        </select>
    </div>
</div>
<div class="row" v-if="">
    
    <div class="col-sm-2">@traduction('champs_libres.mappage_champs_conversion.champ_arrivee.nom')</div>
    <div class="col-sm-4">
        <select-champs-libres :champs_libres="champs_type_arrivee"
                              :type_element_origine="mappage_champs_conversion.type_element_arrivee"
                              :type_element="mappage_champs_conversion.type_element_arrivee"
                              :nom_sql="mappage_champs_conversion.champ_arrivee"
                              @changement_select_champs_libres="mappage_champs_conversion.champ_arrivee = $event.nom_sql; 
                                mappage_champs_conversion.type_element_arrivee = $event.type_element; 
                                mappage_champs_conversion.champ_liaison = $event.champ_liaison;">
        </select-champs-libres>
    </div>

    <input type="hidden" v-model="mappage_champs_conversion.champ_arrivee" name="champ_arrivee">
    <input type="hidden" v-model="mappage_champs_conversion.type_element_arrivee" name="type_element_arrivee">
    <input type="hidden" v-model="mappage_champs_conversion.champ_liaison" name="champ_liaison">
</div>
@push('donnees_pour_vuejs_data')

    champs_type_depart: [],
    champs_type_arrivee: [],
    type_element_depart: '',
    mappage_table: {},
@endpush


@push('donnees_pour_vuejs_methods')

    chargement_champs_libres : function(){

        this.champs_type_arrivee = [];

        if(this.mappage_champs_conversion.mappage_table == null || this.mappage_champs_conversion.mappage_table == '' ||
            this.mappage_champs_conversion.champ_depart == null || this.mappage_champs_conversion.champ_depart == '')
            return;

        $.post({
            url: 'eden/champs/conversion',
            dataType:'json',
            data:{

                type_element_arrivee : this.mappage_champs_conversion.type_element_arrivee,
                type_element_depart : this.type_element_depart,
                champ_depart : this.mappage_champs_conversion.champ_depart,
            }
        }).done((champs_libres) => {
            this.champs_type_arrivee = champs_libres;
        });
    },
@endpush
@push('donnees_pour_vuejs_mounted')

    this.chargement_champs_libres();

    this.$root.$on('selection-element',async (parametres) => {

        if(parametres.nom_champ != 'mappage_table')
            return true;

        this.$set(this, 'mappage_table',parametres.element);

        //On récupère les champs du type de départ
        await $.ajax({

            url: '/eden/champs/valeurs/'+this.mappage_table.type_element_depart,
            dataType: "json"

        }).done((champs_libres) => {

            champs_libres.unshift({nom_sql : 'id', nom : 'Id'});
            this.$set(this, 'champs_type_depart',champs_libres);
        });

        this.$set(this.mappage_champs_conversion, 'type_element_arrivee',this.mappage_table.type_element_arrivee);
        this.$set(this, 'type_element_depart',this.mappage_table.type_element_depart);
        this.$set(this.mappage_champs_conversion, 'champ_depart', '');
    });
@endpush

@push('donnees_pour_vuejs_watch')

    'mappage_champs_conversion.champ_depart' : function(nouvelle_valeur){

        if(nouvelle_valeur != null && nouvelle_valeur != '')
            this.chargement_champs_libres();
    },
@endpush
