<template v-if="modal_champ_libre_liste_preenregistree">
    <transition name="modal" >
        <div class="modal-mask">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Gestion des listes</h5>
                        <button type="button" class="close" @click="modal_champ_libre_liste_preenregistree = false" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="#" method="post" class="css_form" id="">
                            {{ csrf_field() }}

                            <div class="row">
                                <div class="col-sm-1">#</div>
                                <div class="col-sm-3">Valeur</div>
                                <div class="col-sm-1">Ordre</div>
                                <div class="col-sm-1">Désactivée</div>
                                <div :class="fonctionnalite_google && champ_libre_liste.liste_choix === 504 ? 'col-sm-1' : 'col-sm-2'">
                                    Couleur police
                                </div>
                                <div :class="fonctionnalite_google && champ_libre_liste.liste_choix === 504 ? 'col-sm-1' : 'col-sm-2'">
                                     Couleur fond
                                </div>
                                <div class="col-sm-2">Icône</div>
                                <div v-if="fonctionnalite_google && champ_libre_liste.liste_choix === 504" class="col-sm-2">
                                    Couleur Google
                                </div>
                            </div>
                            <div>
                                <div class="row" v-for="(liste_libre, index) in liste_libre_preenregistree" :key="index">
                                    <div class="col-sm-1">
                                        @{{ liste_libre.id_valeur }}
                                    </div>
                                    <div class="col-sm-3">
                                        <traduction-element :index_traduction="'valeurs_listes_formatees.'+champ_libre_liste.liste_choix" :champ="'valeur_'+liste_libre.id_valeur" ></traduction-element>
                                    </div>
                                    <div class="col-sm-1"><input type="text" v-model="liste_libre.ordre"></div>
                                    <div class="col-sm-1"><input type="checkbox" v-model="liste_libre.desactivee"></div>
                                    <div :class="fonctionnalite_google && champ_libre_liste.liste_choix === 504 ? 'col-sm-1' : 'col-sm-2'">
                                        <input type="color" v-model="liste_libre.couleur_police">
                                    </div>
                                    <div :class="fonctionnalite_google && champ_libre_liste.liste_choix === 504 ? 'col-sm-1' : 'col-sm-2'">
                                        <input type="color" v-model="liste_libre.couleur_fond">
                                    </div>
                                    <div class="col-sm-2">
                                        <button type="button" class="btn btn-primary iconpicker-component" style="position:relative;">
                                            <i :class="liste_libre.icone"></i>
                                            <span v-if="liste_libre.icone" @click="liste_libre.icone = ''" class="fa fa-times btn-primary" style="position:absolute;border-radius:10px;top:-10px;right:-10px;padding: 2px 5px;"></span>
                                        </button>
                                        <button type="button" class="icp icp-dd btn btn-primary dropdown-toggle liste_libre_preenregistree"
                                                data-selected="fa-car" data-toggle="dropdown" :id="'liste_libre_preenregistree_'+index" >
                                            <span class="caret"></span>
                                            <span class="sr-only">Icone</span>
                                        </button>
                                        <div class="dropdown-menu"></div>
                                    </div>
                                    <div v-if="fonctionnalite_google && champ_libre_liste.liste_choix === 504" class="col-sm-2">
                                        <select name="id_couleur_google" v-model="liste_libre.id_couleur_google"
                                                :style="liste_libre.id_couleur_google != undefined && liste_libre.id_couleur_google !== 0 ?
													'background-color:'+recuperer_valeur_liste_formatee(9,liste_libre.id_couleur_google).valeur : ''">
                                            <option v-for="couleur in valeurs_listes_formatees[9]" :style="couleur.id_valeur !== 0 ?
													'background-color:'+couleur.valeur : 'background-color:white'" :value="couleur.id_valeur">
                                                @{{ couleur.valeur }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                        </form>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="modal_champ_libre_liste_preenregistree = false">Fermer</button>
                        <a type="button" class="btn btn-success" :href="'eden/parametrage/traduction?categorie=9&recherche=valeurs_listes_formatees.'+champ_libre_liste.liste_choix+'.'" target="_blank">Traduire en masse</a>
                        @if(table_libre($type_element)->vue_sql !=1)
                            <button type="button" class="btn btn-primary" @click="enregistrer_liste_libre_preenregistree">Enregistrer</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>