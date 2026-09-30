<div class="row">
	<div class="col-md-12">
		<div class="card mb-3">
			<div class="card-header">
				<h4>
					@traduction('module_sur_fiche.projet.participants.titre')
				</h4>
			</div>
			<div class="card-body">

				<div class="table-responsive">
					<table class="table table-bordered table-hover" width="100%" cellspacing="0">
						<thead>
							<tr>
								<th scope="col">@traduction('module_sur_fiche.projet.participants.nom')</th>
								<th scope="col">@traduction('module_sur_fiche.projet.participants.prenom')</th>
								<th scope="col">@traduction('module_sur_fiche.projet.participants.date_naissance')</th>
								<th scope="col">@traduction('module_sur_fiche.projet.participants.nationalite')</th>
								<th scope="col">@traduction('module_sur_fiche.projet.participants.commentaires')</th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="(participant, participant_id) in participants_projet" class="css_form" v-show="participant.nom != null" :id_participant="participant_id">
								<td>
									<input type="text" v-model="participant.nom" name="nom" style="display: none" @blur="participant_projet_enregistrer(participant, participant_id)" />
									<span onClick="$(this).hide(); $(this).parent().find('input').show()">@{{ participant.nom }}<p v-show="participant.nom == null || participant.nom == ''" style="color: #aaa;">@traduction('module_sur_fiche.projet.participants.non_renseigne')</p></span>
								</td>
								<td>
									<input type="text" v-model="participant.prenom" name="prenom" style="display: none" @blur="participant_projet_enregistrer(participant, participant_id)" />
									<span onClick="$(this).hide(); $(this).parent().find('input').show()">@{{ participant.prenom }}<p v-show="participant.prenom == null || participant.prenom == ''" style="color: #aaa;">@traduction('module_sur_fiche.projet.participants.non_renseigne')</p></span>
								</td>
								<td>
									<input type="text" v-model="participant.date_naissance" name="date_naissance" style="display: none" @blur="participant_projet_enregistrer(participant, participant_id)" />
									<span onClick="$(this).hide(); $(this).parent().find('input').show()">@{{ participant.date_naissance }}<p v-show="participant.date_naissance == null || participant.date_naissance == ''" style="color: #aaa;">@traduction('module_sur_fiche.projet.participants.non_renseigne_f')</p></span>
								</td>
								<td>
									<input type="text" v-model="participant.nationalite" name="nationalite" style="display: none" @blur="participant_projet_enregistrer(participant, participant_id)" />
									<span onClick="$(this).hide(); $(this).parent().find('input').show()">@{{ participant.nationalite }}<p v-show="participant.nationalite == null || participant.nationalite == ''" style="color: #aaa;">@traduction('module_sur_fiche.projet.participants.non_renseigne_f')</p></span>
								</td>
								<td>
									<input type="text" v-model="participant.commentaires" name="commentaires" style="display: none" @blur="participant_projet_enregistrer(participant, participant_id)" />
									<span onClick="$(this).hide(); $(this).parent().find('input').show()">@{{ participant.commentaires }}<p v-show="participant.commentaires == null || participant.commentaires == ''" style="color: #aaa;">@traduction('module_sur_fiche.projet.participants.non_renseigne')</p></span>
								</td>
								<td><span class="btn btn-primary btn-xs" @click="participant_projet_supprimer(participant_id)" :data-id="participant_id">{{traduction('interface.modales.supprimer')}}</span></td>
							</tr>										
							<tr class="css_form" id="#nouveau_participant">
								<td><input type="text" name="nom" placeholder="{{ traduction('module_sur_fiche.projet.participants.nom') }}" v-model="nouveau_participant.nom" /></td>
								<td><input type="text" name="prenom" placeholder="{{ traduction('module_sur_fiche.projet.participants.prenom') }}" v-model="nouveau_participant.prenom" /></td>
								<td><input type="text" name="date_naissance" class="datepicker" placeholder="{{ traduction('module_sur_fiche.projet.participants.date_naissance') }}" v-model="nouveau_participant.date_naissance" /></td>
								<td><input type="text" name="nationalite" placeholder="{{ traduction('module_sur_fiche.projet.participants.nationalite') }}" v-model="nouveau_participant.nationalite" /></td>
								<td><input type="text" name="commentaires" placeholder="{{ traduction('module_sur_fiche.projet.participants.commentaires') }}" v-model="nouveau_participant.commentaires" /></td>
								
								<td><span class="btn btn-primary btn-xs" @click="participant_projet_ajouter(participant)">@traduction('module_sur_fiche.projet.participants.ajouter')</span></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>


@push('donnees_pour_vuejs_data')
	participants_projet: {!! $participants_projet !!},
	nouveau_participant: {
		
		nom: '',
		prenom: '',
		date_naissance: '',
		nationalite: '',
		commentaires: ''
	},
@endpush


@push('donnees_pour_vuejs_methods')
	participant_projet_ajouter: function() {
		
		// On affiche le loader
		loading();
		
		// on enregistre les infos du champ libre
		$.post({

			url: "{{ route('base_eden.fiche.index_post', ['projet', $management_element->modele->id, 'ajoute_participant']) }}",
			dataType: "json",
			method: 'POST',
			data: {
				
				nouveau_participant: vue_instance.nouveau_participant
			}
		}).done(function(participants_projet) {
			
			// On retire le loader
			loading(false);
			
			vue_instance.participants_projet = participants_projet;
			
			// on réinitialise
			vue_instance.nouveau_participant = {
				
				nom: '',
				prenom: '',
				date_naissance: '',
				nationalite: '',
				commentaires: ''
			};
		});
	},
	
	participant_projet_supprimer: function(participant_id) {

		{{-- console.log(participant); --}}

		// On affiche le loader
		loading();
		
		// on enregistre les infos du champ libre
		$.post({

			url: "{{ route('base_eden.fiche.index_post', ['projet', $management_element->modele->id, 'supprimer_participant']) }}",
			dataType: "json",
			method: 'POST',
			data: {
				
				participant_id: participant_id
			}
		}).done(function(participants_projet) {
			
			// On retire le loader
			loading(false);
			
			vue_instance.participants_projet = participants_projet;
			
			
		});
	},
	
	participant_projet_enregistrer: function(participant, participant_id) {
		
		//console.log(participant);
		
		loading(true);
		
		
		// on enregistre les infos du champ libre
		$.post({

			url: "{{ route('base_eden.fiche.index_post', ['projet', $management_element->modele->id, 'modifie_participant']) }}",
			dataType: "json",
			method: 'POST',
			data: {
				
				participant_id: participant_id,
				participant: participant
			}
		}).done(function(participants_projet) {
			
			// On retire le loader
			loading(false);
			$('tr[id_participant='+participant_id+'] span').show();
			$('tr[id_participant='+participant_id+'] input').hide();
			
			vue_instance.participants_projet = participants_projet;
			
		});
		
	},
	
@endpush
