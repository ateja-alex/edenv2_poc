<template v-if="(ligne.element.comptabilisee == 0 || ligne.element.comptabilisee == null) && (ligne.element.valide === 1 || ligne.element.accepte === 1) && ligne.droits_comptabilisation">
    <span @click="comptabiliser(ligne.element)" :title="$root.traduction('interface.listes.comptabiliser_documents')"
          class="btn btn-mini btn-xs btn-default css_icone_option_dans_liste css_btn_action_theme">
        <i class="fas fa-calculator"></i>
    </span>
</template>

@push('donnees_pour_vuejs_methods')

    comptabiliser : function(element){

        var type_element = this.liste.type_element;

		loading(true);

		// on comptabilise
		$.post({

			url: "eden/compta/comptabiliser/"+type_element,
			dataType: "json",
			method: 'POST',
			data: {

				ids_element: [element.id]
			}
		}).done(async (donnees) => {

			// On retire le loader
			loading(false);

			if(donnees.retour !== true) {

				await erreur(donnees.retour);
				return;
			}

			if(donnees.resultat.succes > 0) {

				info(this.$root.traduction('interface.listes.elements_comptabilises_avec_succes', null, [donnees.resultat.succes]));
			}

			await $.each(donnees.resultat.erreurs, async (message_erreur, nombre) => {

				await erreur(this.$root.traduction('interface.liste.erreur')+" ("+nombre+"x) : "+message_erreur);
			});

			this.actualisation_filtres();
		});
    },

@endpush
