<div class="row">
    <div class="col-sm-2">
        @traduction('champs_libres.transformation_document_temps_mappage.champ_destination.nom')
    </div>
    <div class="col-sm-4">
        <select-champs-libres
                :champs_libres="champs_document"
                :type_element="type_document"
                :nom_sql="transformation_document_temps_mappage.champ_destination"
                @changement_select_champs_libres="transformation_document_temps_mappage.champ_destination = $event.nom_sql;"
        ></select-champs-libres>
        <input type="hidden" name="champ_destination" v-model="transformation_document_temps_mappage.champ_destination"/>
    </div>
    @champ('transformation_document_temps_mappage','champ_dur',2,4)
</div>
<div class="row">
    <div class="col-sm-2">
        @traduction('champs_libres.transformation_document_temps_mappage.champ_source.nom')
    </div>
    <div class="col-sm-10" v-if="transformation_document_temps_mappage.champ_dur == 1">
        <input-parametrage
                :type_utilisateur="$root.moi.type_utilisateur"
               at_custom="#" name="champ_source"
               :vmodel="transformation_document_temps_mappage"
               :donnees="champs_input_parametrage">
        </input-parametrage>
    </div>
    <div class="col-sm-4" v-else>
        <select-champs-libres
                :champs_libres="champs_element"
                :type_element="$root.transformation_document_temps_modele.type_element_cible"
                :nom_sql="transformation_document_temps_mappage.champ_source"
                @changement_select_champs_libres="transformation_document_temps_mappage.champ_source = $event.nom_sql;"
        ></select-champs-libres>
    </div>
    <input type="hidden" name="champ_source" v-model="transformation_document_temps_mappage.champ_source"/>
</div>

@push('donnees_pour_vuejs_data')

    champs_document : [],
    champs_element : [],
    type_element_origine : '',

@endpush

@push('donnees_pour_vuejs_computed')

    type_document : function(){

        return Object.values(this.$root.valeurs_listes_formatees[118])
            .filter(valeur => valeur.id_valeur == this.$root.transformation_document_temps_modele.type_element_cible)
            .valeur ?? 'facture_vente';
    },

    champs_input_parametrage : function(){

        if(!this.champs_element[0] || !this.champs_element[0].champs_libres)
            return [];

        champs_input_parametrage =  this.champs_element[0].champs_libres.map((champ_libre) => {

            var nom = this.$root.traduction('tables_libres.'+champ_libre.type_element+'.nom_table') +' : '+
            (champ_libre.nom_sql == 'id' ? 'Id' :this.$root.traduction(champ_libre.index_traduction+'.nom'));
            return {
                id : '#'+champ_libre.nom_sql+'#',
                name : nom
            }
        });

        champs_input_parametrage.push({
            id : '#/date_debut#',
            name : this.$root.traduction('interface.transformation_document_temps_mappage.date_debut_periode')
        });

        champs_input_parametrage.push({
            id : '#/date_fin#',
            name : this.$root.traduction('interface.transformation_document_temps_mappage.date_fin_periode')
        });

        return champs_input_parametrage;
    },

@endpush

@push('donnees_pour_vuejs_mounted')

    $.ajax({

        url: "{{ URL::to('/eden/champs/valeurs') }}/" + this.$root.transformation_document_temps_modele.type_element_cible,
        dataType: "json"
    }).done((donnees) => {

        donnees.unshift({
            nom_sql: 'id',
            nom: 'Id',
            type_element : this.$root.transformation_document_temps_modele.type_element_cible,
        })

        this.champs_element = [{
            champs_libres : donnees,
            type_element : this.$root.transformation_document_temps_modele.type_element_cible,
            index_traduction : 'tables_libres.'+this.$root.transformation_document_temps_modele.type_element_cible+'.nom_table'
        }];
    });

    $.ajax({

        url: "{{ URL::to('/eden/champs/valeurs') }}/" + this.type_document,
        dataType: "json"
    }).done((donnees) => {

        this.champs_document = [{
            champs_libres : donnees,
            type_element : this.type_document,
            index_traduction : 'tables_libres.'+this.type_document+'.nom_table'
        }];

    });

@endpush