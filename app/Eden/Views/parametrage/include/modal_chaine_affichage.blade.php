<template v-if="modal_chaine_affichage">
    <transition name="modal" >
        <div class="modal-mask">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Gestion des affichages</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true" @click="modal_chaine_affichage = false">&times;</span>
                        </button>
                    </div>
                    <form action="#" method="post" class="css_form" id="formulaire_champ_libre">
                        <div class="modal-body">
                            {{ csrf_field() }}
                            <div class="row mt-10">
                                <div class="col-sm-2">Affichage général
                                    <i @click="aide_parametrage.aide_affichage_liste = !aide_parametrage.aide_affichage_liste" class="far fa-question-circle"></i>
                                </div>
                                <div class="col-sm-10">
                                    <input-parametrage :type_utilisateur="type_utilisateur" at_custom="#" name="affichage_dans_liste" :vmodel="table_libre_modification" :donnees="champs_libres_tries"></input-parametrage>
                                </div>
                            </div>
                            <div class="row" v-if="aide_parametrage.aide_affichage_liste">
                                <div class="col-sm-12 css_infos_parametrage_champ_libre">
                                    <div>
                                        Affichage de l'élément dans le fil d'Ariane et par défaut.
                                    </div>
                                    <div>
                                        <img class="w-50" src="{{ asset('eden/images/aide-affichage/aide-affichage-liste.png') }}" alt="Aide à l'affichage pour liste"/>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-10">
                                <div class="col-sm-2">Affichage extranet
                                    <i @click="aide_parametrage.aide_affichage_extranet = !aide_parametrage.aide_affichage_extranet" class="far fa-question-circle"></i>
                                </div>
                                <div class="col-sm-10">
                                    <input-parametrage :type_utilisateur="type_utilisateur" at_custom="#" name="affichage_extranet" :vmodel="table_libre_modification" :donnees="champs_libres_tries"></input-parametrage>
                                </div>
                            </div>
                            <div class="row" v-if="aide_parametrage.aide_affichage_extranet">
                                <div class="col-sm-12 css_infos_parametrage_champ_libre">
                                    <div>
                                        Affichage de l'élément dans l'extranet.
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-15">
                                <div class="col-sm-2">Affichage recherche
                                    <i @click="aide_parametrage.aide_affichage_recherche = !aide_parametrage.aide_affichage_recherche" class="far fa-question-circle"></i>
                                </div>
                                <div class="col-sm-10">
                                    <input-parametrage :type_utilisateur="type_utilisateur" at_custom="#" name="affichage_recherche" :vmodel="table_libre_modification" :donnees="champs_libres_tries"></input-parametrage>
                                </div>
                            </div>
                            <div class="row" v-if="aide_parametrage.aide_affichage_recherche">
                                <div class="col-sm-12 css_infos_parametrage_champ_libre">
                                    <div>
                                        Affichage de l'élément dans le tableau de résultat (colonne "lien") après une recherche globale sur l'ERP. Présent aussi sur les autres types de recherches de l'ERP.
                                    </div>
                                    <div>
                                        <img class="w-75" src="{{ asset('eden/images/aide-affichage/aide-affichage-recherche.png') }}" alt="Aide à l'affichage pour la recherche"/>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-15">
                                <div class="col-sm-2">Affiche fiche type
                                    <i @click="aide_parametrage.aide_affichage_fiche_type = !aide_parametrage.aide_affichage_fiche_type" class="far fa-question-circle"></i>
                                </div>
                                <div class="col-sm-10">
                                    <input-parametrage :type_utilisateur="type_utilisateur" at_custom="#" name="affichage_fiche_type" :vmodel="table_libre_modification" :donnees="champs_libres_tries"></input-parametrage>
                                </div>
                            </div>
                            <div class="row" v-if="aide_parametrage.aide_affichage_fiche_type">
                                <div class="col-sm-12 css_infos_parametrage_champ_libre">
                                    <div>
                                        Correspond au titre de la fiche d'un élément.
                                    </div>
                                    <div>
                                        <img class="w-50" src="{{ asset('eden/images/aide-affichage/aide-affichage-fiche-type.png') }}" alt="Aide à l'affichage pour une fiche type"/>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-15">
                                <div class="col-sm-2">Affichage dans select
                                    <i @click="aide_parametrage.aide_affichage_select = !aide_parametrage.aide_affichage_select" class="far fa-question-circle"></i>
                                </div>
                                <div class="col-sm-10">
                                    <input-parametrage :type_utilisateur="type_utilisateur" at_custom="#" name="affichage_pour_select" :vmodel="table_libre_modification" :donnees="champs_libres_tries"></input-parametrage>
                                </div>
                            </div>
                            <div class="row" v-if="aide_parametrage.aide_affichage_select">
                                <div class="col-sm-12 css_infos_parametrage_champ_libre">
                                    <div>
                                        Affichage de l'élément dans la section de sélection permettant de sélectionner un élément en tapant une composante de sa chaine d'affichage ou en cliquant sur le déroulant.
                                    </div>
                                    <div>
                                        <img class="h-75" src="{{ asset('eden/images/aide-affichage/aide-affichage-select.png') }}" alt="Aide à l'affichage pour select"/>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-10">
                                <div class="col-sm-2">Affichage kanban
                                    <i @click="aide_parametrage.aide_affichage_kanban = !aide_parametrage.aide_affichage_kanban" class="far fa-question-circle"></i>
                                </div>
                                <div class="col-sm-10">
                                    <input-parametrage :type_utilisateur="type_utilisateur" at_custom="#" name="affichage_dans_kanban" :vmodel="table_libre_modification" :donnees="champs_libres_tries"></input-parametrage>
                                </div>
                            </div>
                            <div class="row" v-if="aide_parametrage.aide_affichage_kanban">
                                <div class="col-sm-12 css_infos_parametrage_champ_libre">
                                    <div>
                                        Affichage de l'élément dans les kanbans.
                                    </div>
                                    <div>
                                        <img class="w-50" src="{{ asset('eden/images/aide-affichage/aide-affichage-kanban.png') }}" alt="Aide à l'affichage pour kanban"/>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-10">
                                <div class="col-sm-2">Affichage dédoublonnage
                                    <i @click="aide_parametrage.aide_affichage_dedoublonnage = !aide_parametrage.aide_affichage_dedoublonnage" class="far fa-question-circle"></i>
                                </div>
                                <div class="col-sm-10">
                                    <input-parametrage :type_utilisateur="type_utilisateur" at_custom="#" name="affichage_dedoublonnage" :vmodel="table_libre_modification" :donnees="champs_libres_tries"></input-parametrage>
                                </div>
                            </div>
                            <div class="row" v-if="aide_parametrage.aide_affichage_dedoublonnage">
                                <div class="col-sm-12 css_infos_parametrage_champ_libre">
                                    <div>
                                        Affichage de l'élément pour la gestion du dédoublonnage des éléments
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-10">
                                <div class="col-sm-2">Affichage planning
                                    <i @click="aide_parametrage.aide_affichage_planning = !aide_parametrage.aide_affichage_planning" class="far fa-question-circle"></i>
                                </div>
                                <div class="col-sm-10">
                                    <input-parametrage :type_utilisateur="type_utilisateur" at_custom="#" name="affichage_planning" :vmodel="table_libre_modification" :donnees="champs_libres_tries"></input-parametrage>
                                </div>
                            </div>
                            <div class="row" v-if="aide_parametrage.aide_affichage_planning">
                                <div class="col-sm-12 css_infos_parametrage_champ_libre">
                                    <div>
                                        Affichage de l'élément dans le planning
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-10">
                                <div class="col-sm-2">Affichage calendrier
                                    <i @click="aide_parametrage.aide_affichage_calendrier = !aide_parametrage.aide_affichage_calendrier" class="far fa-question-circle"></i>
                                </div>
                                <div class="col-sm-10">
                                    <input-parametrage :type_utilisateur="type_utilisateur" at_custom="#" name="affichage_calendrier" :vmodel="table_libre_modification" :donnees="champs_libres_tries"></input-parametrage>
                                </div>
                            </div>
                            <div class="row" v-if="aide_parametrage.aide_affichage_calendrier">
                                <div class="col-sm-12 css_infos_parametrage_champ_libre">
                                    <div>
                                        Affichage de l'élément dans le calendrier
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="modal_chaine_affichage = false">Fermer</button>
                            @if(table_libre($type_element)->vue_sql !=1)
                                <button type="button" class="btn btn-primary" @click="enregistrer_chaine_affichage">Enregistrer</button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </transition>

</template>