<div class="row recapitulatif">
    <div class="col-md-12">
        <div class="row" >
            <div class="col-md-3 text-right">
                <h6><small>@traduction('interface.import_sur_mesure.intitule')</small></h6>
            </div>
            <div class="col-md-2">
                <h6><small>@traduction('interface.import_sur_mesure.correspondance')</small></h6>
            </div>
            <div class="col-md-2 text-center">
                <h6><small>@traduction('interface.import_sur_mesure.cle_maj')</small></h6>
            </div>
            <div class="col-md-2 text-center">
                <h6><small>@traduction('interface.import_sur_mesure.maj_donnees')</small></h6>
            </div>
            <div class="col-md-2 text-center">
                <h6><small>@traduction('interface.import_sur_mesure.utilisation_valeur_defaut_si_vide')</small></h6>
            </div>
        </div>

        <template v-for="(champs,element) in champs_trie_par_type_element">
            <div v-if="informations.cle_cree !== true" class="row" :key="element+'_'+index" v-for="(informations,index) in champs">
                <div class="col-md-3 text-right">
                    <p v-if="informations.champ_import_parent == undefined">@{{ informations.champ_import }} :</p>
                </div>
                <div class="col-md-2">
                    <p>@{{ nom_table(element)  }} > @{{ informations.champ_libre.nom }} (@{{ informations.champ_libre.nom_sql }})</p>
                </div>
                <div class="col-md-2 text-center">
                    <input type="checkbox" v-model="informations.cle_mise_a_jour" disabled/>
                </div>
                <div class="col-md-2 text-center">
                    <input type="checkbox" v-model="informations.mettre_a_jour" disabled />
                </div>
                <div class="col-md-2 text-center">
                    <input type="checkbox" v-model="informations.utilisation_valeur_par_defaut" disabled />
                </div>
            </div>
        </template>

        <div class="row categorie">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12">
                        @traduction('interface.import_sur_mesure.utilisation_cles_crees')
                    </div>
                </div>
                <div class="row" :key="element+'_'+index" v-for="(informations,index) in champs_cles">
                    <div class="col-md-3 text-right">
                        <p v-if="informations.champ_import_parent == undefined">@traduction('interface.import_sur_mesure.cle') @{{ tables_libres[informations.table_cle] }} :</p>
                    </div>
                    <div class="col-md-2">
                        <p>@{{ nom_table(informations.champ_libre.type_element)  }} > @{{ informations.champ_libre.nom }} (@{{ informations.champ_libre.nom_sql }})</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row categorie" v-if="etat_post_import == false" @click="verifications_cles(false)">
            <div id="bouton_chargement_etat_import">
                <i class="fas fa-sync"></i>
                @traduction('interface.import_sur_mesure.charger_previsualisation')
            </div>
        </div>
        <template  v-else>
            <div class="row categorie" >
                <div class="col-md-12">
                    @traduction('interface.import_sur_mesure.previsualisation')
                </div>
            </div>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>@traduction('interface.import_sur_mesure.nom_table')</th>
                        <th>@traduction('interface.import_sur_mesure.nombres_ajoutes')</th>
                        <th>@traduction('interface.import_sur_mesure.nombres_mises_a_jour')</th>
                    </tr>
                </thead>
                <tbody>
                    <template v-for="(etats,type_element) in etat_post_import.par_type_element">
                        <tr>
                            <td>
                                @{{ traduction('tables_libres.'+type_element+'.nom_table') }} (@{{type_element}})
                            </td>
                            <td v-for="nombre in etats">
                                @{{ nombre }}
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
            <div class="row text-right" >
                <div class="col-md-12">
                    @traduction('interface.import_sur_mesure.nombre_lignes_total') : @{{ etat_post_import.nombre_lignes }} @traduction('interface.import_sur_mesure.lignes')
                </div>
            </div>
        </template>
    </div>
</div>

@push('donnees_pour_vuejs_data')

    etat_post_import : false,
@endpush

@push('donnees_pour_vuejs_methods')

    lancement_import : function(){

        var vue_instance = this;
        vue_instance.etape_import++;

        vue_instance.import_statut_en_cours = true;

        $.post({
            url: '{{route('import_sur_mesure.traitement_bdd')}}',
            dataType : 'json',
            data : {
                import_en_cours : vue_instance.import_en_cours,
            },
        });
    },

    nom_table:function (element){

        if(element == this.import_sur_mesure.type_element){
            return this.tables_libres[element];
        }

        var nom_table = '';

        $.each(this.import_sur_mesure.tables_jointes,function(index,table_jointe){

            if(table_jointe.id == element)
                nom_table = table_jointe.nom;
        });

        return nom_table;

    },

@endpush

@push('donnees_pour_vuejs_computed')
    champs_trie_par_type_element : function(){

        var champs_trie_par_type_element = {};

        var vue_instance = this;

        $.each(this.import_sur_mesure.champs,function(index,informations){

            if(informations.correspondance != null
                && informations.correspondance != 'aucune_correspondance'
                && informations.cle_cree !== true){

                var champ_libre = vue_instance.recuperation_champ_libre(informations.correspondance);

                var correspondance = informations.correspondance.split('.');

                var element = correspondance[0];
                var nom_sql = correspondance[1];

                if(champ_libre != null){
                    informations.champ_libre = champ_libre;
                }
                else{

                    champ_libre = {
                        nom_sql: nom_sql,
                        nom: 'ID'
                    };

                    informations.champ_libre = champ_libre;
                }

                if(champs_trie_par_type_element[element] == undefined)
                    champs_trie_par_type_element[element] = [];

                champs_trie_par_type_element[element].push(informations);
            }
        });

        return champs_trie_par_type_element;

    },

    champs_cles : function(){

        var champs_cles = [];

        var vue_instance = this;

        $.each(this.import_sur_mesure.champs,function(index,informations){

            if(informations.correspondance != null
                && informations.correspondance != 'aucune_correspondance'
                && informations.cle_cree === true){

                var champ_libre = vue_instance.recuperation_champ_libre(informations.correspondance);

                var correspondance = informations.correspondance.split('.');

                var element = correspondance[0];
                var nom_sql = correspondance[1];

                if(champ_libre != null)
                    informations.champ_libre = champ_libre;

                champs_cles.push(informations);
            }
        });

        return champs_cles;
    },
@endpush