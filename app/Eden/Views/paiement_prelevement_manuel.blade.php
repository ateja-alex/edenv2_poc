@extends('eden::templates.template')

@section('content')
	
<div class="content-wrapper" >
		<div id="base-content" class="container-fluid">

			<div class="row">
				<div class="col-md-12">
             
					<div class="card mb-3">
						<div class="card-header">
							<h4>
								@traduction('interface.paiement_prelevement_manuel.texte',null,false, [montant($montant), maquette('devise_application_symbole'), $client->denomination])
							</h4>
						</div>
						<div class="card-body" id="">

							{!! $chaine !!}
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

@endsection

