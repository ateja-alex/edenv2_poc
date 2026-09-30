<style>
    .navbar-nav navbar-sidenav li!hover {

		background: #eee;
	}


		#menu_app {

			position: fixed;
			background: rgba(0, 0, 0, 0.7);
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			z-index: 99999;
			color: white;
			display: none;
		}



		.css_erreurs_champs_obligatoire {
			border:1px solid red;
		}

        .bloc_champ_formulaire{

            display: flex;
            position:relative;
        }


		.css_menu_app_lien span {

			font-size: calc(var(--taille_police) *  30px);
		}

		.css_menu_app_lien {

			font-size: calc(var(--taille_police) *  15px);
			text-align: center;
			margin-bottom: 17px;
			border: 1px solid transparent;
			cursor: pointer;
			padding: 20px;
		}

		.css_menu_app_lien_super_admin {

			color: var(--background_navbar)!important;
            filter: invert(100%);
		}

		.css_menu_app_lien:hover {

			border: 1px solid white;
		}

		.css_menu_titre {

			background: transparent;
			color: var(--background_menus)!important;
			border-bottom: 1px solid #ffffffb0;
			text-align: center;
			margin-bottom: 3px;
			font-size: calc(var(--taille_police) *  35px);

		}

		.bubble {
			position: absolute;
			background: #fea204;
			color: #FFFFFF;
			font-family: Arial;
			font-size: calc(var(--taille_police) *  20px);
			text-align: center;
			width: 409px;
			border-radius: 4px;
			padding: 1rem;
			left: -240px;
			top : 55px;

		}
		.bubble:after {
			content: '';
			position: absolute;
			display: block;
			width: 0;
			z-index: 1;
			border-style: solid;
			border-color: #fea204 transparent;
			border-width: 0 10px 10px;
			top: -10px;
			left: 92%;
			margin-left: -10px;
		}

		.table{
			/*display: block;*/
    		overflow-x: scroll;
    		/*white-space: nowrap;*/
		}

    .iti__flag {background-image: '/eden/vendors/intl-tel-input/img/flags.png';}

    @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
        .iti__flag {background-image: '/eden/vendors/intl-tel-input/img/flags@2x.png';}
    }

    .iti{
        width:100%;
    }

    .css_dropzone_documents_lies {

        /* display: none; */
    }

    .css_ligne_hover:hover {

        background-color: #eee;
    }

    .css_dropzone_documents_lies_hover {

        border: 2px dashed red !important;
        display: block !important;
    }
    .css_dropzone_documents_lies_active {

        display: block !important;
        border: 2px solid red !important;
    }

    li.css_list_item.selected span.badge {
        background-color:GoldenRod;
        color: #fff;
    }
    .css_conteneur_des_listes_drag_and_drop .css_conteneur_liste_drag_and_drop:first-child {
        margin: 0;
        margin-right: 5px;
    }
    .css_conteneur_liste_drag_and_drop {
        margin: 0 10px;
    }
    .css_liste_header_drag_and_drop {
        padding: 5px 0px;
        background: #038D8D;
        color: #fff;
        text-align: center;
        width: 180px;
    }
    .css_list_item {
        display: flex;
        margin: 3px 0;
        justify-content: space-between;
    }
    .css_list_item .badge {
        width: 85%;
        /*border-radius: 0;
        border-bottom:1px solid black;*/
        cursor: pointer;
    }
    .css_btn_more_drag_and_drop {
        background: #038D8D;
        color: #fff;
        font-size: calc(var(--taille_police) *  10px);
        height: 20px;
        width: 20px;
        display: inline-flex;
        justify-content: center;
        align-items: center;
    }
    .js_sticky_document_footer.scroll-to-fixed-fixed {
        background: #fff;
        border:1px solid black;
        padding: 6px 0px;
    }
    .js_sticky_document_footer.scroll-to-fixed-fixed .row {
        margin-bottom: 0;
    }
    .js_sticky_document_footer.scroll-to-fixed-fixed .row .col-sm-6 {
        display: flex;
        align-items: center;
        justify-content: space-around;
    }
    .js_sticky_document_footer.scroll-to-fixed-fixed .hide_sticky {
        display: none !important;
    }
    .js_sticky_document_footer .hide_sticky {
        display: block !important;
    }
    .js_sticky_document_footer.scroll-to-fixed-fixed .show_sticky {
        display: flex !important;
    }
    .js_sticky_document_footer .show_sticky {
        display: none !important;
    }

    :root {
        --card_header: #f8f8f8;
        --card_background: #fff;
        --secondary: #868e96;
        --success: #28a745;
        --info: #17a2b8;
        --warning: #ffc107;
        --danger: #dc3545;
        --light: #f8f9fa;
        --dark: #343a40;
        --taille_police: {{ maquette('taille_police')/100 }};
        --poids_police: {{ maquette('poids_police') }};
        
        @foreach (maquette('couleurs') as $couleur)

            @if($couleur['nom_couleur'] == 'background_navbar')
                --primary: {{ $couleur['valeur'] }};
            @endif

            --{{ $couleur['nom_couleur'] }}: {{ $couleur['valeur'] }};
        @endforeach
    }

    @foreach (maquette('couleurs') as $couleur)

        @if(str_contains($couleur['nom_couleur'], 'highcharts'))
            .{{ $couleur['nom_couleur'] }} {
                stroke: var(--{{ $couleur['nom_couleur'] }});
                fill: var(--{{ $couleur['nom_couleur'] }});
            }

            .{{ $couleur['nom_couleur'] }} text {
                stroke: initial;
            }

            .{{ $couleur['nom_couleur'] }} rect {
                stroke: var(--{{ $couleur['nom_couleur'] }});
                fill: var(--{{ $couleur['nom_couleur'] }});
            }
        @endif
    @endforeach

    .toggle-checkbox {
        position: absolute;
        opacity: 0;
        cursor: pointer;
        height: 0;
        width: 0;
    }

    .toggle-slot {
        position: relative;
        height: 2em;
        width: 6em;
        border: 5px solid #e4e7ec;
        border-radius: 10em;
        background-color: white;
        /* box-shadow: 0px 10px 25px #e4e7ec; */
        transition: background-color 250ms;
    }

    .toggle-checkbox:checked ~ .toggle-slot {
        background-color: #374151;
    }

    .toggle-button {
        transform: translate(3.75em, 0.1em);
        position: absolute;
        height: 1em;
        width: 1em;
        border-radius: 50%;
        background-color: #ffeccf;
        box-shadow: inset 0px 0px 0px 0.75em #ffbb52;
        transition: background-color 250ms, border-color 250ms, transform 500ms cubic-bezier(.26,2,.46,.71);
    }

    .toggle-checkbox:checked ~ .toggle-slot .toggle-button {
        background-color: #485367;
        box-shadow: inset 0px 0px 0px 0.75em white;
        transform: translate(0.75em, 0.1em);
    }

    .sun-icon {
        position: absolute;
        height: 1em;
        width: 1em;
        color: #ffbb52;
    }

    .sun-icon-wrapper {
        position: absolute;
        height: 1em;
        width: 1em;
        opacity: 1;
        transform: translate(0.5em, 0.1em) rotate(15deg);
        transform-origin: 50% 50%;
        transition: opacity 150ms, transform 500ms cubic-bezier(.26,2,.46,.71);
    }

    tr.document_saisie_articles_ligne_selectionee {

        background: #f1febb !important;
    }

    tr.document_ligne_selection {

        background: #eee !important;
    }

    .css_button_document_vente_active {
        color:#fff !important;
        background: #000 !important;
    }

    .toggle-checkbox:checked ~ .toggle-slot .sun-icon-wrapper {
        opacity: 0;
        transform: translate(3em, 2em) rotate(0deg);
    }

    .moon-icon {
        position: absolute;
        height: 1em;
        width: 1em;
        color: white;
    }

    .moon-icon-wrapper {
        position: absolute;
        height: 1em;
        width: 1em;
        opacity: 0;
        transform: translate(11em, 2em) rotate(0deg);
        transform-origin: 50% 50%;
        transition: opacity 150ms, transform 500ms cubic-bezier(.26,2.5,.46,.71);
    }

    .toggle-checkbox:checked ~ .toggle-slot .moon-icon-wrapper {
        opacity: 1;
        transform: translate(3.75em, 0.1em) rotate(-15deg);
    }


    .css_option_choix_sur_formulaire {

        background: #eeeeee;
        text-align: center;
        font-size: calc(var(--taille_police) *  11px);
        cursor: pointer;
        line-height: 15px;
        padding-bottom: 20px;
    }

    .css_option_choix_sur_formulaire.css_option_choix_sur_formulaire_actif, .css_option_choix_sur_formulaire:hover  {

        background: #646464;
        color: white;
    }

    .css_option_choix_sur_formulaire_titre {

        font-size: calc(var(--taille_police) *  20px);
        font-weight: calc(var(--poids_police) *  700);
        padding: 20px 0px;
        display: block;
    }

    .css_modification_valeur_a_la_volee_dans_listes .css_champ_modification_valeur_a_la_volee_dans_listes  {

        display: none;
    }

    /*CSS GESTION TICKET MESSAGE*/

    .paragraphe-ticket{
        background: #ebebeb none repeat scroll 0 0;
        border-radius: 3px;
        color: #646464;
        margin: 0;
        padding: 5px 10px 5px 12px;
        width: 100%;
        margin-top: 5px;
        margin-bottom: 5px;
    }
    /*FIN CSS GESTION TICKET MESSAGE*/


    .estTermine{
        color:#9f8987;
        text-decoration: line-through;
    }

    #menus li:hover, #menus li.active, #menus li.active a,#menus_replier li:hover, #menus_replier li.active, #menus_replier li.active a {

        background: var(--background_menus_hover);
    }

    .liste_onglets > li {
        margin-right: 0;
    }
    .liste_onglets > li > a {
        font-size: calc(var(--taille_police) *  16px);
        text-transform: uppercase;
        border: 1px solid transparent;
    }

    .liste_onglets > li > a.active:hover {
        color: #fff !important;
    }

    .css_nav_tabs_formulaire {

        padding: 8px 15px;
    }

    .css_nav_tabs_formulaire li {
        margin-right: 0;
        margin-bottom: -1px;
    }
    .css_nav_tabs_formulaire li > a {
        font-size: calc(var(--taille_police) *  13px);
        text-transform: uppercase;

        margin-left: 0px;
        border: 1px solid #e8e8e8;
        border-bottom: 0px;
        padding: 6px 21px;

        background: #e8e8e8;
        color: #b9b9b9;
    }

    .css_nav_tabs_formulaire li > a.active, .css_nav_tabs_formulaire li > a:hover {

        margin-left: 1px;
        border: 1px solid #e8e8e8;
        border-bottom: 0px;
        background: rgb(250, 250, 250);
        color: #313131;
    }
    .css_nav_tabs_formulaire .tab-content {

        background: rgb(250, 250, 250);
        padding: 10px 2px;
        margin-top: -2px;
        margin-left: 1px;
        margin-bottom: 5px;
        border: 1px solid #e8e8e8;
    }

    .btn-warning, .btn-danger, .btn-primary, .btn-success, .btn-secondary {

        border: 1px solid white;
        border-radius: 0px;
    }

    .btn-primary {

         background: var(--primary) !important;
         border: solid var(--primary) 1px !important;
     }

    .btn{

        font-weight:calc(var(--poids_police) * 400);
        font-size:inherit;
    }

    .css_parametrage_menu .row.css_inactif {

        background: #eee;
        color: #aaa;
    }

    .css_document_valide {

        background: #dcedcf;
    }

    .css_document_regle, .css_document_accepte {

        background: #71a14d;
        color: white !important;
    }

    .dropdown-menu.show {

        max-height: 400px;
        overflow-y: auto;
    }
    .css_conteneur_card_loading {
      min-height: 159px;
    }
    #loading_card {
      position: absolute;
        height: 200px;
        width: 100%;
        text-align: center;
        background: rgba(0, 0, 0, 0.7);
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        /* display: none; */
        margin: -1.25rem;
    }
    #loading_card img {
      height: 50px;
    }


    #base-content rapport div.js_rapport {
        overflow:auto;
    }

    .css_icone_option_dans_liste {

        padding: 0px !important;
        border: 0px solid white !important;
        background: transparent !important;
        color: #6f6f6f !important;
        width: 25px  !important;
        font-size: calc(var(--taille_police) *  16px);
    }

    .css_icone_option_dans_liste.btn.btn-default, .css_icone_option_dans_liste a {

        color: #6f6f6f !important;
    }

    .css_card_body_compact {

        padding: 0px;
    }
    .css_card_body_compact tr th, .css_card_body_compact tr td {

        border-width: 1px 0px !important;
    }

    /*#### tooltip sur la saisie des documents ###*/
    .css_tooltip {
     display:inline-block;
     position:relative;
     border-bottom:1px dotted #666;
     text-align:left;
    }
    .css_tooltip .css_top {
     /*min-width:200px; */
     top:-20px;
     left:50%;
     transform:translate(-50%, -100%);
     padding:10px 20px;
     color:#444444;
     background-color:#EEEEEE;
     font-weight: calc(var(--poids_police) * 400);
     font-size: calc(var(--taille_police) * 13px);
     border-radius:8px;
     position:absolute;
     z-index:99999999;
     box-sizing:border-box;
     box-shadow:0 1px 8px rgba(0,0,0,0.5);
     display:none;
     width: 400px;
    }
    .css_tooltip:hover .css_top {
     display:block;
    }
    .css_tooltip .css_top i {
     position:absolute;
     top:100%;
     left:50%;
     margin-left:-12px;
     width:24px;
     height:12px;
     overflow:hidden;
    }
    .css_tooltip .css_top i::after {
     content:'';
     position:absolute;
     width:12px;
     height:12px;
     left:50%;
     transform:translate(-50%,-50%) rotate(45deg);
     background-color:#EEEEEE;
     box-shadow:0 1px 8px rgba(0,0,0,0.5);
    }



    .css_toggle_traduction:checked ~ .toggle-slot {

        background-color: #66b336;
    }

    .css_toggle_traduction ~ .toggle-slot .toggle-button {

        background-color: #66b336;
        box-shadow: inset 0px 0px 0px 0.75em #66b336;
    }

    .nav-tabs > li > a {

        padding: 10px;
    }


    .select2-container--default .select2-selection--multiple .select2-selection__choice {

        background-color: #edebeb;
        border: 1px solid #d9d9d9;
        padding: 9px 2px !important;
        margin-left: 5px !important;
        font-size: calc(var(--taille_police) *  13px);
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

    body {
        color: #272727;
    }

    .navbar-nav::-webkit-scrollbar-track {
      background: var(--background_menus);
    }

    .navbar-nav::-webkit-scrollbar-thumb {
      background: var(--background_sous_menus);
    }

    .navbar-nav::-webkit-scrollbar-thumb:hover {
      background: #555;
    }

    #menus::-webkit-scrollbar {
      width: 8px;
    }

    #menus_replier::-webkit-scrollbar {
      width: 5px;
    }

    .css_filtres_actions_et_recherche_liste_libre{

        display: flex;

    }

    .apparition-document-lie-enter-active,
    .apparition-document-lie-leave-active {
        transition: all 0.25s ease-out;
    }

    .apparition-document-lie-enter,
    .apparition-document-lie-leave-to{
        opacity: 0;
    }
    
    .separation_categorie_transformation_possible{
        
        border-bottom: 2px solid rgba(0,0,0,0.05);
        padding-bottom: 5px !important;
        
    }
    
    .separation_categorie_transformation_possible:not(:first-child) {

        padding-top: 5px !important;
        border-top: 2px solid rgba(0, 0, 0, 0.05);

    }
    
    .css_champ_avec_helper{
        
        display: flex;
        
    }
    
    .css_champ_avec_helper > div{
        
        width : 100%;
        
    }
    
    .css_bouton_ajout_sous_formulaire{
        
        display: inline-block;
        padding: 15px;
        margin-top: 10px;
        cursor: pointer;
        box-shadow: inset 0px 0px 15px -10px rgba(0,0,0,0.5);
        border: 1px solid #d2d2d2;
        
    }

    .css_bouton_ajout_sous_formulaire:active{
        
        background: #ffffff;
        
    }
    
    .highcharts-data-table table {
        font-family: "Poppins", sans-serif;
        border-collapse: collapse;
        border-spacing: 0;
        background: white;
        width: auto;
        margin-left: auto;
        margin-right: auto;
        margin-top: 10px;
        font-size: 0.9em;
    }

    .highcharts-data-table th{

        font-weight: calc(var(--poids_police) *  700);

    }

    .highcharts-data-table td, .highcharts-data-table th, .highcharts-data-table caption {
        border: 1px solid silver;
        padding: 0.5em;
    }
    .highcharts-data-table tr:nth-child(even), .highcharts-data-table thead tr {
        background: #f8f8f8;
    }
    .highcharts-data-table tr:hover {
        background: #eff;
    }
    .highcharts-data-table caption {
        font-size: 1.1em;
        font-weight: calc(var(--poids_police) *  700);
    }

    .highcharts-data-table th[scope="col"] {
        text-decoration: underline;
        cursor: pointer;
    }

    .highcharts-contextmenu{

        font-size: 13px;
        background: rgba(255,255,255,0.7);

    }

    .highcharts-menu-item{

        cursor: pointer;

    }

    .highcharts-credits{
        display: none;
    }

    .highcharts-title{
        display: none;
    }

    .colonne-kanban {
        overflow-y: auto;
        border: 1px solid #dedede;
        padding: 5px;
        flex: 1;
        min-width: 250px;
    }

    .colonne-entete-kanban {
        text-align: center;
        border: 1px solid #dedede;
        position: relative;
        flex: 1;
        min-width: 250px;
    }
</style>

@include('eden::templates.css_specifique_projet')
@include('eden::templates.css_responsive_eden')
