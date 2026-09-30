@extends('eden::fiche')

@section('title') {{ $management_element->affiche() }} @stop

@section('content')

	<div class="content-wrapper" >
	    <div id="base-content" class="container-fluid css_conteneur_fiche">

			{{-- Fil d'arianne --}}
			@include('eden::includes.fil_ariane', ['fil_ariane' => $fil_ariane])

                <!-- l'élément a été supprimé -->
                @if($management_element->modele->inactif == 1)
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-danger text-center" role="alert">@traduction('module_sur_fiche.fiche.generique.supprime')</div>
                        </div>
                    </div>
                @endif
                <!-- l'élément n'existe pas -->
                @if(!$management_element->existe())
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-danger text-center" role="alert">@traduction('module_sur_fiche.fiche.generique.inexistant')</div>
                        </div>
                    </div>
                @else

                    @include('eden::fiches.include.structure_fiche')

                @endif


            </div>
        </div>

    @endsection

    @push('donnees_pour_vuejs_data')

        {{ $management_element->_type_element }}: {!! ${$management_element->_type_element} !!},

    @endpush





