<div class="table-responsive">
	<div class="css_actualisation_liste" @click="actualisation_filtres()">@traduction('interface.listes.cliquez_ici_pour_actualiser_la_liste')</div>
	<table class="table table-bordered table-hover" id="liste_elements_{{$id_liste}}" width="100%" cellspacing="0">
		@if(view()->exists('eden::listes.tableau_'.$type_element))
			@include('eden::listes/tableau_'.$type_element)
		@else
				<thead>
					<tr>
						<th style="max-width: 20px; text-align: center; padding-top: 10px; background: var(--card_header); width: 44px" :rowspan="colonne_groupe ? 2 : 1" v-if="liste.desactiver_checkbox !== true" class="entete_colonne_liste_{{$id_liste}}_checkbox"><input type="checkbox" id="checkbox_selection_global_liste_{{$id_liste}}" @change="checkbox_selectionner_toutes_les_lignes($event)" /></th>
						@if(fonctionnalite('listes_activer_menu_options_a_gauche') === true && $liste_libre->desactiver_options !== 1)
							<th style="max-width: 189px;" class="entete_colonne_liste_{{$id_liste}}_option" :rowspan="colonne_groupe ? 2 : 1">
								@traduction('interface.listes.options')
								<template v-if="mode_parametrage == 1">
									<a href="{{ route('parametrage.liste_libre.index', $id_liste, false) }}" style="float: right;font-size: 14px" data-toggle="tooltip" data-placement="left" :title="'Paramétrer liste ' + @if(empty($rapport)) $root.traduction('{{table_libre($type_element)->index_traduction}}','element_pluriel') @else $root.traduction('{{$rapport->index_traduction}}','titre') @endif">
										<i class="fas fa-cog"></i>
									</a>
								</template>
							</th>
						@endif

						<th v-for="colonne in liste.colonnes"
							@click="colonne.groupements_calcul == null ? change_tri(colonne) : null"
							:style="(colonne.couleur_colonne ? ('background-color : '+colonne.couleur_colonne) : '')"
							:class="'entete_colonne_liste_{{$id_liste}}_' + colonne.id+' alignement_'+(colonne.alignement_colonne == '' || colonne.alignement_colonne == null ? 'left' : colonne.alignement_colonne)" :rowspan="colonne_groupe && colonne.groupements_calcul == null ? 2 : 1" :colspan="colonne.groupements_calcul != null ? colonne.groupements_calcul.length : 1">
							<template v-if="colonne.index_traduction == null">
								<span v-html="colonne.nom"></span>
							</template>
							<template v-else>
								@traduction('colonne.index_traduction','nom',true)
							</template>

							<span :class="'fa fa-arrow-'+(liste.options_liste.direction_tri == 1 ? 'up' : 'down')" v-if="liste.options_liste.tri == colonne.id"></span>
						</th>
						
						@if(fonctionnalite('listes_activer_menu_options_a_gauche') === false && $liste_libre->desactiver_options !== 1)
							<th style="max-width: 189px;" :rowspan="colonne_groupe ? 2 : 1">

								@traduction('interface.listes.options')
								<template v-if="mode_parametrage == 1">
									<a href="{{ route('parametrage.liste_libre.index', $id_liste, false) }}" style="float: right;font-size: 14px" data-toggle="tooltip" data-placement="left" :title="'Paramétrer liste ' + @if(empty($rapport)) $root.traduction('{{table_libre($type_element)->index_traduction}}','element_pluriel') @else $root.traduction('{{$rapport->index_traduction}}','titre') @endif">
										<i class="fas fa-cog"></i>
									</a>
								</template>
							</th>
						@endif
					</tr>

					<tr v-if="colonne_groupe != null">
						<template v-for="colonne in liste.colonnes.filter(c => c.groupements_calcul != null)">
							<th v-for="groupement_calcul in colonne.groupements_calcul" @click="change_tri(colonne,groupement_calcul.id)" :style="(colonne.couleur_colonne ? ('background-color : '+colonne.couleur_colonne) : '')+';min-width: 60px;'" 
								:class="'entete_colonne_liste_{{$id_liste}}_groupe alignement_'+(colonne.alignement_colonne == '' || colonne.alignement_colonne == null ? 'left' : colonne.alignement_colonne)">
								<span v-html="groupement_calcul.nom"></span>
								<span :class="'fa fa-arrow-'+(liste.options_liste.direction_tri == 1 ? 'up' : 'down')" v-if="liste.options_liste.tri == colonne.id+'_'+groupement_calcul.id"></span>
							</th>
						</template>
					</tr>
				</thead>
				<tbody>
					<tr v-show="liste.lignes.length == 0">
						<td :colspan="liste.colonnes.length + 4">@traduction('interface.listes.aucun_element')</td>
					</tr>
					<template v-for="(ligne, index_ligne) in liste.lignes" >

						<tr v-if="ligne.sous_total !== true" :class="verifie_si_ligne_cochee_class(ligne.id)" :element_id="ligne['id']" :style="ligne.element.couleur_background" :id_liste="{{$id_liste}}" :index_ligne="index_ligne">
							
							<td style=" text-align: center; padding-top: 10px;" v-if="liste.desactiver_checkbox !== true">
								<input @change="calculs_lignes_selectionnes" type="checkbox" :value="ligne.id" :checked="verifie_si_ligne_cochee(ligne.id)" />
							</td>
							@if(fonctionnalite('listes_activer_menu_options_a_gauche') === true && $liste_libre->desactiver_options !== 1)
								<td style="white-space: nowrap;">
									<component :is="afficher_options(ligne)" :ligne="ligne"></component>
								</td>
							@endif

							<template v-for="colonne in liste.colonnes">
								<template v-if="colonne.groupements_calcul != null">
									<td v-if="colonne.groupements_calcul.length == 0">0</td>
									<td v-for="colonne_groupement in colonne.groupements_calcul" :key="colonne.id+'_'+colonne_groupement.id" 
										:class="(colonne.retour_a_la_ligne_impossible ? 'css_retour_a_la_ligne_impossible_liste' : '') + ' alignement_'+(colonne.alignement_colonne == '' || colonne.alignement_colonne == null ? 'left' : colonne.alignement_colonne)"
										:style="'background-color :'+(ligne[colonne.id].contenu.find(c => c.id == colonne_groupement.id)?.couleur ?? colonne.couleur_colonne ?? 'unset')">
										<span v-html="ligne[colonne.id].contenu.find(c => c.id == colonne_groupement.id)?.valeur ?? ''"></span>
									</td>
								</template>
								<td v-else :key="colonne.id" :class="colonne.retour_a_la_ligne_impossible ? 'css_retour_a_la_ligne_impossible_liste' : ''"
									:style="(colonne.couleur_colonne ? ('background-color : '+colonne.couleur_colonne) : '')">
									<colonne-champ v-if="ligne[colonne.id] && ligne[colonne.id].type == 'champ'" :colonne="colonne" :ligne="ligne">
										<template v-slot:contenu v-if="ligne[colonne.id].affichage">
											<div :class="'css_'+(ligne[colonne.id].type) + ' alignement_'+(colonne.alignement_colonne == '' || colonne.alignement_colonne == null ? 'left' : colonne.alignement_colonne)">
												<template v-for="contenu in ligne[colonne.id].affichage.contenus ?? [ligne[colonne.id].affichage.contenu]">
													<component v-if="ligne[colonne.id].affichage.type == 'composant'"
														:is="contenu.composant"
														v-bind="contenu.props">
													</component>
													<span v-else v-html="contenu"></span>
												</template>
											</div>
										</template>
									</colonne-champ>
									<div v-else-if="ligne[colonne.id]" @click="gestion_lien($event,ligne[colonne.id].lien,ligne)" :class="'css_'+(ligne[colonne.id].type) + ' alignement_'+(colonne.alignement_colonne == '' || colonne.alignement_colonne == null ? 'left' : colonne.alignement_colonne) + (ligne[colonne.id].lien != null ? ' css__lien' : '')" >
										<template v-for="contenu in ligne[colonne.id].contenus ?? [ligne[colonne.id].contenu]">
											<a v-if="ligne[colonne.id].lien && ligne[colonne.id].lien.type == 'redirection'" :href="ligne[colonne.id].lien.redirection">
												<component v-if="ligne[colonne.id].type == 'composant'"
													:is="contenu.composant"
													v-bind="contenu.props">
												</component>
												<span v-else v-html="contenu"></span>
											</a>
											<template v-else>
												<component v-if="ligne[colonne.id].type == 'composant'"
													:is="contenu.composant"
													v-bind="contenu.props">
												</component>
												<span v-else v-html="contenu"></span>
											</template>
										</template>
									</div>
								</td>
							</template>
							@if(fonctionnalite('listes_activer_menu_options_a_gauche') === false && $liste_libre->desactiver_options !== 1)
								<td style="white-space: nowrap;">
									<component :is="afficher_options(ligne)" :ligne="ligne"></component>
								</td>
							@endif
						</tr>
						<tr v-if="ligne.details_ligne != null">

							<td :colspan="liste.colonnes.length + 2">
								<component ref="details_ligne" :is="details_ligne(ligne)"></component>
							</td>
						</tr>
						<tr v-if="ligne.sous_total === true" :style="'background-color: '+ligne.couleur">
							<td :colspan="ligne.colonne" :style="'background: '+ligne.couleur"><b>Sous total pour @{{ ligne.valeur }}</b></td>
							<td :style="'background: '+ligne.couleur"><b>@{{ ligne.montant_sous_total }}</b></td>
							<td colspan="10" :style="'background: '+ligne.couleur">&nbsp;</td>
						</tr>
					</template>
				</tbody>
		@endif
	</table>
</div>

@push('donnees_pour_vuejs_methods')

	details_ligne : function(ligne){

		var details_ligne = ligne.details_ligne;

		if(details_ligne.composant !== undefined){
			eval(details_ligne.composant)
			return composant;
		}

		var vue_instance = this;
		return {
			template:'<div>'+details_ligne+'</div>',
			name: 'detail-ligne-'+ligne.id,
			methods:this.$options.methods,
			data: function(){
				return vue_instance.$data;
			},
		}
	},

@endpush

@push('donnees_pour_vuejs_computed')

	colonne_groupe : function(){

		return this.liste.colonnes.find(colonne => colonne.type == 'calcul' && colonne.groupements_calcul != null);
	},
@endpush
