@extends('eden::templates.template')

@section('title') {{traduction('interface.accueil.titre_accueil')}} @endsection

@section('content')

   	<div class="content-wrapper" >
		<div  id="base-content" class="container-fluid">

			@if(session()->has('redirection_post_login'))
				<div class="alert alert-warning" style="line-height: 26px;">
					@traduction('interface.accueil.redirection')

					<a href="{!! session()->get('redirection_post_login') !!}" class="btn btn-sm btn-dark float-right" style="padding: .25rem .5rem;font-size: .875rem;">@traduction('interface.accueil.continuer')</a>
				</div>
				@php
					session()->forget('redirection_post_login');
				@endphp
			@endif

			<div class="row">
				<div class="col-md-12">
					<div class="card mb-3">
						<div class="card-header">
							<h4>
								@traduction('interface.accueil.bienvenue')
							</h4>
						</div>
					</div>
				</div>
			</div>

			@include('eden::listes.includes.liste', [
				'type_element' => 'tache',
				'id_liste' => \App\Eden\Models\Liste_libre::where('type_element','tache')
					->where('id_rapport','tache_tableau_de_bord')->first()->id,
				'modele_par_defaut' => modele_par_defaut('tache'),
			])

		</div>
	</div>

@endsection
