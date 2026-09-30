@extends('eden::listes.includes.actions.action_en_masse_generique',['action' => 'accepter_documents'])

@push('donnees_pour_vuejs_methods')

	accepter_documents_elements_selectionnes: function() {

		var composant = this;

		this.modale_accepter_documents = false;

		var type_element = composant.liste.type_element;

		// on va chercher les ID des éléments sélectionnés
		var ids = composant.liste.lignes_selectionnees;

		if(ids.length == 0)
			return;

		// on récupère les ids...
		loading(true);

		$.post({

			url: "{{ route('document.accepter_documents_en_masse', [], false) }}",
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

	accepter_documents_tous_elements: async function() {

		var composant = this;

		this.modale_accepter_documents = false;

		// on récupère les ids...
		loading(true);
	
		await composant.actualisation_filtres(true);

		var ids = composant.liste.ids;

		var type_element = composant.liste.type_element;

		$.post({

			url: "{{ route('document.accepter_documents_en_masse', [], false) }}",
			data: {

				type_element: type_element,
				ids: ids,
			}
		}).always(async function(retour) {

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

@endpush
