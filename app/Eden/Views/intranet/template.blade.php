<!DOCTYPE html>
<!--[if lt IE 7 ]><html class="ie ie6" lang="en"> <![endif]-->
<!--[if IE 7 ]><html class="ie ie7" lang="en"> <![endif]-->
<!--[if IE 8 ]><html class="ie ie8" lang="en"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!-->

@php

    $utilisateurs_pour_mention = modele('utilisateur')->selectRaw("CONCAT(prenom,' ',nom) as name,CONCAT(prenom,nom) as username, CONCAT('".asset('storage')."/', avatar) as image");

    if(!super_admin())
        $utilisateurs_pour_mention->whereNull('super_admin')->orWhere('super_admin',0);

    $utilisateurs_pour_mention = $utilisateurs_pour_mention->get();

    if(!empty($utilisateurs_pour_mention)){

        foreach ($utilisateurs_pour_mention as &$utilisateur_pour_mention){

            $utilisateur_pour_mention['username'] = retraite_caracteres_speciaux($utilisateur_pour_mention['username']);
        }
    }

@endphp

<html class="not-ie" lang="fr">
    <!--<![endif]-->

    <head>
        <meta charset="utf-8">
        <meta name="description" content="description" />
        <meta name="keywords" content="keywords"/>
        <meta name="author" content="Ateja" />
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>
        <meta name="robots" content="noindex, nofollow" />

        <title>@yield('titre') - {{traduction('interface.intranet.titre_page')}}</title>
	    <link rel="shortcut icon" href="{{ asset('storage/'.(maquette('favicon') ?? maquette('logo_application_connexion'))) }}">

