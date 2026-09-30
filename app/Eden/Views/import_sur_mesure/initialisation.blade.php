<div class="row">
    <div class="col-md-12">
        <div class="row">
            <div class="col-md-3">
                <p>@traduction('interface.import_sur_mesure.votre_fichier')</p>
            </div>
            <div class="col-md-9">
                <span style="background: #eee; padding: 7px; cursor: pointer;" class="css_champ_file_comp" onClick="$(this).closest('div').find('input[type=file]').click()">
                    <i class="fa fa-upload"></i>
                </span>
                <span >@{{import_en_cours.nom_fichier}}</span>
                <input @change="ajout_file" accept="text/csv, application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" type="file" id="fichier_import" style="display: none;"  />
            </div>
        </div>
        <div class="row" v-if="import_sur_mesure.id != undefined">
            <div class="col-md-3">
                @traduction('interface.import_sur_mesure.modele_import_choisi') :
            </div>
            <div class="col-md-9">
                <span>@{{ import_sur_mesure.titre }}</span>
                <span @click="import_sur_mesure=modele_import_sur_mesure;"><i class="fas fa-times"></i></span>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <p>@traduction('interface.import_sur_mesure.a_importer_dans_la_table')</p>
            </div>
            <div class="col-md-9">
                <select :disabled="import_sur_mesure.id != undefined" name="type_element" v-model="import_sur_mesure.type_element"  @change="recuperation_tables_jointes">
                    <option v-for="(nom_table,type_element) in tables_libres" :value="type_element">@{{ nom_table }} (@{{ type_element}})</option>
                </select>
            </div>
        </div>
        <div class="row" v-if="import_sur_mesure.tables_jointes.length > 0" v-for="(table_jointe,index_table_jointe) in import_sur_mesure.tables_jointes">
            <div class="col-md-3"></div>
            <div class="col-md-8">
                <input v-model="table_jointe.nom" type="text" style="width:auto" />
                (@{{ table_jointe.type_element }})
            </div>
            <div class="col-md-1">
                <span class="css_ajouter_element mr-3" v-if="import_sur_mesure.id == undefined" data-toggle="tooltip" data-placement="top" :title="traduction('interface.import_sur_mesure.tooltip.supprimer_table')" @click="supprimer_table(index_table_jointe)">
                    <i class="css_action_icon fas fa-trash"></i>
                </span>
            </div>
        </div>
        <div class="row" v-if="tables_jointes.length > 0">
            <div class="col-md-3">
                <p>@traduction('interface.import_sur_mesure.tables_jointes_disponibles')</p>
            </div>
            <div class="col-md-8">
                <select v-model="table_jointe_selectionnee" :disabled="import_sur_mesure.id != undefined">
                    <option v-for="table in tables_jointes" :value="table.type_element">@{{table.nom_table}} (@{{ table.type_element }})</option>
                </select>
            </div>
            <div class="col-md-1">
                <span v-if="table_jointe_selectionnee != null" class="css_ajouter_element mr-3" data-toggle="tooltip" data-placement="top" :title="traduction('interface.import_sur_mesure.tooltip.ajouter_table')" @click="ajouter_table">
                    <i class="css_action_icon fas fa-plus"></i>
                </span>
            </div>
        </div>
    </div>
</div>

@push('donnees_pour_vuejs_data')
	tables_jointes: [],
	table_jointe_selectionnee: null,
    tables_libres : {!! collect($tables_libres) !!},
    modele_import_sur_mesure : {!! modele_par_defaut('import_sur_mesure') !!},
@endpush

@push('donnees_pour_vuejs_methods')

	recuperation_tables_jointes(){

		var vue_instance = this;

		vue_instance.import_sur_mesure.tables_jointes = [];
		vue_instance.table_jointe_selectionnee = null;

		if(vue_instance.import_sur_mesure.type_element == null)
			return true;

        loading(true);

		$.ajax({
			url : '{{URL::to('eden/champs/tables_jointes')}}/'+vue_instance.import_sur_mesure.type_element,
			dataType: 'json',
		}).done(function(tables){

			vue_instance.tables_jointes = tables;

            loading(false);

		});
	},

	ajouter_table(){

        var vue_instance = this;
        var id_unique = vue_instance.table_jointe_selectionnee;
        var id_non_unique = true;
        var compteur = 0;

        while(id_non_unique){

            compteur ++;
            var correspondance = false;

            if(vue_instance.import_sur_mesure.type_element == id_non_unique){
                id_unique = vue_instance.table_jointe_selectionnee+'_'+compteur;
                correspondance = true;
            }

            $.each(vue_instance.import_sur_mesure.tables_jointes,function(index,table_jointe){
                if(table_jointe.id == id_unique){
                    id_unique = vue_instance.table_jointe_selectionnee+'_'+compteur;
                    correspondance = true;
                }
            });

            id_non_unique = correspondance;

        }

        var nom_table = vue_instance.recuperation_nom_table(this.table_jointe_selectionnee);
        var nom_table_unique = nom_table;
        var nom_non_unique = true;
        var compteur = 0;

        while(nom_non_unique){

            compteur ++;

            var correspondance = false;

            $.each(vue_instance.import_sur_mesure.tables_jointes,function(index,table_jointe){
                if(table_jointe.nom == nom_table_unique){
                    nom_table_unique = nom_table+' ('+compteur+')';
                    correspondance = true;
                }
            });

            nom_non_unique = correspondance;

        }

		this.import_sur_mesure.tables_jointes.push({
            id : id_unique,
            type_element : this.table_jointe_selectionnee,
            nom : nom_table_unique,
        });

		this.table_jointe_selectionnee = null;
	},

	supprimer_table(index_table_jointe){
		this.import_sur_mesure.tables_jointes.splice(index_table_jointe,1);
	},

    ajout_file(){

        var fichier = document.getElementById('fichier_import').files[0];

        this.import_en_cours.nom_fichier = fichier.name;
    },

    recuperation_nom_table(type_element){

        var nom_table = '';

        $.each(this.tables_jointes,function(index,table_jointe){
            if(table_jointe.type_element == type_element){
                nom_table = table_jointe.nom_table;
                return;
            }
        });

        return nom_table;
    },

    suite_initialisation(){

        var vue_instance = this;

        loading(true);

        var formulaire = new FormData();

        var fichier_import = document.getElementById('fichier_import').files[0];

        formulaire.append('fichier_import',fichier_import,this.import_en_cours.nom_fichier);

        formulaire.append('import_sur_mesure', JSON.stringify(this.import_sur_mesure));
        formulaire.append('import_en_cours', JSON.stringify(this.import_en_cours));

        $.post({

            url: '{{URL::route('import_sur_mesure.traitement_donnee')}}',
            data: formulaire,
            contentType: false,
            processData: false,
        }).done(function(donnees){

            loading(false);

            if(donnees.succes !== true){
                erreur_toast(donnees.message);
                return;
            }

            vue_instance.champs_libres = donnees.champs_libres;
            vue_instance.import_sur_mesure = donnees.import_sur_mesure;
            vue_instance.import_en_cours = donnees.import_en_cours;
            vue_instance.champs_libres_valeurs_par_defaut = donnees.champs_libres_valeurs_par_defaut;

            vue_instance.valeur_par_defaut_affichage = vue_instance.import_sur_mesure.type_element;

            vue_instance.affichages_champs_valeur_par_defaut();

            vue_instance.etape_import ++;

            vue_instance.$forceUpdate();

        });
    },

@endpush