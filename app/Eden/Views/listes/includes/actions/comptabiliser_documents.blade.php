@extends('eden::listes.includes.actions.action_en_masse_generique',['action' => 'comptabiliser_documents'])

@push('donnees_pour_vuejs_methods')
    /**
	 *
	 * On comptabilise les éléments sélectionnés
	 *
	 */
	comptabiliser_documents_elements_selectionnes: function() {

		var composant = this;

		this.modale_comptabiliser_documents = false;

		var type_element = composant.liste.type_element;

		// on va chercher les ID des éléments sélectionnés
		var ids = composant.liste.lignes_selectionnees;

		if(ids.length == 0)
			return;

		loading(true);

		// on comptabilise
		$.post({

			url: "/eden/compta/comptabiliser/"+type_element,
			dataType: "json",
			method: 'POST',
			data: {

				ids_element: ids
			}
		}).done(async function(donnees) {

			// On retire le loader
			loading(false);

			composant.deselectionner_toutes_les_lignes();

			composant.actualisation_filtres();

			if(donnees.retour !== true) {

				await erreur(donnees.retour);
				return;
			}

			if(donnees.resultat.succes > 0) {

				info(composant.$root.traduction('interface.listes.elements_comptabilises_avec_succes', null, [donnees.resultat.succes]));
			}

			$.each(donnees.resultat.erreurs, async function(message_erreur, nombre) {

				await erreur(composant.$root.traduction('interface.liste.erreur')+" ("+nombre+"x) : "+message_erreur);
			});

		});
	},

	/**
	 *
	 * On comptabilise tous les éléments de la liste
	 *
	 */
	comptabiliser_documents_tous_elements: async function() {

		var composant = this;

		this.modale_comptabiliser_documents = false;

		var type_element = composant.liste.type_element;

		// on récupère les ids...
		loading(true);
		
		await composant.actualisation_filtres(true);

		var ids = composant.liste.ids;

		var type_element = composant.liste.type_element;

		$.post({

			url: "/eden/compta/comptabiliser/"+type_element,
			dataType: "json",
			method: 'POST',
			data: {
				ids_element: ids
			}
		}).always(async function(retour) {

			// on cache le loader
			loading(false);

			composant.deselectionner_toutes_les_lignes();

			composant.actualisation_filtres();

			if(retour.retour !== true) {

				await erreur(retour.retour);
				return;
			}

			if(retour.resultat.succes > 0) {

				info(composant.$root.traduction('interface.listes.elements_comptabilises_avec_succes', null, [retour.resultat.succes]));
			}

			$.each(retour.resultat.erreurs, async function(message_erreur, nombre) {

				await erreur(composant.$root.traduction('interface.liste.erreur')+" ("+nombre+"x) : "+message_erreur);
			});

		});
	},

@endpush
