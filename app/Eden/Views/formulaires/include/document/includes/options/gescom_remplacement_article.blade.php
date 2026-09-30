<!-- Cas classique, document modifiable -->
@if($articles_modifiables === true)
	<div style="position:relative;" v-clique_en_dehors="{ func: () => {if(popover_articles_substitution == article_sur_document.index_article) popover_articles_substitution = null}}">
		<a class="mb-1 css_btn_action_article_document" 
			@click="chargement_articles_substitution(article_sur_document)" target="_blank" :title="traduction('interface.document.tableau_des_articles.remplacer_article')">
			<i class="fa fa-refresh"></i>
		</a>
		<div v-show="popover_articles_substitution === article_sur_document.index_article" class="popover_remplacement_article">
			<span class="ligne_selection" v-for="article_substitution in articles_substitution" 
				v-html="article_substitution.affichage_pour_recherche" 
				@click="popover_articles_substitution=false;index_article_remplacement = article_sur_document.index_article;remplacer_article_via_modale(article_substitution)" >
			</span>
			<span class="ligne_selection" @click="remplacement_article(article_sur_document.index_article)">
				<i class="fas fa-mouse-pointer"></i>
				<span v-html="traduction('document.blocs.articles.remplacement_article.selection_autre_article')">	</span>
			</span>
		</div>
	</div>
@endif

@push('donnees_pour_vuejs_data')
	afficher_modale_remplacement_article: false,
	index_article_remplacement: false,
	articles_substitution: [],
	popover_articles_substitution: null,
@endpush

@push('donnees_pour_vuejs_methods')

	remplacement_article(index_article) {

		this.index_article_remplacement = index_article;
		this.afficher_modale_remplacement_article = true;
		this.valeur_champ_recherche = '';
	},


	remplacer_article_via_modale: async function(article) {

		var article_sur_document = this.articles_du_document[this.index_article_remplacement];
		await this.ajoute_article_au_document_vue(article.id, article_sur_document.quantite,true, false,false, {index_article_reference : this.index_article_remplacement});

		this.supprimer_article_du_document(article_sur_document, this.index_article_remplacement);

		this.afficher_modale_remplacement_article = false;

	},

	chargement_articles_substitution : async function(article_sur_document) {

		if(article_sur_document.modele.articles_substitution.length == 0)
			return this.remplacement_article(article_sur_document.index_article);

		if(article_sur_document.index_article === this.popover_articles_substitution) {
			this.popover_articles_substitution = null;
			return;
		}

		this.articles_substitution = await $.post({
			url : 'eden/elements/article',
			dataType:'json',
			data:{
				filtrage:[
					{
						champ : 'id',
						condition : 'whereIn',
						valeur : article_sur_document.modele.articles_substitution
					},
					{
						champ : 'disponible_pour_saisie',
						condition : 'whereIn',
						valeur : [0,(this.type_element.includes('achat') ? 2 : 1 )]
					},
				]
			}
		});

		if(this.articles_substitution.length == 0)
			this.remplacement_article(article_sur_document.index_article);
		else
			this.popover_articles_substitution = article_sur_document.index_article;
	},
@endpush

@push('modales')
	<template v-if="afficher_modale_remplacement_article">
		<transition name="modal" >
			<div class="modal-mask">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">

						<div class="modal-header">
							<h5 class="modal-title">@traduction('document.blocs.articles.remplacer')</h5>
						</div>

						<div class="modal-body"  v-clique_en_dehors="{ func: desaffichage_scroll, params: ['remplacement'] }" @click="afficher_scroll_articles_autres.remplacement = true">
							<div class="row css_row_ajouter_article_au_document" style="padding-top: 1%;">
								<div class="col-md-12" style="position:relative;height: 50px;">
									<input type="text" :placeholder="traduction('document.blocs.articles.remplacer')" id="js_valeur_champ_recherche" v-model="valeur_champ_recherche_modale" @keyup="afficher_articles(true)"  style="background: #f9f9f9; color: #272727; padding: 14px 10px; height: 50px;border:2px solid var(--background_menus);position: absolute;top: 0;left: 0;width: 100%;">@if(fonctionnalite('gescom_garder_valeur_saisie_des_articles') === true)<span class="fas fa-times" @click="valeur_champ_recherche = ''" style="position: absolute;top: 50%;right: 20px;transform: translateY(-50%);cursor: pointer;font-size: 17px;"></span>@endif
								</div>
							</div>
							<div class="row" v-if="valeur_champ_recherche_modale != ''" style="margin-bottom: 2%;">
								<div class="col-md-12" >
									@include('eden::formulaires.include.document_affichage_resultat_recherche',['type' => 'remplacement'])
								</div>
							</div>
						</div>

						<div class="modal-footer">
							<button type="button" class="btn btn-secondary" @click="afficher_modale_remplacement_article = false;index_article_remplacement = false;" data-dismiss="modal">@traduction('interface.modales.fermer')</button>
						</div>
					</div>
				</div>
			</div>
		</transition>
	</template>
@endpush