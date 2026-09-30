<div class="row" id="preselection_des_articles" style="margin: 20px;border-top:solid 1px #666;padding-top:15px;">

    <div class="col-md-12" v-if="fournisseur_articles.id > 0 && affichage_articles_fournisseur_pret ">

        <div class="row">
            <div class="col-md-12">
                <h4>@traduction('document.blocs.saisie_des_articles.articles_fournisseur') : @{{ fournisseur.modele.chaine_affichage }}</h4>
            </div>
        </div>
        <div class="row">
            <div class="col-md-2">
                @traduction('document.blocs.saisie_des_articles.famille_articles') :
            </div>
            <div class="col-md-2">
                <select v-model="filtre_famille_article_fournisseur">
                    <option value="0">{{ traduction('document.blocs.saisie_des_articles.toutes') }}</option>
                    <option v-for="famille in articles_via_fournisseur.familles" :value="famille.id">@{{ famille.nom }}</option>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col-md-2">
                @traduction('document.blocs.saisie_des_articles.afficher_entrepot') :
            </div>
            <div class="col-md-2">
                <select v-model="filtre_entrepot_article_fournisseur" @change="fournisseur_articles_select()">
                    <option value="0">{{ traduction('document.blocs.saisie_des_articles.tous') }}</option>
                    @foreach(modele('entrepot')->orderBy('nom')->get() as $entrepot)
                        <option :value="{{ $entrepot->id }}">{{ $entrepot->nom }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <input class='css_input_recherche_liste js_input_recherche_liste' :placeholder="traduction('interface.placeholder.recherche_globale_sur_erp')" style="padding-left: 5px" type="text" v-model="input_recherche_articles_via_fournisseur" />
            </div>
        </div>
        <div class="row" style="margin-top: 10px;">

            <div class="col-md-12" v-for="famille in articles_via_fournisseur_familles" v-if="articles_famille_du_fournisseur_correspondant_a_la_recherche(famille).length" style="padding-top:10px;">
                <div class="row">
                    <div class="col-md-7">

                    </div>
                    <div class="col-md-5">
                        <div class="css_form_ligne_titre" style="text-align: center; padding: 0px;">
                            @traduction('document.blocs.saisie_des_articles.stock')
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="css_form_ligne_titre">
                            @{{ famille.nom }}
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="css_form_ligne_titre">
                            @traduction('document.blocs.saisie_des_articles.pu')
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="css_form_ligne_titre">
                            @traduction('document.blocs.saisie_des_articles.quantite')
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="css_form_ligne_titre">
                            @traduction('document.blocs.saisie_des_articles.total')
                        </div>
                    </div>
                    <div class="col-md-5" style="display: flex;justify-content: space-around;">
                        <div class="css_form_ligne_titre" style="width: 100%;font-size: 11px; text-align: center;padding: 10px 15px;">
                            @traduction('document.blocs.saisie_des_articles.actuel')
                        </div>
                        <div class="css_form_ligne_titre" style="width: 100%;font-size: 11px; text-align: center;padding: 10px 15px;">
                            @traduction('document.blocs.saisie_des_articles.reserve')
                        </div>
                        <div class="css_form_ligne_titre" style="width: 100%;font-size: 11px; text-align: center;padding: 10px 15px;">
                            @traduction('document.blocs.saisie_des_articles.disponible')
                        </div>
                        <div class="css_form_ligne_titre" style="width: 100%;font-size: 11px; text-align: center;padding: 10px 15px;">
                            @traduction('document.blocs.saisie_des_articles.a_recevoir')
                        </div>
                        <div class="css_form_ligne_titre" style="width: 100%;font-size: 11px; text-align: center;padding: 10px 15px;">
                            @traduction('document.blocs.saisie_des_articles.a_terme')
                        </div>

                        <div class="css_form_ligne_titre" style="width: 100%;font-size: 11px; text-align: center;padding: 10px 15px;" v-for="(rotation,periode_rotation) in articles_famille_du_fournisseur_correspondant_a_la_recherche(famille)[0].vitesse_de_rotation">
                            @traduction('composant.gestion_des_stocks.vitesse_de_rotation') (@{{ periode_rotation }})
                        </div>
                    </div>


                    <div class="col-md-12">
                        <div class="row css_ligne_hover" v-for="article in articles_famille_du_fournisseur_correspondant_a_la_recherche(famille)">
                            <div class="col-md-4">
                                @{{ article.designation }}
                                <b>(@{{ article.reference }})</b>
                                <span v-show="article.conditionnement_affiche != null">
											@{{ article.conditionnement_affiche }}
											</span>
                                <span :title="traduction('interface.document.saisie_des_articles.fournisseur_prioritaire')" data-toggle="tooltip" v-show="article.fournisseur_prioritaire == 1" class="fa fa-star" style="color: #e5bf32;"></span>
                            </div>
                            <div class="col-md-1">@{{ article.tarif | montant }}</div>
                            <div class="col-md-1"><input type="number" step="1" v-model="article.quantite" @change="maj_quantite_article_fournisseur(article)" @wheel.prevent @keydown.up.prevent @keydown.down.prevent></div>
                            <div class="col-md-1">@{{ article.total | montant }}</div>

                            <div class="col-md-5" style="display: flex;justify-content: space-around;">
                                <div style="width: 100%;text-align: center">
                                    @{{ article.stock_actuel }}

                                    <template v-if="article.seuil_alerte != null">
                                        <span v-if="article.stock_actuel >= article.seuil_alerte" class="badge badge-success"><i aria-hidden="true" class="fa fa-check"></i></span>

                                        <span v-else-if="article.stock_actuel < article.seuil_alerte && article.stock_actuel >= article.seuil_mini" class="badge badge-warning"><i aria-hidden="true" class="fa fa-exclamation-triangle"></i></span>

                                        <span v-else class="badge badge-danger"><i aria-hidden="true" class="fa fa-exclamation-triangle"></i></span>
                                    </template>
                                </div>
                                <div style="width: 100%;text-align: center">
                                    @{{ article.stock_reserve }}
                                </div>
                                <div style="width: 100%;text-align: center">
                                    @{{ article.stock_disponible }}
                                </div>
                                <div style="width: 100%;text-align: center">
                                    @{{ article.stock_achete }}
                                </div>
                                <div style="width: 100%;text-align: center">
                                    @{{ article.stock_a_terme }}

                                    <template v-if="article.seuil_alerte != null ">
                                        <span v-if="article.stock_a_terme >= article.seuil_alerte" class="badge badge-success"><i aria-hidden="true" class="fa fa-check"></i></span>

                                        <span v-else-if="article.stock_a_terme < article.seuil_alerte && article.stock_actuel >= article.seuil_mini" class="badge badge-warning"><i aria-hidden="true" class="fa fa-exclamation-triangle"></i></span>

                                        <span v-else class="badge badge-danger"><i aria-hidden="true" class="fa fa-exclamation-triangle"></i></span>
                                    </template>
                                </div>
                                <div style="width: 100%;text-align: center" v-for="(rotation,periode_rotation) in article.vitesse_de_rotation">
                                    @{{ rotation }}
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12" v-if="fournisseur_articles.id > 0 && !affichage_articles_fournisseur_pret ">
        <div class="row">
            <div class="col-md-1">
                <img style="background-color: {{ maquette('background_menus') }};width:20px;" src="{{ asset('eden/images/loading.svg') }}" />
            </div>
            <div class="col-md-11">
                <span style="font-style: italic;">@traduction('document.blocs.saisie_des_articles.chargement_articles_fournisseur')</span>
            </div>
        </div>
    </div>
</div>