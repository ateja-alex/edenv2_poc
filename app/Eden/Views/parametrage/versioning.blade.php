@extends('eden::templates.template')

@section('title') Versioning @stop


@section('content')

	<div class="content-wrapper" >
		<div id="base-content" class="container-fluid">

			@include('eden::includes.fil_ariane', ['fil_ariane' => array(
				array('route' => 'parametrage.index', 'nom' => 'Paramétrage'),
				array('nom' => 'Versioning')
			)])

			<div class="row">
				<div class="col-md-12">
					<div class="card mb-3">
						<div class="card-header d-flex align-items-center">
							<h4>
								Version actuelle : {{ array_key_first($versions) }}
							</h4>
							
						</div>
						<div class="card-body">
							@foreach($versions as $id_version => $tickets)
								<div class="css_form_ligne_titre">Version : {{ $id_version}} </div>
								
								
								@foreach($tickets as $id_ticket => $ticket)
									<div class="row">
										<div class="col-md-2"><b>Ticket #{{ $id_ticket }}</b></div>
										<div class="col-md-6" style="margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 15px;">
											<b>{{ $ticket['titre'] }}</b><br/>
											@if(!empty($ticket['description']))
											<div style="padding-left: 50px;">{{ $ticket['description'] }}</div>
											@endif
										</div>
									</div>
								@endforeach
							@endforeach
							
							<br/>
						</div>
					</div>
				</div>
			</div>

			
		</div>
	</div>

	

@endsection
