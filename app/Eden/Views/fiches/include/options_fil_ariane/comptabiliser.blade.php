<i class="css_action_icon primaire fas fa-calculator" @click="comptabiliser()" :title="$root.traduction('interface.listes.comptabiliser_documents')" data-toggle="tooltip"></i>

@push('donnees_pour_vuejs_methods')

    comptabiliser : function(element){

        var type_element = this.$root.type_element;

		loading(true);

		// on comptabilise
		$.post({

			url: "eden/compta/comptabiliser/"+type_element,
			dataType: "json",
			method: 'POST',
			data: {

				ids_element: [this.$root.element_id]
			}
		}).done(async (donnees) => {

			// On retire le loader
			loading(false);

			if(donnees.retour !== true) {

				await erreur(donnees.retour);
				return;
			}

			if(donnees.resultat.succes > 0)
				info(this.$root.traduction('interface.listes.elements_comptabilises_avec_succes', null, [donnees.resultat.succes]));

			await $.each(donnees.resultat.erreurs, async (message_erreur, nombre) => {

				await erreur(this.$root.traduction('interface.liste.erreur')+" ("+nombre+"x) : "+message_erreur);
			});

			this.$emit('element_comptabilise');
		});
    },

@endpush