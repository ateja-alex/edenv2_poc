<div>
	<ul class="nav nav-tabs liste_onglets" style="position: relative; top: -7px; margin-top: 16px; border-bottom: 0px solid #dedede;">
        <li>
            <a data-toggle="tab" href="#correspondance" class="css_background_couleur_primaire_active active" style="margin-left: 1px;">
                @traduction('interface.import_sur_mesure.correspondances')
            </a>
        </li>
        <li>
            <a data-toggle="tab" href="#valeur_par_defaut" @click="affichages_champs_valeur_par_defaut();" class="css_background_couleur_primaire_active" style="margin-left: 1px;">
                @traduction('interface.import_sur_mesure.valeurs_par_defaut')
            </a>
        </li>
	</ul>
	<div class="tab-content">
        <div class="card-body css_form css_parametrage_formulaire tab-pane in active" id="correspondance" style="border: 0.1px solid lightgrey">
            <div class="row">
                <div class="col-md-12">
                    <div class="row" style="font-size: 1.4em;margin-bottom: 3%;">
                        <div class="col-md-2" style="text-align: right;">
                            <h6><small>@traduction('interface.import_sur_mesure.intitule')</small></h6>
                        </div>
                        <div class="col-md-3">
                            <h6><small>@traduction('interface.import_sur_mesure.correspondance')</small></h6>
                        </div>
                        <div class="col-md-1" style="text-align: center;">
                            <h6><small>@traduction('interface.import_sur_mesure.cle_maj')</small></h6>
                        </div>
                        <div class="col-md-1" style="text-align: center;">
                            <h6><small>@traduction('interface.import_sur_mesure.maj_donnees')</small></h6>
                        </div>
                        <div class="col-md-1" style="text-align: center;">
                            <h6><small>@traduction('interface.import_sur_mesure.correspondance_valeurs_necessaires')</small></h6>
                        </div>
                        <div class="col-md-1" style="text-align: center;">
                            <h6><small>@traduction('interface.import_sur_mesure.utilisation_valeur_defaut_si_vide')</small></h6>
                        </div>
                        <div class="col-md-3" style="text-align: center;">
                            <h6><small>@traduction('interface.import_sur_mesure.valeur_par_defaut')</small></h6>
                        </div>
                    </div>

                    <div class="row" v-if="informations.cle_cree !== true" style="margin-bottom: 1%;" :key="index" v-for="(informations,index) in import_sur_mesure.champs">
                        <div class="col-md-2" style="text-align: right;">
                            <p  v-if="informations.champ_import_parent == undefined">@{{ informations.champ_import }} :</p>
                        </div>
                        <div class="col-md-3 d-flex">
                            <select-champs-libres
                                :champs_libres="champs_libres_affichage(informations.correspondance)"
                                :type_element="informations.correspondance == null ? null : informations.correspondance.split('.')[0]"
                                :nom_sql="informations.correspondance == null ? null : informations.correspondance.split('.')[1]"
                                @changement_select_champs_libres="changement_select_champs_libres($event,informations,index)"
                            >
                            </select-champs-libres>
                            <span v-if="informations.champ_import_parent == undefined" class="css_ajouter_element mr-3" data-toggle="tooltip" data-placement="top" :title="traduction('interface.import_sur_mesure.tooltip.doubler_colonne')" @click="doubler_colonne_import(index,informations)">
                                <i class="css_action_icon fas fa-plus"></i>
                            </span>
                            <span v-else class="css_ajouter_element mr-3" data-toggle="tooltip" data-placement="top" :title="traduction('interface.import_sur_mesure.tooltip.supprimer_doublon')" @click="supprimer_colonne_import(index)">
                                <i class="css_action_icon fas fa-trash"></i>
                            </span>
                        </div>
                        <div class="col-md-1" style="text-align: center;">
                            <input type="checkbox" v-model="informations.cle_mise_a_jour" :disabled="(
                            informations.correspondance == 'aucune_correspondance' ||
                            informations.correspondance == null || cle_sur_element(informations.correspondance.split('.')[0])
                            || [10,11,12].includes(recuperation_champ_libre(informations.correspondance).type)
                            || (cle_sur_element_principal == false && informations.correspondance.split('.')[0] != import_sur_mesure.type_element)
                            ? true : false
                            )"  @change="gestion_cle_mise_a_jour(informations)"/>
                        </div>
                        <div class="col-md-1" style="text-align: center;">
                            <input type="checkbox" v-model="informations.mettre_a_jour" :disabled="(informations.correspondance != 'aucune_correspondance' && informations.cle_mise_a_jour == false ? false : true)" />
                        </div>
                        <div class="col-md-1" style="text-align: center;">
                            <input :disabled="(
                            informations.correspondance != null
                            && informations.correspondance != 'aucune_correspondance'
                            && recuperation_champ_libre(informations.correspondance) != null
                            && [1,20,10,11,12,42].includes(recuperation_champ_libre(informations.correspondance).type)
                            ? false : true
                            )" type="checkbox" v-model="informations.correspondances_valeurs" />
                        </div>
                        <div class="col-md-1" style="text-align: center;">
                            <input :disabled="(
                            informations.correspondance != null
                            && informations.correspondance != 'aucune_correspondance'
                            && recuperation_champ_libre(informations.correspondance) != null
                            && verification_disponilite_valeur_par_defaut(informations)
                            && recuperation_champ_libre(informations.correspondance).obligatoire != 1
                            ? false : true
                            )" type="checkbox" v-model="informations.utilisation_valeur_par_defaut" />
                        </div>
                        <div class="col-md-3" style="text-align: center;">
                            <component v-if="informations.correspondance != null && informations.correspondance != 'aucune_correspondance' && recuperation_champ_libre(informations.correspondance) != null && informations.utilisation_valeur_par_defaut === true"
                                       :is="champs_libres_affichages_composants[informations.correspondance.split('.')[0]+'.'+recuperation_champ_libre(informations.correspondance).nom_sql]"
                                       :element="import_sur_mesure.valeurs_par_defaut[informations.correspondance.split('.')[0]]"
                                       :type_element="recuperation_champ_libre(informations.correspondance).type_element"
                            />
                        </div>
                        <template v-if="informations.correspondance != null && informations.correspondance.split('.')[1] == 'id'">
                            <div class="col-md-2">
                            </div>
                            <div class="col-md-10" style="font-style: italic;">
                                <span>* @traduction('interface.import_sur_mesure.champ_id_sert_maj')</span>
                            </div>
                        </template>
                        <template v-if="informations.correspondance != null && informations.correspondance.split('.')[1] == 'cle_externe'">
                            <div class="col-md-2">
                            </div>
                            <div class="col-md-10" style="font-style: italic;">
                                <span>* @traduction('interface.import_sur_mesure.utilite_cle_externe')</span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div class="row" style="font-size: 1.4em;margin-top: 15px;border-top: 1px solid lightgrey;padding: 13px;">
                        <div class="col-md-12">
                            @traduction('interface.import_sur_mesure.utilisation_cles_crees')
                        </div>
                    </div>
                    <div class="row" v-if="informations.cle_cree === true" style="margin-bottom: 1%;" :key="index" v-for="(informations,index) in import_sur_mesure.champs">
                        <div class="col-md-2" style="text-align: right;">
                            <p  v-if="informations.champ_import_parent == undefined">@traduction('interface.import_sur_mesure.cle') @{{ tables_libres[informations.table_cle] }} :</p>
                        </div>
                        <div class="col-md-3">
                            <select :name="informations.champ_import" v-model="informations.correspondance" style="width: 75%;" @change="gestion_champs(index,informations)">
                                <option v-if="informations.champ_import_parent == undefined" value="aucune_correspondance"
                                    v-html="traduction('interface.import_sur_mesure.aucune_correspondance')">
                                </option>
                                <optgroup v-if="table_jointe.id != informations.table_id" v-for="table_jointe in import_sur_mesure.tables_jointes" :label="table_jointe.nom+' ('+table_jointe.type_element+')'">
                                    <option v-if="[42,22].includes(champ_libre.type) && (informations.correspondance == table_jointe.id+'.'+champ_libre.nom_sql || !correspondances_selectionnees.includes(table_jointe.id+'.'+champ_libre.nom_sql))" v-for="champ_libre in champs_libres.tables_jointes[table_jointe.type_element]" :value="table_jointe.id+'.'+champ_libre.nom_sql">
                                        @{{ table_jointe.nom }} > @{{ champ_libre.nom }} (@{{ champ_libre.nom_sql }})
                                    </option>
                                </optgroup>
                            </select>
                            <span v-if="informations.champ_import_parent == undefined" class="css_ajouter_element mr-3" data-toggle="tooltip" data-placement="top" :title="traduction('interface.import_sur_mesure.tooltip.doubler_colonne')" @click="doubler_colonne_import(index,informations)">
                                <i class="css_action_icon fas fa-plus"></i>
                            </span>
                            <span v-else class="css_ajouter_element mr-3" data-toggle="tooltip" data-placement="top" :title="traduction('interface.import_sur_mesure.tooltip.supprimer_doublon')" @click="supprimer_colonne_import(index)">
                                <i class="css_action_icon fas fa-trash"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body css_form css_parametrage_formulaire tab-pane" id="valeur_par_defaut" style="border: 0.1px solid lightgrey">
            <div class="row">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-8" style="display: inline-flex;">
                                    <h6 style="margin-right: 10px;">
                                        @traduction('interface.import_sur_mesure.type_element') :
                                    </h6>
                                    <span v-if="Object.keys(champs_libres_valeurs_par_defaut.tables_jointes).length == 0" style="position: relative;top: -2px;">
                                        @{{ tables_libres[import_sur_mesure.type_element]+' ('+import_sur_mesure.type_element+')' }}
                                    </span>
                                    <span v-else>
                                        <select v-model="valeur_par_defaut_affichage" @chnage="affichages_champs_valeur_par_defaut">
                                            <option :value="import_sur_mesure.type_element"> @{{ tables_libres[import_sur_mesure.type_element]+' ('+import_sur_mesure.type_element+')' }}</option>
                                            <option v-for="table_jointe in import_sur_mesure.tables_jointes" :value="table_jointe.id"> @{{ table_jointe.nom+' ('+table_jointe.id+')' }}</option>
                                        </select>
                                    </span>
                                </div>
                            </div>
                            <template v-if="valeur_par_defaut_affichage == import_sur_mesure.type_element">
                                <div class="row" v-for="champ_libre in champs_libres_valeurs_par_defaut.table_import">
                                    <div class="col-md-2">
                                        @{{ champ_libre.nom }} (@{{ champ_libre.nom_sql }})
                                    </div>
                                    <div class="col-md-9">
                                        <component :is="champs_libres_affichages_composants[import_sur_mesure.type_element+'.'+champ_libre.nom_sql]"
                                                   :element="import_sur_mesure.valeurs_par_defaut[import_sur_mesure.type_element]"
                                                   :type_element="champ_libre.type_element"
                                        />
                                    </div>
                                    <div class="col-md-1" v-if="champ_libre.obligatoire != 1">
                                        <span class="css_ajouter_element mr-3" data-toggle="tooltip" data-placement="top" :title="traduction('interface.import_sur_mesure.tooltip.supprimer_champ')" @click="supprimer_champ_valeur_par_defaut(champ_libre)">
                                            <i class="css_action_icon fas fa-trash"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        @traduction('interface.import_sur_mesure.ajouter_valeur_defaut')
                                    </div>
                                    <div class="col-md-7">
                                       <select v-model="ajout_champ_libre_valeur_par_defaut" >
                                           <option v-for="champ_libre in champs_libres_non_valeurs_par_defaut.table_import" :value="champ_libre.nom_sql" >@{{ champ_libre.nom }} (@{{ champ_libre.nom_sql }})</option>
                                       </select>
                                    </div>
                                    <div class="col-md-1">
                                        <span class="css_ajouter_element mr-3" data-toggle="tooltip" data-placement="top" :title="traduction('interface.import_sur_mesure.tooltip.ajouter_champ')" @click="ajouter_champ_valeur_par_defaut">
                                            <i class="css_action_icon fas fa-plus"></i>
                                        </span>
                                    </div>
                                </div>
                            </template>
                            <div v-if="valeur_par_defaut_affichage == table_jointe.id" v-for="table_jointe in import_sur_mesure.tables_jointes">
                                <div class="row" v-for="champ_libre in champs_libres_valeurs_par_defaut.tables_jointes[table_jointe.id]">
                                    <div class="col-md-2">
                                        @{{ champ_libre.nom }} (@{{ champ_libre.nom_sql }})
                                    </div>
                                    <div class="col-md-9">
                                        <component :is="champs_libres_affichages_composants[table_jointe.id+'.'+champ_libre.nom_sql]"
                                                   :element="import_sur_mesure.valeurs_par_defaut[table_jointe.id]"
                                                   :type_element="champ_libre.type_element"
                                        />
                                    </div>
                                    <div class="col-md-1" v-if="champ_libre.obligatoire != 1">
                                        <span class="css_ajouter_element mr-3" data-toggle="tooltip" data-placement="top" :title="traduction('interface.import_sur_mesure.tooltip.supprimer_champ')" @click="supprimer_champ_valeur_par_defaut(champ_libre)">
                                            <i class="css_action_icon fas fa-trash"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        @traduction('interface.import_sur_mesure.ajouter_valeur_defaut')
                                    </div>
                                    <div class="col-md-7">
                                       <select v-model="ajout_champ_libre_valeur_par_defaut" >
                                           <option v-for="champ_libre in champs_libres_non_valeurs_par_defaut.tables_jointes[table_jointe.id]" :value="champ_libre.nom_sql" >@{{ champ_libre.nom }} (@{{ champ_libre.nom_sql }})</option>
                                       </select>
                                    </div>
                                    <div class="col-md-1">
                                        <span class="css_ajouter_element mr-3" data-toggle="tooltip" data-placement="top" :title="traduction('interface.import_sur_mesure.tooltip.ajouter_champ')" @click="ajouter_champ_valeur_par_defaut">
                                            <i class="css_action_icon fas fa-plus"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('donnees_pour_vuejs_data')
    champs_libres: {!! collect($champs_libres) !!},
    champs_libres_valeurs_par_defaut: {!! collect($champs_libres_valeurs_par_defaut) !!},
    valeur_par_defaut_affichage: '',
    ajout_champ_libre_valeur_par_defaut: '',
    champs_libres_affichages_composants: {},

