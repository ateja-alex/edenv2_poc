@extends('eden::templates.template')

@section('title') Fiches @stop

@section('content')
	
	
	<div class="content-wrapper" >
		<div id="base-content" class="container-fluid">

			@include('eden::includes.fil_ariane', ['fil_ariane' => array(
				array('route' => 'parametrage.index', 'nom' => 'Paramétrage'),
				array('nom' => 'Fiches')
			)])

			<div class="row">
				<div class="col-md-12">
					<div class="card mb-3">
						<div class="card-header">
							<h4>
								Fiches
							</h4>
						</div>
						<div class="card-body css_parametrage_menu">
							@foreach($fiches as $fiche)
								<div class="row">
									<div class="col-md-12">
										<a href="{{ route('parametrage.'.($extranet ? 'extranet' : '').'.fiche.index', [$fiche->type_element]) }}">{{ $fiche->element }}</a>
									</div>
								</div>
							@endforeach
						</div>
					</div>
				</div>
				
			</div>
		</div>
	</div>

	

@endsection



