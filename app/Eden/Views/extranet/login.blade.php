@extends('eden::extranet.template-login.template')

@section('title')
    Login - Extranet
@endsection

@section("style")
    <style>
        #login .button-submit {
            background-color: {{ maquette('couleur_liens') }};
            border-color: {{ maquette('couleur_liens') }};
        }

        #login .button-reset {
            color: {{maquette("couleur_liens")}};
        }

        .button-submit:active{
            filter: brightness(80%);
        }
    </style>
@endsection

@section('contenu')
    <div id="login">
        <form method="POST" action="{{route('extranet.login_post')}}" class="form">
            {{ csrf_field() }}

            <h3 v-html="traduction('interface.authentification.phrase_login_extranet')"></h3> <br>

            @if($errors->count() > 0)
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <div class="form-field @if(session()->has('errors')) {{ session('errors')->has('email') ? ' has-error' : '' }} @endif">
                <label for="email" v-html="traduction('interface.authentification.adresse_email')"> : </label>

                <div>
                    <input id="email" type="text" name="email" value="{{ old('email') }}"
                           :placeholder="traduction('interface.authentification.adresse_email')" required autofocus>
                </div>
            </div>

            <div class="form-field @if(session()->has('errors')) {{ session('errors')->has('password') ? ' has-error' : '' }} @endif">
                <label for="password" v-html="traduction('interface.authentification.mot_de_passe')"> : </label>
                <div>
                    <input id="password" type="password" name="password" :placeholder="traduction('interface.authentification.mot_de_passe')" required>
                </div>
            </div>

            <div class="button">
                <a class="button-reset" href="{{route('extranet.mot_de_passe_oublie')}}" v-html="traduction('interface.authentification.mot_de_passe_oublie')"></a>

                <button type="submit" class="button-submit">
                    <span v-html="traduction('interface.authentification.connexion')"></span>
                </button>
            </div>
        </form>
    </div>
@endsection
