{{-- Regroupement --}}
{{-- Select & Move --}}
<div class="document_ligne_drag_drop" :style="retourne_couleur_regroupement_pour_ligne(article_sur_document, ['left', 'top'])">
	@if(empty($recapitulatif))
		<div class="css_flex_actions_article_document">
			<div class="haut_actions_article">
				<span class="css_move_article_document handle" v-if="!article_sur_document.afficher_regroupement">
					<i class="fas fa-arrows-alt"></i>
				</span>
				<input type="color" v-show="article_sur_document.afficher_regroupement === true || article_sur_document.afficher_regroupement === 'true'" 
					v-model="article_sur_document.couleur_regroupement" class="document_ligne_regroupement_couleur" @change="changement_couleur_regroupement_chaque_article()">

				<label class="css_checkbox_article_document" v-if="!article_sur_document.afficher_regroupement">
					<input type="checkbox" v-model="lignes_selectionnes" :value="article_sur_document.index_article" @change="selections_enfants_regroupement(article_sur_document.id, !lignes_selectionnes.includes(article_sur_document.index_article))">
					<span class="checkmark"></span>
				</label>
			</div>

			<div class="mb-1 css_btn_action_article_document document_ligne_drag_drop_conteneur_coller" 
				:title="traduction('document.blocs.saisie_des_articles.actions.coller_ligne')"
				v-if="elements_a_coller !== false"
				@click="coller_lignes(article_sur_document.afficher_regroupement ? article_sur_document.index_article :
				articles_du_document.filter(article => article.type_ligne == 'regroupement_fermeture' && article.regroupement_id == article_sur_document.id)[0].index_article)">
				<i class="fa fa-paste"></i>
				<i class="fa fa-arrow-down"></i>
			</div>
		</div>
	@endif
