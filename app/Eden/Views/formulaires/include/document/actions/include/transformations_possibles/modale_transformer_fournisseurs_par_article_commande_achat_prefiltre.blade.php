<template v-if="modale_transformer_fournisseurs_par_article_commande_achat_prefiltre">
    <transition name="modal">
        <div class="modal-mask">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">@traduction('document.actions.transformations_possibles.fournisseurs_par_article')</h5>
                        <button type="button" class="close" @click="modale_transformer_fournisseurs_par_article_commande_achat_prefiltre = false" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <h5>@traduction('document.actions.transformations_possibles.regroupements')</h5><br>
                        <table style="width: 100%;">
                            <template v-for="regroupement in regroupements">
                                <tr>
                                    <td colspan="6" style="text-align: center; background-color: #d1d1d1;font-weight: bold;text-transform: uppercase;cursor: pointer" @click="ajouter_supprimer_regroupement_etape_2(regroupement)" v-if="regroupement.etape_2 == true">@{{ regroupement.nom }}</td>
                                    <td colspan="6" style="text-align: center; font-weight: bold;text-transform: uppercase;cursor: pointer;" @click="ajouter_supprimer_regroupement_etape_2(regroupement)" v-else>@{{ regroupement.nom }}</td>
                                </tr>
                            </template>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="modale_transformer_fournisseurs_par_article_commande_achat_prefiltre = false">@traduction('interface.modales.annuler')</button>
                        <button role="button" class="btn btn-secondary" @click="transformation_commande_vente_vers_achat(true)">@traduction('document.actions.transformations_possibles.suivant')</button>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>

