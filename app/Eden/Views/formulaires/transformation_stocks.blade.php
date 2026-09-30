<div class="row">
	<div class="col-sm-12 css_form_ligne_titre">@traduction('formulaire.divers.informations_generales')</div>
</div>
<div class="row">
	@champ('transformation_stocks', 'entrepot_id', 2,4)
	@champ('transformation_stocks', 'date_transformation', 2,4)
</div>
<div class="row">
	@php
		$champ_article_id = management('transformation_stocks')->champ('article_id');
	@endphp
	<div class="col-sm-2">
		{!! $champ_article_id->nom_vue() !!}
	</div>
	<div class="col-sm-4">
		@php
			$champ_article_id->filtrage('(transformation_stocks.articles_entrepot ? [{"champ":"id","condition_ou":false,"condition":"WhereIn","symbole":"","valeur":transformation_stocks.articles_entrepot.join(",")}] : [])');
		@endphp
		{!! $champ_article_id->cree() !!}
	</div>
</div>
<div class="row">
	<div class="col-sm-12 css_form_ligne_titre">@traduction('formulaire.transformation_stocks.informations_de_depart')</div>
</div>
<div class="row">
	<template v-if="transformation_stocks.article_id != ''">
		@champ('transformation_stocks', 'conditionnement_depart_id', 2,4)
	</template>
</div>
<div class="row">
	<div class="col-sm-4 css_form_ligne_titre">@traduction('formulaire.transformation_stocks.conditionnement_arrivee')</div>
	<div class="col-sm-4 css_form_ligne_titre">@traduction('formulaire.transformation_stocks.quantite_depart')</div>
	<div class="col-sm-4 css_form_ligne_titre">@traduction('formulaire.transformation_stocks.quantite_arrivee')</div>
</div>
<template v-for="(ligne, ligne_index) in details_transformation_stock">
	
	<div class="row">
		<div class="col-sm-4">
			<champ-selection-element type_element="conditionnement" :name="'conditionnement_arrivee_' + ligne_index"
                nom_sql="conditionnement_id" :modele="ligne" type_element_origine="tranformation_stocks_ligne"
                :filtrage="[{champ:'article_id',condition:'where',valeur:transformation_stocks.article_id}]"
            ></champ-selection-element>
		</div>
		<div class="col-sm-4">
			<input type="number" @change="modification_quantite('depart',ligne)" :name="'quantite_depart_' + ligne_index" v-model="ligne.quantite_depart" @wheel.prevent @keydown.up.prevent @keydown.down.prevent/>
		</div>
		<div class="col-sm-4">
			<input type="number" @change="modification_quantite('arrivee',ligne)" :name="'quantite_arrivee_' + ligne_index" v-model="ligne.quantite_arrive" @wheel.prevent @keydown.up.prevent @keydown.down.prevent/>
		</div>
	</div>
	<input type="hidden" :name="'id_arrivee_' + ligne_index" v-model="ligne.id" />
	<br>

</template>
<div class="row">
	<template v-if="transformation_stocks.article_id != ''">
		<div class="col-sm-2"><span class="css_ajouter_ligne_nomenclature" @click="details_transformation_stock[Object.keys(details_transformation_stock).length] = {},$forceUpdate()">+ <span>@traduction('formulaire.transformation_stocks.transformation')</span></span></div>
	</template>
</div>

@push('donnees_pour_vuejs_data')

	{{ 'transformation_stocks_lignes' }}: {!! management('transformation_stocks_lignes')->modele_par_defaut() !!},
	'conditionnements' : {},
	'details_transformation_stock' : {},

@endpush

@push('donnees_pour_vuejs_methods')

	modification_quantite : function(type,ligne){

		var vue_instance = this;

		var quantite_conditionnement_depart = vue_instance.conditionnements[vue_instance.transformation_stocks.article_id].filter(conditionnement => conditionnement.id == vue_instance.transformation_stocks.conditionnement_depart_id)[0]?.quantite ?? 1;
		if(!ligne.conditionnement_id)
			var quantite_conditionnement_arrive = 1;
		else
			var quantite_conditionnement_arrive = vue_instance.conditionnements[vue_instance.transformation_stocks.article_id].filter(conditionnement => conditionnement.id == ligne.conditionnement_id)[0].quantite;

		if(type == "arrivee"){

			var quantite_arrive = ligne.quantite_arrive;
			var resultat = quantite_arrive * quantite_conditionnement_arrive /quantite_conditionnement_depart;
			ligne.quantite_depart = resultat;

		}
		else if(type == "depart"){

			var quantite_depart = ligne.quantite_depart;
			var resultat = quantite_depart * quantite_conditionnement_depart / quantite_conditionnement_arrive;
			ligne.quantite_arrive = resultat;

		}

		vue_instance.$forceUpdate();

	},

	chargement_articles_entrepot : function(){

		if(!(this.transformation_stocks.entrepot_id > 0)){
			this.$set(this.transformation_stocks, 'articles_entrepot', false);
			return;
		}

		$.post({
			url: 'eden/elements/stocks',
			dataType: 'json',
			data: {
				filtrage: [
					{ champ: 'entrepot_id', condition: 'where', valeur: this.transformation_stocks.entrepot_id }
				]
			}
		}).done((elements) => {
			this.$set(this.transformation_stocks, 'articles_entrepot', elements.map(element => element.article_id));
		});

	},

@endpush

@push('donnees_pour_vuejs_mounted')

	var vue_instance = this;
	
	if(this.transformation_stocks != undefined){

		this.$set(this.transformation_stocks, 'articles_entrepot', false);
		this.$watch('transformation_stocks.entrepot_id', () => {
			this.chargement_articles_entrepot();
		});
		this.chargement_articles_entrepot();

		$.post({
			url: "{{route('article.conditionnements')}}",
			dataType: "json",
		}).done(function(donnees){

			vue_instance.conditionnements = donnees;

		});
	}

@endpush