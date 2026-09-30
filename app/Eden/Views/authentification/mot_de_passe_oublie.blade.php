@extends('eden::authentification.template')

@section('title')
Mot de passe oublié - ERP
@endsection

@section('formulaire')
<form class="form-horizontal" method="POST" action="{{route("mot_de_passe_oublie")}}">
	{{ csrf_field() }}

	<p class="text-center" v-html="traduction('interface.mot_de_passe_oublie.phrase')"></p> <br>
	
	@if($errors->count() > 0)
		<div class="alert alert-danger">{{ $errors->first() }}</div>
	@endif
	
	
	
	<div class="form-group @if(session()->has('errors')) {{ session('errors')->has('email') ? ' has-error' : '' }} @endif">
		<label for="email" class="col-md-4 control-label" v-html="traduction('interface.mot_de_passe_oublie.adresse_email')"></label>

		<div class="col-md-6">
			<input id="email" type="text" class="form-control" name="email" value="{{ old('email') }}" :placeholder="traduction('interface.mot_de_passe_oublie.placeholder_adresse_email')" required autofocus>
		</div>
	</div>

	<div class="form-group">
		<div class="col-md-6 col-md-offset-4">
			<button type="submit" class="btn btn-primary btn-lrdl" style="background-color: #038d8d; border-color: #527d1e; width: 100%">
				<span v-html="traduction('interface.mot_de_passe_oublie.retrouver_mot_de_passe')"></span>
			</button>
		</div>
	</div>

	<div class="form-group">
		<div class="col-md-8 col-md-offset-4">
			<a class="btn btn-link btn-link-lrdl" href="{{route('login')}}" style="color: #ee7767" v-html="traduction('interface.mot_de_passe_oublie.je_me_souviens')"> 
			</a>
		</div>
	</div>
</form>
@endsection
						