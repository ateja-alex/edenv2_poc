@extends('eden::templates.template')

@section('title') Configuration fonctionnalités maquette @stop

@section('content')

	<div class="content-wrapper" >
		<div id="base-content" class="container-fluid">
			<div class="row">
				<div class="col-md-12">
					<div class="card mb-3">
						<div class="card-header">
							<h4>
								Parametres fonctionnalités
								<span class="css_ajouter_element css__lien" @click="enregistrer_fonctionnalites"><i class="fa fa-fw fa-plus-square"></i> Enregistrer</span>
							</h4>
						</div>
						<div class="card-body">
							<div class="table-responsive">
								<table class="table table-bordered table-hover css_form" width="100%" cellspacing="0">
									<thead>
										<tr>
											<th scope="col">Nom parametre</th>
											<th scope="col">Valeur</th>
										</tr>
									</thead>
									<tbody>
										
										@include('eden::parametrage.include.fonctionnalites_specifiques_parametres')
										
									</tbody>
								</table>
                            </div>
                        </div>
					</div>
				</div>
			</div>
		</div>
	</div>

@endsection

@push('donnees_pour_vuejs_data')

	fonctionnalites_specifiques: {!! collect($fonctionnalites_specifiques) !!},
@endpush

<script>
@push('donnees_pour_vuejs_methods')

	enregistrer_fonctionnalites: function() {
		
		loading(true);
		
		var fonctionnalites_specifiques = this.fonctionnalites_specifiques;
		// console.log(fonctionnalites_specifiques);
		$.post({

			url: '{{ route('parametrage.fonctionnalites.specifiques.enregistrer') }}',
			dataType:'json',
			data: {fonctionnalites_specifiques : fonctionnalites_specifiques},
			success: function(data) {

				loading(false);

			}
		});
	},
@endpush
</script>
