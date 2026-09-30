<div>
	<div class="card mb-3">
		<div class="card-header">
			<h4>
				@traduction('module_sur_fiche.client.parametrage_avance_des_documents.titre')
			</h4>
		</div>
		<div class="card-body">
			
			<div class="row">
				<div class="col-sm-3">
					<b>@traduction('module_sur_fiche.client.parametrage_avance_des_documents.document')</b>
				</div>
				<div class="col-sm-3">
					<b>@traduction('module_sur_fiche.client.parametrage_avance_des_documents.modeles')</b>
				</div>
				<div class="col-sm-2">
					<b>@traduction('module_sur_fiche.client.parametrage_avance_des_documents.exemplaires')</b>
				</div>
				<div class="col-sm-4">
					<b>@traduction('module_sur_fiche.client.parametrage_avance_des_documents.champs_obligatoires')</b>
				</div>
			</div>
			<div class="row" v-for="parametrage_avance_document in liste_parametrage_avance_des_documents">
				<div class="col-sm-3">
					<span class="css__lien" @click="parametrage_avance_des_documents_modifier(parametrage_avance_document)">@{{ parametrage_avance_document.type_element_nom }}</span>
				</div>
				<div class="col-sm-3">@{{ parametrage_avance_document.modele }}</div>
				<div class="col-sm-2">@{{ parametrage_avance_document.exemplaires }}</div>
				<div class="col-sm-4">
					<span class="badge badge-default" v-for="champ_obligatoire in parametrage_avance_document.champs_obligatoires">@{{ champ_obligatoire}}</span>
					<span class="badge badge-default" v-show="parametrage_avance_document.champs_obligatoires.length == 0">@traduction('module_sur_fiche.client.parametrage_avance_des_documents.aucun')</span>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Modal ajout contact -->
<div class="modal fade" id="modal_modification_parametrage_avance_des_documents" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">@traduction('module_sur_fiche.client.parametrage_avance_des_documents.titre_modal')</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			
			<div class="modal-body">
				<form action="#" id="formulaire_modification_parametrage_avance_des_documents" method="post" class="css_form">
					<input type="hidden" name="client_id" value="{{ $management_element->modele->id }}" />
					<input type="hidden" name="type_element" v-model="parametrage_avance_des_documents.type_element" />

					<!-- nombre d'exemplaires -->
					<div class="row">
						@champ('parametrage_avance_des_documents', 'exemplaires', 4, 2)
					</div>
					<!-- choix du modèle -->
					<div class="row">
						<div class="col-md-4">@traduction('module_sur_fiche.client.parametrage_avance_des_documents.modele_de_document')</div>
						<div class="col-md-2">
							<select name="modele" v-model="parametrage_avance_des_documents.modele">
								<option value="standard">{{traduction('module_sur_fiche.client.parametrage_avance_des_documents.standard')}}</option>
								<option v-for="modele_parametrage_des_documents in parametrage_avance_des_documents_vues[parametrage_avance_des_documents.type_element]" :value="modele_parametrage_des_documents">@{{ modele_parametrage_des_documents }}</option>
							</select>
						</div>
					</div>
					<!-- champs obligatoires -->
					<div class="row">
						<div class="col-md-12">@traduction('module_sur_fiche.client.parametrage_avance_des_documents.champs_obligatoires')</div>
					</div>
					<div class="row">
						@foreach(\App\Eden\Models\Champ_libre::whereIn('type_element', array('facture_vente', 'devis_vente', 'bl_vente', 'acompte_vente', 'avoir_vente', 'commande_vente'))->get() as $champ_libre)
							<div class="col-md-12" v-if="'{{$champ_libre->type_element}}' == parametrage_avance_des_documents.type_element">
								<input type="checkbox" name="champs_obligatoires[]" value="{{$champ_libre->nom_sql}}" v-model="parametrage_avance_des_documents.champs_obligatoires" />
								{{ $champ_libre->nom }}
							</div>
						@endforeach
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" @click="parametrage_avance_des_documents_enregistrer">{{traduction('interface.modales.enregistrer')}}</button>
			</div>
		</div>
	</div>
</div>

@push('donnees_pour_vuejs_data')
	liste_parametrage_avance_des_documents: {!! collect($parametrage_avance_des_documents['parametrages']) !!},
	parametrage_avance_des_documents_vues: {!! collect($parametrage_avance_des_documents['vues']) !!},
	parametrage_avance_des_documents: {champs_obligatoires: {}},
@endpush
@push('donnees_pour_vuejs_methods')
	parametrage_avance_des_documents_enregistrer: function() {
		
		loading(true);
		
		// on enregistre les infos du paramétrage via ajax
		$.post({

			url: "{{ route('base_eden.fiche.index_post', ['client', $management_element->modele->id, 'parametrage_avance_des_documents_enregistre']) }}",
			dataType: "json",
			method: 'POST',
			data: $('#formulaire_modification_parametrage_avance_des_documents').serialize()
		}).done(function(donnees) {
			
			// On retire le loader
			loading(false);
			
			$('#modal_modification_parametrage_avance_des_documents').modal('hide');
			
			vue_instance.liste_parametrage_avance_des_documents = donnees.parametrages;
		});
	},
	
	parametrage_avance_des_documents_modifier: function(parametrage_avance_des_documents) {
		
		this.parametrage_avance_des_documents = parametrage_avance_des_documents;
		
		$('#modal_modification_parametrage_avance_des_documents').modal('show');
	},
@endpush