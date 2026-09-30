@extends('eden::listes.includes.actions.action_en_masse_generique',['action' => 'refuser_documents'])

@push('donnees_pour_vuejs_methods')

	refuser_documents_elements_selectionnes: function() {

		var composant = this;

		this.modale_refuser_documents = false;

		var type_element = composant.liste.type_element;

		// on va chercher les ID des éléments sélectionnés
		var ids = composant.liste.lignes_selectionnees;

		if(ids.length == 0)
			return;

		// on récupère les ids...
		loading(true);

		$.post({

			url: "{{ route('document.refuser_documents_en_masse', [], false) }}",
			data: {

				type_element: type_element,
				ids: ids,
			}
		}).always(async function(retour) {

			// on cache le loader
			loading(false);

			composant.deselectionner_toutes_les_lignes();

			// on actualise
			composant.actualisation_filtres();

			if(retour.retour === false) {

				await erreur(retour.message);
				return;
			}

		});
	},

	refuser_documents_tous_elements: async function() {

		var composant = this;

		this.modale_refuser_documents = false;

		var type_element = composant.liste.type_element;

		// on récupère les ids...
		loading(true);

		// on va chercher la liste d'ids
		await composant.actualisation_filtres(true);

		ids = composant.liste.ids;

		$.post({

			url: "{{ route('document.refuser_documents_en_masse', [], false) }}",
			data: {

			type_element: type_element,
			ids: ids,
		}
		}).always(async function(retour) {

			composant.deselectionner_toutes_les_lignes();

			// on actualise
			composant.actualisation_filtres();

			if(retour.retour === false) {

				await erreur(retour.message);
				return;
			}

		});

		loading(false);

	},
@endpush
