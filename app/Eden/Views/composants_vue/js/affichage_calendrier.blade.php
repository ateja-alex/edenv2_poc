@include('eden::composants_vue.js.include.commun_calendrier_planning')

<script>
    const affichage_calendrier = Vue.component('affichage-calendrier', {
        template: `
    <div>
        <div class="">
            <div class="" v-show="!creation_tache">
                <div class="card mb-3">
                    <div class="card-header" id="calendrier-header" :style="{ 'justify-content': (afficher_les_dates === true ? 'space-between' : 'flex-end') }">
                        <div v-if="afficher_les_dates === true" style="display: inline-flex;">
                            <h4 id="calendrier-header-selecteur_date">
                                <span class="css_action_icon secondaire" @click="met_a_jour_les_dates('precedent',false)"> &lt;&lt; </span>
                                <span class="dropdown">
                                    <span @click="choix_dates_en_cours = !(choix_dates_en_cours)" style="cursor:pointer;">
                                        <span v-if="type_affichage_calendrier == 'jour' ">Le @{{ date_debut.format_fr}}</span>
                                        <span v-else-if="['semaine_5j', 'semaine_6j', 'semaine_7j'].includes(type_affichage_calendrier)" v-html="$root.traduction('composant.affichage_calendrier.semaine_du', null, [date_debut.numero_semaine, date_debut.format_fr, date_fin.format_fr])"></span>
                                        <span v-else>@traduction('composant.affichage_calendrier.du') @{{ date_debut.format_fr}} @traduction('composant.affichage_calendrier.au') @{{date_fin.format_fr}}</span>
                                        <span v-if="choix_dates_en_cours"><i class="fas fa-chevron-up"></i></span>
                                        <span v-else ><i class="fas fa-chevron-down"></i></span>
                                    </span>
                                    <div id="calendrier-header-selecteur_date-datepicker" v-show="choix_dates_en_cours">
                                        <div :class="'selection_datepicker_'+type_affichage_calendrier" onchange="met_a_jour_les_dates()"></div>
                                    </div>
                                </span>
                                <span class="css_action_icon secondaire" @click="met_a_jour_les_dates('suivant',false)"> &gt;&gt; </span>
                                <span class="css_action_icon secondaire" @click="date = $root.aujourdhui;met_a_jour_les_dates()" :title="$root.traduction('composant.affichage_calendrier.aujourdhui')"><i class="fas fa-calendar-day"></i></span>
                                @include('eden::composants_vue.js.include.calendrier.boutons_impression')
                            </h4>
                        </div>
                        <div v-if="afficher_les_filtres === true" style="display:inline-flex">

                          <filtres ref="filtres" :appliquer_recherche_avancee="false"  :valeurs_filtres="valeurs_filtres" :filtres="filtres"></filtres>

                        </div>

                        <div class="calendrier_entete_partie_droite">
                            <select v-if="afficher_mode_affichage" class="calendrier_select_type_affichage" v-model="type_affichage_calendrier" @change="met_a_jour_les_dates()">
                                <option value="jour">@{{ $root.traduction('composant.affichage_calendrier.jour')}}</option>
                                <option value="semaine_5j">@{{ $root.traduction('composant.affichage_calendrier.semaine_de_5_jours')}}</option>
                                <option value="semaine_6j">@{{ $root.traduction('composant.affichage_calendrier.semaine_de_6_jours')}}</option>
                                <option value="semaine_7j">@{{ $root.traduction('composant.affichage_calendrier.semaine_de_7_jours')}}</option>
                                <option value="mois">@{{ $root.traduction('composant.affichage_calendrier.mois_entier')}}</option>
                            </select>
                            <div class="dropdown">
                                <span class="fa fa-calendar dropdown-toggle css_dropdown_sans_fleche_vers_le_bas css_pointer dropdown_calendrier_type_tache" data-toggle="dropdown" aria-expanded="false"></span>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <div class="dropdown-item">
                                        @traduction('composant.calendrier.type_taches_affichees.titre')
                                    </div>
                                    <div class="dropdown-item" v-for="type_possible in type_taches_affichees_possibles">
                                        <input class="calendrier_input_type_tache" type="radio" :id="'calendrier_type_tache_' + type_possible" :value="type_possible" v-model="type_taches_affichees" @change="met_a_jour_les_dates()">
                                        <label class="calendrier_label_type_tache" :for="'calendrier_type_tache_' + type_possible" v-html="$root.traduction('composant.calendrier.type_taches_affichees.' + type_possible)"></label>
                                    </div>
                                </div>
                            </div>
                            <span style="display:inline;margin-left:2px;" class="css_ajouter_element css__lien" @click="ajouter_tache()" :title="$root.traduction('composant.affichage_calendrier.ajouter')">
                                <i class="css_action_icon fa fa-fw fa-plus-square"></i>
                            </span>
                        </div>
                    </div>

                    <div class="card-body" style="padding: 0px !important;">
                        <div id="div_calendrier" @scroll="scroll_indicateur++" :style="'height:'+height_div_calendrier" class="table-responsive">
                            <table id="mois" v-if="type_affichage_calendrier == 'mois'" class="table table-bordered table-hover" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th width="14.29%">@traduction('interface.jours.lundi')</th>
                                        <th width="14.29%">@traduction('interface.jours.mardi')</th>
                                        <th width="14.29%">@traduction('interface.jours.mercredi')</th>
                                        <th width="14.29%">@traduction('interface.jours.jeudi')</th>
                                        <th width="14.29%">@traduction('interface.jours.vendredi')</th>
                                        <th width="14.29%">@traduction('interface.jours.samedi')</th>
                                        <th width="14.29%">@traduction('interface.jours.dimanche')</th>
                                    </tr>

                                </thead>
                                <tbody>
                                    <tr v-for="semaine in agenda">
                                        <template v-for="jour in semaine">
                                            <td class="colonne_tableau" v-if="jour['date'] == null"></td>
                                            <td class="colonne_tableau droppable" style="position:relative;height:53px;" :data-date="jour['date'].format_us" v-if="jour['date'] != null" @dblclick="ajouter_tache(jour['date'],null)">
                                                <div style="float: right; background: #eee; padding: 2px;position:absolute;top:0%;right:0%;z-index: 2">
                                                    @{{ jour['date']['format_date_du_jour'] }}
                                                </div>
                                                <div class="conteneur_taches_mois" style="position:relative;">
                                                    <template v-for="tache in jour['taches']">
                                                        <div @dblclick="ajouter_tache(null,null,tache)" v-tooltip_tache="tache" :class="classes_tache(tache)" :data-tache-id="tache.id" :data-tache-date-debut="tache.date_de_debut" :data-tache-date-fin="tache.date_de_fin" :style="'position:relative;padding: 5px;margin: 5px;'+tache.style" :key="tache.id">
                                                            <span>
                                                                <i v-show="tache.urgent == 1" class="fas fa-exclamation-triangle" :title="$root.traduction('composant.affichage_calendrier.urgent')" data-toggle="tooltip"></i>
                                                                <b v-html="tache.label"></b><br>
                                                                @{{ tache.affectation | affiche_utilisateur }}
                                                                <i v-if="tache.commentaire != null && tache.commentaire != ''" :title="tache.commentaire_title" data-toggle="tooltip" class="far fa-envelope css_btn_action_theme "></i>
                                                                <span style="font-style: italic;">@{{ tache.affichage_nom_tache }}</span>
                                                            </span>
                                                            <div style="display: flex; position: absolute; bottom: 5px; right: 5px; gap: 2px;">
                                                                <i v-show="tache.prive == 1" class="fas fa-lock" :title="$root.traduction('composant.affichage_calendrier.privee')" data-toggle="tooltip"></i>
                                                                <i v-show="tache.parent_id != null" class="fas fa-sync" :title="$root.traduction('composant.affichage_calendrier.recurrence')" data-toggle="tooltip"></i>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </td>
                                        </template>
                                    </tr>
                                </tbody>
                            </table>

                            <table v-else class="table table-bordered table-hover" width="100%" cellspacing="0">
                                <thead ref="header_sticky" id="header_sticky" v-clique_en_dehors="{ func: () => {deplier_ligne_journee_entiere ? deplier_ligne_journee_entiere = 0 : null}}">
                                    <tr>
                                        <th width="5%" style="text-align:center">
                                         <span style="display:inline;">
                                            <span class="fa fa-clock dropdown-toggle css_dropdown_sans_fleche_vers_le_bas" data-toggle="dropdown" aria-expanded="false"></span>
                                            <div class="dropdown-menu">
                                                <form id="formulaire_tranche_horaire">
                                                    <label class="dropdown-item">
                                                    <label>@traduction('composant.affichage_calendrier.debut') : </label>
                                                    <select name="tranche_horaire[heure_debut]" v-model='tranche_horaire.heure_debut' @change="met_a_jour_les_dates()" @click="$event.stopPropagation();">
                                                    <option v-for="heure in tranches_horaires_disponibles" v-if="parseFloat(heure.replace(':', '.')) < parseFloat(tranche_horaire.heure_fin.replace(':', '.'))" :value="heure" :key="heure">@{{ heure }}</option>
                                                    </select>
                                                    </label>
                                                    <label class="dropdown-item">
                                                    <label>@traduction('composant.affichage_calendrier.fin') : </label>
                                                    <select name="tranche_horaire[heure_fin]" v-model='tranche_horaire.heure_fin' @change="met_a_jour_les_dates()" @click="$event.stopPropagation();">
                                                        <option v-for="heure in tranches_horaires_disponibles" v-if="parseFloat(heure.replace(':', '.')) > parseFloat(tranche_horaire.heure_debut.replace(':', '.'))" :value="heure" :key="heure">@{{ heure }}</option>
                                                    </select>
                                                    </label>
                                                    <label class="dropdown-item">
                                                    <label>@traduction('composant.affichage_calendrier.granularite') : </label>
                                                    <select name="granularite" v-model='granularite' @change="changement_granularite()" @click="$event.stopPropagation();">
                                                        <option value="5">@{{ $root.traduction('composant.affichage_calendrier.cinq_minutes') }}</option>
                                                        <option value="10">@{{ $root.traduction('composant.affichage_calendrier.dix_minutes') }}</option>
                                                        <option value="15">@{{ $root.traduction('composant.affichage_calendrier.quinze_minutes') }}</option>
                                                        <option value="30">@{{ $root.traduction('composant.affichage_calendrier.trente_minutes') }}</option>
                                                        <option value="60">@{{ $root.traduction('composant.affichage_calendrier.une_heure') }}</option>
                                                    </select>
                                                    </label>
                                                </form>
                                            </div>
                                        </span>
                                        </th>
                                        <th style="position:relative" :class="date.format_us == aujourdhui ? 'css_aujourdhui' : ''" :width="100/dates.length - 5+'%' " v-for="date in dates" >
                                            <div style="display: flex;flex-direction: column;gap: 5px;">
                                                <span>@traduction("date['index_traduction']",null,true) @{{ date['date'] }}</span>
                                                <span class="badge badge-default" :style="'width: fit-content;background:'+indisponibilite.couleur+';color:'+indisponibilite.couleur_police" v-html="indisponibilite.chaine_affichage" v-for="indisponibilite in date.indisponibilites"></span>
                                            </div>
                                            <span class="calendrier_indicateur_jour" v-if="agenda[date.format_us] !== undefined && indicateur_taches_inferieurs[date.format_us] > 0">
                                                @{{ indicateur_taches_inferieurs[date.format_us] }}
                                                <i class="fas fa-long-arrow-alt-up"></i>
                                            </span>
                                        </th>
                                    </tr>
                                    @if(fonctionnalite('affichage_taches_journee_entiere') === 'bandeau')
                                        <tr id="calendrier_ligne_journee_entiere" :style="deplier_ligne_journee_entiere ? 'box-shadow: 0px 5px 10px grey;' : ''">
                                            <th style="width:5%"></th>
                                            <template v-for="date in dates">
                                                <th :width="100/dates.length - 5+'%' " data-cellule-journee-entiere="1" :data-date="date.format_us"
                                                    :class="'cellule_journee_entiere colonne_tableau droppable date_'+date.format_us" @dblclick="ajouter_tache(date,null,null,1)">
                                                    <div class="conteneur_tache_journee_entiere" :style="deplier_ligne_journee_entiere ? 'max-height:none' : ''" v-if="agenda[date.format_us]">
                                                        <template v-for="tache in agenda[date.format_us]['taches']['journee_entiere']">
                                                            @include('eden::composants_vue.js.include.calendrier.tache_journee_entiere')
                                                        </template>
                                                    </div>
                                                    <span class="calendrier_indicateur_jour calendrier_deplier_journee_entiere" @click="deplier_ligne_journee_entiere = 1"
                                                        v-if="agenda[date.format_us] !== undefined && indicateur_taches_journee_entiere[date.format_us] > 0">
                                                        <span v-text="$root.traduction('composant.affichage_calendrier.deplier_journee_entiere')"></span>
                                                        <span>(
                                                            <span v-text="indicateur_taches_journee_entiere[date.format_us]"></span>
                                                            <i class="fas fa-long-arrow-alt-down" aria-hidden="true"></i>
                                                        )</span>
                                                    </span>
                                                </th>
                                            </template>
                                        </tr>
                                    @endif
                                </thead>
                                <tbody>
                                    <tr v-for="heure in heures.filter((heure) => heure != 'journee_entiere')" :heure="heure" :class="{'ligne_hors_plage_horaire': heure_hors_plage(heure)}" style="text-align:center" >
                                        <td> @{{ heure }} </td>

                                        <template v-for="date in dates">
                                            <td :class="'colonne_tableau droppable date_'+date.format_us" :data-heure="heure" :data-date="date.format_us" @dblclick="ajouter_tache(date,heure)">
                                                <div class="calendrier_taches" v-if="agenda[date.format_us]">
                                                    <template v-for="(tache,cle) in agenda[date.format_us].taches[heure]">
                                                        @include('eden::composants_vue.js.include.calendrier.tache')
                                                    </template>
                                                </div>
                                            </td>
                                        </template>
                                    </tr>
                                </tbody>
                                <tfoot id="footer_sticky">
                                  <tr>
                                    <th width="5%"></th>
                                    <template v-for="date in dates">
                                      <th :width="100/dates.length - 5+'%' ">
                                        <span class="calendrier_indicateur_jour superieur" v-if="agenda[date.format_us] !== undefined && indicateur_taches_superieurs[date.format_us] > 0">
                                            @{{ indicateur_taches_superieurs[date.format_us] }}
                                          <i class="fas fa-long-arrow-alt-down"></i>
                                        </span>
                                      </th>
                                    </template>
                                  </tr>
                                </tfoot>
                            </table>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12" v-if="creation_tache" id="calendrier_formulaire">
            <div class="card mb-3">

                <div class="card-header" style="display: flex;align-items: center;justify-content: space-between;">
                    <h5 class="modal-title">@traduction('composant.affichage_calendrier.titre_modal')</h5>
                    <div class="save-button-container">
                        <template v-if="tache_selectionnee !== null && tache_selectionnee.parent_id > 0 && !tache_selectionnee.exception_recurrence && !tache_selectionnee.annulee">
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
                        <template v-else-if="tache_selectionnee == null || !tache_selectionnee.annulee" class="conteneur_boutons_enregistrement">
                            <span @click="enregistrer_tache()" class="css_ajouter_element ml-auto" data-toggle="tooltip" data-placement="top" :title="$root.traduction('interface.enregistrer')">
                                <i class="css_action_icon secondaire fa fa-fw fa-save"></i>
                            </span>
                            <template v-if="tache_selectionnee == null || !tache_selectionnee.exception_recurrence">
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
                        <button type="button" class="close" @click="creation_tache = false" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>

                <div class="card-body css_form js_selection_element" >
                    <formulaire ref="formulaire" :rendez_vous=true nom_formulaire="tache"></formulaire>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="fermer_modale_creation()">@traduction('interface.modales.fermer')</button>

                    <!-- Boutons de suppression -->
                    <div class="dropdown" v-if="tache_selectionnee !== null && tache_selectionnee.parent_id > 0 && (!tache_selectionnee.participant || tache_selectionnee.annulee)">
                        <div class="btn btn-danger" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">@traduction('interface.modales.supprimer')</div>
                        <div class="dropdown-menu">
                            <div class="dropdown-save">
                                <span class="dropdown-item" @click="supprimer_tache()">@traduction('composant.affichage_calendrier.cet_evenement')</span>
                                <span class="dropdown-item" @click="supprimer_recurrence()">@traduction('composant.affichage_calendrier.evenements_suivants')</span>
                                <span class="dropdown-item" @click="supprimer_recurrence(1)">@traduction('composant.affichage_calendrier.toute_la_serie')</span>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-danger" v-else-if="tache_id && (!tache_selectionnee.participant || tache_selectionnee.annulee)" @click="supprimer_tache()">@traduction('interface.modales.supprimer')</button>
                    <div v-else>

                        @include('eden::composants_vue.js.include.tache.boutons_statut_participants')
                    </div>

                    <!-- Bouton Afficher -->
                    @if(table_libre('tache')->fiche === 1)
                        <a :href="'/eden/fiche/tache/'+tache_id" class="btn btn-primary" v-if="tache_id != undefined && tache_id != false">@traduction('interface.modales.afficher')</a>
                    @endif

                    <!-- Boutons de modification -->
                    <div class="dropdown" v-if="tache_selectionnee !== null && tache_selectionnee.parent_id > 0 && !tache_selectionnee.exception_recurrence && !tache_selectionnee.annulee">
                        <div class="btn btn-primary" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">@traduction('interface.modales.enregistrer')</div>
                        <div class="dropdown-menu">
                            <div style="display:flex;flex-direction: column;gap:5px">
                                <span class="dropdown-item" @click="enregistrer_tache()">@traduction('composant.affichage_calendrier.cet_evenement')</span>
                                <span class="dropdown-item" @click="enregistrer_tache(1)" v-if="$refs.formulaire != undefined && $refs.formulaire.$refs.formulaire.participants_modifies?.() !== true">@traduction('composant.affichage_calendrier.evenements_suivants')</span>
                                <span class="dropdown-item" @click="enregistrer_tache(2)">@traduction('composant.affichage_calendrier.toute_la_serie')</span>
                            </div>
                        </div>
                    </div>
                    <div v-else-if="tache_selectionnee == null || !tache_selectionnee.annulee" class="conteneur_boutons_enregistrement">
                        <button type="button" class="btn btn-primary css_btn_responsive" @click="enregistrer_tache()">
                            @traduction('interface.listes.enregistrer')
                        </button>
                        <template v-if="tache_selectionnee == null || !tache_selectionnee.exception_recurrence">
                            <span class="btn btn-primary css_btn_responsive bouton_dropdown_enregistrer" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-angle-down"></i>
                            </span>
                            <div class="dropdown-menu">
                                <span class="dropdown-item" @click="enregistrer_tache(null, 1)">@traduction('interface.listes.enregistrer_et_nouveau')</span>
                                <span class="dropdown-item" @click="enregistrer_tache(null, 2)">@traduction('interface.listes.enregistrer_et_dupliquer')</span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>`,

		props:{

			valeurs_par_defaut_tache: {},
			afficher_les_dates: {
				type: Boolean,
				default: true
			},
			afficher_les_filtres: {
				type: Boolean,
				default: true
			},
            afficher_mode_affichage: {
                type: Boolean,
                default: true
            },
            filtres_pour_fiche: {
                type : Object,
                default : function(){
                    return {};
                }
            },

            @yield('donnees_pour_vuejs_props')
            @stack('donnees_pour_vuejs_props')
		},

        data: function(){

            return {

                date_dans_les_data: [],
                dates: [],
                date_debut: [],
                aujourdhui: '',
                date_fin: [],
                deplier_ligne_journee_entiere: 0,
                agenda:[],
                heures:[],
                creneaux:[],
                composants_tooltip: {},
                tache: {},
                tache_id: null,
                tache_selectionnee: null,
                modele_par_defaut_tache: {},
                creation_tache: false,
                type_affichage_calendrier : '',
                date : '',
                tranche_horaire : {
                    heure_debut : '',
                    heure_fin : '',
                },
                granularite : '30',
                requete_actualisation_en_cours: undefined,
                choix_dates_en_cours: false,
                height_card_body: '100vh',
                height_div_calendrier: '',
                hauteur_ligne_calendrier: 32,
                scroll_indicateur:0,
                filtres : {},
                valeurs_filtres: [],
                type_taches_affichees: 'les_deux',
                type_taches_affichees_possibles: [
                    'les_deux',
                    'tache',
                    'conges'
                ],

                @yield('donnees_pour_vuejs_data')
                @stack('donnees_pour_vuejs_data')
		    }

        },

        methods: {

            @yield('donnees_pour_vuejs_methods')
            @stack('donnees_pour_vuejs_methods')

            actualisation_affichage: function(){

                if(this.creation_tache)
                    this.fermer_modale_creation();

                this.met_a_jour_les_dates();
            },

            met_a_jour_les_dates: function(modification_periode = false,initialisation = false) {

                var vue_composant = this;

                vue_composant.agenda = [];
                vue_composant.creneaux = [];
                vue_composant.heures = [];

                vue_composant.$forceUpdate();

                loading(true);

                if(vue_composant.requete_actualisation_en_cours !== undefined)
                    vue_composant.requete_actualisation_en_cours.abort();

                if(initialisation == true){
                    parametres = {
                        'initialisation' : true,
						'valeurs_par_defaut_tache' : this.valeurs_par_defaut_tache,
                        'filtres_pour_fiche': this.filtres_pour_fiche,
                        'type_taches_affichees' : this.type_taches_affichees,
                    };
                }

                else{
                    parametres = {

                        'format_calendrier' : this.type_affichage_calendrier,
                        'modification_periode' : modification_periode,
                        'filtres_pour_fiche': this.filtres_pour_fiche,
                        'filtres' :this.valeurs_filtres,
                        'date' : this.date,
                        'tranche_horaire' : this.tranche_horaire,
                        'granularite' : this.granularite,
                        'type_taches_affichees' : this.type_taches_affichees,
						'valeurs_par_defaut_tache' : this.valeurs_par_defaut_tache,
                    };
                }

                vue_composant.requete_actualisation_en_cours = $.post({
                    url: '{{route('calendrier.recuperation_donnees', [], false)}}',
                    dataType: "json",
                    data: parametres,
                }).done(function(informations) {
                    vue_composant.dates = informations.dates;
                    vue_composant.aujourdhui = informations.aujourdhui;
                    vue_composant.date_debut = informations.date_debut;
                    vue_composant.date_fin = informations.date_fin;
                    vue_composant.heures = informations.heures;
                    vue_composant.agenda = informations.agenda;
                    vue_composant.creneaux = informations.creneaux;
                    vue_composant.type_affichage_calendrier = informations.format_calendrier;
                    vue_composant.tranche_horaire = informations.tranche_horaire;
                    vue_composant.granularite = informations.granularite;
                    vue_composant.date = informations.date;
                    vue_composant.choix_dates_en_cours = false;
                    vue_composant.modele_par_defaut_tache = informations.modele_par_defaut_tache;
                    vue_composant.type_taches_affichees = informations.type_taches_affichees;
                    vue_composant.requete_actualisation_en_cours = undefined;

                    if(initialisation === true) {
                        vue_composant.filtres = informations.filtres;
                        vue_composant.valeurs_filtres = informations.valeurs_filtres;
                    }

					@yield('action_a_executer_suite_actualisation')
                    @stack('action_a_executer_suite_actualisation')

					vue_composant.$root.$emit('actualisation_calendrier');

                    //Calcul de la largeur des cellules du calendrier
                    setTimeout(() => {

                        @yield('action_a_executer_suite_actualisation_timeout')
                        @stack('action_a_executer_suite_actualisation_timeout')

                        $(".selection_datepicker_" + vue_composant.type_affichage_calendrier).datepicker('remove');

                        var langue = vue_composant.$root.moi.langue !== null ? vue_composant.$root.moi.langue : 'fr';

                        if (vue_composant.type_affichage_calendrier == 'mois') {

                            $(".selection_datepicker_" + vue_composant.type_affichage_calendrier).datepicker({
                                viewMode: "months",
                                minViewMode: "months",
                                language: langue,
                                todayHighlight: true,
                            });

                        } else {
                            $(".selection_datepicker_" + vue_composant.type_affichage_calendrier).datepicker({
                                language: langue,
                                todayHighlight: true,
                            });
                        }

                        let date_debut = moment(vue_composant.date_debut.format_us, 'YYYY-MM-DD');

                        if(vue_composant.type_affichage_calendrier.includes('semaine')){

                            let date_fin = moment(vue_composant.date_fin.format_us, 'YYYY-MM-DD');
                            let dates = [date_debut.toDate()];

                            for(let i = 0; i<=5;i++){
                                date_debut.add(1, 'd');
                                dates.push(date_debut.toDate());
                            }

                            $(".selection_datepicker_" + vue_composant.type_affichage_calendrier).datepicker('setDates', dates);
                        }
                        else
                            $(".selection_datepicker_" + vue_composant.type_affichage_calendrier).datepicker('update', date_debut.toDate());

                        $(".selection_datepicker_" + vue_composant.type_affichage_calendrier).on("changeDate", function (e) {

                            if(e.dates.length > 1)
                                return;

                            var date = $(this).datepicker('getDate');
                            date = $.datepicker.formatDate("yy-mm-dd", date)

                            vue_composant.met_a_jour_les_dates(date);
                        });

                        vue_composant.initialisation_draggable();

                        vue_composant.calcule_placement_calendrier();

                        loading(false);

                    },0);

                });
            },
            changement_granularite: function(){

                var granularite = parseInt(this.granularite);

                var minutes_debut = this.heure_en_minutes(this.tranche_horaire.heure_debut);
                var minutes_fin = this.heure_en_minutes(this.tranche_horaire.heure_fin);

                minutes_debut = Math.floor(minutes_debut / granularite) * granularite;
                minutes_fin = Math.ceil(minutes_fin / granularite) * granularite;

                if(minutes_fin <= minutes_debut)
                    minutes_fin = minutes_debut + granularite;

                this.tranche_horaire.heure_debut = this.minutes_en_heure(minutes_debut);
                this.tranche_horaire.heure_fin = this.minutes_en_heure(minutes_fin);

                this.met_a_jour_les_dates();
            },
            heure_en_minutes: function(heure){

                var parties = heure.split(':');

                return parseInt(parties[0]) * 60 + parseInt(parties[1]);
            },
            minutes_en_heure: function(minutes){

                var heure = Math.floor(minutes / 60);
                var minute = minutes % 60;

                return ('0'+heure).slice(-2)+':'+('0'+minute).slice(-2);
            },

            /*
             *
             * Indique si une heure de la grille est hors de la tranche horaire sélectionnée par l'utilisateur (heure_debut inclus, heure_fin exclu)
             *
             */
            heure_hors_plage: function(heure){

                return this.heure_en_minutes(heure) < this.heure_en_minutes(this.tranche_horaire.heure_debut) ||
                    this.heure_en_minutes(heure) >= this.heure_en_minutes(this.tranche_horaire.heure_fin);
            },
            changement_date_tache: function(tache_id,informations) {

                var vue_composant = this;

                // on fait un appel ajax
                var url = "eden/element/tache/"+tache_id+"/enregistrer";

                // On afficher le loader
                loading();

                // on enregistre les infos du champ libre
                $.post({

                    url: url,
                    dataType: "json",
                    method: 'POST',
                    data: informations
                }).done(async function(donnees) {

                    // On retire le loader
                    if(donnees.retour !== true) {

                        await erreur(donnees.retour);
                        return;
                    }

                    vue_composant.met_a_jour_les_dates();

                    vue_composant.$parent.$emit('enregistrement_tache');

                    loading(false);

                });

            },

            /*
             *
             * Permet de savoir si l'utilisateur a le droit de redimensionner (modifier le début ou la fin par glissement) une tâche donnée
             *
             */
            peut_redimensionner_tache: function(tache){

                return this.type_affichage_calendrier != 'mois' && (tache.prive != 1 || tache.affectation == this.$root.moi.id) && tache.draggable;
            },

            /*
             *
             * Démarre le redimensionnement d'une tâche par une de ses extrémités ('debut' ou 'fin')
             *
             */
            demarrer_redimensionnement: function(event, tache, extremite){

                if(!this.peut_redimensionner_tache(tache))
                    return;

                var vue_composant = this;

                var element_tache = this.recuperer_tache_dom(event.target);
                var rect_tache = element_tache.getBoundingClientRect();
                var td_origine = element_tache.closest('td[data-date]');

                this.redimensionnement = {

                    tache: tache,
                    extremite: extremite,
                    date_de_debut_fixe: new Date(tache.date_de_debut),
                    date_de_fin_fixe: new Date(tache.date_de_fin),
                    nouvelle_date_debut: new Date(tache.date_de_debut),
                    nouvelle_date_fin: new Date(tache.date_de_fin),
                    // Largeur/position horizontale de la tâche elle-même (et non de toute la cellule), figées au début du glissement
                    // : elles ne changent pas pendant le redimensionnement (seule la hauteur/position verticale évolue).
                    largeur_tache: rect_tache.width,
                    gauche_tache: rect_tache.left,
                    // Jour (colonne) où se trouve le bloc saisi : sert à conserver sa largeur/position exacte parmi les jours affichés par l'aperçu.
                    jour_origine: td_origine != null ? td_origine.dataset.date : null,
                    apercus: [],
                    intervalle_defilement: null,
                    dernier_x: event.clientX,
                    dernier_y: event.clientY,
                };

                $('body').addClass('redimensionnement_tache_en_cours');

                $(document).on('mousemove.redimensionnement_tache', function(e){

                    vue_composant.redimensionnement.dernier_x = e.clientX;
                    vue_composant.redimensionnement.dernier_y = e.clientY;

                    vue_composant.redimensionnement_evaluer_position();
                });

                $(document).on('mouseup.redimensionnement_tache', function(e){

                    vue_composant.terminer_redimensionnement();
                });

                this.redimensionnement_evaluer_position();
            },

            /*
             *
             * Recherche la cellule du calendrier (jour + heure) située sous le pointeur.
             * On utilise elementsFromPoint (et non elementFromPoint) car, pendant un redimensionnement, la tâche
             * elle-même (toujours affichée à sa taille d'origine) peut recouvrir la cellule réellement survolée :
             * on parcourt donc toute la pile d'éléments à cette position pour trouver la vraie cellule de la grille,
             * même si elle est visuellement masquée par la tâche en cours de redimensionnement.
             *
             */
            recuperer_cellule_sous_pointeur: function(x, y){

                var elements = document.elementsFromPoint(x, y);

                for(var i = 0; i < elements.length; i++){

                    if(elements[i].matches('.colonne_tableau[data-heure]'))
                        return elements[i];
                }

                return null;
            },

            /*
             *
             * Déclenche un défilement automatique du calendrier lorsque le pointeur approche du haut ou du bas de la zone visible
             *
             */
            redimensionnement_gerer_defilement_automatique: function(y){

                var redimensionnement = this.redimensionnement;
                var vue_composant = this;

                var conteneur = document.getElementById('div_calendrier');
                var rect = conteneur.getBoundingClientRect();
                var zone_sensible = 40;
                var vitesse = 15;

                var direction = 0;

                if(y < rect.top + zone_sensible)
                    direction = -1;
                else if(y > rect.bottom - zone_sensible)
                    direction = 1;

                if(direction === 0){

                    if(redimensionnement.intervalle_defilement !== null){

                        clearInterval(redimensionnement.intervalle_defilement);
                        redimensionnement.intervalle_defilement = null;
                    }

                    return;
                }

                if(redimensionnement.intervalle_defilement !== null)
                    return;

                redimensionnement.intervalle_defilement = setInterval(function(){

                    conteneur.scrollTop += direction * vitesse;

                    vue_composant.redimensionnement_evaluer_position();
                }, 16);
            },

            /*
             *
             * Calcule la nouvelle date de début ou de fin selon la position du pointeur, en respectant la granularité et la durée minimale
             *
             */
            redimensionnement_evaluer_position: function(){

                var redimensionnement = this.redimensionnement;

                if(redimensionnement == null)
                    return;

                this.redimensionnement_gerer_defilement_automatique(redimensionnement.dernier_y);

                var cellule = this.recuperer_cellule_sous_pointeur(redimensionnement.dernier_x, redimensionnement.dernier_y);

                if(cellule == null)
                    return;

                var granularite = parseInt(this.granularite);
                var nouvelle_date = new Date(cellule.dataset.date+' '+cellule.dataset.heure+':00');

                if(redimensionnement.extremite == 'debut'){

                    var limite_maximum = new Date(redimensionnement.date_de_fin_fixe.getTime() - granularite * 60000);

                    if(nouvelle_date.getTime() > limite_maximum.getTime())
                        nouvelle_date = limite_maximum;

                    redimensionnement.nouvelle_date_debut = nouvelle_date;
                }
                else{

                    nouvelle_date = new Date(nouvelle_date.getTime() + granularite * 60000);

                    var limite_minimum = new Date(redimensionnement.date_de_debut_fixe.getTime() + granularite * 60000);

                    if(nouvelle_date.getTime() < limite_minimum.getTime())
                        nouvelle_date = limite_minimum;

                    redimensionnement.nouvelle_date_fin = nouvelle_date;
                }

                this.redimensionnement_actualiser_apercu();
            },

            /*
             *
             * Recherche, dans le corps du tableau (tbody), la cellule correspondant précisément à une date et une heure
             * données. On cible directement le <td> par ses attributs data-date/data-heure (plutôt que de combiner une
             * ligne <tr[heure]> - commune à toutes les colonnes - avec une classe .date_X qui existe aussi sur l'en-tête
             * "journée entière", ce qui pouvait faire échouer la recherche selon la tâche/l'heure concernée).
             *
             */
            recuperer_cellule_par_date_heure: function(date_format_us, heure){

                return document.querySelector('#div_calendrier tbody td.colonne_tableau[data-date="'+date_format_us+'"][data-heure="'+heure+'"]');
            },

            /*
             *
             * Positionne l'aperçu (un rectangle en pointillés par jour couvert) représentant la nouvelle taille/position de la
             * tâche en cours de redimensionnement. Un seul rectangle ne suffit pas dès que la tâche s'étend sur plusieurs jours :
             * dans cette grille, chaque jour est une colonne distincte partageant les mêmes lignes d'heures (le jour suivant
             * n'est donc pas "plus bas" mais dans une autre colonne) : on construit alors un rectangle par jour couvert.
             *
             */
            redimensionnement_actualiser_apercu: function(){

                var redimensionnement = this.redimensionnement;
                var granularite = parseInt(this.granularite);

                var heures_creneaux = this.heures.filter((heure) => heure != 'journee_entiere');
                var premiere_heure = heures_creneaux[0];
                var derniere_heure = heures_creneaux[heures_creneaux.length - 1];

                var date_debut_format_us = moment(redimensionnement.nouvelle_date_debut).format('YYYY-MM-DD');
                var heure_debut = moment(redimensionnement.nouvelle_date_debut).format('HH:mm');

                // date_de_fin est exclusive : le dernier créneau réellement occupé est celui d'avant, il faut calculer
                // sa date ET son heure à partir du même instant ajusté (sinon désync dès que la fin tombe à minuit).
                var dernier_creneau_fin = new Date(redimensionnement.nouvelle_date_fin.getTime() - granularite * 60000);
                var date_fin_format_us = moment(dernier_creneau_fin).format('YYYY-MM-DD');
                var heure_fin_incluse = moment(dernier_creneau_fin).format('HH:mm');

                redimensionnement.apercus.forEach((apercu) => apercu.remove());
                redimensionnement.apercus = [];

                var jour_courant = new Date(date_debut_format_us+'T00:00:00');
                var jour_fin = new Date(date_fin_format_us+'T00:00:00');

                while(jour_courant.getTime() <= jour_fin.getTime()){

                    var jour_format_us = moment(jour_courant).format('YYYY-MM-DD');
                    var premier_jour = jour_format_us == date_debut_format_us;
                    var dernier_jour = jour_format_us == date_fin_format_us;

                    var cellule_haut = this.recuperer_cellule_par_date_heure(jour_format_us, premier_jour ? heure_debut : premiere_heure);
                    var cellule_bas = this.recuperer_cellule_par_date_heure(jour_format_us, dernier_jour ? heure_fin_incluse : derniere_heure);

                    jour_courant.setDate(jour_courant.getDate() + 1);

                    if(cellule_haut == null || cellule_bas == null)
                        continue;

                    var rect_haut = cellule_haut.getBoundingClientRect();
                    var rect_bas = cellule_bas.getBoundingClientRect();

                    // Sur le jour où se trouve le bloc saisi, on reprend sa largeur/position exacte (et non celle de toute la cellule)
                    // pour que l'aperçu lui ressemble exactement ; les autres jours n'ont pas cette information (pas encore affichés
                    // ce jour-là) donc on prend toute la largeur de la colonne.
                    var origine = jour_format_us == redimensionnement.jour_origine;

                    var apercu = $('<div class="calendrier_apercu_redimensionnement"></div>').appendTo('body').css({
                        top: rect_haut.top+'px',
                        left: rect_haut.left+'px',
                        width: rect_haut.width+'px',
                        height: (rect_bas.bottom - rect_haut.top)+'px',
                    });

                    redimensionnement.apercus.push(apercu);
                }
            },

            /*
             *
             * Termine le redimensionnement en cours : nettoyage des écouteurs/de l'aperçu, puis enregistrement si une extrémité a changé
             *
             */
            terminer_redimensionnement: function(){

                var redimensionnement = this.redimensionnement;

                if(redimensionnement == null)
                    return;

                $(document).off('.redimensionnement_tache');
                $('body').removeClass('redimensionnement_tache_en_cours');

                if(redimensionnement.intervalle_defilement !== null)
                    clearInterval(redimensionnement.intervalle_defilement);

                redimensionnement.apercus.forEach((apercu) => apercu.remove());

                this.redimensionnement = null;

                var debut_inchange = redimensionnement.nouvelle_date_debut.getTime() === redimensionnement.date_de_debut_fixe.getTime();
                var fin_inchangee = redimensionnement.nouvelle_date_fin.getTime() === redimensionnement.date_de_fin_fixe.getTime();

                if(debut_inchange && fin_inchangee)
                    return;

                var informations = {
                    'date_de_debut': moment(redimensionnement.nouvelle_date_debut).format('YYYY-MM-DD HH:mm:SS'),
                    'date_de_fin': moment(redimensionnement.nouvelle_date_fin).format('YYYY-MM-DD HH:mm:SS'),
                };

                this.changement_date_tache(redimensionnement.tache.id, informations);
            },

            ajouter_tache: function(date = null,heure = null,tache = null, journee_entiere = null){

                this.tache_id = null;
                this.tache_selectionnee = null;

                event.stopPropagation();

                if(tache != null && (tache.type_element == 'demande_cp' || (tache.prive == 1 && tache.affectation != this.$root.moi.id)))
                    return false;

                this.$once('formulaire_charger',() => {
                    var modele_tache = structuredClone(this.$refs.formulaire.element);
                    if(tache != null) {
                        modele_tache = tache;
                        this.tache_id = tache.id;
                        this.tache_selectionnee = tache;
                    }
                    else {

                        if(date === null || heure === null)
                            aujourdhui = new Date();

                        if(heure === null)
                            heure = aujourdhui.getHours() + ':' + aujourdhui.getMinutes();

                        if(date !== null)
                            date = new Date(date.format_us + ' ' + heure);
                        else
                            date = aujourdhui;

                        if(date.getMinutes() > 0 && date.getMinutes() < 30)
                            date.setMinutes(30);
                        else if(date.getMinutes() > 30){

                            date.setMinutes(0);
                            date.setHours(date.getHours() + 1);
                        }

                        if(journee_entiere !== null)
                            modele_tache.journee_entiere = journee_entiere;

                        modele_tache.date_de_debut = moment(date).format('YYYY-MM-DD HH:mm') + ":00";
                        date.setHours(date.getHours() + 1);
                        modele_tache.date_de_fin = moment(date).format('YYYY-MM-DD HH:mm') + ":00";

                        var valeur_filtre_utilisateur = this.$refs.filtres.valeurs_filtres.find((element) => element.id === 1 || element.id === '1');

                        if(this.filtres_pour_fiche.utilisateur != null && Number.isInteger(this.filtres_pour_fiche.utilisateur))
                            modele_tache.affectation = this.filtres_pour_fiche.utilisateur;
                        else if(valeur_filtre_utilisateur != undefined && Array.isArray(valeur_filtre_utilisateur.valeurs) && 
                            valeur_filtre_utilisateur.valeurs.length === 1)
                            modele_tache.affectation = valeur_filtre_utilisateur.valeurs[0] === '#utilisateur_connecte#' ? 
                                this.$root.moi.id : valeur_filtre_utilisateur.valeurs[0];
                    }
                    this.$refs.formulaire.element = modele_tache;
                });

                this.creation_tache = true;
            },

            /*
             *
             * Déclenche l'enregistrement d'une tâche
             * modifier_recurrence : null, 1 (modifie cet événement et les suivants) ou 2 (modifie toute la série)
             *
             */
            enregistrer_tache: async function(modifier_recurrence = null, type_enregistrement = 0){

                // On afficher le loader
                loading(true);

                var parametres = {};

                if(modifier_recurrence !== null)
                    parametres = {modifier_recurrence};

                var donnees = await this.$refs.formulaire.enregistrer(parametres, null, type_enregistrement);

                if(donnees.retour !== true){
                
                    loading(false);
                    return;
                }

                toastr.success(this.$root.traduction('interface.listes.element_enregistre_avec_succes'));

                if(type_enregistrement === 0)
                    this.creation_tache = false;
                else if(type_enregistrement === 2)
                    this[this.type_element] = this.$refs.formulaire.element;

                loading(false);

                this.$parent.$emit('enregistrement_tache', {type_enregistrement});
                this.met_a_jour_les_dates();
            },

            supprimer_tache: async function() {

                var vue_composant = this;

                if(!await confirm_eden(vue_composant.$root.traduction('composant.affichage_calendrier.confirmation_suppression')))
                    return false;

                loading(true);


                // on fait un appel ajax pour supprimer
                $.get({

                    url: "eden/element/tache/"+vue_composant.tache_id+"/supprimer",
                    dataType: "json",
                }).done(async function(donnees) {

                    loading(false);

                    if(donnees.retour !== true) {

                        await erreur(donnees.retour);
                        return;
                    }

                    vue_composant.creation_tache = false;

                    vue_composant.met_a_jour_les_dates();

                    vue_composant.$parent.$emit('suppression_tache');

                });
            },

            supprimer_recurrence: async function(toute_la_serie = null) {

                if(!await confirm_eden('{!! traduction('interface.alerte.attention') !!}',this.$root.traduction('composant.affichage_calendrier.confirmation_suppression'),'{!! traduction('interface.modales.oui') !!}','{!! traduction('interface.modales.non') !!}'))
                    return false;

                loading(true);

                // on fait un appel ajax pour supprimer
                $.get({

                    url: "eden/calendrier/"+this.tache_id+"/supprimer_recurrence",
                    dataType: "json",
                    data: {
                        toute_la_serie : toute_la_serie
                    }
                }).done(async (donnees) => {

                    loading(false);

                    if(donnees.retour !== true) {

                        await erreur(donnees.retour);
                        return;
                    }

                    this.creation_tache = false;
                    this.met_a_jour_les_dates();
                });
            },

            initialisation_draggable : function(){

                var vue_composant = this;

                var draggable = $('.draggable').draggable({
                    revert: 'invalid',
                    delay: 200,
                    cancel: '.poignee_redimensionnement_tache',
                    start : function(e){
                        var item = $(this);
                        var td = item.parent('td');
                        item.css('max-width',td.css('width'));
                        item.css('max-height',td.css('height'));
                        item.css('min-height','');
                        item.css('cursor','grabbing');
                        item.css('z-index','9999');
                    },
                    stop : function(e){
                        var item = $(this);
                        item.css('max-width', '');
                        item.css('max-height', '');
                        item.css('cursor','grab');
                        item.css('min-height',item.css('height'));
                        item.css('z-index', '');
                    }
                });

                //Gestion du mobile
                var mobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

                if(mobile)
                    draggable = draggable.draggable('disable')

                var en_mouvement = false;

                $(".draggable").bind('touchstart',function(event) {
                    if(en_mouvement == false) {

                        var item = $(this);
                        this.duree_souris = setTimeout(() => {
                            $('.draggable').draggable('enable');
                            var td = item.parent('td');
                            item.css('max-width',td.css('width'));
                            item.css('max-height',td.css('height'));
                            item.css('min-height','');
                            item.css('z-index','9999');

                            en_mouvement = true;
                            item.trigger(event);
                        }, 500);
                    }
                }).bind('touchend',function(e) {
                    var item = $(this);
                    clearTimeout(this.duree_souris);
                    $('.draggable').draggable('disable');
                    item.css('max-width', '');
                    item.css('max-height', '');
                    item.css('min-height',item.css('height'));
                    item.css('z-index', '');
                    en_mouvement = false;
                }).bind('touchmove',function(e) {
                    clearTimeout(this.duree_souris);
                    en_mouvement = false;
                });

                $('.droppable').droppable({
                    tolerance: 'pointer',
                    accept: function(dropElem) {

                        var vue_mois = $(this).closest('table#mois').length > 0;
                        var date = $(this).attr('data-date');
                        var cellule_journee_entiere = $(this).attr('data-cellule-journee-entiere');

                        var date_de_debut_initial = new Date(dropElem.attr('data-tache-date-debut'));
                        var journee_entiere = dropElem.attr('data-tache-journee-entiere');

                        if(!vue_mois && journee_entiere == 1 && cellule_journee_entiere != 1)
                            return false;

                        var nouvelle_date_debut;

                        if(vue_mois){

                            var heure_initiale = ("0"+date_de_debut_initial.getHours()).slice(-2)+":"+("0"+date_de_debut_initial.getMinutes()).slice(-2)+":"+("0"+date_de_debut_initial.getSeconds()).slice(-2);
                            nouvelle_date_debut = new Date(date+" "+heure_initiale);
                        }
                        else{

                            var heure = $(this).attr('data-heure');
                            nouvelle_date_debut = new Date(date+" "+heure+":00");
                        }

                        if(nouvelle_date_debut.getTime() == date_de_debut_initial.getTime())
                            return false;

                        return true;
                    },
                    drop: function(ev, ui) {

                        ev.stopPropagation();

                        var vue_mois = $(this).closest('table#mois').length > 0;
                        var date = $(this).attr('data-date');

                        var tache_id = ui.draggable.attr('data-tache-id');
                        var journee_entiere = ui.draggable.attr('data-tache-journee-entiere');
                        var date_de_debut_initial = new Date(ui.draggable.attr('data-tache-date-debut'));
                        var date_de_fin_initial = new Date(ui.draggable.attr('data-tache-date-fin'));
                        var diffMs = date_de_fin_initial - date_de_debut_initial;

                        var nouvelle_date_debut, nouvelle_date_fin;

                        if(vue_mois){

                            var heure_initiale = ("0"+date_de_debut_initial.getHours()).slice(-2)+":"+("0"+date_de_debut_initial.getMinutes()).slice(-2)+":"+("0"+date_de_debut_initial.getSeconds()).slice(-2);
                            nouvelle_date_debut = new Date(date+" "+heure_initiale);
                            nouvelle_date_fin = new Date(nouvelle_date_debut.getTime() + diffMs);
                        }
                        else{

                            var ligne_tache = $(this).parent('tr');
                            var heure = $(this).attr('data-heure');
                            var nombre_demies_heures = ui.draggable.attr('data-tache-nombre-demies-heures');

                            for(var i = 1;i<=nombre_demies_heures;i++){

                                var bloc_tache = ligne_tache.find('.date_'+date);
                                bloc_tache.css('border-left','');
                                bloc_tache.css('border-right','');
                                if(i == 1)
                                    bloc_tache.css('border-top','');

                                if(i == nombre_demies_heures)
                                    bloc_tache.css('border-bottom','');

                                ligne_tache = ligne_tache.next('tr');
                            }

                            if(journee_entiere == 1){

                                nouvelle_date_debut = new Date(date+" 00:00:00");
                                nouvelle_date_fin = new Date(date+" 23:59:59");
                            }
                            else{

                                nouvelle_date_debut = new Date(date+" "+heure+":00");
                                nouvelle_date_fin = new Date(nouvelle_date_debut.getTime() + diffMs);
                            }
                        }

                        var informations = {
                            'date_de_debut': moment(nouvelle_date_debut.getTime()).format('YYYY-MM-DD HH:mm:SS'),
                            'date_de_fin': moment(nouvelle_date_fin.getTime()).format('YYYY-MM-DD HH:mm:SS'),
                        };

                        if(journee_entiere == 1 && !vue_mois)
                            informations.journee_entiere = 1;

                        vue_composant.changement_date_tache(tache_id,informations);

                    },
                    over: function(ev,ui){

                        var vue_mois = $(this).closest('table#mois').length > 0;

                        if(vue_mois){

                            $(this).css('box-shadow','inset 0 0 0 2px black');
                            return;
                        }

                        var ligne_tache = $(this).parent('tr');
                        var date = $(this).attr('data-date');

                        var nombre_demies_heures = ui.draggable.attr('data-tache-nombre-demies-heures');

                        setTimeout(function() {
                            for (var i = 1; i <= nombre_demies_heures; i++) {

                                var bloc_tache = ligne_tache.find('.date_' + date);
                                bloc_tache.attr('style','border-left:1px dashed black!important;' +
                                    'border-right:1px dashed black!important;' +
                                    (i== 1 ? 'border-top:1px dashed black!important;' : '')+
                                    (i== nombre_demies_heures ? 'border-bottom:1px dashed black!important;' : ''));

                                ligne_tache = ligne_tache.next('tr');
                            }

                        },0);

                    },
                    out:function(){

                        $('.droppable').not('table#mois .droppable').each(function(){
                            $(this).attr('style','');
                        });

                        $('table#mois .droppable').css('box-shadow','');
                    },
                });
            },

            calcule_placement_calendrier : function(){

                if(this.type_affichage_calendrier == 'mois')
                    return;

                var heure_debut = this.tranche_horaire.heure_debut;
                var heure_fin = this.tranche_horaire.heure_fin;

                // On retire une éventuelle hauteur de ligne appliquée lors d'un calcul précédent, pour repartir de la hauteur naturelle des lignes avant de la mesurer
                $('#div_calendrier tbody tr[heure]').css('height', '');

                var distance = ($('tr[heure="'+heure_fin+'"]').offset().top + $('tr[heure="'+heure_debut+'"]').height()) - $('tr[heure="'+heure_debut+'"]').offset().top;

                // Hauteur naturelle d'une ligne, utilisée pour positionner correctement le point de départ des tâches (voir tache.blade.php) : elle doit rester à jour
                // si les lignes sont ensuite agrandies ci-dessous, sinon les tâches se décalent visuellement de leur horaire réel.
                var hauteur_ligne_naturelle = $('tr[heure="'+heure_debut+'"]').height();

                var hauteur_souhaitee = distance + this.$refs.header_sticky.clientHeight;

                // Avec une granularité fine (5 / 10 min), le nombre de lignes augmente et la hauteur souhaitée peut dépasser l'espace restant à l'écran,
                // ce qui ajoute un scroll de page en plus du scroll interne du calendrier. On plafonne donc la hauteur à l'espace réellement disponible.
                var taille_ecran = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
                var hauteur_disponible = Math.abs(taille_ecran - document.getElementById('div_calendrier').getBoundingClientRect().top);

                // A l'inverse, si la plage sélectionnée est courte, la hauteur naturelle des lignes laisse un espace vide sous le calendrier :
                // on agrandit alors toutes les lignes pour que le calendrier occupe toute la hauteur disponible.
                if(hauteur_souhaitee < hauteur_disponible){

                    var facteur_agrandissement = (hauteur_disponible - this.$refs.header_sticky.clientHeight) / distance;

                    $('#div_calendrier tbody tr[heure]').each(function(){
                        $(this).height($(this).height() * facteur_agrandissement);
                    });

                    hauteur_ligne_naturelle *= facteur_agrandissement;

                    hauteur_souhaitee = hauteur_disponible;
                }

                this.$set(this,'hauteur_ligne_calendrier', hauteur_ligne_naturelle);
                this.$set(this,'height_div_calendrier', Math.min(hauteur_souhaitee, hauteur_disponible) +'px');

                setTimeout(() => {
                    $('#div_calendrier').scrollTop($('tr[heure="'+heure_debut+'"]').position().top - this.$refs.header_sticky.clientHeight);

                    this.$forceUpdate();
                },0);
            },

            fermer_modale_creation : function(){

                this.creation_tache = false;

                this.$nextTick(() => {
                    this.calcule_placement_calendrier();
                });
            },

            classes_tache : function(tache){

                var classes = ['bloc_tache_calendrier'];

                if(this.type_affichage_calendrier.includes('semaine'))
                    classes.push('affichage_semaine');

                if((tache.prive != 1 || tache.prive == 1 && tache.affectation == this.$root.moi.id) && tache.draggable)
                    classes.push('draggable');

                if(tache.type_element == 'demande_cp'){

                    if((tache.statut == 0 || tache.statut == null) && tache.valide_n1 == 1)
                        classes.push('conge_accepte_n1')
                    else if(tache.statut == 1)
                        classes.push('conge_accepte')
                    else
                        classes.push('conge_en_attente');
                }

                return classes.join(' ');
            },

            recuperer_tache_dom(cible_evenement){

                return cible_evenement.classList.contains('bloc_tache_calendrier') ? cible_evenement : cible_evenement.closest('.bloc_tache_calendrier')
            },

            recuperer_conteneur_tache_dom(cible_evenement, tache_dom){

                if(tache_dom.dataset.tacheJourneeEntiere)
                    return cible_evenement.closest('.conteneur_tache_journee_entiere');

                return cible_evenement.closest('.calendrier_taches') || cible_evenement.closest('.conteneur_taches_mois');
            },

			{!! management('tache')->methode_vuejs_affichage_title_tache() !!}

        },

        mounted: function() {

            @yield('donnees_pour_vuejs_mounted')
            @stack('donnees_pour_vuejs_mounted')

            // Etat transitoire du redimensionnement d'une tâche par ses extrémités (volontairement hors de data() pour ne pas le rendre réactif : il contient des références DOM/jQuery)
            this.redimensionnement = null;

            this.met_a_jour_les_dates(false,true);

            var vue_composant = this;

			this.$root.$on('changement_parametres_pour_calendrier', function(date) {

				vue_composant.date = date;
				vue_composant.met_a_jour_les_dates();

			});

            this.$on('changement_filtres',(valeurs) => {
                this.valeurs_filtres = valeurs;
                this.met_a_jour_les_dates();
            });

            this.$root.$on('tooltip_actualisation_apres_action',() => {
                this.actualisation_affichage();
            });

            this.$root.$on('tooltip_affichage_formulaire',(tache) => {

                this.ajouter_tache(null,null,tache);
            });

            this.$root.$on('tooltip_suppression_tache',(suppression_recurrence, tache_id) => {

                this.tache_id = tache_id;

                if(suppression_recurrence === null)
                    this.supprimer_tache();
                else
                    this.supprimer_recurrence(suppression_recurrence === 2 ? 1 : null);
            });

            $(document).on('click', function(e) {
                var target = $(e.target);
                var datepicker_class = ".day, .dow, .prev, .next, .month, .datepicker-months, .today, .datepicker-years, .year, .new, .clear, .datepicker-switch, .datepicker-days";
                if(!target.is($('#calendrier-header .css_conteneur_popover_filtre,#calendrier-header .css_conteneur_popover_filtre').find('*').addBack()) && !target.is(datepicker_class)) {
                    vue_composant.filtre_actif = null;
                }
            });

        },

        computed:{

            @yield('donnees_pour_vuejs_computed')
            @stack('donnees_pour_vuejs_computed')

            tranches_horaires_disponibles : function(){

                var granularite = parseInt(this.granularite);
                var heures = [];

                for(var minutes_totales = 0; minutes_totales < 24 * 60; minutes_totales += granularite){

                    var heure = Math.floor(minutes_totales / 60);
                    var minute = minutes_totales % 60;

                    heures.push(('0'+heure).slice(-2)+':'+('0'+minute).slice(-2));
                }

                return heures;
            },

            indicateur_taches_inferieurs : function() {

                this.scroll_indicateur;

                var taille_calendrier = $("#div_calendrier").offset().top + this.$refs.header_sticky.clientHeight;

                var indicateur_taches_inferieurs = {};

                for (date in this.agenda) {
                    indicateur_taches_inferieurs[date] = $('tbody .date_'+date+ ' .bloc_tache_calendrier ').filter(function () {
                        return ($(this).offset().top + $(this).height()) < taille_calendrier;
                    }).length;
                }

                return indicateur_taches_inferieurs;
            },

            indicateur_taches_superieurs : function(){

                this.scroll_indicateur;

                var taille_calendrier = $("#div_calendrier").offset().top + $("#div_calendrier").height();

                var indicateur_taches_superieurs = {};

                for (date in this.agenda) {
                    indicateur_taches_superieurs[date] = $('tbody .date_'+date+ ' .bloc_tache_calendrier ').filter(function () {
                        return $(this).offset().top + 1 >= taille_calendrier;
                    }).length;
                }

                return indicateur_taches_superieurs;
            },

            indicateur_taches_journee_entiere : function(){

                this.scroll_indicateur;

                var taille_ligne_journee_entiere = $("#calendrier_ligne_journee_entiere").offset().top + $("#calendrier_ligne_journee_entiere").height();

                var indicateur_taches_journee_entiere = {};

                for (date in this.agenda) {

                    indicateur_taches_journee_entiere[date] = $('#calendrier_ligne_journee_entiere .date_'+date+ ' .bloc_tache_calendrier ').filter(function () {

                        return $(this).offset().top + 1 >= taille_ligne_journee_entiere;
                    }).length;
                }

                return indicateur_taches_journee_entiere;
            },

        },
        watch: {

            @yield('donnees_pour_vuejs_watch')
            @stack('donnees_pour_vuejs_watch')

        },
        directives: {
            @yield('donnees_pour_vuejs_directives')
            @stack('donnees_pour_vuejs_directives')

        }
    });
</script>
