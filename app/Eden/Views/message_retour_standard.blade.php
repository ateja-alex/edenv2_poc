
@extends('eden::templates.template')

@section('title') {{ $titre }} @stop


@section('content')

	<div class="content-wrapper" >
		<div id="base-content" class="container-fluid">

			<div class="row">
				<div class="col-md-12">
					<div class="card mb-3">
						<div class="card-header">
							<h4>
								{{ $titre }}
							</h4>
						</div>
						<div class="card-body css_form css_parametrage_formulaire" id="sortable">
							<div class="row">
								<div class="col-md-12" >
									
										<h2 style="text-align: center;"><small><i class="fas fa-check-circle" style="margin-right: 2%;"></i>{{ $titre_texte }}</small></h2>
										<p style="margin-top: 3%;text-align: center;"> {!! $texte !!} </p>
									
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

@endsection