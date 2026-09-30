@extends('eden::listes.includes.actions.action_en_masse_generique',['action' => 'commander_documents'])

@push('donnees_pour_vuejs_methods')

    /**
	 *
	 * On commande les éléments sélectionnés
	 *
	 */
	commander_documents_elements_selectionnes: function() {

		var composant = this;

		this.modale_commander_documents = false;

		var type_element = composant.liste.type_element;

		// on va chercher les ID des éléments sélectionnés
		var ids = composant.liste.lignes_selectionnees;

		if(ids.length == 0)
			return;

		loading(true);

		// on facture
		$.post({

			url: "{{ route('document.vente.devis.commander', [], false) }}",
			dataType: "json",
			method: 'POST',
			data: {

				ids: ids
			}
		}).done(async function(donnees) {

			// On retire le loader
			loading(false);

			composant.actualisation_filtres();

			if(donnees.retour !== true) {

				await erreur(donnees.message);
				return;
			}

			info(composant.$root.traduction('interface.listes.documents_transformes_en_commandes', null, [donnees.nombre_documents, donnees.nombre_commandes]));

			composant.deselectionner_toutes_les_lignes();
		});
	},

	/**
	 *
	 * On commande tous les éléments de la liste
	 * 	
	 */
	commander_documents_tous_elements: async function() {

		var composant = this;

		// on récupère les ids...
		loading(true);

		this.modale_commander_documents = false;
		var type_element = this.liste.type_element;

		// on va chercher la liste d'ids
		await composant.actualisation_filtres(true);

		var ids = composant.liste.ids;

		var type_element = composant.liste.type_element;

		$.post({

			url: "{{ route('document.vente.devis.commander', [], false) }}",
			dataType: "json",
			method: 'POST',
			data: {
				ids: ids
			}
		}).done(async function(donnees) {

			// On retire le loader
			loading(false);

			composant.deselectionner_toutes_les_lignes();

			composant.actualisation_filtres();

			if(donnees.retour !== true) {

				await erreur(donnees.message);
				return;
			}

			if(donnees.nombre_documents > 0) {

				info(composant.$root.traduction('interface.listes.documents_transformes_en_commandes', null, [donnees.nombre_documents, donnees.nombre_commandes]));
			}
		});
	
	},

@endpush
