@extends('eden::listes.includes.actions.action_en_masse_generique',['action' => 'campagne_de_prospection'])

@section('texte_modale_campagne_de_prospection')
	<select name="campagne_de_prospection_id" id="campagne_de_prospection_id">
		<option value=""></option>
		<option v-for="campagne in listes_prospection" :value="campagne.id" v-html="campagne.nom"></option>
	</select><br/><br/>
	 @traduction('interface.listes.qui_voulez_vous_ajouter_a_la_campagne')
	<br/><br/>
@endsection

@push('donnees_pour_vuejs_data')
	listes_prospection : [],
@endpush

@push('donnees_pour_vuejs_mounted')
	this.recuperer_listes_prospection();
@endpush

@push('donnees_pour_vuejs_methods')

	recuperer_listes_prospection: function() {

		var composant = this;
		$.post({

			url: "/eden/element/campagne_de_prospection/recuperer_tous_les_elements_ajax",
			dataType: "json",
			data: {
				'ordre': 'date_de_debut',
			}
		}).done(function(liste) {

			composant.listes_prospection = liste;
			composant.$forceUpdate();
		});

	},

    /**
	 *
	 * On ajoute les lignes à une campagne de prospection
	 *
	 */
	campagne_de_prospection_elements_selectionnes: function() {

		var composant = this;
		this.modale_campagne_de_prospection = false;

		// on va chercher les ID des éléments sélectionnés
		var ids = composant.liste.lignes_selectionnees;

		if(ids.length == 0)
			return;

		// on récupère les ids...
		loading(true);

		$.post({

			url: "{{ route('campagne_de_prospection.ajouter', [], false) }}",
			data: {

				campagne_de_prospection_id: $('#campagne_de_prospection_id').val(),
				ids: ids,
			}
		}).always(async function(retour) {

			// on cache le loader
			loading(false);

			if(retour.retour === false) {

				await erreur(retour.message);
				return;
			}

			info(composant.$root.traduction('interface.listes.contacts_ajoutes_avec_succes'));

		});
	},

	/**
	 *
	 * On envoie un mail à tous les éléments de la liste
	 *
	 */
	campagne_de_prospection_tous_elements: async function() {

		var composant = this;

		// on récupère les ids...
		loading(true);

		await composant.actualisation_filtres(true);

		var ids = composant.liste.ids;

		$.post({

			url: "{{ route('campagne_de_prospection.ajouter', [], false) }}",
			data: {

				campagne_de_prospection_id: $('#campagne_de_prospection_id').val(),
				ids: ids,
			}
		}).always(async function(retour) {

			// on cache le loader
			loading(false);

			if(retour.retour === false) {

				await erreur(retour.message);
				return;
			}

			info(composant.$root.traduction('interface.listes.contacts_ajoutes_avec_succes'));

		});
	},

@endpush
