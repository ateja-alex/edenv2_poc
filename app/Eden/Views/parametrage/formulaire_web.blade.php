@extends('eden::templates.template')

@section('title') Paramétrage formulaire @stop

@section('styles')
    <style type="text/css">
        /* Firefox */
        input[type=number] {
            -moz-appearance: textfield;
        }

        /* Chrome */
        input::-webkit-inner-spin-button,
        input::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin:0;
        }

        /* Opéra*/
        input::-o-inner-spin-button,
        input::-o-outer-spin-button {
            -o-appearance: none;
            margin:0
        }

        tr.js_scroll{
            background-color: rgb(238, 238, 238);
            transition-duration:1.5s;
            transition-timing-function: ease-in;
        }

        tr.js_scroll td.css_nom_champ {
            font-size: 1.2em;
            transition-duration:1.5s;
            transition-timing-function: ease-in;
        }

        tr {
            background-color: white;
            transition-duration:1.5s;
            transition-timing-function: ease-in;
        }

        td.css_nom_champ {
            font-size: 1em;
            transition-duration:1.5s;
            transition-timing-function: ease-in;
        }

        .element_apercu{

            background: #E3E1E1;
            color: #aaa;
            height: 30px;
            float:left
        }

        .avap{
            background-color: #CFCFCF;
        }

        .champ{
            background: #F1F0F0;
        }

        .bouton_header {
            float: right;
            margin-left: 10px;
        }

        .select_champs_libres .affichage{
            width: 100%!important;
        }

    </style>

@endsection

