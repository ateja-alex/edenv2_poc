@extends('eden::templates.template')

@section('title') Stripe  @endsection

@section('content')
	

   	<div class="content-wrapper" >
	
		<div  id="base-content" class="container-fluid">

			test ici
			
			<label style="width: 500px;">Card
			  <div id="card-element"></div>
			</label>
			
			<div class="btn btn-success" onClick="enregistre_paiement_intent_stripe()">OK</div>
			<div class="btn btn-success" onClick="envoie_paiement_stripe()">Test paiement</div>
		</div>
	</div>
	

@endsection

@push('scripts')

	<script src="https://js.stripe.com/v3/"></script>
	<script>
		
		var stripe = Stripe("{{ service('stripe')->cle_publique() }}");
		
		var elements = stripe.elements({
		  clientSecret: '{{ $payment_intent->client_secret }}',
		});
		
		elements.update({locale: 'fr'});
		
		// var paymentElement = elements.create('payment');
		var card = elements.create('card');
		
		card.mount('#card-element');
		
		var customer = '{{ $client->id }}';
		var payment_method = false;
		
		  
		function enregistre_paiement_intent_stripe() {
			
			stripe.handleCardPayment("{{ $payment_intent->client_secret }}", card, {
					payment_method_data: {
						billing_details: {name: 'Frederic Bry'}
					}
				}
			).then(function(result) {
				
				// console.log('result', result);
				
				payment_method = result.paymentIntent.payment_method;
				
				$.get({

                    url: "{{ URL::to("eden/stripe/test_modification_client/".$client->id) }}/"+result.paymentIntent.payment_method,
                    dataType: "json"
                }).done(function() {

                    // console.log('ok !');
                });
			});

			/*
			stripe.createPaymentMethod({
				type: 'card',
				card: card,
				billing_details: {
					name: 'Jenny Rosen',
				},
			}).then(function(result) {
				
				// Handle result.error or result.paymentMethod
				console.log(result);
			});
			*/
		}
		
		function envoie_paiement_stripe() {
			
			$.get({

				url: "{{ URL::to("eden/stripe/test_paiement_client/".$client->id) }}/"+payment_method,
				dataType: "json"
			}).done(function(result) {

				// console.log(result);
			});
		}
	</script>
@endpush