@include('eden::composants_vue.js.include.commun_calendrier_planning')
<script>

const planning = Vue.component('planning', {
    template: ` <div>
                    <div class="row planning">
                        <div class="col-md-12" v-show="!modale_planning">
                            <div class="card mb-3">
                                <div class="card-header" style="display: flex;align-items: center;gap: 10px;">
                                    <h4>
                                        @traduction('interface.planning.planning')
                                    </h4>
                                    <div style="display: flex;align-items: center;gap: 20px;">
                                        <div style="display: flex;align-items: center;gap: 5px;">
                                            <span class="css_action_icon secondaire" @click="semaine_voulue = dates.entete.semaine_actuelle;actualisation();" :title="$root.traduction('composant.affichage_calendrier.aujourdhui')"><i class="fas fa-calendar-day"></i></span>
                                            @include('eden::composants_vue.js.include.planning.boutons_impression')
                                        </div>
                                        <span :class="'css_action_icon '+(selection_tache ? 'primaire' : 'secondaire')" @click="selection_tache = !selection_tache;selection_tache_ids = []" 
                                            :title="$root.traduction('composant.affichage_calendrier.selection')">
                                            <i class="fas fa-hand-pointer"></i>
                                        </span>
                                    </div>
                                    <div id="entete" v-if="chargement_initialisation === false">
                                        @include('eden::composants_vue.js.include.planning.entete')
                                    </div>
                                </div>
                                <div class="barre_actions_selection" v-if="selection_tache">
                                    <span class="compteur_selection">
                                        <i class="fas fa-check-square"></i>
                                        @{{ selection_tache_ids.length }} @traduction('composant.planning.taches_selectionnees')
                                    </span>
                                    <div class="actions_selection" v-if="selection_tache_ids.length > 0">
                                        <button type="button" class="btn_action_selection" @click="modifier_taches_selectionnees()">
                                            <i class="fas fa-edit"></i> @traduction('composant.planning.modifier')
                                        </button>
                                        <button type="button" class="btn_action_selection danger" @click="supprimer_taches_selectionnees()">
                                            <i class="fas fa-trash"></i> @traduction('composant.planning.supprimer')
                                        </button>
                                    </div>
                                    <button type="button" class="btn_action_selection neutre" style="margin-left:auto;" @click="selection_tache_ids = taches.map(tache => tache.id)">
                                        <i class="fas fa-check"></i> @traduction('composant.planning.tout_selectionner')
                                    </button>
                                    <button type="button" class="btn_action_selection neutre" @click="selection_tache_ids = []">
                                        <i class="fas fa-times"></i> @traduction('composant.planning.tout_deselectionner')
                                    </button>
                                    <button type="button" class="btn_action_selection neutre" @click="selection_tache = false; selection_tache_ids = []">
                                        <i class="fas fa-ban"></i> @traduction('interface.modales.annuler')
                                    </button>
                                </div>
                                <div class="card-body">
                                    <template v-if="chargement_initialisation === false">
                                        @include('eden::composants_vue.js.include.planning.taches')
                                    </template>
                                    <template v-else>
                                        <div style="display: flex;justify-content: center;">
                                            <img src="/eden/images/ajax_loader.gif" style="width: 75px;"/>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12" v-if="modale_planning">
                            <div class="card mb-3">
                                <div class="card-header" style="display: flex;align-items: center;justify-content: space-between;">
                                    <h5 class="modal-title">
                                        @traduction('interface.planning.planning')
                                    </h5>

                                    <div class="save-button-container">
                                        <template v-if="!lecture_seule">
                                            <template v-if="tache !== null && tache.parent_id > 0">
                                                <span class="css_ajouter_element ml-auto" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-placement="top" :title="$root.traduction('interface.enregistrer')">
                                                    <i class="css_action_icon secondaire fa fa-fw fa-save"></i>
                                                </span>
                                                <div class="dropdown-menu">
                                                    <div class="dropdown-save">
                                                        <span class="dropdown-item" @click="enregistrer_tache()">@traduction('composant.affichage_calendrier.cet_evenement')</span>
                                                        <span class="dropdown-item" @click="enregistrer_tache(1)" v-if="$refs.formulaire != undefined && $refs.formulaire.$refs.formulaire.participants_modifies?.() !== true">@traduction('composant.affichage_calendrier.evenements_suivants')</span>
                                                        <span class="dropdown-item" @click="enregistrer_tache(2)">@traduction('composant.affichage_calendrier.toute_la_serie')</span>
                                                    </div>
                                                </div>
                                            </template>
                                            <template v-else class="conteneur_boutons_enregistrement">
                                                <span @click="enregistrer_tache()" class="css_ajouter_element ml-auto" data-toggle="tooltip" data-placement="top" :title="$root.traduction('interface.enregistrer')">
                                                    <i class="css_action_icon secondaire fa fa-fw fa-save"></i>
                                                </span>

                                                <template v-if="tache !== null && !tache.exception_recurrence">
                                                    <span class="css_ajouter_element bouton_dropdown_enregistrer" style="padding: 0" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <i class="css_action_icon secondaire fas fa-angle-down"></i>
                                                    </span>
                                                    <div class="dropdown-menu">
                                                        <span class="dropdown-item" @click="enregistrer_tache(null, 1)">
                                                            @traduction('interface.listes.enregistrer_et_nouveau')
                                                        </span>
                                                        <span class="dropdown-item" @click="enregistrer_tache(null, 2)">
                                                            @traduction('interface.listes.enregistrer_et_dupliquer')
                                                        </span>
                                                    </div>
                                                </template>
                                            </template>
                                        </template>
                                        <button type="button" class="close" @click="modale_planning = false" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body css_form">
                                    <formulaire ref="formulaire"
                                                nom_formulaire="tache"
                                                :dates="dates"
                                                :rendez_vous="true"
                                                :equipes_d_utilisateurs="utilisateurs_par_equipe">
                                    </formulaire>
                                </div>
                                <div class="modal-footer" v-if="!lecture_seule">
                                    <button type="button" class="btn btn-secondary" @click="modale_planning = false">@traduction('interface.modales.fermer')</button>
                                    <div class="dropdown" v-if="tache !== null && tache.parent_id > 0">
                                        <div class="btn btn-danger" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">@traduction('interface.modales.supprimer')</div>
                                        <div class="dropdown-menu">
                                            <div class="dropdown-save">
                                                <span class="dropdown-item" @click="supprimer_tache()">@traduction('composant.affichage_calendrier.cet_evenement')</span>
                                                <span class="dropdown-item" @click="supprimer_recurrence()">@traduction('composant.affichage_calendrier.evenements_suivants')</span>
                                                <span class="dropdown-item" @click="supprimer_recurrence(1)">@traduction('composant.affichage_calendrier.toute_la_serie')</span>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-danger" v-else-if="tache.id > 0" @click="supprimer_tache()">@traduction('interface.modales.supprimer')</button>
                                    <div v-else>

                                        @include('eden::composants_vue.js.include.tache.boutons_statut_participants')
                                    </div>
                                    
                                    <!-- Bouton Afficher -->
                                    @if(table_libre('tache')->fiche === 1)
                                        <a :href="'/eden/fiche/tache/'+tache.id" class="btn btn-primary" v-if="tache.id != undefined && tache.id != false">@traduction('interface.modales.afficher')</a>
                                    @endif

                                    <div class="dropdown" v-if="tache !== null && tache.parent_id > 0">
                                        <div class="btn btn-primary" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">@traduction('interface.modales.enregistrer')</div>
                                        <div class="dropdown-menu">
                                            <div class="dropdown-save">
                                                <span class="dropdown-item" @click="enregistrer_tache()">@traduction('composant.affichage_calendrier.cet_evenement')</span>
                                                <span class="dropdown-item" @click="enregistrer_tache(1)" v-if="$refs.formulaire != undefined && $refs.formulaire.$refs.formulaire.participants_modifies?.() !== true">@traduction('composant.affichage_calendrier.evenements_suivants')</span>
                                                <span class="dropdown-item" @click="enregistrer_tache(2)">@traduction('composant.affichage_calendrier.toute_la_serie')</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else class="conteneur_boutons_enregistrement">
                                        <button type="button" class="btn btn-primary css_btn_responsive" @click="enregistrer_tache()">
                                            @traduction('interface.listes.enregistrer')
                                        </button>
                                        <template v-if="tache !== null && !tache.exception_recurrence">
                                            <span class="btn btn-primary css_btn_responsive bouton_dropdown_enregistrer" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fas fa-angle-down"></i>
                                            </span>
                                            <div class="dropdown-menu">
                                                <span class="dropdown-item" @click="enregistrer_tache(null, 1)">@traduction('interface.listes.enregistrer_et_nouveau')</span>
                                                <span class="dropdown-item" @click="enregistrer_tache(null, 2)">@traduction('interface.listes.enregistrer_et_dupliquer')</span>
                                            </div>
                                        </template>
                                    </div>
                                    <slot name="boutons_formulaire_tache" :tache="tache"></slot>
                                </div>
                            </div>
                        </div>
                    </div>
                    <template v-if="modale_formulaire_utilisateur">
                        <transition name="modal" >
                            <div class="modal-mask">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" v-html="utilisateur_affiche.chaine_affichage"></h5>
                                            <button type="button" class="close" @click="modale_formulaire_utilisateur = false" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body css_form js_selection_element" >
                                            <formulaire ref="formulaire_utilisateur" nom_formulaire="utilisateur" contexte="formulaire_planning_"></formulaire>
                                        </div>
                                        <div class="modal-footer" v-if="$root.moi.type_utilisateur === 1 || $root.moi.type_utilisateur === 2">
                                            <button type="button" class="btn btn-primary" @click="enregistrer_utilisateur()">@traduction('interface.modales.enregistrer')</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </transition>
                    </template>
                    <modification-en-masse
                        ref="modification_en_masse"
                        :ids_elements="selection_tache_ids"
                        type_element="tache"
                        @modification_terminee="apres_modification_en_masse()">
                    </modification-en-masse>
            </div>
        `,
        props:{
            lecture_seule : false,
            filtres_tache : {
                type:Object,
                default:function(){

                    return {};
                }
            },
            droppable:false,
        },
        data: function(){

            return {

                dates : {},
                utilisateurs : {},
                composants_tooltip: {},
                taches_par_utilisateur : {},
                nombre_de_semaines : 0,
                filtres_planning: [],
                filtre_utilisateurs : [],
                filtre_actif : false,
                modale_planning : false,
                equipes : {},
                chargement_initialisation : true,
                cle_formulaire: 0,
                tache_affichage_options: null,
                utilisateurs_affiches: [],
                semaine_voulue: null,
                modale_formulaire_utilisateur : false,
                utilisateur_affiche:{},
                type_taches_affichees: 'les_deux',
                selection_tache : false,
                selection_tache_ids : [],

                @yield('donnees_pour_vuejs_data')
                @stack('donnees_pour_vuejs_data')

            }
        },

        computed:{

            @yield('donnees_pour_vuejs_computed')
            @stack('donnees_pour_vuejs_computed')

            tache : function(){

                this.cle_formulaire;

                if(this.$refs.formulaire != undefined)
                    return this.$refs.formulaire.element;

                return {};
            },

            parametres_taches : function(){

                return {
                    utilisateurs_id : this.filtre_utilisateurs,
                    nombre_de_semaines : this.nombre_de_semaines,
                    semaine_voulue : this.semaine_voulue,
                    filtres_tache : this.filtres_tache,
                    type_taches_affichees : this.type_taches_affichees
                };
            },


            utilisateurs_par_equipe : function(){

                var utilisateurs_par_equipe = {};

                for(utilisateur of this.utilisateurs_affiches){

                    var index = utilisateur.equipe > 0 ? utilisateur.equipe : 'non_attribue';

                    var couleur_fond = '#ddd';

                    if(index != 'non_attribue' && this.equipes[index] != undefined && this.equipes[index].couleur_fond != null && this.equipes[index].couleur_fond != '')
                        couleur_fond = this.equipes[index].couleur_fond;

                    if(utilisateurs_par_equipe[index] == undefined)
                        utilisateurs_par_equipe[index] = {
                            couleur_fond : couleur_fond,
                            utilisateurs : [],
                        };

                    utilisateurs_par_equipe[index].utilisateurs.push(utilisateur);


                }

                return utilisateurs_par_equipe;
            },

            taches : function(){
                return Object.values(this.taches_par_utilisateur).map(taches_par_date => Object.values(taches_par_date).flat()).flat();
            },

        },

        methods:{

            initialisation : function(){

                var initialisation = true;

                $.post({
                    url : '{{  route('planning.initialisation', [], false) }}',
                    dataType: "json",
                    data : {
                        filtres_tache : this.filtres_tache,
                    }
                }).done((donnees) => {

                    for(cle of Object.keys(donnees)){
                        this.$set(this, cle, donnees[cle]);
                    }

                    this.calcul_utilisateurs_affiches();

                    @yield('action_a_executer_suite_actualisation')
                    @stack('action_a_executer_suite_actualisation')

                    this.$nextTick(() => {

                        this.gestion_droppable();

                        @yield('action_a_executer_suite_actualisation_timeout')
                        @stack('action_a_executer_suite_actualisation_timeout')
                    });

                    this.chargement_initialisation = false;
                });
            },

            actualisation_affichage: function(){

                if(this.modale_planning)
                    this.modale_planning = false;

                this.actualisation();
            },

            actualisation : async function(){

                var parametres = this.parametres_taches;

                parametres.filtres = this.valeurs_filtres;

                loading(true);

                await $.post({
                    url : '{{  route('planning.actualisation', [], false) }}',
                    dataType: "json",
                    data : this.parametres_taches,
                }).done((donnees) => {

                    for(cle of Object.keys(donnees)){
                        this.$set(this, cle, donnees[cle]);
                    }

                    this.calcul_utilisateurs_affiches();

                    @yield('action_a_executer_suite_actualisation')
                    @stack('action_a_executer_suite_actualisation')

                    this.$nextTick(() => {
                        this.gestion_droppable();

                        @yield('action_a_executer_suite_actualisation_timeout')
                        @stack('action_a_executer_suite_actualisation_timeout')
                    });

                    loading(false);
                });
            },

            calcul_utilisateurs_affiches : function(){

                var utilisateurs_affiches = [];

                if(this.filtre_actif === false){
                    utilisateurs_affiches = Object.values(this.utilisateurs);
                }
                else{

                    for(utilisateur_id of this.filtre_utilisateurs){

                        if(this.utilisateurs[utilisateur_id] != undefined)
                            utilisateurs_affiches.push(this.utilisateurs[utilisateur_id]);
                    }
                }

                this.utilisateurs_affiches = this.ordonne_utilisateurs(utilisateurs_affiches);
            },

            creer_tache: function(utilisateur_id, date,moment = false) {

                var vue_instance = this;

                if(vue_instance.lecture_seule)
                    return false;

                vue_instance.$once('formulaire_charger',() => {

                    if(vue_instance.$refs.formulaire.element.affectations != undefined)
                        vue_instance.$refs.formulaire.element.affectations.push(utilisateur_id);

                    if(vue_instance.$refs.formulaire.element.affectation != undefined)
                        vue_instance.$refs.formulaire.element.affectation = utilisateur_id;

                    if(vue_instance.$refs.formulaire.element.dates != undefined){

                        if(moment === false){
                            date = date.replace(' am','');
                            vue_instance.$refs.formulaire.element.dates.am.push(date);
                            vue_instance.$refs.formulaire.element.dates.pm.push(date);
                        }
                        else{
                            date = date.replace(' '+moment,'');
                            vue_instance.$refs.formulaire.element.dates[moment].push(date);
                        }
                    }
                    else{
                        var date_debut = new Date(vue_instance.$refs.formulaire.element.date_de_debut);
                        var date_fin = new Date(vue_instance.$refs.formulaire.element.date_de_fin);

                        var heure_debut = (date_debut.getHours() < 10 ? '0' : '') + date_debut.getHours();
                        var minute_debut = (date_debut.getMinutes() < 10 ? '0' : '') + date_debut.getMinutes();

                        var heure_fin = (date_fin.getHours() < 10 ? '0' : '') + date_fin.getHours();
                        var minute_fin = (date_fin.getMinutes() < 10 ? '0' : '') + date_fin.getMinutes();

                        vue_instance.$refs.formulaire.element.date_de_debut = date + ' ' + heure_debut + ':' + minute_debut + ':00';
                        vue_instance.$refs.formulaire.element.date_de_fin = date + ' ' + heure_fin + ':' + minute_fin + ':00';
                    }

                    for(cle_parametre of Object.keys(this.filtres_tache)){

                        this.$refs.formulaire.element[cle_parametre] = this.filtres_tache[cle_parametre];
                    }

                    vue_instance.cle_formulaire++;
                });

                this.modale_planning = true;
            },

            afficher_tache: function(tache_id) {

                event.stopPropagation();
                
                loading(true);

                var vue_instance = this;

                $.get({
                    url : '/eden/element/tache/' + tache_id,
                    dataType: "json",
                    method: 'GET'
                }).done(async function(tache) {

                    vue_instance.$once('formulaire_charger',function() {
                        vue_instance.$refs.formulaire.element = tache;

                        vue_instance.cle_formulaire++;
                    });

                    vue_instance.modale_planning = true;

                    loading(false);
                });

            },

            /*
             *
             * Déclenche l'enregistrement d'une tâche
             * modifier_recurrence : null, 1 (modifie cet événement et les suivants) ou 2 (modifie toute la série)
             *
             */
            enregistrer_tache: async function(modifier_recurrence = null, type_enregistrement = 0) {

                // On afficher le loader
                loading(true);

                var parametres = {};

                if(modifier_recurrence !== null)
                    parametres = {modifier_recurrence};

                var donnees = await this.$refs.formulaire.enregistrer(parametres, null, type_enregistrement);

                loading(false);

                if(donnees.retour !== true)
                    return;

                toastr.success(this.$root.traduction('interface.listes.element_enregistre_avec_succes'));

                if(type_enregistrement === 0)
                    this.modale_planning = false;
                else if(type_enregistrement === 2)
                    this[this.type_element] = this.$refs.formulaire.element;

                this.actualisation();
                this.$root.$emit('enregistrement_planning', {type_enregistrement});
            },

            supprimer_taches_selectionnees: async function() {

                var confirmation = await confirm_eden(this.$root.traduction('composant.affichage_calendrier.confirmation_suppression'));

                if(!confirmation)
                    return;

                loading(true);

                var suppressions = this.selection_tache_ids.map(function(id_tache) {
                    return $.get({
                        url : '/eden/element/tache/' + id_tache + '/supprimer',
                        dataType: 'json',
                    });
                });

                await Promise.all(suppressions);

                this.selection_tache_ids = [];

                loading(false);

                this.actualisation();
                this.$root.$emit('enregistrement_planning');
                this.selection_tache_ids = [];
                this.selection_tache = false;
            },

            supprimer_tache: async function(id_tache = false) {

                if(!id_tache)
                    id_tache = this.tache.id;

                event.stopPropagation();

                var vue_instance = this;

                var confirmation = await confirm_eden(vue_instance.$root.traduction('composant.affichage_calendrier.confirmation_suppression'));

                if(!confirmation)
                    return;

                loading(true);

                $.get({
                    url : '/eden/element/tache/' + id_tache + '/supprimer',
                    dataType: "json",
                }).done(function(donnees) {

                    if(donnees.retour !== true) {
                        alerte_eden(donnees.retour);
                        return;
                    }

                    loading(false);

                    vue_instance.actualisation();

                    vue_instance.$root.$emit('enregistrement_planning');

                    if(vue_instance.modale_planning)
                        vue_instance.modale_planning = false;

                });
            },

            supprimer_recurrence: async function(toute_la_serie = null, id_tache = false) {

                if(!id_tache)
                    id_tache = this.tache.id;

                if(!await confirm_eden('{!! traduction('interface.alerte.attention') !!}',this.$root.traduction('composant.affichage_calendrier.confirmation_suppression'),'{!! traduction('interface.modales.oui') !!}','{!! traduction('interface.modales.non') !!}'))
                    return false;

                loading(true);

                // on fait un appel ajax pour supprimer
                $.get({

                    url: "eden/calendrier/"+this.tache.id+"/supprimer_recurrence",
                    dataType: "json",
                    data: {
                        toute_la_recurrence : toute_la_serie
                    }
                }).done(async (donnees) => {

                    loading(false);

                    if(donnees.retour !== true) {

                        await erreur(donnees.retour);
                        return;
                    }

                    this.actualisation();

                    this.$root.$emit('enregistrement_planning');

                    if(this.modale_planning)
                        this.modale_planning = false;
                });
            },

            @if(fonctionnalite('planning_affichage_demi_journee'))

                verification_semaine_par_utilisateur: function(utilisateur_id, semaines) {
                    var retour = true;

                    var vue_composant = this;

                    for(semaine of semaines){

                        semaine.dates.some(function(date,index){

                            if(retour !== false){

                                var matin = vue_composant.taches_par_utilisateur[utilisateur_id][semaine.matins[index]];
                                var apres_midi = vue_composant.taches_par_utilisateur[utilisateur_id][semaine.apres_midi[index]];

                                var taches_id_matin = matin.map(i => i.id);
                                var taches_id_apres_midi = apres_midi.map(i => i.id);

                                if(taches_id_matin.length != taches_id_apres_midi.length)
                                    retour = false;
                                else{

                                    var difference = taches_id_matin.filter(x => !taches_id_apres_midi.includes(x));

                                    // rien le matin, rien l'après midi
                                    if(difference.length > 0)
                                        retour = false;

                                }

                            }

                        });

                    }

                    return retour;
                },

            @endif

            nom_equipe: function(index, affiche_html = true){

                var style = '';
                var nom = this.$root.traduction('composant.planning.sans_equipe');

                if(index > 0 && this.equipes[index] !== undefined){
                    var equipe = this.equipes[index];
                    style="style='background:"+equipe.couleur_fond+";color:"+equipe.couleur_police+";'";
                    nom = equipe.nom;
                }
                if(affiche_html === false)
                    return nom;
                else
                    return '<span class="badge badge-default" '+style+'>'+nom+'</span>';


            },

            ordonne_utilisateurs : function(utilisateurs){

                var compare_champs = function(a, b){
                    if(!a && !b) 
                        return 0;
                    if(!a) 
                        return 1;
                    if(!b) 
                        return -1;
                    return a.localeCompare(b);
                };

                utilisateurs.sort(function(a, b){
                    var compare_nom = compare_champs(a.nom, b.nom);
                    if(compare_nom !== 0) 
                        return compare_nom;
                    return compare_champs(a.prenom, b.prenom);
                });

                return utilisateurs;
            },

            html_to_text: function(html) {

                if (!html)
                    return '';

                return html.replace(/<[^>]+>/g, '');
            },

            gestion_droppable : function(){

                if(!this.droppable)
                    return;

                var instance = this;

                $('.cellule_tache').droppable({
                    drop: function (ev, ui) {
                        ev.stopPropagation();
                        $(this).find('.temp_div').remove();
                        instance.$root.$emit('planning_drop',$(this),ui);
                    },
                    over: function(ev,ui){

                        var div = document.createElement('div');

                        div.classList.add('temp_div');
                        div.style.background = 'lightgrey';
                        div.style.width = '100%';
                        div.style.height = '50px';

                        $(this).append(div);

                    },
                    out:function(){

                        $(this).find('.temp_div').remove();
                    },
                });
            },

            affichage_formulaire_utilisateur : function(utilisateur){

                $.ajax({
                    url :'eden/element/utilisateur/'+utilisateur.id,
                    dataType:'json'
                }).done((element) => {

                    this.utilisateur_affiche = element;

                    this.$once('formulaire_charger', () => {

                        this.$refs.formulaire_utilisateur.element = element;

                    });

                    this.modale_formulaire_utilisateur = true;

                });
            },

            modifier_taches_selectionnees: function() {

                this.$refs.modification_en_masse.ouvrir();
            },

            apres_modification_en_masse: function() {

                this.selection_tache_ids = [];
                this.selection_tache = false;
                this.actualisation();
                this.$root.$emit('enregistrement_planning');
            },

            enregistrer_utilisateur : async function(){

                loading(true);

                await this.$refs.formulaire_utilisateur.enregistrer();
                this.modale_formulaire_utilisateur = false;
                this.actualisation();

                loading(false);
            },

            classe_conge : function(tache){

                if((tache.statut == 0 || tache.statut == null) && tache.valide_n1 == 1)
                    return 'conge_accepte_n1';
                else if(tache.statut == 1)
                    return 'conge_accepte';
                else
                    return 'conge_en_attente';
            },

            recuperer_tache_dom(cible_evenement){

                return cible_evenement.classList.contains('badge_planning') ? cible_evenement : cible_evenement.closest('.badge_planning')
            },

            recuperer_conteneur_tache_dom(cible_evenement, tache_dom){

                return cible_evenement.classList.contains('css_cellule_tache') ? cible_evenement.closest('.css_cellule_tache') : cible_evenement.closest('.css_cellule_tache')
            },

            @yield('donnees_pour_vuejs_methods')
            @stack('donnees_pour_vuejs_methods')

        },

        mounted: function() {

            var vue_instance = this;

            vue_instance.initialisation();

            vue_instance.$on('changement_semaine_voulue',function(date){
                vue_instance.semaine_voulue = date;
            });

            this.$on('changement_filtres',(valeurs) => {
                this.valeurs_filtres = valeurs;
                this.actualisation();
            });

            this.$root.$on('tooltip_actualisation_apres_action',() => {
                this.actualisation_affichage();
            });

            this.$root.$on('tooltip_affichage_formulaire',(tache) => {
                this.afficher_tache(tache.id);
            });

            this.$root.$on('tooltip_suppression_tache',(suppression_recurrence, tache_id) => {
                
                if(suppression_recurrence === null)
                    this.supprimer_tache(tache_id);
                else
                    this.supprimer_recurrence(suppression_recurrence === 2 ? 1 : null, tache_id);
            });

            @yield('donnees_pour_vuejs_mounted')
            @stack('donnees_pour_vuejs_mounted')

        },
        directives :{
            @yield('donnees_pour_vuejs_directives')
            @stack('donnees_pour_vuejs_directives')
        },
    });

</script>