@section('content')

    <div class="content-wrapper" >
        <div id="base-content" class="container-fluid">

            @php
                $nom_formulaire_tmp = 'générique';
                if(!empty($formulaire->index_traduction)) {
                    $nom_formulaire_tmp = \Illuminate\Support\Facades\Blade::compileString('@traduction("'.$formulaire->index_traduction.'","titre")');
                }
            @endphp
            @include('eden::includes.fil_ariane', ['fil_ariane' => array(
                    array('route' => 'parametrage.index', 'nom' => 'Paramétrage'),
                    array('route' => 'parametrage.table_libre.principales', 'nom' => 'Elements paramétrables'),
                    array('route' => 'parametrage.table_libre.zoom','arguments' => [table_libre($type_element)->type_element] , 'nom' => table_libre($type_element)->element),
                    array('nom' => 'Formulaire - '.$nom_formulaire_tmp)
                )])

            <div class="row">
                <div class="col-md-12">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h4>
                                &Eacute;léments du formulaire
                            </h4>
                            <span @click="supprimer_formulaire()"  data-toggle="tooltip" data-placement="top" title="Supprimer le formulaire" class="css_ajouter_element css__lien bouton_header" data-original-title="Supprimer le formulaire"><i aria-hidden="true" class="css_action_icon fa fa-fw fa-trash"></i></span>

                            <span data-toggle="tooltip" data-placement="left" title="" class="css_ajouter_element css__lien bouton_header" data-original-title="Modifier le formulaire" @click="modifier_formulaire()"><i class="css_action_icon fa fa-pencil-square-o" aria-hidden="true"></i></span>

                            <span @click="modifier_champ()"  data-toggle="tooltip" data-placement="top" title="Ajouter un champ" class="css_ajouter_element css__lien bouton_header" data-original-title="Ajouter un champ"><i aria-hidden="true" class="css_action_icon fa fa-fw fa-plus-square"></i></span>

                            <span @click="affichage_iframe()"  data-toggle="tooltip" data-placement="top" title="Affichage iframe" class="css_ajouter_element css__lien bouton_header"><i aria-hidden="true" class="css_action_icon fa fa-fw fa-code"></i></span>
                        </div>
                        <div class="card-body css_form css_parametrage_formulaire">

                            <div class="alert alert-danger" v-if="champs_libres_obligatoires_non_presents.length > 0">
                                <div>
                                    Certains champs obligatoires ne sont pas présents dans la configuration du formulaire :
                                    <span v-html="champs_libres_obligatoires_non_presents.map(champ_libre => traduction(champ_libre.index_traduction+'.nom') + ' ('+champ_libre.nom_sql+')').join(', ')"></span>
                                </div>
                                <div>
                                    Attention, l'enregistrement ne sera pas possible dans ces conditions.
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="w-100 mt-3">
                                        <div class="d-flex mb-1" style="flex-wrap: wrap;">
                                            <div class="css_th_parametrage_formulaire" style="width: 30%">Intitulé</div>
                                            <div class="css_th_parametrage_formulaire" style="width: 10%">Obligatoire</div>
                                            <div class="css_th_parametrage_formulaire" style="width: 15%">Espace avant intitulé</div>
                                            <div class="css_th_parametrage_formulaire" style="width: 15%">Taille de l'intitulé</div>
                                            <div class="css_th_parametrage_formulaire" style="width: 15%">Taille du champ</div>
                                            <div class="css_th_parametrage_formulaire" style="width: 15%">Taille après le champ</div>
                                        </div>
                                        <draggable v-model="champs_libres" class="w-100 d-flex " style="flex-wrap: wrap;" @change="modification_ordre_champ" handle=".handle">
                                            <div class="css_ligne_parametrage_formulaire handle"  v-for="(champ, index) in champs_libres">
                                                <div class="w-100 d-flex">
                                                    <div style="display: inline-flex;gap:5px;width: 30%;align-items:center;">
                                                        <i @click="supprimer_le_champ(champ)"  class="fas fa-trash mr-1 ml-1"></i>
                                                        @{{ champ.nom }} (@{{ champ.nom_sql }})
                                                    </div>
                                                    <div style="display: inline-flex;gap:5px;width: 10%;align-items:center;justify-content: center;">
                                                        <input v-if="recuperer_champ_libre(champ.nom_sql).obligatoire == 1" type="checkbox" disabled="true" checked>
                                                        <input v-else v-model="champ.condition_obligatoire" :true-value="1" false-value="" @change="enregistre_un_champ(champ)" type="checkbox">
                                                    </div>
                                                    <div class="css_td_input_parametrage_formulaire" style="width: 15%">
                                                        <select v-model="champ.taille_avant" @change="enregistre_un_champ(champ)" style="width: 20%;">
                                                            <option value="0">0</option>
                                                            <option v-for="index in 12" :value="index" v-html="index"></option>
                                                        </select>
                                                    </div>
                                                    <div class="css_td_input_parametrage_formulaire" style="width: 15%">
                                                        <select v-model="champ.taille_libelle" @change="enregistre_un_champ(champ)" style="width: 20%;">
                                                            <option value="0">0</option>
                                                            <option v-for="index in 12" :value="index" v-html="index"></option>
                                                        </select>
                                                    </div>
                                                    <div class="css_td_input_parametrage_formulaire" style="width: 15%">
                                                        <select v-model="champ.taille_champ" @change="enregistre_un_champ(champ)" style="width: 20%;">
                                                            <option value="0">0</option>
                                                            <option v-for="index in 12" :value="index" v-html="index"></option>
                                                        </select>
                                                    </div>
                                                    <div class="css_td_input_parametrage_formulaire" style="width: 15%">
                                                        <select v-model="champ.taille_apres" @change="enregistre_un_champ(champ)" style="width: 20%;">
                                                            <option value="0">0</option>
                                                            <option v-for="index in 12" :value="index" v-html="index"></option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </draggable>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card mb-3">
                        <div class="card-header">
                            <h4>
                                Aperçu affichage
                            </h4>
                        </div>
                        <div class="card-body css_form css_parametrage_formulaire ">
                            <draggable v-model="champs_libres" class="w-100 d-flex" style="flex-wrap: wrap;gap: 5px 0px;" @change="modification_ordre_champ"  handle=".handle" >
                                <template v-for="champ in champs_libres">
                                    <div :class="'element_apercu avap col-sm-'+champ.taille_avant"  v-if="champ.taille_avant != 0"></div>
                                    <div :class="'element_apercu col-sm-'+champ.taille_libelle" v-if="champ.taille_libelle != 0"><p style="text-align:center;font-weight: bold;">@{{ champ.nom }}</p></div>
                                    <div :class="'element_apercu champ col-sm-'+champ.taille_champ" v-if="champ.taille_champ != 0"><p style="text-align:center;">CHAMP</p></div>
                                    <div :class="'element_apercu avap col-sm-'+champ.taille_apres" v-if="champ.taille_apres!= 0"></div>
                                </template>
                            </draggable>
                        </div>
                    </div>

                    @include('eden::parametrage.include.formulaire_valeurs_par_defaut')
                </div>
            </div>
        </div>
    </div>

    <!-- Modal modification du formulaire -->
    <template v-if="modale_modification_formulaire">
        <transition name="modal">
            <div class="modal-mask">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Gestion du formulaire</h5>
                            <button type="button" class="close" @click="modale_modification_formulaire = false">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body css_form">
                            <div class="row">
                                <div class="col-sm-12">
                                    <traduction-table  categorie="12" :filtrage_index="formulaire.index_traduction+'.'"></traduction-table>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-2">
                                    Champ rempli par l'url de source
                                </div>
                                <div class="col-sm-10">
                                    <select-champs-libres :nom_sql="formulaire.champ_url_source_origine"
                                        :type_element="formulaire.type_element"
                                        :champs_libres="[{
                                            type_element : formulaire.type_element,
                                            index_traduction :'tables_libres.'+formulaire.type_element+'.nom_table',
                                            champs_libres: modele_champs_libres.filter(champ_libre => [0,null].includes(champ_libre.type)),
                                        }]"
                                        @changement_select_champs_libres="formulaire.champ_url_source_origine = $event.nom_sql;"
                                    >
                                    </select-champs-libres>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-2">
                                    CSS personnalisé
                                </div>
                                <div class="col-sm-10">
                                    <editeur-code v-model="formulaire.css_personnalise" language="css"></editeur-code>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="modale_modification_formulaire = false">Fermer</button>
                            <button type="button" class="btn btn-primary" @click="enregistrer_formulaire">Enregistrer</button>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </template>


    <!-- Modal ajout élément -->
    <div class="modal fade" id="modal_modification_champ" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Gestion des formulaires</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="#" method="post" class="css_form" id="formulaire_champ_libre">
                        {{ csrf_field() }}
                        <div class="row">
                            <div class="col-sm-12 css_form_ligne_titre">Informations générales</div>
                        </div>
                        <div class="row">
                            <div class="col-sm-3">
                                Nom sql
                            </div>
                            <div class="col-sm-9">
                                <select name="nom_sql" id="nom_sql">
                                    <option v-for="champ_libre in modele_champs_libres_formulaire" :value="champ_libre.nom_sql" v-html="champ_libre.nom"></option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">Taille avant le libellé</div>
                            <div class="col-sm-2">
                                <select name="taille_avant" id="taille_avant">
                                    @for($i=0; $i<=12; $i++)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-sm-4">Taille après le champ</div>
                            <div class="col-sm-2">
                                <select name="taille_apres" id="taille_apres">
                                    @for($i=0; $i<=12; $i++)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-sm-4">Taille du libellé</div>
                            <div class="col-sm-2">
                                <select name="taille_libelle" id="taille_libelle">
                                    @for($i=0; $i<=12; $i++)
                                        @if($i == 2)
                                            <option value="{{ $i }}" selected="">{{ $i }}</option>
                                        @else
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endif
                                    @endfor
                                </select>
                            </div>
                            <div class="col-sm-4">Taille du champ</div>
                            <div class="col-sm-2">
                                <select name="taille_champ" id="taille_champ">
                                    @for($i=0; $i<=12; $i++)
                                        @if($i == 4)
                                            <option value="{{ $i }}" selected="">{{ $i }}</option>
                                        @else
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endif
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                    <button type="button" class="btn btn-primary" @click="enregistrer">Enregistrer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal ajout élément -->
    <div class="modal fade" id="modal_suppression" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Suppression du formulaire</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Êtes-vous certain de vouloir supprimer le formulaire ?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-danger" @click="supprimer_formulaire_valide">Supprimer</button>
                </div>
            </div>
        </div>
    </div>

    <template v-if="modale_iframe">
        <transition name="modal">
            <div class="modal-mask">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">

                        <div class="modal-header">
                            <h5 class="modal-title">Intégration web</h5>
                        </div>

                        <div class="modal-body css_form js_selection_element">
                            <div class="row">
                                <div class="col-sm-2">
                                    Iframe
                                </div>
                                <div class="col-sm-10">
                                    <div style="display: flex">
                                        <input :value="valeur_iframe" style="width: 100%;" disabled>
                                        <span class="fas fa-copy css_pointer css_input_ajout_selection_element css_background_couleur_primaire"
                                           style="padding: 10px;text-align: center;border: 1px solid #d4d4d4;"
                                           @click="copier_iframe"
                                        ></span>
                                    </div>
                                </div>
                            </div>

                            @if(!empty($id_liste_domaine))
                                <div class="row">
                                    <div class="col-sm-12">
                                        <liste-libre-{{ $id_liste_domaine }}

                                            :filtres_pour_fiche="{formulaire_id : {{$formulaire->id}}}"

                                            :modele_par_defaut="{formulaire_id : {{$formulaire->id}}}"

                                            @if(super_admin() || mode_parametrage())
                                                :mode_parametrage=1
                                            @endif
                                        >
                                        </liste-libre-{{ $id_liste_domaine }}>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="modale_iframe = false">@traduction('interface.modales.fermer')</button>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </template>

