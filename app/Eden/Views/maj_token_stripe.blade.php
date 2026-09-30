@extends('eden::authentification.template')

@section('title')
Stripe - ERP
@endsection

@section('link')
    <link href="{{ asset('eden/css/stripe.css') }}" rel="stylesheet">    
@endsection

@section('formulaire')
<form id="payment-form" class="form-horizontal" method="POST" action="{{ URL::route('maj_token_stripe', array('token' => $token, 'client_id' => $client_id)) }}">
	{{ csrf_field() }}

	<h3 class="text-center">@traduction('interface.maj_token_stripe.titre_formulaire')</h3> <br>
	
	@if($errors->count() > 0)
		<div class="alert alert-danger">{{ $errors->first() }}</div>
	@endif
	@if(session()->has('ok'))
		<div class="alert alert-success">{{ session('ok') }}</div>
	@endif

	<div class="form-group">
		<div class="row">
			<label class="col-md-6" style="text-align: right;"><b>@traduction('tables_libres.client.element')</b></label>
			<span class="col-md-6">{{ $client->nom }}</span>
		</div>
		<div class="row">
			<label class="col-md-6" style="text-align: right;"><b>@traduction('interface.maj_token_stripe.champs.titulaire_carte')</b></label>
			<span class="col-md-5">
				<div class="site-payment-addCard">
					<input type="text" name="ccard-holder" id="ccard-holder" class="form-control input-myaccount" placeholder="ex : Bernard Toulemond">
					<br/>
					<div id="card-element"></div>
					<div id="card-errors" role="alert"></div>
				</div>
			</span>
		</div>
		
	</div>

		

	<div class="form-group">
		<div class="col-md-6 col-md-offset-4">
			<button type="submit" class="btn btn-primary btn-lrdl" style="background-color: {{ maquette('background_menus') }}; border-color: {{ maquette('background_sous_menus') }}; width: 100%">
				@traduction('interface.modales.valider')
			</button>
		</div>
	</div>
</form>
@endsection


@section('scripts')
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

		stripe.confirmCardSetup("{{ $payment_intent->client_secret }}", {
			payment_method: {
				card: card,
				billing_details: {},
			},
		}).then(function(result) {
			
			//console.log('result', result);
			
			if (result.error) {

			  	// chargement(false);
              	// Inform the user if there was an error.
              	var errorElement = document.getElementById('card-errors');
              	errorElement.textContent = result.error.message;
            } else {

                // Send the token to your server.
                var payment_method = result.setupIntent.payment_method;
				
				$.get({

					url: '{{ URL::to("eden/stripe/modification_client/".$client->portefeuille_stripe) }}/'+payment_method,
					dataType: "json"
				}).done(function() {

					toastr.success("Merci, nous avons bien enregistré vos coordonnées bancaires !");
				});
            }
			
		});


        
    });

</script>
@endsection
						