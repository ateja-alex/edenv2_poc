@php
    $version_composants = parametre('version_composants') ?? 1;
@endphp

<link rel="stylesheet" href="{{ url('eden/vendors/bootstrap/css/bootstrap.min.css?v='.$version_composants) }}" type="text/css" media="all" />

<script src="{{ url('eden/vendors/tinymce/tinymce.min.js?v=7')}}"></script>

@if(fonctionnalite('scribens_activation'))
    <script src="{{url('eden/vendors/scribens/tinymce.plugin.js?v=7')}}"></script>
    <script src="{{url('eden/vendors/scribens/scribens_api_'.(moi()->langue ?? 'fr').'.js')}}" data-name="scribens"></script>
@endif

<link rel="stylesheet" href="{{ url('/eden/vendors/tinymce/custom.css?v='.$version_composants)}}">

<!-- Include styles -->

<script src="{{ url('eden/vendors/font-awesome/js/font-awesome.js?v='.$version_composants) }}" crossorigin="anonymous"></script>
<link rel="stylesheet" href="{{ url('eden/vendors/font-awesome/css/all.css?v='.$version_composants) }}" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">

<link rel="stylesheet" href="{{ url('eden/vendors/jquery/jquery-ui.min.css?v='.$version_composants)}}">
<link rel="stylesheet" href="{{ url('eden/vendors/datatables/jquery.dataTables.min.css?v='.$version_composants) }}" type="text/css" media="all" />
<link rel="stylesheet" href="{{ url('eden/vendors/bootstrap-datepicker/css/bootstrap-datepicker.min.css?v='.$version_composants) }}" type="text/css" media="all" />
<link href="{{ url('eden/vendors/select2/css/select2.min.css?v='.date('YmdH'))}}" rel="stylesheet" />
<link rel="stylesheet" href="{{ url('eden/vendors/vuetify/vuetify.min.css?v='.$version_composants) }}" type="text/css" media="all" />
<link rel="stylesheet" href="{{ url('eden/css/sb-admin.css?v='.$version_composants) }}" type="text/css" media="all" />
<link rel="stylesheet" href="{{ url('eden/css/style.css?v='.$version_composants) }}" type="text/css" media="all" />
<link rel="stylesheet" href="{{ url('eden/css/wiki.css?') }}" type="text/css" media="all" />
<link href="{{ url('eden/vendors/animate/css/animate.css?v='.$version_composants) }}" rel="stylesheet" type="text/css">
<link href="{{ url('eden/vendors/bootstrap-toggle/css/bootstrap-toggle.min.css?v='.$version_composants) }}" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="{{ url('eden/vendors/toastr/css/toastr.css')}}">

<link rel="stylesheet" href="{{ url('eden/vendors/bower_components/summernote/dist/summernote.css?v='.$version_composants) }}" type="text/css" media="all" />
<link rel="stylesheet" href="{{ url('eden/css/eden.css?v='.$version_composants) }}" type="text/css" media="all" />
<link href="{{ url('eden/css/fonts.css?v='.$version_composants) }}" rel="stylesheet">
<!-- style css summernote -->
<!-- Include more styles -->
<link rel="stylesheet" href="{{ url('eden/css/template_erp.css?v='.$version_composants) }}" type="text/css" media="all" />
<link rel="stylesheet" href="{{ url('eden/css/elements.css?v='.$version_composants) }}" type="text/css" media="all" />
<link rel="stylesheet" href="{{ url('eden/css/personnalisation_theme.css?v='.$version_composants) }}" type="text/css" media="all" />

<link href="{{url('eden/vendors/fontawesome-iconpicker/dist/css/fontawesome-iconpicker.min.css')}}" rel="stylesheet">

<link rel="stylesheet" href="{{url('eden/vendors/intl-tel-input/css/intlTelInput.css')}}">

<style>
@stack('styles')
</style>

<!-- nouvelle version des cards -->
<link rel="preconnect" href="https://fonts.gstatic.com">

<style>

.card-header h4 {

	color: #494949	;
	font-family: 'Montserrat', sans-serif;
    font-size: calc(var(--taille_police) *  23px);
    font-weight: calc(var(--poids_police) + 700);
}

.card {

	box-shadow: none;
}

.css_form_ligne_titre {

	background: #F1F1EF;
	padding: 10px 5px;
    padding-left: 15px;
    margin-bottom: 10px;
	font-family: 'Montserrat', sans-serif;
}

* {-webkit-font-smoothing: antialiased;}

body {
    color: #272727;
}
<!-- fin nouvelle version des cards -->

</style>

