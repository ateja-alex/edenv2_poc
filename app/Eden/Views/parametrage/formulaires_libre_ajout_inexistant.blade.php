@extends('eden::templates.template')

@section('title') Ajout formulaire libre inexistant @endsection

@section('content')

   	<div class="content-wrapper" >
		<div  id="base-content" class="container-fluid">

			<div class="row">
				<div class="col-md-12">
					<div class="card mb-3">
						<div class="card-header">
							<h4>
								Ajout d'un formulaire
							</h4>
						</div>
						<div class="card-body">
							<h4 style="margin-left: 15%">Aucun formulaire n'existe pour ce type élement, souhaitez-vous en créer un ?</h4>
							<a href="{{ route("parametrage.formulaire.ajouter_volee", [$type_element,$type_formulaire]) }}"><button type="submit" style="margin-top: 3%;margin-left: 40%;" class="btn btn-primary css_btn_responsive">Enregistrer</button></a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

@endsection