@endpush

@push('donnees_pour_vuejs_methods')

    changement_select_champs_libres : function(valeur,informations,index){

        if(valeur.nom_sql == null)
            this.$set(informations,'correspondance','aucune_correspondance');
        else{
            this.$set(informations,'correspondance',valeur.type_element+'.'+valeur.nom_sql);

            this.gestion_champs(index,informations);
        }

        this.$forceUpdate();
    },

    affichages_champs_valeur_par_defaut(){

        var vue_instance = this;

        $.each(vue_instance.champs_libres_valeurs_par_defaut.table_import,function(index,champ_libre){

            var identifiant = vue_instance.import_sur_mesure.type_element+'.'+champ_libre.nom_sql;

            if(vue_instance.champs_libres_affichages_composants[identifiant] == undefined){

                vue_instance.champs_libres_affichages_composants[identifiant] = {
                    template:champ_libre.champ_creation,
                    methods:vue_instance.$options.methods,
                    props:{
                        element:{},
                        type_element:{}
                    },
                    created:function(){
                        this[this.type_element] = this.element;
                    }
                };

            }

        });

        vue_instance.import_sur_mesure.tables_jointes.forEach(function(table_jointe){

            $.each(vue_instance.champs_libres_valeurs_par_defaut.tables_jointes[table_jointe.id],function(index,champ_libre){

                var identifiant = table_jointe.id+'.'+champ_libre.nom_sql;

                if(vue_instance.champs_libres_affichages_composants[identifiant] == undefined){

                    vue_instance.champs_libres_affichages_composants[identifiant] = {
                        template:champ_libre.champ_creation,
                        methods:vue_instance.$options.methods,
                        props:{
                            element:{},
                            type_element:{}
                        },
                        created:function(){
                            this[this.type_element] = this.element;
                        }
                    };

                }

            });

        });

        vue_instance.import_sur_mesure.champs.forEach(function(informations){

            champ_libre = vue_instance.recuperation_champ_libre(informations.correspondance);

            if(champ_libre != null && informations.correspondance != null && informations.correspondance != 'aucune_correspondance' && informations.utilisation_valeur_par_defaut === true){

                var identifiant = informations.correspondance.split('.')[0]+'.'+champ_libre.nom_sql;

                if(vue_instance.champs_libres_affichages_composants[identifiant] == undefined){

                    vue_instance.champs_libres_affichages_composants[identifiant] = {
                        template:champ_libre.champ_creation,
                        methods:vue_instance.$options.methods,
                        props:{
                            element:{},
                            type_element:{}
                        },
                        created:function(){
                            this[this.type_element] = this.element;
                        }
                    };

                }
            }

        });

        this.$forceUpdate();

    },

    ajouter_champ_valeur_par_defaut(){

        var element = this.valeur_par_defaut_affichage;
        var nom_sql = this.ajout_champ_libre_valeur_par_defaut;
        var champs_libres = {};
        var champs_libres_valeurs_par_defaut = {};

        if(this.import_sur_mesure.type_element == element){
            champs_libres = this.champs_libres.table_import;
            champs_libres_valeurs_par_defaut = this.champs_libres_valeurs_par_defaut.table_import;
        }
        else{

            var type_element = null;

            $.each(this.import_sur_mesure.tables_jointes,function(index,table_jointe){
                if(table_jointe.id == element)
                    type_element = table_jointe.type_element;

            });

            champs_libres = this.champs_libres.tables_jointes[type_element];
            champs_libres_valeurs_par_defaut = this.champs_libres_valeurs_par_defaut.tables_jointes[element];
        }

        $.each(champs_libres,function(index, champ_libre){
            if(champ_libre.nom_sql == nom_sql){
                champs_libres_valeurs_par_defaut.push(champ_libre);
                return;
            }
        });

        this.affichages_champs_valeur_par_defaut();

        this.ajout_champ_libre_valeur_par_defaut = '';
    },

    supprimer_champ_valeur_par_defaut(champ_libre_valeur_par_defaut){

        var element = champ_libre_valeur_par_defaut.type_element;
        var nom_sql = champ_libre_valeur_par_defaut.nom_sql;

        if(Array.isArray(this.import_sur_mesure.valeurs_par_defaut[element][nom_sql]))
            this.import_sur_mesure.valeurs_par_defaut[element][nom_sql] = [];
        else
            this.import_sur_mesure.valeurs_par_defaut[element][nom_sql] = null;

        if(this.import_sur_mesure.type_element == element){
            champs_libres = this.champs_libres.table_import;
            champs_libres_valeurs_par_defaut = this.champs_libres_valeurs_par_defaut.table_import;
        }
        else{
            champs_libres_valeurs_par_defaut = this.champs_libres_valeurs_par_defaut.tables_jointes[element];
        }

        $.each(champs_libres_valeurs_par_defaut,function(index, champ_libre){
            if(champ_libre.nom_sql == nom_sql)
                champs_libres_valeurs_par_defaut.splice(index,1);
        });

    },

    recuperation_champ_libre(correspondance){

        if(correspondance == null || correspondance == undefined)
            return null;

        var correspondances = correspondance.split(".");
        var element = correspondances[0];
        var nom_sql = correspondances[1];

        if(nom_sql == 'id')
            return null;

        var champ_libre_trouve = null;

        if(this.import_sur_mesure.type_element == element)
            champs_libres = this.champs_libres.table_import;
        else{
            var type_element = null;

            $.each(this.import_sur_mesure.tables_jointes,function(index,table_jointe){
                if(table_jointe.id == element)
                    type_element = table_jointe.type_element;

            });

            champs_libres = this.champs_libres.tables_jointes[type_element];
        }

        $.each(champs_libres,function(index, champ_libre){
            if(champ_libre.nom_sql == nom_sql){
                champ_libre_trouve = champ_libre;
                return;
            }
        });

        return champ_libre_trouve;

    },

    gestion_champs(index,informations){

        var vue_instance = this;

        var champ_libre = vue_instance.recuperation_champ_libre(informations.correspondance);

        informations.utilisation_valeur_par_defaut = vue_instance.verification_disponilite_valeur_par_defaut(informations);

        vue_instance.affichages_champs_valeur_par_defaut();

        if(champ_libre != null && [10,11,12].includes(champ_libre.type))
            informations.cle_mise_a_jour = false;
        else if(informations.correspondance != null && informations.correspondance.split('.')[1] == 'id'){

            informations.cle_mise_a_jour = true;
            var element = informations.correspondance.split('.')[0];

            var champ_cle_externe_existant = false;

            var champs_libres = [];

            if(element == vue_instance.import_sur_mesure.type_element)
                champs_libres = vue_instance.champs_libres.table_import;
            else{
                var type_element = null;
                $.each(this.import_sur_mesure.tables_jointes,function(index,table_jointe){
                    if(table_jointe.id == element)
                        type_element = table_jointe.type_element;

                });

                champs_libres = vue_instance.champs_libres.tables_jointes[type_element];
            }

            $.each(champs_libres,function(index,champ_libre){
                if(champ_libre.nom_sql == 'cle_externe')
                    champ_cle_externe_existant = true;
            });

            if(champ_cle_externe_existant)
                vue_instance.doubler_colonne_import(index,informations,informations.correspondance.split('.')[0]+'.cle_externe');

        }
        else if(informations.correspondance == 'aucune_correspondance'){
            informations.correspondances_valeurs = false;
            informations.utilisation_valeur_par_defaut = false;
            informations.cle_mise_a_jour = false;
            informations.mettre_a_jour = false;
        }

        $.each(vue_instance.import_sur_mesure.champs,function(index,informations_champ){

            if(informations.utilisation_valeur_par_defaut == false && informations.correspondance == informations_champ.correspondance)
                informations_champ.utilisation_valeur_par_defaut = false;

            if(informations_champ.correspondance != null &&
                informations_champ.correspondance != 'aucune_correspondance' &&
                vue_instance.cle_sur_element(informations_champ.correspondance.split('.')[0]) &&
                informations_champ.correspondance.split('.')[1] != 'id'
            )
                informations_champ.cle_mise_a_jour = false;

        });

        informations.correspondances_valeurs = false;

        vue_instance.gestion_champs_valeurs_par_defaut();

    },

    gestion_champs_valeurs_par_defaut : function(){

        var vue_instance = this;

        var champs_libres_obligatoires_correspondances = []

        $.each(vue_instance.import_sur_mesure.champs,function(nom_import,informations){

            var champ_libre = vue_instance.recuperation_champ_libre(informations.correspondance);

            if(champ_libre != null && champ_libre.obligatoire == 1)
                champs_libres_obligatoires_correspondances.push(informations.correspondance);
        });

        $.each(vue_instance.champs_libres.table_import,function(index,champ_libre){

            if(champ_libre.obligatoire == 1){

                var correspondance = champ_libre.type_element + '.' + champ_libre.nom_sql;

                var contenu = vue_instance.champs_libres_valeurs_par_defaut_correspondances.table_import.includes(correspondance);
                var contenu_correspondance = champs_libres_obligatoires_correspondances.includes(correspondance);

                if(!contenu && !contenu_correspondance)
                    vue_instance.champs_libres_valeurs_par_defaut.table_import.push(champ_libre);
                else if(contenu_correspondance && contenu){
                    $.each(vue_instance.champs_libres_valeurs_par_defaut.table_import,function(index_valeur_par_defaut,champ_libre_valeur_par_defaut){
                        if(champ_libre_valeur_par_defaut !== undefined && champ_libre_valeur_par_defaut.nom_sql == champ_libre.nom_sql)
                            vue_instance.champs_libres_valeurs_par_defaut.table_import.splice(index_valeur_par_defaut,1);
                    });
                }
            }

        });

        $.each(vue_instance.import_sur_mesure.tables_jointes,function(index_table_jointe,table_jointe){

            $.each(vue_instance.champs_libres.tables_jointes[table_jointe.type_element],function(index,champ_libre){

                if(champ_libre.obligatoire == 1){

                    var correspondance = table_jointe.id + '.' + champ_libre.nom_sql;
                    var contenu = vue_instance.champs_libres_valeurs_par_defaut_correspondances.tables_jointes.includes(correspondance);
                    var contenu_correspondance = champs_libres_obligatoires_correspondances.includes(correspondance);

                    if(!contenu && !contenu_correspondance)
                        vue_instance.champs_libres_valeurs_par_defaut.tables_jointes[table_jointe.id].push(champ_libre);
                    else if(contenu_correspondance && contenu){
                        $.each(vue_instance.champs_libres_valeurs_par_defaut.tables_jointes[table_jointe.id],function(index_valeur_par_defaut,champ_libre_valeur_par_defaut){
                            if(champ_libre_valeur_par_defaut.nom_sql == champ_libre.nom_sql)
                                vue_instance.champs_libres_valeurs_par_defaut.tables_jointes[table_jointe.id].splice(index_valeur_par_defaut,1);
                        });

                    }
                }
            });

        });

    },

    gestion_cle_mise_a_jour: function(informations){

        $.each(vue_instance.import_sur_mesure.champs,function(index,informations_champ){

            if(informations_champ.cle_mise_a_jour === true &&
                vue_instance.cle_sur_element_principal === false &&
                informations_champ.correspondance != null &&
                informations_champ.correspondance.split(".")[0] != vue_instance.import_sur_mesure.type_element
            ){
                informations_champ.cle_mise_a_jour = false;
            }
        });

        if(informations.cle_mise_a_jour){
            informations.mettre_a_jour = false;
            informations.utilisation_valeur_par_defaut = false;
        }
    },

    doubler_colonne_import: function(index,informations,correspondance = 'aucune_correspondance'){

        var vue_instance = this;
        var nouveau_nom_champ = informations.champ_import;
        var nom_non_unique = true;
        var compteur = 0;

        while(nom_non_unique){

            compteur ++;

            if(vue_instance.import_sur_mesure.champs[nouveau_nom_champ] != undefined)
                nouveau_nom_champ = informations.champ_import+'_'+compteur;
            else
                nom_non_unique = false;
        }

        if(correspondance != 'aucune_correspondance'){

            var correspondance_deja_existante = false;
            $.each(vue_instance.import_sur_mesure.champs,function(index,champ){
                if(champ.correspondance == correspondance){
                    correspondance_deja_existante = true;
                    return;
                }
            });

            if(correspondance_deja_existante)
                return;

        }

        if(informations.cle_cree == true){

            var nouveau_champ = {
                champ_import : nouveau_nom_champ,
                correspondance : correspondance,
                table_cle : informations.table_cle,
                cle_cree : true,
                champ_import_parent : informations.champ_import,
            };

        }
        else{

            var nouveau_champ = {
                champ_import : nouveau_nom_champ,
                correspondance : correspondance,
                utilisation_valeur_par_defaut : true,
                cle_mise_a_jour : false,
                mettre_a_jour : false,
                correspondances_valeurs : false,
                champ_import_parent : informations.champ_import,
            };
        }

        vue_instance.import_sur_mesure.champs.splice(index+1,0,nouveau_champ);

        vue_instance.$forceUpdate();
    },

    supprimer_colonne_import(index){

        vue_instance.import_sur_mesure.champs.splice(index,1);
    },

    verification_disponilite_valeur_par_defaut(informations){

        var vue_instance = this;

        var champ_libre = vue_instance.recuperation_champ_libre(informations.correspondance);

        if(champ_libre != null && [10,11,12].includes(champ_libre.type)){

            var nombre_de_correspondance = 0;

            $.each(this.import_sur_mesure.champs,function(index,informations_champ){
                if(informations_champ.correspondance == informations.correspondance)
                    nombre_de_correspondance++;
            });

            if(nombre_de_correspondance > 1)
                return false;

        }

        return true;
    },

    enregistrer_import: function(){

        var vue_instance = this;

        $.post({

            url: '{{URL::route('import_sur_mesure.enregistrer_champs')}}',
            data: {
                import_sur_mesure : vue_instance.import_sur_mesure,
                champs_libres_valeurs_par_defaut : vue_instance.champs_libres_valeurs_par_defaut,
                import_en_cours : vue_instance.import_en_cours
            },
            dataType: 'json',
        }).done(function(import_sur_mesure){

            info('Import enregistré avec succès');
            vue_instance.import_sur_mesure = import_sur_mesure;
            loading(false);
        });

    },

    suite_parametrage : async function(){

        var vue_instance = this;

        var correspondances = false;

        for(informations of this.import_sur_mesure.champs){
            if(informations.correspondances_valeurs == true)
                correspondances = true;
        }

        if(correspondances)
            return this.gestion_correspondances();

        if(this.cle_sur_element_principal)
            return this.verifications_cles();

        this.etape_import++;
        this.enregistrer_import();
    },

    cle_sur_element(type_element){

        var cle_sur_element = false;

        $.each(this.import_sur_mesure.champs,function(index,champ){
            if(champ.correspondance == type_element+'.id' && champ.cle_mise_a_jour === true){
                cle_sur_element = true;
                return;
            }
        });

        return cle_sur_element;
    },

    gestion_correspondances : async function(){

        this.correspondance_en_visionnage = null;

        loading(true);

        var donnees = await $.post({

            url: '{{URL::route('import_sur_mesure.correspondance_valeurs_liste')}}',
            data: {
                import_sur_mesure : vue_instance.import_sur_mesure,
                import_en_cours : vue_instance.import_en_cours
            },
            dataType: 'json',
        });

        loading(false);

        this.correspondances = donnees.correspondances;

        for(correspondance of this.correspondances){

            if(this.correspondance_en_visionnage == null)
                this.correspondance_en_visionnage = correspondance.champ_import;

            for(informations of this.import_sur_mesure.champs){

                if(informations.champ_import == correspondance.champ_import){

                    var champ_libre = this.recuperation_champ_libre(informations.correspondance);

                    correspondance.champ_libre = champ_libre;

                    if(informations.correspondances_valeurs_liste == undefined)
                        informations.correspondances_valeurs_liste = [];

                    for(valeur of Object.values(correspondance.valeurs_import)){

                        var existant = false;

                        for(information_existante of informations.correspondances_valeurs_liste){
                            if(information_existante.valeur == valeur){

                                existant = information_existante[champ_libre.type_element] !== undefined && information_existante[champ_libre.type_element][champ_libre.nom_sql] !== undefined;

                                if(existant && typeof information_existante[champ_libre.type_element][champ_libre.nom_sql] == 'object'){
                                    for(index_valeur_correspondance in information_existante[champ_libre.type_element][champ_libre.nom_sql]){

                                        valeur_correspondance_liste = information_existante[champ_libre.type_element][champ_libre.nom_sql][index_valeur_correspondance];

                                        if(valeur_correspondance_liste == 'true')
                                            information_existante[champ_libre.type_element][champ_libre.nom_sql][index_valeur_correspondance] = true;

                                        if(valeur_correspondance_liste == 'false')
                                            information_existante[champ_libre.type_element][champ_libre.nom_sql][index_valeur_correspondance] = false;

                                    }
                                }
                            }
                        }

                        if(existant === false){

                            var donnee = {valeur : valeur};

                            var modele = {};

                            if([10,11,12].includes(champ_libre.type))
                                modele[champ_libre.nom_sql] = {};
                            else
                                modele[champ_libre.nom_sql] = null;

                            donnee[champ_libre.type_element] = modele;

                            informations.correspondances_valeurs_liste.push(donnee);
                        }
                    }
                }
            }
        }

        for(correspondance of this.correspondances){

            for(cle_import of Object.keys(correspondance.valeurs_import)){

                valeur_import = correspondance.valeurs_import[cle_import];

                this.initialisation_component(correspondance,valeur_import);
            }
        }

        this.affichage_modale_correspondance = true;

        this.$forceUpdate();
    },

    verifications_cles : async function(changement_etape = true){

        loading(true);

        var donnees = await $.post({

            url: '{{URL::route('import_sur_mesure.verifications_cles')}}',
            data: {
                import_sur_mesure : vue_instance.import_sur_mesure,
                import_en_cours : vue_instance.import_en_cours
            },
            dataType: 'json',
        });

        loading(false);

        if(donnees.succes !== true){
            await alerte_eden(donnees.message);
            return;
        }

        this.etat_post_import = donnees.etat_post_import;

        if(changement_etape == true){
            this.etape_import++;
            this.enregistrer_import();
        }
    },

    champs_libres_affichage: function(correspondance){
        return structuredClone(this.champs_libres_tries).map((champs_libres_type_element) => {
            champs_libres_type_element.champs_libres = champs_libres_type_element.champs_libres.filter((champ_libre) => {
                return correspondance == champs_libres_type_element.type_element+'.'+champ_libre.nom_sql || !this.correspondances_selectionnees.includes(champs_libres_type_element.type_element+'.'+champ_libre.nom_sql) || [10,11,12].includes(champ_libre.type);
            });
            return champs_libres_type_element;
        });
    },