<template v-if="modale_transformer_fournisseurs_par_article_commande_achat_prefiltre_etape_2">
    <transition name="modal">
        <div class="modal-mask">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">@traduction('document.actions.transformations_possibles.fournisseurs_par_article')</h5>
                        <button type="button" class="close" @click="modale_transformer_fournisseurs_par_article_commande_achat_prefiltre_etape_2 = false" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('document.transformer_fournisseur_avec_articles', [$management->_type_element, $management->modele->id, 'commande_achat']) }}" id="document_transformer_fournisseur_avec_articles" method="post" class="css_form" onsubmit="return false;">

                        <div class="alert alert-success" v-for="retour in document_transformer_fournisseur_avec_articles_succes" v-html="retour"></div>
                        <div class="alert alert-danger" v-for="retour in document_transformer_fournisseur_avec_articles_echecs" v-html="retour"></div>

                        <div class="modal-body" style="font-size: 13px;">
                            <table style="width: 100%;">
                                <tr>
                                    <td><b>@traduction('document.actions.transformations_possibles.article')</b></td>
                                    <td><b>@traduction('document.actions.transformations_possibles.quantite_vendue')</b></td>
                                    <td><b>@traduction('document.actions.transformations_possibles.fournisseurs')</b></td>
                                    <td><b>@traduction('document.actions.transformations_possibles.articles')</b></td>
                                    <td><b>@traduction('document.actions.transformations_possibles.quantite_commandee')</b></td>
                                    <td><b>@traduction('document.actions.transformations_possibles.commandes_existantes')</b></td>
                                    <td><b>@traduction('document.actions.transformations_possibles.stock_actuel')</b></td>
                                </tr>
                                <template v-for="regroupement in regroupements" v-if="regroupement.etape_2 == true">
                                    <tr>
                                        <td colspan="6" style="text-align: center; background-color: #d1d1d1;font-weight: bold;text-transform: uppercase;">@{{ regroupement.nom }}</td>
                                    </tr>
                                    <tr v-for="(infos_fournisseur, index) in fournisseurs_par_article" v-if="infos_fournisseur.regroupement_id == regroupement.id && infos_fournisseur.article">
                                        <td>@{{ infos_fournisseur.article.designation }}
                                            <span v-if="infos_fournisseur.designation && infos_fournisseur.article.designation != infos_fournisseur.designation" v-html="'(' + infos_fournisseur.designation + ')'"></span>
                                        </td>
                                        <td>@{{ infos_fournisseur.quantite.toFixed(2) }} @{{ infos_fournisseur.unite }}</td>
                                        <td style="padding-right: 5px;">
                                            <select :name="'commandes_fournisseur['+index+'][fournisseur_selectionne]'" v-model="infos_fournisseur.fournisseur_selectionne">
                                                <option :value="info_fournisseur.fournisseur_id" v-for="info_fournisseur in infos_fournisseur.tarifs_par_fournisseurs">
                                                    @{{ info_fournisseur.fournisseur }}
                                                </option>
                                            </select>
                                        </td>
                                        <td style="padding-right: 5px;">
                                            <select :name="'commandes_fournisseur['+index+'][article_fournisseur]'" v-model="infos_fournisseur.conditionnement_selectionne">
                                                <template v-for="info_fournisseur in infos_fournisseur.tarifs_par_fournisseurs" v-if="info_fournisseur.fournisseur_id == infos_fournisseur.fournisseur_selectionne">
                                                    <option :value="article_fournisseur.id_article_fournisseur" v-for="article_fournisseur in info_fournisseur.articles" v-if="article_fournisseur != undefined">
                                                        @{{ article_fournisseur.affichage }}
                                                    </option>
                                                </template>
                                            </select>
                                        </td>
                                        <td>

                                            <div class="css_saisie_articles_sur_document_input_group">
                                                <input type="text" class="css_input_article_document css_saisie_articles_sur_document_input_input" :name="'commandes_fournisseur['+index+'][quantite_commandee]'" :disabled="commande_frs_depuis_cmd_client_article_selectionne(infos_fournisseur)" />
                                                <span class="css_saisie_articles_sur_document_input_texte_droite" style="background: #eee;border: 1px solid #d4d4d4;border-left: 0px;margin-right: 5px;">@{{ affiche_conditionnement_selectionne_pour_commande_frs(infos_fournisseur) }}</span>
                                            </div>
                                        </td>
                                        <td style="padding-right: 5px;">
                                            <select :name="'commandes_fournisseur['+index+'][commande]'"  :disabled="commande_frs_depuis_cmd_client_article_selectionne(infos_fournisseur)">
                                                <option value="nouvelle_commande" v-html="traduction('document.actions.transformations_possibles.nouvelle_commande')"></option>
                                                <template v-for="info_fournisseur in infos_fournisseur.tarifs_par_fournisseurs" v-if="info_fournisseur.fournisseur_id == infos_fournisseur.fournisseur_selectionne">

                                                    <option :value="commande.id" v-for="commande in info_fournisseur.commandes">@traduction('document.actions.transformations_possibles.commande_du') @{{ commande.date }}</option>

                                                </template>
                                            </select>
                                        </td>
                                        <td>@{{ infos_fournisseur.stock_actuel }} @{{  infos_fournisseur.unite }}</td>

                                        <input type="hidden" :name="'commandes_fournisseur['+index+'][id_ligne_source]'" :value="infos_fournisseur.id_ligne_source" />
                                        <input type="hidden" :name="'commandes_fournisseur['+index+'][designation]'" :value="infos_fournisseur.designation" />
                                        <input type="hidden" :name="'commandes_fournisseur['+index+'][prix_achat]'" :value="infos_fournisseur.prix_achat" />
                                    </tr>
                                </template>
                                <tr>
                                    <td colspan="6" style="text-align: center; background-color: #d1d1d1;font-weight: bold;text-transform: uppercase;">@traduction('document.actions.transformations_possibles.hors_regroupement')</td>
                                </tr>
                                <tr v-for="(infos_fournisseur, index) in fournisseurs_par_article" v-if="infos_fournisseur.regroupement_id == null">
                                    <td>@{{ infos_fournisseur.article.designation }}
                                        <span v-if="infos_fournisseur.designation && infos_fournisseur.article.designation != infos_fournisseur.designation" v-html="'(' + infos_fournisseur.designation + ')'"></span>
                                    </td>
                                    <td>@{{ infos_fournisseur.quantite }} @{{ infos_fournisseur.unite }}</td>
                                    <td style="padding-right: 5px;">
                                        <select :name="'commandes_fournisseur['+index+'][fournisseur_selectionne]'" v-model="infos_fournisseur.fournisseur_selectionne">
                                            <option :value="info_fournisseur.fournisseur_id" v-for="info_fournisseur in infos_fournisseur.tarifs_par_fournisseurs">
                                                @{{ info_fournisseur.fournisseur }}
                                            </option>
                                        </select>
                                    </td>
                                    <td style="padding-right: 5px;">
                                        <select :name="'commandes_fournisseur['+index+'][article_fournisseur]'" v-model="infos_fournisseur.conditionnement_selectionne">
                                            <template v-for="info_fournisseur in infos_fournisseur.tarifs_par_fournisseurs" v-if="info_fournisseur.fournisseur_id == infos_fournisseur.fournisseur_selectionne">
                                                <option :value="article_fournisseur.id_article_fournisseur" v-for="article_fournisseur in info_fournisseur.articles" v-if="article_fournisseur != undefined">
                                                    @{{ article_fournisseur.affichage }}
                                                </option>
                                            </template>
                                        </select>
                                    </td>
                                    <td>

                                        <div class="css_saisie_articles_sur_document_input_group">
                                            <input type="text" class="css_input_article_document css_saisie_articles_sur_document_input_input" :name="'commandes_fournisseur['+index+'][quantite_commandee]'" :disabled="commande_frs_depuis_cmd_client_article_selectionne(infos_fournisseur)" />
                                            <span class="css_saisie_articles_sur_document_input_texte_droite" style="background: #eee;border: 1px solid #d4d4d4;border-left: 0px;margin-right: 5px;">@{{ affiche_conditionnement_selectionne_pour_commande_frs(infos_fournisseur) }}</span>
                                        </div>
                                    </td>
                                    <td style="padding-right: 5px;">
                                        <select :name="'commandes_fournisseur['+index+'][commande]'"  :disabled="commande_frs_depuis_cmd_client_article_selectionne(infos_fournisseur)">
                                            <option value="nouvelle_commande" v-html="traduction('document.actions.transformations_possibles.nouvelle_commande')"></option>
                                            <template v-for="info_fournisseur in infos_fournisseur.tarifs_par_fournisseurs" v-if="info_fournisseur.fournisseur_id == infos_fournisseur.fournisseur_selectionne">

                                                <option :value="commande.id" v-for="commande in info_fournisseur.commandes">@traduction('document.actions.transformations_possibles.commande_du') @{{ commande.date }}</option>

                                            </template>
                                        </select>
                                    </td>
                                    <td>@{{ infos_fournisseur.stock_actuel }} @{{  infos_fournisseur.unite }}</td>

                                    <input type="hidden" :name="'commandes_fournisseur['+index+'][id_ligne_source]'" :value="infos_fournisseur.id_ligne_source" />
                                    <input type="hidden" :name="'commandes_fournisseur['+index+'][designation]'" :value="infos_fournisseur.designation" />
                                    <input type="hidden" :name="'commandes_fournisseur['+index+'][prix_achat]'" :value="infos_fournisseur.prix_achat" />
                                </tr>
                            </table>

                        </div>
                        <div class="modal-footer">

                            <button type="button" class="btn btn-secondary" @click="modale_transformer_fournisseurs_par_article_commande_achat_prefiltre_etape_2 = false">@traduction('interface.modales.annuler')</button>
                            <button role="button" class="btn btn-secondary" @click="transformation_commande_vente_vers_achat(false)">@traduction('interface.modales.precedent')</button>
                            <button role="button" class="btn btn-secondary" @click="document_transformer_fournisseur_avec_articles()">@traduction('document.actions.transformations_possibles.transformer')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </transition>
