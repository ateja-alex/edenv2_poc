<template v-if="modale_ajout_rapport">
    <transition name="modal" >
        <div class="modal-mask">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Création : @{{ rapport.nom }}</h5>
                        <button type="button" class="close" @click="modale_ajout_rapport = false" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form class="css_form" id="formulaire_rapport">
                            <div class="row" v-if="rapport.index_traduction == null">
                                <div class="col-sm-2">@traduction('rapport.divers.titre')</div>
                                <div class="col-sm-4">
                                    <input required type="text" name="titre" v-model="rapport.titre" @change="calcul_nom_sql('rapport.id_rapport', 'rapport.titre')"/>
                                </div>
                                <div class="col-sm-2">@traduction('rapport.divers.id_rapport')</div>
                                <div class="col-sm-4">
                                    <input required type="text" name="id_rapport" v-model="rapport.id_rapport" @change="calcul_nom_sql('rapport.id_rapport')"/>
                                </div>
                            </div>
                            <div class="row" v-else>
                                <div class="col-sm-12">
                                    <traduction-table ref="traduction_table"  categorie="10" :filtrage_index="rapport.index_traduction+'.'"></traduction-table>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-2">@traduction('rapport.divers.type_de_rapport')</div>
                                <div class="col-sm-4">
                                    <select name="type_rapport" v-model="rapport.type_rapport">
                                        <option value="liste_libre">{{ traduction('rapport.divers.liste_libre') }}</option>
                                        <option value="histogramme">{{ traduction('rapport.divers.histogramme') }}</option>
                                        <option value="courbe">{{ traduction('rapport.divers.courbe') }}</option>
                                        <option value="diagramme_circulaire">{{ traduction('rapport.divers.diagramme_circulaire') }}</option>
                                        <option value="tableau">{{ traduction('rapport.divers.tableau') }}</option>
                                        <option value="pdf">{{ traduction('rapport.divers.pdf') }}</option>
                                        <option value="indicateur">{{ traduction('rapport.divers.indicateur') }}</option>
                                        <option value="graphique_funnel">{{ traduction('rapport.divers.graphique_funnel') }}</option>
                                        <option value="carte">{{ traduction('rapport.divers.carte') }}</option>
                                    </select>
                                </div>
                                <template v-if="rapport.type_rapport === 'liste_libre'">
                                    <div class="col-sm-2">@traduction('rapport.divers.type_de_liste') </div>
                                    <div class="col-sm-4">
                                        <select name="type" v-model="rapport.type">
                                            <option value="liste_libre">{{ traduction('rapport.divers.liste_libre.type.liste') }}</option>
                                            <option value="requete_sql">{{ traduction('rapport.divers.liste_libre.type.requete_sql') }}</option>
                                            <option value="kanban">{{ traduction('rapport.divers.liste_libre.type.kanban') }}</option>
                                        </select>
                                    </div>
                                </template>
                            </div>
                            <div class="row">
                                <div class="col-sm-2">@traduction('rapport.divers.categorie')</div>
                                <div class="col-sm-4">
                                    <select name="categorie" v-model="rapport.categorie">
                                        @foreach($categories as $categorie => $nom)

                                            <option value="{{$categorie}}">{{ $nom['nom']}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-2">@traduction('rapport.divers.description')</div>
                                <div class="col-sm-10">
                                    <textarea v-model="rapport.description" name="description"></textarea>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-2">@traduction('rapport.divers.icone')</div>
                                <div class="col-sm-4">
                                    <select name="icone" v-model="rapport.icone">
                                        <option value="table">{{ traduction('rapport.divers.table') }}</option>
                                        <option value="chart-area">{{ traduction('rapport.divers.chart_area') }}</option>
                                        <option value="chart-pie">{{ traduction('rapport.divers.chart_pie') }}</option>
                                        <option value="info">{{ traduction('rapport.divers.info') }}</option>
                                    </select>
                                </div>
                                <div class="col-sm-2">@traduction('rapport.divers.ordre')</div>
                                <div class="col-sm-4"><input required type="text"  v-model="rapport.ordre" name="ordre"  /></div>
                            </div>
                            <div class="row">
                                <div class="col-sm-2">@traduction('rapport.divers.type_element_fiche')</div>
                                <div class="col-sm-4">
                                    <select :value="rapport.type_element_fiche" disabled>
                                        <option v-for="element in types_elements" :value="element.type_element" v-html="element.type_element"></option>
                                    </select>
                                </div>
                                <div class="col-sm-2">
                                    Clé primaire
                                </div>
                                <div class="col-sm-4 css_champ_obligatoire js_champ_obligatoire">
                                    <select v-model="rapport.cle_primaire">
                                        <option value="id">Id élément</option>
                                        <option v-for="(champ, nom_sql) in champs_selection_type_element_liste" :value="nom_sql">
                                            @{{traduction(champ.index_traduction,'nom')}} (@{{nom_sql}})
                                        </option>
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-2">@traduction('rapport.divers.type_element_principal')</div>
                                <div class="col-sm-4">
                                    <select id="type_element" name="type_element" @change="rapport.id_rapport_cible = null" v-model="rapport.type_element">
                                        <template v-if="rapport.type_rapport == 'carte'">
                                            <option v-for="type in types_vues_carte" :value="type.type_element ? type.type_element : type.nom_sql" v-html="type.type_element ? type.type_element : type.nom_sql"></option>
                                        </template>
                                        <template v-else>
                                            <option v-for="element in types_elements" :value="element.type_element" v-html="element.type_element"></option>
                                        </template>
                                    </select>
                                </div>
                                <div class="col-sm-2">
                                    Clé étrangère
                                </div>
                                <div class="col-sm-4 css_champ_obligatoire js_champ_obligatoire">
                                    <input type="text" v-model="rapport.cle_etrangere" v-if="rapport.type == 'requete_sql'">
                                    <select v-model="rapport.cle_etrangere" v-else>
                                        <option value="id">Id élément</option>
                                        <option v-for="champ in champs_selection_type_element_fiche[rapport.type_element]" :value="champ.nom_sql">
                                            @{{traduction(champ.index_traduction,'nom')}} (@{{champ.nom_sql}})
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-2">@traduction('rapport.divers.disponible_extranet')</div>
                                <div class="col-sm-4">
                                    <select id="extranet" name="extranet" v-model="rapport.extranet">
                                        <option value="0">{{ traduction('rapport.divers.non') }}</option>
                                        <option value="1">{{ traduction('rapport.divers.oui') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row" v-show="rapport.type_rapport == 'indicateur'">
                                <div class="col-sm-2">@traduction('rapport.divers.icone_dans_rapport')</div>
                                <div class="col-sm-4">
                                    <button type="button" class="btn btn-primary iconpicker-component ">
                                        <i class="fas" :class="rapport.icone_dans_rapport"></i>
                                    </button>
                                    <button type="button" ref="icp-dd" class="icp icp-dd btn btn-primary dropdown-toggle menu"
                                            data-selected="fa-car" data-toggle="dropdown">
                                        <span class="caret"></span>
                                        <span class="sr-only">@traduction('rapport.divers.toggle_dropdown')</span>
                                    </button>
                                    <div class="dropdown-menu"></div>
                                </div>
                                <template v-if="!rapport.type_rapport || rapport.type_rapport == 'liste_libre'">
                                    <div class="col-sm-2">@traduction('rapport.divers.rapport_detaille')</div>
                                    <div class="col-sm-4">
                                        <select v-model="rapport.id_rapport_cible" name="id_rapport_cible">
                                            <optgroup v-for="categorie in categories_rapport" :label="categorie.nom">
                                                <option
                                                    v-for="rapport_cat in categorie.rapport"
                                                    v-if="(!rapport.type_rapport || rapport.type_rapport == 'liste_libre') && rapport_cat.type_element == rapport.type_element"
                                                    :value="rapport_cat.id_rapport"
                                                    v-html="traduction(rapport_cat.index_traduction, 'titre')"
                                                ></option>
                                            </optgroup>
                                        </select>
                                    </div>
                                </template>
                            </div>

                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="modale_ajout_rapport = false" data-dismiss="modal">Fermer</button>
                        <button type="button" class="btn btn-primary" @click="creation_rapport">Enregistrer</button>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>

@include('eden::parametrage.include.js_calcul_nom_sql')

@push('donnees_pour_vuejs_data')
    modale_ajout_rapport: false,
    categories_rapport: {!! json_encode($categories_rapports) !!},
    rapport: {
        titre: '',
        type_rapport: 'liste_libre',
        categorie: '',
        index_traduction: null,
        type_element: '',
        type_element_fiche: '',
    },
    types_vues_carte: {!! collect($types_vues_carte) !!},
@endpush

@push('donnees_pour_vuejs_methods')

    creer_nouveau_rapport() {
        this.modale_ajout_rapport = true;

        this.$nextTick(() => {
            $('.icp-dd').iconpicker({});

            $('.icp').on('iconpickerSelected', (e) => {
                this.rapport.icone_dans_rapport = e.iconpickerValue;
            });
        });
    },

    verifie_id_rapport: function() {

        // on vérifie que l'id rapport ne pose pas de pb

        // 1) il ne doit pas commencer par un chiffre
        var premier_caractere = this.rapport.id_rapport.charAt(0);

        if(premier_caractere <= '9' && premier_caractere >= '0')
            this.rapport.id_rapport = 'rapport_' + this.rapport.id_rapport;
    },

    creation_rapport: function() {

        loading(true);
        var vue_contexte = this;

        var rapport = this.rapport;

        // on enregistre les infos du champ libre
        $.post({

            url: 'eden/parametrage/rapport/enregistrer_parametrage',
            dataType: "json",
            data: rapport,
        }).done(async (donnees) => {

            if(donnees.retour !== true) {

                await erreur(donnees.retour);
                loading(false);
                return;
            }

            if(donnees.redirect) {

                document.location = donnees.redirect;
                return;
            }

            if(this.rapport.type_rapport == 'liste_libre' && donnees.hasOwnProperty('id')) {

                window.location.href = "{{URL::to('/eden/parametrage/liste_libre')}}"+'/'+donnees.id;
                return;
            }
            else {

                window.location.href = "{{URL::to('/eden/rapport')}}"+'/'+donnees.id_rapport;
                return;
            }
        });

    },

@endpush

@push('donnees_pour_vuejs_mounted')
    this.rapport.type_element_fiche = this.table_libre.type_element;
@endpush
