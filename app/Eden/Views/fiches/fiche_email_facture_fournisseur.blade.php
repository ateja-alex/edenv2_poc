@extends('eden::fiche')

@section('title') {{ $management_element->affiche() }}  @stop

@section('content')

	<div class="content-wrapper" >
	    <div id="base-content" class="container-fluid css_conteneur_fiche">
		
			{{-- Fil d'arianne --}}
			@include('eden::includes.fil_ariane', ['fil_ariane' => $fil_ariane])

			<!-- l'élément a été supprimé -->
			@if($management_element->modele->inactif == 1)
				<div class="row">
					<div class="col-md-12">
						<div class="alert alert-danger text-center" role="alert">@traduction('module_sur_fiche.fiche.email_facture_fournisseur.supprime')</div>
					</div>
				</div>
			@endif
			
			<!-- Retour à la liste -->
			<h2 class="css_titre_page_eden">

				@include('eden::fiches.include.lien_retour_liste_type_element')
				@traduction('module_sur_fiche.fiche.email_facture_fournisseur.fiche') {{  table_libre($type_element)->element }}

				{{-- Supprimer --}}
				<span class="css_barre_titre_options d-flex">
					<a onclick="supprimer_email_facture_fournisseur()" :href="'{{ URL::to('eden/fiche') }}/'+type_element+'/'+element_id+'/supprimer'" class="css_barre_titre_option" title="{{traduction('module_sur_fiche.fiche.supprimer')}}" data-toggle="tooltip" style="
					width: 45px;
					height: 45px;
					justify-content: center;
					align-items: center;
					display: flex;">
						<i class="far fa-trash-alt"></i>
					</a>
				</span>
			</h2>
			<!-- édition de l'élément -->
			<div class="row">
				<div class="col-md-12">
					<formulaire-fiche :route="'{{ route('base_eden.element.enregistrer', [$management_element->_type_element, $management_element->modele->id]) }}'" :type_element="'{{$management_element->_type_element}}'" :element_id='{{ $management_element->modele->id }}'>
						<template slot="titre">{{ $management_element->affiche() }} </template>
					</formulaire-fiche>
				</div>
				
				@yield('blocs_fiche')
			</div>
			
			<!-- affichage de l'email -->
			<div class="card mb-3">
				<div class="card-header js_fermeture_bloc">
					<h4>{{ $email_facture_fournisseur->sujet }}, {{ traduction('module_sur_fiche.fiche.email_facture_fournisseur.le') }} {{ formate_date(traduction('interface.global.date_avec_minutes_et_secondes'), $email_facture_fournisseur->date) }}</h4>
				</div>
				<div class="card-body">
					<iframe src="{{ route('email.affichage', $email_facture_fournisseur->id) }}" width="100%" height="200px"></iframe>
					
				</div>
			</div>
			
			<!-- affichage des PJ -->
			<div class="row">
				<div class="col-md-12">
					@include('eden::fiches.include.email_facture_fournisseur.pieces_jointes')
				</div>
			</div>
		</div>
	</div>
	
@endsection

@section('donnees_pour_vuejs_data')
	{{ $management_element->_type_element }}: {!! ${$management_element->_type_element} !!},
	
@endsection

@push('scripts')

	<script>

		async function supprimer_email_facture_fournisseur(){
			if (await confirm_eden("{{ traduction('module_sur_fiche.fiche.entreprise.confirmation_suppression') }}"))
				return false;
		}
	</script>

@endpush