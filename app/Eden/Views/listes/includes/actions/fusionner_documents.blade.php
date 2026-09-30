@extends('eden::listes.includes.actions.action_en_masse_generique',['action' => 'fusionner','desactiver_lignes_liste' => true])

@push('donnees_pour_vuejs_methods')
	/**
	 *
	 * Permet de fusionner plusieurs documents en un seul
	 *
	 */
	fusionner_elements_selectionnes : function() {

		var composant = this;

		this.modale_fusionner = false;

		var type_element = composant.liste.type_element;

		loading(true);

		// on va chercher les ID des éléments sélectionnés
		var ids = composant.liste.lignes_selectionnees;

		if(ids.length == 0)
			return;

		$.post({

			url: "{{ route('document.fusionner_documents_en_masse', [], false) }}",
			data: {

				type_element: type_element,
				ids: ids,
			}
		}).always(async function(retour) {

			loading(false);

			composant.deselectionner_toutes_les_lignes();

			// on actualise
			composant.actualisation_filtres();

			if(retour.retour !== true) {

				await erreur(retour.message);
				return;
			}
		});
	},
@endpush
