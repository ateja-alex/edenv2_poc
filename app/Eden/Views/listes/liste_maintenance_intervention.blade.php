@extends('eden::composants_vue.js.liste_libre')

@push('scripts')

	<script type="text/javascript">
		$('body').on('click', '.js_generer_facture_maintenance_intervention', function() {

			var id_element = $(this).attr('id_element');
			
			var id_liste = $(this).closest('.js_liste').attr('id_liste');

			vue_instance.generer_facture_maintenance_intervention(id_element, id_liste);
		});
	</script>

@endpush

@push('donnees_pour_vuejs_methods')

	generer_facture_maintenance_intervention: async function(id_element, id_liste) {

		if(!await confirm_eden())
			return false;

		$('#liste_elements_'+id_liste).addClass('css_actualisation_ajax_en_cours');


		// on fait un appel ajax pour supprimer
		$.get({

			url: "eden/element/maintenance_intervention/"+id_element+"/generer_facture_maintenance_intervention",
			dataType: "json",
			method: 'GET'
		}).done(async function(donnees) {

			if(donnees.retour !== true) {

				$('#liste_elements_'+id_liste).removeClass('css_actualisation_ajax_en_cours');

				await erreur(donnees.retour);
				return;
			}

			// on actualise la liste
			vue_instance.actualisation_filtres();
		});
	},

@endpush
