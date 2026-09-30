@extends('eden::composants_vue.js.liste_libre')

@section('options_modale_edition')
	<div style="display: flex;gap:5px;justify-content: flex-end">

		<button type="button" class="btn btn-secondary css_btn_responsive" @click="liste_paiement_neutraliser(1);" v-show="(paiement.neutralise === null || paiement.neutralise === 0) && paiement.id !== null">
			@traduction('interface.listes.neutraliser')
		</button>
		<button type="button" class="btn btn-secondary css_btn_responsive" @click="liste_paiement_neutraliser(0);" v-show="paiement.neutralise == 1">
			@traduction('interface.listes.deneutraliser')
		</button>
		<a :href="'/eden/fiche/'+liste.type_element+'/'+liste.element_id_modification" class="btn btn-secondary css_btn_responsive" v-if="liste.fiche == 1 && liste.element_id_modification != null && liste.element_id_modification != ''">
			@traduction('interface.listes.afficher')
		</a>
		<button type="button" class="btn btn-secondary css_btn_responsive" @click="retour_a_la_liste();">
			@traduction('interface.listes.fermer')
		</button>
		<button type="button" class="btn btn-danger css_btn_responsive" v-if="(ligne_modification == null || ligne_modification.droits_suppression) && liste.element_id_modification != null && liste.element_id_modification != ''" @click="supprimer_dans_liste(liste.element_id_modification); retour_a_la_liste();">
			@traduction('interface.listes.supprimer')
		</button>

		<div class="conteneur_boutons_enregistrement" v-if="ligne_modification == null || ligne_modification.droits_modification">
			<button type="button" class="btn btn-primary css_btn_responsive" @click="enregistrer_dans_liste()">
				@traduction('interface.listes.enregistrer')
			</button>
			<span class="btn btn-primary css_btn_responsive bouton_dropdown_enregistrer" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				<i class="fas fa-angle-down"></i>
			</span>
			<div class="dropdown-menu">
				<span class="dropdown-item" @click="enregistrer_dans_liste({}, 1)">@traduction('interface.listes.enregistrer_et_nouveau')</span>
				<span class="dropdown-item" @click="enregistrer_dans_liste({}, 2)">@traduction('interface.listes.enregistrer_et_dupliquer')</span>
			</div>
		</div>
	</div>
@endsection

@push('donnees_pour_vuejs_methods')

	liste_paiement_neutraliser: function(valeur_neutralisation) {

		var vue_instance =this;

		// On afficher le loader
		loading();

		var id_paiement = this.paiement.id;

		// on enregistre les infos du champ libre
		$.post({

			url: "eden/element/paiement/"+id_paiement+"/enregistrer",
			dataType: "json",
			method: 'POST',
			data: {
				neutralise: valeur_neutralisation,
			}

		}).done(async function(donnees) {

			// On retire le loader
			loading(false);

			if(donnees.retour !== true) {

				await erreur(donnees.retour);
				return;
			}
			else {

				if(valeur_neutralisation == 1)
					info(vue_instance.$root.traduction('interface.listes.paiement_neutralise'));
				else
					info(vue_instance.$root.traduction('interface.listes.paiement_deneutralise'));

				vue_instance.retour_a_la_liste();

				vue_instance.actualisation_filtres();
			}

		});
	},

@endpush