@endpush

@push('donnees_pour_vuejs_computed')

    champs_libres_non_valeurs_par_defaut : function(){

        var vue_instance = this;

        var champs_libres_non_valeurs_par_defaut = {
            table_import : [],
            tables_jointes : {},
        };

        $.each(vue_instance.champs_libres.table_import,function(index,champ_libre){

            var correspondance = false;

            var nommage = vue_instance.import_sur_mesure.type_element+'.'+champ_libre.nom_sql;

            $.each(vue_instance.import_sur_mesure.champs,function(index,informations){
                if(informations.correspondance == nommage)
                    correspondance = true;
            });

            $.each(vue_instance.champs_libres_valeurs_par_defaut.table_import,function(index_valeur_par_defaut,champ_libre_valeur_par_defaut){
                if(champ_libre == champ_libre_valeur_par_defaut)
                    correspondance = true;
            });

            if(!correspondance)
                champs_libres_non_valeurs_par_defaut.table_import.push(champ_libre);
        });

        $.each(vue_instance.import_sur_mesure.tables_jointes,function(index_table_jointe,table_jointe){

            champs_libres_non_valeurs_par_defaut.tables_jointes[table_jointe.id] = [];

            $.each(vue_instance.champs_libres.tables_jointes[table_jointe.type_element],function(index,champ_libre){

                var correspondance = false;

                var nommage = table_jointe.id+'.'+champ_libre.nom_sql;

                $.each(vue_instance.import_sur_mesure.champs,function(index,informations){
                    if(informations.correspondance == nommage)
                        correspondance = true;
                });

                $.each(vue_instance.champs_libres_valeurs_par_defaut.tables_jointes[table_jointe.id],function(index_valeur_par_defaut,champ_libre_valeur_par_defaut){
                    if(champ_libre == champ_libre_valeur_par_defaut)
                        correspondance = true;
                });

                if(!correspondance)
                    champs_libres_non_valeurs_par_defaut.tables_jointes[table_jointe.id].push(champ_libre);
            });
        });

        return champs_libres_non_valeurs_par_defaut;
    },

    correspondances_selectionnees:function(){

        var correspondances_selectionnees = [];

        $.each(this.import_sur_mesure.champs,function(index,informations){
            correspondances_selectionnees.push(informations.correspondance);
        });

        return correspondances_selectionnees;
    },

    cle_sur_element_principal:function(){

        var vue_instance = this;

        var cle_sur_element_principal = false;

        $.each(this.import_sur_mesure.champs,function(index,informations){

            if(informations.correspondance == null || informations.correspondance == 'aucune_correspondance' )
                return;

            var type_element =  informations.correspondance.split('.')[0];

            if(type_element == vue_instance.import_sur_mesure.type_element && informations.cle_mise_a_jour === true){
                cle_sur_element_principal = true;
                return;
            }
        });

        return cle_sur_element_principal;
    },

    champs_libres_valeurs_par_defaut_correspondances: function(){

        var vue_instance = this;

        var champs_libres_valeurs_par_defaut_correspondances = {
            table_import : [],
            tables_jointes : [],
        };

        $.each(vue_instance.champs_libres_valeurs_par_defaut.table_import,function(index,champ_libre){
            champs_libres_valeurs_par_defaut_correspondances.table_import.push(champ_libre.type_element+'.'+champ_libre.nom_sql);
        });

        $.each(vue_instance.champs_libres_valeurs_par_defaut.tables_jointes,function(id_table_jointe,champs_libres){
            $.each(champs_libres,function(index,champ_libre){
                champs_libres_valeurs_par_defaut_correspondances.tables_jointes.push(id_table_jointe+'.'+champ_libre.nom_sql);
            });
        });

        return champs_libres_valeurs_par_defaut_correspondances;

    },

    champs_libres_tries : function(){

        var champs_libres_tries = [];

        var champs_libres = structuredClone(this.champs_libres.table_import)

        champs_libres.unshift({
            nom_sql: 'id',
            nom : 'ID',
            type_element : this.import_sur_mesure.type_element,
            type : 2,
        });

        champs_libres_tries.push({
            type_element : this.import_sur_mesure.type_element,
            champs_libres : champs_libres,
            index_traduction: 'tables_libres.'+this.import_sur_mesure.type_element+'.nom_table'
        });

        for(type_element in this.champs_libres.tables_jointes){

            var champs_libres_table_jointe = structuredClone(this.champs_libres.tables_jointes[type_element]);

            champs_libres_table_jointe.unshift({
                nom_sql: 'id',
                nom : 'ID',
                type_element : type_element,
                type : 2,
            });

            champs_libres_tries.push({
                type_element : type_element,
                champs_libres : champs_libres_table_jointe,
                index_traduction: 'tables_libres.'+type_element+'.nom_table'
            });
        }

        return champs_libres_tries;
    },

@endpush