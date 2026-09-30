@extends('eden::templates.template')

@section('title') Synchronisation Google Drive @stop

@section('content')
	
	
	<div class="content-wrapper" >
		<div id="base-content" class="container-fluid">

			@include('eden::includes.fil_ariane', ['fil_ariane' => array(
					array('nom' => 'Paramétrage'),
					array('nom' => 'Synchro GDrive')
				)])

			<div class="row">
				<div class="col-md-12">
					<div class="card mb-3">
						<div class="card-header d-flex align-items-center">
							<h4>Choix du fournisseur pour la bibliothèque et les pièces jointes <span class="badge badge-success" id="badge_provider" style="display: none">Enregistré !</span></h4>
						</div>
						<div class="card-body css_parametrage_menu">

							<select id="racine_provider" class="form-control" @change="enregistre_provider()">
								<option value="">Local</option>
								<option value="gdrive" {{ parametre('type_synchro_bibliotheque') == 'gdrive' ? 'selected="selected"' : '' }}>Google Drive</option>
							</select>

						</div>
					</div>

					<div class="card mb-3">
						<div class="card-header d-flex align-items-center">
							<h4>
								Synchronisation Google Drive de la Bibliothèque
							</h4>
						</div>
						<div class="card-body css_parametrage_menu">
							
							@php $i=0; @endphp

							@if(admin())

							<h5 class="card-title">{{++$i}} - Composer</h5>

								<div class="alert alert-info">composer require google/apiclient 2.0</div>

							<h5 class="card-title">{{++$i}} - Création de l'application</h5>

								@if(file_exists(storage_path('app/credentials.json')))

									<div class="alert alert-success">
										<span class="fa fa-check"></span>
										L'application est configurée
									</div>

								@else

									<a href="https://developers.google.com/drive/api/v3/quickstart/php" target="_blank" class="btn btn-primary">
										<span class="badge badge-primary"><span class="fa fa-angle-double-right"></span></span>
										developers.google.com/drive/api/v3/quickstart/php
									</a>

									<div class="alert alert-primary">
										Placez le fichier JSON dans le répertoire storage/app
									</div>

								@endif

							@endif

							<h5 class="card-title">{{++$i}} - Authentification à l'application</h5>

								@if(file_exists(storage_path('app/token-google-drive.json')))

									<div class="alert alert-success">
										<span class="fa fa-check"></span>
										La connexion est opérationnelle
									</div>

								@else

									<a href="{{ $client->createAuthUrl() }}" target="_blank" class="btn btn-primary">
										<span class="badge badge-primary"><span class="fa fa-angle-double-right"></span></span>
										Authentification Google
									</a>

								@endif


							<h5>{{++$i}} - Choix du dossier parent <span class="badge badge-success" id="badge_racine" style="display: none">Enregistré !</span></h5>

							<select id="racine_gdrive" class="form-control" @change="enregistre_racine()">
								<option value="">Racine Google Drive</option>
								@foreach(service('google')->recupere_fichiers_drive('', true) as $dossier)
									<option value="{{ $dossier->id }}" {{ $parametre == $dossier->id ? 'selected="selected"' : '' }}>{{ $dossier->nom }}</option>
								@endforeach
							</select>

						</div>
					</div>
				</div>
				
			</div>
		</div>
	</div>

	

@endsection

@push('donnees_pour_vuejs_methods')

	enregistre_racine: function() {

        // on enregistre les infos du champ libre
        $.ajax({

	        url: "{{ route('eden.parametrage.parametre_enregistrer', ['nom_parametre' => 'synchro_gdrive_dossier_racine']) }}",
  			method: 'post',
  			data: {valeur:$('#racine_gdrive').val()},
	        dataType: "json",

        }).done(function(donnees) {

	        //console.log(donnees)
	        $('#badge_racine').show('fast').delay(3000).hide('fast');
        });
	},

	enregistre_provider: function() {

        // on enregistre les infos du champ libre
        $.ajax({

	        url: "{{ route('eden.parametrage.parametre_enregistrer', ['nom_parametre' => 'type_synchro_bibliotheque']) }}",
  			method: 'post',
  			data: {valeur:$('#racine_provider').val()},
	        dataType: "json",

        }).done(function(donnees) {

	        //console.log(donnees)
	        $('#badge_provider').show('fast').delay(3000).hide('fast');
        });
	},

@endpush