<!-- Modal Devis par mail -->
<div class="modal fade" id="modal_devis_par_mail" tabindex="-1" role="dialog" aria-labelledby="modal_devis_par_mailLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal_devis_par_mailLabel">
					Indiquez votre adresse mail pour recevoir directement votre devis
				</h4>
			</div>
			<div class="modal-body">
				<form action="{{ route('devis.recevoir-par-mail') }}" class="css_form_modal_devis_par_mail" method="post">
					<div>
						@if(session()->get('utilisateur_eden_ecommerce') === null)
							<input type="text" class="css_input_mail_devis_par_mail" name="adresse_email_pour_envoyer_document" placeholder="moi@domaine.com" />
						@else
							@if(empty(modele('client', session()->get('utilisateur_eden_ecommerce'))->adresse_email)) 
								<input type="text" class="css_input_mail_devis_par_mail" name="adresse_email_pour_envoyer_document" placeholder="moi@domaine.com" />
							@else
								L'email sera envoyé à : {{ modele('client', session()->get('utilisateur_eden_ecommerce'))->adresse_email }}<br/><br/>
							@endif
						@endif
						<button type="submit" class="btn btn-rouge-amc">Recevoir le devis par email</button>
					</div>
				</form>
			</div>
			<div class="modal-footer"></div>
		</div>
	</div>
</div>