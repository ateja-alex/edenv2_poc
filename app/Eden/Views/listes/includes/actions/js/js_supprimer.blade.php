@extends('eden::listes.includes.actions.action_en_masse_generique')

@push('donnees_pour_vuejs_methods')

    /**
	 * 
	 * On supprime les lignes sélectionnées
	 * 
	 */
	{{$action}}_elements_selectionnes: function() {
		
		// on va chercher les ID des éléments sélectionnés
		var ids = [];

		var composant = this;
	
		// on va chercher les ID des éléments sélectionnés
		var ids = composant.liste.lignes_selectionnees;
		
		if(ids.length == 0)
			return;

		composant.eden_supprimer_en_masse(ids);
	},

	/**
	 * 
	 * On envoie un mail à tous les éléments de la liste
	 * 
	 */
	{{$action}}_tous_elements: async function() {

		// on récupère les ids...
		loading(true);

		var composant = this;

		var type_element = composant.liste.type_element;

		// on récupère les ids...
		loading(true);

		// on va chercher la liste d'ids
		await composant.actualisation_filtres(true);

		composant.eden_supprimer_en_masse(composant.liste.ids);


	},

	eden_supprimer_en_masse: function(ids) {

		var composant = this;
		loading(true);

		var parametres = {
			ids_elements: ids,
			type_element: composant.liste.type_element
		};

		// on va chercher le template
		$.post({

			url: 'eden/elements/supprimer_en_masse',
			method:"post",
			dataType: "json",
			data: { parametres:parametres},
		}).always(async (retour) => {

			if ( retour.success == true ) {

				this.modale_{{$action}} = false;

			} else {

				retour.message = retour.message.replace('<br>', '\n')

				await alerte_eden(composant.$root.traduction('interface.listes.des_erreurs_sont_survenues')+"\n\n"+composant.$root.traduction('interface.listes.lignes_modifiees')+retour.lignes_modifiees+"\n"+retour.message);
				
			}

			composant.deselectionner_toutes_les_lignes();

			composant.actualisation_filtres();

			// on cache le loader
			loading(false);
		});
	},


@endpush