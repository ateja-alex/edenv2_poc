@extends('eden::templates.template')

@section('title') {{traduction('module_sur_fiche.fiche.generique.inexistant')}} @stop

@section('content')

    <div class="content-wrapper" >
        <div id="base-content" class="container-fluid css_conteneur_fiche">

            {{-- Fil d'arianne --}}
            @include('eden::includes.fil_ariane', ['fil_ariane' => $fil_ariane])


            <!-- l'élément n'existe pas -->
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-danger text-center" role="alert">@traduction('module_sur_fiche.fiche.generique.inexistant')</div>
                </div>
            </div>


        </div>
    </div>

@endsection