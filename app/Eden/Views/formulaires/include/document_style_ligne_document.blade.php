@if(fonctionnalite('gescom_activer_style_sur_ligne_document'))
	<span class="dropdown">
		<span class="css_btn_action_article_document" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="cursor:pointer">@traduction('document.blocs.style_ligne_document.aa')</span>
			<div class="dropdown-menu" >
				<div class="form-group">
					<div class="dropdown-item" @click.prevent="changement_style_lignes_divers(article_sur_document,0)">
						<span v-if="article_sur_document.id_style_ligne_document == 0">✓</span>
						<a href="" class="" style="color:black;">@traduction('document.blocs.style_ligne_document.aucun_style')</a>
					</div>
					<div v-for="(style, index) in style_ligne_document" :key=index class="dropdown-item" @click.prevent="changement_style_lignes_divers(article_sur_document, style.id)">
						<span v-if="article_sur_document.id_style_ligne_document == style.id">✓</span>
						<a href="" :class="'style_ligne_document_' + style.id">@{{ style.nom }}</a>
					</div>
				</div>
			</div>
		</span>
	</span>

	@push('donnees_pour_vuejs_data')	

		style_ligne_document: {!! $style_ligne_document !!},	

	@endpush	


	@push('donnees_pour_vuejs_methods')	

		changement_style_lignes_divers : function(article_sur_document, id_style) {

			article_sur_document.id_style_ligne_document = id_style;
			vue_instance.$forceUpdate();
		},
	@endpush

@endif