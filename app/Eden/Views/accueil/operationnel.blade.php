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

			{!! $rapport_commande_vente !!}
			{!! $rapport_bl_vente !!}
			{!! $rapport_facture_vente !!}

		</div>
	</div>

@endsection
