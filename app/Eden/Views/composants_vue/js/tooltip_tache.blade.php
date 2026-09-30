<script>
    const tooltip_tache = Vue.component('tooltip-tache', {
        template: `<div class="tooltip-vue-tache" @dblclick="$event.stopPropagation()">
                <h3 v-html="tache.label"></h3>
                <div class="tooltip-vue-tache-horaire">
                    <template v-if="affiche_date(tache.date_de_debut, 'YYYY-MM-DD') === affiche_date(tache.date_de_fin, 'YYYY-MM-DD')">
                        <p>{{ traduction('composant.affichage_calendrier.le') }} <span v-text="affiche_date(tache.date_de_debut, 'YYYY-MM-DD')"></span></p>
                        <p>{{ traduction('composant.affichage_calendrier.de') }} <span v-text="affiche_date(tache.date_de_debut, 'HH:mm')"></span> {{ traduction('composant.affichage_calendrier.a') }} <span v-text="affiche_date(tache.date_de_fin, 'HH:mm')"></span></p>
                    </template>
                    <template v-else>
                        <p>{{ traduction('composant.affichage_calendrier.du') }} <span v-text="affiche_date(tache.date_de_debut, 'YYYY-MM-DD HH:mm')"></span></p>
                        <p>{{ traduction('composant.affichage_calendrier.au') }} <span v-text="affiche_date(tache.date_de_fin, 'YYYY-MM-DD HH:mm')"></span></p>
                    </template>
                </div>
                <template v-if="tache.commentaire_title">
                    <hr/>
                    <p class="tooltip-vue-tache-description" v-html="tache.commentaire_title.length > 50 ? tache.commentaire_title.substring(0,50) + '...' : tache.commentaire_title"></p>
                </template>
                <hr/>

                <template v-if="tache.organisateur && (tache.participant || tache.participants)">
                    <div class="tooltip-vue-tache-participants">
                        <div class="tooltip-vue-tache-affectation" v-if="tache.organisateur != undefined">
                            <img :src="tache.organisateur.id | affiche_utilisateur_avatar"/>
                        </div>
                        <div class="tooltip-vue-tache-invite" v-if="tache.participant && tache.organisateur != undefined && tache.organisateur.id !== $root.moi.id && tache.affectation === $root.moi.id">
                            <span v-text="$root.traduction('composant.tooltip_tache.vous_a_invite', null, [$root.$options.filters.affiche_utilisateur(tache.organisateur.id)])"></span>
                        </div>
                        <div class="tooltip-vue-tache-invite" v-else-if="tache.participant && (tache.organisateur != undefined || tache.adresse_email_organisateur != undefined)">
                            <span v-text="$root.traduction('composant.tooltip_tache.a_invite', null, [tache.organisateur != undefined ? $root.$options.filters.affiche_utilisateur(tache.organisateur.id) : tache.adresse_email_organisateur, $root.$options.filters.affiche_utilisateur(tache.affectation)])"></span>
                        </div>
                        <div class="tooltip-vue-tache-organisateur" v-else-if="!tache.participant && tache.organisateur != undefined && tache.organisateur.id === $root.moi.id">
                            <span v-text="$root.traduction('composant.tooltip_tache.organisateur')"></span>
                        </div>
                        <div class="tooltip-vue-tache-organisateur" v-else-if="tache.organisateur != undefined">
                            <span v-text="$root.traduction('composant.tooltip_tache.est_l_organisateur', null, [$root.$options.filters.affiche_utilisateur(tache.organisateur.id)])"></span>
                        </div>
                    </div>
                    <div class="tooltip-vue-tache-statuts">
                        <span v-text="phrase_statuts"></span>
                    </div>
                </template>
                <div class="tooltip-vue-tache-liste-utilisateur" v-else>
                    <div class="tooltip-vue-tache-affectation">
                        <img :src="tache.affectation | affiche_utilisateur_avatar()"/>
                        <span>@{{ tache.affectation | affiche_utilisateur }}</span>
                    </div>
                    <div class="tooltip-vue-tache-autres-affectations" v-if="tache.autres_affectations_groupe && typeof tache.autres_affectations_groupe === 'object' && !Array.isArray(tache.autres_affectations_groupe)">
                        <span v-text="$root.traduction('formulaire.tache.multi_affectation.autres_affectations') + ' : ' + Object.values(tache.autres_affectations_groupe).join(', ')"></span>
                    </div>
                </div>
                <template v-if="tache.participant && !tache.annulee && tache.affectation === $root.moi.id">
                    <hr/>
                    @include('eden::composants_vue.js.include.tache.boutons_statut_participants')
                </template>
                <template v-else-if="tache.affectation === $root.moi.id">
                    <hr/>
                    <div class="tooltip-vue-tache-boutons">
                        <button type="button" @click="afficher_formulaire()" class="btn btn-primary css_btn_responsive">
                            <span v-text="$root.traduction('interface.listes.afficher')"></span>
                        </button>
                        <div class="dropdown" v-if="tache !== null && tache.parent_id > 0 && (!tache.participant || tache.annulee)">
                            <div class="btn btn-danger" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">@traduction('interface.modales.supprimer')</div>
                            <div class="dropdown-menu">
                                <div class="dropdown-save">
                                    <span class="dropdown-item" @click="supprimer_tache()">@traduction('composant.affichage_calendrier.cet_evenement')</span>
                                    <span class="dropdown-item" @click="supprimer_tache(1)">@traduction('composant.affichage_calendrier.evenements_suivants')</span>
                                    <span class="dropdown-item" @click="supprimer_tache(2)">@traduction('composant.affichage_calendrier.toute_la_serie')</span>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-danger" v-else-if="tache.id && (!tache.participant || tache.annulee)" @click="supprimer_tache()">@traduction('interface.modales.supprimer')</button>
                    </div>
                </template>
                
            </div>
        `,
        props: {
            tache: {},
        },
        data: function() {

            return {

                @stack('donnees_pour_vuejs_data')
            }
        },
        methods: {

            actualisation_affichage: function(){

                this.$root.$emit('tooltip_actualisation_apres_action');
            },

            afficher_formulaire: function(){

                this.$root.$emit('tooltip_affichage_formulaire', this.tache);
            },

            supprimer_tache: function(suppression_recurrence = null){

                this.$root.$emit('tooltip_suppression_tache', suppression_recurrence, this.tache.id);
            },

            affiche_date:function(valeur,format){

                return moment(valeur).format(format)
            },

            @stack('donnees_pour_vuejs_methods')
        },
        computed: {

            phrase_statuts(){

                var nb_non_repondus = 0;
                var statuts = Object.groupBy(this.tache.participants, (participant) => {

                    return participant.statut_participant;
                })

                var phrase = "";

                if(statuts[1] != undefined && statuts[1].length > 0)
                    phrase += this.$root.traduction('composant.tooltip_tache.acceptes', null, [statuts[1].length]);

                if(statuts[2] != undefined && statuts[2].length > 0)
                    phrase += this.$root.traduction('composant.tooltip_tache.refuses', null, [statuts[2].length]);

                if(statuts[3] != undefined && statuts[3].length > 0)
                    phrase += this.$root.traduction('composant.tooltip_tache.provisoires', null, [statuts[3].length]);

                if(statuts[0] != undefined && statuts[0].length > 0)
                    nb_non_repondus += statuts[0].length;

                if(statuts[null] != undefined && statuts[null].length > 0)
                    nb_non_repondus += statuts[null].length;

                if(nb_non_repondus > 0)
                    phrase += this.$root.traduction('composant.tooltip_tache.non_repondus', null, [nb_non_repondus]);

                return phrase;
            }
        },
    });
</script>