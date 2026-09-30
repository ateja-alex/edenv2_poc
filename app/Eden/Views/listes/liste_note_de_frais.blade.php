@extends('eden::composants_vue.js.liste_libre')

@push('vue_liste_actions')
	<!-- Modale valider les documents -->
	<div class="modal fade" id="modal_valider_note_de_frais" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">@traduction('interface.listes.valider_notes_de_frais')</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					@traduction('interface.listes.que_souhaitez_vous_faire')<br/><br/>
					<span class="btn btn-xs btn-primary js_action_lignes_selectionnees" :class="this.liste.lignes_selectionnees.length == 0 ? 'disabled ' : ''" @click="eden_changer_statut_elements(true, 1)">@traduction('interface.listes.valider_notes_de_frais_selectionnees') (<span class="js_nombre_lignes_selectionnees" v-html="this.liste.lignes_selectionnees.length"></span>)</span><br/><br/>
					<span class="btn btn-xs btn-primary" @click="eden_changer_statut_elements(false, 1)">@traduction('interface.listes.valider_toutes_les_notes_de_frais')</span><br/>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">@traduction('interface.listes.fermer')</button>
				</div>
			</div>
		</div>
	</div>

	<!-- Modale refuser les notes de frais -->
	<div class="modal fade" id="modal_refuser_note_de_frais" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">@traduction('interface.listes.refuser_notes_de_frais')</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					@traduction('interface.listes.que_souhaitez_vous_faire')<br/><br/>
					<span class="btn btn-xs btn-primary js_action_lignes_selectionnees" :class="this.liste.lignes_selectionnees.length == 0 ? 'disabled ' : ''" @click="eden_changer_statut_elements(true, 2)">@traduction('interface.listes.refuser_notes_de_frais_selectionnees') (<span class="js_nombre_lignes_selectionnees" v-html="this.liste.lignes_selectionnees.length"></span>)</span><br/><br/>
					<span class="btn btn-xs btn-danger" @click="eden_changer_statut_elements(false, 2)">@traduction('interface.listes.refuser_toutes_les_notes_de_frais')</span><br/>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">@traduction('interface.listes.fermer')</button>
				</div>
			</div>
		</div>
	</div>

	<!-- Modale refuser les notes de frais -->
	<div class="modal fade" id="modal_pdf_note_de_frais" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">@traduction('interface.listes.exporter_en_pdf')</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					@traduction('interface.listes.que_souhaitez_vous_faire')<br/><br/>
					<span class="btn btn-xs btn-primary js_action_lignes_selectionnees" :class="this.liste.lignes_selectionnees.length == 0 ? 'disabled ' : ''" @click="eden_export_pdf_selectionnes({{$id_liste}})">@traduction('interface.listes.exporter_les_notes_de_frais_selectionnees') (<span class="js_nombre_lignes_selectionnees" v-html="this.liste.lignes_selectionnees.length"></span>)</span><br/><br/>
					<span class="btn btn-xs btn-primary" @click="eden_export_pdf_tous({{$id_liste}})">@traduction('interface.listes.exporter_toutes_les_notes_de_frais')</span><br/>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">@traduction('interface.listes.fermer')</button>
				</div>
			</div>
		</div>
	</div>

	<form method="post" action="{{ route('note_de_frais.export_pdf', [], false) }}" target="_blank" style="display: none;" id="formulaire_pdf_note_de_frais_{{$id_liste}}">
		<input type="text" name="ids_note_de_frais" id="imprimer_ids_note_de_frais_{{$id_liste}}" value="" />
		<input type="text" name="id_liste" value="{{ $id_liste }}" />
		<input type="text" name="tous" id="imprimer_tous_ids_note_de_frais_{{$id_liste}}" value="" />
		<input type="text" name="filtres" id="filtres_note_de_frais_{{$id_liste}}" value="" />
	</form>
@endpush

@push('donnees_pour_vuejs_methods')

	eden_changer_statut_elements: function(elements_selectionnes, statut) {

		if(statut === 1)
			$('#modal_valider_note_de_frais').modal('hide');
		else
			$('#modal_refuser_note_de_frais').modal('hide');

		var ids = [];

		if(elements_selectionnes)
			ids = this.liste.lignes_selectionnees;
		else
			ids = this.liste.lignes.map((ligne) => ligne.id);

		if(ids.length == 0)
			return;

		loading(true);

		$.post({

			url: "{{ route('note_de_frais.changement_statut', [], false) }}",
			data: {

				statut: statut,
				ids: ids,
			}
		}).done(async (retour) => {

			// on cache le loader
			loading(false);

			if(retour.retour === false) {

				retour.message = retour.message.replaceAll('<br>', '\n')
				await erreur(retour.message);
			}

			// on actualise
			this.actualisation_filtres();
		});
	},

	eden_export_pdf_selectionnes: function(id_liste){

		var composant =this;
		$('#modal_pdf_note_de_frais').modal('hide');

		loading(true);
		// on va chercher les ID des éléments sélectionnés
		var ids = composant.liste.lignes_selectionnees;

		if(ids.length == 0)
		return;

		$('#imprimer_ids_note_de_frais_'+id_liste).val(JSON.stringify(ids));

		$('#imprimer_tous_ids_note_de_frais_'+id_liste).val(false);

		$('#filtres_note_de_frais_'+id_liste).val(JSON.stringify(composant.liste.options_liste.filtres));

		$('#formulaire_pdf_note_de_frais_'+id_liste).submit();

		loading(false);

	},

	eden_export_pdf_tous: async function(id_liste){

		var composant =this;

		$('#modal_pdf_note_de_frais').modal('hide');

		loading(true);

		await composant.actualisation_filtres(true);

		var ids = composant.liste.ids;

		//console.log(ids);

		if(ids.length == 0){
			loading(false);
			return;
		}

		$('#imprimer_ids_note_de_frais_'+id_liste).val(JSON.stringify(ids));

		$('#imprimer_tous_ids_note_de_frais_'+id_liste).val(true);

		$('#filtres_note_de_frais_'+id_liste).val(JSON.stringify(composant.liste.options_liste.filtres));

		$('#formulaire_pdf_note_de_frais_'+id_liste).submit();

		loading(false);

	},

@endpush

@push('donnees_pour_vuejs_mounted')

	this.$root.$on('enregistrement_liste_'+this.id_liste,(donnees) => {
		if(donnees.lien_vers_element)
			location.href = donnees.lien_vers_element;
	});

@endpush