{{--        <!-- google web font-->--}}
{{--        <link href='http://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,400,300,600,700' rel='stylesheet' type='text/css'>--}}

        <!-- style sheets-->
        <link rel="stylesheet" media="screen" href="{{ asset('eden/configuration_intranet/css/bootstrap.min.css?v=0.1') }}" type="text/css"/>
        <link rel="stylesheet" media="screen" href="{{ asset('eden/configuration_intranet/css/custom.css') }}" type="text/css"/>
        <link rel="stylesheet" media="screen" href="{{ asset('eden/configuration_intranet/css/jquery.mCustomScrollbar.css') }}" type="text/css" />

        <!-- main jquery libraries / others are at the bottom-->
        <script src="{{ asset('eden/configuration_intranet/js/twitterFetcher_v10_min.js') }}" type="text/javascript"></script>
        <script type="text/javascript" src="{{ url('eden/vendors/jquery/jquery.min.js?v='.date('YmdH'))}}"></script>
        <script type="text/javascript" src="{{ asset('eden/js/jquery-3.6.0.min.js') }}"></script>
		<script src="{{ url('eden/vendors/jquery/jquery-ui.min.js?v='.date('YmdH'))}}"></script>
		<script type="text/javascript" src="{{ url('eden/js/typeahead-v2.js?v='.date('YmdH'))}}"></script>
        <script type="text/javascript" src="{{ url('eden/js/eden.js?v='.date('YmdH'))}}"></script>
        <link href="{{ asset('eden/vendors/select2/css/select2.min.css') }}" rel="stylesheet" />
        <script src="{{ asset('eden/vendors/select2/js/select2.min.js') }}"></script>

        <script type="text/javascript" src="{{ asset('eden/vendors/bootstrap/js/bootstrap.bundle.min.js?v='.date('YmdH'))}}"></script>
        <script src="{{ asset('eden/vendors/bootstrap-toggle/js/bootstrap-toggle.min.js') }}"></script>

        <!-- Scrollbar -->
        <script src="{{ asset('eden/configuration_intranet/js/jquery.mCustomScrollbar.concat.min.js') }}"></script>

        <!-- Scripts -->
        <script src="{{ asset('eden/configuration_intranet/js/scripts.js') }}"></script>

        <script type='text/javascript' src="{{ url('eden/js/bootstrap-typeahead.js')}}"></script>
        <script type="text/javascript" src="{{ url('eden/js/mention.js')}}"></script>
        @if(request()->vue_dev == 1)
			<script src="{{ asset('eden/js/vue.min.js') }}"></script>
		@else
			<script src="{{ asset('eden/js/vue.js') }}"></script>
		@endif
        <script type="text/javascript" src="{{ url('eden/vendors/toastr/js/toastr.js')}}"></script>
		<script type="text/javascript" src="{{ url('eden/js/composants_vue/bloc-fiche.js?v='.date('YmdH'))}}"></script>
        <script type="text/javascript" src="{{ asset('storage/filtres_vuejs.js') }}?v={{time()}}"></script>
        <script src="{{url('eden/vendors/lodash/js/lodash.min.js')}}"></script>

        <script src="{{url('eden/vendors/signature_pad/signature_pad.umd.min.js?v='.date('YmdH'))}}"></script>

        <script type="text/javascript" src="{{url('eden/vendors/v-tooltip/v-tooltip.min.js')}}"></script>

        @include('eden::templates.scripts_css_eden')
        @include('eden::templates.template_vuejs')
        @include('eden::templates.chargement_module')

        @if(\Storage::has('css_specifique_erp.css'))
            <style>
                {!! \Storage::get('css_specifique_erp.css') !!}
            </style>
        @endif
        
        <link rel="stylesheet" href="{{ asset('storage/css/css_generer.css') }}" type="text/css" media="all" />
        <link rel="stylesheet" media="screen" href="{{ asset('eden/configuration_intranet/css/style_intranet.css') }}" type="text/css"/>

        @yield('styles')

        <base href="{{ URL::to('/') }}/">

    </head>
    <body class="intranet">

    @include('eden::traduction.donnees_vue')
    <div id="vue">

        @php $version_composants = parametre('version_composants'); @endphp

        <!-- Ajax loader content -->
        <div id="loading">
            <img src="{{ asset('eden/images/loading.svg') }}" />

            @if(empty(moi_extranet()) && moi()->super_admin == 1)
            <br/>
            <br/>
            <span class="btn btn-primary" onClick="loading(false)">{{ traduction('interface.loader.fermer') }}</span>
            @endif
        </div>

        <div id="alerte_eden" title="">
            <p>
            </p>
        </div>

        <utilisateur-deconnecte></utilisateur-deconnecte>

        @stack('modales')

        <div class="contenu_intranet">
            <div class="row">
                <div class="col-sm-12" >

                    @include('eden::intranet.header')

                    @yield('contenu')

                    @stack('section_modules')
                    @yield('section_modules')
                </div>
            </div>
        </div>

    </div>

        <?php temps_execution('avant composants'); ?>

        @stack('javascript_modules_intranet')

        @stack('composants_vue')

        @include('eden::templates.composants')

        @yield('scripts_avant_vue')
        @stack('scripts_avant_vue')

        <?php temps_execution('après composants'); ?>

        <!-- Java Script -->
        <!-- Placed at the end of the document so the pages load faster -->
        <script type="text/javascript" src="{{ url('eden/vendors/bootstrap/js/bootstrap.bundle.min.js?v='.date('YmdH'))}}"></script>
        <script type="text/javascript" src="{{ url('eden/vendors/bootstrap-toggle/js/bootstrap-toggle.min.js?v='.date('YmdH'))}}"></script>

        <script type="text/javascript" src="{{ url('eden/vendors/bootstrap-datepicker/js/bootstrap-datepicker.min.js?v='.date('YmdH'))}}"></script>
		<script type="text/javascript" src="{{ url('eden/vendors/bootstrap-datepicker/js/fr-bootstrap-datepicker.min.js?v='.date('YmdH'))}}"></script>

        <!-- Scrollbar -->
        <script src="{{ asset('eden/configuration_intranet/js/jquery.mCustomScrollbar.concat.min.js') }}"></script>

        <!-- Scripts -->
        <script src="{{ asset('eden/configuration_intranet/js/scripts.js') }}"></script>

        <script type='text/javascript' src="{{ url('eden/js/bootstrap-typeahead.js')}}"></script>

        <link href="{{ url('eden/vendors/select2/css/select2.min.css?v='.date('YmdH'))}}" rel="stylesheet" />
        <script src="{{ url('eden/vendors/select2/js/select2.min.js?v='.date('YmdH'))}}"></script>

        <script type="text/javascript" src="{{ url('eden/js/mention.js')}}"></script>
        <script type="text/javascript" src="{{ url('eden/js/moment.js?v='.date('YmdH'))}}"></script>

        @if(file_exists(storage_path('app/public/valeurs_champs_listes/utilisateur_'.moi()->id.'.js')))
            <script type="text/javascript" src="{{ url('storage/valeurs_champs_listes/utilisateur_'.moi()->id.'.js?v='.parametre('version_composants'))}}"></script>
        @endif

        <script>

                $(document).on('click', function(e) {
                    var target = $(e.target);
                    if(!target.is($('#bouton_toggle_parametrage_eden')) && !target.is($('#toggle_parametrage_eden').find('*').addBack())) {
                        $('#toggle_parametrage_eden').hide('fast');
                    }
                });

                Vue.filter('nl2br', function (value) {

                    if (!value)
                        return '';

                    return (value + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + '<br/>' + '$2')
                });

                Vue.filter('date', function (value) {

                    if (!value)
                        return '';

                    return moment(String(value)).format('DD/MM/YYYY')

                });

                Vue.filter('date_relatif', function (value) {

                    if (!value)
                        return '';

                    moment.lang('fr');

                    return moment(value, "YYYY-MM-DD").calendar();

                });

                Vue.filter('date_relatif_sans_heure', function (value) {

                    if (!value)
                        return '';

                    moment.lang('fr');

                    return moment(value).format("dddd Do MMMM YYYY");

                });

                Vue.filter('datetime', function (value) {

                    if (!value)
                        return '';

                    return moment(String(value)).format('DD/MM/YYYY à MM:mm:ss')

                });

                Vue.filter('time', function (value) {

                    if (!value)
                        return '';

                    return moment(String(value)).format('MM:mm:ss')

                });


                Vue.filter('datetime_relatif', function (value) {

                    if (!value)
                        return '';

                    moment.lang('fr');

                    return moment(value, "YYYY-MM-DD HH:mm:ss").calendar();

                });

                Vue.filter('devises', function (value) {

                    if (!value)
                        return '0,00';

                    return value.toFixed(2);

                });

                Vue.filter('nombre_couleur', function (nombre) {

                    if (!nombre)
                        return '';

                    if(nombre >= 0) {

                        return '<span style="color: #669e24;">'+nombre+'</span>';
                    }
                    else {

                        return '<span style="color: #ed6f56;">'+nombre+'</span>';
                    }
                });

                Vue.filter('montant', function (nombre) {

                    if (nombre === null || nombre == undefined || nombre == '')
                        return '0,00';

                    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: '{!! maquette('devise_application_iso') !!}' }).format(parseFloat(nombre).toFixed(2));

                    // return parseFloat(nombre).toFixed(2).replace('.', ',');
                });




                
                var vue_instance = new Vue({
                    el: '#vue',

                    data: {

                        @yield('donnees_pour_vuejs_data')
                        @stack('donnees_pour_vuejs_data')

                        moi: {!! collect(moi()) !!},
                        utilisateurs_pour_mention: {!! collect($utilisateurs_pour_mention) !!},
                        largeur_ecran: window.innerWidth,
                        cache_formulaires: {},
                        valeurs_listes_libres: (typeof valeurs_listes_libres === 'undefined' ? {} : valeurs_listes_libres),
					    valeurs_listes_formatees: (typeof valeurs_listes_formatees === 'undefined' ? {} : valeurs_listes_formatees),
                        intranet: true,
                },

                methods: {

					montant_depasse_le_plafond: function(ligne) {

						if(this.articles_pour_note_de_frais[ligne.article_id] == undefined)
							return false;

						if(isNaN(this.articles_pour_note_de_frais[ligne.article_id].plafond))
							return false;

						if(this.articles_pour_note_de_frais[ligne.article_id].remboursement_plafonne != 1)
							return false;

						if(this.articles_pour_note_de_frais[ligne.article_id].plafond < ligne.montant_ttc)
							return true;

						return false;
					},

                    modele_par_defaut: async function(type_element){

                        var modele_par_defaut = {};

                        await $.ajax({
                            url : '{{URL::to('eden/element')}}/'+type_element+'/modele_par_defaut',
                            dataType:'json',
                        }).done(function(modele){
                            modele_par_defaut = modele;
                        });

                        return modele_par_defaut;

                    },

                    nl2br: function(texte) {

                        if (!texte)
                            return '';

                        return (texte + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + '<br/>' + '$2')
                    },

                    @include('eden::champs.liste.gestion_affichage_valeur_liste')

                    @yield('donnees_pour_vuejs_methods')
                    @stack('donnees_pour_vuejs_methods')

                    vue_vider_cache(){

                        $.ajax({
                            method: 'GET',
                            dataType: 'json',
                            url: '{{ URL::to('/eden/cache/vider') }}'
                        }).done(function(donnees) {

                            document.location.reload();
                        });

                    },

                    // a priori appelé uniquement depuis les fiches pour le moment
                    creer_element: function(type_element) {

                        $('#modal_ajout_'+type_element).modal('show');

                        this.$emit('creation_element_dans_modale');
                    },



                    // Permet de selectionner un filtre à la fois
                    filtre_modification: function(type_element) {

                        //on reset à false tous les elements
                        var keys = Object.keys(this.filtres_affichage_recherche_globale)

                        keys.forEach(function(element) {
                            vue_instance.filtres_affichage_recherche_globale[element] = false
                        });

                        this.filtres_affichage_recherche_globale[type_element] = true
                    },

                    toggle_0_1: function(valeur) {

                        if(valeur == 1)
                            valeur = 0;
                        else
                            valeur = 1;

                        return valeur;
                    },

                },

                computed: {

                    @yield('donnees_pour_vuejs_computed')
                    @stack('donnees_pour_vuejs_computed')
                },

                created: function() {

                    this.$on('edition_element_dans_modale', function(info) {

                        // on ouvre le modal
                        $('#modal_ajout_'+info.type_element).modal('show');

                        // on va chercher l'élément
                        $.each(info.liste, function(cle, element) {

                            if(element.id == info.id_element) {

                                vue_instance.element = element;
                                return false;
                            }
                        });
                    });

                    @yield('donnees_pour_vuejs_created')
                    @stack('donnees_pour_vuejs_created')

                },

                mounted: async function() {

                    $(document).on('click', '.dropdown .dropdown-menu', function (e) {
                        e.stopPropagation();
                    });

                    var loading_apres_mounted = false;

                    @yield('donnees_pour_vuejs_mounted')
                    @stack('donnees_pour_vuejs_mounted')

                    loading(loading_apres_mounted);

                },
                watch: {

                    @stack('donnees_pour_vuejs_watch')
                },
                filters: {

                    @stack('donnees_pour_vuejs_filter')
                }

            });
        </script>
    </body>
</html>
