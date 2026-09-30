{{-- Select & Move --}}
@if(empty(moi_extranet()))
	<div class="document_ligne_drag_drop" :style="retourne_couleur_regroupement_pour_ligne(article_sur_document, ['left'])">
		@if(empty($recapitulatif))
			<div class="css_flex_actions_article_document">
				<div class="haut_actions_article">
					<span class="css_move_article_document handle">
						<i class="fas fa-arrows-alt"></i>
					</span>
					<label class="css_checkbox_article_document">
						<input type="checkbox" v-model="lignes_selectionnes" :value="article_sur_document.index_article">
						<span class="checkmark"></span>
					</label>

					@if(fonctionnalite('gescom_affichage_compteurs_articles') && !isset($ligne_divers))
						<div class="css__numero_de_ligne"></div>
					@endif
				</div>

				<div class="document_ligne_drag_drop_conteneur_coller mb-1 css_btn_action_article_document"
					:title="traduction('document.blocs.saisie_des_articles.actions.coller_ligne')"  
					v-if="elements_a_coller !== false" @click="coller_lignes(article_sur_document.index_article)">
					<i class="fa fa-paste"></i>
					<i class="fa fa-arrow-down"></i>
				</div>
			</div>
		@endif
	</div>
@endif