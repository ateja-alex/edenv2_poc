@foreach($listes_enfants as $infos_liste)
	<div class="row">
		<div class="col-md-12">
			<div class="card mb-3">
				<div class="card-header">
					<h4>
						{{ $infos_liste['titre'] }}
						<span class="css__lien" @click="{{$infos_liste['type_element']}}_creer"><i class="fa fa-fw fa-plus-square"></i> @traduction('module_sur_fiche.listes_enfants.nouveau')</span>
					</h4>
				</div>
				<div class="card-body">

					<div class="table-responsive">
						<table class="table table-bordered table-hover" width="100%" cellspacing="0">
							<thead>
								<tr>
									<th>#</th>
									@foreach($infos_liste['colonnes'] as $valeur_colonne)
										<th>{{ $valeur_colonne }}</th>
									@endforeach
								</tr>
							</thead>
							<tbody>
								<tr v-show="listes_enfants_{{ $infos_liste['type_element'] }}.length == 0">
									<td colspan="{{ count($infos_liste['colonnes']) + 1 }}">@traduction('module_sur_fiche.listes_enfants.aucun_1') &laquo; <span class="css__lien" @click="{{$infos_liste['type_element']}}_creer">@traduction('module_sur_fiche.listes_enfants.aucun_2')</span> &raquo; @traduction('module_sur_fiche.listes_enfants.aucun_3')</td>
								</tr>
								<tr v-for="element_{{ $infos_liste['type_element'] }} in listes_enfants_{{ $infos_liste['type_element'] }}">
									<td><span class="css__lien" @click="{{$infos_liste['type_element']}}_modifier(element_{{$infos_liste['type_element']}}.id)">{{ '{{element_'.$infos_liste['type_element'].'.id' }} }}</span></td>
									@foreach($infos_liste['colonnes'] as $id_colonne => $valeur_colonne)
										<td>{{ '{{element_'.$infos_liste['type_element'].'.'.$id_colonne }} }}</td>
									@endforeach
								</tr>
							</tbody>
						</table>
					</div>
					
				</div>
			</div>
		</div>
	</div>		
	
	<!-- Modal ajout élément -->
	<div class="modal fade" id="modal_ajout_liste_enfant_{{$infos_liste['type_element']}}" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">@traduction('module_sur_fiche.listes_enfants.titre_modal')</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<input type="hidden" name="id" v-model="{{ $infos_liste['type_element'] }}.id"  />
				
				<div class="modal-body">
					<form action="#" method="post" class="css_form">

						<input type="hidden" name="{{ $infos_liste['cle_etrangere'] }}" value="{{ $management_element->modele->id }}" />
						@if(view()->exists("eden::formulaires.".$infos_liste['type_element'].'_fiche_'.$management_element->_type_element))
							@include("eden::formulaires.".$infos_liste['type_element'].'_fiche_'.$management_element->_type_element)
						@elseif(view()->exists("eden::formulaires.".$infos_liste['type_element'].'_fiche'))
							@include("eden::formulaires.".$infos_liste['type_element'].'_fiche')
						@elseif(view()->exists("eden::formulaires.".$infos_liste['type_element']))
							@include("eden::formulaires.".$infos_liste['type_element'])
						@else
							@include("eden::formulaires.formulaire_generique", ['type_element' => $infos_liste['type_element'], 'champs_a_ne_pas_creer' => array($infos_liste['cle_etrangere'])])
						@endif
					
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-danger" @click="{{$infos_liste['type_element']}}_supprimer" v-show="{{ $infos_liste['type_element'] }}.id != ''">{{ traduction('interface.modales.supprimer') }}</button>
					<button type="button" class="btn btn-primary" @click="{{$infos_liste['type_element']}}_enregistrer">{{ traduction('interface.modales.enregistrer') }}</button>
				</div>
			</div>
		</div>
	</div>	


	@push('donnees_pour_vuejs_data')
		listes_enfants_{{ $infos_liste['type_element'] }}: {!! collect($infos_liste['donnees']) !!},
		listes_enfants_donnees_{{ $infos_liste['type_element'] }}: {!! collect($infos_liste['donnees_liste']) !!},
		{{ $infos_liste['type_element'] }}: {id: ''},
	@endpush
	
	@push('donnees_pour_vuejs_methods')
	
		<!-- créer -->
		{{$infos_liste['type_element']}}_creer: function() {
			
			$('#modal_ajout_liste_enfant_{{$infos_liste['type_element']}}').modal('show');
			
			// on réinitialise le contact
			this.{{ $infos_liste['type_element'] }} = {id: ''};
		},
		
		<!-- modifier -->
		{{$infos_liste['type_element']}}_modifier: function(id) {
			
			$.each(vue_instance.listes_enfants_donnees_{{ $infos_liste['type_element'] }}, function(osef, {{$infos_liste['type_element']}}) {
				
				if({{$infos_liste['type_element']}}.id == id) {
					
					vue_instance.{{$infos_liste['type_element']}} = {{$infos_liste['type_element']}};
				}
			});
			
			$('#modal_ajout_liste_enfant_{{$infos_liste['type_element']}}').modal('show');
		},
		
		<!-- enregistrer-->		
		{{$infos_liste['type_element']}}_enregistrer: function() {
			
			// on fait un appel ajax
			
			if($('#modal_ajout_liste_enfant_{{$infos_liste['type_element']}} input[name=id]').val() != '' && $('#modal_ajout_liste_enfant_{{$infos_liste['type_element']}} input[name=id]').val() != '0')
				var url = "eden/element/{{$infos_liste['type_element']}}/"+$('#modal_ajout_liste_enfant_{{$infos_liste['type_element']}} input[name=id]').val()+"/enregistrer";
			else
				var url = "eden/element/{{$infos_liste['type_element']}}/creer";

			// On affiche le loader
			loading();
			
			// on enregistre les infos du champ libre
			$.post({

				url: url,
				dataType: "json",
				method: 'POST',
				data: $('#modal_ajout_liste_enfant_{{$infos_liste['type_element']}} form').serialize()
			}).done(async function(donnees) {
				
				// On retire le loader
				loading(false);
				
				if(donnees.retour !== true) {
				
					await erreur(donnees.retour);
					return;
				}

				$('#modal_ajout_liste_enfant_{{$infos_liste['type_element']}}').modal('hide');
				// on actualise la liste
				vue_instance.{{$infos_liste['type_element']}}_actualiser();
				
			});
			
		},
		
		<!-- supprimer-->
		{{$infos_liste['type_element']}}_supprimer: async function() {

			if(!await confirm_eden("{{ traduction('interface.modales.confirmation_suppression') }}"))
				return false;

			loading(true);

			// on fait un appel ajax pour supprimer
			$.get({

				url: "eden/element/{{$infos_liste['type_element']}}/"+vue_instance.$data.{{$infos_liste['type_element']}}.id+"/supprimer",
				dataType: "json",
				method: 'GET'
			}).done(async function(donnees) {

				if(donnees.retour !== true) {

					loading(false);

					await erreur(donnees.retour);
					return;
				}
				
				$('#modal_ajout_liste_enfant_{{$infos_liste['type_element']}}').modal('hide');

				// on actualise la liste
				vue_instance.{{$infos_liste['type_element']}}_actualiser();
			});
		},
		
		<!-- actualiser-->	
		{{$infos_liste['type_element']}}_actualiser: function() {
			
			loading(true);
			
			$.get({

				url: "{{ route('base_eden.fiche.liste_elements_enfants', [$management_element->_type_element, $infos_liste['type_element'], $management_element->modele->id], false) }}",
				dataType: "json"
			}).done(function(elements) {
				
				// On retire le loader
				loading(false);
				
				vue_instance.listes_enfants_{{ $infos_liste['type_element'] }} = elements.donnees;
				vue_instance.listes_enfants_donnees_{{ $infos_liste['type_element'] }} = elements.donnees_liste;
			});
		},	
		
	@endpush

@endforeach
