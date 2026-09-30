@extends('eden::authentification.template')

@section('title')
{{ ucfirst($plateforme) }}- ERP
@endsection

@section('link')
    <link href="{{ asset('eden/css/gestion-paiement.css') }}" rel="stylesheet">    
@endsection

@section('formulaire')
<form id="payment-form" class="form-horizontal" method="POST" action="{{ URL::route('maj_mandat_post', array('plateforme'=> $plateforme, 'token' => $token, 'client_id' => $client_id)) }}">
	{{ csrf_field() }}

	<h3 class="text-center">@traduction('interface.maj_mandat.titre_formulaire')</h3> <br>
	
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
				<div class="form-row col-md-12">
					<div class="row">
						<label class="col-md-4 control-label" for="ccard-num">@traduction('interface.maj_mandat.champs.iban')IBAN</label>
						<div class="col-md-6">
							<input type="text" name="iban" id="iban-num" class="form-control input-myaccount">
						</div>
					</div>
					
				</div>
			</div>

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
						