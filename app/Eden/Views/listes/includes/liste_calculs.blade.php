<div class="css_calculs_liste {{ $classe_css }}">
    <template v-for="calcul in liste.calculs">
        <div class="col-md-6" v-if="calcul.taille == 6">
            <div class="css_block_calcul_liste">
                <span class="css_chiffre_calcul_liste" v-show="calcul.type == 'une_valeur'">
                    <span v-if="liste.lignes_selectionnees.length > 0 && calcul.calcul_elements_selectionnes">
                        @{{ calcul.calcul_elements_selectionnes.resultat}} /
                    </span>
                    @{{ calcul.resultat }}
                </span>
                <span class="css_chiffre_calcul_liste" v-show="calcul.titre != ''">
                    @{{ calcul.titre }}
                </span>
                <span class="css_type_chiffre_calcul_liste">
                    <template v-if="calcul.index_traduction == null">
                        @{{ calcul.nom }}
                    </template>
                    <template v-else>
                        @traduction('calcul.index_traduction','nom',true)
                    </template>
                </span>
                <div class="css_details_calcul_liste" v-show="calcul.type == 'liste_de_valeurs'">
                    <span v-for="(resultat, nom) in calcul.resultat">
                        <span v-html="nom"></span>: 
                        <span v-if="liste.lignes_selectionnees.length > 0 && calcul.calcul_elements_selectionnes">
                            @{{ calcul.calcul_elements_selectionnes.resultat[nom] ?? 0}} /
                        </span>
                        @{{ resultat }}
                    </span>
                </div>
            </div>
        </div>
        <div :class="'col-md-' + (calcul.taille ? calcul.taille : '2')"
             style="width: auto; max-width: none; flex: none;" v-else>
            <div class="css_block_calcul_liste">
                <span :class="'css_icone_calcul_liste fa ' + calcul.icone " aria-hidden="true" style="font-size: 50px; opacity: 0.5;"></span>
                <span class="css_chiffre_calcul_liste">
                    <template v-if="calcul.index_traduction == null">
                        @{{ calcul.nom }}
                    </template>
                    <template v-else>
                        @traduction('calcul.index_traduction','nom',true)
                    </template>
                    <div v-if="calcul.afficher_somme" v-text="calcul.titre"></div>
                </span>
                <span class="css_type_chiffre_calcul_liste" v-show="calcul.type != 'liste_de_valeurs'">
                    <span v-if="liste.lignes_selectionnees.length > 0 && calcul.calcul_elements_selectionnes">
                        @{{ calcul.calcul_elements_selectionnes.resultat}} /
                    </span>
                    @{{ calcul.resultat }}
                </span>
            
                <div class="css_details_calcul_liste" v-show="calcul.type == 'liste_de_valeurs'">
                    <span v-show="calcul.toujours_deploye !== 1 && (calcul.affichage_resultats == false || calcul.affichage_resultats === null || calcul.affichage_resultats == undefined)">
                        <span class="fa fa-chevron-down" @click="calcul.affichage_resultats = true;"></span>
                    </span>
                    <span v-show="calcul.affichage_resultats === true || calcul.toujours_deploye === 1">
                        <div v-if="calcul.toujours_deploye !== 1">
                            <span class="fa fa-chevron-up" @click="calcul.affichage_resultats = false;"></span><br/>
                        </div>
                        <div v-for="(resultat, nom) in calcul.resultat">
                            <span v-html="nom"></span>: 
                            <span v-if="liste.lignes_selectionnees.length > 0 && calcul.calcul_elements_selectionnes">
                                @{{ calcul.calcul_elements_selectionnes.resultat[nom] ?? 0}} /
                            </span>
                            @{{ resultat }}
                        </div>
                    </span>
                </div>
            </div>
        </div>
    </template>
</div>
