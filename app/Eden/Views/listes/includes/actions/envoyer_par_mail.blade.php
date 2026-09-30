@extends('eden::listes.includes.actions.action_en_masse_generique',['action' => 'envoi_email'])

@push('donnees_pour_vuejs_methods')

    /**
	 * 
	 * On envoie un mail aux lignes sélectionnées
	 * 
	 */
	envoi_email_elements_selectionnes: async function() {

		var composant = this;

		this.modale_envoi_email = false;
		var type_element = composant.liste.type_element;

		// on va chercher les ID des éléments sélectionnés
		var ids = composant.liste.lignes_selectionnees;
		let types_documents = {!! collect(\App\Eden\Variables::$documents_gescom) !!};

		if(types_documents.includes(type_element)){

			let presence_pro_forma = false;

			composant.liste.lignes.forEach((document) => {

				if(ids.includes(document.element.id) && (document.element.valide == false || document.element.valide == null))
					presence_pro_forma = true;
			});

			if(presence_pro_forma === true)
				await alerte_eden(this.$root.traduction('messages.js.documents.alerte_envoi_une_ou_plusieurs') + ' ' + this.$root.traduction('tables_libres.' + type_element + '.element') + '(s) ' + this.$root.traduction('messages.js.documents.proforma'), this.$root.traduction('interface.alerte.attention'));
		}

		var ids_documents = [];

		ids.forEach(id => {

			@foreach(\App\Eden\Variables::$documents_gescom as $type_element_tmp) 
				
				if(type_element == '{{$type_element_tmp}}') {
					
					ids_documents.push({type_element: '{{$type_element_tmp}}', id_element: id});
				}
			@endforeach
			

			if(type_element == 'coupon_reduction') {
				
				ids_documents.push({type_element: 'coupon_reduction', id_element: id});
			}
			
		});
		
		if(ids.length == 0)
			return;
		
		@foreach(\App\Eden\Variables::$documents_gescom as $type_element_tmp) 
			if(type_element == '{{$type_element_tmp}}') {

				this.$root.$emit('envoie_email',{ids_elements: ids, type_element: '{{$type_element_tmp}}', documents: ids_documents, modele_email: this.liste.modele_liste_libre.modele_email_defaut ?? null});
				return;
			}
		@endforeach
		
		
		if(type_element == 'coupon_reduction') {

	        parametres = {ids_elements: ids, type_element: 'coupon_reduction', documents: ids_documents};
		}
		else {

	        parametres = {ids_elements: ids, type_element: type_element};
		}

		parametres.modele_email = this.liste.modele_liste_libre.modele_email_defaut ?? null;
    
	    this.$root.$emit('envoie_email',parametres);
	},
	
	/**
	 * 
	 * On envoie un mail à tous les éléments de la liste
	 * 
	 */
	envoi_email_tous_elements: async function() {

		var composant = this;

		// on récupère les ids...
		loading(true);

		this.modale_envoi_email = false;

		await composant.actualisation_filtres(true);

		var ids_documents = [];

		var ids = composant.liste.ids;

		var type_element = composant.liste.type_element;

		let types_documents = {!! collect(\App\Eden\Variables::$documents_gescom) !!};

		if(types_documents.includes(type_element)){

			let presence_pro_forma = false;

			composant.liste.lignes.forEach((document) => {

				if(ids.includes(document.element.id) && (document.element.valide == false || document.element.valide == null))
					presence_pro_forma = true;
			});

			if(presence_pro_forma === true)
				await alerte_eden(this.$root.traduction('messages.js.documents.alerte_envoi_une_ou_plusieurs') + ' ' + this.$root.traduction('tables_libres.' + type_element + '.element') + '(s) ' + this.$root.traduction('messages.js.documents.proforma'), this.$root.traduction('interface.alerte.attention'));
		}

		ids.forEach(id => {

			@foreach(\App\Eden\Variables::$documents_gescom as $type_element_tmp)

				if(type_element == '{{$type_element_tmp}}') {

				ids_documents.push({type_element: '{{$type_element_tmp}}', id_element: id});
				}
			@endforeach


			if(type_element == 'coupon_reduction') {

			ids_documents.push({type_element: 'coupon_reduction', id_element: id});
			}
		});

		// on cache le loader
		loading(false);

		@foreach(\App\Eden\Variables::$documents_gescom as $type_element_tmp)
			if(type_element == '{{$type_element_tmp}}') {

			    this.$root.$emit('envoie_email',{ids_elements: ids, type_element: '{{$type_element_tmp}}', documents: ids_documents, modele_email: this.liste.modele_liste_libre.modele_email_defaut ?? null});
			    return;
			}
		@endforeach


		if(type_element == 'coupon_reduction') {

	        parametres = {ids_elements: ids, type_element: 'coupon_reduction', documents: ids_documents};
		}
		else {

	        parametres = {ids_elements: ids, type_element: type_element };
		}

		parametres.modele_email = this.liste.modele_liste_libre.modele_email_defaut ?? null;
    
	    this.$root.$emit('envoie_email',parametres);
	},

@endpush