<!-- CSS pour les changements de statut des suivis recette -->
<style type="text/css">
	.changement_statut {
		margin-top: 30px;
		margin-bottom: 30px;
		color: #888;
		text-align: center;
		font-size: calc(var(--taille_police) *  15px);
		position:relative;
	}

	.changement_statut .fa-flag {
		font-size: calc(var(--taille_police) *  32px);
		margin-right: 10px;
	}

	.changement_statut_auteur {
		position: absolute;
		right: 0;
		top: 0;
		text-align: right;
		font-size: calc(var(--taille_police) *  14px);
	}
    
    .css_card_indicateur > div:first-child{
        
        height: 100%;
        
    }
    
    .css_card_indicateur .row{
        
        height: 100%;
        
    }
    
    .css_rapport_indicateur{
        
        overflow: hidden;
        height: 100%;
        
    }

    .css_rapport_indicateur .card-header {

		background: var(--background_navbar);
		color: white;
		text-align: center;
	}
    
    .css_icon_donnees_indicateur{
        
        margin: 15px 0;
        gap: 10px;
        display: flex;
        align-items: center;
        justify-content: flex-start;
        
    }

    .css_rapport_indicateur .card-header h4 {

		color: white;
		text-align: center;
	}

	.css_rapport_indicateur .card-body {

		background: var(--background_menus);
		color: white;
		text-align: center;
        display: flex;
        height: 100%;
        flex-direction: column;
        justify-content: center;
        
	}
</style>

<style>


    .css_modification_valeur_a_la_volee_dans_listes:hover  {

        color: var(--couleur_liens);
    }

    #menus li a {

		@if(maquette('color_menus') != null)
			color: {{ maquette('color_menus') }} !important;
		@endif

    }

    .css_background_couleur_primaire {
      background: var(--background_menus) !important;
    }
    .css_background_couleur_primaire_active.active {
        background: var(--background_menus) !important;
        color: #fff !important;
    }

    .liste_onglets > li > a:hover {
        border: 1px solid var(--background_menus) !important;
        color: var(--background_menus) !important;
    }

    .css_couleur_primaire {
        color: var(--background_menus) !important;
    }

    .page-item.active .page-link {
      background: var(--background_menus);
      border-color: var(--background_menus);
    }

    .pagination > li > a, .pagination > li > span, .css_color_couleur_primaire {

        color: var(--background_menus);
    }

    .pagination > li.active > a, .pagination > li.active > span, .pagination > li.active > a:hover, .pagination > li.active > a:focus, .pagination > li.active > a:active, .pagination > li.active > span:hover, .pagination > li.active > span:focus, .pagination > li.active > span:active {

        background: var(--primary);
        color: white;
    }

    .css__lien, a {

        color: var(--couleur_liens);
    }

    .content-wrapper{

        @if(parametre_utilisateur('type_menu') == 1 || parametre_utilisateur('type_menu') === null)

        margin-left: 250px;

        @else

        margin-left: 43px;

        @endif

    }

	.css_img_champ_file.css_img_champ_file_note_de_frais_scan {

		max-width: 200px;
		max-height: 100px;
	}

	.css_img_champ_file.css_img_champ_file_note_de_frais_scan {

		max-width: 400px;
		max-height: 300px;
	}

    .ui-widget-overlay{

        z-index: 100000!important;

    }

    .ui-dialog-titlebar-close{

        display: none;

    }

    .ui-dialog{

        padding: 0;
        border-radius: 2px;
        border: none!important;
        box-shadow: 0px 5px 14px -1px rgba(0,0,0,0.5);

    }

    .ui-dialog-titlebar{

        text-transform: uppercase;
        border-radius: 0;
        border: none;
        background: var(--background_menus);
        color: white;

    }

    .ui-dialog-content{

        color: var(--primary);
        border: none!important;

    }

    .ui-dialog-buttonpane{

        border: none!important;

    }

    .ui-dialog-buttonset, .ui-dialog-buttonset > button{

        border: none;
        color: white;
        border-radius: 3.5px!important;

    }

    .ui-dialog-buttonset > button {

        background: var(--background_menus);
        margin: 0 2px!important;
        padding: 7px 10px;

    }

    @if(env('APP_ENV') == 'preprod')
        .bloc_selections_elements{
            top:212px;
        }

        .css_tableau_liste_articles_document thead{
            top:172px;
        }

        @media (max-width: 1023px) {

            .css_tableau_liste_articles_document thead{
                top:111px;
            }

            .bloc_selections_elements{
                top:141px;
            }
        }
    @endif


</style>
