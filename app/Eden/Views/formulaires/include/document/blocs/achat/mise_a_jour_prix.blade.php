<div class="card mb-3">
	<div class="card-header">
		<h4>@traduction('document.blocs.mise_a_jour_prix.titre')</h4>
        <span class="css_tooltip" style="font-size:18px;cursor:pointer">
			<i class="fa fa-question"></i>
			<div class="css_top">

				<p style="font-size:12px;"><span style="font-weight:bold">@traduction('document.blocs.mise_a_jour_prix.description')</span></p>
				<i></i>
			</div>
		</span>
	</div>
	<div id="css_achat_maj_article" class="card-body">

		<table id="css_achat_tableau_maj_article">
			<tbody>

				<template v-if="articles_a_modifier.length == 0">
					<tr>
						<td>
							<h6>@traduction('document.blocs.mise_a_jour_prix.prix_coherence_erp')</h6>
						</td>
					</tr>
				</template>

				<template  v-else>

					<tr>
						<th></th>
						<th>@traduction('document.colonnes.designation.titre')</th>
						<th>@traduction('document.blocs.mise_a_jour_prix.prix_achat') - {!!$management->modele->reference_document!!}</th>
						<th>@traduction('document.blocs.mise_a_jour_prix.prix_achat') - Eden</th>
					</tr>

					<template v-for="(article_sur_document, article_index) in articles_a_modifier">

						<tr>
							<td style="text-align: center;padding-top: 5px;">
								<input class="js_selection_mise_a_jour" type="checkbox" :value="article_sur_document.article_id + '_' + article_sur_document.document_id + '_' + article_sur_document.conditionnement" @click="changement_css_ligne($event)" name="selection"/>
							</td>

							<td>
								<a :href="'{!! url('eden/fiche/article') !!}/' + article_sur_document.article_id">
									<span>@{{article_sur_document.designation}} (@{{article_sur_document.reference}})  <i class="fas fa-eye" aria-hidden="true"></i></span>
								</a>
							</td>
							<td>
								<span>@{{arrondi_nombre_depuis_fonctionnalite(article_sur_document.tarif)}}</span>
							</td>
							<td>
								<span v-if="article_sur_document.prix_achat_old">@{{arrondi_nombre_depuis_fonctionnalite(article_sur_document.prix_achat_old)}}</span>
								<p v-else>
									<span>@traduction('document.blocs.mise_a_jour_prix.non_disponible')</span>
									<span>@traduction('document.blocs.mise_a_jour_prix.article_absent_fournisseur')</span>
								</p>
							</td>
						</tr>

						@include('eden::formulaires.include.document.vues_a_surcharger.tableau_articles.infos_supplementaires_sur_article')

					</template>
				</template>
			</tbody>
		</table>

		<span @click="mise_a_jour_des_prix_achat();" style="width: fit-content;" class="btn btn-primary" v-if="articles_a_modifier.length > 0">@traduction('interface.modales.mettre_a_jour')</span>
	</div>
</div>

@push('donnees_pour_vuejs_data')

	articles_a_modifier : {},
	nomenclature_ligne_index : -1,

@endpush

@push('donnees_pour_vuejs_methods')

	changement_css_ligne : function(event){

		var ligne = $(event.target).closest('tr');

		if($(event.target).prop("checked")){

			ligne.css("color","rgba(0,0,0,0.7)");
			ligne.css("font-weight","bolder");
			var spans = ligne.find("span");

			$(spans).css('color',"rgba(0,0,0,0.7)");
			$(spans).css('font-style','normal');
			$(spans).css('font-weight','bolder');
			$(spans).find("a").css('color',"rgba(0,0,0,0.7)");

		}

		else{

			ligne.css("color","#707070");
			ligne.css("font-weight","normal");
			var spans = ligne.find("span");

			$(spans).css('color','#707070');
			$(spans).css('font-style','italic');
			$(spans).css('font-weight','normal');
			$(spans).find("a").css('color','#707070');


		}

	},

	mise_a_jour_des_prix_achat : function(){

		var selection = $('.js_selection_mise_a_jour:checkbox:checked');

		if(selection.length > 0){

			loading(true);

			var articles_a_envoyer = [];

			for(let i = 0 ; i < selection.length ; i++){

				vue_instance.articles_a_modifier.forEach(function(article) {

					if(article.article_id + '_' + article.document_id + '_' + article.conditionnement === selection[i].value)
						articles_a_envoyer.push({
							'id' : article.article_id,
							'designation' : article.designation,
							'conditionnement_id' : article.conditionnement,
							'fournisseur_id' : article.fournisseur_id_ligne,
							'condition_commerciale_id' : article.condition_commerciale_id,
							'nouveau_prix' : article.tarif
						});
				});
			}

			$.post(
        "{{ route('document.achat.maj_prix_achat_article') }}",

				{
					'selection' : articles_a_envoyer,
				},
			).done((data) => {

				loading(false);

				if(data.retour != true){

					toastr.error(data.retour);
					return false;
				}

				this.articles_prix_achats_differents();
			});
		}
	},

	articles_prix_achats_differents : function(){
		$.post(

			"{{ route('document.achat.articles_prix_achats_differents') }}",
			{
				'articles' : this.articles_du_document,
				'document_id' : this.document.id,
				'type_element' : this.type_element
			}
		).done((articles) => {

			this.articles_a_modifier = articles;
		});
	},
@endpush

@push('donnees_pour_vuejs_mounted')

	this.articles_prix_achats_differents();
@endpush
