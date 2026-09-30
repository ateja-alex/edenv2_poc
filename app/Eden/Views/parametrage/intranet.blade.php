@extends('eden::templates.template')

@section('title') Intranet @stop

@push('link')
    <link rel="stylesheet" media="screen" href="{{ asset('eden/configuration_intranet/css/custom.css') }}" type="text/css"/>
@endpush

@section('options_fil_ariane')
    <i class="fas fa-history css_action_icon secondaire" @click="annuler_changement" v-if="structure_intranet_sauvegarde.length > 0"></i>
    <i class="fas fa-save css_action_icon secondaire" @click="enregistrer_changement"></i>
@endsection

@section('content')

	<div class="content-wrapper parametrage_intranet">
		<div id="base-content" class="container-fluid">

			@include('eden::includes.fil_ariane', ['fil_ariane' => array(
				array('route' => 'parametrage.index', 'nom' => 'Paramétrage'),
				array('nom' => 'Intranet')
			)])

			<div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body" style="z-index: 500;">
                            <section class="intranet home  on">
                                <div class="container">
                                    <draggable v-model="structure_intranet.lignes" @change="verification_changement_ordre" >
                                        <div class="parametrage_intranet_ligne" :key="modules_intranets_par_ligne.index" v-for="(modules_intranets_par_ligne,index_ligne) in structure_intranet.lignes">

                                            <draggable ghost-class="fantome_ligne_module" @change="ajout_nouveau_module($event,modules_intranets_par_ligne)" style="flex-wrap: nowrap;" :group="{ name: 'modules' }" class="row" :list="modules_intranets_par_ligne.modules" tag="div">

                                                <div :key="module_intranet.id" v-for="(module_intranet,index_module) in modules_intranets_par_ligne.modules" :class="'col-sm-'+module_intranet.taille"
                                                     :style="'margin-left:'+((module_intranet.taille_avant*100)/12)+'%;margin-right:'+((module_intranet.taille_apres*100)/12)+'%'"
                                                    @mouseenter="module_affichage_options = module_intranet.id;affichage_couleur($event,module_intranet.couleur);"
                                                    @mouseleave="module_affichage_options = null;desaffichage_couleur($event);">
                                                    <div
                                                          class="box" :style="'height:'+structure_intranet.parametrage.bloc.taille+'px'" >
                                                        <p class="title" :style="'font-size:'+structure_intranet.parametrage.bloc.taille_texte+'px'">
                                                            <b v-if="module_intranet.nouvel_element !== true" v-html="traduction('intranet.modules.module_'+module_intranet.id,'nom')"></b>
                                                            <b v-else v-html="module_intranet.nom_module"></b>
                                                        </p>
                                                        <span :style="'font-size:'+structure_intranet.parametrage.bloc.taille_icone+'px'" :class="'icone_intranet '+module_intranet.icone"></span>
                                                        <span v-if="module_affichage_options == module_intranet.id" class="options_module_intranet">
                                                            <a target="_blank"
                                                               :href="'eden/parametrage/formulaire/'+module_intranet.nom_formulaire"
                                                               v-if="module_intranet.type_module == 'formulaire' && module_intranet.type_element && module_intranet.nom_formulaire"
                                                               class="bulle_option"
                                                               style="background-color : limegreen">
                                                                <i class="fas fa-file-alt"></i>
                                                            </a>
                                                            <span 
                                                                  @click="gerer_action_liste(module_intranet)"
                                                                  v-if="module_intranet.type_module == 'liste' && module_intranet.type_element && module_intranet.liste"
                                                                  class="bulle_option"
                                                                  style="background-color : navy">
                                                                <i class="fas fa-list"></i>
                                                            </span>
                                                            <span @click="parametrage_module(module_intranet,modules_intranets_par_ligne)" class="bulle_option">
                                                                <i class="fas fa-cog"></i>
                                                            </span>
                                                            <span @click="suppresion_module(modules_intranets_par_ligne.modules,index_module, module_intranet.id)" class="bulle_suppression">
                                                                <i class="fas fa-times"></i>
                                                            </span>
                                                        </span>
                                                    </div>
                                                </div>

                                            </draggable>
                                            <span style="left: -24px;top: -13px;" class="options_module_intranet">
                                                <span class="bulle_suppression" @click="suppression_ligne(index_ligne)">
                                                    <i class="fas fa-times"></i>
                                                </span>
                                            </span>
                                        </div>
                                    </draggable>
                                </div>
                                <div class="parametrage_intranet_ligne_ajouter" @click="ajouter_ligne">
                                    <i class="fas fa-plus"></i>
                                    <span>Ajouter une ligne</span>
                                </div>
                            </section>
                        </div>
                     </div>
                </div>
                <div class="col-md-4">
                    <div class="sections_modales" style="margin: unset;position:relative;">
                        <div class="section_modale" >
                            <span style="margin-left: 1px;" :class="(onglet_ouvert == 'ajout' ? 'section_active' : '')+' nom_section'"
                                @click="onglet_ouvert = 'ajout'">
                                Ajout
                            </span>
                            <div class="card mt-auto contenu_section" v-show="onglet_ouvert == 'ajout'">
                                <div class="card-header" style="display: inline-flex">
                                    <h5>Ajout module</h5>
                                    <span class="ml-auto">
                                        <input type="text" @keyup="deploiement('modules',true);deploiement('autres_modules',true)" v-model="recherche_ajout_module" placeholder="Recherche ...">
                                    </span>
                                </div>
                                <div class="card-body">
                                    <div>
                                        <div class="css_pointer" @click="deploiement('modules')" style="padding: 10px;display:inline-flex;width: 100%;align-items: center;margin-bottom:5px;">
                                            <h6>Modules sur table</h6>
                                            <span class="ml-auto">
                                                 <i :class="'fas fa-chevron-'+(deploiement_parametrage.includes('modules') ? 'up' : 'down')"></i>
                                            </span>
                                        </div>
                                        <div v-if="deploiement_parametrage.includes('modules')" class="liste_modules_intranet">
                                            <draggable :list="modules_par_defaut" :group="{ name: 'modules', pull: 'clone', put: false }">
                                                <div
                                                    class="row"
                                                    v-for="module_disponible in modules_par_defaut"
                                                    :key="module_disponible.type_module"
                                                    style="margin:unset;"
                                                >
                                                    <div class="col-sm-12 ligne_module_ajout_intranet">
                                                        <i v-if="module_disponible.type_module == 'formulaire'" class="fas fa-file-alt"></i>
                                                        <i v-else class="fas fa-list"></i>
                                                        <span class="ml-auto" style="font-size:12px;text-transform: capitalize;">
                                                            @{{ module_disponible.type_module }}
                                                            <span v-if="module_disponible.index_traduction">
                                                                : @{{ $root.traduction(module_disponible.index_traduction) }}
                                                            </span>
                                                            <span>
                                                                intranet
                                                            </span>
                                                        </span>
                                                    </div>
                                                </div>
                                            </draggable>
                                        </div>
                                        <div class="css_pointer" @click="deploiement('autres_modules')" style="padding: 10px;display:inline-flex;width: 100%;align-items: center;margin-bottom:5px;">
                                            <h6>Autres modules</h6>
                                            <span class="ml-auto">
                                                 <i :class="'fas fa-chevron-'+(deploiement_parametrage.includes('autres_modules') ? 'up' : 'down')"></i>
                                            </span>
                                        </div>
                                        <div v-if="deploiement_parametrage.includes('autres_modules')" class="liste_modules_intranet">
                                            <draggable :list="autres_modules_disponibles" :group="{ name: 'modules', pull: 'clone', put: false }">
                                                <div
                                                  class="row"
                                                  v-for="module in autres_modules_disponibles"
                                                  :key="module.module_par_defaut"
                                                  style="margin:unset;"
                                                >
                                                    <div class="col-sm-12 ligne_module_ajout_intranet">
                                                        <i class="fas fa-file"></i>
                                                        <span class="ml-auto" style="font-size:12px;text-transform: capitalize;">
                                                            @{{ module.module_par_defaut | retraite_caracteres_speciaux }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </draggable>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="section_modale ">
                            <span :class="(onglet_ouvert == 'parametrage' ? 'section_active' : '')+' nom_section'"
                                @click="onglet_ouvert = 'parametrage'">
                                Paramétrage
                            </span>
                            <div class="card contenu_section" v-show="onglet_ouvert == 'parametrage'">
                                <div class="card-header" style="display:inline-flex;align-items:center;">
                                    <h5>Paramétrage des modules</h5>
                                </div>
                                <div class="card-body">
                                    <form class="css_form">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <h6>Paramétres généraux</h6>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4">
                                                Taille des blocs
                                            </div>
                                            <div class="col-sm-8" style="display: inline-flex;gap: 15px;">
                                                <input min="50" max="500" type="range" :value="structure_intranet.parametrage.bloc.taille" @change="changement_valeur_range($event,structure_intranet.parametrage.bloc,'taille')">
                                                <input style="width: 60px;text-align: end;" type="number" v-model="structure_intranet.parametrage.bloc.taille" @wheel.prevent @keydown.up.prevent @keydown.down.prevent> px
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4">
                                                Taille des icones
                                            </div>
                                            <div class="col-sm-8" style="display: inline-flex;gap: 15px;">
                                                <input min="10" max="200" type="range" :value="structure_intranet.parametrage.bloc.taille_icone" @change="changement_valeur_range($event,structure_intranet.parametrage.bloc,'taille_icone')">
                                                <input style="width: 60px;text-align: end;" type="number" v-model="structure_intranet.parametrage.bloc.taille_icone" @wheel.prevent @keydown.up.prevent @keydown.down.prevent> px
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4">
                                                Taille des titres
                                            </div>
                                            <div class="col-sm-8" style="display: inline-flex;gap: 15px;">
                                                <input min="10" max="50" type="range" :value="structure_intranet.parametrage.bloc.taille_texte" @change="changement_valeur_range($event,structure_intranet.parametrage.bloc,'taille_texte')">
                                                <input style="width: 60px;text-align: end;" type="number" v-model="structure_intranet.parametrage.bloc.taille_texte" @wheel.prevent @keydown.up.prevent @keydown.down.prevent> px
                                            </div>
                                        </div>
                                        <div v-if="module_affichage_option !== null" style="padding-top:15px;margin-top:15px;border-top:1px solid lightgrey">
                                            <div class="row">
                                                <div class="col-sm-12" style="display:inline-flex">
                                                    <h6 v-if="module_affichage_option.nouvel_element !== true" v-html="'Module : '+traduction('intranet.modules.module_'+module_affichage_option.id,'nom')"></h6>
                                                    <h6 v-else v-html="'Module : '+module_affichage_option.nom_module"></h6>
                                                    <div class="ml-auto">
                                                        <i class="fas fa-times css_pointer" @click="module_affichage_option = null;ligne_affichage_option = null;"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-12" v-if="module_affichage_option.nouvel_element !== true">
                                                    <traduction-table :vertical=true :key="module_affichage_option.id"  categorie="15" :filtrage_index="'intranet.modules.module_'+module_affichage_option.id+'.nom'"></traduction-table>
                                                </div>
                                                <template v-else>
                                                    <div class="col-sm-4">
                                                        Nom module
                                                    </div>
                                                    <div class="col-sm-8">
                                                        <input type="text" v-model="module_affichage_option.nom_module">
                                                    </div>
                                                </template>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-4">
                                                    Type module
                                                </div>
                                                <div class="col-sm-8">
                                                    <input type="hidden" v-model="module_affichage_option.type_element" name="module_affichage_option.type_element">
                                                    <select-table-libre 
                                                        @changement_select_table_libre="changement_select_table_libre($event)"
                                                        :tables_libres="types_element_disponibles" :type_element="module_affichage_option.type_element"></select-table-libre>
                                                </div>
                                            </div>
                                            <div class="row" v-if="module_affichage_option.type_module == 'liste' && module_affichage_option.type_element != ''">
                                                <div class="col-sm-4">
                                                    Listes possibles
                                                </div>
                                                <div class="col-sm-7">
                                                    <select v-model="module_affichage_option.liste" name="module_affichage_option.liste">
                                                        <option value="">Aucune valeur</option>
                                                        <option v-for="liste in listes_disponibles" :value="liste.id" v-html="liste.rapport_index_traduction ? traduction(liste.rapport_index_traduction+'.titre') : traduction(liste.table_index_traduction+'.element_pluriel')"></option>
                                                    </select>
                                                </div>
                                                <div class ="col-sm-1" v-if="module_affichage_option.type_element != ''">
                                                    <span data-toggle="tooltip"  data-placement="left" data-original-title="Créer une liste" @click="creer_liste()">
                                                        <i aria-hidden="true" class="fas fa-plus css_pointer"></i>   
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="row" v-if="module_affichage_option.type_module == 'liste' && module_affichage_option.type_element != ''" >
                                                <div class='col-sm-4'>
                                                    Formulaire associé
                                                </div>
                                                <div class="col-sm-8">
                                                    <select v-model="module_affichage_option.formulaire_liste" name="module_affichage_option.formulaire_liste">
                                                        <option value="">Aucune valeur</option>
                                                        <option v-for="formulaire in formulaires_associes" :value="formulaire.id" v-html="traduction('formulaire.'+ formulaire.nom_formulaire +'.titre') + ' ('+formulaire.nom_formulaire+')'"></option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row" v-if="module_affichage_option.type_element != '' && module_affichage_option.type_module == 'formulaire'">
                                                <div class="col-sm-4">
                                                    Formulaires possibles
                                                </div>
                                                <div class="col-sm-7">
                                                    <select v-model="module_affichage_option.nom_formulaire" name="module_affichage_option.nom_formulaire">
                                                        <option value="">Aucune valeur</option>
                                                        <option v-for="formulaire in formulaires_disponibles" :value="formulaire.nom_formulaire" v-html="traduction(formulaire.index_traduction+'.titre')+' ('+formulaire.nom_formulaire+')'"></option>
                                                    </select>
                                                </div>
                                                <div class ="col-sm-1">
                                                    <span data-toggle="tooltip"  data-placement="left" data-original-title="Créer un formulaire" @click="creer_formulaire()">
                                                        <i aria-hidden="true" class="fas fa-plus css_pointer"></i>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="row" v-if="module_affichage_option.type_module == 'formulaire' && module_affichage_option.type_element != ''">
                                                <div class="col-sm-4">
                                                    Champ utilisateur
                                                </div>
                                                <div v-if="module_affichage_option.champs_libres_disponibles.length == 1" class="col-sm-8">
                                                    <span v-html="traduction('champs_libres.'+module_affichage_option.type_element+'.'+module_affichage_option.champ_utilisateur,'nom')"></span>
                                                    ( @{{ module_affichage_option.champ_utilisateur }} )
                                                </div>
                                                <div v-else class="col-sm-8">
                                                    <span v-for="champ_libre in module_affichage_option.champs_libres_disponibles"
                                                          @click="module_affichage_option.champ_utilisateur = champ_libre.nom_sql"
                                                          :class="'css_pointer badge badge-'+(module_affichage_option.champ_utilisateur == champ_libre.nom_sql ? 'success' : 'default')">
                                                        <span v-html="traduction('champs_libres.'+module_affichage_option.type_element+'.'+champ_libre.nom_sql,'nom')"></span>
                                                        ( @{{ champ_libre.nom_sql }} )
                                                    </span>
                                                </div>
                                            </div>
                                             <div class="row">
                                                <div class="col-sm-4">
                                                    Condition d'affichage
                                                </div>
                                                <div class="col-sm-8" style="display: inline-flex;gap: 15px;">
                                                    <input type="text" v-model="module_affichage_option.condition_affichage">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-4">
                                                    Couleur de fond
                                                </div>
                                                <div class="col-sm-8">
                                                    <input type="color" v-model="module_affichage_option.couleur">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-4">
                                                    Icone
                                                </div>
                                                <div class="col-sm-8">
                                                    <button type="button" class="btn btn-primary"  >
                                                        <i :class="module_affichage_option.icone"></i>
                                                    </button>
                                                    <button type="button" id="selection_icone" class="icp icp-dd btn btn-primary dropdown-toggle icone_url"
                                                        data-selected="fa-paperclip" data-toggle="dropdown">
                                                        <span class="caret"></span>
                                                        <span class="sr-only">Icone</span>
                                                    </button>
                                                    <div class="dropdown-menu"></div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-4">
                                                    Taille avant
                                                </div>
                                                 <div class="col-sm-8" style="display: inline-flex;gap: 15px;">
                                                    <input min="0" max="12" type="range" :value="module_affichage_option.taille_avant" @change="changement_valeur_range($event,module_affichage_option,'taille_avant',ligne_affichage_option)">
                                                    <input min="0" max="12" step="1" style="width: 50px;text-align: end;" type="number" :value="module_affichage_option.taille_avant" @change="changement_valeur_range($event,module_affichage_option,'taille_avant',ligne_affichage_option)" @wheel.prevent @keydown.up.prevent @keydown.down.prevent>
                                                 </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-4">
                                                    Taille
                                                </div>
                                                 <div class="col-sm-8" style="display: inline-flex;gap: 15px;">
                                                    <input min="0" max="12" type="range" :value="module_affichage_option.taille" @change="changement_valeur_range($event,module_affichage_option,'taille',ligne_affichage_option)">
                                                    <input min="0" max="12" step="1" style="width: 50px;text-align: end;" type="number" :value="module_affichage_option.taille" @change="changement_valeur_range($event,module_affichage_option,'taille',ligne_affichage_option)" @wheel.prevent @keydown.up.prevent @keydown.down.prevent>
                                                 </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-4">
                                                    Taille après
                                                </div>
                                                 <div class="col-sm-8" style="display: inline-flex;gap: 15px;">
                                                    <input min="0" max="12" type="range" :value="module_affichage_option.taille_apres" @change="changement_valeur_range($event,module_affichage_option,'taille_apres',ligne_affichage_option)">
                                                    <input min="0" max="12" step="1" style="width: 50px;text-align: end;" type="number" :value="module_affichage_option.taille_apres" @change="changement_valeur_range($event,module_affichage_option,'taille_apres',ligne_affichage_option)" @wheel.prevent @keydown.up.prevent @keydown.down.prevent>
                                                 </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('eden::parametrage.include.modale_ajout_liste_libre', [
        'parametrage_intranet' => true,
    ])

@endsection

@push('donnees_pour_vuejs_data')
    structure_intranet : {!! collect($structure_intranet) !!},
    structure_intranet_sauvegarde : [],
    autres_modules:{!! collect($autres_modules) !!},
    derniers_ids:{!! collect($derniers_ids) !!},
    module_affichage_options : null,
    module_affichage_option : null,
    ligne_affichage_option : null,
    onglet_ouvert: 'ajout',
    recherche_ajout_module : '',
    deploiement_parametrage:[],
    listes_disponibles : [],
    formulaires_disponibles : [],
    modules_par_defaut : {!! collect($modules_par_defaut) !!},
    tables_libres_disponibles : {!! collect($tables_libres_disponibles) !!},
@endpush

@push('donnees_pour_vuejs_methods')

    affichage_couleur(event,couleur){

        $(event.target).children(":first").css('background-color',couleur);
    },

    desaffichage_couleur(event){

        $(event.target).children(":first").css('background-color','rgba(145, 145, 145, 1)');
    },

    annuler_changement(){

        var index_dernier_element = this.structure_intranet_sauvegarde.length - 1;

        this.structure_intranet = this.structure_intranet_sauvegarde[index_dernier_element];

    },

    suppresion_module(modules_intranets_par_ligne,index_module, id_module){
        
        modules_intranets_par_ligne.splice(index_module, 1);

        if(id_module == this.module_affichage_option.id)
            this.module_affichage_option = null;

        this.module_affichage_options = null;
    },

    enregistrer_changement(){

        var vue_instance = this;

        var erreur_taille = false;

        vue_instance.structure_intranet.lignes.forEach(function(ligne){

            if(erreur_taille == true)
                return;

            if(vue_instance.erreur_taille_ligne(ligne.modules) == true)
                erreur_taille = true;
        });

        if(erreur_taille == true){
            toastr.error("Vos modules ne doivent pas dépassés le cadre");
            return;
        }

        loading(true);

        $.post({
            url: '{{route('parametrage.intranet.enregistrer_nouvelle_structure')}}',
            dataType : 'json',
            data:{
                structure_intranet : vue_instance.structure_intranet
            }
        }).done(async function(nouvelle_structure){

            vue_instance.structure_intranet = nouvelle_structure;

            toastr.success("Enregistrement effectué avec succés");

            vue_instance.$forceUpdate();

            vue_instance.mise_a_jour_traductions_valeurs();

            vue_instance.structure_intranet_sauvegarde = [];

            vue_instance.module_affichage_option = null;
            vue_instance.ligne_affichage_option = null;

            loading(false);
        });

    },

    recupere_listes_type_element: function(){

        var vue_instance = this;

        this.$root.cache.parametrage_intranet ??= {};

        this.$root.cache.parametrage_intranet.listes_type_element ??= {};

        const type_element = this.module_affichage_option.type_element;

        if(this.$root.cache.parametrage_intranet.listes_type_element[type_element] != undefined)
            vue_instance.listes_disponibles = this.$root.cache.parametrage_intranet.listes_type_element[type_element];

        else{
            $.get({

                url: 'eden/parametrage/intranet/recupere_listes_type_element' + '/' + type_element,
                dataType: "json"

            }).done((donnees) => {

                this.listes_disponibles = donnees;

                this.$root.cache.parametrage_intranet.listes_type_element[type_element] = donnees;
            });
        }
        
    },

    recupere_formulaires_type_element: function(){

        var vue_instance = this;

        this.$root.cache.parametrage_intranet ??= {};

        this.$root.cache.parametrage_intranet.formulaires_type_element ??= {};

        const type_element = this.module_affichage_option.type_element;
        this.module_affichage_option.champs_libres_disponibles = this.tables_libres_disponibles[type_element];

        if(this.$root.cache.parametrage_intranet.formulaires_type_element[type_element] != undefined)
            vue_instance.formulaires_disponibles = this.$root.cache.parametrage_intranet.formulaires_type_element[type_element];

        else{
            $.get({

                url: 'eden/parametrage/intranet/recupere_formulaires_type_element' + '/' + type_element,
                dataType: "json"

            }).done((donnees) => {

                this.formulaires_disponibles = donnees;

                this.$root.cache.parametrage_intranet.formulaires_type_element[type_element] = donnees;
                    
            });
        } 
    },

    creer_formulaire : function(){
        loading(true);

        var vue_instance = this;
        const parametrage_formulaire = window.open('', '_blank');

        var nom_formulaire_a_creer = this.generer_nom_formulaire(this.module_affichage_option.type_element);

        $.post({
            url: '/eden/parametrage/formulaire/ajouter/intranet/' + this.module_affichage_option.type_element + '/' + nom_formulaire_a_creer,
            dataType: "json",

        }).done((formulaire) => {
            var module = vue_instance.module_affichage_option;
            this.mise_a_jour_traductions_valeurs();

            if(this.$root.cache.parametrage_intranet?.formulaires_type_element?.[module.type_element] != undefined)
                this.$root.cache.parametrage_intranet.formulaires_type_element[module.type_element].push(formulaire);

            this.module_affichage_option.nom_formulaire = formulaire.nom_formulaire;

            loading(false);

            parametrage_formulaire.location = 'eden/parametrage/formulaire/' + formulaire.nom_formulaire;
        });
        
    },

    generer_nom_formulaire: function(type_element){
        const regex = new RegExp(`^intranet_${type_element}_(\\d+)$`);

        const numeros = this.formulaires_disponibles
            .map(f => {
                const match = f.nom_formulaire.match(regex);
                return match ? parseInt(match[1], 10) : null;
            })
            .filter(n => n !== null);

        const numero_max = numeros.length ? Math.max(...numeros) : 0;

        return `intranet_${type_element}_${numero_max + 1}`;
    },

    parametrage_module: async function(module_intranet,ligne){

        var vue_instance = this;

        vue_instance.onglet_ouvert = 'parametrage';

        vue_instance.module_affichage_option = null;

        await vue_instance.module_affichage_option == null;

        vue_instance.module_affichage_option = module_intranet;
        vue_instance.ligne_affichage_option = ligne;

        if(vue_instance.module_affichage_option.type_element){
            if(vue_instance.module_affichage_option.type_module == 'formulaire')
                vue_instance.recupere_formulaires_type_element();
            else if(vue_instance.module_affichage_option.type_module == 'liste'){
                vue_instance.recupere_listes_type_element();
            }   
        }
        
        await vue_instance.module_affichage_option != null;

        $('.icp-dd').iconpicker({
            defaultValue: false,
            placement: 'bottomLeft',
            hideOnSelect: false,
        });

        var action = $._data(document.getElementById('selection_icone'), "events");

        if(action.click === undefined){
            $('#selection_icone').on('iconpickerSelected', function (e) {

                vue_instance.module_affichage_option.icone = e.iconpickerValue;
            });
        }
    },

    changement_valeur_range(event,valeur_a_changer,champ,ligne = false){

        var nouvelle_valeur = parseInt($(event.target).val());

        var module = JSON.parse(JSON.stringify(valeur_a_changer));

        module[champ] = nouvelle_valeur;

        if(ligne !== false && this.erreur_taille_ligne(ligne.modules,module,18) == true){

            $(event.target).val(valeur_a_changer[champ]);
            toastr.error("Taille impossible");
        }
        else
            valeur_a_changer[champ] = nouvelle_valeur;
    },

    erreur_taille_ligne: function(modules_par_ligne, nouvelle_valeur_module = false, seuil_max = 12){

        var erreur_taille_ligne = false;

        var compteur_taille_ligne = 0;

        modules_par_ligne.forEach(function(module){

            if(nouvelle_valeur_module !== false && nouvelle_valeur_module.id == module.id)
                module = nouvelle_valeur_module;

            if(erreur_taille_ligne == true)
                return;

            compteur_taille_ligne += parseInt(module.taille) + parseInt(module.taille_avant) + parseInt(module.taille_apres);

            if(compteur_taille_ligne > seuil_max)
                erreur_taille_ligne = true;
        });

        return erreur_taille_ligne;
    },

    gerer_action_liste : function(module){
        if(module.liste)
            window.open("{{ URL::to('/eden/parametrage/intranet/gestion_liste') }}" + '/' + module.type_element + '/' + module.liste, '_blank');  
    },

    creer_liste : function(module = false){
        if(module)
            this.module_affichage_option = module;
        this.choix_type_liste({index : 'rapport',nom : 'Rapport'});
        this.modale_ajout_liste_libre = true;
    },

    verification_changement_ordre : function(){

        var erreur_taille = false;

        var vue_instance = this;

        vue_instance.structure_intranet.lignes.forEach(function(ligne){

            if(erreur_taille == true)
                return;

            if(vue_instance.erreur_taille_ligne(ligne.modules,false,18) == true)
                erreur_taille = true;
        });

        if(erreur_taille == true){
            this.annuler_changement();
            toastr.error("Taille indisponible");
        }

        return erreur_taille;
    },

    ajouter_ligne:function(){

        var vue_instance = this;

        var nouvelle_id_ligne = vue_instance.dernier_id_ligne + 1;

        vue_instance.structure_intranet.lignes.push({
            'id' : nouvelle_id_ligne,
            'modules' : [],
        });
    },

    suppression_ligne:function(index_ligne){

        if(!confirm('Etes vous sur de vouloir supprimer cette ligne ?'))
            return false;

        this.structure_intranet.lignes.splice(index_ligne,1);
    },

    deploiement(nom_module, forcer_deploiement = false){

        if(forcer_deploiement === true){

            if(!this.deploiement_parametrage.includes(nom_module))
                this.deploiement_parametrage.push(nom_module);

            return;
        }

        if(this.deploiement_parametrage.includes(nom_module))
            this.deploiement_parametrage.splice(this.deploiement_parametrage.indexOf(nom_module),1);
        else
            this.deploiement_parametrage.push(nom_module);
    },

    ajout_nouveau_module(ajout, ligne){

        if(this.verification_changement_ordre() === true || ajout.added === undefined)
            return;

        element_ajoute = ajout.added.element;

        if(element_ajoute.id === undefined){

            element_ajoute.id = this.dernier_id_module + 1;
            element_ajoute.nouvel_element = true;

            this.parametrage_module(element_ajoute,ligne);
        }
    },

@endpush

@push('donnees_pour_vuejs_computed')
    structure_intranet_json: function() {
        var vue_instance = this;

        return JSON.stringify(vue_instance.structure_intranet);
    },

    dernier_id_ligne: function() {
        var vue_instance = this;

        var max_id_ligne = parseInt(vue_instance.derniers_ids.dernier_id_ligne);

        vue_instance.structure_intranet.lignes.forEach(function(ligne){

            if(ligne.id > max_id_ligne)
                max_id_ligne = ligne.id;
        });

        return max_id_ligne;
    },

    dernier_id_module: function() {
        var vue_instance = this;

        var max_id_module = parseInt(vue_instance.derniers_ids.dernier_id_module);

        vue_instance.structure_intranet.lignes.forEach(function(ligne){

            ligne.modules.forEach(function(module){
                if(module.id > max_id_module)
                    max_id_module = module.id;
            });

        });

        return max_id_module;
    },

    autres_modules_disponibles : function(){

        var vue_instance = this;

        var recherche = vue_instance.recherche_ajout_module.toLowerCase();

        var autres_modules_disponibles = [];

        vue_instance.autres_modules.forEach(function(module){

            var nom_module = vue_instance.$options.filters.retraite_caracteres_speciaux(module.module_par_defaut).toLowerCase();

            if(!nom_module.includes(recherche))
                return;

            autres_modules_disponibles.push(module);
        });

        return autres_modules_disponibles;
    },

    types_element_disponibles : function(){

        let resultat = [];
        for(let table in this.tables_libres_disponibles){
            resultat.push({
                type_element: table,
                index_traduction: 'tables_libres.'+ table
            });
        }

        return resultat;
    },

    formulaires_associes() {

        const type_element = this.module_affichage_option.type_element;

        if (!type_element) return [];

        let formulaires = [];

        for (let ligne of Object.values(this.structure_intranet.lignes)) {
            for (let module of Object.values(ligne.modules)) {
                if (module.type_module === 'formulaire' && module.type_element === type_element) 
                    formulaires.push(module);
            }
        }

        return formulaires;
    },

    changement_select_table_libre: function(table){

        if(table)
            this.module_affichage_option.type_element = table.type_element;
        else
            this.module_affichage_option.type_element = '';

        if(this.module_affichage_option.type_element){

            if(this.module_affichage_option.type_module == 'formulaire')
                this.recupere_formulaires_type_element();
            else
                this.recupere_listes_type_element();      
        }
    },

@endpush

@push('donnees_pour_vuejs_mounted')

    var vue_instance = this;

    $(document).keydown(function(e){
          if( e.which === 90 && e.ctrlKey && vue_instance.structure_intranet_sauvegarde.length > 0){
             vue_instance.annuler_changement();
          }
    });

@endpush

@push('donnees_pour_vuejs_watch')

    structure_intranet_json :{

        handler: function(nouvelle_valeur, ancienne_valeur) {

            var ancienne_valeur = JSON.parse(ancienne_valeur);

            var index_dernier_element = this.structure_intranet_sauvegarde.length - 1;

            var derniere_sauvegarde = this.structure_intranet_sauvegarde[index_dernier_element];

            if(nouvelle_valeur == JSON.stringify(derniere_sauvegarde))
                this.structure_intranet_sauvegarde.splice(index_dernier_element);
            else
                this.structure_intranet_sauvegarde.push(ancienne_valeur);

        },
        deep:true
    },
@endpush