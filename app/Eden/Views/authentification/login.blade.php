@extends('eden::authentification.template')

@section('title')
Login - @if(isset($extranet)) Extranet @else ERP @endif
@endsection

@section('formulaire')
<form class="form-horizontal" method="POST" action="@if(isset($extranet)){{route('extranet.login_post')}}@else{{ URL::to('/eden/login') }}@endif">
	{{ csrf_field() }}
    
    @if($errors->count() > 0)
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    @if(fonctionnalite('utiliser_connexion_classique') || isset($extranet))

        @if(isset($extranet))
            <p class="text-center" v-html="traduction('interface.authentification.phrase_login_extranet')"></p> <br>
        @else
            <p class="text-center" v-html="traduction('interface.authentification.phrase_login')"></p> <br>
        @endif

        <div class="form-group @if(session()->has('errors')) {{ session('errors')->has('email') ? ' has-error' : '' }} @endif">
            <label for="email" class="col-md-4 control-label" style="text-transform: uppercase;" v-html="traduction('interface.authentification.adresse_email')"></label>

            <div class="col-md-6">
                <input id="email" type="text" class="form-control" name="email" value="{{ old('email') }}" :placeholder="traduction('interface.authentification.adresse_email')" required autofocus>
            </div>
        </div>

        <div class="form-group @if(session()->has('errors')) {{ session('errors')->has('password') ? ' has-error' : '' }} @endif">
            <label for="password" class="col-md-4 control-label" style="text-transform: uppercase;" v-html="traduction('interface.authentification.mot_de_passe')"></label>

            <div class="col-md-6">
                <input id="password" type="password" class="form-control" name="password" :placeholder="traduction('interface.authentification.mot_de_passe')" required>
            </div>
        </div>

        @if(!isset($extranet))
            <div class="form-group">
                <div class="col-md-6 col-md-offset-4">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                            <span v-html="traduction('interface.authentification.se_souvenir')"></span>
                        </label>
                    </div>
                </div>
            </div>
        @endif

        <div class="form-group">
            <div class="col-md-6 col-md-offset-4">
                <button type="submit" class="btn btn-primary btn-lrdl" style="background-color: {{ maquette('couleur_liens') }}; border-color: {{ maquette('background_sous_menus') }}; width: 100%" >
                    <span v-html="traduction('interface.authentification.connexion')"></span>
                </button>
            </div>
        </div>
    @endif
	@if(!isset($extranet))
	
		@if(config('fonctionnalites_integrations.microsoft_utiliser_connexion'))
            <div class="form-group">
                <div class="col-md-6 @if(fonctionnalite('utiliser_connexion_classique')) col-md-offset-4 @else col-md-offset-3 @endif">
                    <a href="/eden/login/connexion_microsoft" class="btn btn-primary btn-lrdl" style="background-color: {{ maquette('couleur_liens') }}; border-color: {{ maquette('background_sous_menus') }}; width: 100%">
                        <span v-html="traduction('interface.authentification.connexion_office')"></span>
                    </a>
                </div>
            </div>
		@endif

		@if(config('fonctionnalites_integrations.google_utiliser_connexion'))
            <div class="form-group">
                <div class="col-md-6 @if(fonctionnalite('utiliser_connexion_classique')) col-md-offset-4 @else col-md-offset-3 @endif">
                    <a href="/eden/login/connexion_google" class="btn btn-primary btn-lrdl" style="background-color: {{ maquette('couleur_liens') }}; border-color: {{ maquette('background_sous_menus') }}; width: 100%">
                        <span v-html="traduction('interface.authentification.connexion_google')"></span>
                    </a>
                </div>
            </div>
		@endif

	@endif
	@if(fonctionnalite('utiliser_connexion_classique') || isset($extranet))
        <div class="form-group">
            <div class="col-md-8 col-md-offset-4">
                <a class="btn btn-link btn-link-lrdl" href="@if(isset($extranet)){{route('extranet.mot_de_passe_oublie')}}@else{{ route('mot_de_passe_oublie') }}@endif" style="color: #ee7767">
                    <span v-html="traduction('interface.authentification.mot_de_passe_oublie')"></span>
                </a>
            </div>
        </div>
	@endif
</form>
@endsection
						