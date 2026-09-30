<script>
const calcul_tarif_sur_fiche_article_avec_marge = Vue.component('calcul-tarif-sur-fiche-article-avec-marge', {
    template: `<div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card mb-3">
                            <div class="card-header d-flex align-items-center">
                                <h4>
                                    @traduction('composants.calcul_tarif_sur_fiche_article_avec_marge.titre_modal')
                                </h4>
                                <span class="css_ajouter_element ml-auto" data-toggle="tooltip" data-placement="left" :title="$root.traduction('interface.modales.enregistrer')" @click="enregistre_tarif_via_calcul_par_marge">
                                    <i class="css_action_icon secondaire fa fa-fw fa-save"></i>
                                </span>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive css_form">
                                    <div class="row">
                                        <div class="col-md-6">@traduction('composants.calcul_tarif_sur_fiche_article_avec_marge.prix_achat') :</div>
                                        <div class="col-md-6"><input type="text" v-model="article.prix_d_achat" @change="mise_a_jour_prix_de_vente('prix_achat')" /></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">@traduction('composants.calcul_tarif_sur_fiche_article_avec_marge.prix_marge') ({!! maquette('devise_application_symbole') !!}) :</div>
                                        <div class="col-md-6"><input type="text" v-model="article.marge_devises" @change="mise_a_jour_prix_de_vente('marge_devises')" /></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">@traduction('composants.calcul_tarif_sur_fiche_article_avec_marge.prix_marge_vente') :</div>
                                        <div class="col-md-6"><input type="text" v-model="article.marge_pourcent" @change="mise_a_jour_prix_de_vente('marge_pourcent')" /></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">@traduction('composants.calcul_tarif_sur_fiche_article_avec_marge.prix_vente') :</div>
                                        <div class="col-md-6"><input type="text" v-model="article.tarif" @change="mise_a_jour_prix_de_vente()" /></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`,
            props:{

                article_id:0,
                article:{},

            },
            methods: {

                enregistre_tarif_via_calcul_par_marge() {

                    var vue_composant = this

                    // On affiche le loader
                    loading();

                    // on enregistre les infos du champ libre
                    $.post({

                        url: "/eden/element/article/" + vue_composant.article_id + "/enregistrer",
                        dataType: "json",
                        method: 'POST',
                        data: {

                            prix_d_achat: vue_composant.article.prix_d_achat,
                            marge_devises: vue_composant.article.marge_devises,
                            marge_pourcent: vue_composant.article.marge_pourcent,
                            tarif: vue_composant.article.tarif,
                        }
                    }).done(async function (donnees) {

                        // On retire le loader
                        loading(false);

                        if (donnees.retour !== true) {

                            await erreur(donnees.retour);
                            return;
                        }
                    });
                },

                // mise_a_jour_prix_de_vente_depuis_prix_vente: function () {
                //
                //     var vue_composant = this
                //
                //     // on recalcule les marges
                //     if (vue_composant.article.prix_d_achat != '' && vue_composant.article.prix_d_achat !== null && vue_composant.article.tarif != '' && vue_composant.article.tarif !== null) {
                //
                //         var marge_devises = vue_composant.article.tarif - vue_composant.article.prix_d_achat;
                //
                //         vue_composant.article.marge_devises = marge_devises.toFixed(2);
                //
                //         if (vue_composant.article.tarif != 0) {
                //
                //             var marge_pourcent = Math.round((vue_composant.article.tarif - vue_composant.article.prix_d_achat) / vue_composant.article.tarif * 10000) / 100;
                //
                //             vue_composant.article.marge_pourcent = marge_pourcent.toFixed(2);
                //         }
                //     }
                //
                //     return;
                // },
                //
                // mise_a_jour_prix_de_vente_depuis_marge_pourcent: function () {
                //
                //     var vue_composant = this
                //
                //     // on recalcule les marges
                //     if (vue_composant.article.prix_d_achat != '' && vue_composant.article.prix_d_achat !== null && vue_composant.article.marge_pourcent != '' && vue_composant.article.marge_pourcent !== null) {
                //
                //         /*
                //         // calcul via taux de marge
                //         var tarif = Math.round(vue_composant.article.prix_d_achat * (parseFloat(100) + parseFloat(vue_composant.article.marge_pourcent))) / 100;
                //         */
                //
                //         // calcul via taux de marque
                //         var tarif = Math.round(vue_composant.article.prix_d_achat * 100 / (100 - vue_composant.article.marge_pourcent) * 100) / 100;
                //
                //         vue_composant.article.tarif = tarif.toFixed(2);
                //
                //         vue_composant.mise_a_jour_prix_de_vente_depuis_prix_vente();
                //     }
                //
                //     return;
                // },
                //
                // mise_a_jour_prix_de_vente_depuis_marge_devises: function () {
                //
                //     var vue_composant = this
                //
                //     // on recalcule les marges
                //     if (vue_composant.article.prix_d_achat != '' && vue_composant.article.prix_d_achat !== null && vue_composant.article.marge_devises != '' && vue_composant.article.marge_devises !== null) {
                //
                //         var tarif = Math.round((parseFloat(vue_composant.article.prix_d_achat) + parseFloat(vue_composant.article.marge_devises)) * 100) / 100;
                //
                //         vue_composant.article.tarif = tarif.toFixed(2);
                //
                //         vue_composant.mise_a_jour_prix_de_vente_depuis_prix_vente();
                //     }
                //
                //     return;
                // },
                //
                // mise_a_jour_prix_de_vente_depuis_prix_achat: function () {
                //
                //     var vue_composant = this
                //
                //     // on recalcule le prix de vente final
                //     if (vue_composant.article.prix_d_achat != '' && vue_composant.article.prix_d_achat !== null && vue_composant.article.marge_pourcent != '' && vue_composant.article.marge_pourcent !== null) {
                //
                //         var tarif = Math.round(vue_composant.article.prix_d_achat * (parseFloat(100) + parseFloat(vue_composant.article.marge_pourcent))) / 100;
                //
                //         vue_composant.article.tarif = tarif.toFixed(2);
                //
                //         vue_composant.mise_a_jour_prix_de_vente_depuis_prix_vente();
                //     }
                //
                //     return;
                // },

                mise_a_jour_prix_de_vente(modification) {

                    var vue_composant = this;

                    var tarif = parseFloat(vue_composant.article.tarif);
                    var prix_d_achat = parseFloat(vue_composant.article.prix_d_achat);
                    var marge_pourcent = parseFloat(vue_composant.article.marge_pourcent);
                    var marge_devises = parseFloat(vue_composant.article.marge_devises);

                    if (modification == 'prix_achat') {

                        if (!isNaN(prix_d_achat)) {

                            if (!isNaN(marge_pourcent)) {

                                tarif = Math.round(prix_d_achat * 100 / (100 - marge_pourcent) * 100) / 100;

                            } else if (!isNaN(marge_devises)) {

                                tarif = Math.round((parseFloat(prix_d_achat) + parseFloat(marge_devises)) * 100) / 100;

                            }
                        }
                    } else if (modification == "marge_pourcent") {

                        if (!isNaN(marge_pourcent)) {

                            if (!isNaN(prix_d_achat)) {

                                if(marge_pourcent == 100)
                                    tarif = prix_d_achat;

                                else
                                    tarif = Math.round(prix_d_achat * 100 / (100 - marge_pourcent) * 100) / 100;

                            }
                        }
                    } else if (modification == "marge_devises") {

                        if (!isNaN(marge_devises)) {

                            if (!isNaN(prix_d_achat)) {

                                tarif = Math.round((parseFloat(prix_d_achat) + parseFloat(marge_devises)) * 100) / 100;

                            }

                        }
                    }

                    if (!isNaN(prix_d_achat) && !isNaN(tarif)) {

                        marge_devises = (tarif - prix_d_achat).toFixed(2);

                        if (tarif != 0) {

                            marge_pourcent = (Math.round((tarif - prix_d_achat) / tarif * 10000) / 100).toFixed(2);
                        }
                        else
                            marge_pourcent = 0;
                    }

                    if(!isNaN(tarif))
                        vue_composant.article.tarif = tarif;

                    if(!isNaN(marge_pourcent))
                        vue_composant.article.marge_pourcent = marge_pourcent;

                    if(!isNaN(marge_devises))
                        vue_composant.article.marge_devises = marge_devises;
                },
            },
        });
