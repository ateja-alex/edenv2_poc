@if(!empty(moi_extranet()))
	@section('styles')
		<style>
			.css_tableau_liste_articles_document tr td:first-child{
				background-color: #f9f9f9!important;
				width:auto!important;
				border-right: none!important;
			}

			.css_tableau_liste_articles_document tr td:last-child{
				background-color: #f9f9f9!important;
				width:auto!important;
				border-left: none!important;
			}
		</style>
	@endsection
@endif



@php
    $contexte_ligne = empty($recapitulatif) ? 'saisie' : 'recap';

    $edition_ligne_calculee = ($articles_modifiables === true && empty($recapitulatif))
        ? 'ligne_article_est_active(article_sur_document, article_index)'
        : 'false';

    $edition_ligne = ($articles_modifiables === true && empty($recapitulatif)) ? 'edition' : 'false';
@endphp


@includeWhen(!$recapitulatif, 'eden::formulaires.include.document.includes.selections_elements')


<div data-type="random" class="document_conteneur_article document_sticky_header">

	<div class="document_header_selection" v-if="!moi_extranet">
		@if(empty($recapitulatif))
			<div class="css_flex_actions_article_document" style="justify-content: center;">
				<label class="css_checkbox_article_document" v-if="articles_du_document.length > 0">
					<input type="checkbox" @click="lignes_selectionnes = (lignes_selectionnes.length == articles_du_document.length ? [] : Object.keys(articles_du_document))" :checked="lignes_selectionnes.length == articles_du_document.length">
					<span class="checkmark"></span>
				</label>
			</div>
		@endif
	</div>
	<div class="document_header_selection" v-else></div>

	<div class="document_header_colonnes" :style="{'grid-template-columns': grid_template_columns()}">
		@foreach($colonnes_articles as $index => $colonne)
			<div class="document_header_colonne" @if(isset($colonne['condition_v_if'])) v-if="{{$colonne['condition_v_if']}}" @endif>
				<div class="document_header_titre_colonne" v-html="traduction('document.colonnes.{{$index}}.titre') @if(isset($colonne['nom_dynamique'])) + {{$colonne['nom_dynamique']}} @endif"></div>
			</div>
		@endforeach
	</div>

	@if(empty(moi_extranet()))
		<div class="document_header_titre_colonne_options">
			@traduction('document.tableau_des_articles.options')
		</div>
	@endif
</div>

<div v-if="!chargement_tableau_article" is="draggable" v-model="articles_liste"
	handle=".handle"
	ghost-class="css_hack_tr_articles_document_fantome"
	:multi-drag="true"
	selected-class="sortable-selected"
	swap-threshold=1
	animation=150
	:scroll="true"
	:force-auto-scroll-fallback="true"
	@change="fin_deplacement"
	@maj_champ_montant="$emit('maj_champ_montant',...arguments)"
	@if(empty($recapitulatif))
		@start="debut_deplacement_ligne_article()"
		@mouseleave.native="quitter_tableau_articles()"
	@endif
	class="document_conteneur_articles">

	{{--Article--}}
	<ligne-article-{{ $contexte_ligne }}
		v-for="(article_sur_document, article_index) in articles_liste"
		:key="cle_ligne_article(article_sur_document, article_index)"
		:article_sur_document="article_sur_document"
		:article_index="article_index"
		:edition="{{ $edition_ligne_calculee }}"
		@maj_champ_montant="$emit('maj_champ_montant',...arguments)"
	></ligne-article-{{ $contexte_ligne }}>
</div>

@push('composants_vue')
<script type="text/x-template" id="tpl_ligne_article_{{ $contexte_ligne }}">
	<div
		:class="class_regroupement(article_index)+ article_non_utilisable(article_sur_document) + (article_sur_document.affichage_nouvelle_ligne ? ' ajout_recent' : '')"
		:article_index="article_index"
		class="document_conteneur_article"
		@if(empty($recapitulatif))
			@mouseenter="survoler_ligne_article(article_sur_document, article_index)"
			@mousedown.capture="activer_ligne_article(article_sur_document, article_index, $event)"
			@focusin.capture="focus_ligne_article(article_sur_document, article_index); activer_ligne_article(article_sur_document, article_index, $event)"
			@focusout="fin_focus_ligne_article($event)"
		@endif
	>

		<template v-if="article_sur_document.type_ligne == undefined">

			@include('eden::formulaires.include.document.includes.debut_ligne', ['recapitulatif' => $recapitulatif])

			<div class="document_contenu_ligne">
				<div class="document_colonnes_ligne" :style="{'grid-template-columns': grid_template_columns()}">
					@foreach($colonnes_articles as $nom_colonne => $colonne)
						@include('eden::formulaires.include.document.includes.colonnes.'.$nom_colonne, ['recapitulatif' => $recapitulatif])
					@endforeach
					@foreach($colonnes_articles_lignes_entieres as $nom_colonne => $colonne)
						@include('eden::formulaires.include.document.includes.colonnes_lignes_entieres.'.$nom_colonne, ['recapitulatif' => $recapitulatif])
					@endforeach
				</div>
			</div>
			{{--Options tout à droite de la ligne--}}
			@if(empty(moi_extranet()))
				<div class="document_ligne_options" :style="retourne_couleur_regroupement_pour_ligne(article_sur_document, ['right'])">
					@if(!$recapitulatif)
						<div class="css_actions_article_document">
							<a class="mb-1 css_btn_action_article_document" :href="'{{URL::to('eden/fiche/article')}}/'+article_sur_document.article_id" style="color: #fff;" target="_blank" :title="traduction('interface.document.tableau_des_articles.afficher_article')"><i class="far fa-eye"></i></a>
							@if(fonctionnalite('calculateur_sur_document'))
								<span
										:title="traduction('document.colonnes.designation.afficher_calculateur')"
										class="mb-1 css_btn_action_article_document"
										@click="afficher_calculateur(article_sur_document);"
										:style="couleur_calculateur_article(article_sur_document)"
								>
									<i class="fas fa-calculator"></i>
								</span>
							@endif

							@foreach($options_articles as $vue)
								@includeWhen(!$recapitulatif, 'eden::formulaires.include.document.includes.options.'.$vue)
							@endforeach

							<!-- Divers -->
							@if(!isset($colonnes_articles['tarif']))
								<input type="hidden" v-model="article_sur_document.tarif" />
							@endif
							@if(!isset($colonnes_articles['remise']))
								<input type="hidden" v-model="article_sur_document.remise" />
							@endif
							@if(!isset($colonnes_articles['tva']))
								<input type="hidden" v-model="article_sur_document.tva" />
							@endif
							@if(!isset($colonnes_articles['prix_achat']))
								<input type="hidden" v-model="article_sur_document.prix_achat" />
							@endif

							@include('eden::formulaires.include.document.vues_a_surcharger.tableau_articles.options_supplementaires', ['recapitulatif' => $recapitulatif])

							<input type="hidden" class="article_index" value="1">
						</div>
					@endif
				</div>
			@endif

		</template>
		@foreach($options_lignes_divers as $nom_option_ligne_divers => $option_ligne_divers)
			<template v-if="article_sur_document.type_ligne == '{{$nom_option_ligne_divers}}'">
				@includeIf('eden::formulaires.include.document.includes.lignes_diverses.'.$nom_option_ligne_divers, ['recapitulatif' => $recapitulatif])
			</template>
		@endforeach
	</div>
</script>
@endpush