</template>

@push('donnees_pour_vuejs_data')

    modale_transformer_fournisseurs_par_article_commande_achat_prefiltre : false,
    modale_transformer_fournisseurs_par_article_commande_achat_prefiltre_etape_2 : false,
    regroupements: [],
@endpush

@push('donnees_pour_vuejs_methods')

    ajouter_supprimer_regroupement_etape_2 : function(regroupement, forcer_valeur = "non") {

        if(forcer_valeur != "non"){
            regroupement.etape_2 = forcer_valeur;
        }
        else if(regroupement.etape_2 == undefined){

            regroupement.etape_2 = true;
        }
        else if(regroupement.etape_2 == true){

            regroupement.etape_2 = false;
        }
        else{

            regroupement.etape_2 = true;
        }

        {{-- On va voir si il le regroupement est regroupement mère, si oui, on va les selectionner / désélectionner aussi --}}
        this.regroupements.forEach(function(regroupement_boucle){

            if(regroupement.id == regroupement_boucle.regroupement_id)
                vue_instance.ajouter_supprimer_regroupement_etape_2(regroupement_boucle, regroupement.etape_2);
        });

        vue_instance.$forceUpdate();
        return;
    },

    transformation_commande_vente_vers_achat : function(premiere_etape) {

        this.modale_transformer_fournisseurs_par_article_commande_achat_prefiltre = premiere_etape != true;
        this.modale_transformer_fournisseurs_par_article_commande_achat_prefiltre_etape_2 = premiere_etape == true;
    },
@endpush
