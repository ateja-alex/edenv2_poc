@extends('eden::listes.includes.actions.action_en_masse_generique',['action' => 'envoi_questionnaire'])

@section('texte_modale_envoi_questionnaire')
	<p class="my-1">@traduction('interface.listes.selectionner_un_questionnaire')</p>
	<select class="my-2" name="questionnaire_id" id="questionnaire_id" >
		<option v-for="questionnaire in $root.valeurs_listes_formatees[507]" :value="questionnaire.id_valeur">@{{ questionnaire.valeur }}</option>
	</select>
	<button class="css_pointer" style="padding: 10px;background: lightgrey;border: unset;" @click="element_origine = true" v-if="element_origine === false"> + Ajouter un élémént d'origine </button>
	<template v-else>
		<p class="my-1" style="display: flex;">
			@traduction('champs_libres.questionnaire_element_repondant.type_element_origine.nom')
			<i @click="element_origine = false" class="ml-auto css_pointer fas fa-times"></i>
			</label>
		<div class="row">
			<div class="col-sm-4">{!! management('questionnaire_element_repondant')->champ('type_element_origine')->cree() !!}</div>
			<div class="col-sm-4">{!! management('questionnaire_element_repondant')->champ('element_origine_id')->cree() !!}</div>
		</div>
	</template>
	<p class="my-1">@traduction('interface.listes.a_qui_voulez_vous_envoyer_questionnaire')</p>
@endsection

@push('donnees_pour_vuejs_data')
	questionnaire_element_repondant : {!! modele_par_defaut('questionnaire_element_repondant') !!},
	element_origine : false,
@endpush

@push('donnees_pour_vuejs_methods')

    /**
	 * 
	 * On ajoute les lignes à une campagne de questionnaire
	 * 
	 */
	envoi_questionnaire_elements_selectionnes: function() {

		var composant = this;
		
		// on va chercher les ID des éléments sélectionnés
		var ids = composant.liste.lignes_selectionnees;

		var type_element = composant.liste.type_element;

		if(ids.length == 0)
			return;

		// on récupère les ids...
		loading(true);

		$.post({
				
			url: "{{ route('questionnaire.envoi', [], false) }}",
			data: {

				questionnaire_id: $('#questionnaire_id').val(),
				ids: ids,
				type_element: type_element,
				type_element_origine: composant.questionnaire_element_repondant.type_element_origine,
				element_origine_id: composant.questionnaire_element_repondant.element_origine_id,
			}
		}).done(async (retour) => {
			
			// on cache le loader
			loading(false);
			
			if(retour.retour === false) {
				
				await erreur(retour.message);
				return;
			}

			this.modale_envoi_questionnaire = false;
			
			info(composant.$root.traduction('interface.listes.questionnaires_envoyes_avec_succes'));
			
		});
	},
	
	/**
	 * 
	 * On envoie un mail à tous les éléments de la liste
	 * 
	 */
	envoi_questionnaire_tous_elements: async function() {

		var composant = this;

		// on récupère les ids...
		loading(true);
		
		await composant.actualisation_filtres(true);

		var ids = composant.liste.ids;

		var type_element = composant.liste.type_element;

		$.post({

			url: "{{ route('questionnaire.envoi', [], false) }}",
			data: {

				questionnaire_id: $('#questionnaire_id').val(),
				ids: ids,
				type_element: type_element,
				type_element_origine: composant.questionnaire_element_repondant.type_element_origine,
				element_origine_id: composant.questionnaire_element_repondant.element_origine_id,
			}
		}).done(async (retour) => {

			// on cache le loader
			loading(false);

			if(retour.retour === false) {

				await erreur(retour.message);
				return;
			}

			this.modale_envoi_questionnaire = false;

			info(composant.$root.traduction('interface.listes.questionnaires_envoyes_avec_succes'));

		});
	},

@endpush
