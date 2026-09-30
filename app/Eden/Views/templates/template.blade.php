<!DOCTYPE html>


<?php

temps_execution('debut template');

$version_composants = parametre('version_composants');


$utilisateurs_pour_mention = modele('utilisateur')->selectRaw("CONCAT(prenom,' ',nom) as name,CONCAT(prenom,nom) as username, CONCAT('".asset('storage')."/', avatar) as image");

if(!super_admin())
	$utilisateurs_pour_mention->whereNull('super_admin')->orWhere('super_admin',0);

$utilisateurs_pour_mention = $utilisateurs_pour_mention->get();

if(!empty($utilisateurs_pour_mention)){

	foreach ($utilisateurs_pour_mention as &$utilisateur_pour_mention){

		$utilisateur_pour_mention['username'] = retraite_caracteres_speciaux($utilisateur_pour_mention['username']);
	}
}

$favicon = maquette('favicon');
?>

<?php temps_execution('HEAD template : après php initial'); ?>

<html lang="fr-FR">

<head>

	<!-- Initialize metas -->
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="author" content="Ateja" />
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=0" />
	<meta name="robots" content="noindex, nofollow" />


	<base href="{{ URL::to('/') }}/">

	<!-- Include more metas -->
	@yield('metas')

	<!-- Include title and favicon -->
	<title>
		@if($_SERVER['HTTP_HOST'] == fonctionnalite('url_extranet'))
		    {{maquette('nom_application')}}
	    @else
            @hasSection('title')
			    @yield('title')
            @else
			    {{maquette('nom_application')}}
            @endif - Erp
		@endif
	</title>

	@if(empty($favicon))
		<link rel="shortcut icon" href="{{ asset('storage/'.maquette('logo_application_connexion')) }}">
		<link href="{{env('EDEN_CONSOLE_API_URL')}}storage/logo_e_easydev.png" rel="apple-touch-icon" sizes="76x76"/>
		<link href="{{env('EDEN_CONSOLE_API_URL')}}storage/logo_e_easydev.png" rel="apple-touch-icon" sizes="120x120"/>
		<link href="{{env('EDEN_CONSOLE_API_URL')}}storage/logo_e_easydev.png" rel="apple-touch-icon" sizes="152x152"/>
		<link href="{{env('EDEN_CONSOLE_API_URL')}}storage/logo_e_easydev.png" rel="apple-touch-icon" sizes="180x180"/>
	@else
		<link rel="shortcut icon" href="{{ strpos($favicon, 'http') === 0 ? $favicon : asset('storage/'.$favicon) }}">
		<link href="{{ strpos($favicon, 'http') === 0 ? $favicon : asset('storage/'.$favicon) }}" rel="apple-touch-icon" />
	@endif
	@yield('link')
	@stack('link')

	<script>
		var url_base_projet = '{{URL::to('/')}}';
	</script>

	<script type="text/javascript" src="{{ url('eden/vendors/highcharts/code/highcharts.js')}}"></script>
    <script type="text/javascript" src="{{ url('eden/vendors/highcharts/code/js/modules/funnel.js')}}"></script>
    <script type="text/javascript" src="{{ url('eden/vendors/highcharts/code/accessibility.js')}}"></script>
	<script type="text/javascript" src="{{ url('eden/vendors/highcharts/code/js/modules/exporting.js')}}"></script>
	<script type="text/javascript" src="{{ url('eden/vendors/highcharts/code/js/modules/export-data.js')}}"></script>

	<link href="{{ asset('eden/vendors/prime_vue/primevue.min.css') }}" rel="stylesheet">
	<link href="{{ asset('eden/vendors/prime_vue/theme.css') }}" rel="stylesheet">

    <script>
        Highcharts.setOptions({
            lang: {
                months: [
                    'Janvier', 'Février', 'Mars', 'Avril',
                    'Mai', 'Juin', 'Juillet', 'Août',
                    'Septembre', 'Octobre', 'Novembre', 'Décembre'
                ],
                weekdays: [
                    'Dimanche', 'Lundi', 'Mardi', 'Mercredi',
                    'Jeudi', 'Vendredi', 'Samedi'
                ],
                shortMonths:["Jan", "Fév", "Mar", "Avr", "Mai", "Jun", "Jul", "Aoû", "Sep", "Oct", "Nov", "Déc"]
            }
        });
    </script>

	@yield('styles')
	@stack('link_styles')

	@include('eden::templates.scripts_css_eden')

	<style>

		.content-wrapper{

			@if(parametre_utilisateur('type_menu') == 1 || parametre_utilisateur('type_menu') === null)

			margin-left: 225px;

			@else

			margin-left: 43px;

			@endif

		}

	</style>

	@if(\Storage::has('css_specifique_erp.css'))
		<style>
			{!! \Storage::get('css_specifique_erp.css') !!}
		</style>
	@endif

	@if(\Storage::has('css_specifique_extranet.css') && fonctionnalite('url_extranet') != "" && $_SERVER['HTTP_HOST'] == fonctionnalite('url_extranet'))
		<style>
			{!! \Storage::get('css_specifique_extranet.css') !!}
		</style>
	@endif


	<link rel="stylesheet" href="{{ asset('storage/css/css_generer.css') }}?version={{$version_composants}}" type="text/css" media="all" />

