<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="author" content="Ateja" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow" />

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @include('eden::traduction.donnees_vue')

    <title>@yield('title')</title>
    <link rel="icon" href="{{ asset('storage/'.maquette('logo_application_connexion')) }}"/>

    <!-- Styles -->

    <link href="{{ asset('eden/css/extranet.css') }}" rel="stylesheet">
    
    {{--    Style généré par LV --}}
    <style>
        body{
            @if(!empty(maquette('background_connexion')))background-image: url('{{ asset('storage/'.maquette('background_connexion')) }}'); @endif
        }
    </style>
    
    <link href="{{ asset('eden/vendors/flag-icons/css/flag-icons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('eden/vendors/prime_vue/primevue.min.css') }}" rel="stylesheet">
    <link href="{{ asset('eden/vendors/prime_vue/theme.css') }}" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ url('eden/vendors/toastr/css/toastr.css')}}">
    <link rel="stylesheet" href="{{ asset('eden/vendors/font-awesome/css/all.css') }}" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">

    @yield("style")
</head>


<body style="">
<div id="auth">
    <div class="auth-panel">
        <div>
            <div class="auth-logo">
                <a href="{{route('extranet.login')}}">
                    <img src="{{ asset('storage/'.maquette('logo_application_connexion')) }}" class="auth-logo-img"/>
                </a>
            </div>

            <selecteur-langue ref="selecteur_langue" :langues_traduction_erp="langues_traduction_erp" :langue_traduction_erp_defaut="langue_traduction_erp_defaut"></selecteur-langue>

            <div class="auth-form">
                @if(session('status'))
                    <div class="alert alert-success">
                        {{ session('status') }}
                    </div>
                @endif

                @yield('contenu')
            </div>
        </div>
    </div>
</div>

@yield('scripts_avant_vue')
@stack('scripts_avant_vue')

<!-- Scripts -->
<script src="{{ asset('eden/js/vue.js') }}"></script>
<script type="text/javascript" src="{{ url('eden/vendors/jquery/jquery.min.js?v='.date('YmdH'))}}"></script>
<script src="{{url('eden/vendors/lodash/js/lodash.min.js')}}"></script>
<script src="{{ url('eden/vendors/prime_vue/password.umd.min.js')}}"></script>

@stack('composants_vue')
@include('eden::templates.composants')

<script>
    var vue_instance = new Vue({
        el: '#auth',
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
        mounted: function () {

            this.$on('changement_langue_traduction', (traductions) => {

                this.$set(this, 'traductions_valeurs', traductions);
            });

            @yield('donnees_pour_vuejs_mounted')
            @stack('donnees_pour_vuejs_mounted')
        },
        created: function () {
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
        components: {
            'password': password
        },
    });
</script>


<script type="text/javascript" src="{{ url('eden/vendors/bootstrap/js/bootstrap.bundle.min.js?v='.date('YmdH'))}}"></script>
<script src="{{ asset('eden/js/app.js') }}"></script>
<script type="text/javascript" src="{{ url('eden/vendors/bootstrap-datepicker/js/bootstrap-datepicker.min.js?v='.date('YmdH'))}}"></script>
<script type="text/javascript" src="{{ url('eden/vendors/bootstrap-datepicker/js/fr-bootstrap-datepicker.min.js?v='.date('YmdH'))}}"></script>
<script type="text/javascript" src="{{ url('eden/vendors/toastr/js/toastr.js')}}"></script>
<script type="text/javascript" src="{{ url('eden/js/eden.js?v='.date('YmdH'))}}"></script>
<script src="{{ url('eden/vendors/font-awesome/js/font-awesome.js') }}" crossorigin="anonymous"></script>



@if(file_exists(storage_path('app/public/valeurs_champs_listes/utilisateur_extranet_login.js')))
    <script type="text/javascript" src="{{ url('storage/valeurs_champs_listes/app/public/valeurs_champs_listes/utilisateur_extranet_login.js')}}"></script>
@endif

@yield('scripts')
</body>
</html>
