<div class="card mb-3 css_bloc_formulaire_fiche">
	<div class="card-header d-flex align-items-center">
		<h4 style="width:100%" class="css_titre_formulaire_fiche_element">

			{!! $management_element->affiche() !!}

			<template v-if="$root.mode_parametrage == 1">
				<a href="{{ URL::to('/eden/parametrage/table_libre/zoom/note_de_frais') }}"
				 class="css_bouton_modifier_liste_primaire">
					<i class="fas fa-cog"></i> Paramétrer "note_de_frais"
				</a>
			</template>

		</h4>
		<span @click="enregistrer_formulaire_fiche_note_de_frais" class="css_ajouter_element ml-auto" data-toggle="tooltip" data-placement="left" id="formulaire_fiche_enregistrer" :title="$root.traduction('composant.formulaire_fiche.enregistrer')">
			<i class="css_action_icon secondaire far fa-save css_font_16"></i>
		</span>
	</div>
	<div class="card-body">
		<div class="row">
			<div class="col-sm-6 css_form_ligne_titre">@traduction('formulaire.divers.informations_generales')</div>
			<div class="col-sm-6 css_form_ligne_titre">@traduction('formulaire.note_de_frais.justificatif')</div>
		</div>
		<div class="row">
			<div class="col-md-6">
				<formulaire v-if="formulaire_lecture_seule !== null" nom_formulaire="note_de_frais_formulaire" ref="note_de_frais_formulaire" :options="{ formulaire_lecture_seule: formulaire_lecture_seule }"></formulaire>
			</div>
			<div class="col-md-6">{!! management('note_de_frais')->champ('scan')->cree() !!}</div>
		</div>
	</div>
</div>

@push('donnees_pour_vuejs_data')
    utilisateurs : [],
	formulaire_lecture_seule: null,
@endpush

@push('donnees_pour_vuejs_methods')

	enregistrer_formulaire_fiche_note_de_frais : async function(){

		var vue_composant = this;

		// On afficher le loader
		loading(true);

		var donnees = await vue_composant.$refs.note_de_frais_formulaire.enregistrer(
			{scan : this.note_de_frais.scan}
		);

		loading(false);
	},

	calcul_formulaire_lecture_seule: function(){

		const ndf = this.note_de_frais;
		const utilisateurs = this.utilisateurs;
		const utilisateur_connecte = this.$root.moi;

		var employe = this.utilisateurs.find(utilisateur => utilisateur.id == ndf.utilisateur_id);

		if(!employe){
			return false;
		}

		const n_plus_1 = Array.from(employe.validation_ndf_n_plus_1 || []);
		const n_plus_2 = Array.from(employe.validation_ndf_n_plus_2 || []);

		const id = Number(utilisateur_connecte.id);
	
		this.formulaire_lecture_seule = (ndf.accepte >= 1 && ((!n_plus_1.includes(id) && !n_plus_2.includes(id)) || (ndf.valide_n2 != null && n_plus_1.includes(id))))
			 		
	},
@endpush

@push('donnees_pour_vuejs_mounted')	
	$.post({
		url : 'eden/elements/utilisateur',
		dataType : 'json'
	}).done((utilisateurs) => {
		this.utilisateurs = utilisateurs;
		this.calcul_formulaire_lecture_seule();
	});
	this.$once('formulaire_charger',() => {
		this.$refs.note_de_frais_formulaire.element = this.note_de_frais;
	});
@endpush