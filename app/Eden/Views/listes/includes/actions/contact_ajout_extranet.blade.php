@extends('eden::listes.includes.actions.action_en_masse_generique',['action' => 'ajout_extranet'])

@push('donnees_pour_vuejs_methods')
    /**
	 *
	 * On crée les comptes extranet des éléments sélectionnés
	 *
	 */
	ajout_extranet_elements_selectionnes: function() {

		var composant = this;

		this.modale_ajout_extranet = false;

		// on va chercher les ID des éléments sélectionnés
		var ids = composant.liste.lignes_selectionnees;

		if(ids.length == 0)
			return;

		loading(true);

		$.post({

			url: "/eden/utilisateur_extranet/ajout_contact_en_masse",
			dataType: "json",
			method: 'POST',
			data: {

				ids_element: ids
			}
		}).done(async function(donnees) {

			// On retire le loader
			loading(false);

			if(donnees.retour !== true) {

				await erreur(donnees.retour);
				return;
			}

            info(composant.$root.traduction('interface.listes.creation_compte_extranet_succes'));

			composant.deselectionner_toutes_les_lignes();

			composant.actualisation_filtres();
		});
	},

	/**
	 *
	 * On crée les comptes extranet de tous les éléments de la liste
	 *
	 */
	ajout_extranet_tous_elements: async function() {

		var composant = this;

		this.modale_ajout_extranet = false;

		// on récupère les ids...
		loading(true);
		
		await composant.actualisation_filtres(true);

		var ids = composant.liste.ids;

		var type_element = composant.liste.type_element;

		$.post({

			url: "/eden/utilisateur_extranet/ajout_contact_en_masse",
			dataType: "json",
			method: 'POST',
			data: {

				ids_element: ids
			}
		}).always(async function(retour) {

			// on cache le loader
			loading(false);

			if(retour.retour !== true) {

				await erreur(retour.retour);
				return;
			}

            info(composant.$root.traduction('interface.listes.creation_compte_extranet_succes'));

			composant.deselectionner_toutes_les_lignes();

			composant.actualisation_filtres();

		});
	
	},

@endpush