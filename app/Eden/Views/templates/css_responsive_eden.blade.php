<style>
    /* CSS Mobile */
    @media (min-width: 1024px) {

        .css_filtres_liste_mobile{

            display: none;
        }

        .css_filtres_listes{

            display: flex;
        }

        .menu_app {

            display: none !important;
        }

        .eden_navbar{

            gap: 20px;
        }

        .bloc_recherche_globale{
            margin-top: -16px;
            margin-bottom: -16px;
            height: 55px;
        }
        
        .titre_navbar_haut{
            min-width: 200px;
        }

        #navbarResponsive{
            display: flex!important;
        }
    }
    @media (max-width: 1023px) {

        .eden_navbar{

            gap: 5px;
            flex-direction: column;
        }

        #recherche-form{
            height: 30px;
        }

        .titre_navbar_haut{
            display: none !important;
        }

        .titre_navbar_haut_gauche{
            width:100%;
        }

        .fixed-sidebar-left {

            display: none !important;
        }

        .css_filtres_liste_mobile{

            display: block;
        }

        .css_filtres_listes{

            display: none;
        }

        .css_mobile_bouton_filtre_liste {
            background-color: var(--background_navbar);
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .css_mobile_effacer_filtres {

            font-style: italic;
            cursor: pointer;
            margin-bottom: 10px;
        }

        .css_mobile_effacer_filtre {

            position: absolute;
            top: 3px;
            right: 20px;
            cursor: pointer;
        }

        .css_input_recherche_liste{

            width: 100%;
        }

        .css_mobile_dropdown_filtres {

            padding: 0 0 5px 0;
            display: flex;
            width: 100%;
            border-bottom: solid grey 1px;
            justify-content: space-between;
            align-items: center;
        }

        .content-wrapper {
            margin-left: 0;
            margin-top: 55px;
        }
        .css_groupe_bouton_action_navbar_haut {
            margin: auto;
            margin-top: 10px;
            display: none!important;
        }
        #recherche_globale_sur_appli {
            width:100%;
        }
        .bloc_recherche_globale{
            width: 100%;
            margin-top: 5px;
        }
        .card-header h4 {
            width: 100%;
        }
        .card-header div.dropdown:not(.bouton_actions_liste):not(.bouton_export_liste) {
            display: block !important;
        }
        .card-header h4 + .css_rapport_filtre_et_options {
            display: block !important;
            margin: 0;
        }
        .card-header h4 + .css_rapport_filtre_et_options_recherche {
            display: block !important;
            margin: 0;
        }
        h4 a.css_ajouter_element {
            font-size: calc(var(--taille_police) *  13px);
            margin: 8px 0;
            display: flex;
            align-items: center;
            /* Note Thibaut : Pourquoi ???? */
            /*background: {{ maquette('background_menus') }} !important;*/
            color: white;
            text-transform: uppercase;
            font-weight: calc(var(--poids_police) + 700);
            cursor: pointer;
            padding: 2px 8px;
            height: 27px;
        }
        .css_rapport_filtre_et_options .css__lien {
            margin: 0;
            margin-bottom: 8px;
        }
        .css_rapport_filtre_et_options {

        }
        .css_rapport_filtre_et_options_recherche .css__lien {
            margin: 0;
            margin-bottom: 8px;
        }
        .css_rapport_filtre_et_options_recherche {
            margin: 8px 0;
        }
        .css_input_recherche_liste {
            margin-left: 0;
        }
        .liste_onglets {
            flex-direction: column;
        }
        .liste_onglets > li > a {
            font-size: calc(var(--taille_police) *  20px);
            border-radius: 5px !important;
            padding: 5px 5px;
            display: block;
            width: 100%;
            margin: 0px;
        }
        #popover-recherche {
            margin-left: 0 !important;
            margin: 0 15px !important;
            margin-top: 85px !important;
        }
        .modal-footer .btn {
            padding: 8px 14px;
        }
        .css_pagination_perso {
            flex-wrap: wrap;
            justify-content: center;
        }
        .css_block_bouton_creation_document {
            display: flex;
            justify-content: space-between;
        }
        .css_btn_mobile_eden {
            padding: 6px 10px;
        }
        .input-group-addon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .css_gescom_saisie_document_suppression_ligne {
            display: inline-flex;
            justify-content: center;
            align-items: center;
        }
        .css_flex_options_titre_card {
            display: flex;
            margin-left: auto;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
        }
        .css_titre_creer_document {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }
        .css_barre_titre_options {
            margin: auto;
            float: none;
        }
        .css_barre_titre_option {
            height: 35px;
            width: 35px;
            font-size: calc(var(--taille_police) *  20px);
        }
        .css_panel_toggle_parametre_eden {
            /*left: calc(280px - 50%);*/
            left: 11%;
            top: 144px;
        }
        .dropdown_menu_haut_historique{
            margin-left: -200%;
            width: 700%;
        }
        .dropdown_menu_haut_action{
            margin-left: -100%;
            width: 700%;
        }
        #liste_des_articles.table-responsive-sm td, .css_table_recap_articles.table-responsive-sm td {
            padding: 0 5px;
        }
        .css_badge_nb_filtres_actif {
            top: -5px;
            font-size: calc(var(--taille_police) *  12px);
            padding: 5px;
            align-items: center;
            justify-content: center;
            color: #ee7767;
        }
        .css_badge_nb_filtres_actif {
            top: -5px;
            font-size: calc(var(--taille_police) *  9px);
            display: inline-flex;
            height: 20px;
            width: 20px;
            align-items: center;
            justify-content: center;
            color: #ee7767;
        }
        .css_conteneur_chiffres_accueil {
            flex-wrap: wrap;
        }
        .css_conteneur_chiffres_accueil .css_badge_info_chiffres {
            margin-right: 0 !important;
            margin: 3px 0;
        }

        #filtres-popover-recherche{

            display: grid;
        }

        #filtres-popover-recherche span{
            margin-bottom: 5px;
        }

        #filtres-popover-recherche-top{
            display:inline-flex;
        }

        .css_conteneur_popover_filtre{
            margin-bottom:15px;
        }

        #partie_droite_fiche{
            display:none!important;
        }

        #partie_droite_fiche_responsive{
            display:unset!important;
        }

        .content-wrapper{
            margin-right:0!important;
        }

        .css_bouton_modifier_liste_primaire{
            font-size: calc(var(--taille_police) * smaller);
        }

        .btn {
            font-size: calc(var(--taille_police) *  13px);
            max-width: 100%;
            overflow-wrap: break-word;
            display:block;
            white-space: normal;
        }

        #parametrage_module {
            display: block !important;
        }

        .ml_responsive{
            margin-left: 0!important;

        }

        .mr_responsive{
            margin-right: 0rem!important;
        }

        .item_responsive{
            white-space: normal !important;
        }

        .css_envoie_mail{
            height: 200px !important;
        }

        .btn_responsive_footer_document{
            margin-bottom: 2%;
        }

        .eden_navbar{
            align-items: normal;
        }

        .css_zone_notifications_navbar{

            display:block;
        }

        .css_zone_notifications_navbar_panel{

            width: 300px!important;
            right: -200%!important;
        }

        .toggle_bouton_header_responsive{
            display:unset;
        }

        #profil{
            margin-left:unset!important;
        }

        #profil img{
            width: 35px;
            height: 35px;
        }
    }

    /* Bug uniquement sur IOS */
    @supports (-webkit-touch-callout: none) {
      
      .fil_ariane{

            margin-top: -70px!important;
        }
    }
</style>
