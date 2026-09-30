@extends('eden::templates.template')

@section('title') Maintenance @stop

@section('content')

	<div class="content-wrapper" >
		<div id="base-content" class="container-fluid">
			@include('eden::includes.fil_ariane', ['fil_ariane' => array(
					array('route' => 'parametrage.index', 'nom' => 'Paramétrage'),
					array('nom' => 'Maintenance')
				)])
			<div class="row">

				<div class="col-md-12">
					<div class="card mb-3">
						<div class="card-header">
							<h4>
								Maintenance
							</h4>
						</div>
						<div class="card-body">

							<div class="row">

								<div class="col-md-3 mb-3">
									<a href="{{ route('maintenance.migrations') }}">
										<div class="css_block_acces_module_parametrage">
											<img src="{{ asset('eden/images/pictos/gears.png') }}" alt="">
											<span>Migrations</span>
										</div>
									</a>
								</div>

								<div class="col-md-3 mb-3">
									<a href="{{ route('maintenance.cache') }}">
										<div class="css_block_acces_module_parametrage">
											<img src="{{ asset('eden/images/pictos/gears.png') }}" alt="">
											<span>Contrôle du cache</span>
										</div>
									</a>
								</div>

								<div class="col-md-3 mb-3">
									<a href="{{ route('maintenance.colonnes_null') }}">
										<div class="css_block_acces_module_parametrage">
											<img src="{{ asset('eden/images/pictos/gears.png') }}" alt="">
											<span>Passer toutes les colonnes en NULL</span>
										</div>
									</a>
								</div>
							</div>

						</div>

					</div>
				</div>

				<div class="col-md-12">
					<div class="card mb-3">
						<div class="card-header">
							<h4>
								Génération des composants
							</h4>
						</div>
						<div class="card-body">

							<div class="row">
								<div class="col-md-3 mb-3">
									<a href="{{ route('maintenance.generation_fichier.composants.index') }}">
										<div class="css_block_acces_module_parametrage">
											<img src="{{ asset('eden/images/pictos/gears.png') }}" alt="">
											<span>Mettre à jour tous les composants</span>
										</div>
									</a>
								</div>

								<div class="col-md-3 mb-3">
									<a href="{{ route('maintenance.generation_fichier.menus') }}">
										<div class="css_block_acces_module_parametrage">
											<img src="{{ asset('eden/images/pictos/gears.png') }}" alt="">
											<span>Mettre à jour les menus</span>
										</div>
									</a>
								</div>

								<div class="col-md-3 mb-3">
									<a href="{{ route('maintenance.generation_fichier.css') }}">
										<div class="css_block_acces_module_parametrage">
											<img src="{{ asset('eden/images/pictos/gears.png') }}" alt="">
											<span>Mettre à jour le css</span>
										</div>
									</a>
								</div>

								<div class="col-md-3 mb-3">
									<a href="{{ route('maintenance.generation_fichier.composants.module') }}">
										<div class="css_block_acces_module_parametrage">
											<img src="{{ asset('eden/images/pictos/gears.png') }}" alt="">
											<span>Mettre à jour les modules</span>
										</div>
									</a>
								</div>

								<div class="col-md-3 mb-3">
									<a href="{{ route('maintenance.generation_fichier.composants.liste') }}">
										<div class="css_block_acces_module_parametrage">
											<img src="{{ asset('eden/images/pictos/gears.png') }}" alt="">
											<span>Mettre à jour les listes</span>
										</div>
									</a>
								</div>

								<div class="col-md-3 mb-3">
									<a href="{{ route('maintenance.generation_fichier.valeurs_champs_listes') }}">
										<div class="css_block_acces_module_parametrage">
											<img src="{{ asset('eden/images/pictos/gears.png') }}" alt="">
											<span>Mettre à jour les valeurs des listes libres et des listes formatées</span>
										</div>
									</a>
								</div>

							</div>

						</div>

					</div>
				</div>

			</div>
		</div>
	</div>

@endsection


