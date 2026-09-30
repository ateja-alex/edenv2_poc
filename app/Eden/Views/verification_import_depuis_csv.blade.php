
@extends('eden::templates.template')

@section('title') Import sur mesure @stop


@section('content')

	<div class="content-wrapper" >
		<div id="base-content" class="container-fluid">

			<div class="row">
				<div class="col-md-12">
					<div class="card mb-3">
						<div class="card-header">
							<h4>
								@traduction('interface.verification_import_depuis_csv.titre')
							</h4>
						</div>
						<div class="card-body css_form css_parametrage_formulaire" id="sortable">
							<div class="row">
								<div class="col-md-12" >
									@if(!isset($verification))
										<h2 style="text-align: center;"><small><i class="fas fa-check-circle" style="margin-right: 2%;"></i>@traduction('interface.verification_import_depuis_csv.verification_reussi')</small></h2>
										<p style="margin-top: 3%;text-align: center;">@traduction('interface.verification_import_depuis_csv.verification_reussi_detail')</p>
									@else
										<h2 style="margin-left: 42%;"><small><i style="margin-right: 2%;" class="fas fa-exclamation-triangle"></i>@traduction('interface.verification_import_depuis_csv.verification_erreur')</small></h2>
										<p style="margin-top: 3%;margin-bottom: 2%;text-align: center;">@traduction('interface.verification_import_depuis_csv.verification_erreur_detail')</p>
										<hr style="width: 90%;margin-left: 5%;margin-bottom: 3%;">
										<p style="margin-left: 15%;">- {{ $nombre_ligne_succes }} @traduction('interface.verification_import_depuis_csv.verification_erreur_detail_lignes_reussies')</p>
										<p style="margin-left: 15%;">-  @traduction('interface.verification_import_depuis_csv.verification_erreur_detail_lignes_erreurs') : </p>
										<ul style="margin-left: 16%;">
											@foreach($erreurs_rencontrees as $erreur)
												<li>. {{ $erreur }}</li>
											@endforeach
										</ul>
									@endif
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

@endsection

