<script>
const declinaisons = Vue.component('declinaisons', {
    template: `<div>
                    <div v-show="article.type_declinaison == 1 || article.type_declinaison == 2">

                        <div class="row">
                            <div class="col-md-12">
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h4>
                                            @traduction('composants.declinaisons.titre')
                                            <span class="css__lien" @click="article_declinaison_creer"><i class="fa fa-fw fa-plus-square"></i>@traduction('composants.declinaisons.nouvelle')</span>
                                        </h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                                                <thead>
                                                    <tr>
                                                        <th>@traduction('composants.declinaisons.id')</th>
                                                        <th>@traduction('composants.declinaisons.designations')</th>
                                                        <th>@traduction('composants.declinaisons.tarif')</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-show="articles_declinaisons.length == 0">
                                                        <td colspan="5">@traduction('composants.declinaisons.aucun_1')<span class="css__lien" @click="article_declinaison_creer">@traduction('composants.declinaisons.aucun_2')</span>@traduction('composants.declinaisons.aucun_3')</td>
                                                    </tr>
                                                    <tr v-for="article_declinaison in articles_declinaisons">
                                                        <td>
                                                            <span class="btn btn-mini btn-xs btn-default css_btn_action_theme" style="padding: 5px; border: 1px solid white; background: white; color: #6f6f6f !important;">
                                                                <span class="fa fa-search"  @click="article_declinaison_modifier(article_declinaison)" data-toggle="tooltip" :title="$root.traduction('interface.modales.afficher')"></span>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            @{{ article_declinaison.designation }}<br/>
                                                            <span v-if="article_declinaison.famille_declinaison_1 != null"><br/>@{{ article_declinaison.famille_declinaison_1 | affiche_famille_declinaison }} : @{{ article_declinaison.valeur_declinaison_1 }}</span>
                                                            <span v-if="article_declinaison.famille_declinaison_2 != null"><br/>@{{ article_declinaison.famille_declinaison_2 | affiche_famille_declinaison }} : @{{ article_declinaison.valeur_declinaison_2 }}</span>
                                                            <span v-if="article_declinaison.famille_declinaison_3 != null"><br/>@{{ article_declinaison.famille_declinaison_3 | affiche_famille_declinaison }} : @{{ article_declinaison.valeur_declinaison_3 }}</span>
                                                            <span v-if="article_declinaison.famille_declinaison_4 != null"><br/>@{{ article_declinaison.famille_declinaison_4 | affiche_famille_declinaison }} : @{{ article_declinaison.valeur_declinaison_4 }}</span>
                                                            <span v-if="article_declinaison.famille_declinaison_5 != null"><br/>@{{ article_declinaison.famille_declinaison_5 | affiche_famille_declinaison }} : @{{ article_declinaison.valeur_declinaison_5 }}</span>
                                                        </td>
                                                        <td>@{{ article_declinaison.tarif | montant }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal ajout article_declinaison -->
                    <div class="modal fade" id="modal_ajout_article_declinaison" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">

                                <div class="modal-header">
                                    <h5 class="modal-title">@traduction('composants.declinaisons.titre_modal')</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>

                                <div class="modal-body">
                                    @include('eden::formulaires.include.creation_declinaison')
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger" @click="article_declinaison_supprimer" v-show="article_declinaison.id != ''">@traduction('interface.modales.supprimer')</button>
                                    <button type="button" class="btn btn-primary" @click="article_declinaison_enregistrer">@traduction('interface.modales.enregistrer')</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`,
                props:{

                    article_id:0,
                    article: {},

                },
                data:function(){

                    return {

                        articles_declinaisons: "",
	                    article_declinaison: {id: '', designation:'', tarif:'', image:''},

                    }

                },

                methods:{
	                //article_declinaison creer

                    article_declinaison_creer: function() {

                        $('#modal_ajout_article_declinaison').modal('show');

                        // on réinitialise le contact
                        this.article_declinaison = {id: '', designation:'', tarif:'', image:''};
                    },


                    //article_declinaison modif

                    article_declinaison_modifier: function(article_declinaison) {

                        var vue_composant = this;

                        vue_composant.article_declinaison = article_declinaison;

                        $('#modal_ajout_article_declinaison').modal('show');
                    },

                    //article_declinaison enregistrer
                    article_declinaison_enregistrer: function() {

                        var vue_composant = this;

                        // On affiche le loader
                        loading();

                        // on enregistre les infos du champ libre
                        $.post({

                            url: "/eden/fiche/article/" + vue_composant.article_id + "/post/enregistrer_article_declinaison",
                            dataType: "json",
                            method: 'POST',
                            data: $('#formulaire_ajout_article_declinaison').serialize()
                        }).done(async function(donnees) {

                            // On retire le loader
                            loading(false);

                            if(donnees.retour !== true) {

                                await erreur(donnees.retour);
                                return;
                            }

                            $('#modal_ajout_article_declinaison').modal('hide');

                            // on actualise la liste
                            vue_composant.article_declinaison_actualiser();
                        });

                    },

                    //article_declinaison supprimer

                    article_declinaison_supprimer: async function() {

                        var vue_composant = this;

                        if(!await confirm_eden(vue_composant.$root.traduction('interface.modales.confirmation_suppression')))
                            return false;

                        loading(true);


                        // on fait un appel ajax pour supprimer
                        $.post({

                            url: "/eden/fiche/article/" + vue_composant.article_id + "/post/supprimer_article_declinaison",
                            dataType: "json",
                            data: {

                                id: vue_composant.$data.article_declinaison.id
                            }
                        }).done(async function(donnees) {

                            if(donnees.retour !== true) {

                                loading(false);

                                await erreur(donnees.retour);
                                return;
                            }

                            $('#modal_ajout_article_declinaison').modal('hide');

                            // on actualise la liste
                            vue_composant.article_declinaison_actualiser();
                        });
                    },

                    //article_declinaison actualiser

                    article_declinaison_actualiser: function() {

                        var vue_composant = this;

                        loading(true);

                        $.get({

                            url: 'eden/fiche/article/' + vue_composant.article_id + '/articles_declinaisons',
                            dataType: "json"
                        }).done(function(articles_declinaisons) {

                            // On retire le loader
                            loading(false);

                            vue_composant.articles_declinaisons = articles_declinaisons;
                        });
                    },
                },



                mounted: function() {

                    this.article_declinaison_actualiser();
                },

                watch: {

                    article_id: function(nouvelle_valeur, ancienne_valeur) {

                        this.article_declinaison_actualiser();

                    }

                }
            });
