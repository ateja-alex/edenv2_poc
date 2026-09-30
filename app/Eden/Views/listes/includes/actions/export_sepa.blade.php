@extends('eden::listes.includes.actions.action_en_masse_generique',['action' => 'export_sepa'])

@push('donnees_pour_vuejs_methods')
    /**
	 *
	 * Permet de valider les différents documents sélectionnés dans une liste
	 *
	 */
	export_sepa_elements_selectionnes: function() {

		var composant = this;

		this.modale_export_sepa = false;

		var type_element = composant.liste.type_element;

		// on va chercher les ID des éléments sélectionnés
		var ids = composant.liste.lignes_selectionnees;

		if(ids.length == 0)
			return;

		// on récupère les ids...
		loading(true);

		$.post({

			url: "{{ route('document.export_sepa_en_masse', [], false) }}",
			data: {

				type_element: type_element,
				ids: ids,
			}
		}).always(async function(donnees) {

			// on cache le loader
			loading(false);

			if(donnees.retour === false) {

				await erreur(donnees.message);
				return;
			}

			window.location = donnees.chemin;

		});
	},

	/**
	*
	* Permet de valider tous les documents sélectionnés dans une liste
	*
	*/
	export_sepa_tous_elements: async function() {

		var composant = this;

		this.modale_export_sepa = false;

		// on récupère les ids...
		loading(true);

		await composant.actualisation_filtres(true);

		var ids = composant.liste.ids;

		var type_element = composant.liste.type_element;

		$.post({

			url: "{{ route('document.export_sepa_en_masse', [], false) }}",
			data: {

			type_element: type_element,
			ids: ids,
		}
		}).always(async function(donnees) {

			loading(false);

			if(donnees.retour === false) {

				await erreur(donnees.message);
				return;
			}

			window.location = donnees.chemin;

		});
    },

@endpush
