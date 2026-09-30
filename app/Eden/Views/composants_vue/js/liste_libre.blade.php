@include('eden::listes.js')

<script>
     var liste_libre_{{$id_liste}} = Vue.component('liste-libre-{{$id_liste}}', {
        template:`<div id="liste_{{$id_liste}}" class="js_liste" id_liste="{{$id_liste}}">
                        <div class="row" v-show="liste.messsage_liste_succes != ''">
                            <div class="col-md-12">
                                <div class="alert alert-success" v-html="liste.messsage_liste_succes"></div>
                            </div>
                        </div>
                        <div class="row" id="affichage_liste_{{$id_liste}}">
                            <div class="col-md-12">
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <div class="css_flex_header_liste">
                                            <div class="css_titre_liste">
                                                <div class="dropdown dropdown_hover" style="display: flex!important;align-items: center;cursor: pointer;">
                                                    @if(isset($id_rapport) && View::hasSection('header_liste_'.$id_rapport))
                                                        @yield('header_liste_'.$id_rapport)
                                                    @else
                                                        <h4>@include('eden::listes.includes.titre_titre')</h4>
                                                    @endif
                                                    @if(count($autres_vues) > 0)
                                                        <i class="fas fa-angle-down" style="margin: 5px;"></i>
                                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                        @foreach($autres_vues as $autre_vue)
                                                            @if(!empty($autre_vue->liste_libre->id_rapport))
                                                                <a class="dropdown-item" href="{{ route('base_eden.liste.rapport', [$autre_vue->liste_libre->id_rapport], false) }}"> {{ $autre_vue->liste_libre->titre }}</a>
                                                            @else
                                                                <a class="dropdown-item" href="{{ route('base_eden.liste.index', ['type_element' => $autre_vue->liste_libre->type_element], false) }}"> {{ $autre_vue->liste_libre->titre }}</a>
                                                            @endif
                                                        @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                                <template v-if="mode_parametrage == 1">
                                                    <div>
                                                      <a href="{{ route('parametrage.table_libre.zoom', ['type_element' => $type_element], false) }}" data-toggle="tooltip" data-placement="right" :title="'Paramétrer ' + $root.traduction('{{table_libre($type_element)->index_traduction}}','element_pluriel')" class="css_bouton_modifier_liste_primaire" style="padding: 0;">
                                                        <i class="css_action_icon fas fa-cog"></i>
                                                      </a>
                                                    </div>
                                                </template>
                                                @if($liste_libre->desactiver_options == 1)
                                                    <template v-if="mode_parametrage == 1">
                                                        <div>
                                                            <a href="{{ route('parametrage.liste_libre.index', $id_liste, false) }}" data-toggle="tooltip" data-placement="right" :title="'Paramétrer liste ' + @if(empty($rapport)) $root.traduction('{{table_libre($type_element)->index_traduction}}','element_pluriel') @else $root.traduction('{{$rapport->index_traduction}}','titre') @endif" class="css_bouton_modifier_liste_primaire" style="padding: 0;">
                                                                <i class="css_action_icon fas fa-edit"></i>
                                                            </a>
                                                        </div>
                                                    </template>
                                                @endif
                                            </div>
                                            <div class="css_filtres_actions_et_recherche_liste_libre">

                                                @if(empty($liste_libre->desactiver_filtres) || empty($liste_libre->desactiver_recherche_avancee))
                                                   <filtres ref="filtres" v-if="liste.filtres !== false" :desactiver_filtres="@if(!empty($liste_libre->desactiver_filtres)) true @else false @endif" :appliquer_recherche_avancee="@if(empty($liste_libre->desactiver_recherche_avancee)) true @else false @endif" :parametres_recherche_avancee="{type_element: type_element, type : 'liste', id_cible : id_liste, utilisateur_id : $root.moi.id}" :valeurs_filtres="liste.options_liste.filtres" :filtres="liste.filtres"></filtres>
                                                @endif
                                                @if(View::hasSection('actions_supplementaires_sur_listes'))
                                                    @yield('actions_supplementaires_sur_listes')
                                                @endif

                                                @if(empty($liste_libre->desactiver_actions))

                                                   <component v-if="liste.actions && liste.type_element" :is="afficher_actions_masse()"></component>
                                                @endif

                                                @if(empty($liste_libre->desactiver_export))
                                                    <div class="dropdown dropdown_hover bouton_export_liste">

                                                    @if(View::hasSection('modification_style_exporter_liste'))

                                                        @yield('modification_style_exporter_liste')
                                                    @else
                                                        <div class="css_action_icon secondaire" :title="$root.traduction('interface.listes.export')">
                                                        <i class="fas fa-external-link-alt"></i>
                                                        </div>
                                                    @endif
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                    <label class="dropdown-item css__lien" @click="exporter('basique')">@traduction('interface.listes.exporter_colonnes_de_base')</label>
                                                    <label class="dropdown-item css__lien" @click="exporter('csv')">@traduction('interface.listes.exporter_colonnes_de_base_csv')</label>
                                                    <label class="dropdown-item css__lien" @click="exporter('pdf')">@traduction('interface.listes.exporter_colonnes_de_base_pdf')</label>
                                                    <label class="dropdown-item css__lien" v-if="$root.moi.type_utilisateur == 2" @click="exporter('total')">@traduction('interface.listes.exporter_toutes_colonnes')</label>
                                                    @foreach(management($type_element)->retourne_exports_sur_mesure() as $export)
                                                        <label class="dropdown-item css__lien" @click="exporter('sur_mesure', '{{$export->id}}')"><span>@traduction('{{$export->index_traduction}}','titre') (XLSX)</span></label>
                                                        <label class="dropdown-item css__lien" @click="exporter('sur_mesure_csv', '{{$export->id}}')"><span>@traduction('{{$export->index_traduction}}','titre') (CSV)</span></label>
                                                        <label class="dropdown-item css__lien" @click="exporter('sur_mesure_pdf', '{{$export->id}}')"><span>@traduction('{{$export->index_traduction}}','titre') (PDF)</span></label>
                                                    @endforeach
                                                    @php $export_modele = modele('export_compta_modele')->where('type_element',$type_element)->get(); @endphp
                                                    @if($export_modele->isNotEmpty())
                                                        <hr>
                                                        @foreach($export_modele as $export)
                                                            <label class="dropdown-item css__lien" @click="exporter_modele('{{$export->id}}')"><span>{!! management('export_compta_modele',$export,$export->id)->affiche() !!}</span></label>
                                                        @endforeach
                                                    @endif
                                                    </div>
                                                    </div>
                                                @endif
                                                @if(View::hasSection('filtre_recherche_texte'))
                                                    @yield('filtre_recherche_texte')
                                                @else
                                                    @if(empty($liste_libre->desactiver_recherche))
                                                        <div class="css_block_btn_recherche_liste">
                                                            <input class='css_input_recherche_liste js_input_recherche_liste' :placeholder="$root.traduction('interface.listes.recherche')" :title="$root.traduction('interface.listes.recherche')" style="padding-left: 5px" type="text" name="recherche" v-model="liste.options_liste.recherche" v-on:keyup.enter="actualiser()" />
                                                            <div class="css_btn_recherche_liste" :title="$root.traduction('interface.listes.effectuer_recherche')" @click="actualiser()">
                                                               <i class="fa fa-search" aria-hidden="true"></i>
                                                            </div>
                                                        </div>
                                                    @endif
                                                 @endif
                                                @if(View::hasSection('creation_specifiques_sur_liste'))
                                                    @yield('creation_specifiques_sur_liste')
                                                @elseif((empty($liste_libre->desactiver_creation) || $liste_libre->desactiver_creation == 2) && $creation_possible)
                                                  <template v-if="liste.droits_liste.profil_creation && !($root.intranet) @if($liste_libre->desactiver_creation == 2 && !empty($liste_libre->condition_desactiver_creation)) && !({!! $liste_libre->condition_desactiver_creation !!}) @endif">
                                                    @if(in_array($type_element, \App\Eden\Variables::$documents_gescom))
                                                        <a :href="lien_nouveau_document" class="css_ajouter_element" data-toggle="tooltip" data-placement="top" :title="$root.traduction('interface.listes.nouveau_document')">
                                                          <i class="css_action_icon fa fa-fw fa-plus-square"></i>
                                                        </a>
                                                    @else
                                                        <span class="css_ajouter_element css__lien" @click="creer_dans_liste()" data-toggle="tooltip" data-placement="top" title="Ajouter">
                                                             <i class="css_action_icon fa fa-fw fa-plus-square"></i>
                                                        </span>
                                                    @endif
                                                  </template>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    @if(View::hasSection('actions_specifiques_sur_liste'))
                                        @yield('actions_specifiques_sur_liste')
                                    @endif
                                    <div v-show="liste.chargement_initial_a_effectuer === false">

                @if($liste_libre->afficher_calculs_haut_liste)
                    @include('eden::listes.includes.liste_calculs', ['classe_css' => 'css_calculs_haut_liste'])
                @endif
                <div class="card-body @if($liste_libre->affichage_compact == 1) css_card_body_compact @endif">

					<div class="row">

                        @yield('bloc_superieur_tableau')
                        @stack('bloc_superieur_tableau')

                        <!-- colonne de gauche pour les filtres : vue personnalisée -->
                        @if(View::hasSection('bloc_gauche_liste_'.$type_element))

                            @yield('bloc_gauche_liste_'.$type_element)
                        @endif

                        <div :class="'col-md-'+('{{View::hasSection('bloc_gauche_liste_'.$type_element) ? 'true' : 'false'}}' === 'true' ? 10 : 12)">

                        <div class="alert alert-success" v-html="session.message" v-if="session.message != undefined">
                        </div>
                        <div class="alert alert-danger" v-if="session.erreur != undefined" v-html="session.erreur">
                        </div>
                        <div class="alert alert-danger" v-if="session.erreurs != undefined" v-html="session.erreurs">
                        </div>

                        @if(empty($vue))

                            <div :class="draggable ? '' : 'd-sm-none'" v-if="Array.isArray(liste.colonnes) && liste.colonnes.filter((colonne) => {return colonne.responsive == 1}).length > 0">
                                <div v-for="(ligne, index_ligne) in liste.lignes" :element_id="ligne.element.id" :type_element="type_element" 
                                    :class="'row liste_ligne_mobile ' + (draggable ? 'draggable_element_liste' : '')" :style="elements_deplies_mobile.includes(ligne.element.id) ? '' : 'max-height:100px;overflow-y:hidden'">
                                    <div class="liste_btn_deplier_replier_mobile" @click="elements_deplies_mobile.includes(ligne.element.id) ?
                        	            elements_deplies_mobile.splice(elements_deplies_mobile.indexOf(ligne.element.id),1) : elements_deplies_mobile.push(ligne.element.id)">
                                        <i aria-hidden="true" :class="'fas ' + (elements_deplies_mobile.includes(ligne.element.id) ? 'fa-chevron-up' : 'fa-chevron-down')"></i>
                                    </div>
                                    <div class="col-md-12" v-for="(colonne,index) in liste.colonnes.filter((colonne) => {return colonne.responsive == 1})">
                                        <b v-html="$root.traduction(colonne.index_traduction+'.nom')+' :'"> :</b> 
                                        <span v-html="colonne.type == 'champ' ? ligne[colonne.id].affichage : ligne[colonne.id]"></span>
                                    </div>
                                    <div class="liste_conteneur_options_element_mobile" :style="elements_deplies_mobile.includes(ligne.element.id) ? 'padding: 0 5px 0 0;' : 'position:absolute;'">
                                        <component :is="afficher_options(ligne, true)" :ligne="ligne"></component>
                                    </div>
                                </div>
                            </div>

                            <div v-if="!draggable" :class="Array.isArray(liste.colonnes) && liste.colonnes.filter((colonne) => {return colonne.responsive == 1}).length > 0 ? 'd-none d-sm-block' : ''">
                                @section('contenu_liste_'.$type_element)
                                    @include('eden::listes.includes.liste_tableau_standard')
                                @endsection
                                @yield('contenu_liste_'.$type_element)
							</div>

								<!-- vue sur mesure -->
							@else
								@include($vue)
							@endif

                            <!-- fin col md * pour la liste -->
                        </div>
                            <!-- fin row -->
                        </div>
                            </div>

                            @if(View::hasSection('pagination'))
                                @yield('pagination')
                            @else
										<div class="card-footer" @if($liste_libre->affichage_compact == 1) v-show="liste.options_liste.nombre_pages.length > 1" @endif>
										<nav class='css_nav_pagination_listes'>
										<ul class="pagination css_pagination_perso">
										<!-- <li class="page-item disabled"><a class="page-link" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li> -->

                                        <li class="page-item" v-show="liste.options_liste.page > 1">
                                        <a class="page-link" @click="change_page(1)">@traduction('interface.listes.premiere_page')</a>
                                        </li>

                                        <li :class="{'page-item':true, 'active':(page === liste.options_liste.page)}" v-for="page in pages_affichage">
                                        <a class="page-link" @click="change_page(page)">@{{ page }}</a>
                                        </li>
                                        <li class="page-item" v-show="liste.options_liste.page != liste.options_liste.nombre_pages">
                                        <a class="page-link" @click="change_page(liste.options_liste.nombre_pages)">@traduction('interface.listes.derniere_page')</a>
                                        </li>

										<!-- <li class='page-item'><a class="page-link" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li> -->
										</ul>
										</nav>

										<div class="row">
										    <div class="col-md-4 offset-md-4" v-html="(liste.lignes_selectionnees.length > 0 ? (liste.nb_lignes_selectionnees + ' / ') : '') + liste.nombre_elements" style="text-align: center;"></div>
										</div>

                                    {{-- Les calculs --}}
                                    @if(empty($liste_libre->afficher_calculs_haut_liste))
                                        @include('eden::listes.includes.liste_calculs', ['classe_css' => 'css_calculs_bas_liste'])
                                    @endif
                                    @if(isset($id_rapport) && View::hasSection('indicateurs_liste_'.$id_rapport))
                                        @yield('indicateurs_liste_'.$id_rapport)
                                    @endif
                                    </div>
                                    @endif
                                    </div>
                                    <div v-show="liste.chargement_initial_a_effectuer === true && liste.erreur_ajax === false">
                                        <div class="card-body @if($liste_libre->affichage_compact == 1) css_card_body_compact @endif" style="text-align:center;">
                                            <img style="width: 60px;" src="<?php echo e('eden/images/ajax_loader.gif'); ?>">
                                        </div>
                                    </div>
                                    <div v-show="liste.erreur_ajax !== false">
                                        <div class="card-body @if($liste_libre->affichage_compact == 1) css_card_body_compact @endif" style="text-align:center;">
                                            <div class="alert alert-danger">
                                                <strong v-text="$root.traduction('messages.js.liste_erreur_chargement')"></strong>
                                                <div v-if="mode_parametrage == 1" class="mt-15">
                                                    <span v-text="liste.erreur_ajax"></span>
                                                </div>
                                            </div>

                                            <template v-if="mode_parametrage == 1">
                                                <a href="{{ route('parametrage.liste_libre.index', $id_liste, false) }}" data-toggle="tooltip" data-placement="right" v-text="'Paramétrer liste ' + @if(empty($rapport)) $root.traduction('{{table_libre($type_element)->index_traduction}}','element_pluriel') @else $root.traduction('{{$rapport->index_traduction}}','titre') @endif">
                                                </a>
                                            </template>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if(!in_array($type_element, App\Eden\Variables::$documents_gescom) && empty($liste_libre->formulaire_modale))
                            <div id="popover_ajout_element_{{$id_liste}}" v-if="popover_ajout_element">
                                @include('eden::listes.includes.formulaire_liste')
                            </div>
                        @endif

                        <div id="modales_liste_libre_{{ $id_liste }}">
                            @stack('vue_liste_actions')
                            @if(!in_array($type_element, App\Eden\Variables::$documents_gescom) && !empty($liste_libre->formulaire_modale))
                                <transition name="modal" id="popover_ajout_element_{{$id_liste}}" v-if="popover_ajout_element">
                                  <div class="modal-mask">
                                    <div class="modal-dialog">
                                        @include('eden::listes.includes.formulaire_liste')
                                    </div>
                                  </div>
                                </transition>
                            @endif
                        @yield('modales')
                        @stack('modales')

                        <?php // temps_execution(' => fin vue liste_standard => modale refus approbation'); ?>

                        </div>
                    </div>`,
        props:{

            session : {
                type:Object,
                default:function(){
                    return {};
                }
            },
            informations_pour_fiche : {
                type:Object,
                default:function(){
                    return {};
                }
            },
            seulement_inactif : 0,
            indicateur_source : '',
            mode_parametrage: 0,
            filtres_pour_fiche: {},
            modele_par_defaut: {},
            draggable : false,
            recherche_par_defaut: null,

        },
        data: function(){

            return{

                @yield('donnees_pour_vuejs_data')
                @stack('donnees_pour_vuejs_data')

                id_element: null,
                element_id: '',
                commentaire_refus: '',
                approbation_id: '',
                creation_liste_type: 'vide',
                type_element: '{{$type_element}}',
                @if($type_element == "devis_vente")
                    affichage_acceptation_cgv: '',
                    cgv_non_accepter: false,
                    case_cgv: false,
                    nom_acceptation_cgv: '',
                    prenom_acceptation_cgv: '',
                @endif
                indicateurs : {},
                popover_ajout_element: false,
                id_liste : {{ $id_liste }},
                liste: {
                    chargement_initial_a_effectuer: true,
                    erreur_ajax: false,
                    filtres: null,
                    droits_liste:{},
                    modele_liste_libre: {},
                    options: '',
                    options_mobile: '',
                    actions: '',
                    duplication_en_cours: false,
                    colonnes: [],
                    lignes: [],
                    calculs: [],
                    elements_a_copier: [],
                    type_element: '{{ $type_element }}',
                    type_element_options: '{{ $type_element_options ?? false }}',
                    ids: [],
                    options_liste: {!! collect($options_liste) !!},
                    nombre_elements: "",
                    nombre_elements_nombres: 0,
                    nombre_elements_nombres_sans_filtres: 0,
                    fiche: "{!! $table_libre->fiche !!}",
                    messsage_liste_succes: '',
                    lignes_selectionnees: [],
                    nb_lignes_selectionnees: '',
                    element_id_modification: null,
                    @for($i=1; $i <=3; $i++)
                        sous_total_sur_liste_{{$i}}: '',
                        sous_total_sur_liste_{{$i}}_champ: '',
                    @endfor
                    modif_en_masse: {},
                    orderby_avec_sous_totaux : '',
                    vue_sql : {!! empty($vue_sql) ? 'null' : $vue_sql !!},
                },
                liste_elements_a_dupliquer: [],
                {{$type_element}} : {},
                ligne_modification : null,
                elements_deplies_mobile : [],
            }
        },
        computed:{

            @yield('donnees_pour_vuejs_computed')
            @stack('donnees_pour_vuejs_computed')

            lien_nouveau_document: function(){

                var lien_nouveau_document = "{{ route('document.creer', [$type_element], false) }}";

                var vue_instance = this;

                var filtres_pour_fiche = vue_instance.filtres_pour_fiche;

                if(filtres_pour_fiche !== undefined && Object.values(filtres_pour_fiche).length > 0){

                    var champ = Object.keys(filtres_pour_fiche)[0];

                    var valeur = 0;

                    if(Array.isArray(filtres_pour_fiche[champ]) || typeof filtres_pour_fiche[champ] === 'object')
                        valeur = Object.values(filtres_pour_fiche[champ])[0];
                    else
                        valeur = filtres_pour_fiche[champ];

                    lien_nouveau_document = '/eden/document/{{$type_element}}/avec_element/'+champ+'/'+valeur;
                }

                return lien_nouveau_document;
            },

            pages_affichage : function (){

                var page = this.liste.options_liste.page;
                var nombre_pages = this.liste.options_liste.nombre_pages;

                var pages_affichage = [];

                for(let i = page - 10; i <= page + 10; i++){

                    if(i > 0 && i <= nombre_pages){

                        pages_affichage.push(i);

                    }

                }

                return pages_affichage;

            },

        },
        methods:{

            @yield('donnees_pour_vuejs_methods')
            @stack('donnees_pour_vuejs_methods')

            //Fonction qui permet de serialize comme en php

            toggle_0_1: function(valeur) {

                if(valeur == 1)
                    valeur = 0;
                else
                    valeur = 1;

                return valeur;
            },

            changer_type_formulaire_echange: function(type) {

            this.echange.type = type;

            this.$forceUpdate();
            },

            apporteur_affaire : function() {

            },
        },

        mounted: function() {

            this.initialisation_complete_liste().then(() => {

                @yield('action_a_executer_intitilisation_liste')
		        @stack('action_a_executer_intitilisation_liste')
            });

            @yield('donnees_pour_vuejs_mounted')
            @stack('donnees_pour_vuejs_mounted')

            $('#stack_modales_composants').append($('#modales_liste_libre_{{$id_liste}}'));

            @yield('scripts')
            @stack('scripts')
            
            if(this.$root.moi_extranet) {
                this.liste.nom_acceptation_cgv = "";
                this.liste.prenom_acceptation_cgv = "";
                this.liste.affichage_acceptation_cgv = true;
                this.liste.cgv_non_accepter = false;
            }

        },

        watch: {

            @yield('donnees_pour_vuejs_watch')
            @stack('donnees_pour_vuejs_watch')

        },

         created: function() {
            var vue_instance = this;

            if(this.modele_par_defaut != undefined){

                 this.liste.modele_par_defaut = this.modele_par_defaut;
            }

            @yield('donnees_pour_vuejs_created')
            @stack('donnees_pour_vuejs_created')
        },
    });

        liste_libre_{{$id_liste}}.filter('montant', function (nombre) {

        if (nombre === null || nombre == undefined || nombre == '')
        return '0,00';

        return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: '{!! maquette('devise_application_iso') !!}' }).format(parseFloat(nombre).toFixed(2));

        // return parseFloat(nombre).toFixed(2).replace('.', ',');
        });
</script>
