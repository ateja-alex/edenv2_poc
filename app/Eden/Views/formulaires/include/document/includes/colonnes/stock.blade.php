{{-- Stock --}}
<div class="cellule_document_colonne_article">
	<template v-if="article_sur_document.modele && (article_sur_document.modele.stockable == 1 || article_sur_document.nomenclature)">
		<span class="css_prix_ligne_article_document" :title="detail_stock_par_entrepot(article_sur_document)" data-html="true" data-toggle="tooltip">

			<template v-if="article_sur_document.modele && article_sur_document.modele.type_article != 1 ">
				@{{ article_sur_document.stock.total }}
				<template v-if="Number.isInteger(article_sur_document.stock.total) && article_sur_document.stock.total - calcule_quantite_total_article(article_sur_document) < 0 ">
					<span class="badge badge-danger"><i aria-hidden="true" class="fa fa-exclamation-triangle"></i></span>
				</template>
			</template>
			<span class="css_span_vide_pour_nomenclature" v-if="article_sur_document.modele && article_sur_document.modele.type_article == 1"></span>

		</span>

		<!-- nomenclature -->
		<template v-for="(article_nomenclature, nomenclature_index) in article_sur_document.nomenclature" v-if="article_sur_document.afficher_nomenclature">

			<span class="css_prix_ligne_article_document_nomenclature" :title="detail_stock_par_entrepot(article_nomenclature)" data-html="true" data-toggle="tooltip">
				<template v-if="article_nomenclature.modele.type_article != 1 && article_nomenclature.modele && article_nomenclature.modele.stockable == 1">
					@{{ article_nomenclature.stock.total }}
					<template v-if="Number.isInteger(article_nomenclature.stock.total) && article_nomenclature.stock.total - calcule_quantite_total_article(article_sur_document, article_nomenclature) < 0 ">
						<span class="badge badge-danger"><i aria-hidden="true" class="fa fa-exclamation-triangle"></i></span>
					</template>
				</template>
				<span class="css_span_vide_pour_nomenclature" v-if="article_nomenclature.modele.type_article == 1"></span>
			</span>

			<span :class="{css_retour_ligne_apres_element: article_nomenclature.nomenclature && sous_nomenclature_index == article_nomenclature.nomenclature.length - 1 && article_nomenclature.modele.type_article == 1}" 
				v-for="(sous_nomenclature, sous_nomenclature_index) in article_nomenclature.nomenclature" v-if="article_nomenclature.afficher_nomenclature">
				
				<span class="css_prix_ligne_article_document_nomenclature" :title="detail_stock_par_entrepot(sous_nomenclature)" data-html="true" data-toggle="tooltip" style="border-top: 0px;">
					<template v-if="sous_nomenclature.modele.type_article != 1 && sous_nomenclature.modele && sous_nomenclature.modele.stockable == 1">
						@{{ sous_nomenclature.stock.total }}
						<template v-if="Number.isInteger(sous_nomenclature.stock.total) && sous_nomenclature.stock.total - calcule_quantite_total_article(article_sur_document, article_nomenclature, sous_nomenclature) < 0 ">
							<span class="badge badge-danger"><i aria-hidden="true" class="fa fa-exclamation-triangle"></i></span>
						</template>
					</template>
				</span>
			</span>

			<span :class="{css_retour_ligne_apres_element: article_nomenclature.nomenclature && article_nomenclature.nomenclature.length == 0 && article_nomenclature.modele.type_article == 1}" v-if="article_nomenclature.afficher_nomenclature && article_nomenclature.modele.type_article == 1"></span>
		</template>
	</template>
</div>

@push('donnees_pour_vuejs_methods')
	
	detail_stock_par_entrepot: function(article_sur_document){

		if(!article_sur_document.modele || article_sur_document.modele.type_article == 1)
			return '';
	
		if(article_sur_document.stock == undefined || article_sur_document.stock.par_entrepot == undefined)
			return "";
	
		if(Object.entries(article_sur_document.stock.par_entrepot).length <= 1)
			return "";
	
		var detail_stock_formate = "";
	
		for(const [entrepot_id, stock_entrepot] of Object.entries(article_sur_document.stock.par_entrepot)){

			detail_stock_formate += stock_entrepot.nom + ": " + stock_entrepot.total + "<br>";
	
		}
	
		return detail_stock_formate;
	
	},

@endpush