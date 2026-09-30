@extends('eden::fiches.include.article.options_fil_ariane.copie_article_categorie_comptable',array('contexte' => 'liste'))

@push('donnees_pour_vuejs_methods')

    eden_copier_elements_selectionnes : function(){

		// on va chercher les ID des éléments sélectionnés
		var ids = this.liste.lignes_selectionnees;

		if(ids.length == 0)
			return;

        this.copier_categories_comptables_article(ids);
    },

    eden_copier_tous_les_elements : async function(){

        // on récupère les ids...
		loading(true);

		await this.actualisation_filtres(true);

		var ids = this.liste.ids;

        this.copier_categories_comptables_article(ids);
    },
@endpush