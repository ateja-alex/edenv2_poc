
@foreach(\App\Eden\Models\Champ_libre::where('type',42)->pluck('type_element')->unique() as $type_element)
	@push('donnees_pour_vuejs_data')

		{{ $type_element }}: {
		
		@foreach(table_libre($type_element)->champs_libres()->where('type', 7)->get() as $champ_libre)
				
			{{ $champ_libre->nom_sql }}: '',
		@endforeach
		
		@foreach(table_libre($type_element)->champs_libres()->where('type', 10)->get() as $champ_libre)
				
			{{ $champ_libre->nom_sql }}: {},
		@endforeach
		
		
	},
	

	@endpush
    <div class="modal fade" id="modal_creation_a_la_volee_{{$type_element}}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">@traduction('interface.modal_creation_element_pour_recherche.modale.titre') {{ table_libre($type_element)->element_pluriel }}</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<form action="#" method="post" class="css_form" id="formulaire_element_liste_{{$type_element}}" enctype="multipart/form-data">

						<input type="hidden" id="id_element_{{ $type_element }}" v-model="{{ $type_element }}.id"/>
						
						@if(view()->exists("eden::formulaires.$type_element"))
							@include("eden::formulaires.$type_element",['management_element' => management($type_element)])
						@else
							@include("eden::formulaires.formulaire_generique",['management_element' => management($type_element)])
						@endif

					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal" >@traduction('interface.modales.fermer')</button>
					<button type="button" class="btn btn-danger" data-dismiss="modal" >@traduction('interface.modales.supprimer')</button>
					<button type="button" class="btn btn-primary" type_element={{$type_element}} @click="enregistrer_modal_recherche" >@traduction('interface.modales.enregistrer')</button>
				</div>
			</div>
		</div>
	</div>

@endforeach

@push('donnees_pour_vuejs_methods')	

		enregistrer_modal_recherche: function(event) {
			
			var event=$(event.target);
			var formulaire = $('#formulaire_element_liste_'+event.attr('type_element'));
			if(formulaire.length == 0) {

				toastr.error("Le formulaire pour éditer l'élément doit avoir l'id formulaire_element_liste, il n'a pas été trouvé");
				return;
			}

			if($('#id_element_'+event.attr('type_element')).val() != '' && $('#id_element_'+event.attr('type_element')).val() != '0')
				var url = "eden/element/"+event.attr('type_element')+"/"+$('#id_element_'+event.attr('type_element')).val()+"/enregistrer";
			else
				var url = "eden/element/"+event.attr('type_element')+"/creer";

			// On afficher le loader
			loading();
			

			// on enregistre les infos du champ libre
			$.post({

				url: url,
				dataType: "json",
				method: 'POST',
				// data: formulaire.serialize()
				data: new FormData(formulaire[0]),
				cache: false,
				contentType: false,
				processData: false,
				// Custom XMLHttpRequest
				xhr: function() {
					var myXhr = $.ajaxSettings.xhr();
					if (myXhr.upload) {
						// For handling the progress of the upload
						myXhr.upload.addEventListener('progress', function(e) {
							if (e.lengthComputable) {
								$('progress').attr({
									value: e.loaded,
									max: e.total,
								});
							}
						} , false);
					}
					return myXhr;
				}
			}).done(async function(donnees) {
				
				// On retire le loader
				loading(false);
				
				if(donnees.retour !== true) {
				
					await erreur(donnees.retour);
					return;
				}

				$('#modal_creation_a_la_volee_'+event.attr('type_element')).modal('hide');
				
				
			});
	},

	
	@endpush




