@extends('eden::authentification.template')

@section('title')
{{ ucfirst($plateforme) }}- ERP
@endsection

@section('link')
    <link href="{{ asset('eden/css/gestion-paiement.css') }}" rel="stylesheet">    
@endsection

@section('formulaire')

	<h3 class="text-center">@traduction('interface.maj_paiement_payzen.titre_formulaire')</h3> <br>
	
	@if($errors->count() > 0)
		<div class="alert alert-danger">{!! $errors->first() !!}</div>
	@endif
	@if(session()->has('ok'))
		<div class="alert alert-success">{!! session('ok') !!}</div>
	@endif

	<div class="form-group">
		<div class="row">
			<label class="col-md-4 control-label">@traduction('tables_libres.client.element')</label>
			<span class="col-md-6 css-gestion-paiement">@if(!empty($client->denomination)) {{ $client->denomination }} @else {{ $client->prenom }} {{ $client->nom }} @endif</span>
		</div>
		<div class="site-payment-addCard">
			
			<div class="row">
				<div class="form-row col-md-12">
					<form method="POST" action="https://secure.payzen.eu/vads-payment/">

						@foreach($params as $key => $value)

							<input type="hidden" name="{{ $key }}" value="{{ $value }}" />
						@endforeach					

						<div class="row">
							<div class="form-row col-md-12">
								<div class="row">
									<div class="col-md-6 col-md-offset-4">
										<input type="submit" class="btn btn-primary btn-lrdl" name="payer" :value="traduction('interface.maj_token_stripe.bouton_renseigner_coordonnees')" style="background-color: rgb(245, 184, 0); border-color: rgb(251, 212, 96); width: 100%;" />
									</div>
								</div>
							</div>
						</div>
						
					</form>
					
				</div>
			</div>

		</div>
	</div>
</form>
@endsection
						