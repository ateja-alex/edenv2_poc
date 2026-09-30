@if($management->existe())
	
	<span :title="traduction('document.actions.relance_utilisateurs.relancer')" data-toggle="tooltip">
		<span class="css_action_icon primaire fa fa-fw fa-envelope-open-text" data-toggle="modal" data-target="#relance_utilisateurs_modal"></span>
	</span>

	<div class="modal fade" id="relance_utilisateurs_modal" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLabel">@traduction('document.actions.relance_utilisateurs.choix_utilisateurs')</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<form action="">
						@foreach(modele('utilisateur')->liste_utilisateurs_visibles() as $utilisateur)
							<div>
							<input type="checkbox" value="{{ $utilisateur->id }}" v-model="utilisateurs_a_relancer" id="utilisateur_{{ $utilisateur->id }}">
								<label for="utilisateur_{{ $utilisateur->id }}" style="color:black;font-size:14px;">
									{{ $utilisateur->prenom .' '.$utilisateur->nom }}
								</label>
							</div>
						@endforeach
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-danger" data-dismiss="modal">@traduction('interface.modales.fermer')</button>
					<button type="button" class="btn btn-primary" @click="relancer_utilisateurs">@traduction('document.actions.relance_utilisateurs.relancer')</button>
				</div>
			</div>
		</div>
	</div>

	@push('donnees_pour_vuejs_data')
		utilisateurs_a_relancer : [],
	@endpush

	@push('donnees_pour_vuejs_methods')

		relancer_utilisateurs : function() {

			loading(true);

			$.ajax({

				url: "{{ route('document.relancer_utilisateurs', ['type_element' => $management->_type_element, 'id' => $management->modele->id]) }}",
				dataType: "json",
				method: "POST",
				data: { utilisateurs_a_relancer: vue_instance.utilisateurs_a_relancer }
			}).done(function(retour) {

				loading(false);

                if(retour.succes != true){
                    toastr.error(retour.message);
                    return false;
                }

				toastr.success(vue_instance.traduction('messages.js.documents.relances_envoyees'));
				$('#relance_utilisateurs_modal').modal('hide');
			});
		},
	@endpush
@endif