<!-- Modale affichant détail des stocks -->
<template v-if="afficher_modale_calculateur">
	<transition name="modal" >
		<div class="modal-mask">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">

					<div class="modal-header" v-if="detail_calculateur.article_nomenclature_index >= 0">
						<h5 class="modal-title" >@traduction('document.lignes_diverses.calculateur.titre') - @{{ detail_calculateur.nomenclature[detail_calculateur.article_nomenclature_index].calculateur.designation }}</h5>
					</div>

					<div class="modal-header" v-else-if="detail_calculateur.article_sous_nomenclature >= 0">
						<h5 class="modal-title" >@traduction('document.lignes_diverses.calculateur.titre') - @{{ detail_calculateur.nomenclature[detail_calculateur.article_nomenclature_index].nomenclature[detail_calculateur.article_sous_nomenclature].calculateur.designation }}</h5>
					</div>
					<div class="modal-header" v-else>
						<h5 class="modal-title" v-if="detail_calculateur.type_ligne == 'calculateur'">@traduction('document.lignes_diverses.calculateur.titre') - @{{ detail_calculateur.nom }}</h5>
						<h5 class="modal-title" v-else>@traduction('document.lignes_diverses.calculateur.titre') - @{{ detail_calculateur.calculateur.designation }}</h5>
					</div>

					<div class="modal-body css_form js_selection_element" >

						<div>
							<h6 style="font-weight:bold;">@traduction('document.lignes_diverses.coefficient.quantite')</h6>
							<div class="row">
								<span class="col-md-2">@traduction('document.colonnes.designation.titre')</span>
								<input type="text" class="col-md-4" @if($articles_modifiables !== true) disabled @endif v-if="detail_calculateur.type_ligne == 'calculateur'" v-model="detail_calculateur.nom">
								<input type="text" class="col-md-4" @if($articles_modifiables !== true) disabled @endif v-else-if="detail_calculateur.article_nomenclature_index >= 0" v-model="detail_calculateur.nomenclature[detail_calculateur.article_nomenclature_index].calculateur.designation"/>
								<input type="text" class="col-md-4" @if($articles_modifiables !== true) disabled @endif v-else-if="detail_calculateur.article_sous_nomenclature >= 0" v-model="detail_calculateur.nomenclature[detail_calculateur.article_nomenclature_index].nomenclature[detail_calculateur.article_sous_nomenclature].calculateur.designation"/>
								<input type="text" class="col-md-4" @if($articles_modifiables !== true) disabled @endif v-else v-model="detail_calculateur.calculateur.designation"/>
								<span class="col-md-2">@traduction('document.tableau_des_articles.modele_calculateur')</span>
								<select class="col-md-4" @if($articles_modifiables !== true) disabled @endif :value="detail_calculateur.modele_de_calculateur_id" @change="change_modele_de_calculateur(detail_calculateur,$event,detail_calculateur.article_nomenclature_index, detail_calculateur.article_nomenclature_index)">

									@foreach(modele('modele_de_calculateur')->get() as $modele)
										<option value="{{ $modele->id }}">{{ $modele->nom }}</option>
									@endforeach

								</select>
							</div>
						</div>

						<div style="margin-top:15px;">
							<h6 style="font-weight:bold;">@traduction('document.tableau_des_articles.creation_variables')</h6>
							<table class="table table-bordered table-hover">

								<thead>

								<tr class="css_tableau_titre">
									<th></th>
									<th>@traduction('document.tableau_des_articles.categorie')</th>
									<th>@traduction('document.tableau_des_articles.titre')</th>
									<th>@traduction('document.tableau_des_articles.valeur')</th>
									<th>@traduction('document.tableau_des_articles.resultat')</th>
									<th>@traduction('document.tableau_des_articles.variable')</th>
									<th>@traduction('document.tableau_des_articles.options')</th>
								</tr>

								</thead>
								<tbody class="deplacer_ligne_variable">

								<template v-if="detail_calculateur.type_ligne == undefined" v-for="article in article_a_parcourir(detail_calculateur)">
									<template v-if="article.calculateur != undefined && article.calculateur != null && Object.keys(article.calculateur).length > 0">
										<template v-for="(variable,variable_index) in article.calculateur.variables">
											<tr :position_vue="variable_index" :key="variable.ordre" :data-id="variable.ordre">
												<td v-if="article.designation">
													@traduction('document.tableau_des_articles.devis')(@{{ article.designation }})
													<span v-if="variable.erreur" style="color:red;font-size:15px;line-height: 30px" class="fas fa-times-circle"></span>
												</td>
												<td v-else>
													@traduction('document.tableau_des_articles.devis')(@{{ article.nom }})
													<span v-if="variable.erreur" style="color:red;font-size:15px;line-height: 30px" class="fas fa-times-circle"></span>
												</td>
												<td><input type="text" v-model="variable.categorie" readonly/></td>
												<td><input type="text" v-model="variable.titre" readonly/></td>
												<td><input type="text" :style="'background:' + variable.couleur" v-model="variable.valeur" readonly/></td>
												<td><input type="text" v-model="variable.resultat" disabled/></td>
												<td><input type="text" v-model="variable.nom" readonly/></td>
												<td></td>
											</tr>

										</template>
									</template>

								</template>

								<template v-if="detail_calculateur.type_ligne == 'regroupement'" v-for="article in article_a_parcourir(detail_calculateur)">

									<template v-if="article.calculateur != undefined && article.calculateur != null && Object.keys(article.calculateur).length > 0">

										<template v-for="(variable,variable_index) in article.calculateur.variables">
											<tr :position_vue="variable_index" :key="variable.ordre" :data-id="variable.ordre">
												<td v-if="article.designation">
													@traduction('document.tableau_des_articles.devis')(@{{ article.designation }})
													<span v-if="variable.erreur" style="color:red;font-size:15px;line-height: 30px" class="fas fa-times-circle"></span>
												</td>
												<td v-else>
													@traduction('document.tableau_des_articles.devis')(@{{ article.nom }})
													<span v-if="variable.erreur" style="color:red;font-size:15px;line-height: 30px" class="fas fa-times-circle"></span>
												</td>
												<td><input type="text" v-model="variable.categorie" readonly/></td>
												<td><input type="text" v-model="variable.titre" readonly/></td>
												<td><input type="text" :style="'background:' + variable.couleur" v-model="variable.valeur" readonly/></td>
												<td><input type="text" v-model="variable.resultat" disabled/></td>
												<td><input type="text" v-model="variable.nom" readonly/></td>
												<td></td>
											</tr>

										</template>

									</template>

								</template>

								<template v-if="detail_calculateur.calculateur != undefined && detail_calculateur.calculateur != null && Object.keys(detail_calculateur.calculateur).length > 0">
									<template v-for="(variable,variable_index) in detail_calculateur.calculateur.variables">

										<tr :class="detail_calculateur.article_nomenclature_index >= 0 || detail_calculateur.article_sous_nomenclature >= 0 ? '' : 'ligne_variable'" :position_vue="variable_index" :key="variable.ordre" :data-id="variable.ordre">
											<td>
												<span v-if="detail_calculateur.type_ligne == 'calculateur'">@traduction('document.tableau_des_articles.devis')</span>
												<span v-else-if="detail_calculateur.type_ligne == 'regroupement'">@traduction('document.lignes_diverses.regroupement.titre')</span>
												<span v-else-if="detail_calculateur.type_ligne == undefined && detail_calculateur.article_sous_nomenclature >= 0">@traduction('document.tableau_des_articles.article_grand_parent')</span>
												<span v-else-if="detail_calculateur.type_ligne == undefined && detail_calculateur.article_nomenclature_index >= 0">@traduction('document.tableau_des_articles.article_parent')</span>
												<span v-else-if="detail_calculateur.type_ligne == undefined">@traduction('document.tableau_des_articles.article')</span>
												<span v-if="detail_calculateur.designation">(@{{detail_calculateur.designation}})</span>
												<span v-else>(@{{detail_calculateur.nom}})</span>
												<span v-if="variable.erreur" style="color:red;font-size:15px;line-height: 30px" class="fas fa-times-circle"></span>
											</td>
											<td><input type="text" v-model="variable.categorie" @if($articles_modifiables !== true) disabled @endif :readonly="detail_calculateur.article_nomenclature_index >= 0 || detail_calculateur.article_sous_nomenclature >= 0 ? true : false"/></td>
											<td><input type="text" v-model="variable.titre" @if($articles_modifiables !== true) disabled @endif :readonly="detail_calculateur.article_nomenclature_index >= 0 || detail_calculateur.article_sous_nomenclature >= 0 ? true : false"/></td>
											<td><input type="text" :style="'background:' + variable.couleur" v-model="variable.valeur" @if($articles_modifiables !== true) disabled @endif @change="calculer_total_calculateur()" :readonly="detail_calculateur.article_nomenclature_index >= 0 || detail_calculateur.article_sous_nomenclature >= 0 ? true : false" /></td>
											<td><input type="text" v-model="variable.resultat" @if($articles_modifiables !== true) disabled @endif disabled/></td>
											<td><input type="text" v-model="variable.nom" @if($articles_modifiables !== true) disabled @endif @change="calculer_total_calculateur()" :readonly="detail_calculateur.article_nomenclature_index >= 0 || detail_calculateur.article_sous_nomenclature >= 0 ? true : false" /></td>
											<td> @if($articles_modifiables === true)<span class="far fa-trash-alt" @click="supprimer_element_calculateur(detail_calculateur.calculateur.variables,variable_index);calculer_total_calculateur();$forceUpdate()"></span>@endif</td>
										</tr>

									</template>
								</template>


								<template v-if="detail_calculateur.article_nomenclature_index >= 0 ">
									<tr v-for="(variable_nomenclature,variable_nomenclature_index) in detail_calculateur.nomenclature[detail_calculateur.article_nomenclature_index].calculateur.variables" class="ligne_variable" :position_vue="variable_nomenclature_index" :key="variable_nomenclature.ordre" :data-id="variable_nomenclature.ordre">
										<td>
											<span v-if="detail_calculateur.article_sous_nomenclature >= 0 ">@traduction('document.tableau_des_articles.article_parent')</span>
											<span v-else>@traduction('document.tableau_des_articles.article_enfant')</span>
											<span v-if="detail_calculateur.nomenclature[detail_calculateur.article_nomenclature_index].designation">(@{{detail_calculateur.nomenclature[detail_calculateur.article_nomenclature_index].designation}})</span>
											<span v-else>(@{{detail_calculateur.nomenclature[detail_calculateur.article_nomenclature_index].nom}})</span>
											<span v-if="variable_nomenclature.erreur" style="color:red;font-size:15px;line-height: 30px" class="fas fa-times-circle"></span>
										</td>
										<td><input @if($articles_modifiables !== true) disabled @endif type="text" v-model="variable_nomenclature.categorie" :readonly="detail_calculateur.article_sous_nomenclature >= 0 ? true : false"/></td>
										<td><input @if($articles_modifiables !== true) disabled @endif type="text" v-model="variable_nomenclature.titre" :readonly="detail_calculateur.article_sous_nomenclature >= 0 ? true : false"/></td>
										<td><input @if($articles_modifiables !== true) disabled @endif type="text" v-model="variable_nomenclature.valeur" @change="calculer_total_calculateur()" :readonly="detail_calculateur.article_sous_nomenclature >= 0 ? true : false"/></td>
										<td><input @if($articles_modifiables !== true) disabled @endif type="text" v-model="variable_nomenclature.resultat" disabled/></td>
										<td><input @if($articles_modifiables !== true) disabled @endif type="text" v-model="variable_nomenclature.nom" @change="calculer_total_calculateur()" :readonly="detail_calculateur.article_sous_nomenclature >= 0 ? true : false"/></td>
										<td> @if($articles_modifiables === true)<span class="far fa-trash-alt" @click="supprimer_element_calculateur(detail_calculateur.nomenclature[detail_calculateur.article_nomenclature_index].calculateur.variables,variable_nomenclature_index);calculer_total_calculateur();$forceUpdate()"></span> @endif</td>
									</tr>
								</template>

								<template v-if="detail_calculateur.article_sous_nomenclature >= 0 ">
									<tr v-for="(variable_sous_nomenclature,variable_sous_nomenclature_index) in detail_calculateur.nomenclature[detail_calculateur.article_nomenclature_index].nomenclature[detail_calculateur.article_sous_nomenclature].calculateur.variables" class="ligne_variable" :position_vue="variable_sous_nomenclature_index" :key="variable_sous_nomenclature.ordre" :data-id="variable_sous_nomenclature.ordre">
										<td>
											@traduction('document.tableau_des_articles.article_enfant')
											<span v-if="detail_calculateur.nomenclature[detail_calculateur.article_nomenclature_index].nomenclature[detail_calculateur.article_sous_nomenclature].designation">(@{{detail_calculateur.nomenclature[detail_calculateur.article_nomenclature_index].nomenclature[detail_calculateur.article_sous_nomenclature].designation}})</span>
											<span v-else>(@{{detail_calculateur.nomenclature[detail_calculateur.article_nomenclature_index].nomenclature[detail_calculateur.article_sous_nomenclature].nom}})</span>
											<span v-if="variable_sous_nomenclature.erreur" style="color:red;font-size:15px;line-height: 30px" class="fas fa-times-circle"></span>
										</td>
										<td><input @if($articles_modifiables !== true) disabled @endif type="text" v-model="variable_sous_nomenclature.categorie" /></td>
										<td><input @if($articles_modifiables !== true) disabled @endif type="text" v-model="variable_sous_nomenclature.titre" /></td>
										<td><input @if($articles_modifiables !== true) disabled @endif type="text" v-model="variable_sous_nomenclature.valeur" @change="calculer_total_calculateur()" /></td>
										<td><input @if($articles_modifiables !== true) disabled @endif type="text" v-model="variable_sous_nomenclature.resultat" disabled/></td>
										<td><input @if($articles_modifiables !== true) disabled @endif type="text" v-model="variable_sous_nomenclature.nom" @change="calculer_total_calculateur()" /></td>
										<td> @if($articles_modifiables === true)<span class="far fa-trash-alt" @click="supprimer_element_calculateur(detail_calculateur.nomenclature[detail_calculateur.article_nomenclature_index].nomenclature[detail_calculateur.article_sous_nomenclature].calculateur.variables,variable_sous_nomenclature_index);calculer_total_calculateur();$forceUpdate()"></span> @endif</td>
									</tr>
								</template>

								</tbody>

							</table>

							@if($articles_modifiables === true)
								<span v-if="detail_calculateur.article_sous_nomenclature >= 0" class="css_ajouter_ligne_nomenclature" @click="ajouter_variable_article(detail_calculateur.nomenclature[detail_calculateur.article_nomenclature_index].nomenclature[detail_calculateur.article_sous_nomenclature])">@traduction('document.tableau_des_articles.ajouter_variable')</span>
								<span v-else-if="detail_calculateur.article_nomenclature_index >= 0" class="css_ajouter_ligne_nomenclature" @click="ajouter_variable_article(detail_calculateur.nomenclature[detail_calculateur.article_nomenclature_index])">@traduction('document.tableau_des_articles.ajouter_variable')</span>
								<span v-else class="css_ajouter_ligne_nomenclature" @click="ajouter_variable_article(detail_calculateur)">@traduction('document.tableau_des_articles.ajouter_variable')</span>
							@endif
						</div>

						<div style="margin-top:15px;">
							<h6 style="font-weight:bold;">@traduction('document.tableau_des_articles.ponderations_conditionnelles')</h6>

							<template v-for="article in articles_du_document">
								<template v-if="article.calculateur != undefined && article.calculateur != null && Object.keys(article.calculateur).length > 0">
									<template v-for="(ponderation,ponderation_index) in article.calculateur.ponderation">
										<div v-if="article.type_ligne == 'calculateur' && detail_calculateur.type_ligne !='calculateur'" style="border: solid #bababa 1px;padding:10px; margin: 20px 0px;">
											<div class="row">
												<span class="col-md-2">@traduction('document.tableau_des_articles.variable')</span>
												<span class="col-md-2" style="font-weight:bold;">@traduction('document.tableau_des_articles.devis')</span>
												<input type="text" class="col-md-3" v-model="ponderation.designation" readonly></input>
												<input type="text" style="margin-left:10px;" class="col-md-3" v-model="ponderation.resultat" disabled></input>
											</div>
										</div>

										<div v-if="detail_calculateur.regroupement_id && article.type_ligne == 'regroupement' && detail_calculateur.regroupement_id == article.id &&  detail_calculateur.type_ligne == undefined" style="border: solid #bababa 1px;padding:10px; margin: 20px 0px;">
											<div class="row">
												<span class="col-md-2">@traduction('document.tableau_des_articles.variable')</span>
												<span class="col-md-2" style="font-weight:bold;">@traduction('document.lignes_diverses.regroupement.titre')</span>
												<input type="text" class="col-md-3" v-model="ponderation.designation" readonly></input>
												<input type="text" style="margin-left:10px;" class="col-md-3" v-model="ponderation.resultat" disabled></input>
											</div>
										</div>

									</template>
								</template>

							</template>

							<template v-if="detail_calculateur.article_nomenclature_index >= 0">
								<template v-if="detail_calculateur.calculateur != undefined && detail_calculateur.calculateur != null && Object.keys(detail_calculateur.calculateur).length > 0">
									<template v-for="(ponderation,ponderation_index) in detail_calculateur.calculateur.ponderation">

										<div style="border: solid #bababa 1px;padding:10px; margin: 20px 0px;">
											<div class="row">
												<span class="col-md-2">@traduction('document.tableau_des_articles.variable')</span>
												<span class="col-md-2" style="font-weight:bold;" v-if="detail_calculateur.article_sous_nomenclature >= 0">@traduction('document.tableau_des_articles.article_grand_parent')</span>
												<span class="col-md-2" style="font-weight:bold;" v-else>@traduction('document.tableau_des_articles.article_parent')</span>
												<input type="text" class="col-md-3" v-model="ponderation.designation" readonly></input>
												<input type="text" style="margin-left:10px;" class="col-md-3" v-model="ponderation.resultat" disabled></input>
											</div>
										</div>

									</template>
								</template>

							</template>

							<template v-if="detail_calculateur.article_sous_nomenclature >= 0">
								<template v-if="detail_calculateur.nomenclature[detail_calculateur.article_nomenclature_index].calculateur != undefined && detail_calculateur.nomenclature[detail_calculateur.article_nomenclature_index].calculateur != null && Object.keys(detail_calculateur.nomenclature[detail_calculateur.article_nomenclature_index].calculateur).length > 0">
									<template v-for="(ponderation,ponderation_index) in detail_calculateur.nomenclature[detail_calculateur.article_nomenclature_index].calculateur.ponderation">

										<div style="border: solid #bababa 1px;padding:10px; margin: 20px 0px;">
											<div class="row">
												<span class="col-md-2">@traduction('document.tableau_des_articles.variable')</span>
												<span class="col-md-2" style="font-weight:bold;">@traduction('document.tableau_des_articles.article_parent')</span>
												<input type="text" class="col-md-3" v-model="ponderation.designation" readonly></input>
												<input type="text" style="margin-left:10px;" class="col-md-3" v-model="ponderation.resultat" disabled></input>
											</div>
										</div>

									</template>
								</template>

							</template>

							<template  v-if="detail_calculateur.article_sous_nomenclature >= 0">
								<div v-for="(ponderation,ponderation_index) in detail_calculateur.nomenclature[detail_calculateur.article_nomenclature_index].nomenclature[detail_calculateur.article_sous_nomenclature].calculateur.ponderation" style="border: solid #bababa 1px;padding:10px; margin: 20px 0px;">
									<div class="row">
										<span class="col-md-2">@traduction('document.tableau_des_articles.variable')</span>
										<input type="text" @if($articles_modifiables !== true) disabled @endif class="col-md-4" v-model="ponderation.designation" @change="calculer_total_calculateur()"/>
										@if($articles_modifiables === true)
											<span style="line-height: 30px;" class="col-md-4 far fa-trash-alt" @click="supprimer_element_calculateur(detail_calculateur.nomenclature[detail_calculateur.article_nomenclature_index].nomenclature[detail_calculateur.article_sous_nomenclature].calculateur.ponderation,ponderation_index);calculer_total_calculateur();"></span>
										@endif
									</div>
									<table class="table" style="border:none;">
										<thead style="border:none;">

										<tr style="border:none;">
											<th style="border:none;font-weight:bold;">@traduction('document.tableau_des_articles.condition')</th>
											<th style="border:none;font-weight:bold;"></th>
											<th style="border:none;font-weight:bold;"></th>
											<th style="border:none;font-weight:bold;">@traduction('document.tableau_des_articles.critere')</th>
											<th style="border:none;font-weight:bold;">@traduction('document.tableau_des_articles.comp')</th>
											<th style="border:none;font-weight:bold;">@traduction('document.tableau_des_articles.valeur')</th>
											<th style="border:none;"></th>
											<th style="border:none;"></th>
											<th style="border:none;"></th>
											<th style="border:none;font-weight:bold;">@traduction('document.tableau_des_articles.resultat')</th>
											<th style="border:none;"></th>
										</tr>

										</thead>
										<tbody style="border:none;">

										<tr v-for="(condition,condition_index) in ponderation.conditions" style="border:none;">

											<td style="border:none;" class="align-top">

												<select @if($articles_modifiables !== true) disabled @endif v-model="condition.type" @change="check_condition(ponderation, condition);calculer_total_calculateur();" >

													<option value="0" v-html="$root.traduction('document.tableau_des_articles.sinon')"></option>
													<option value="1" v-html="$root.traduction('document.tableau_des_articles.si')"></option>

												</select>
											</td>


											<td style="border: none;">
												<template v-for="(donnees,index) in condition.donnees">
													@if($articles_modifiables === true)
														<span style="display: block;line-height: 30px" @click="supprimer_element_calculateur(condition.donnees, index);calculer_total_calculateur();$forceUpdate()" class="far fa-trash-alt"></span>
													@endif
												</template>
											</td>

											<td style="border: none;">
												<template v-for="(donnees,index) in condition.donnees" v-if="donnees.erreur">
													<span  style="color:red;font-size:15px;line-height: 30px" class="fas fa-times-circle"></span>
												</template>
											</td>

											<td style="border:none;">
												<template v-for="(donnees,index) in condition.donnees" >
													<input type="text" @if($articles_modifiables !== true) disabled @endif v-model="donnees.critere" @change="calculer_total_calculateur()"></input>
												</template>
											</td>
											<td style="border:none;">
												<template v-for="(donnees,index) in condition.donnees">
													<select @if($articles_modifiables !== true) disabled @endif v-model="donnees.comp" @change="calculer_total_calculateur()">

														<option value=">">></option>
														<option value="<"><</option>
														<option value=">=">>=</option>
														<option value="<="><=</option>
														<option value="==">=</option>

													</select>
												</template>
											</td>
											<td style="border:none;">

												<template v-for="(donnees,index) in condition.donnees">
													<input type="text" @if($articles_modifiables !== true) disabled @endif v-model="donnees.valeur" @change="calculer_total_calculateur()"></input>
												</template>

											</td>

											<td style="border:none;" class="align-bottom">

												<template v-for="(donnees,index) in condition.donnees" v-if="condition.donnees.length - 1 > index" @change="calculer_total_calculateur()">
													<select @if($articles_modifiables !== true) disabled @endif v-model="donnees.cond" @change="calculer_total_calculateur()">
														<option value="&&">@traduction('document.tableau_des_articles.et')</option>
														<option value="||">@traduction('document.tableau_des_articles.ou')</option>
													</select>
												</template>

											</td>

											<td style="border:none;" class="align-bottom">
												@if($articles_modifiables === true)
													<span v-if="condition.type == 1" class="far fa-plus-square" @click="ajouter_condition_conditions(ponderation, condition)"></span>
												@endif
											</td>
											<td style="border:none;" class="align-bottom">@traduction('document.tableau_des_articles.alors')</td>
											<td style="border:none;" class="align-bottom"><input type="text" @if($articles_modifiables !== true) disabled @endif v-model="condition.resultat" @change="calculer_total_calculateur()"></input></td>
											<td style="border:none;" class="align-bottom">
												@if($articles_modifiables === true)
													<span class="far fa-trash-alt" @click="supprimer_element_calculateur(ponderation, condition_index);$forceUpdate();"></span>
												@endif
											</td>


										</tr>

										</tbody>
									</table>
									@if($articles_modifiables === true)
										<span class="css_ajouter_ligne_nomenclature" @click="ajouter_condition_article(ponderation)">@traduction('document.tableau_des_articles.ajouter_condition')</span>
									@endif
								</div>
							</template>
							<template  v-else-if="detail_calculateur.article_nomenclature_index >= 0">
								<div v-for="(ponderation,ponderation_index) in detail_calculateur.nomenclature[detail_calculateur.article_nomenclature_index].calculateur.ponderation" style="border: solid #bababa 1px;padding:10px; margin: 20px 0px;">
									<div class="row">
										<span class="col-md-2">@traduction('document.tableau_des_articles.variable')</span>
										<input type="text" @if($articles_modifiables !== true) disabled @endif class="col-md-4" v-model="ponderation.designation" @change="calculer_total_calculateur()"/>
										@if($articles_modifiables === true)
											<span style="line-height: 30px;" class="col-md-4 far fa-trash-alt" @click="supprimer_element_calculateur(detail_calculateur.nomenclature[detail_calculateur.article_nomenclature_index].calculateur.ponderation,ponderation_index);calculer_total_calculateur();"></span>
										@endif
									</div>
									<table class="table" style="border:none;">
										<thead style="border:none;">

										<tr style="border:none;">
											<th style="border:none;font-weight:bold;">@traduction('document.tableau_des_articles.condition')</th>
											<th style="border:none;font-weight:bold;"></th>
											<th style="border:none;font-weight:bold;"></th>
											<th style="border:none;font-weight:bold;">@traduction('document.tableau_des_articles.critere')</th>
											<th style="border:none;font-weight:bold;">@traduction('document.tableau_des_articles.comp')</th>
											<th style="border:none;font-weight:bold;">@traduction('document.tableau_des_articles.valeur')</th>
											<th style="border:none;"></th>
											<th style="border:none;"></th>
											<th style="border:none;"></th>
											<th style="border:none;font-weight:bold;">@traduction('document.tableau_des_articles.resultat')</th>
											<th style="border:none;"></th>
										</tr>

										</thead>
										<tbody style="border:none;">

										<tr v-for="(condition,condition_index) in ponderation.conditions" style="border:none;">

											<td style="border:none;" class="align-top">

												<select @if($articles_modifiables !== true) disabled @endif v-model="condition.type" @change="check_condition(ponderation, condition);calculer_total_calculateur();" >

													<option value="0" v-html="$root.traduction('document.tableau_des_articles.sinon')"></option>
													<option value="1" v-html="$root.traduction('document.tableau_des_articles.si')"></option>

												</select>
											</td>


											<td style="border: none;">
												<template v-for="(donnees,index) in condition.donnees">
													@if($articles_modifiables === true)
														<span style="display: block;line-height: 30px" @click="supprimer_element_calculateur(condition.donnees, index);calculer_total_calculateur();$forceUpdate()" class="far fa-trash-alt"></span>
													@endif
												</template>
											</td>

											<td style="border: none;">
												<template v-for="(donnees,index) in condition.donnees" v-if="donnees.erreur">
													<span  style="color:red;font-size:15px;line-height: 30px" class="fas fa-times-circle"></span>
												</template>
											</td>

											<td style="border:none;">
												<template v-for="(donnees,index) in condition.donnees" >
													<input type="text" @if($articles_modifiables !== true) disabled @endif v-model="donnees.critere" @change="calculer_total_calculateur()"></input>
												</template>
											</td>
											<td style="border:none;">
												<template v-for="(donnees,index) in condition.donnees">
													<select @if($articles_modifiables !== true) disabled @endif v-model="donnees.comp" @change="calculer_total_calculateur()">

														<option value=">">></option>
														<option value="<"><</option>
														<option value=">=">>=</option>
														<option value="<="><=</option>
														<option value="==">=</option>

													</select>
												</template>
											</td>
											<td style="border:none;">

												<template v-for="(donnees,index) in condition.donnees">
													<input type="text" @if($articles_modifiables !== true) disabled @endif v-model="donnees.valeur" @change="calculer_total_calculateur()"></input>
												</template>

											</td>

											<td style="border:none;" class="align-bottom">

												<template v-for="(donnees,index) in condition.donnees" v-if="condition.donnees.length - 1 > index" @change="calculer_total_calculateur()">
													<select @if($articles_modifiables !== true) disabled @endif v-model="donnees.cond" @change="calculer_total_calculateur()">
														<option value="&&">@traduction('document.tableau_des_articles.et')</option>
														<option value="||">@traduction('document.tableau_des_articles.ou')</option>
													</select>
												</template>

											</td>

											<td style="border:none;" class="align-bottom">
												@if($articles_modifiables === true)
													<span v-if="condition.type == 1" class="far fa-plus-square" @click="ajouter_condition_conditions(ponderation, condition)"></span>
												@endif
											</td>
											<td style="border:none;" class="align-bottom">@traduction('document.tableau_des_articles.alors')</td>
											<td style="border:none;" class="align-bottom"><input type="text" @if($articles_modifiables !== true) disabled @endif v-model="condition.resultat" @change="calculer_total_calculateur()"></input></td>
											<td style="border:none;" class="align-bottom">
												@if($articles_modifiables === true)
													<span class="far fa-trash-alt" @click="supprimer_element_calculateur(ponderation, condition_index);$forceUpdate();"></span>
												@endif
											</td>


										</tr>

										</tbody>
									</table>
									@if($articles_modifiables === true)
										<span class="css_ajouter_ligne_nomenclature" @click="ajouter_condition_article(ponderation)">@traduction('document.tableau_des_articles.ajouter_condition')</span>
									@endif
								</div>
							</template>
							<template v-else>
								<div v-for="(ponderation,ponderation_index) in detail_calculateur.calculateur.ponderation" style="border: solid #bababa 1px;padding:10px; margin: 20px 0px;">
									<div class="row">
										<span class="col-md-2">@traduction('document.tableau_des_articles.variable')</span>
										<input type="text" @if($articles_modifiables !== true) disabled @endif class="col-md-4" v-model="ponderation.designation"/>
										@if($articles_modifiables === true)
											<span style="line-height: 30px;" class="col-md-4 far fa-trash-alt" @click="supprimer_element_calculateur(detail_calculateur.calculateur.ponderation,ponderation_index);calculer_total_calculateur();"></span>
										@endif
									</div>
									<table class="table" style="border:none;">
										<thead style="border:none;">

										<tr style="border:none;">
											<th style="border:none;font-weight:bold;">@traduction('document.tableau_des_articles.condition')</th>
											<th style="border:none;font-weight:bold;"></th>
											<th style="border:none;font-weight:bold;"></th>
											<th style="border:none;font-weight:bold;">@traduction('document.tableau_des_articles.critere')</th>
											<th style="border:none;font-weight:bold;">@traduction('document.tableau_des_articles.comp')</th>
											<th style="border:none;font-weight:bold;">@traduction('document.tableau_des_articles.valeur')</th>
											<th style="border:none;"></th>
											<th style="border:none;"></th>
											<th style="border:none;"></th>
											<th style="border:none;font-weight:bold;">@traduction('document.tableau_des_articles.resultat')</th>
											<th style="border:none;"></th>
										</tr>

										</thead>
										<tbody style="border:none;">

										<tr v-for="(condition,condition_index) in ponderation.conditions" style="border:none;">

											<td style="border:none;" class="align-top">
												<select v-model="condition.type" @if($articles_modifiables !== true) disabled @endif @change="check_condition(ponderation, condition);calculer_total_calculateur()">

													<option value="0" v-html="$root.traduction('document.tableau_des_articles.sinon')"></option>
													<option value="1" v-html="$root.traduction('document.tableau_des_articles.si')"></option>

												</select>
											</td>

											<td style="border: none;">
												<template v-for="(donnees,index) in condition.donnees">
													@if($articles_modifiables === true)
														<span style="display: block;line-height: 30px" @click="supprimer_element_calculateur(condition.donnees, index);calculer_total_calculateur();" class="far fa-trash-alt"></span>
													@endif
												</template>
											</td>

											<td style="border: none;">
												<template v-for="(donnees,index) in condition.donnees" v-if="donnees.erreur">
													<span  style="color:red;font-size:15px;line-height: 30px" class="fas fa-times-circle"></span>
												</template>
											</td>

											<td style="border:none;">
												<template v-for="(donnees,index) in condition.donnees">
													<input type="text" @if($articles_modifiables !== true) disabled @endif v-model="donnees.critere" @change="calculer_total_calculateur()"></input>
												</template>
											</td>
											<td style="border:none;">
												<template v-for="(donnees,index) in condition.donnees">
													<select @if($articles_modifiables !== true) disabled @endif v-model="donnees.comp" @change="calculer_total_calculateur()">

														<option value=">">></option>
														<option value="<"><</option>
														<option value=">=">>=</option>
														<option value="<="><=</option>
														<option value="==">=</option>

													</select>
												</template>
											</td>
											<td style="border:none;">

												<template v-for="(donnees,index) in condition.donnees">
													<input type="text" @if($articles_modifiables !== true) disabled @endif v-model="donnees.valeur" @change="calculer_total_calculateur()"></input>
												</template>

											</td>

											<td style="border:none;" class="align-top">

												<template v-for="(donnees,index) in condition.donnees" v-if="condition.donnees.length - 1 > index" >
													<select @if($articles_modifiables !== true) disabled @endif v-model="donnees.cond" @change="calculer_total_calculateur()">
														<option value="&&">@traduction('document.tableau_des_articles.et')</option>
														<option value="||">@traduction('document.tableau_des_articles.ou')</option>
													</select>
												</template>

											</td>

											<td style="border:none;" class="align-bottom">
												@if($articles_modifiables === true)
													<span v-if="condition.type == 1" class="far fa-plus-square" @click="ajouter_condition_conditions(ponderation, condition)"></span>
												@endif
											</td>
											<td style="border:none;" class="align-bottom">@traduction('document.tableau_des_articles.alors')</td>
											<td style="border:none;" class="align-bottom"><input type="text" @if($articles_modifiables !== true) disabled @endif v-model="condition.resultat" @change="calculer_total_calculateur()"></input></td>
											<td style="border:none;" class="align-bottom">
												@if($articles_modifiables === true)
													<span class="far fa-trash-alt" @click="supprimer_condition_article(ponderation, condition_index);calculer_total_calculateur();$forceUpdate();"></span>
												@endif
											</td>


										</tr>

										</tbody>
									</table>
									@if($articles_modifiables === true)
										<span class="css_ajouter_ligne_nomenclature" @click="ajouter_condition_article(ponderation)">@traduction('document.tableau_des_articles.ajouter_condition')</span>
									@endif
								</div>
							</template>

							@if($articles_modifiables === true)
								<span v-if="detail_calculateur.article_sous_nomenclature >= 0" class="css_ajouter_ligne_nomenclature" @click="ajouter_ponderation_article(detail_calculateur.nomenclature[detail_calculateur.article_nomenclature_index].nomenclature[detail_calculateur.article_sous_nomenclature])">@traduction('document.tableau_des_articles.ajouter_ponderation')</span>
								<span v-else-if="detail_calculateur.article_nomenclature_index >= 0" class="css_ajouter_ligne_nomenclature" @click="ajouter_ponderation_article(detail_calculateur.nomenclature[detail_calculateur.article_nomenclature_index])">@traduction('document.tableau_des_articles.ajouter_ponderation')</span>
								<span v-else class="css_ajouter_ligne_nomenclature" @click="ajouter_ponderation_article(detail_calculateur)">@traduction('document.tableau_des_articles.ajouter_ponderation')</span>
							@endif
						</div>

						<div style="margin-top:15px;" v-if="detail_calculateur.type_ligne == undefined">
							<h6 style="font-weight:bold;">@traduction('document.tableau_des_articles.calcul_quantite')</h6>
							<div class="row">
								<span class="col-md-2">@traduction('document.tableau_des_articles.formule')</span>
								<span v-if="detail_calculateur.article_sous_nomenclature >= 0 && detail_calculateur.nomenclature[detail_calculateur.article_nomenclature_index].nomenclature[detail_calculateur.article_sous_nomenclature].calculateur.calcul.erreur" style="color:red;font-size:15px;line-height: 30px" class="fas fa-times-circle"></span>
								<span v-else-if="detail_calculateur.article_nomenclature_index >= 0 && detail_calculateur.nomenclature[detail_calculateur.article_nomenclature_index].calculateur.calcul.erreur" style="color:red;font-size:15px;line-height: 30px" class="fas fa-times-circle"></span>
								<span v-else-if="detail_calculateur.article_nomenclature_index == 'false' && detail_calculateur.calculateur.calcul.erreur" style="color:red;font-size:15px;line-height: 30px" class="fas fa-times-circle"></span>
								<input type="text" @if($articles_modifiables !== true) disabled @endif class="col-md-4" v-if="detail_calculateur.article_sous_nomenclature >= 0"  v-model="detail_calculateur.nomenclature[detail_calculateur.article_nomenclature_index].nomenclature[detail_calculateur.article_sous_nomenclature].calculateur.calcul.formule" @change="calculer_total_calculateur()"></input>
								<input type="text" @if($articles_modifiables !== true) disabled @endif class="col-md-4" v-else-if="detail_calculateur.article_nomenclature_index >= 0"  v-model="detail_calculateur.nomenclature[detail_calculateur.article_nomenclature_index].calculateur.calcul.formule" @change="calculer_total_calculateur()"></input>
								<input type="text" @if($articles_modifiables !== true) disabled @endif class="col-md-4" v-else v-model="detail_calculateur.calculateur.calcul.formule" @change="calculer_total_calculateur()"/>
								<span class="col-md-2">@traduction('document.tableau_des_articles.total')</span>
								<input v-if="detail_calculateur.article_sous_nomenclature >= 0" class="col-md-3" v-model="detail_calculateur.nomenclature[detail_calculateur.article_nomenclature_index].nomenclature[detail_calculateur.article_sous_nomenclature].calculateur.resultat_calcul" readonly/>
								<input v-else-if="detail_calculateur.article_nomenclature_index >= 0" class="col-md-3" v-model="detail_calculateur.nomenclature[detail_calculateur.article_nomenclature_index].calculateur.resultat_calcul" readonly/>
								<input v-else-if="detail_calculateur.article_nomenclature_index == 'false'" class="col-md-3" v-model="detail_calculateur.calculateur.resultat_calcul" readonly/>
							</div>
							<div class="row">
								<span class="col-md-6"></span>
								<span class="col-md-2">@traduction('document.tableau_des_articles.quantite')</span>
								<input class="col-md-3" @if($articles_modifiables !== true) disabled @endif :style="retourne_border_input_quantite_pour_calculateur(detail_calculateur)" v-if="detail_calculateur.article_sous_nomenclature >= 0"  v-model="detail_calculateur.nomenclature[detail_calculateur.article_nomenclature_index].nomenclature[detail_calculateur.article_sous_nomenclature].quantite" @change="changement_quantite_calculateur(detail_calculateur.nomenclature[detail_calculateur.article_nomenclature_index].nomenclature[detail_calculateur.article_sous_nomenclature])"/>
								<input class="col-md-3" @if($articles_modifiables !== true) disabled @endif :style="retourne_border_input_quantite_pour_calculateur(detail_calculateur)" v-else-if="detail_calculateur.article_nomenclature_index >= 0"  v-model="detail_calculateur.nomenclature[detail_calculateur.article_nomenclature_index].quantite" @change="changement_quantite_calculateur(detail_calculateur.nomenclature[detail_calculateur.article_nomenclature_index])"/>
								<input class="col-md-3" @if($articles_modifiables !== true) disabled @endif :style="retourne_border_input_quantite_pour_calculateur(detail_calculateur)" v-else v-model="detail_calculateur.quantite" @change="changement_quantite_calculateur(detail_calculateur)"/>
							</div>
						</div>

					</div>

					<div class="modal-footer" style="margin-top:10px;">
							<button type="button" class="btn btn-secondary" @click="fermer_calculateur();">@traduction('interface.modales.fermer')</button>
						@if($articles_modifiables === true)
							<button type="button" class="btn btn-danger" @click="supprimer_certain_calculateur();">@traduction('interface.modales.supprimer')</button>
						@endif
					<!--						<button type="button" class="btn btn-primary" @click="calculer_total_calculateur()">Calculer</button>-->
					</div>

				</div>
			</div>
		</div>
	</transition>
	<transition name="modal" v-if="afficher_modale_supprimer_calculateur">
		<div class="modal-mask">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">

					<div class="modal-header">
						<h5 class="modal-title">@traduction('document.tableau_des_articles.supprimer_calculateur')</h5>
					</div>

					<div class="modal-body css_form js_selection_element" >

						<h6>@traduction('document.tableau_des_articles.confirmation_suppression_calculateur')</h6>

						<button type="button" class="btn btn-danger" @click="supprimer_definitivement_calculateur(detail_calculateur);">@traduction('interface.modales.supprimer')</button>
						<button type="button" class="btn btn-secondary" @click="afficher_modale_supprimer_calculateur = false">@traduction('interface.modales.fermer')</button>

					</div>
				</div>
			</div>
		</div>

	</transition>
