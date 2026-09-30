@extends('eden::templates.template')

@section('title') Encaissement CB @endsection

@section('content')

   	<div class="content-wrapper" >
		<div  id="base-content" class="container-fluid">
			
					
			<div class="row">
				@if(isset($errors) && !empty($errors->all()))
					<br/>
					<br/>
					<div class="col-md-12">
						@foreach($errors->all() as $message)
							<div class="alert alert-danger">{{ $message }}</div>
						@endforeach
					</div>
					<br/>
					<br/>
					<br/>
				@endif
				<div class="col-md-12">
					<div class="card mb-3">
						<div class="card-header">
							<h4>
								@traduction('interface.encaissement_cb.titre')
							</h4>
						</div>
						<div class="card-body">
							<form action="{{ route('encaissement_cb.encaisse') }}" method="post" class="css_form">
								<div class="row">
									<div class="col-md-6">@traduction('champs_libres.paiement.client_id.nom')</div>
									<div class="col-md-6">{!! management('facture_vente')->champ('client_id')->cree() !!}</div>
								</div>
								<div class="row">
									<div class="col-md-6">@traduction('champs_libres.paiement.montant.nom')</div>
									<div class="col-md-6"><input type="text" name="montant" /></div>
								</div>
								<div class="row">
									<div class="col-md-6">@traduction('interface.encaissement_cb.date_signature_bail')</div>
									<div class="col-md-6"><input type="text" name="date_signature_bail" class="js_datepicker" /></div>
								</div>
								<div class="row">
									<div class="col-md-6">@traduction('interface.encaissement_cb.date_debut_bail')</div>
									<div class="col-md-6"><input type="text" name="date_debut_bail" class="js_datepicker" /></div>
								</div>
								<div class="row">
									<div class="col-md-6">@traduction('interface.encaissement_cb.adresse_bureaux')</div>
									<div class="col-md-6"><input type="text" name="adresse_bureaux" /></div>
								</div>
								<div class="row">
									<div class="col-md-6">@traduction('interface.encaissement_cb.debiter_client')</div>
									<div class="col-md-6">
										<input type="checkbox" name="debiter_client" value="1" checked />
										@traduction('interface.encaissement_cb.debiter_client.explication')
									</div>
								</div>
								<div class="row">
									<div class="col-md-6"></div>
									<div class="col-md-6">
										<input type="submit" class="btn btn-primary" :value="traduction('interface.encaissement_cb.debiter_client.bouton_encaisser')" />
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

@endsection

@push('donnees_pour_vuejs_data')
	document: {},

@endpush
