
@extends('eden::templates.template')

@section('title') Débit CB @stop


@section('content')

	<div class="content-wrapper" >
		<div id="base-content" class="container-fluid">

			<div class="row">
				<div class="col-md-12">
					<div class="card mb-3">
						<div class="card-header">
							<h4>
								@traduction('interface.interface_paiement_factures_payline.titre')
							</h4>
						</div>
						<div class="card-body css_form css_parametrage_formulaire" id="sortable">
							<div class="row">
								<div class="col-md-12" >
									
									<h2 style="text-align: center;">
										<button class="btn btn-default"
												style="cursor: pointer" 
												onclick="if ( confirm('Voulez-vous lancer l\'encaissement des factures en attente de paiement ?') ) document.location='{{ route('paiement_factures_payline') }}';"
												v-html="traduction('interface.interface_paiement_factures_payline.bouton_encaissement')">
										</button>
									</h2>									
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

@endsection