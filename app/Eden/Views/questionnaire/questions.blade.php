@extends('eden::authentification.template')

@section('title')
{!! traduction('interface.questionnaire.titre') !!}
@endsection

@push('styles')

    <style>

    .col-xs-12.col-md-8.col-md-offset-2{

        width : 100%;
        margin-left : unset;
        flex: unset;
        max-width: unset;

    }

    .questionnaire_afficher{
        min-height:10vh;
        max-height: 40vh;
        overflow-y:auto;
        overflow-x:hidden;
    }

    .container{
        flex: 1 0;
    }

    </style>
@endpush

@section('formulaire')
<form class="form-horizontal" style="display: flex;flex-direction: column;gap: 35px;justify-content: space-between;" method="POST" action="{{ URL::to('/eden/questionnaire/traitement') }}">
	{{ csrf_field() }}

    <div>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <h2 class="text-center">
            {{ modele('questionnaire')->select('nom')->where('id',$questionnaire_id)->first()['nom'] }}
        </h2>

        <div class="questionnaire_afficher" >

            <div style="display:none;">
                <input name="repondant_id" value="{{$repondant_id}}" required>
                <input name="token" value="{{$token}}" required>
                <input name="questionnaire_id" value="{{$questionnaire_id}}" required>
            </div>

            <questionnaire
                :questionnaire_id="{{$questionnaire_id}}"
                :affichage_client=true>
            </questionnaire>

        </div>
    </div>

    <div style="display: flex;justify-content: center;">
        <button type="submit" class="btn btn-primary btn-lrdl" style="background-color: {{ maquette('background_menus') }}; border-color: {{ maquette('background_sous_menus') }}; width: 100px">
            {{traduction('interface.modales.valider') }}
        </button>
    </div>
</form>
@endsection