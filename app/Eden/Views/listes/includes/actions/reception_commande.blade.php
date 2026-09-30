@extends('eden::listes.includes.actions.action_en_masse_generique',['action' => 'reception_commande'])

@push('donnees_pour_vuejs_methods')
    /**
	 *
	 * On réceptionne les éléments sélectionnés
	 *
	 */
	reception_commande_elements_selectionnes: function() {

		var composant = this;

		this.modale_reception_commande = false;

		var type_element = composant.liste.type_element;

		// on va chercher les ID des éléments sélectionnés
		var ids = composant.liste.lignes_selectionnees;

		if(ids.length == 0)
			return;

		loading(true);

		// on réceptionne
		$.post({

			url: "/eden/document/reception_lignes_en_masse",
			dataType: "json",
			method: 'POST',
			data: {

				ids_element: ids
			}
		}).done(async function(donnees) {

			composant.deselectionner_toutes_les_lignes();

			composant.actualisation_filtres();

			// On retire le loader
			loading(false);

			if(donnees.retour !== true) {

				await erreur(donnees.retour);
				return;
			}

			if(donnees.resultat.succes > 0) {

				if(donnees.resultat.succes > 1)
					info(donnees.resultat.succes+' '+composant.$root.traduction('interface.listes.elements_receptionnes_avec_succes'));
				else
					info(donnees.resultat.succes+' '+composant.$root.traduction('interface.listes.element_receptionne_avec_succes'));
			}

			$.each(donnees.resultat.erreurs, async function(message_erreur, nombre) {

				await erreur(composant.$root.traduction('interface.liste.erreur')+" ("+nombre+"x) : "+message_erreur);
			});

		});
	},

	/**
	 *
	 * On réceptionne tous les éléments de la liste
	 *
	 */
	reception_commande_tous_elements: async function() {

		var composant = this;

		this.modale_reception_commande = false;

		var type_element = composant.liste.type_element;

		// on récupère les ids...
		loading(true);

		await composant.actualisation_filtres(true);

		var ids = composant.liste.ids;

		var type_element = composant.liste.type_element;

		$.post({

			url: "/eden/document/reception_lignes_en_masse",
			dataType: "json",
			method: 'POST',
			data: {

				ids_element: ids
			}
		}).always(async function(retour) {

			composant.deselectionner_toutes_les_lignes();

			composant.actualisation_filtres();

			// on cache le loader
			loading(false);

			if(retour.retour !== true) {

				await erreur(retour.retour);
				return;
			}

			if(retour.resultat.succes > 0) {

				if(donnees.resultat.succes > 1)
					info(donnees.resultat.succes+' '+composant.$root.traduction('interface.listes.elements_receptionnes_avec_succes'));
				else
					info(donnees.resultat.succes+' '+composant.$root.traduction('interface.listes.element_receptionne_avec_succes'));
			}

			$.each(retour.resultat.erreurs, async function(message_erreur, nombre) {

				await erreur(composant.$root.traduction('interface.liste.erreur')+" ("+nombre+"x) : "+message_erreur);
			});

		});
	},

@endpush