</template>

@push('donnees_pour_vuejs_data')
	chargement_tableau_article: false,
	ligne_article_active: null,
	ligne_article_focus: null,
	timer_survol_ligne_article: null,
	delai_survol_ligne_article: 0,
	tailles_par_type_colonne: {
		'selection_element' : 1.5,
		'select' : 1.25,
		'montant' : 1,
		'texte' : 3,
	},
@endpush

@push('donnees_pour_vuejs_methods')

	cle_ligne_article : function(article_sur_document, article_index){

		if(article_sur_document.id_provisoire)
			return 'p' + article_sur_document.id_provisoire;

		if(article_sur_document.id)
			return 'i' + article_sur_document.id;

		return 'x' + article_index;
	},

	ligne_article_est_active : function(article_sur_document, article_index){

		if(this.ligne_article_active === null)
			return false;

		return this.ligne_article_active === this.cle_ligne_article(article_sur_document, article_index);
	},

	survoler_ligne_article : function(article_sur_document, article_index){

		if(this.ligne_article_focus !== null)
			return;

		var cle = this.cle_ligne_article(article_sur_document, article_index);

		if(this.ligne_article_active === cle)
			return;

		if(this.timer_survol_ligne_article)
			clearTimeout(this.timer_survol_ligne_article);

		if(this.delai_survol_ligne_article <= 0){

			this.ligne_article_active = cle;

			return;
		}

		this.timer_survol_ligne_article = setTimeout(() => {

			if(this.ligne_article_focus !== null)
				return;

			this.ligne_article_active = cle;

		}, this.delai_survol_ligne_article);
	},

	debut_deplacement_ligne_article : function(){

		// aucune ligne en edition pendant un deplacement : la cle d'une ligne sans identifiant
		// repose sur sa position, l'edition suivrait la position au lieu de la ligne
		if(this.timer_survol_ligne_article)
			clearTimeout(this.timer_survol_ligne_article);

		this.ligne_article_active = null;
		this.ligne_article_focus = null;
	},

	quitter_tableau_articles : function(){

		if(this.timer_survol_ligne_article)
			clearTimeout(this.timer_survol_ligne_article);

		if(this.ligne_article_focus !== null)
			return;

		this.ligne_article_active = null;
	},

	focus_ligne_article : function(article_sur_document, article_index){

		this.ligne_article_focus = this.cle_ligne_article(article_sur_document, article_index);
	},

	fin_focus_ligne_article : function(evenement){

		if(evenement && evenement.relatedTarget && evenement.currentTarget && evenement.currentTarget.contains(evenement.relatedTarget))
			return;

		this.ligne_article_focus = null;
	},

	activer_ligne_article : function(article_sur_document, article_index, evenement){

		if(this.timer_survol_ligne_article)
			clearTimeout(this.timer_survol_ligne_article);

		var cle = this.cle_ligne_article(article_sur_document, article_index);

		if(this.ligne_article_active === cle)
			return;

		this.ligne_article_active = cle;

		var cellule = evenement && evenement.target ? evenement.target.closest('.cellule_document_colonne_article') : null;

		if(cellule == null)
			return;

		this.$nextTick(() => {

			var champ = cellule.querySelector('input:not([type=hidden]), select, textarea');

			if(champ == null)
				return;

			champ.focus();

			if(typeof champ.select == 'function')
				champ.select();
		});
	},

	retourne_couleur_regroupement_pour_ligne: function(article, liste_bordures) {

		var vue_composant = this;

		if((article.couleur_regroupement === false || article.couleur_regroupement == undefined || article.couleur_regroupement == ''))
			return '';

		// Si le regroupement a la couleur par défaut et qu'il est contenu dans un groupement, on lui attribue la couleur
		if(
			(article.type_ligne == 'regroupement' && article.couleur_regroupement == '{{fonctionnalite('couleur_regroupement_articles_documents')}}' && article.regroupement_id != 0 && typeof article.regroupement_id != 'undefined')
			) {

			article.couleur_regroupement = "{{fonctionnalite('couleur_sous_regroupement_articles_documents')}}";
			vue_composant.changement_couleur_regroupement_chaque_article();
		}

		// Si le regroupement a la couleur des sous-regroupements, mais qu'il n'est pas dans un regroupement, on le remet à la couleur des regroupements de base
		if(
			(article.type_ligne == 'regroupement' && article.couleur_regroupement == '{{fonctionnalite('couleur_sous_regroupement_articles_documents')}}' && (article.regroupement_id == 0 || typeof article.regroupement_id == 'undefined'))
			) {

			article.couleur_regroupement = "{{fonctionnalite('couleur_regroupement_articles_documents')}}";
			vue_composant.changement_couleur_regroupement_chaque_article();
		}


		var style = ';color:'+article.couleur_regroupement+';';

		liste_bordures.forEach(function(quelle_bordure) {

			if(quelle_bordure == 'top' && article.type_ligne == 'regroupement' && article.regroupement_id != undefined && article.regroupement_id != false)
				return style;

			if(quelle_bordure == 'bottom' && article.type_ligne == 'regroupement_fermeture'){

				var regroupement = vue_composant.articles_du_document.filter(ligne => ligne.id == article.regroupement_id && ligne.type_ligne == "regroupement");

				if(regroupement.length > 0 && regroupement[0].regroupement_id != undefined && regroupement[0].regroupement_id != false)
					return style;

			}

			style += 'border-'+quelle_bordure+': 5px solid '+article.couleur_regroupement+';';

			if(article.type_ligne == 'regroupement') {

				if(article.afficher_regroupement !== true && article.afficher_regroupement !== 'true')
					style += 'border-bottom: 5px solid '+article.couleur_regroupement+';';
			}

		});

		return style;
	},

	class_regroupement : function(article_index){

		var classes = [];

		var vue_composant = this;

		for(let i = 0; i < article_index; i++){

			var article = vue_composant.articles_du_document[i];

			if(article.type_ligne != undefined && article.type_ligne == "regroupement")
				classes.push('regroupement_lie_' + article.id);
			else if(article.type_ligne != undefined && article.type_ligne == "regroupement_fermeture" && classes.includes('regroupement_lie_' + article.regroupement_id)){
				var position_a_supprimer = classes.indexOf('regroupement_lie_' + article.regroupement_id)
				classes.splice(position_a_supprimer);
			}

		}

		if(classes.length > 0)
			return classes.join(' ');
		else
			return '';

	},


    article_a_parcourir : function(article, lignes_a_retourner = []){

        var vue_instance = this;

        vue_instance.articles_du_document.forEach(function(article_du_document, index){

            var deja_dans_le_tableau = lignes_a_retourner.filter(ligne => JSON.stringify(ligne) == JSON.stringify(article_du_document));

            if(deja_dans_le_tableau.length > 0)
                return;

            if(article_du_document.type_ligne == "regroupement" && article.regroupement_id == article_du_document.id){

                lignes_a_retourner = vue_instance.article_a_parcourir(article_du_document, lignes_a_retourner);
                lignes_a_retourner.push(article_du_document);

            }

            if(article_du_document.type_ligne == "calculateur")
                lignes_a_retourner.push(article_du_document);

        });

        return lignes_a_retourner;

    },

	couleur_calculateur_article: (article_sur_document) => {

	    var style = '';

	    if (article_sur_document.calculateur != undefined && article_sur_document.calculateur.resultat_calcul != undefined && article_sur_document.calculateur.resultat_calcul != '' && article_sur_document.calculateur.resultat_calcul != article_sur_document.quantite)
			style += 'background-color: #e98a1df5;';
		else if ((article_sur_document.calculateur && article_sur_document.calculateur.erreur == false) || ((article_sur_document.calculateur == undefined || article_sur_document.calculateur == null || Object.keys(article_sur_document.calculateur).length == 0) && article_sur_document.erreur == false) || (article_sur_document.calculateur && article_sur_document.calculateur.erreur == undefined && article_sur_document.erreur == false))
			style += 'background-color: yellowgreen;';
		else if ((article_sur_document.calculateur && article_sur_document.calculateur.erreur == true) || ((article_sur_document.calculateur == undefined || article_sur_document.calculateur == null || Object.keys(article_sur_document.calculateur).length == 0) && article_sur_document.erreur == true) || (article_sur_document.calculateur && article_sur_document.calculateur.erreur == undefined && article_sur_document.erreur == true))
			style += 'background-color: red;';

		return style;

	},

	couleur_calculateur_regroupement: (regroupement) => {

	    var style = '';

	    if (regroupement.calculateur && regroupement.calculateur.erreur == false)
			style += 'background-color: yellowgreen;';
		else if (regroupement.calculateur && regroupement.calculateur.erreur == true)
			style += 'background-color: red;';

		return style;

	},

    article_non_utilisable(article) {

		if(article.type_ligne != null)
			return '';

        var modele = article.modele ?? article;

        if(
            modele.inactif
            || (modele.disponible_pour_saisie === 1 && !{!! json_encode(\App\Eden\Variables::$documents_vente_gescom) !!}.includes(this.type_element))
            || (modele.disponible_pour_saisie === 2 && !{!! json_encode(\App\Eden\Variables::$documents_achat_gescom) !!}.includes(this.type_element))
            || (modele.disponible_pour_saisie === 4 && !{!! json_encode(\App\Eden\Variables::$documents_avoir_et_retour_vente) !!}.includes(this.type_element))
            || modele.disponible_pour_saisie === 3
        )
            return 'non_utilisable';

        return '';

    },

	fin_deplacement : function(event){

		var elements_deplaces = Array.isArray(event.moved) ? event.moved : [event.moved];

		for(element_deplace of elements_deplaces){

			if(element_deplace.element.type_ligne != 'regroupement' && element_deplace.element.type_ligne != 'regroupement_fermeture'){
				var nouvel_index = element_deplace.newIndex;
				var nouvel_element = this.articles_liste[nouvel_index];

				var derniers_regroupements = [];

				for(index_article in this.articles_liste){

					article = this.articles_liste[index_article];

					if(index_article < nouvel_index){
						if(article.type_ligne == 'regroupement' && article.afficher_regroupement === true)
							derniers_regroupements.push(article.id);
						else if(article.type_ligne == 'regroupement_fermeture')
							derniers_regroupements.splice(derniers_regroupements.indexOf(article.regroupement_id),1);
					}
				}

				var regroupement_id = derniers_regroupements[derniers_regroupements.length-1] ?? null;

				if(regroupement_id != nouvel_element.regroupement_id){
					this.$set(nouvel_element,'regroupement_id',regroupement_id);
					this.$set(nouvel_element,'couleur_regroupement',regroupement_id != null ? this.articles_du_document.filter(article => article.id == regroupement_id)[0].couleur_regroupement : null);
				}
			}
		}
	},

	ajout_article_index : function(articles,index_prochain_article,regroupement_id){

		for(article_regroupement of this.articles_du_document.filter(article_du_doc => article_du_doc.regroupement_id == regroupement_id)){

			index_prochain_article++;
			articles.splice(index_prochain_article,0,article_regroupement);

			if(article_regroupement.type_ligne == 'regroupement')
				[articles,index_prochain_article] = this.ajout_article_index(articles,index_prochain_article,article_regroupement.id);
		}

		return [articles,index_prochain_article];
	},

	grid_template_columns : function(){

		var colonnes = Object.values(this.colonnes_articles);
		colonnes = colonnes.filter(colonne => !colonne.condition_v_if || eval('this.'+colonne.condition_v_if));
		
		colonnes = colonnes.map(colonne => {

			if(colonne.type_colonne == undefined)
				colonne.type_colonne = 'montant';

			return colonne;
		});

		return colonnes.map(colonne => 'minmax(0, ' + this.tailles_par_type_colonne[colonne.type_colonne] + 'fr)').join(' ');
	},

@endpush

@push('donnees_pour_vuejs_computed')

	taille_colonne : function(){

		var taille_colonne = Object.values(this.colonnes_articles).filter((colonne) => {return colonne.masquer != 1}).length;

		for(colonne of Object.values(this.colonnes_articles)){

			if(colonne.condition_v_if != null && !eval('this.'+colonne.condition_v_if))
				taille_colonne--;
		}

		return taille_colonne;
	},

	articles_liste : {

		get(){

			var articles_liste = [];

			for(index_article in this.articles_du_document){

				var article_du_document = this.articles_du_document[index_article];
				article_du_document.index_article = index_article;

				if(this.nomenclature_affiche(article_du_document.regroupement_id))
					articles_liste.push(article_du_document);
			}

			return articles_liste;
		},

		set(articles){

			var index_prochain_article = -1;

			for(index_article in articles){

				index_prochain_article++;
	
				var article = articles[index_prochain_article];

				if(article.type_ligne == 'regroupement' && article.afficher_regroupement !== true)
					[articles,index_prochain_article] = this.ajout_article_index(articles,index_prochain_article,article.id);
			}

			this.articles_du_document = articles;

			this.mise_a_jour_total_document_vue();
		},
	},

	
@endpush
