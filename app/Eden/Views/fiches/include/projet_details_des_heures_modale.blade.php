<div class="modal fade" id="modal_details_heures" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">@traduction('module_sur_fiche.projet_details_des_heures_modale.details_des_heures')</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body" style="font-size: 18px;">
				@foreach($heures_details['temps_par_utilisateur'] as $utilisateur_id => $duree)
					<div class="row">
						<div class="col-md-6" style="text-align: right;">{{ modele('utilisateur', $utilisateur_id)->prenom }} {{ modele('utilisateur', $utilisateur_id)->nom }}</div>
						<div class="col-md-6">
							{{ temps($duree * 60) }}
						</div>
					</div>
				@endforeach
				<div class="row">
					<div class="col-md-6" style="text-align: right;">@traduction('module_sur_fiche.projet_details_des_heures_modale.total')</div>
					<div class="col-md-6">
						{{ temps($heures_details['temps_realise'] * 60) }}
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">@traduction('module_sur_fiche.projet_details_des_heures_modale.fermer')</button>
			</div>
		</div>
	</div>
</div>