</head>

<?php temps_execution('fin HEAD template'); ?>

<body class="fixed-nav @if(!empty(moi_extranet())) body_extranet @endif" id="page-top" style="background-color: #F1F1EF">


	<div id="vue">
		<!-- Ajax loader content -->
		<div id="loading">
			<img src="{{ asset('eden/images/loading.svg') }}" />

			@if(!empty(moi()) && moi()->super_admin == 1)
			<br/>
			<br/>
			<span class="btn btn-primary" onClick="loading(false)">{{traduction('interface.loader.fermer')}}</span>
			@endif
		</div>
		<?php
			/*
			<div id="formulaire_droite_client" style="position: fixed; right: 0; height: 100%; width: 100%; background: rgba(0, 0, 0, 0.7); z-index: 9999; margin-right: -100%">
				<div style="background: white; margin-left: 25%; padding: 25px;">
					<div style="height: 80vh; overflow-y: auto;">
						<div class="container-fluid css_form">
							{!! formulaire('adresse','creation_volee_') !!}
						</div>
					</div>
					<div style="height: 80vh;padding-top: 5px; text-align: right;">
						<span class="btn btn-default">Annuler</span>
						<span class="btn btn-primary">Enregistrer</span>
					</div>
				</div>
			</div>
			*/
			?>
			<!-- Le menu type app -->
			<menus :type_menu="'app'" @if(super_admin()) :superadmin="'1'" @endif maquette_nom_application="{{ maquette('nom_application') }}" :droits_non_acces='{!! collect(session()->get('cache.droits_profils.divers_non_acces')[(!empty(moi_extranet()) ? 'menu_extranet' : 'menu')] ?? []) !!}' ></menus>
			<?php temps_execution('après menus_app'); ?>

			<!-- Include header content -->
			@include('eden::templates.header')

			@include('eden::traduction.donnees_vue')
			@include('eden::templates.chargement_module')
			@include('eden::templates.template_vuejs')

			@include('eden::templates.resultats_recherche')

			<div class="alert alert-success" style="text-align: center; display: none;" id="message_application_succes"></div>

			@include('eden::templates.modales_specifiques')

			@stack('modales')

			<utilisateur-deconnecte></utilisateur-deconnecte>

			<envoi-email></envoi-email>

			<div id="alerte_eden" title="">
				<p>
				</p>
			</div>
			<div id="stack_modales_composants" ></div>

			<!-- Include page content -->
			<div v-cloak>
				@yield('content')

				@if(session()->has('eden.alertes_erp.modales'))
					@foreach(session()->get('eden.alertes_erp.modales') as $modale_alerte)
						@include('eden::modales_alertes.'.$modale_alerte)
					@endforeach
				@endif
			</div>
			{{-- Footer --}}
			
			@if(View::hasSection('footer_sticky'))
				<footer>
					@yield('footer_sticky')
				</footer>
			@endif
		</div>

		<script type="text/javascript" src="{{ url('eden/vendors/jquery/jquery.min.js?v='.date('YmdH'))}}"></script>
		<script type="text/javascript" src="{{ asset('eden/js/jquery-3.6.0.min.js') }}"></script>
		<script src="{{ url('eden/vendors/jquery/jquery-ui.min.js?v='.date('YmdH'))}}"></script>
		<script src="{{ url('eden/js/jquery.ui.touch-punch.min.js?v='.date('YmdH'))}}"></script>
		<script src="{{ url('eden/vendors/select2/js/select2.min.js?v='.date('YmdH'))}}"></script>
		<script type="text/javascript" src="{{ url('eden/vendors/bootstrap/js/bootstrap.bundle.min.js?v='.date('YmdH'))}}"></script>
		<script type="text/javascript" src="{{ url('eden/vendors/jquery-easing/jquery.easing.min.js?v='.date('YmdH'))}}"></script>
        <script type="text/javascript" src="{{ url('eden/vendors/bootstrap-datepicker/js/bootstrap-datepicker.min.js?v='.date('YmdH'))}}"></script>
		<script type="text/javascript" src="{{ url('eden/vendors/bootstrap-datepicker/js/fr-bootstrap-datepicker.min.js?v='.date('YmdH'))}}"></script>


        <script type="text/javascript" src="{{ url('eden/vendors/bower_components/summernote/dist/summernote.min.js?v='.date('YmdH'))}}"></script>
		<script type="text/javascript" src="{{ url('eden/vendors/bootstrap-toggle/js/bootstrap-toggle.min.js?v='.date('YmdH'))}}"></script>

		<script type="text/javascript" src="{{ url('eden/js/typeahead-v2.js?v='.date('YmdH'))}}"></script>

		<script type="text/javascript" src="{{ url('eden/js/eden.js?v='.date('YmdH'))}}"></script>
		<script type="text/javascript" src="{{ url('eden/js/champs.js?v='.date('YmdH'))}}"></script>
		<script type="text/javascript" src="{{ url('eden/js/jquery-scrolltofixed-min.js?v='.date('YmdH'))}}"></script>
		<script type="text/javascript" src="{{ url('eden/vendors/iconify/js/iconify.min.js?v='.date('YmdH'))}}"></script>

		<script type="text/javascript" src="{{ url('eden/vendors/toastr/js/toastr.js')}}"></script>

		<!-- moment -->
		<script type="text/javascript" src="{{ url('eden/js/moment.js?v='.date('YmdH'))}}"></script>

		<!-- style js summernote -->
		<script src="{{ url('eden/vendors/summernote/js/summernote.min.js?v='.date('YmdH'))}}"></script>

		<script src="{{url('eden/vendors/fontawesome-iconpicker/dist/js/fontawesome-iconpicker.min.js')}}"></script>

		<script src="{{url('eden/vendors/intl-tel-input/js/intlTelInput.js')}}"></script>

		<script type="text/javascript" src="{{ url('eden/vendors/atwho/js/jquery.caret.js')}}"></script>
		<script type="text/javascript" src="{{ url('eden/vendors/atwho/js/jquery.atwho.js')}}"></script>
		<link href="{{ url('eden/vendors/atwho/css/jquery.atwho.css') }}" rel="stylesheet">
		@if(request()->vue_dev == 1 || super_admin() || session()->get('vuejs_mode_dev') === 1)
			<script src="{{ asset('eden/js/vue.js') }}"></script>
		@else
			<script src="{{ asset('eden/js/vue.min.js') }}"></script>
		@endif

		<!-- Sortable -->
		<script type="text/javascript" src="{{url('eden/js/vuejs/sortable.min.js')}}"></script>
		<!-- Vue.Draggable -->
		<script type="text/javascript" src="{{url('eden/js/vuejs/draggable.min.js')}}"></script>

		<script src="{{ url('eden/vendors/prime_vue/password.umd.min.js')}}"></script>

		<script type="text/javascript" src="{{url('eden/vendors/vuetify/vuetify.min.js')}}"></script>

		<script type="text/javascript" src="{{url('eden/vendors/v-tooltip/v-tooltip.min.js')}}"></script>

		@stack('script_sortable')

		<!-- Valeurs des listes libres -->
		@if(moi() !== null)
			@if(file_exists(storage_path('app/public/valeurs_champs_listes/utilisateur_'.moi()->id.'.js')))
				<script type="text/javascript" src="{{ url('storage/valeurs_champs_listes/utilisateur_'.moi()->id.'.js?v='.parametre('version_composants'))}}"></script>
			@endif
		@elseif(moi_extranet() !== null)
			@if(file_exists(storage_path('app/public/valeurs_champs_listes/utilisateur_extranet.js')))
				<script type="text/javascript" src="{{ url('storage/valeurs_champs_listes/utilisateur_extranet.js?v='.parametre('version_composants'))}}"></script>
			@endif
		@endif
		@yield('scripts_avant_vue')
		@stack('scripts_avant_vue')

		{{-- cdn lodash --}}
		<script src="{{url('eden/vendors/lodash/js/lodash.min.js')}}"></script>

		<script src="{{url('eden/vendors/signature_pad/signature_pad.umd.min.js?v='.date('YmdH'))}}"></script>

		<script src="{{url('eden/vendors/loader/js/loader.min.js')}}"></script>

		<!-- les composants vue de l'erp -->
		<script type="text/javascript" src="{{ url('eden/js/composants_vue/bloc-fiche.js?v='.date('YmdH'))}}"></script>

		@stack('composants_vue')

		@include('eden::templates.composants')

	<script type="text/javascript">
	var t0 = performance.now()

	function performance_eden(nom, temps_initial) {

		@if(super_admin())
			console.log("Temps après "+nom+" " + (performance.now() - temps_initial) + " ms.");
		@endif
	}
	</script>

	@include('eden::templates.scripts_js_specifiques_clients')

	<script type="text/javascript" src="{{ asset('storage/filtres_vuejs.js') }}?v={{time()}}"></script>
	<script type='text/javascript' src="{{ url('eden/js/scripts_erp/filtres_vuejs_erp.js')}}" id="filtres_vuejs_erp" devise_application_iso="{!! maquette('devise_application_iso') !!}"></script>

	<script type="text/javascript">

		performance_eden('filtres vuejs', t0);

        $( document ).ready(function() {

			var t0_document_ready = performance.now();

			//Gestion des mentions sur les modal
			$('.modal').on('shown.bs.modal',function(){
				$("textarea").mention({
					queryBy: ['name', 'username'],
					users: {!! $utilisateurs_pour_mention !!}
				});
			});

			performance_eden('mention js', t0_document_ready);

        	$('.modal').on('shown.bs.modal', function () {
        		var baseBackdropZIndex = 1040;
        		$('.modal-backdrop.show').each(function (i) {
        			$(this).css('z-index', baseBackdropZIndex + (i * 20));
        		});
        	});

        	$('.modal').on('hide.bs.modal', function () {
        		var $modal = $(this);
        		$modal.css('z-index', '');
        	});
        });

    	$(document).ready(function(){

			var t0_document_ready = performance.now();

			performance_eden('gestion des modales', t0_document_ready);

			$('.js_sticky_document_footer').scrollToFixed({
    			bottom: 0,
    			minWidth:920,
			});

			performance_eden('js_sticky_document_footer', t0_document_ready);
        });

		performance_eden('etape 1', t0);

    	// loading card recherche global
		function loading_card_recherche_global(value) {

			if(value == true) {
				$('#popover-recherche').removeClass("d-none");
				$('.js_conteneur_card_loading').removeClass("d-none");
				$('#table_recherche').addClass('d-none');
				$('#base-content').addClass("d-none");
			} else if (value == false) {
				$('.js_conteneur_card_loading').addClass('d-none');
				$('#table_recherche').removeClass('d-none');
			}
		}

		function js_vider_cache_menu(){

			loading(true);

			$.ajax({
				method: 'GET',
				dataType: 'json',
				url: '{{ URL::to('/eden/cache/vider') }}'
			}).done(function(donnees) {
				loading(false);
				document.location.reload();
			});
		}

		performance_eden("Temps etape 2", t0);

			var vue_instance = new Vue({
				el: '#vue',

				data: {

                    largeur_ecran: window.innerWidth,
					contexte: '',
					@yield('donnees_pour_vuejs_data')
					@stack('donnees_pour_vuejs_data')
					element: {},
					modales: [],
					@if(moi() !== null)
					    moi: {!! collect(moi()) !!},
					@else
					    moi: {},
					@endif
                    @if(moi_extranet() !== null)
                        moi_extranet: {!! collect(moi_extranet()) !!},
                    @else
                        moi_extranet: null,
                    @endif
					mode_parametrage:{{super_admin() || mode_parametrage() ? '1' : '0'}},
					valeurs_listes_libres: (typeof valeurs_listes_libres === 'undefined' ? {} : valeurs_listes_libres),
					valeurs_listes_formatees: (typeof valeurs_listes_formatees === 'undefined' ? {} : valeurs_listes_formatees),
					utilisateurs_pour_mention: {!! $utilisateurs_pour_mention !!},
					aides_utilisateur: false,
			},
			methods: {

				nl2br: function(texte) {

					if (!texte)
						return '';

					return (texte + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + '<br/>' + '$2')
				},

				@include('eden::champs.liste.gestion_affichage_valeur_liste')

				@yield('donnees_pour_vuejs_methods')
				@stack('donnees_pour_vuejs_methods')

                redimensionnement_ecran() {

                    this.largeur_ecran = window.innerWidth
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

				mise_a_jour_action_modales : function(){

					var vue_instance = this;

					vue_instance.$forceUpdate();

					setTimeout(function(){

						$.each(vue_instance.modales, function (cle, modale) {

							var composant = vue_instance.$refs[modale.ref_composant];

							$.each(modale.action, function (cle, nom_action) {

								var div_action = $('#modale_' + modale.id_random + '_fonction_' + nom_action);

								if ($._data(div_action[0], "events") == undefined) {
									div_action.click(function () {

										var action = composant[nom_action];

										action();
									});
								}
							});

							$.each(modale.champ, function (cle, nom_champ) {

								var input_champ = $('#modale_' + modale.id_random + '_champ_' + nom_champ);

								if($._data(input_champ[0], "events")['change'] == undefined ) {
									input_champ.on('change', function () {
										composant[modale.type_element][nom_champ] = input_champ.val();
										vue_instance.$forceUpdate();

									});
								}
							});
						});
					},500);
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

				modele_table_libre: async function(type_element){

					var table_libre = {};

					await $.ajax({
						url : '{{url('eden/parametrage/table_libre/ajax/')}}/'+type_element,
						dataType:'json',
					}).done(function(modele){

						table_libre = modele;
					});

					return table_libre;
				},

				aide_jai_compris: function(index){

					$('.js_bubble_action_sur_devis').hide();

					var vue_instance = this;

					// on enregistre que l'utilisateur à précisé qu'il est ok avec la modale
					$.post({

						url: "{{ route('base_eden.element.creer', ['aide_utilisateur']) }}",
						dataType: "json",
						method: "post",
						data: {
							'utilisateur_id': vue_instance.moi.id,
							'index_aide': index,
						},
					});
				},

				charger_aides_valides_utilisateur: function(){

					@if(empty(moi()) || moi()->aide_contextuelle != 1)
						return;
					@endif

					var vue_instance = this;

					// On charge les aides de l'utilisateur
					$.ajax({

						url: "{{ route('base_eden.aide_contextuelle.retour') }}",
						dataType: "json",
						method: "get",
						data: {},
					}).done(function(aides){

						vue_instance.aides_utilisateur = aides;
					});
				},

				verification_aide_contextuelle: function(id_aide){

					var vue_instane = this;
					var present_array = false;

					if(vue_instane.aides_utilisateur === false)
						return true;

					$.each(vue_instane.aides_utilisateur, function(index, aide_enregistre) {

						if(aide_enregistre.index_aide == id_aide) {

							present_array = true;
							return true;
						}
					});

					return present_array;
				},

				checkIfComponentExists: function(nom) {
					return Object.keys(Object.getPrototypeOf(this.$options.components)).includes(nom);
				},

				copie_siren_nic_siret(event){

					event.preventDefault();
					var clipboard = event.clipboardData,
					text = clipboard.getData('Text');

					var valeur = text.replaceAll(/\s/g,'').substring(0,event.target.maxLength);

					event.target.value = valeur;

					if (event.target.onchange != undefined)
					  event.target.onchange();

					return valeur;
				},

				basename_url : function(url){

					if(url == null)
						return url;
					
					return url.substring(url.lastIndexOf("/") + 1, url.lastIndexOf("."))
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

			updated: function(){

				@yield('donnees_pour_vuejs_updated')
				@stack('donnees_pour_vuejs_updated')
			},

			mounted: async function() {

				var loading_apres_mounted = false;

                this.$nextTick(() => {
                    window.addEventListener('resize', this.redimensionnement_ecran);
                });

				@yield('donnees_pour_vuejs_mounted')
				@stack('donnees_pour_vuejs_mounted')

				loading(loading_apres_mounted);

				this.charger_aides_valides_utilisateur();

				@if(!empty(session('succes')))
					toastr.success("{!! session('succes') !!}");
				@endif

				@if(!empty(session('error')))
					toastr.error("{!! session('error') !!}");
				@endif

				require.config({ paths: { 'vs': 'https://unpkg.com/monaco-editor@latest/min/vs' }});
				window.MonacoEnvironment = { getWorkerUrl: () => proxy };

				let proxy = URL.createObjectURL(new Blob([`
				self.MonacoEnvironment = {
					baseUrl: 'https://unpkg.com/monaco-editor@latest/min/'
				};
				importScripts('https://unpkg.com/monaco-editor@latest/min/vs/base/worker/workerMain.js');
				`], { type: 'text/javascript' }));
				require(["vs/editor/editor.main"], function () {});
			},
			watch: {

				modales: {
					handler: function() {

						this.mise_a_jour_action_modales();
					},
				},

				@stack('donnees_pour_vuejs_watch')
			},

			directives :{
				@yield('donnees_pour_vuejs_directives')
				@stack('donnees_pour_vuejs_directives')
			},
			filters: {

				nom_valeur_liste_formatee:function(valeur,id_liste){

					var nom_valeur_liste = '';

					if(vue_instance == undefined)
						return;

					if(valeur == null)
						valeur = 0;

					var valeurs_liste = vue_instance.$root.valeurs_listes_formatees[id_liste] ?? [];

					if(id_liste == 14 || id_liste == 3)
						valeurs_liste = valeurs_liste.standard;

					for(valeur_liste of Object.values(valeurs_liste)){

						if(valeur_liste.id_valeur == valeur)
							nom_valeur_liste = valeur_liste.valeur;

					}

					return nom_valeur_liste;
				},

				@stack('donnees_pour_vuejs_filter')
			},

			components: {
				'password': password
			},

		});

		performance_eden("Temps etape 4", t0);

	</script>
	<!-- Include scripts -->
	<script type="text/javascript" src="{{ url('eden/js/sb-admin.min.js?v='.date('YmdH'))}}"></script>
	<script type="text/javascript" src="{{ url('eden/js/sticky.js?v='.date('YmdH'))}}"></script>
	<script type="text/javascript" src="{{ url('eden/js/surcharge_highcharts.js?v='.date('YmdH'))}}"></script>

	<script>

		performance_eden("Temps etape 5", t0);

        // Define base URL
        var url_application = "{{ url('/') }}/";

        $('[data-toggle="tooltip_fiche"]').tooltip();

		//Initialisation de l'éditeur
		$('.summernote').summernote({
			height: 250,
			placeholder: '',
			toolbar: [
			['style', ['bold', 'italic', 'underline']],
			['fontsize', ['fontsize']],
			['color', ['color']],
			['para', ['paragraph']],
			]
		});

		$(function () {

			$('#recherche-form').submit(function() {

				return false;
			});

            // On initialise à null la variable de timeout permettant d'éviter
            // des requêtes trop rapprochées
            timeout = null;

            $('#close_recherche').on('click', function() {

            	$('#popover-recherche').addClass("d-none");

            	$('#base-content').removeClass("d-none");
            });
        });

		var onClick_badge_resultat = function(event) {

			if(event.target.classList.contains('badge-success')) {

				event.target.classList.remove('badge-success');
				event.target.classList.add('badge-secondary');

				lignes = $('#table_recherche tbody tr');

				for(ligne in lignes) {

					if(lignes[ligne].dataset.type == event.target.innerHTML) {

						lignes[ligne].classList.add('d-none');
					}
				}

			} else {

				event.target.classList.remove('badge-secondary');
				event.target.classList.add('badge-success');

				lignes = $('#table_recherche tbody tr');

				for(ligne in lignes) {

					if(lignes[ligne].dataset.type == event.target.innerHTML) {

						lignes[ligne].classList.remove('d-none');
					}
				}
			}
		}

        /**
         * Fonction envoyant la requête ajax pour la recherche sur l'ERP
         */
         function envoyer_requete_recherche_erp() {

         	if($('#recherche_globale_sur_appli').val().length >= 3) {

         		$('html, body').animate({ scrollTop : 0 }, 500);

         		var data = $('#recherche-form').serialize();

                 if(vue_instance.recherche_en_cours !== false)
                     vue_instance.recherche_en_cours.abort();

				// On affiche le loader
				loading_card_recherche_global(true);

				vue_instance.recherche_en_cours = $.ajax({

					method: 'POST',
					dataType: 'json',
					data: data,
					url: '{{ URL::to('/eden/recherche') }}'
				}).done(function(donnees) {

                    vue_instance.recherche_en_cours = false;

					vue_instance.resultat_recherche_globale = donnees.resultats;
					vue_instance.types_elements_recherche_globale = donnees.types_elements;
					vue_instance.filtres_affichage_recherche_globale = donnees.affichage;

					loading_card_recherche_global(false);
                });
			}
		}

		performance_eden("Temps etape 6", t0);

	</script>

<!-- gestion des rapports -->
<script type='text/javascript' src="{{ url('eden/js/scripts_erp/filtres_sur_liste.js')}}"></script>

<script type='text/javascript' src="{{ url('eden/js/bootstrap-typeahead.js')}}"></script>
<script type="text/javascript" src="{{ url('eden/js/mention.js')}}"></script>
<script type="text/javascript">
	$("textarea").mention({
		queryBy: ['name', 'username'],
		users: {!! $utilisateurs_pour_mention !!}
	});
</script>

<!-- Include more scripts -->
@yield('scripts')
@stack('scripts')
@yield('scripts_formulaire')

<script>

performance_eden("Temps total", t0);

</script>
</body>

</html>

<?php

temps_execution('fin template');
temps_execution_recapitulatif();

?>
