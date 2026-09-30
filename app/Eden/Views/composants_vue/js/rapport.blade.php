<script>
    const rapport = Vue.component('rapport', {
        template: `
            <div :id="id_rapport" :ref="id_rapport" :class="'css_rapport_' + type_rapport +  ' css_rapport_' + id_rapport">

                <rapport-indicateur
                    v-if="['indicateur'].includes(type_rapport)"
                    v-bind="props_composant"
                    :actualisation_rapport="actualisation_rapport"
                ></rapport-indicateur>
                <div v-else class="row highcharts-light">
                    <div class="col-md-12">
                        <div
                            :class="classes_specifiques_conteneur_rapport"
                        >
                            <slot name="header">
                                <div class="card-header">

                                    <!-- les options -->
                                    <div class="pull-right" style="display: flex; float: right">
                                        <div class="css_rapport_filtre_et_options">
                                            <filtres :filtres="filtres" :valeurs_filtres="valeurs_filtres"></filtres>
                                        </div>

                                       <input
                                            v-for="(cle_tmp, valeur_tmp) in donnees_a_conserver"
                                            type="hidden"
                                            :class="'js_champ_' + id_rapport"
                                            :name="'donnees_a_conserver_' + id_rapport + '[' + cle_tmp + ']'"
                                            :value="valeur_tmp"
                                        />
                                    </div>
                                    <h4>
                                        <template v-if="rapport_libre && rapport_libre.index_traduction">
                                            @{{ $root.traduction(rapport_libre.index_traduction + '.titre') }}
                                        </template>
                                        <template v-else>
                                            @{{ titre_du_rapport }}
                                        </template>

                                        <template v-if="type_rapport == 'pdf'">
                                          <a href="#" class="css_ajouter_element"  @click="ouvrir_modal_abonnement" data-toggle="modal" :data-target="'#modal_abonner_rapport_' + id_rapport"><i class="fa fa-fw fa-bell"></i>
                                            @traduction('interface.listes.abonner')
                                          </a>
                                        </template>
                                        <slot name="option_abonnement"></slot>
                                    </h4>
                                </div>
                            </slot>

                            <div class="card-body">
                                <div class="css_actualisation_rapport js_actualisation_rapport">@traduction('rapport.divers.cliquez_ici_pour_actualiser_le_rapport')</div>
                                <div class="css_actualisation_rapport_en_cours js_actualisation_rapport_en_cours" v-show="actualisation_rapport">
                                  <img src="/eden/images/ajax_loader.gif" />
                                </div>
                                <div class="js_rapport css_rapport" style="overflow-x: auto" v-if="!actualisation_rapport">
                                    <slot name="contenu_rapport">
                                       <component v-if="composant" :is="composant" v-bind="props_composant"></component>
                                    </slot>
                                </div>
                           </div>
                        </div>
                    </div>
                </div>
            </div>
            `,
        props:{
            id_rapport: {
                type: String,
                default:'',
            },
            classes_supplementaires: {
                type: Object,
                default: () => {
                    return {};
                }
            },
            filtres_pour_fiche: {
                type: Object,
                default: () => {
                    return {};
                }
            }
        },
        data : () => {
            return {
                filtres: [],
                valeurs_filtres: [],
                rapport_libre: {},
                parametrage_rapport_libre: {},
                parametres_pour_vue: {},
                donnees_a_conserver: {},
                actualisation_rapport: false,
                html: undefined,
                type_rapport: 'histogramme',
                titre_du_rapport: 'Rapport',
                legendes: [],
                objectif: {},
                libelle_du_rapport: '',
                composant: '',
                props_composant: {},
                id_liste_libre: 0,
                liste_chargee: false,
                icone_dans_rapport: '',
                objectif_indicateur: '',
                objectif_atteint_indicateur: '',
                valeur_indicateur: '',
                modal_abonner_rapport: false,
                requete_rapport: false,
                @yield('data_rapport')
                @stack('data_rapport')
            }
        },
        mounted : function(){
            this.initialise_rapport();

            this.$on('changement_filtres',(nouvelles_valeurs) => {

                this.valeurs_filtres = nouvelles_valeurs;

                this.actualise_rapport();
            });
            @yield('mounted_rapport')
            @stack('mounted_rapport')
        },
        methods: {
            async initialise_rapport(parametres = {}) {
                this.actualisation_rapport = true;

                // on doit aller chercher le rapport en ajax
                $.post({

                    url: "/eden/rapport/ajax/"+this.id_rapport,
                    // dataType: "json",
                    data: {
                        'filtres': this.valeurs_filtres,
                        'filtres_pour_fiche': this.filtres_pour_fiche,
                        'initialisation' : true,
                    }
                }).done((donnees) => {

                    this.actualisation_rapport = false;
                    this.filtres = Object.values(donnees.options ?? {}) ?? [];
                    this.valeurs_filtres = donnees.valeurs_filtre ?? [];
                    this.rapport_libre = donnees.rapport_libre ?? {};
                    this.parametrage_rapport_libre = donnees.parametrage_rapport_libre ?? {};
                    this.parametres_pour_vue = donnees.parametres_pour_vue ?? {};
                    this.donnees_a_conserver = donnees.donnees_a_conserver ?? {};
                    this.legendes = donnees.legende ?? [];
                    this.objectif = donnees.parametrage_rapport_libre ? {
                        affichage_ligne_objectif: donnees.parametrage_rapport_libre.affichage_ligne_objectif ?? undefined,
                        afficher_objectif: donnees.parametrage_rapport_libre.afficher_objectif ?? undefined,
                        couleur_ligne_objectif: donnees.parametrage_rapport_libre.couleur_ligne_objectif ?? undefined,
                        texte_objectif: donnees.parametrage_rapport_libre.texte_objectif ?? undefined,
                        valeur_objectif: donnees.parametrage_rapport_libre.valeur_objectif ?? undefined,
                    } : {};
                    this.objectif_indicateur = donnees.objectif ?? undefined;
                    this.objectif_atteint_indicateur = donnees.objectif_atteint ?? undefined;
                    this.html = donnees.html ?? undefined;
                    this.icone_dans_rapport = this.parametres_pour_vue?.icone_dans_rapport ?? '';
                    this.valeur_indicateur = this.parametres_pour_vue?.valeur_indicateur ?? null;
                    this.type_rapport = donnees.type_rapport ?? this.rapport_libre.type_rapport ?? 'histogramme';
                    this.libelle_du_rapport = donnees.libelle_du_rapport ?? '';

                    if(this.parametres_pour_vue.props_composant)
                        this.$set(this, 'props_composant', this.parametres_pour_vue.props_composant);

                    if (['histogramme', 'courbe', 'diagramme_circulaire', 'graphique_funnel', 'carte'].includes(this.type_rapport) && !this.html) {
                        this.composant = "highcharts-";

                        if (['histogramme', 'courbe', 'carte'].includes(this.type_rapport))
                            this.composant += this.type_rapport;
                        else if (this.type_rapport == 'diagramme_circulaire')
                            this.composant += 'pie';
                        else
                            this.composant += 'funnel';

                    }
                    else if(this.type_rapport == 'pdf')
                        this.composant = "rapport-pdf";
                    else if(this.type_rapport == 'tableau')
                        this.composant = "rapport-tableau";
                    else if(this.html) {
                        this.$set(this, 'composant', {
                            template: `<div>${this.html}</div>`,
                        });
                    }

                    if(donnees.js)
                        eval(donnees.js);

                    this.$nextTick(() => {
                        this.$emit('actualisation_rapport_event');
                        this.$forceUpdate();
                        loading(false);
                    })

                });
            },

            actualise_rapport() {
                this.actualisation_rapport = true;

                if(this.requete_rapport !== false)
                    this.requete_rapport.abort();

                // on doit aller chercher le rapport en ajax
                this.requete_rapport = $.post({

                    url: "/eden/rapport/ajax/"+this.id_rapport,
                    // dataType: "json",
                    data: {
                        'filtres': this.valeurs_filtres,
                        'filtres_pour_fiche': this.filtres_pour_fiche,
                    }
                }).done((donnees) => {

                    this.requete_rapport = false;
                    this.actualisation_rapport = false;
                    this.valeurs_filtres = donnees.valeurs_filtre ?? [];
                    this.legendes = donnees.legende ?? [];
                    this.html = donnees.html ?? undefined;
                    this.valeur_indicateur = donnees.parametres_pour_vue?.valeur_indicateur ?? null;

                    if (['histogramme', 'courbe', 'diagramme_circulaire', 'graphique_funnel', 'pdf', 'carte','indicateur','tableau'].includes(this.type_rapport))
                        this.$set(this, 'props_composant', donnees.parametres_pour_vue.props_composant);
                    else if(this.html) {
                        this.$set(this, 'composant', {
                            template: `<div>${this.html}</div>`,
                        });
                    }

                    if(donnees.js)
                        eval(donnees.js);

                    this.$nextTick(() => {
                        this.$emit('actualisation_rapport_event');
                        this.$forceUpdate();
                        loading(false);
                    })

                });
            },

            ouvrir_modal_abonnement: function() {
                this.$emit('ouvrir_modal_abonnement', true);
            },

            @yield('methods_rapport')
            @stack('methods_rapport')
        },
        computed: {
            classes_specifiques_conteneur_rapport() {
                var classes = 'card mb-3';

                if(this.classes_supplementaires)
                    classes += Object.values(this.classes_supplementaires).join(' ');

                return classes;
            },
            @yield('computed_rapport')
            @stack('computed_rapport')
        },
    });
</script>
