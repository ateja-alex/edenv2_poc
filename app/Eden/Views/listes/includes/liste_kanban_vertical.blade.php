<div class="table-responsive kanban-vertical-scroll" :id="'liste_elements_'+id_liste"
    :ref="'liste_elements_'+id_liste"
     @scroll="(event) => actualisation_auto_colonnes_verticale(event)"
>
	<div class="css_actualisation_liste" @click="actualisation_filtres()">@traduction('interface.listes.cliquez_ici_pour_actualiser_la_liste')</div>

    {{-- Un statut = un bloc avec son propre en-tête et son propre tableau ( colonnes dupliquées, mais scroll indépendant par statut ) --}}
    <div style="display: flex; flex-direction: column">
        <template v-for="(colonne, id_colonne) in liste.kanban_colonnes">
            <div class="section-kanban-verticale" :key="colonne.id_valeur">
                <div :style="(colonne.info_colonne != null && colonne.info_colonne.couleur_fond !=undefined ? 'background:'+colonne.info_colonne.couleur_fond + ';' : '') + (colonne.info_colonne != null && colonne.info_colonne.couleur_police !=undefined ? 'color:'+colonne.info_colonne.couleur_police + ';' : '')"
                     class="colonne-entete-kanban-verticale"
                     @click="basculer_section_kanban(colonne.id_valeur)"
                >
                    <span class="css_chevron_kanban_verticale" :class="sections_kanban_fermees[colonne.id_valeur] ? 'fas fa-chevron-right' : 'fas fa-chevron-down'"></span>
                    <span class="css_titre_colonne_kanban_verticale" v-html="colonne.nom"></span>

                    <span class="css_infos_colonne_kanban_verticale">
                        <template v-if="liste.options_liste.kanban_colonne_somme != 'false' && liste.options_liste.kanban_colonne_somme != '' && liste.options_liste.kanban_colonne_somme != undefined ">
                            <span v-html="liste.kanban_somme_par_colonnes['colonne_'+colonne.id_valeur]"></span>
                            <span v-if="liste.options_liste.kanban_unite != 'false'" v-html="liste.options_liste.kanban_unite"></span>
                        </template>
                        <template v-if="liste.options_liste.kanban_colonne_count != 'false' && liste.options_liste.kanban_colonne_count != '' && liste.options_liste.kanban_colonne_count != undefined ">
                            <span v-if="liste.kanban_nombre_par_colonnes['colonne_'+colonne.id_valeur] != undefined" v-html="liste.kanban_nombre_par_colonnes['colonne_'+colonne.id_valeur] +' '+liste.element_pluriel"></span>
                        </template>
                        <span v-if="(liste.modele_liste_libre.desactiver_creation !== 1) && liste.droits_liste.profil_creation && !($root.intranet)" class="css_ajouter_element_kanban_colonne css__lien" @click.stop="creer_dans_liste(colonne.id_valeur)" data-toggle="tooltip" data-placement="top" :title="$root.traduction('interface.listes.ajouter')">
                            <i class="fas fa-plus"></i>
                        </span>
                    </span>
                </div>

                <div
                    v-show="!sections_kanban_fermees[colonne.id_valeur]"
                    class="colonne-kanban-verticale"
                    @scroll="(event) => actualisation_auto(event, colonne.id_valeur)"
                    @wheel="transferer_scroll_kanban_verticale($event)"
                >
                    {{-- Si des colonnes de tableau sont paramétrées sur cette liste, on affiche une vraie table ( en-tête + tri + drag&drop par ligne ) --}}
                    <table v-if="liste.colonnes && liste.colonnes.length > 0" class="table table-bordered table-hover css_table_kanban_verticale">
                        <thead>
                            <tr>
                                <th style="max-width: 20px; text-align: center; padding-top: 10px; background: var(--card_header); width: 44px" :rowspan="colonne_groupe ? 2 : 1" v-if="liste.desactiver_checkbox !== true"><input type="checkbox" :id="'checkbox_selection_global_liste_'+id_liste+'_'+colonne.id_valeur" @change="checkbox_selectionner_toutes_les_lignes_groupe($event, colonne.id_valeur)" /></th>

                                @if(fonctionnalite('listes_activer_menu_options_a_gauche') === true)
                                    <th style="width: 189px;" :rowspan="colonne_groupe ? 2 : 1" v-if="liste.modele_liste_libre.desactiver_options !== 1">
                                        @traduction('interface.listes.options')
                                        <template v-if="mode_parametrage == 1">
                                            <a :href="'/eden/parametrage/liste_libre/'+id_liste" style="float: right;font-size: 14px" data-toggle="tooltip" data-placement="left" title="Paramétrer la liste">
                                                <i class="fas fa-cog"></i>
                                            </a>
                                        </template>
                                    </th>
                                @endif

                                <th v-for="colonne_entete in liste.colonnes" :key="'entete_'+colonne_entete.id"
                                    @click="colonne_entete.groupements_calcul == null ? change_tri(colonne_entete) : null"
                                    :class="colonne_entete.groupements_calcul == null && colonne_entete.tri_desactive !== 1 ? 'css_entete_triable_kanban_verticale' : ''"
                                    :rowspan="colonne_groupe && colonne_entete.groupements_calcul == null ? 2 : 1"
                                    :colspan="colonne_entete.groupements_calcul != null ? Math.max(colonne_entete.groupements_calcul.length, 1) : 1"
                                    :style="colonne_entete.groupements_calcul == null ? {width: largeur_colonnes_kanban_verticale[colonne_entete.id]} : ''"
                                >
                                    <template v-if="colonne_entete.index_traduction != '' && colonne_entete.index_traduction != null">
                                        @traduction('colonne_entete.index_traduction','nom',true)
                                    </template>
                                    <template v-else>
                                        <span v-html="colonne_entete.nom"></span>
                                    </template>
                                    <span :class="'fa fa-arrow-'+(liste.options_liste.direction_tri == 1 ? 'up' : 'down')" v-if="liste.options_liste.tri == colonne_entete.id"></span>
                                </th>

                                @if(fonctionnalite('listes_activer_menu_options_a_gauche') === false)
                                    <th style="width: 189px;" :rowspan="colonne_groupe ? 2 : 1" v-if="liste.modele_liste_libre.desactiver_options !== 1">
                                        @traduction('interface.listes.options')
                                        <template v-if="mode_parametrage == 1">
                                            <a :href="'/eden/parametrage/liste_libre/'+id_liste" style="float: right;font-size: 14px" data-toggle="tooltip" data-placement="left" title="Paramétrer la liste">
                                                <i class="fas fa-cog"></i>
                                            </a>
                                        </template>
                                    </th>
                                @endif
                            </tr>
                            <tr v-if="colonne_groupe != null">
                                <template v-for="colonne_entete in liste.colonnes.filter(c => c.groupements_calcul != null)">
                                    <th v-if="colonne_entete.groupements_calcul.length == 0"></th>
                                    <th v-for="colonne_groupement in colonne_entete.groupements_calcul" :key="colonne_entete.id+'_'+colonne_groupement.id"
                                        @click="change_tri(colonne_entete, colonne_groupement.id)" class="css_entete_triable_kanban_verticale">
                                        <span v-html="colonne_groupement.nom"></span>
                                        <span :class="'fa fa-arrow-'+(liste.options_liste.direction_tri == 1 ? 'up' : 'down')" v-if="liste.options_liste.tri == colonne_entete.id+'_'+colonne_groupement.id"></span>
                                    </th>
                                </template>
                            </tr>
                        </thead>
                        {{-- On vérifie si le drag & drop est désactivé ou non ( paramétrage ) --}}
                        <tbody
                            :id="liste.modele_liste_libre.desactiver_drag_drop_kanban !== 1 ? 'sortable_'+id_liste+'_'+colonne.id_valeur : false"
                            :class="liste.modele_liste_libre.desactiver_drag_drop_kanban !== 1 ? 'connectedSortable_'+id_liste : ''"
                            :data-categorie_id="colonne.id_valeur"
                        >
                            <tr v-if="colonne_kanban_verticale_vide(colonne.id_valeur)">
                                <td :colspan="nombre_colonnes_kanban_verticale" class="css_placeholder_kanban_verticale">@traduction('interface.listes.aucun_element')</td>
                            </tr>

                            <tr v-for="ligne in liste.lignes.filter(ligne => ligne.element[kanban_champ] == colonne.id_valeur)" :key="'kanban_' + colonne.id_valeur + '_' + ligne.id" :data-id_element="ligne.element.id" class="css_item_liste_kanban css_ligne_tableau_kanban_verticale" :class="verifie_si_ligne_cochee_class(ligne.id)" :style="ligne.element.couleur_background">
                                <td style="text-align: center; padding-top: 10px;" v-if="liste.desactiver_checkbox !== true">
                                    <input @change="calculs_lignes_selectionnes" type="checkbox" :value="ligne.id" :checked="verifie_si_ligne_cochee(ligne.id)" />
                                </td>

                                @if(fonctionnalite('listes_activer_menu_options_a_gauche') === true)
                                    <td style="white-space: nowrap;" v-if="liste.modele_liste_libre.desactiver_options !== 1">
                                        <component :is="afficher_options(ligne)" :ligne="ligne"></component>
                                    </td>
                                @endif

                                <template v-for="colonne_ligne in liste.colonnes">
                                    <template v-if="colonne_ligne.groupements_calcul != null">
                                        <td v-if="colonne_ligne.groupements_calcul.length == 0">0</td>
                                        <td v-for="colonne_groupement in colonne_ligne.groupements_calcul" :key="colonne_ligne.id+'_'+colonne_groupement.id"
                                            :class="(colonne_ligne.retour_a_la_ligne_impossible ? 'css_retour_a_la_ligne_impossible_liste' : '') + ' alignement_'+(colonne_ligne.alignement_colonne == '' || colonne_ligne.alignement_colonne == null ? 'left' : colonne_ligne.alignement_colonne)"
                                            :style="(colonne_ligne.couleur_colonne ? ('background-color : '+colonne_ligne.couleur_colonne) : '')">
                                            <span v-html="ligne[colonne_ligne.id].contenu.find(c => c.id == colonne_groupement.id)?.valeur ?? ''"></span>
                                        </td>
                                    </template>
                                    <td v-else :key="colonne_ligne.id" :class="colonne_ligne.retour_a_la_ligne_impossible ? 'css_retour_a_la_ligne_impossible_liste' : ''"
                                        :style="(colonne_ligne.couleur_colonne ? ('background-color : '+colonne_ligne.couleur_colonne+';') : '') + 'width:'+largeur_colonnes_kanban_verticale[colonne_ligne.id]">
                                        <colonne-champ v-if="ligne[colonne_ligne.id] && ligne[colonne_ligne.id].type == 'champ'" :colonne="colonne_ligne" :ligne="ligne">
                                            <template v-slot:contenu v-if="ligne[colonne_ligne.id].affichage">
                                                <div :class="'css_'+(ligne[colonne_ligne.id].type) + ' alignement_'+(colonne_ligne.alignement_colonne == '' || colonne_ligne.alignement_colonne == null ? 'left' : colonne_ligne.alignement_colonne)">
                                                    <template v-for="contenu in ligne[colonne_ligne.id].affichage.contenus ?? [ligne[colonne_ligne.id].affichage.contenu]">
                                                        <component v-if="ligne[colonne_ligne.id].affichage.type == 'composant'" :is="contenu.composant" v-bind="contenu.props"></component>
                                                        <span v-else v-html="contenu"></span>
                                                    </template>
                                                </div>
                                            </template>
                                        </colonne-champ>
                                        <div v-else-if="ligne[colonne_ligne.id]" @click="gestion_lien($event,ligne[colonne_ligne.id].lien,ligne)" :class="'css_'+(ligne[colonne_ligne.id].type) + ' alignement_'+(colonne_ligne.alignement_colonne == '' || colonne_ligne.alignement_colonne == null ? 'left' : colonne_ligne.alignement_colonne) + (ligne[colonne_ligne.id].lien != null ? ' css__lien' : '')">
                                            <template v-for="contenu in ligne[colonne_ligne.id].contenus ?? [ligne[colonne_ligne.id].contenu]">
                                                <a v-if="ligne[colonne_ligne.id].lien && ligne[colonne_ligne.id].lien.type == 'redirection'" :href="ligne[colonne_ligne.id].lien.redirection">
                                                    <component v-if="ligne[colonne_ligne.id].type == 'composant'" :is="contenu.composant" v-bind="contenu.props"></component>
                                                    <span v-else v-html="contenu"></span>
                                                </a>
                                                <template v-else>
                                                    <component v-if="ligne[colonne_ligne.id].type == 'composant'" :is="contenu.composant" v-bind="contenu.props"></component>
                                                    <span v-else v-html="contenu"></span>
                                                </template>
                                            </template>
                                        </div>
                                    </td>
                                </template>

                                @if(fonctionnalite('listes_activer_menu_options_a_gauche') === false)
                                    <td style="white-space: nowrap;" v-if="liste.modele_liste_libre.desactiver_options !== 1">
                                        <component :is="afficher_options(ligne)" :ligne="ligne"></component>
                                    </td>
                                @endif
                            </tr>
                        </tbody>
                    </table>

                    {{-- Sinon on garde l'affichage carte standard du kanban --}}
                    <template v-else>
                        <div v-if="colonne_kanban_verticale_vide(colonne.id_valeur)" class="css_placeholder_kanban_verticale css_placeholder_kanban_verticale_carte">@traduction('interface.listes.aucun_element')</div>
                        <div
                            :id="liste.modele_liste_libre.desactiver_drag_drop_kanban !== 1 ? 'sortable_'+id_liste+'_'+colonne.id_valeur : false"
                            :class="liste.modele_liste_libre.desactiver_drag_drop_kanban !== 1 ? 'connectedSortable_'+id_liste : ''"
                            :data-categorie_id="colonne.id_valeur"
                        >
                            <div v-for="element in liste.lignes.filter(ligne => ligne.element[kanban_champ] == colonne.id_valeur)" :key="'kanban_' + colonne.id_valeur + '_' + element.id" :data-id_element="element.element.id" class="css_item_liste_kanban css_ligne_tableau_kanban_verticale" :style="element.element.couleur_background">
                                <component :is="afficher_element_kanban(element)" />
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>
    </div>
</div>

@push('donnees_pour_vuejs_methods')

	/**
	 *
	 * Un groupe kanban vertical est considéré vide en se basant sur le DOM réel ( attribut data-id_element )
	 * plutôt que sur liste.lignes : le drag & drop ( jQuery UI Sortable ) déplace le noeud DOM immédiatement,
	 * avant que liste.lignes ne soit mis à jour par l'aller-retour ajax, ce qui laissait le placeholder
	 * "aucun élément" affiché en même temps que l'élément déplacé.
	 *
	 */
	colonne_kanban_verticale_vide(id_colonne) {

		var conteneur = document.getElementById('sortable_'+this.id_liste+'_'+id_colonne);

		if(!conteneur)
			return this.liste.lignes.filter(ligne => ligne.element[this.kanban_champ] == id_colonne).length == 0;

		return conteneur.querySelectorAll('[data-id_element]').length == 0;
	},

	/**
	 *
	 * Permet de faire remonter le scroll ( molette ) vers le conteneur principal de la liste quand un groupe
	 * n'a pas ( ou plus ) de marge de scroll dans le sens demandé : sans ça, overscroll-behavior: contain
	 * ( voir eden.css ) bloque tout chainage du scroll vers la page.
	 *
	 */
	transferer_scroll_kanban_verticale(event) {

		var conteneur_groupe = event.currentTarget;
		var delta = event.deltaY;

		var peut_scroller_bas = conteneur_groupe.scrollTop + conteneur_groupe.clientHeight < conteneur_groupe.scrollHeight - 1;
		var peut_scroller_haut = conteneur_groupe.scrollTop > 0;

		if((delta > 0 && !peut_scroller_bas) || (delta < 0 && !peut_scroller_haut)) {

			event.preventDefault();

			var conteneur_principal = this.$refs['liste_elements_'+this.id_liste];

			if(conteneur_principal)
				conteneur_principal.scrollTop += delta;
		}
	},

@endpush

@push('donnees_pour_vuejs_mounted')

	// force le re-rendu ( et donc la ré-évaluation de colonne_kanban_verticale_vide ) dès que jQuery UI Sortable
	// déplace un élément entre deux groupes, sans attendre le retour de l'appel ajax d'enregistrement
	(() => {
		var conteneur = this.$refs['liste_elements_'+this.id_liste];

		if(conteneur)
			$(conteneur).on('sortreceive sortremove', () => this.$forceUpdate());
	})();

@endpush