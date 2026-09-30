@extends('eden::templates.template')

@section('title') Traduction @stop

@php
    $background_navbar = maquette('background_navbar');

    list($r, $g, $b) = sscanf($background_navbar, "#%02x%02x%02x");

    $rgba = $r.', '.$g.', '.$b.',0.3';
@endphp

@section('content')

    <div class="content-wrapper traduction" >
		<div id="base-content" class="container-fluid">
			@include('eden::includes.fil_ariane', ['fil_ariane' => array(
					array('nom' => 'Traduction')
				)])
			<div class="row">
				<div class="col-md-12">
					<div class="card mb-3">
						<div class="card-header d-flex align-items-center">
                            <h4 style="font-size:20px">
                                <span style="cursor: pointer" @click="categorie = null">@traduction('interface.index_traduction.titre')</span>
                                <template v-if="categorie != null">
                                    <span style="color: rgb(163, 163, 163);" class="dropdown"  @mouseout="choix_categorie = false" @mouseover="choix_categorie = true">
                                        <span v-html="categories[categorie]" ></span>
                                        <i v-if="choix_categorie === true" style="font-size: 17px;" class="fas fa-chevron-up"></i>
                                        <i v-else style="font-size: 17px;" class="fas fa-chevron-down"></i>
                                        <div v-show="choix_categorie" style="position: absolute;background-color: #f9f9f9;min-width: 100%;box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);padding: 12px 16px;z-index: 1059;font-size:12px;right: 0;width: max-content;">
                                            <div v-if="index_categorie != categorie && index_categorie != 0" style="padding-bottom: 5px;cursor: pointer;" @click="changement_categorie(index_categorie)" v-for="(categorie_disponible,index_categorie) in categories" v-html="categorie_disponible"></div>
                                        </div>
                                    </span>
                                </template>
                            </h4>
                            <div style="margin-left: auto!important;display:inline-flex">
                                <input v-if="categorie == null" type="text" placeholder="Recherche" id="js_recherche_liste" class="css_input_recherche_liste" v-on:keyup.enter="recherche_traduction_methode()" v-model="recherche_globale" style="padding-left: 5px">
                                    <div v-if="categorie == null" class="css_btn_recherche_liste" @click="recherche_traduction_methode()">
                                        <i aria-hidden="true" class="css_action_icon css_font_16 fa fa-search"></i>
                                    </div>
                                <div v-if="enregistrement_en_cours == 1" style="display: inline-flex;">
                                    <img style="width: 34px;height: 34px;" src="{{asset('eden/images/loader.svg')}}" />
                                    <span style="margin:10px">@traduction('interface.index_traduction.enregistrement_en_cours')</span>
                                </div>
                                <div v-if="enregistrement_en_cours == 2" style="display: inline-flex;">
                                    <i style="color:green;font-size:20px;margin:6px;" class="fas fa-check"></i>
                                    <span style="margin:10px">@traduction('interface.index_traduction.enregistrement_effectue')</span>
                                </div>
                                <template v-if="categorie !== null">
                                    <span class="dropdown css_form" id="dropdown_filtre_recherche">
                                        <span style="border-radius:unset;padding: 5px;" class="css_action_icon" @click="affichage_filtre_recherche = !affichage_filtre_recherche">
                                            <i aria-hidden="true" class="css_action_icon css_font_16 fas fa-search"></i>
                                            <i aria-hidden="true" style="font-size: 10px;position: relative;top: -1px;" class="fas fa-chevron-down"></i>
                                        </span>
                                        <div v-show="affichage_filtre_recherche" style="position: absolute;background-color: #f9f9f9;min-width: 100%;box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);z-index: 1059;font-size:12px;width: max-content;right:0;top:30px;">
                                            <label class="filtre_traduction">@traduction('interface.index_traduction.filtre.effectuer_la_recherche_sur') :</label>
                                            <div class="filtre_traduction">
                                                <input @change="chargement_table_traduction" id="filtres_recherches_index_categorie" type="checkbox" v-model="filtres_recherches.index_categorie">
                                                <label for="filtres_recherches_index_categorie">@traduction('interface.index_traduction.filtre.colonne_pour_recherche.index')</label>
                                            </div>
                                            <div class="filtre_traduction" v-for="(langue,index_langue) in langues">
                                                <input @change="chargement_table_traduction" :id="'filtres_recherches_'+langue.code" type="checkbox" v-model="filtres_recherches[langue.code]">
                                                <label :for="'filtres_recherches_'+langue.code" v-html="traduction('interface.index_traduction.filtre.colonne_pour_recherche.traduction_en') + ' ' + langue.nom.toLowerCase()"></label>
                                            </div>
                                            <hr>
                                            <label class="filtre_traduction">@traduction('interface.index_traduction.filtre.type_de_recherche') :</label>
                                            <div class="filtre_traduction">
                                                <input style="cursor: pointer;" id="filtres_recherches_type_recherche_contient" @change="chargement_table_traduction" type="radio" value="contient" v-model="filtres_recherches.type_recherche">
                                                <label style="cursor: pointer;" for="filtres_recherches_type_recherche_contient" style="cursor: pointer;" >@traduction('interface.index_traduction.filtre.type_de_recherche.contient')</label>
                                            </div>
                                            <div class="filtre_traduction">
                                                <input id="filtres_recherches_type_recherche_commence_par" @change="chargement_table_traduction"  type="radio" value="commence_par" v-model="filtres_recherches.type_recherche">
                                                <label for="filtres_recherches_type_recherche_commence_par">@traduction('interface.index_traduction.filtre.type_de_recherche.commence_par')</label>
                                            </div>
                                            <div class="filtre_traduction" style="padding:15px">
                                                <input id="filtres_recherches_type_recherche_egal" @change="chargement_table_traduction" type="radio" value="egal"  v-model="filtres_recherches.type_recherche">
                                                <label for="filtres_recherches_type_recherche_egal">@traduction('interface.index_traduction.filtre.type_de_recherche.egal_a')</label>
                                            </div>
                                        </div>
                                    </span>
                                    <input class='css_input_recherche_liste js_input_recherche_liste' :placeholder="traduction('interface.listes.recherche')" type="text" @change="chargement_table_traduction()" v-model="recherche" />
                                    <span class="dropdown" id="dropdown_filtre_langue" >
                                        <span @click="affichage_filtre_langue = !affichage_filtre_langue" data-toggle="tooltip" data-placement="left" data-original-title="Filtre langue" class="css_ajouter_element ml-2">
                                            <i aria-hidden="true" class="css_action_icon fas fa-language css_font_16"></i>
                                        </span>
                                        <div v-show="affichage_filtre_langue" style="position: absolute;background-color: #f9f9f9;min-width: 100%;box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);z-index: 1059;font-size:12px;width: max-content;right:0;">
                                            <div class="filtre_traduction" v-for="(langue,index_langue) in langues">
                                                <input :id="'filtres_langues_'+langue.code" @change="cle_filtre_langue ++;" type="checkbox" v-model="filtres_langues[langue.code]">
                                                <label :for="'filtres_langues_'+langue.code" v-html="langue.nom"></label>
                                            </div>
                                            <hr>
                                            <div style="text-align: center;padding: 0 15px 15px;cursor: pointer;" @click="filtre_langue_meme_valeur(false)" v-html="traduction('interface.valeurs_select.aucun')"></div>
                                            <div style="text-align: center;padding: 0 15px 15px;cursor: pointer;" @click="filtre_langue_meme_valeur(true)" v-html="traduction('interface.valeurs_select.tous')"></div>
                                        </div>
                                    </span>
                                    <span class="dropdown" id="dropdown_filtre_type" >
                                        <span @click="affichage_filtre_type = !affichage_filtre_type" data-toggle="tooltip" data-placement="left" data-original-title="Filtre type" class="css_ajouter_element ml-2">
                                            <i aria-hidden="true" class="css_action_icon fas fa-cog css_font_16"></i>
                                        </span>
                                        <div v-show="affichage_filtre_type" style="position: absolute;background-color: #f9f9f9;min-width: 100%;box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);z-index: 1059;font-size:12px;width: max-content;right:0;">
                                            @if(env('BASE_TRADUCTION') !== true)
                                                <label class="filtre_traduction">@traduction('interface.index_traduction.filtre.affichage_des_index') : </label>
                                                <div class="filtre_traduction">
                                                    <input id="filtres_types_standard" @change="chargement_table_traduction" type="checkbox" v-model="filtres_types['standard']">
                                                    <label for="filtres_types_standard">@traduction('interface.index_traduction.filtre.affichage_des_index.index_standard')</label>
                                                </div>
                                                <div class="filtre_traduction">
                                                    <input id="filtres_types_specifique" @change="chargement_table_traduction" type="checkbox" v-model="filtres_types['specifique']">
                                                    <label for="filtres_types_specifique">@traduction('interface.index_traduction.filtre.affichage_des_index.index_sspecifique')</label>
                                                </div>
                                                <hr>
                                            @endif
                                            <label class="filtre_traduction">Affichage des valeurs vides pour : </label>
                                            <div class="filtre_traduction" :style="index_langue + 1 == langues.length ? 'padding : 15px' : ''" v-for="(langue,index_langue) in langues">
                                                <input @change="chargement_table_traduction" :id="'filtres_valeurs_vides_'+langue.code" type="checkbox" v-model="filtres_valeurs_vides[langue.code]">
                                                <label :for="'filtres_valeurs_vides_'+langue.code" v-html="traduction('interface.index_traduction.filtre.colonne_pour_recherche.traduction_en') + ' ' + langue.nom.toLowerCase()"></label>
                                            </div>
                                        </div>
                                    </span>
                                </template>
                                <traduction-action icone="fas fa-plus" :categorie="categorie"></traduction-action>
                                @if(env('BASE_TRADUCTION') !== true)
                                    @include('eden::traduction.modale_synchronisation_environnement')
                                    @include('eden::traduction.modale_ajout_rapide_base_modele')
                                @endif
                                <span data-toggle="tooltip" @click="import_traduction_json" data-placement="left" data-original-title="Import json" class="css_ajouter_element ml-2">
                                    <i aria-hidden="true" class="css_action_icon fas fa-file-import css_font_16"></i>
                                </span>
                            </div>
						</div>
                        <div class="row" v-if="!resultats_recherche">
                            <div class="card-body" v-if="categorie == null">
                                <div class="row">
                                    @foreach($categories as $id_categorie => $categorie)
                                        @php
                                            if($id_categorie == 0)
                                                continue;
                                        @endphp
                                        <div class="col-md-3 mb-3">
                                            <span @click="affichage_traduction_categorie({{$id_categorie}})">
                                                <div class="css_block_acces_module_parametrage">
                                                    <span>
                                                        {!! $categorie !!}
                                                    </span>
                                                </div>
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="card-body" v-else>
                                <table v-if="!chargement_table_traductions" style="width: 100%;" class="table table-bordered table-hover" width="100%" cellspacing="0">
                                     <thead>
                                        <tr>
                                            <th style="width:30%" scope="col"></th>
                                            <th :style="'width:'+(70/langues_affichage_tableau.length)+'%'" v-for="langue in langues_affichage_tableau" scope="col">@{{ langue.nom }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td style="width:30%" scope="col">@traduction('interface.index_traduction.modification_en_masse')</td>
                                            <td :style="'width:'+(70/langues_affichage_tableau.length)+'%'" v-for="langue in langues_affichage_tableau" scope="col">
                                                <div style="display: inline-flex;width:100%">
                                                     <textarea  v-if="modification_en_masse[langue.code].textarea === true" @dblclick="modification_en_masse[langue.code].textarea = false;$forceUpdate();" style="width:100%;height:150px" v-model="modification_en_masse[langue.code].valeur"></textarea>
                                                    <input v-else @dblclick="modification_en_masse[langue.code].textarea = true;$forceUpdate();" style="width:100%;" type="text" v-model="modification_en_masse[langue.code].valeur" />
                                                    <span @click="modifier_en_masse(langue.code)" style="width: 30px;background-color:var(--background_navbar);cursor:pointer;" >
                                                        <i class="fas fa-angle-double-down" style="position: relative;top: 4px;left: 0.5vw;color: white;"></i>
                                                    </span>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <table v-if="chargement_table_traductions" style="width: 100%;" class="table table-bordered table-hover" id="liste_champs_libres" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th style="width:30%" scope="col">@traduction('interface.index_traduction.table_traductions.colonne.index')</th>
                                            <th :style="'width:'+(70/langues_affichage_tableau.length)+'%'" v-for="langue in langues_affichage_tableau" scope="col">@{{ langue.nom }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="5" style="height: 15vh;text-align: center;">
                                                <img class="image_rotation" src="{{asset('eden/images/logo_eden.svg')}}" />
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <traduction-table ref="traduction_table" v-show="!chargement_table_traductions" :categorie="categorie" :key="traduction_table_cle" :langues_affichage_tableau="langues_affichage_tableau" :recherche="recherche" :filtres_types="filtres_types" :filtres_recherches="filtres_recherches" :filtres_valeurs_vides="filtres_valeurs_vides"> </traduction-table>
                            </div>
                        </div>
                        <div class="row" v-else>
                            
                            <div class="col-md-12">
                                <span v-if="!chargement_recherche_globale" class="btn btn-xs btn-primary" id="revenir_traduction" @click="resultats_recherche = false; recherche_globale = ''">Revenir aux traductions</span>
                                <img v-if="chargement_recherche_globale" class="image_rotation" src="{{asset('eden/images/logo_eden.svg')}}" />
                                <table v-else-if="resultats_recherche.length > 0" class="table table-bordered table-hover css_form css_table_parametrage">
                                    <tbody v-if="resultats_recherche.length">
                                    
                                        <template v-for="resultat in resultats_recherche">
                                            <tr>
                                                <td colspan="2" class="css_form_ligne_titre">
                                                    @{{  resultat.categorie_id | nom_valeur_liste_formatee(590) }}
                                                    
                                                </td>
                                            </tr>
                                            <template v-for="valeur in resultat.valeurs">
                                                <tr>
                                                    <td>
                                                        <a @click="resultats_recherche = false; recherche_globale=''; affichage_traduction_categorie(resultat.categorie_id); recherche = valeur;">  @{{ valeur }}</a>
                                                    </td>
                                                </tr>
                                            </template>
                                        </template>
                                    </tbody>
                                </table>
                                <div v-else class="col text-center">
                                    <p class="aucun_resultat">Aucun resultat</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <template v-if="modale_import_traduction_json">
		<transition name="modal" >
			<div class="modal-mask">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">

						<div class="modal-header">
							<h5 class="modal-title">@traduction('interface.index_traduction.modal_import_traduction_json.titre')</h5>
						</div>

						<div class="modal-body css_form js_selection_element" >
                            <textarea v-model="import_json" style="height:50vh"></textarea>
						</div>

						<div class="modal-footer">
							<button type="button" class="btn btn-secondary" @click="modale_import_traduction_json = false">@traduction('interface.modales.fermer')</button>
                            <button type="button" class="btn btn-primary" @click="importer_json()">@traduction('interface.modales.importer')</button>
						</div>

					</div>
				</div>
			</div>
		</transition>
	</template>

@endsection

@push('donnees_pour_vuejs_data')
    langues:{},
    categories: {!! collect($categories) !!},
    categorie : {!! $categorie_a_afficher === null ? 'null' : $categorie_a_afficher !!},
    modale_import_traduction_json:false,
    enregistrement_en_cours: 0,
    recherche: '{{$recherche}}',
    chargement_table_traductions: false,
    choix_categorie: false,
    import_json: '',
    filtres_langues:{},
    filtres_valeurs_vides:{},
    affichage_filtre_langue:false,
    cle_filtre_langue: 0,
    filtres_types:{
        'standard' : true,
        'specifique' : true,
    },
    affichage_filtre_type:false,
    affichage_filtre_recherche:false,
    filtres_recherches:{
        'index_categorie' : true,
        'type_recherche' : 'contient',
    },
    modification_en_masse:{},
    traduction_table_cle: 0,
    enregistrement_effectue:null,
    recherche_globale: '',
    resultats_recherche: false,
    chargement_recherche_globale: true,
    
@endpush

@push('donnees_pour_vuejs_methods')

    affichage_traduction_categorie: async function(id_categorie,supprimer_recherche = true, forcer_recharement_traduction = false){

        var vue_instance = this;

        vue_instance.categorie = id_categorie;

        if(id_categorie === null){
            return;
        }

        if(supprimer_recherche)
            vue_instance.recherche = '';

        if(forcer_recharement_traduction === true)
            vue_instance.$refs.traduction_table.charger_traductions();
    },

    import_traduction_json :function(){

        this.modale_import_traduction_json = true;

    },

    changement_categorie(id_categorie){

        this.affichage_traduction_categorie(id_categorie);

        this.choix_categorie = false;
    },

    importer_json(){

        var vue_instance = this;

        loading(true);

        $.post({
            url : '{{URL::to('eden/parametrage/traduction/import')}}',
            dataType : 'json',
            data : {
                'import' : vue_instance.import_json,
            },
        }).done(async function(donnees){

            loading(false);

            if(donnees.retour == false){
                toastr.error(donnees.message);
                return;
            }

            toastr.success(vue_instance.traduction('messages.js.import_succes'));

            await vue_instance.affichage_traduction_categorie(vue_instance.categorie,false,true);

            vue_instance.modale_import_traduction_json = false;

        });
    },

    filtre_langue_meme_valeur : function(valeur){

        $.each(vue_instance.filtres_langues,function(langue,statut){
            vue_instance.filtres_langues[langue] = valeur;
        });

        vue_instance.$forceUpdate();
        vue_instance.cle_filtre_langue++;
    },

    modifier_en_masse: async function(langue){

        var vue_instance = this;

        var modification = vue_instance.modification_en_masse[langue].valeur;

        if(modification == '')
            return;

        if(!await confirm_eden())
			return false;

        loading(true);

        var index = [];

        var composant_table =  vue_instance.$refs.traduction_table;

        composant_table.traductions_par_sous_categorie.forEach(function(sous_categorie){

            sous_categorie.traductions_a_afficher.forEach(function(traduction){
                index.push(traduction.index);
            });
        });

        $.post({
            url:'{{URL::to('eden/parametrage/traduction/modifier_en_masse')}}',
            dataType: 'json',
            data:{
                langue: langue,
                index: index,
                modification: modification,
            }
        }).done(async function(donnees){

            if(donnees.retour == false){
                toastr.error(donnees.message);
                return;
            }

            vue_instance.modification_en_masse[langue].valeur = '';

            await vue_instance.affichage_traduction_categorie(vue_instance.categorie,false,true);

            loading(false);

            toastr.success(vue_instance.traduction('messages.js.modification_masse_succes'));

        });
    },

    chargement_table_traduction:function(){

        var composant = this.$refs.traduction_table;

        if(composant === undefined)
            return;

        composant.calcul_traductions_a_afficher();
    },

    recherche_traduction_methode:function(){
    
        this.chargement_recherche_globale = true;
        this.resultats_recherche = [];
        // on va chercher les potentielles occurences
        $.post({
            url: "{{ URL::to('/eden/parametrage/traduction/recherche') }}",
            dataType: "json",
            method: "post",
            data:{
                recherche_texte: this.recherche_globale,
            },
        }).done((retour) => {
            this.resultats_recherche = retour;
             this.chargement_recherche_globale = false;   
        });
    },

@endpush

@push('donnees_pour_vuejs_computed')

    langues_affichage_tableau:function(){

        var langues_affichage_tableau = [];

        var vue_instance = this;

        this.cle_filtre_langue;

        var filtres_langues = vue_instance.filtres_langues;

        $.each(vue_instance.langues,function(index,langue){
            if(filtres_langues[langue.code] === true)
                langues_affichage_tableau.push(langue);
        });

        return langues_affichage_tableau;
    },

@endpush

@push('donnees_pour_vuejs_mounted')

    var vue_instance = this;

    if(vue_instance.categorie != 0)
        vue_instance.chargement_table_traductions = true;

    vue_instance.langues = vue_instance.$root.langues_traduction_erp;

    $.each(this.langues,function(index,langue){
        vue_instance.filtres_langues[langue.code] = true;
        vue_instance.filtres_valeurs_vides[langue.code] = false;
        vue_instance.filtres_recherches[langue.code] = true;
        vue_instance.modification_en_masse[langue.code] = {valeur : ''};
    });

    $(document).on('click', function(e) {
        var target = $(e.target);

        if(!target.is($('#dropdown_filtre_langue').find('*').addBack())) {
            vue_instance.affichage_filtre_langue = false;
        }

        if(!target.is($('#dropdown_filtre_type').find('*').addBack())) {
            vue_instance.affichage_filtre_type = false;
        }

        if(!target.is($('#dropdown_filtre_recherche').find('*').addBack())) {
            vue_instance.affichage_filtre_recherche = false;
        }
    });

    vue_instance.affichage_traduction_categorie(vue_instance.categorie,false);

    vue_instance.$on('enregistrement_en_cours',function(){
        vue_instance.enregistrement_en_cours = 1;

        if(vue_instance.enregistrement_effectue != null){
            clearTimeout(vue_instance.enregistrement_effectue);
            vue_instance.enregistrement_effectue = null;
        }
    });

    vue_instance.$on('enregistrement_termine',function(){
        vue_instance.enregistrement_en_cours = 2;

        vue_instance.enregistrement_effectue = setTimeout(function(){

            vue_instance.enregistrement_en_cours = 0;
            vue_instance.enregistrement_effectue = null;
        },5000);
    });

    vue_instance.$on('chargement_table_traductions',function(){
        vue_instance.chargement_table_traductions = true;
        vue_instance.$forceUpdate();
    });

    vue_instance.$on('fin_chargement_table_traductions',function(){
        vue_instance.chargement_table_traductions = false;
        vue_instance.$forceUpdate();
    });
@endpush