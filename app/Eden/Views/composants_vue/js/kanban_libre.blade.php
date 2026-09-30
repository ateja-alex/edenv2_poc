@push('action_a_executer_acualisation_liste')

    this.actualisation_sortable_kanban()

@endpush

@include('eden::listes.js')

<script>
    const kanban_libre = Vue.component('kanban-libre', {

        template: `
            <div class="card mb-3 js_liste" :id="'liste_'+id_liste" :id_liste="id_liste">
                <div :id="'affichage_liste_'+id_liste">
                    <div class="row" v-show="liste.messsage_liste_succes">
                        <div class="col-md-12">
                            <div class="alert alert-success" v-html="liste.messsage_liste_succes"></div>
                        </div>
                    </div>
                    <div class="alert alert-success" v-html="session.message" v-if="session.message != undefined"></div>
                    <div class="alert alert-danger" v-if="session.erreur != undefined" v-html="session.erreur"></div>
                    <div class="alert alert-danger" v-if="session.erreurs != undefined" v-html="session.erreurs"></div>
                    <div class="card-header">
                        <div class="css_flex_header_liste">
                            <div class="css_titre_liste">
                                <div class="dropdown dropdown_hover" style="display: flex!important;align-items: center;cursor: pointer;">
                                    <h4>@include('eden::listes.includes.titre_titre')</h4>
                                </div>
                                <template v-if="mode_parametrage == 1">
                                    <a :href="'/eden/parametrage/liste_libre/'+id_liste" data-toggle="tooltip" data-placement="right" title="Paramétrer la liste" class="css_bouton_modifier_liste_primaire" style="padding: 0;">
                                        <i class="css_action_icon fas fa-cog"></i>
                                    </a>
                                </template>
                            </div>
                            <div class="css_filtres_actions_et_recherche_liste_libre">
                                <filtres ref="filtres" v-if="liste.filtres !== false" :desactiver_filtres="!!liste.modele_liste_libre.desactiver_filtres" :appliquer_recherche_avancee="!liste.modele_liste_libre.desactiver_recherche_avancee" :parametres_recherche_avancee="{type_element: liste.type_element, type : 'liste', id_cible : id_liste, utilisateur_id : $root.moi.id}" :valeurs_filtres="liste.options_liste.filtres" :filtres="liste.filtres"></filtres>
                                <component v-if="liste.actions && liste.type_element" :is="afficher_actions_masse()"></component>
                                <div class="dropdown dropdown_hover bouton_export_liste" v-if="!liste.modele_liste_libre.desactiver_export && liste.modele_liste_libre.affichage_kanban_vertical == 1">
                                    <div class="css_action_icon secondaire" :title="$root.traduction('interface.listes.export')">
                                        <i class="fas fa-external-link-alt"></i>
                                    </div>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        <label class="dropdown-item css__lien" @click="exporter('basique')">@traduction('interface.listes.exporter_colonnes_de_base')</label>
                                        <label class="dropdown-item css__lien" @click="exporter('csv')">@traduction('interface.listes.exporter_colonnes_de_base_csv')</label>
                                        <label class="dropdown-item css__lien" @click="exporter('pdf')">@traduction('interface.listes.exporter_colonnes_de_base_pdf')</label>
                                        <label class="dropdown-item css__lien" v-if="$root.moi.type_utilisateur == 2" @click="exporter('total')">@traduction('interface.listes.exporter_toutes_colonnes')</label>
                                    </div>
                                </div>
                                <div class="css_block_btn_recherche_liste" v-if="!liste.modele_liste_libre.desactiver_recherche">
                                    <input class='css_input_recherche_liste js_input_recherche_liste' :placeholder="$root.traduction('interface.listes.recherche')" :title="$root.traduction('interface.listes.recherche')" style="padding-left: 5px" type="text" name="recherche" v-model="liste.options_liste.recherche" v-on:keyup.enter="actualiser()" />
                                    <div class="css_btn_recherche_liste" :title="$root.traduction('interface.listes.effectuer_recherche')" @click="actualiser()">
                                        <i class="fa fa-search" aria-hidden="true"></i>
                                    </div>
                                </div>
                                <template v-if="(liste.modele_liste_libre.desactiver_creation !== 1) && liste.droits_liste.profil_creation && !($root.intranet)">
                                    <a v-if="documents_gescom.includes(liste.type_element)" :href="'/eden/document/'+liste.type_element+'/creer'" class="css_ajouter_element" data-toggle="tooltip" data-placement="top" :title="$root.traduction('interface.listes.nouveau_document')">
                                        <i class="css_action_icon fa fa-fw fa-plus-square"></i>
                                    </a>
                                    <span v-else class="css_ajouter_element css__lien" @click="creer_dans_liste()" data-toggle="tooltip" data-placement="top" :title="$root.traduction('interface.listes.ajouter')">
                                        <i class="css_action_icon fa fa-fw fa-plus-square"></i>
                                    </span>
                                </template>
                            </div>
                        </div>
                    </div>
                    <div v-show="liste.chargement_initial_a_effectuer === false">
                        <div class="card-body">
                            <div v-if="liste.modele_liste_libre.afficher_calculs_haut_liste">
                                @include('eden::listes.includes.liste_calculs', ['classe_css' => 'css_calculs_haut_liste'])
                            </div>
                            <template v-if="liste.modele_liste_libre.affichage_kanban_vertical == 1">
                                @include('eden::listes.includes.liste_kanban_vertical')
                            </template>
                            <template v-else>
                                @include('eden::listes.includes.liste_kanban')
                            </template>
                        </div>
                        <div class="card-footer">
                            <div style="text-align: center;" v-html="(liste.modele_liste_libre.affichage_kanban_vertical == 1 && liste.lignes_selectionnees.length > 0) ? (liste.nb_lignes_selectionnees + ' / ' + liste.nombre_elements) : liste.nombre_elements"></div>
                            <div v-if="!liste.modele_liste_libre.afficher_calculs_haut_liste">
                                @include('eden::listes.includes.liste_calculs', ['classe_css' => 'css_calculs_bas_liste'])
                            </div>
                        </div>
                    </div>
                    <div v-show="liste.chargement_initial_a_effectuer === true && liste.erreur_ajax === false">
                        <div class="card-body" style="text-align:center;">
                            <img style="width: 60px;" src="/eden/images/ajax_loader.gif">
                        </div>
                    </div>
                    <div v-show="liste.erreur_ajax !== false">
                        <div class="card-body" style="text-align:center;">
                            <div class="alert alert-danger">
                                <strong v-text="$root.traduction('messages.js.liste_erreur_chargement')"></strong>
                                <div v-if="mode_parametrage == 1" class="mt-15">
                                    <span v-text="liste.erreur_ajax"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div :id="'popover_ajout_element_'+id_liste" v-if="popover_ajout_element && !liste.modele_liste_libre.formulaire_modale">
                    @include('eden::listes.includes.formulaire_liste')
                </div>
                <div :id="'modales_kanban_libre_'+id_liste" ref="modales_formulaire">
                    <transition name="modal">
                        <div class="modal-mask" v-if="popover_ajout_element && liste.modele_liste_libre.formulaire_modale">
                            <div class="modal-dialog">
                                @include('eden::listes.includes.formulaire_liste')
                            </div>
                        </div>
                    </transition>
                </div>
            </div>
        `,

        props: {
            id_liste: {
                type: [String, Number],
                required: true,
            },
            session: {
                type: Object,
                default: function(){
                    return {};
                }
            },
            informations_pour_fiche: {
                type: Object,
                default: function(){
                    return {};
                }
            },
            mode_parametrage: {
                default: 0,
            },
            filtres_pour_fiche: {
                type: Object,
                default: function(){
                    return {};
                }
            },
            modele_par_defaut: {
                type: Object,
                default: function(){
                    return {};
                }
            },
            recherche_par_defaut: {
                default: null,
            },
            seulement_inactif: {
                default: 0,
            },
            indicateur_source: {
                default: '',
            },
            kanban_unite: {
                default: '',
            },
            kanban_colonne_somme: {
                default: '',
            },
            kanban_colonne_count: {
                default: false,
            },
            kanban: {
                default: '',
            },
            pleine_hauteur: {
                default: false,
            },
        },

        data: function(){
            return {
                @stack('donnees_pour_vuejs_data')

                type_element: '',
                sections_kanban_fermees: {},
                actualisation_ordre_dans_kanban_en_cours: false,
                id_element: null,
                element_id: '',
                commentaire_refus: '',
                approbation_id: '',
                creation_liste_type: 'vide',
                popover_ajout_element: false,
                ligne_modification: null,
                liste_elements_a_dupliquer: [],
                liste: {
                    chargement_initial_a_effectuer: true,
                    erreur_ajax: false,
                    messsage_liste_succes: '',
                    fiche: '',
                    colonnes: [],
                    lignes: [],
                    calculs: [],
                    ids: [],
                    filtres: [],
                    indicateurs: {},
                    elements_a_copier: [],
                    options_liste: {},
                    liste_libre: {},
                    modele_liste_libre: {},
                    droits_liste: {},
                    options: '',
                    options_mobile: '',
                    actions: '',
                    type_element_options: '',
                    element_id_modification: null,
                    duplication_en_cours: false,
                    vue_sql: null,
                    element_pluriel: '',
                    nombre_elements: '',
                    nombre_elements_nombres: 0,
                    nombre_elements_nombres_sans_filtres: 0,
                    kanban_colonnes: [],
                    kanban_elements_par_colonnes: {},
                    kanban_nombre_par_colonnes: {},
                    kanban_somme_par_colonnes: {},
                    kanban_nombre_elements_affiches: 0,
                    desactiver_checkbox: false,
                    lignes_selectionnees: [],
                    nb_lignes_selectionnees: '',
                    chargement_colonne_en_cours: {},
                    chargement_nouvelles_colonnes_en_cours: undefined,
                },
            }
        },

        computed: {
            @stack('donnees_pour_vuejs_computed')

            kanban_champ: function(){

                return this.liste.options_liste.kanban;
            },

            colonne_groupe: function(){

                return this.liste.colonnes.find(colonne => colonne.type == 'calcul' && colonne.groupements_calcul != null);
            },

            nombre_colonnes_kanban_verticale: function(){

                var total = this.liste.colonnes.reduce((total, colonne) => total + (colonne.groupements_calcul != null ? Math.max(colonne.groupements_calcul.length, 1) : 1), 0);

                if(this.liste.desactiver_checkbox !== true)
                    total++;

                if(this.liste.modele_liste_libre.desactiver_options !== 1)
                    total++;

                return total;
            },

            /**
             *
             * En kanban vertical, chaque catégorie affiche sa propre table indépendante ( pour garder un scroll
             * propre à chacune ), donc le navigateur calcule leurs largeurs de colonnes séparément et rien ne les
             * aligne entre elles. On estime ici, à partir du texte affiché ( toutes catégories confondues, puisque
             * liste.lignes est déjà la liste globale ), la largeur nécessaire par colonne, pour l'appliquer
             * uniformément sur toutes les tables.
             *
             */
            largeur_colonnes_kanban_verticale: function(){

                var largeurs = {};

                this.liste.colonnes.forEach(colonne => {

                    var nom_colonne = (colonne.index_traduction != '' && colonne.index_traduction != null) ? this.$root.traduction(colonne.index_traduction, 'nom') : colonne.nom;

                    // l'entête est en gras/majuscules, donc plus large par caractère que le "0" de référence de l'unité ch
                    var largeur_max = (nom_colonne ?? '').length * 1.3;

                    this.liste.lignes.forEach(ligne => {

                        var cellule = ligne[colonne.id];

                        if(!cellule)
                            return;

                        var contenus = cellule.contenus ?? cellule.affichage?.contenus ?? [cellule.contenu ?? cellule.affichage?.contenu ?? ''];

                        contenus.forEach(contenu => {

                            if(typeof contenu !== 'string')
                                return;

                            var texte = contenu.replace(/<[^>]*>/g, '');

                            if(texte.length > largeur_max)
                                largeur_max = texte.length;
                        });
                    });

                    // marge de sécurité pour le padding des cellules
                    largeurs[colonne.id] = Math.min(Math.max(Math.ceil(largeur_max) + 2, 8), 40) + 'ch';
                });

                return largeurs;
            },
        },

        mounted: function(){

            @stack('donnees_pour_vuejs_mounted')

            $('#stack_modales_composants').append($(this.$refs.modales_formulaire));

            this.$root.$on('liste_'+this.id_liste+'_initialise', () => {
                this.$nextTick(() => this.ajuster_hauteur_kanban());
            });

            if(this.pleine_hauteur){

                this.gestion_redimensionnement_kanban = () => {

                    clearTimeout(this.delai_redimensionnement_kanban);
                    this.delai_redimensionnement_kanban = setTimeout(() => this.ajuster_hauteur_kanban(), 150);
                };

                // window : redimensionnement classique de la fenêtre. visualViewport ( si dispo ) : sur mobile, la
                // barre d'adresse qui se rétracte/réapparaît au scroll change la hauteur visible sans forcément
                // déclencher de 'resize' sur window ( Safari/Chrome mobile ) - c'est l'API prévue pour ce cas
                this.cibles_redimensionnement_kanban = [window, window.visualViewport].filter(Boolean);
                this.cibles_redimensionnement_kanban.forEach(cible => cible.addEventListener('resize', this.gestion_redimensionnement_kanban));
            }

            this.initialisation_complete_liste();
        },

        beforeDestroy: function(){

            if(!this.pleine_hauteur)
                return;

            clearTimeout(this.delai_redimensionnement_kanban);
            this.cibles_redimensionnement_kanban.forEach(cible => cible.removeEventListener('resize', this.gestion_redimensionnement_kanban));
        },

        methods: {
            @stack('donnees_pour_vuejs_methods')

            /**
             *
             * Calé sur le bas de la fenêtre ( - une marge de sécurité ), puis corrigé si la page déborde quand
             * même ( ex: une scrollbar horizontale du kanban, ou un en-tête de filtres plus haut que prévu,
             * grignotent de l'espace vertical qu'on ne peut pas connaître avant le rendu ).
             *
             * La correction est mémorisée d'un calcul à l'autre : repartir de zéro à chaque passage faisait
             * osciller la hauteur ( on rendait les pixels, la page redébordait, on les reprenait, ce qui
             * relançait un évènement de redimensionnement... ) et le scroll en cours dans une colonne se
             * faisait écraser à chaque tour. On ne recalcule donc que si les mesures de départ ont bougé.
             *
             */
            ajuster_hauteur_kanban: async function(){

                if(!this.pleine_hauteur)
                    return;

                const MARGE_SECURITE_KANBAN = 8;
                const MAX_PASSES_CORRECTION = 3;

                let conteneur = this.$refs['liste_elements_'+this.id_liste];

                if(!conteneur)
                    return;

                let taille_ecran = window.visualViewport ? window.visualViewport.height : (window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight);
                let colonnes = conteneur.querySelectorAll('.colonne-kanban');
                let haut_conteneur = conteneur.getBoundingClientRect().top;

                // la scrollbar horizontale du conteneur prend sa place tout en bas, à l'intérieur : les colonnes
                // calées sur le bas de la fenêtre débordent donc de la zone visible de la hauteur de cette
                // scrollbar, et leur dernière vignette se retrouve coupée. On la retire de leur hauteur.
                let styles_conteneur = window.getComputedStyle(conteneur);
                let hauteur_scroll_horizontal = Math.max(0, conteneur.offsetHeight - conteneur.clientHeight - parseFloat(styles_conteneur.borderTopWidth) - parseFloat(styles_conteneur.borderBottomWidth));

                // les évènements de redimensionnement parasites ( apparition/disparition d'une scrollbar, barre
                // d'adresse mobile ) arrivent sans que la place disponible change : on les ignore
                if(this.mesures_kanban !== undefined
                    && this.mesures_kanban.taille_ecran === taille_ecran
                    && this.mesures_kanban.haut_conteneur === haut_conteneur
                    && this.mesures_kanban.nombre_colonnes === colonnes.length
                    && this.mesures_kanban.hauteur_scroll_horizontal === hauteur_scroll_horizontal)
                    return;

                this.mesures_kanban = { taille_ecran: taille_ecran, haut_conteneur: haut_conteneur, nombre_colonnes: colonnes.length, hauteur_scroll_horizontal: hauteur_scroll_horizontal };

                if(this.reduction_kanban === undefined)
                    this.reduction_kanban = 0;

                let appliquer_hauteur = () => {

                    conteneur.style.maxHeight = (taille_ecran - conteneur.getBoundingClientRect().top - MARGE_SECURITE_KANBAN - this.reduction_kanban) + 'px';

                    colonnes.forEach(colonne => {
                        colonne.style.maxHeight = (taille_ecran - colonne.getBoundingClientRect().top - MARGE_SECURITE_KANBAN - this.reduction_kanban - hauteur_scroll_horizontal) + 'px';
                    });
                };

                let debordement_page = () => document.documentElement.scrollHeight - document.documentElement.clientHeight;

                appliquer_hauteur();

                for(let passe = 0; passe < MAX_PASSES_CORRECTION; passe++){

                    await this.$nextTick();

                    let debordement = debordement_page();

                    if(debordement <= 0)
                        break;

                    let reduction_precedente = this.reduction_kanban;

                    this.reduction_kanban += debordement;
                    appliquer_hauteur();

                    await this.$nextTick();

                    // le débordement ne venait pas du kanban ( un autre bloc de la page dépasse ) : on rend la
                    // place qu'on vient de prendre, sinon on le rogne à chaque calcul sans jamais rien régler
                    if(debordement_page() >= debordement){

                        this.reduction_kanban = reduction_precedente;
                        appliquer_hauteur();
                        break;
                    }
                }
            },

            afficher_element_kanban(element){

                return {
                        template: '<div>' + element.element.affichage_kanban + '</div>',
                        data: function(){
                            return{
                                element : element,
                            }
                        },
                    }
            },

            basculer_section_kanban: function(id_valeur) {

                if(this.sections_kanban_fermees[id_valeur]) {
                    this.$delete(this.sections_kanban_fermees, id_valeur);
                } else {
                    this.$set(this.sections_kanban_fermees, id_valeur, true);
                }
            },

            checkbox_selectionner_toutes_les_lignes_groupe: function(event, colonne_id) {

                var coche = event.target.checked;

                var ids_du_groupe = this.liste.lignes.filter(ligne => ligne.element[this.kanban_champ] == colonne_id).map(ligne => ligne.id);

                if(coche) {
                    ids_du_groupe.forEach(id => {
                        if(!this.liste.lignes_selectionnees.includes(id))
                            this.liste.lignes_selectionnees.push(id);
                    });
                } else {
                    this.liste.lignes_selectionnees = this.liste.lignes_selectionnees.filter(id => !ids_du_groupe.includes(id));
                }

                if(this.liste.lignes_selectionnees.length == 0)
                    this.liste.nb_lignes_selectionnees = "";
                else if(this.liste.lignes_selectionnees.length == 1)
                    this.liste.nb_lignes_selectionnees = "1 " + this.$root.traduction('interface.listes.ligne_selectionnee');
                else
                    this.liste.nb_lignes_selectionnees = this.liste.lignes_selectionnees.length + " " + this.$root.traduction('interface.listes.lignes_selectionnees');

                this.calculs_lignes_selectionnes();
            },

            mise_a_jour_compteurs: function() {

                var vue_instance = this;

                $.each(this.liste.kanban_elements_par_colonnes, function(key, value) {

                   var id_colonne = parseInt(key.replace('colonne_', ''));
                   $('#entete_colonne_'+id_colonne+' .js_nombre_elements_par_colonne').html($('#sortable_'+vue_instance.id_liste+'_'+id_colonne).children().length);
               	});
            },

            appliquer_ordre_dans_kanban: function(ordres_par_colonne) {

                var lignes_par_id = {};

                this.liste.lignes.forEach(ligne => lignes_par_id[ligne.element.id] = ligne);

                var lignes = [];

                ordres_par_colonne.forEach(nouvel_ordre => {

                    nouvel_ordre.forEach(element => {

                        if(lignes_par_id[element.id_element] === undefined)
                            return;

                        lignes.push(lignes_par_id[element.id_element]);

                        delete lignes_par_id[element.id_element];
                    });
                });

                this.liste.lignes.forEach(ligne => {

                    if(lignes_par_id[ligne.element.id] !== undefined)
                        lignes.push(ligne);
                });

                this.liste.lignes = lignes;
            },

            actualiser_ordre_dans_kanban: async function(id_element) {

                if(this.actualisation_ordre_dans_kanban_en_cours === true)
                    return;

                this.actualisation_ordre_dans_kanban_en_cours = true;

                loading(true);

                var ordres_par_colonne = [];

                // il faut le faire pour chaque colonne
                for(var index in this.liste.kanban_colonnes) {

                    if(this.liste.kanban_colonnes[index].id_valeur == undefined)
                        continue;

                    var id_valeur = this.liste.kanban_colonnes[index].id_valeur;

                    var liste = $('#sortable_'+this.id_liste+'_'+id_valeur).children();

                    var nouvel_ordre = [];

                    for(var index_element in liste) {

                        var objet = liste[index_element];

                        if(objet.dataset == undefined || objet.dataset.id_element == undefined)
                            continue;

                        nouvel_ordre.push({id_element: objet.dataset.id_element, ordre: index_element});
                    }

                    if(nouvel_ordre.length > 0)
                        ordres_par_colonne.push(nouvel_ordre);
                }

                this.appliquer_ordre_dans_kanban(ordres_par_colonne);

                for(var nouvel_ordre of ordres_par_colonne) {

                    await $.ajax({
                        url: "/eden/element/kanban/ordre_dans_colonne_kanban",
                        type: "post",
                        data: {

                            id_liste: this.id_liste,
                            nouvel_ordre: nouvel_ordre,
                            id_element: id_element,
                        }
                    });
                }

                this.actualisation_ordre_dans_kanban_en_cours = false;

                loading(false);
            },

            /**
             *
             * Chargement progressif des éléments d'un statut ( horizontal et vertical partagent la même mécanique ) :
             * le décalage doit être le nombre d'éléments déjà chargés pour ce statut précis, pas un numéro de page,
             * puisque le tout premier lot affiché vient d'une requête globale non filtrée sur le statut.
             *
             */
            actualisation_auto: function(event, colonne_id) {
                if((event.target.offsetHeight * 1.5) + event.target.scrollTop >= event.target.scrollHeight)
                    this.charger_page_suivante_colonne(colonne_id);
            },

            charger_page_suivante_colonne: function(colonne_id) {

                var parametres = structuredClone(this.parametres_liste);

                let decalage = Object.values(this.liste.kanban_elements_par_colonnes[`colonne_${colonne_id}`]).length;

                delete parametres.recuperer_les_ids_uniquement;

                parametres.kanban_colonnes = [colonne_id];
                parametres.offset_depart_kanban_elements = decalage;

                if(this.liste.kanban_nombre_par_colonnes[`colonne_${colonne_id}`] <= Object.values(this.liste.kanban_elements_par_colonnes[`colonne_${colonne_id}`]).length || this.liste.chargement_colonne_en_cours[colonne_id] !== undefined)
                    return;

                this.liste.chargement_colonne_en_cours[colonne_id] = $.post({

                    url: "/eden/liste/"+this.id_liste,
                    dataType: "json",
                    method: 'POST',
                    data: parametres
                })
                .fail((xhr, status, error) => {

                    this.liste.chargement_colonne_en_cours[colonne_id] = undefined;

                    if(xhr.responseJSON == undefined)
                        return;

                    this.liste.erreur_ajax = xhr.responseJSON.message;

                    toastr.error(this.$root.traduction('interface.listes.erreur_inattendue_survenue'));
                })
                .done(async (donnees) => {
                    if(donnees.retour !== true) {
                        this.liste.chargement_colonne_en_cours[colonne_id] = undefined;
                        await erreur(donnees.retour);
                        return;
                    }

                    this.liste.lignes = this.liste.lignes.concat(donnees.lignes);
                    this.liste.kanban_elements_par_colonnes[`colonne_${colonne_id}`] = this.liste.kanban_elements_par_colonnes[`colonne_${colonne_id}`].concat(donnees.kanban_elements_par_colonnes[`colonne_${colonne_id}`]);
                    this.liste.kanban_nombre_elements_affiches = parseInt(this.liste.kanban_nombre_elements_affiches) + parseInt(donnees.kanban_nombre_elements_affiches);

                    this.liste.chargement_colonne_en_cours[colonne_id] = undefined;
                    this.$forceUpdate();
                });
            },

            /**
             *
             * Chargement de statuts supplémentaires en kanban VERTICAL ( le tableau défile verticalement pour en révéler d'autres ).
             *
             */
            actualisation_auto_colonnes_verticale: function(event) {
                this.actualisation_auto_colonnes_commune(event, 'clientHeight', 'scrollTop', 'scrollHeight');
            },

            /**
             *
             * Chargement de statuts supplémentaires en kanban HORIZONTAL ( le tableau défile horizontalement pour en révéler d'autres ).
             *
             */
            actualisation_auto_colonnes: function(event) {
                this.actualisation_auto_colonnes_commune(event, 'clientWidth', 'scrollLeft', 'scrollWidth');
            },

            actualisation_auto_colonnes_commune: function(event, taille_visible, position_scroll, taille_totale) {


                if(((event.target[taille_visible] * 1.5) + event.target[position_scroll] >= event.target[taille_totale])) {
                    var parametres = structuredClone(this.parametres_liste);

                    delete parametres.recuperer_les_ids_uniquement;

                    parametres.offset_depart_kanban_colonnes = Object.values(this.liste.kanban_colonnes).length;

                    if(parseInt(parametres.colonnes_kanban_total) <= parametres.offset_depart_kanban_colonnes || this.liste.chargement_nouvelles_colonnes_en_cours !== undefined)
                        return;

                    $('#liste_elements_'+this.id_liste).addClass('css_actualisation_ajax_en_cours');

                    this.liste.chargement_nouvelles_colonnes_en_cours = $.post({

                        url: "/eden/liste/"+this.id_liste,
                        dataType: "json",
                        method: 'POST',
                        data: parametres
                    })
                    .fail((xhr, status, error) => {

                        $('#liste_elements_'+this.id_liste).removeClass('css_actualisation_ajax_en_cours');
                        if(xhr.responseJSON == undefined)
                            return;

                        this.liste.erreur_ajax = xhr.responseJSON.message;

                        toastr.error(this.$root.traduction('interface.listes.erreur_inattendue_survenue'));
                    })
                    .done(async (donnees) => {
                        if(donnees.retour !== true) {
                            await erreur(donnees.retour);
                            return;
                        }

                        Object.assign(this.liste.lignes, donnees.lignes);
                        this.$set(this.liste, 'kanban_colonnes', Object.values(this.liste.kanban_colonnes).concat(Object.values(donnees.kanban_colonnes)));
                        this.liste.kanban_nombre_elements_affiches = parseInt(this.liste.kanban_nombre_elements_affiches) + parseInt(donnees.kanban_nombre_elements_affiches);

                        Object.assign(this.liste.kanban_somme_par_colonnes, donnees.kanban_somme_par_colonnes);
                        Object.assign(this.liste.kanban_nombre_par_colonnes, donnees.kanban_nombre_par_colonnes);
                        Object.assign(this.liste.kanban_elements_par_colonnes, donnees.kanban_elements_par_colonnes);

                        this.$forceUpdate();
                        await this.$nextTick();

                        this.ajuster_hauteur_kanban();
                        this.actualisation_sortable_kanban();

                        $('#liste_elements_'+this.id_liste).removeClass('css_actualisation_ajax_en_cours');
                        this.liste.chargement_nouvelles_colonnes_en_cours = undefined;
                    });
                }
            },

            actualisation_sortable_kanban : function () {

                $( ".connectedSortable_"+this.id_liste ).sortable({
                    connectWith: ".connectedSortable_"+this.id_liste,
                    items: '> [data-id_element]',
                    tolerance: 'pointer',
                    helper: 'clone',
                    appendTo: 'body',

                    update : (event,ui) => {

                        var id_element = ui.item[0].dataset.id_element;

                        this.actualiser_ordre_dans_kanban(id_element);
                    },
                    receive : (event,ui) => {

                        var id_element = ui.item[0].dataset.id_element;

                        var id_destination = event.target.dataset.categorie_id;

                        this.actualiser_ordre_dans_kanban(id_element);

                        var ligne_deplacee = this.liste.lignes.find(ligne => ligne.element.id == id_element);

                        if(ligne_deplacee)
                            ligne_deplacee.element[this.kanban_champ] = id_destination;

                        $.ajax({
                            url: "/eden/element/"+this.type_element+"/"+id_element+"/enregistrer",
                            type: "post",
                            data: { [this.kanban_champ]: id_destination+'' }
                        }).done(async (donnees) => {

                            if(donnees.retour !== true){
                                await alerte_eden(donnees.retour);
                                return;
                            }

                            info(this.$root.traduction('messages.js.enregistrement_succes'));

                            this.actualisation_filtres();

                            this.mise_a_jour_compteurs();
                        });
                    },
                    stop : (event,ui) => {

                        $(event.target).sortable('cancel');
                    }

                }).disableSelection();
            },
        },
    });
</script>
