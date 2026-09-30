<div class="row" id="preselection_des_articles" style="margin: 15px;">
	<div class="col-md-12">
		<h5>@traduction('document.blocs.saisie_des_articles.choisir')</h5>
	</div>
	<div class="col-md-12">
		<template v-for="famille in catalogue">
			<div class="row">
				<div class="col-md-3"><b>@{{famille.modele_famille.nom}}</b></div>
				<div class="col-md-9">
					<span class="js_badge_selectionnables">
						<span class="badge badge-default" :id_article="article.id" v-for="article in famille.articles" :title="article.description_courte" style="margin: 2px;">@{{article.designation}}</span>
					</span>
				</div>
			</div>
		</template>
		<div class="btn btn-primary pull-right" style="margin-left: 50%;" @click="ajoute_articles_au_document()">@traduction('document.lignes_diverses.preselection_articles.creer_devis')</div>
	</div>
</div>