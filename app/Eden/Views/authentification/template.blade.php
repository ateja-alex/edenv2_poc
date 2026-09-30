<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="author" content="Ateja" />
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=0">
		<meta name="robots" content="noindex, nofollow" />

		<!-- CSRF Token -->
		<meta name="csrf-token" content="{{ csrf_token() }}">

		@include('eden::traduction.donnees_vue')

		<title>@yield('title')</title>
		<link rel="icon" href="{{ asset('storage/'.(maquette('favicon') ?? maquette('logo_application_connexion'))) }}" />

		@yield('styles')
		@stack('styles')

		@include('eden::templates.scripts_css_eden')
		<!-- Styles -->
		@yield('link')

		
		<link href="{{ asset('eden/css/app.css') }}" rel="stylesheet">
		<link href="{{ asset('eden/vendors/flag-icons/css/flag-icons.min.css') }}" rel="stylesheet">
		<link href="{{ asset('eden/vendors/prime_vue/primevue.min.css') }}" rel="stylesheet">
		<link href="{{ asset('eden/vendors/prime_vue/theme.css') }}" rel="stylesheet">
	</head>


	<body style="height:100vh;@if(!empty(maquette('background_connexion')))background-image: url('{{ asset('storage/'.maquette('background_connexion')) }}'); background-size: cover; background-attachment: fixed; @endif">
		<div id="vue" style="min-height: 100%;padding: 10px;display: flex;align-items: center;justify-content: center;">
			<div class="container">
				<div class="row">
					<div class="col-xs-12 col-md-8 col-md-offset-2">
						<div class="panel panel-default" style="margin: 0;@if(!empty(maquette('background_connexion')))opacity:0.92 @endif">
							<div class="panel-heading" style="text-align: center;">
								<a href="@if(isset($extranet)){{route('extranet.login')}}@else{{ route('login') }}@endif">
									<img src="{{ asset('storage/'.maquette('logo_application_connexion')) }}" style="max-width: 300px; max-height: 200px;" />
								</a>

								<selecteur-langue ref="selecteur_langue" :langues_traduction_erp="langues_traduction_erp" :langue_traduction_erp_defaut="langue_traduction_erp_defaut"></selecteur-langue>
							</div>

							<div class="panel-body">
								@if(session('status'))
									<div class="alert alert-success">
										{{ session('status') }}
									</div>
								@endif

								@yield('formulaire')
							</div>
						</div>
					</div>
				</div>
			</div>


		</div>
		
		@yield('scripts_avant_vue')
		@stack('scripts_avant_vue')

		<script src="{{ asset('eden/js/vue.js') }}"></script>
		<script type="text/javascript" src="{{ url('eden/vendors/jquery/jquery.min.js?v='.date('YmdH'))}}"></script>
		<script src="{{url('eden/vendors/lodash/js/lodash.min.js')}}"></script>
		<script src="{{ url('eden/vendors/prime_vue/password.umd.min.js')}}"></script>

		@stack('composants_vue')
		@include('eden::templates.composants')

		<script>
			var vue_instance = new Vue({
				el: '#vue',
				components: {
					'password': password
				},
				data: () => {
					return {
						@yield('donnees_pour_vuejs_data')
						@stack('donnees_pour_vuejs_data')
						valeurs_listes_libres: (typeof valeurs_listes_libres === 'undefined' ? {} : valeurs_listes_libres),
						valeurs_listes_formatees: (typeof valeurs_listes_formatees === 'undefined' ? {} : valeurs_listes_formatees),
					}
				},
				methods: {

					@yield('donnees_pour_vuejs_methods')
					@stack('donnees_pour_vuejs_methods')
				},
				mounted: function(){

					this.$on('changement_langue_traduction', (traductions) => {

						this.$set(this, 'traductions_valeurs', traductions);
					});

					@yield('donnees_pour_vuejs_mounted')
					@stack('donnees_pour_vuejs_mounted')
				},
				created: function(){
					@yield('donnees_pour_vuejs_created')
					@stack('donnees_pour_vuejs_created')
				},
				watch: {
					@stack('donnees_pour_vuejs_watch')
				},
				computed: {

					@yield('donnees_pour_vuejs_computed')
					@stack('donnees_pour_vuejs_computed')
				},
				updated: function () {

					@yield('donnees_pour_vuejs_updated')
					@stack('donnees_pour_vuejs_updated')
				},
				filters: {

					@stack('donnees_pour_vuejs_filter')
				},
				directives :{

					@yield('donnees_pour_vuejs_directives')
					@stack('donnees_pour_vuejs_directives')
				},
			});
		</script>

		
		<script src="{{url('eden/vendors/fontawesome-iconpicker/dist/js/fontawesome-iconpicker.min.js')}}"></script>
		
		<script src="{{ url('eden/vendors/jquery/jquery-ui.min.js?v='.date('YmdH'))}}"></script>
		<script src="{{ url('eden/js/jquery.ui.touch-punch.min.js?v='.date('YmdH'))}}"></script>
		<script src="{{ url('eden/vendors/select2/js/select2.min.js?v='.date('YmdH'))}}"></script>
		<script type="text/javascript" src="{{ url('eden/vendors/bootstrap/js/bootstrap.bundle.min.js?v='.date('YmdH'))}}"></script>
		<script type="text/javascript" src="{{ url('eden/vendors/jquery-easing/jquery.easing.min.js?v='.date('YmdH'))}}"></script>
		<script src="{{ asset('eden/js/app.js') }}"></script>
		<script src="{{url('eden/vendors/intl-tel-input/js/intlTelInput.js')}}"></script>
		<script type="text/javascript" src="{{ url('eden/vendors/bootstrap-datepicker/js/bootstrap-datepicker.min.js?v='.date('YmdH'))}}"></script>
		<script type="text/javascript" src="{{ url('eden/vendors/bootstrap-datepicker/js/fr-bootstrap-datepicker.min.js?v='.date('YmdH'))}}"></script>
		<script type="text/javascript" src="{{ url('eden/vendors/bootstrap-toggle/js/bootstrap-toggle.min.js?v='.date('YmdH'))}}"></script>
		<script type="text/javascript" src="{{ url('eden/js/typeahead-v2.js?v='.date('YmdH'))}}"></script>
		<script type='text/javascript' src="{{ url('eden/js/bootstrap-typeahead.js')}}"></script>
		<script type="text/javascript" src="{{ url('eden/vendors/toastr/js/toastr.js')}}"></script>
		<script type="text/javascript" src="{{ url('eden/js/eden.js?v='.date('YmdH'))}}"></script>
		<!-- Scripts -->
		

		@if(file_exists(storage_path('app/public/valeurs_champs_listes/utilisateur_extranet_login.js')))
			<script type="text/javascript" src="{{ url('storage/valeurs_champs_listes/utilisateur_extranet_login.js')}}"></script>
		@endif

		@yield('scripts')
	</body>
</html>
