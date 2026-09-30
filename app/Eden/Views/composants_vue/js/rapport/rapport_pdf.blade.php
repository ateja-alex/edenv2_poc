<script>
    const rapport_pdf = Vue.component('rapport-pdf', {
        template: `
          <div>
            <iframe :src="url_pdf" width='90%' style='margin: 0% 5%;min-height: 75vh;' align='middle'></iframe>

            <template v-if="modal_abonner_rapport">
                <transition name="modal">
                  <div class="modal-mask">
                    <div class="modal-dialog modal-lg" role="modal">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title">@traduction('interface.listes.mabonner')</h5>
                          <button type="button" class="close" @click="fermer_modal">
                            <span aria-hidden="true">&times;</span>
                          </button>
                        </div>
                        <div class="modal-body css_form">
                            <p>
                                @traduction('interface.listes.mabonner_pdf_par_mail')
                            </p>

                            <div class="row">
                                <p class="col-sm-4">
                                    @traduction('interface.listes.recurrence')
                                </p>
                                <p class="col-sm-8">
                                    <select id="'abonnement_frequence_rapport_' + id_rapport" v-model="abonnement.frequence">
                                        <option value="jour">@traduction('interface.listes.tous_les_jours')</option>
                                        <option value="semaine">@traduction('interface.listes.toutes_les_semaines')</option>
                                        <option value="mois">@traduction('interface.listes.tous_les_mois')</option>
                                        <option value="annee">@traduction('interface.listes.tous_les_ans')</option>
                                    </select>
                                </p>
                            </div>

                            <div class="row" v-if="abonnement.frequence == 'semaine'">
                                <p class="col-sm-4">
                                    @traduction('interface.listes.jour')
                                </p>
                                <p class="col-sm-8">
                                    <select v-model="abonnement.jour_semaine">
                                            <option value="Lundi">{{ \App\Eden\Variables::jours(1) }}</option>
                                            <option value="Mardi">{{ \App\Eden\Variables::jours(2) }}</option>
                                            <option value="Mercredi">{{ \App\Eden\Variables::jours(3) }}</option>
                                            <option value="Jeudi">{{ \App\Eden\Variables::jours(4) }}</option>
                                            <option value="Vendredi">{{ \App\Eden\Variables::jours(5) }}</option>
                                            <option value="Samedi">{{ \App\Eden\Variables::jours(6) }}</option>
                                            <option value="Dimanche">{{ \App\Eden\Variables::jours(7) }}</option>
                                    </select>
                                </p>
                            </div>

                            <div class="row" v-if="abonnement.frequence == 'mois'">
                                <p class="col-sm-4">
                                    @traduction('interface.listes.jour')
                                </p>
                                <p class="col-sm-8">
                                    <select v-model="abonnement.jour_mois">
                                       <option v-for="i in 31" :value="i" v-html="i"></option>
                                        <option value="dernier jour">@{{ traduction('interface.listes.dernier_jour_du_mois') }}</option>
                                    </select>
                                </p>
                            </div>

                            <div class="row" v-if="abonnement.frequence == 'annee'">
                                <p class="col-sm-4">
                                    @traduction('interface.listes.jour')
                                </p>
                                <p class="col-sm-2">
                                    <select v-model="abonnement.jour_annee">
                                      <option v-for="i in 31" :value="i" v-html="i"></option>
                                    </select>
                                </p>
                                <p class="col-sm-6">
                                    <select v-model="abonnement.mois_annee">
                                        <option value="Janvier">{{ \App\Eden\Variables::mois_de_lannee_format_complet_majuscule(1) }}</option>
                                        <option value="Février">{{ \App\Eden\Variables::mois_de_lannee_format_complet_majuscule(2) }}</option>
                                        <option value="Mars">{{ \App\Eden\Variables::mois_de_lannee_format_complet_majuscule(3) }}</option>
                                        <option value="Avril">{{ \App\Eden\Variables::mois_de_lannee_format_complet_majuscule(4) }}</option>
                                        <option value="Mai">{{ \App\Eden\Variables::mois_de_lannee_format_complet_majuscule(5) }}</option>
                                        <option value="Juin">{{ \App\Eden\Variables::mois_de_lannee_format_complet_majuscule(6) }}</option>
                                        <option value="Juillet">{{ \App\Eden\Variables::mois_de_lannee_format_complet_majuscule(7) }}</option>
                                        <option value="Août">{{ \App\Eden\Variables::mois_de_lannee_format_complet_majuscule(8) }}</option>
                                        <option value="Septembre">{{ \App\Eden\Variables::mois_de_lannee_format_complet_majuscule(9) }}</option>
                                        <option value="Octobre">{{ \App\Eden\Variables::mois_de_lannee_format_complet_majuscule(10) }}</option>
                                        <option value="Novembre">{{ \App\Eden\Variables::mois_de_lannee_format_complet_majuscule(11) }}</option>
                                        <option value="Décembre">{{ \App\Eden\Variables::mois_de_lannee_format_complet_majuscule(12) }}</option>
                                    </select>
                                </p>
                            </div>

                            <div class="row">
                                <p class="col-sm-4">
                                    @traduction('interface.listes.heure')
                                </p>
                                <p class="col-sm-8">
                                    <select v-model="abonnement.heure">
                                      <option v-for="i in 24" :value="i" v-html="i + 'h'"></option>
                                    </select>
                                </p>
                            </div>

                            <div class="row">
                                <p class="col-sm-4">
                                    @traduction('interface.listes.nom')
                                </p>
                                <p class="col-sm-8">
                                    <input type="text" v-model="abonnement.nom">
                                </p>
                            </div>

                        </div>
                        <div class="modal-footer">
                          <a href="{{ route('base_eden.liste.index', ['rapport_abonnement'], false) }}" style="font-size:14px;">@traduction('interface.listes.voir_mes_abonnements')</a>
                          <button type="button" class="btn btn-secondary" @click="fermer_modal">@traduction('interface.listes.fermer')</button>
                          <button type="button" class="btn btn-primary" @click="eden_abonner_rapport()">@traduction('interface.listes.enregistrer')</button>
                        </div>
                      </div>
                    </div>
                  </div>
                </transition>
            </template>
          </div>
        `,
        props: {
            id_rapport: {
                type: String,
                default: '',
            },
            titre: {
                type: String,
                default: 'Sans titre',
            },
            url_pdf: {
                type: String,
                default: '',
            },
        },
        data() {
            return {
                abonnement: {
                    type:'rapport',
                    frequence: 'semaine',
                    nom: this.titre,
                },
                modal_abonner_rapport: false,
            }
        },
        mounted: function() {
            this.$parent.$on('ouvrir_modal_abonnement',() => {
                this.modal_abonner_rapport = true;
            });
        },
        methods: {
            eden_abonner_rapport: function() {

                loading(true);

                var abonnement = this.abonnement;

                $.ajax({

                    url: "/eden/rapport/" + this.id_rapport + "/abonnement/"+ abonnement.frequence,
                    method:'post',
                    data:abonnement,

                }).done((data) => {

                    loading(false);
                    this.fermer_modal();

                });
            },
            fermer_modal: function () {
                this.modal_abonner_rapport = false;
            }
        },
    });
</script>
