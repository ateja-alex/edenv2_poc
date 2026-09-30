@extends('eden::listes.includes.actions.action_en_masse_generique',['action' => 'affacturage_documents'])

@push('donnees_pour_vuejs_methods')

    /**
	 * 
	 * On facture les éléments sélectionnés
	 * 
	 */
	affacturage_documents_elements_selectionnes: function() {

		var composant = this;

		this.affacturage_documents = false;
		
		var type_element = composant.liste.type_element;
		
		// on va chercher les ID des éléments sélectionnés
		var ids = composant.liste.lignes_selectionnees;
		
		if(ids.length == 0)
			return;
		
		loading(true);
		
		// on facture
		$.post({

			url: "{{ route('document.vente.facture.affacturer_documents', [], false) }}",
			dataType: "json",
			method: 'POST',
			data: {
				
				ids: ids
			}
		}).done(function(donnees) {
			
			// On retire le loader
			loading(false);

			// console.log(donnees.chemin)
			window.location = donnees.chemin;
			
		});
	},
	
	/**	
	 * 	
	 * On facture tous les éléments de la liste	
	 * 	
	 */
	affacturage_documents_tous_elements: async function() {

		var composant = this;

		// on récupère les ids...	
		loading(true);

		this.affacturage_documents = false;

		await composant.actualisation_filtres(true);

		var ids = composant.liste.ids;

		var type_element = composant.liste.type_element;

		$.post({

			url: "{{ route('document.vente.facture.affacturer_documents', [], false) }}",
			dataType: "json",
			method: 'POST',
			data: {

				ids: ids
			}
		}).done(function(donnees) {

			// On retire le loader
			loading(false);

			window.location = donnees.chemin;

		});
	
	},

@endpush
