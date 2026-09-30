
{{-- Quantité --}}
<div class="cellule_document_colonne_article">

	<champ-montant v-if="{{ $edition_ligne }}"
				class_input="css_input_article_document"
				:style_input="retourne_background_input_quantite_pour_calculateur(article_sur_document)"
				:modele="article_sur_document"
				nom_sql="quantite"
				:valeur_non_vide="true"
				:lecture_seule="(article_sur_document.modele && article_sur_document.modele.type_numero_de_serie == 1)">
		</champ-montant>
        <span v-else class="css_lecture_ligne css_prix_ligne_article_document css_input_article_document">@{{new Intl.NumberFormat('fr-FR', { style: 'decimal', minimumFractionDigits : 0 }).format(article_sur_document.quantite)}}</span>


	<!-- nomenclature -->
	<template v-for="(article_nomenclature, nomenclature_index) in article_sur_document.nomenclature" v-if="article_sur_document.afficher_nomenclature">
		@if($articles_modifiables === true && empty($recapitulatif))
			<champ-montant
					:class_input="'css_input_article_document '+(contenu_produit_assemble(article_sur_document) ? 'css_champ_readonly' : '')"
					style_input="color:#606060"
					:modele="article_nomenclature"
					nom_sql="quantite"
					:valeur_non_vide="true"
					:lecture_seule="contenu_produit_assemble(article_sur_document)">
			</champ-montant>
		@else
			@{{ article_nomenclature.quantite }}
		@endif

		<span :class="{css_retour_ligne_apres_element: article_nomenclature.nomenclature && sous_nomenclature_index == article_nomenclature.nomenclature.length - 1 && article_nomenclature.modele.type_article == 1}" v-for="(sous_nomenclature, sous_nomenclature_index) in article_nomenclature.nomenclature" v-if="article_nomenclature.afficher_nomenclature">

			@if($articles_modifiables === true && empty($recapitulatif))
				<champ-montant
						:class_input="'css_input_article_document '+(contenu_produit_assemble(article_nomenclature, article_sur_document) ? 'css_champ_readonly' : '')"
						style_input="color:#606060"
						:modele="sous_nomenclature"
						nom_sql="quantite"
						:valeur_non_vide="true"
						:lecture_seule="contenu_produit_assemble(article_nomenclature, article_sur_document)">
				</champ-montant>
			@else
				@{{ sous_nomenclature.quantite }}
			@endif

		</span>
		<span :class="{css_retour_ligne_apres_element: article_nomenclature.nomenclature && article_nomenclature.nomenclature.length == 0 && article_nomenclature.modele.type_article == 1}" v-if="article_nomenclature.afficher_nomenclature && article_nomenclature.modele.type_article == 1"></span>

	</template>
</div>

@if(empty($recapitulatif))

	@push('donnees_pour_vuejs_mounted')

		this.$on('maj_champ_montant',(donnees) => {

			if(donnees.nom_sql != 'quantite')
				return;

			this.corrige_virgule(donnees.modele,donnees.nom_sql);
			this.mise_a_jour_quantite(donnees.modele);
			this.changement_quantite_calculateur(donnees.modele);
			this.$forceUpdate();
		});
	@endpush

	@push('donnees_pour_vuejs_methods')
		retourne_background_input_quantite_pour_calculateur: function(article) {

			if(article.calculateur == undefined)
				return '';

			if(article.calculateur.resultat_calcul == undefined)
				return '';

			if(article.calculateur.resultat_calcul == '')
				return '';

			if(article.calculateur.resultat_calcul != article.quantite)
				return 'background-color: #e98a1df5;';

			return '';
		},

		retourne_border_input_quantite_pour_calculateur: function(detail_calculateur) {

			if(detail_calculateur.calculateur == undefined)
				return '';

			if(detail_calculateur.calculateur.resultat_calcul == undefined)
				return '';

			if(detail_calculateur.calculateur.resultat_calcul == '')
				return '';

			if(detail_calculateur.calculateur.resultat_calcul != detail_calculateur.quantite)
				return 'border: 3px solid #e98a1df5;';

			return '';
		},
	@endpush

@endif
