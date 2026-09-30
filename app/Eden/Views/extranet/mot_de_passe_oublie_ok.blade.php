@extends('eden::extranet.template-login.template')

@section('title')
    Mot de passe oublié - Extranet
@endsection

@section("style")
    <style>
        #auth-reset .bouton_retour{
            background-color: {{ maquette('couleur_liens') }};
            border: solid 1px {{ maquette('couleur_liens') }};
            text-align: center;
            padding: 10px;
            color: white;
            text-decoration: none;
            cursor: pointer;
        }

        .bouton_retour:active{

            filter: brightness(80%);
        }
    </style>
@endsection

@section('contenu')
    <div id="auth-reset">

        @if($retour === true)
            <div class="alert alert-success" v-html="traduction('interface.mot_de_passe_oublie_ok.phrase')"></div>
        @else
            <div class="alert alert-danger">{{$retour}}</div>
        @endif

        <div class="button">
            <a class="bouton_retour" href="{{route('extranet.login')}}" v-html="traduction('interface.modales.retour_accueil')">
            </a>
        </div>
    </div>
@endsection