@endsection

@section('donnees_pour_vuejs_data')
    champs_libres: {!! $les_champs !!},
    type_element: '{{$type_element}}',
    modele_champs_libres: {!! $champs_libres !!},
    formulaire: {!! $formulaire !!},
    champ: {},
    modale_iframe: false,
    modale_modification_formulaire: false,
@endsection

    @section('donnees_pour_vuejs_methods')

    modifier_champ: function(champ) {

        this.champ = champ;
        $('#modal_modification_champ').modal('show');
    },

    supprimer_formulaire: function(event) {

        $('#modal_suppression ').modal('show');
    },

    supprimer_formulaire_valide: function(event) {

        // $('#modal_suppression ').modal('hide');

        $.get({

            url: "{{ URL::to('/eden/parametrage/formulaire/supprimer') }}/{{$formulaire->id}}",
            dataType: "json",
        }).done(async function(donnees) {

            $('#modal_suppression ').modal('hide');

            // console.log(donnees);

            if(donnees == "") {

                window.location = "{{ URL::to("eden/parametrage/table_libre/zoom/".$type_element) }}";
            }
            else{

                await erreur(donnees);
            }
        });
    },

    modifier_formulaire: function() {

        this.modale_modification_formulaire = true;
    },

    enregistre_un_champ: function(champ) {
        loading(true);

        $.post({

            url: "{{ URL::to("eden/parametrage/formulaire/enregistre_un_champ") }}",
            dataType: "json",
            data: {
                champ: champ,
            }
        });

        loading(false);
    },

    enregistrer_formulaire: function() {

        // console.log('enregistrer_formulaire');

        this.modale_modification_formulaire = false;
        var formulaire = vue_instance.formulaire;
        // on enregistre
        $.post({

            url: "{{ route("parametrage.formulaire.modifier") }}",
            dataType: "json",
            data: {
                id_du_formulaire: formulaire['id'],
                nom_formulaire: formulaire['nom_formulaire'],
                titre_formulaire: formulaire['titre_formulaire'],
                css_personnalise: formulaire['css_personnalise'],
                champ_url_source_origine: formulaire['champ_url_source_origine'],
            }
        });
    },

    enregistrer: function() {

        loading(true);

        nom_sql = $("#nom_sql").val();
        taille_libelle = $("#taille_libelle").val();
        taille_champ = $("#taille_champ").val();
        taille_apres = $("#taille_apres").val();
        taille_avant = $("#taille_avant").val();

        $('#modal_modification_champ').modal('hide');
        // on enregistre
        $.post({

            url: "{{ URL::to("eden/parametrage/formulaire") }}",
            dataType: "json",
            data: {
                type_element: '{{$type_element}}',
                nom_sql: nom_sql,
                nom_formulaire: '{{ $nom_formulaire }}',
                taille_avant: parseInt(taille_avant),
                taille_libelle: parseInt(taille_libelle),
                taille_champ: parseInt(taille_champ),
                taille_apres: parseInt(taille_apres),
            }
        }).done(async (donnees) => {

            if(donnees.retour !== true) {

                await erreur(donnees.retour);
                return;
            }
            else{

                this.champs_libres.push(donnees.nouveau_champ);

            }

            loading(false);
        });

    },

    supprimer_le_champ: function(champ) {

        loading(true);

        $.post({

            url: "{{ URL::to("eden/parametrage/formulaire/supprimer") }}",
            dataType: "json",
            data: {
                champ: champ,
            }
        }).done(async function(donnees) {

            if(donnees.retour !== true) {

                await erreur(donnees.retour);
                return;
            }
            else {

                for(index = 0;index < vue_instance.champs_libres.length;index++){

                    if (vue_instance.champs_libres[index] == champ) {

                        vue_instance.champs_libres.splice(index, 1);
                    }
                }

                vue_instance.champs_libres = donnees.les_champs;
            }
            loading(false);

        });
    },

    modification_ordre_champ : function(){

        var champs_libres_ordres = {};

        for(ordre in this.champs_libres){

            var champ_libre = this.champs_libres[ordre];

            champs_libres_ordres[champ_libre.id] = ordre;
        }

        $.post({

            url: "{{ URL::to("eden/parametrage/formulaire/modifier_ordre") }}",
            dataType: "json",
            data: {
                nom_formulaire: this.formulaire.nom_formulaire,
                type_element: this.formulaire.type_element,
                ordres: champs_libres_ordres,
            }
        });
    },

    recuperer_champ_libre : function(nom_sql){

        var champ_libre = {};

        for(champ of this.modele_champs_libres){

            if(champ.nom_sql == nom_sql)
                champ_libre = champ;
        }

        return champ_libre;
    },

    affichage_iframe : function(){

        @if(empty(fonctionnalite('recaptcha_cle_public')) || empty(fonctionnalite('recaptcha_cle_prive')))
            return toastr.error("Veuillez paramétrer le recpatcha dans les fonctionnalités pour pouvoir utiliser l'iframe");
        @endif

        this.modale_iframe = true;

        if(this.formulaire.formulaire_web_id == null)
            this.generation_iframe();
    },

    generation_iframe : function(){

        loading(true);

        $.post({

            url: "{{ route('parametrage.formulaire.generation_iframe') }}",
            dataType:'json',
            data:{
                nom_formulaire: this.formulaire.nom_formulaire,
            }
        }).done((donnees) => {

            this.formulaire = donnees.formulaire;

            loading(false);
        });
    },

    copier_iframe : async function(){

        await navigator.clipboard.writeText(this.valeur_iframe);
        toastr.success('Copié avec succès !');
    },

    @endsection

    @push('donnees_pour_vuejs_computed')

        modele_champs_libres_formulaire : function(){

            return this.modele_champs_libres.filter(champ_libre => ![42, 10, 14, 21, 22].includes(champ_libre.type));
        },

        valeur_iframe : function(){

            return '<iframe width="890" height="700" src="{{URL::to('form') }}/'+this.formulaire.formulaire_web_id+'" allowfullscreen referrerpolicy="unsafe-url"></iframe>';
        },

        champs_libres_obligatoires_non_presents : function(){

            var champs_utilises = this.champs_libres.map(champ => champ.nom_sql).concat(this.valeurs_par_defaut.map(champ => champ.nom_sql));

            return this.modele_champs_libres.filter(champ_libre => champ_libre.obligatoire == 1 && !champs_utilises.includes(champ_libre.nom_sql));
        },
    @endpush

@if(!empty($id_liste_domaine))
    @push('composants_vue')
        <script type="text/javascript" src="{{ asset('storage/composants/liste_libre_'.$id_liste_domaine.'.js') }}"></script>
    @endpush
@endif