</div>
<div class="document_contenu_ligne_diverse document_ligne_regroupement" :style="retourne_couleur_regroupement_pour_ligne(article_sur_document, ['top'])">
	<div>
		<div class="cellule_designation_article">
			<template v-if="article_sur_document.afficher_regroupement === true || article_sur_document.afficher_regroupement === 'true'">
				<div class="document_ligne_regroupement_champs">
					<span class="document_ligne_regroupement_champs_crochet">[</span>
					<div class="css_label_input_article_document w-100">
						<span>@traduction('document.lignes_diverses.regroupement.titre') :</span>
						@if($articles_modifiables === true && empty($recapitulatif))
							<input type="text" class="css_input_article_document js_focus" v-model="article_sur_document.nom" :class="{@foreach($style_ligne_document as $style) style_ligne_document_{{$style->id}} : article_sur_document.id_style_ligne_document == {{$style->id}}, @endforeach}">
						@else
							@{{ article_sur_document.nom }}
						@endif
					</div>
				</div>
				@include('eden::formulaires.include.document.includes.lignes_diverses.regroupement_champs_supplementaires')
			</template>
			<div class="d-flex" v-if="article_sur_document.afficher_regroupement !== true && article_sur_document.afficher_regroupement !== 'true'">
				@traduction('document.lignes_diverses.regroupement.titre') : <b>@{{ article_sur_document.nom }}</b>
			</div>
			@if($management->est_une_vente() && fonctionnalite('utiliser_les_coefficients'))
				<div v-show="article_sur_document.afficher_regroupement === true || article_sur_document.afficher_regroupement === 'true'">
					<label for="" class="w-100" v-if="article_sur_document.coefficient != undefined && article_sur_document.coefficient.length != 0">
						<span>@traduction('document.lignes_diverses.regroupement.coefficients')</span><br/>
						@if($articles_modifiables === true)
							<template v-for="coefficient in article_sur_document.coefficient">
								<div class="designation_coefficient_article">
									<span
										class="css_ajouter_ligne_nomenclature"
										@click="
											masquer_coefficient_sur_document(article_sur_document,coefficient.id);
											modification_coeff_regroupement(article_sur_document)
										"
									>
										<i
											class="far fa-trash-alt"
											data-toggle="tooltip"
											data-position="top"
											:data-original-title="traduction('document.colonnes.designation.coefficient.supprimer')"
										></i>
									</span>
									<div class="d-flex designation_coefficient_regroupement_input">
										<label for="" class="designation_coefficient_article_label">@traduction('document.lignes_diverses.regroupement.denomination') :</label>
										<input type="text" name="" id="" :placeholder="traduction('document.lignes_diverses.regroupement.denomination')" v-model="coefficient.nom">
									</div>
									<div class="d-flex designation_coefficient_regroupement_input">
										<label for="" class="designation_coefficient_article_label">@traduction('document.lignes_diverses.regroupement.type') :</label>
										<select v-model="coefficient.type">
											<option value="0">
												{{traduction('document.lignes_diverses.regroupement.pourcentage')}}
											</option>
										</select>
									</div>
									<div class="d-flex designation_coefficient_regroupement_input">
										<label for="" class="designation_coefficient_article_label">@traduction('document.lignes_diverses.regroupement.quantite') :</label>
										<input type="number" class="js_attention_virgule" name="" id="" :placeholder="traduction('document.lignes_diverses.regroupement.quantite')" @change="modification_coeff_regroupement(article_sur_document)"  v-model="coefficient.quantite" @wheel.prevent @keydown.up.prevent @keydown.down.prevent>
									</div>

									<span class="font-weight-bold designation_coefficient_regroupement_input" v-if="coefficient.type == 0">
										@traduction('document.lignes_diverses.regroupement.total_coefficient') : @{{ (parseFloat(article_sur_document.total_sans_coeff) * (coefficient.quantite/100)).toFixed(2) }}

									</span>
									<span class="font-weight-bold designation_coefficient_regroupement_input" v-else>
										@traduction('document.lignes_diverses.regroupement.total_coefficient') : @{{ coefficient.quantite }}
									</span>
								</div>
							</template>
						@else
							<template v-for="coefficient in article_sur_document.coefficient">
								<br/>@{{article_sur_document.coefficient.nom}}
							</template>
						@endif
					</label>
				</div>
			@endif
		</div>
	</div>
	<div class="document_ligne_regroupement_totaux">
		<div class="document_ligne_regroupement_total">
			
			@traduction('document.lignes_diverses.regroupement.total_ht')
			<span class="css_prix_ligne_article_document" v-html="total_regroupement_affiche(article_sur_document)"></span>
		</div>
		@if($management->_type_element == 'devis_vente')
			<div class="document_ligne_regroupement_total" v-if="article_sur_document.regroupement_id == null || article_sur_document.regroupement_id == 0">
				<span>Option : </span>
				<div>
					<label class="switch">
						<input type="checkbox" v-model="article_sur_document.contenu" @if(!empty($recapitulatif)) disabled @endif @change="mise_a_jour_total_document_vue" :true-value="1" :false-value="0">
						<span class="slider round"></span>
					</label>
				</div>
			</div>
		@endif
	</div>
</div>
{{-- Afficher & Supprimer --}}
<div class="document_ligne_options" :style="retourne_couleur_regroupement_pour_ligne(article_sur_document, ['top', 'right'])">
	@if(empty($recapitulatif))
		<div class="css_actions_article_document">
			<span  class="mb-1 css_btn_action_article_document" :class="{ 'fas fa-toggle-off': !article_sur_document.afficher_regroupement, 'fas fa-toggle-on': article_sur_document.afficher_regroupement }" :title="(article_sur_document.afficher_regroupement) ? traduction('interface.document.regroupement.masquer') : traduction('interface.document.regroupement.afficher')" @click.prevent="afficher_regroupement(article_sur_document)"></span>

			<template v-if="article_sur_document.afficher_regroupement === true">
				@if(fonctionnalite('calculateur_sur_document'))
					<span
							:title="traduction('document.colonnes.designation.afficher_calculateur')"
							class="mb-1 css_btn_action_article_document"
							@click="afficher_calculateur(article_sur_document);"
							:style="couleur_calculateur_regroupement(article_sur_document)"
					>
						<i class="fas fa-calculator"></i>
					</span>
				@endif

				<!-- Coefficient -->
				@if($articles_modifiables === true && empty($recapitulatif) && fonctionnalite('utiliser_les_coefficients'))
					<span class="mb-1 fas fa-euro-sign css_btn_action_article_document" @click="ajouter_coefficient_article(article_sur_document)" :title="traduction('interface.document.regroupement.ajouter_coefficient')"></span>
				@endif

				<span class="mb-1 css_btn_action_article_document" @click="ajout_article_par_modale(article_sur_document,'regroupement');"><i class="fas fa-plus"></i></span>
				<span class="mb-1 css_btn_action_article_document" @click="suppression_regroupement(article_sur_document,article_index);mise_a_jour_total_document_vue();"><i class="far fa-trash-alt"></i></span>

				@include('eden::formulaires.include.document_style_ligne_document')
			</template>
		</div>
	@endif
</div>