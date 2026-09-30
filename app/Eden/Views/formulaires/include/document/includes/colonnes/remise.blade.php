{{-- Remise (%) --}}
<div class="cellule_document_colonne_article">

	<champ-montant v-if="{{ $edition_ligne }}"
				class_input="css_input_article_document"
				style_input="margin-bottom:8px;"
				:modele="article_sur_document"
				nom_sql="remise"
				:valeur_non_vide="true"
				@if(!empty(moi_extranet())) :lecture_seule="disable_champs_extranet" @endif>
		</champ-montant>
        <span v-else class="css_lecture_ligne css_prix_ligne_article_document css_input_article_document">@{{parseFloat( article_sur_document.remise ?? 0).toFixed(2)}} %</span>
</div>

@if(empty($recapitulatif))
	@push('donnees_pour_vuejs_mounted')

		this.$on('maj_champ_montant',(donnees) => {

			if(donnees.nom_sql != 'remise')
				return;

			this.corrige_virgule(donnees.modele,'remise');
			this.arrondi_prix_article_depuis_fonctionnalite(donnees.modele);
			this.mise_a_jour_total_document_vue();
		});
	@endpush
@endif
