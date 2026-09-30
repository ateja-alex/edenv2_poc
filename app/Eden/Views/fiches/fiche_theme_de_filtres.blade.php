@extends('eden::fiche')

@section('title') {{ $management_element->affiche() }} @stop

@section('options_fil_ariane')
					
	<!-- supprimer -->
	<i class="css_action_icon primaire fa fa-fw fa-trash" @click="suppression_fiche('{{$management_element->_type_element}}','{{ $management_element->modele->id }}')" title="aa{{ traduction('interface.modales.supprimer') }}" data-toggle="tooltip">
	</i>
				
@endsection

@section('content')

	<div class="content-wrapper" >
	    <div id="base-content" class="container-fluid css_conteneur_fiche">
		
			{{-- Fil d'arianne --}}
			@include('eden::includes.fil_ariane', ['fil_ariane' => $fil_ariane])

			<!-- l'élément a été supprimé -->
			@if($management_element->modele->inactif == 1)
				<div class="row">
					<div class="col-md-12">
						<div class="alert alert-danger text-center" role="alert">@traduction('module_sur_fiche.fiche.element.supprime')</div>
					</div>
				</div>
			@endif
			
			<!-- édition de l'élément -->
			<div class="row">
				<div class="col-md-12">
					<formulaire-fiche :route="'{{ route('base_eden.element.enregistrer', [$management_element->_type_element, $management_element->modele->id]) }}'" :type_element="'{{$management_element->_type_element}}'" :element_id='{{ $management_element->modele->id }}'>
						<template slot="titre"> {!! $management_element->affiche() !!} </template>
					</formulaire-fiche>
				</div>
			</div>
			
			<!-- Filtres -->
			<div class="row">
				<div class="col-md-6">

					@include('eden::fiches.include.theme_de_filtres.filtres')
				</div>
			</div>
		</div>
	</div>
	
@endsection

@section('donnees_pour_vuejs_data')
	theme_de_filtres: {!! $theme_de_filtres !!},
@endsection



