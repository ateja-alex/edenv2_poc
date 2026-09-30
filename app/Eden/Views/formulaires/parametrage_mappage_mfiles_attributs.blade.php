<div class="row">
    <input type="hidden" name="parametrage_mappage_mfiles_id" :value="$root.parametrage_mappage_mfiles.id">
    <input type="hidden" name="type" v-model="parametrage_mappage_mfiles_attributs.type">
    <div class="col-sm-2">@traduction('champs_libres.parametrage_mappage_mfiles_attributs.champ_libre_eden.nom')</div>
    <div class="col-sm-4">
        <select v-model="parametrage_mappage_mfiles_attributs.champ_libre_eden" name="champ_libre_eden" @change="recherche_attribut_mfiles()">
            <option value="">Sans valeur</option>
            <option v-for="champ_libre in champs_libres" :value="champ_libre.nom_sql">@{{ champ_libre.nom }}</option>
        </select>
    </div>
    <div class="col-sm-2">@traduction('champs_libres.parametrage_mappage_mfiles_attributs.attribut_mfiles.nom')</div>
    <div class="col-sm-4">
        <select name="attribut_mfiles" v-model="parametrage_mappage_mfiles_attributs.attribut_mfiles">
            <optgroup label="Attributs recommandés">
                <option v-for="attribut in attributs_mfiles_recommandes" :value="attribut.ID">@{{ attribut.ID }} - @{{ attribut.Name }}</option>
            </optgroup>
            <optgroup label="Tous les attributs">
                <option v-for="attribut in attributs_mfiles" :value="attribut.ID">@{{ attribut.ID }} - @{{ attribut.Name }}</option>
            </optgroup>
        </select>
    </div>
</div>

@section('donnees_pour_vuejs_data')

    attributs_mfiles: [],
    attributs_mfiles_recommandes: [],
    champs_libres: [],
@endsection

@section('donnees_pour_vuejs_methods')

    recherche_attribut_mfiles: function() {

        var vue_instance = this;

        vue_instance.attributs_mfiles_recommandes = [];

        Object.entries(vue_instance.attributs_mfiles).forEach(function([id_attribut, attribut]){

            if(attribut.Name.toLowerCase().includes(vue_instance.parametrage_mappage_mfiles_attributs.champ_libre_eden))
                vue_instance.attributs_mfiles_recommandes.push(attribut);
        });
    },
@endsection

@section('donnees_pour_vuejs_mounted')

    vue_instance = this;

    $.ajax({

        url: "{{ route('mfiles.recuperer_attributs') }}",
        dataType: "json",
        data: { id : vue_instance.$root.element_id }
    }).done(function(donnees) {

        vue_instance.attributs_mfiles = donnees;
    });

    $.ajax({

        url: "{{ url('eden/champs/valeurs') }}/" + vue_instance.$root.parametrage_mappage_mfiles.table_libre,
        dataType: "json",
    }).done(function(donnees) {

        vue_instance.champs_libres = donnees;
    });
@endsection

@push('donnees_pour_vuejs_watch')

    "parametrage_mappage_mfiles_attributs.attribut_mfiles": function(nouvelle_valeur) {

        vue_instance = this;

        Object.entries(vue_instance.attributs_mfiles).forEach(function([id_attribut, attribut]){

            if(nouvelle_valeur == id_attribut)
                vue_instance.parametrage_mappage_mfiles_attributs.type = attribut.DataType;
        });
    }
@endpush
