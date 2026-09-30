@extends('eden::listes.includes.actions.action_en_masse_generique',['action' => 'envoyer_facturation_electronique', 'desactiver_lignes_liste' => true])

@section('texte_modale_envoyer_facturation_electronique')

	<p>@traduction('interface.listes.envoyer_facturation_electronique.explication')</p>

	<div v-if="erreurs_envoi_facturation_electronique.length" class="alert alert-danger css_erreurs_envoi_facturx">
		<strong>@traduction('interface.listes.envoyer_facturation_electronique.erreurs_titre')</strong>
		<div class="css_erreurs_envoi_facturx_document" v-for="document in erreurs_envoi_facturation_electronique" :key="document.reference">
			<span class="css_erreurs_envoi_facturx_reference">@{{ document.reference }}</span>
			<ul>
				<li v-for="message in document.messages">@{{ message }}</li>
			</ul>
		</div>
	</div>
@endsection

@push('donnees_pour_vuejs_data')
	erreurs_envoi_facturation_electronique : [],
@endpush

@push('donnees_pour_vuejs_methods')

	/**
	 *
	 * Les documents sélectionnés sont contrôlés puis envoyés : au moindre défaut, aucun n'est transmis
	 *
	 */
	envoyer_facturation_electronique_elements_selectionnes: function() {

		var composant = this;

		var ids = composant.liste.lignes_selectionnees;

		if(ids.length == 0)
			return;

		composant.erreurs_envoi_facturation_electronique = [];

		loading(true);

		$.post({

			url: "{{ route('document.envoyer_facturation_electronique_en_masse', [], false) }}",
			dataType: "json",
			data: {
				type_element: composant.liste.type_element,
				ids: ids,
			}
		}).done(async function(donnees) {

			loading(false);

			if(donnees.retour !== true) {

				await erreur(donnees.message);
				return;
			}

			if(donnees.erreurs.length) {

				composant.erreurs_envoi_facturation_electronique = donnees.erreurs;
				return;
			}

			composant.modale_envoyer_facturation_electronique = false;

			composant.deselectionner_toutes_les_lignes();

			composant.actualisation_filtres();

			info(composant.$root.traduction('interface.listes.envoyer_facturation_electronique.documents_envoyes', null, [donnees.succes]));

		}).fail(() => loading(false));
	},
@endpush
