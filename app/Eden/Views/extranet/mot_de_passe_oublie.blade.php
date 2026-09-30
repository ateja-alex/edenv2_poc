@extends('eden::extranet.template-login.template')

@section('title')
    Login - Extranet
@endsection

@section("style")
    <style>
        #auth-reset .button-submit {
            background-color: {{ maquette('couleur_liens') }};
            border-color: {{ maquette('couleur_liens') }};
        }

    </style>
@endsection


@section('contenu')
    <div id="auth-reset">

        @if ( session()->has('erreur_validation') )
            <div class="alert alert-danger">{{ session()->get('erreur_validation') }}</div>
        @endif
        @if (isset($reussite))
            <div class="alert alert-success">{{ $reussite }}</div>
        @endif

        <form method="post" action="/extranet/mot_de_passe_oublie_post" class="form">
            <div class="form-field">
                <label for="email" v-html="traduction('interface.mot_de_passe_oublie.adresse_email')"></label>
                <input type="email" name="email" id="email" :placeholder="traduction('interface.mot_de_passe_oublie.placeholder_adresse_email')">
            </div>
            <!-- /section -->
            <div class="button">
                <button type="submit" class="button-submit">
                    <span v-html="traduction('interface.mot_de_passe_oublie.retrouver_mot_de_passe')"></span>
                </button>
            </div>
        </form>
    </div>
@endsection
                