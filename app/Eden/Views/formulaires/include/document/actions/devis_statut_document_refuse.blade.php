<span :title="traduction('document.actions.devis_statut_document_refuse.refuser')" data-toggle="tooltip">
	<span class="css_action_icon primaire fa fa-fw fa-times" data-toggle="modal" data-target="#changement_statut_devis"></span>
</span>
	
@push('modales')
	<!-- Modal annuler devis -->
	<div class="modal fade" id="changement_statut_devis" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLabel">@traduction('document.actions.devis_statut_document_refuse.statut')</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					
					
					<div class="row">
						<div class="col-sm-5" style="cursor:pointer;justify-content: center;height:100px;text-align: center;display:flex; flex-direction:column; align-items:center;background-color: #eeeeee;margin-left:50px;padding:5px 5px 5px 5px">
							<a href="{{ route('document.devis.refuser', [($management->est_une_vente() ? 'vente' : 'achat'),$management->modele->id]) }}" onclick="loading(true);">
								<span class="fa fa-fw fa-times" style="font-size: 50px;" :title="traduction('document.actions.devis_statut_document_refuse.refuser')" data-toggle="tooltip"></span><br/>
								@traduction('document.actions.devis_statut_document_refuse.devis_refuse')
							</a>
						</div>
						<div class="col-sm-5" style="cursor:pointer;justify-content: center;height:100px;text-align: center;display:flex; flex-direction:column; align-items:center;background-color: #eeeeee;margin-left:50px;padding:5px 5px 5px 5px">
							<a href="{{ route('document.devis.annuler', [($management->est_une_vente() ? 'vente' : 'achat'),$management->modele->id]) }}" onclick="loading(true);">
								<span class="fab fa-creative-commons-nc-eu" style="font-size: 50px;" :title="traduction('document.actions.devis_statut_document_refuse.annuler')" data-toggle="tooltip"></span><br/>
								@traduction('document.actions.devis_statut_document_refuse.devis_annule')
							</a>
						</div>
					</div>
						
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">@traduction('interface.modales.fermer')</button>
				</div>
			</div>
		</div>
	</div>
@endpush


