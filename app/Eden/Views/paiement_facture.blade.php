@extends('eden::authentification.template')

@section('title')
{{ ucfirst($plateforme) }} - paiement - ERP
@endsection

@section('link')
    <link href="{{ asset('eden/css/gestion-paiement.css') }}" rel="stylesheet">    
@endsection

@section('formulaire')
<form id="payment-form" class="form-horizontal" method="POST" action="{{ URL::route('paiement_facture_post', array('plateforme' => $plateforme, 'token' => $token, 'facture_id' => $facture_id)) }}">
	{{ csrf_field() }}

	<h3 class="text-center">@traduction('interface.paiement_facture.titre_formulaire')</h3> <br>
	
	@if($errors->count() > 0)
		<div class="alert alert-danger">{{ $errors->first() }}</div>
	@endif
	@if(session()->has('ok'))
		<div class="alert alert-success">{{ session('ok') }}</div>
	@endif

	<div class="form-group">
		<div class="row">
			<label class="col-md-4 control-label">@traduction('interface.paiement_facture.titre_informations_paiement.facture')</label>
			<a class="col-md-6 css-gestion-paiement" href="{{ route('paiement_afficher_pdf', array('facture_id' => $facture->id, 'token' => $token, 'plateforme' => $plateforme)) }}">{{ $facture->reference_document }}</a>			
		</div>
		<div class="row">
			<label class="col-md-4 control-label">@traduction('interface.paiement_facture.titre_informations_paiement.montant')</label>
			<span class="col-md-6 css-gestion-paiement">{{ montant($facture->solde_document_ttc) }}&nbsp;{!! maquette('devise_application_symbole') !!}</span>
		</div>
		@if($plateforme != "payline")
			<div class="site-payment-addCard">
				<div class="row">
					<label class="col-md-4  control-label" for="ccard-holder">@traduction('interface.paiement_facture.champs.titulaire_carte')</label>
					<div class="col-md-6">
						<input type="text" name="ccard-holder" id="ccard-holder" class="form-control input-myaccount" :placeholder="traduction('interface.paiement_facture.placeholder.titulaire_carte')">
					</div>			
				</div>			
				@include('eden::formulaires.include.mise_a_jour_carte_' . $plateforme)
			</div>
		@endif
	</div>		

	<div class="form-group">
		<div class="col-md-6 col-md-offset-4">
			
			@if($plateforme == "payline")
				<a class="btn btn-primary btn-lrdl" href="{{ $redirect_url }}" style="background-color: {{ maquette('background_menus') }}; border-color: {{ maquette('background_sous_menus') }}; width: 100%">
					@traduction('interface.modales.payer')
				</a>
			@else
				<button type="submit" class="btn btn-primary btn-lrdl" style="background-color: {{ maquette('background_menus') }}; border-color: {{ maquette('background_sous_menus') }}; width: 100%">
					@traduction('interface.modales.valider')
				</button>
			@endif
		</div>
	</div>
</form>
@endsection


@section('scripts')
	@if($plateforme == "stripe")
		<script src="https://js.stripe.com/v3/"></script>
		<script type="text/javascript">
			var stripe = Stripe("{{ service('stripe')->cle_publique() }}");
			
			var elements = stripe.elements();

			var style = {
				base: {

					color: '#32325d',
					lineHeight: '18px',
					fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
					fontSmoothing: 'antialiased',
					fontSize: '16px',
					'::placeholder': {

						color: '#aab7c4'
					}
				},
				invalid: {

					color: '#fa755a',
					iconColor: '#fa755a'
				}
			};

			// Create an instance of the card Element.
			var card = elements.create('card', {style: style});

			// Add an instance of the card Element into the `card-element` <div>.
			card.mount('#card-element');

			// Handle real-time validation errors from the card Element.
			card.addEventListener('change', function(event) {

				var displayError = document.getElementById('card-errors');
				if (event.error) {
					displayError.textContent = event.error.message;
				}
				else {
					displayError.textContent = '';
				}
			});

			// Handle form submission.
			var form = document.getElementById('payment-form');

			form.addEventListener('submit', function(event) {

				event.preventDefault();
				event.stopPropagation();

				stripe.handleCardPayment("{{ $payment_intent->client_secret }}", card, {
					payment_method_data: {
						billing_details: {name: 'Frederic Bry'}
					}
				}).then(function(result) {
					
					// console.log('result', result);
					
					if (result.error) {

						// chargement(false);
						// Inform the user if there was an error.
						var errorElement = document.getElementById('card-errors');
						errorElement.textContent = result.error.message;
					} else {

						$.post({

							url: '{{ URL::to("eden/paiement_facture/stripe/".$facture_id."/".$token) }}',
							dataType: "json",
							data: {
								
								
							}
						}).done(function() {

							toastr.success("Merci, nous avons bien enregistré votre paiement !");
						});
					}
					
				});


				
			});
		</script>
	@endif
@endsection
						