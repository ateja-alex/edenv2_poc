<div class="row">
    <input type="hidden" name="objet_mfiles" v-model="parametrage_mappage_mfiles.objet_mfiles">
    <input type="hidden" name="type_attribut_liaison" v-model="parametrage_mappage_mfiles.type_attribut_liaison">
    <div class="col-sm-2">@traduction('champs_libres.parametrage_mappage_mfiles.table_libre.nom')</div>
    <div class="col-sm-4">
        <select v-model="parametrage_mappage_mfiles.table_libre" name="table_libre" @change="recherche_classe_mfiles(), recherche_attribut_liaison_mfiles()">
            <option v-for="table_libre in $root.valeurs_listes_formatees[71]" :value="table_libre.valeur" :selected="table_libre.valeur == parametrage_mappage_mfiles.table_libre">@{{ table_libre.valeur }}</option>
        </select>
    </div>
    <div class="col-sm-2">@traduction('champs_libres.parametrage_mappage_mfiles.classe_mfiles.nom')</div>
    <div class="col-sm-4">
        <select name="classe_mfiles" v-model="parametrage_mappage_mfiles.classe_mfiles">
            <optgroup label="Classes recommandées">
                <option v-for="classe in classes_mfiles_recommandees" :value="classe.ID">@{{ classe.ID }} - @{{ classe.Name }}</option>
            </optgroup>
            <optgroup label="Toutes les classes">
                <option v-for="classe in classes_mfiles" :value="classe.ID">@{{ classe.ID }} - @{{ classe.Name }}</option>
            </optgroup>
        </select>
    </div>
</div>
<div class="row">
    <div class="col-sm-2">@traduction('champs_libres.parametrage_mappage_mfiles.classe_document_mfiles.nom')</div>
    <div class="col-sm-4">
        <select name="classe_document_mfiles" v-model="parametrage_mappage_mfiles.classe_document_mfiles">
            <option v-for="classe in classes_documents_files" :value="classe.ID">@{{ classe.ID }} - @{{ classe.Name }}</option>
        </select>
    </div>
    <div class="col-sm-2">@traduction('champs_libres.parametrage_mappage_mfiles.attribut_liaison_document_mfiles.nom')</div>
    <div class="col-sm-4">
        <select name="attribut_liaison_document_mfiles" v-model="parametrage_mappage_mfiles.attribut_liaison_document_mfiles">
            <optgroup label="Attributs recommandés" >
                <option v-for="attribut in attributs_document_mfiles_recommandes" :value="attribut.ID">@{{ attribut.ID }} - @{{ attribut.Name }}</option>
            </optgroup>
            <optgroup label="Tous les attributs">
                <option v-for="attribut in attributs_document_mfiles" :value="attribut.ID">@{{ attribut.ID }} - @{{ attribut.Name }}</option>
            </optgroup>
        </select>
    </div>
</div>


@push('donnees_pour_vuejs_data')

    classes_mfiles: [],
    classes_documents_files: [],
    classes_mfiles_recommandees: [],
    attributs_document_mfiles: [],
    attributs_document_mfiles_recommandes: [],
@endpush

@push('donnees_pour_vuejs_methods')

    recherche_classe_mfiles: function() {

        var vue_instance = this;

        vue_instance.classes_mfiles_recommandees = [];

        table_libre_selectionnee = vue_instance.parametrage_mappage_mfiles.table_libre.replaceAll('_', ' ');

        if(vue_instance.parametrage_mappage_mfiles.table_libre != 'Sans valeur'){

            Object.entries(vue_instance.classes_mfiles).forEach(function([id_classe, classe]){

                if(classe.Name.toLowerCase().includes(table_libre_selectionnee))
                    vue_instance.classes_mfiles_recommandees.push(classe);
            });
        } else {

            vue_instance.classes_mfiles_recommandees = vue_instance.classes_mfiles;
        }

    },

    recherche_attribut_liaison_mfiles: function() {

        var vue_instance = this;

        vue_instance.attributs_document_mfiles_recommandes = [];

        table_libre_selectionnee = vue_instance.parametrage_mappage_mfiles.table_libre.replaceAll('_', ' ');

        Object.entries(vue_instance.attributs_document_mfiles).forEach(function([id_attribut, attribut]){

            if(attribut.Name.toLowerCase().includes(table_libre_selectionnee))
                vue_instance.attributs_document_mfiles_recommandes.push(attribut);
        });

    },
@endpush
@push('donnees_pour_vuejs_mounted')

        var vue_instance = this;

        //On récupère toutes les classes
        $.ajax({

            url: "{{ route('mfiles.recuperer_classes') }}",
            dataType: "json"
        }).done(function(donnees) {

            vue_instance.classes_mfiles = donnees;
            vue_instance.recherche_classe_mfiles();
        });

        //On récupère les classes qui peuvent être des documents
        $.ajax({

            url: "{{ route('mfiles.recuperer_classes', ['recuperer_documents' => true]) }}",
            dataType: "json"
        }).done(function(donnees) {

            vue_instance.classes_documents_files = donnees;
        });

        // On récupère les attributs qui peuvent permettre de lier le document à l'élément
        $.ajax({

            url: "{{ route('mfiles.recuperer_attributs') }}",
            dataType: "json",
            data: { id : 0 }
        }).done(function(donnees) {

            vue_instance.attributs_document_mfiles = donnees;
            vue_instance.recherche_attribut_liaison_mfiles();
        });
@endpush

@push('donnees_pour_vuejs_watch')

    "parametrage_mappage_mfiles.classe_mfiles": function(nouvelle_valeur) {

        var vue_instance = this;

        if(vue_instance.classes_mfiles[nouvelle_valeur] !== undefined)
            vue_instance.parametrage_mappage_mfiles.objet_mfiles = vue_instance.classes_mfiles[nouvelle_valeur].ObjectType;
    },

    "parametrage_mappage_mfiles.attribut_liaison_document_mfiles": function(nouvelle_valeur) {

        var vue_instance = this;

        Object.entries(vue_instance.attributs_document_mfiles).forEach(function([id_attribut, attribut]){

            if(id_attribut == nouvelle_valeur)
                vue_instance.parametrage_mappage_mfiles.type_attribut_liaison = attribut.DataType;
        });
    },

@endpush
