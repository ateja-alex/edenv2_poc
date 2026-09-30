<div class="table-responsive kanban-horizontal-scroll" :id="'liste_elements_'+id_liste"
    :ref="'liste_elements_'+id_liste"
     @scroll="(event) => actualisation_auto_colonnes(event)"
>
	<div class="css_actualisation_liste" @click="actualisation_filtres()">@traduction('interface.listes.cliquez_ici_pour_actualiser_la_liste')</div>
    <div style="display: flex">
        <template v-for="(colonne, id_colonne) in liste.kanban_colonnes">
            <div :style="(colonne.info_colonne != null && colonne.info_colonne.couleur_fond !=undefined ? 'background:'+colonne.info_colonne.couleur_fond + ';' : '') + (colonne.info_colonne != null && colonne.info_colonne.couleur_police !=undefined ? 'color:'+colonne.info_colonne.couleur_police + ';' : '')"  class="colonne-entete-kanban">
                <span v-html="colonne.nom"></span>

                <template v-if="liste.options_liste.kanban_colonne_somme != 'false' && liste.options_liste.kanban_colonne_somme != '' && liste.options_liste.kanban_colonne_somme != undefined ">
                    <br/>
                    <span style="font-size: 20px" v-html="liste.kanban_somme_par_colonnes['colonne_'+colonne.id_valeur]"></span>
                    <span style="font-size: 20px" v-if="liste.options_liste.kanban_unite != 'false'" v-html="liste.options_liste.kanban_unite"></span>
                </template>
                <template v-if="liste.options_liste.kanban_colonne_count != 'false' && liste.options_liste.kanban_colonne_count != '' && liste.options_liste.kanban_colonne_count != undefined ">
                    <br>
                    <span style="font-size: 20px" v-if="liste.kanban_nombre_par_colonnes['colonne_'+colonne.id_valeur] != undefined" v-html="liste.kanban_nombre_par_colonnes['colonne_'+colonne.id_valeur] +' '+liste.element_pluriel"></span>
                </template>
                <div v-if="(liste.modele_liste_libre.desactiver_creation !== 1) && liste.droits_liste.profil_creation && !($root.intranet)" class="css_ajouter_ligne_kanban css__lien" @click="creer_dans_liste(colonne.id_valeur)">
                    <i class="fas fa-plus"></i> @traduction('interface.listes.ajouter')
                </div>
            </div>
        </template>
    </div>
    <div style="display: flex">
        <template v-for="colonne in liste.kanban_colonnes">

            {{-- On vérifie si le drag & drop est désactivé ou non ( paramétrage ) --}}
            <div
                :id="liste.modele_liste_libre.desactiver_drag_drop_kanban !== 1 ? 'sortable_'+id_liste+'_'+colonne.id_valeur : false"
                :class="'colonne-kanban ' + (liste.modele_liste_libre.desactiver_drag_drop_kanban !== 1 ? 'connectedSortable_'+id_liste : '')"
                :data-categorie_id="colonne.id_valeur"
                @scroll="(event) => actualisation_auto(event, colonne.id_valeur)"
            >
                <div v-for="element in liste.lignes.filter(ligne => ligne.element[kanban_champ] == colonne.id_valeur)" :key="'kanban_' + colonne.id_valeur + '_' + element.id" :data-id_element="element.element.id" class="css_item_liste_kanban css_vignette_kanban" :style="element.element.couleur_background">
                    <div class="css_options_kanban" v-if="liste.options && liste.modele_liste_libre.desactiver_options !== 1">
                        <component :is="afficher_options(element)" :ligne="element"></component>
                    </div>
                    <component :is="afficher_element_kanban(element)" />
                </div>
            </div>
        </template>
    </div>
</div>
