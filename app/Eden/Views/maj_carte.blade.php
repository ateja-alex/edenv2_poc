@extends('eden::authentification.template')

@section('title')
{{ ucfirst($plateforme) }}- ERP
@endsection

@section('link')
    <link href="{{ asset('eden/css/gestion-paiement.css') }}" rel="stylesheet">    
@endsection

@section('formulaire')
<form id="payment-form" class="form-horizontal" method="POST" action="{{ URL::route('maj_carte_post', array('plateforme'=> $plateforme, 'token' => $token, 'client_id' => $client_id)) }}">
	{{ csrf_field() }}

	<h3 class="text-center">@traduction('interface.maj_carte.titre_formulaire')</h3> <br>
	
	@if($errors->count() > 0)
		<div class="alert alert-danger">{!! $errors->first() !!}</div>
	@endif
	@if(session()->has('ok'))
		<div class="alert alert-success">{!! session('ok') !!}</div>
	@endif

	<div class="form-group">
		<div class="row">
			<label class="col-md-4 control-label" style="text-transform: capitalize">@traduction('tables_libres.client.element')</label>
			<span class="col-md-6 css-gestion-paiement">@if(!empty($client->denomination)) {{ $client->denomination }} @else {{ $client->prenom }} {{ $client->nom }} @endif</span>
		</div>
		<div class="site-payment-addCard">
			<div class="row">
				<label class="col-md-4  control-label" for="ccard-holder">@traduction('interface.maj_carte.champs.titulaire_carte')</label>
				<div class="col-md-6">
					<input type="text" name="ccard-holder" id="ccard-holder" class="form-control input-myaccount" :placeholder="traduction('interface.maj_carte.placeholder.titulaire_carte')">
				</div>
			</div>
			@include('eden::formulaires.include.mise_a_jour_carte_' . $plateforme)
		</div>
	</div>
	<div class="row">
		<div class="form-group">
			<div class="col-md-6 col-md-offset-4">
				<button type="submit" class="btn btn-primary btn-lrdl" style="background-color: {{ maquette('background_menus') }}; border-color: {{ maquette('background_sous_menus') }}; width: 100%">
					@traduction('interface.modales.valider')
				</button>
			</div>
		</div>
	</div>
</form>
@endsection


@section('scripts')
@if($plateforme == "stripe")
	<script src="https://js.stripe.com/v3/"></script>
	<script type="text/javascript">
		var stripe = Stripe('{{config("services.stripe.key")}}');
	</script>
	<script type="text/javascript" src="{{ url('eden/js/stripe.js?v=0.1')}}"></script>
@endif
@endsection
						