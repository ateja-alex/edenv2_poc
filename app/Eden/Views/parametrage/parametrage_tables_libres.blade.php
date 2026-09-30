@extends('eden::templates.template')

@section('title') Paramétres @stop

@section('styles')

<style type="text/css">
	.zoom:hover .fa {
		font-size: 25px !important;
	}
	.zoom:hover p {
		font-size: 15px;
	}
	.zoom:hover {
		
		cursor: pointer;
		padding-top: 18px;
		padding-bottom: 17px;
	}
	.zoom {
		
		border: 1px solid #7F7D7B;
		width: 80%;
		padding: 20px;
	}
</style>
@stop

@section('content')

<div class="content-wrapper" >
	<div id="base-content" class="container-fluid">

		@include('eden::includes.fil_ariane', ['fil_ariane' => array(
			array('route' => 'parametrage.index', 'nom' => 'Paramétrage'),
			array('nom' => 'Elements paramétrables')
		)])

		<div class="row">
			<div class="col-md-12">
				<div class="card mb-3">
					<div class="card-header">
						<h4>
							Paramètres
						</h4>
					</div>
					<div class="card-body">
						@php
						$categorie_precedent = "";
						@endphp
						{{-- On boucle sur les tables libres pour les mettre en forme --}}
						@foreach($tables_libres as $table_libre)

						{{-- On vérifie si c'est une nouvelle catégorie --}}
						@if($categorie_precedent != $table_libre->categorie)
						@if($categorie_precedent != "")
					</div>
					@endif 
					<div class="row">
						<div class="col-md-12">
							@if($table_libre->categorie == "element_primaire")
							<h4 class="mb-3">Éléments primaires</h4>
							@elseif($table_libre->categorie == "documents_de_ventes")
							<h4 class="mb-3">Documents de ventes</h4>
							@else
							<h4 class="mb-3">Documents d'achats</h4>
							@endif
						</div>
					</div>
					<div class="row">

						@endif
						<div class="col-md-3 mb-3">
							<a href="{{ route('parametrage.table_libre.zoom', $table_libre->type_element) }}">
								<div class="css_block_acces_module_parametrage">
									@if($table_libre->categorie == "element_primaire")
									<img src="{{ asset('eden/images/pictos/gears.png') }}" alt="">
									@elseif($table_libre->categorie == "documents_de_ventes")
									<img src="{{ asset('eden/images/pictos/documents_vente.png') }}" alt="">
									@else
									<img src="{{ asset('eden/images/pictos/documents_achats.png') }}" alt="">
									@endif
									<span>
										{!! $table_libre->element_pluriel !!}
									</span>
								</div>
							</a>
						</div>

						@php 
						$categorie_precedent = $table_libre->categorie;
						@endphp

						@endforeach
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
</div>


@endsection
