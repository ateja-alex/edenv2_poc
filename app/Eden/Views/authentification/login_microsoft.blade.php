@extends('eden::authentification.template')

@section('title')
Login - ERP
@endsection

@section('formulaire')

@if(session('nom_utilisateur')!=null)
	{{ session('nom_utilisateur') }}
@endif

<form class="form-horizontal" method="POST" action="{{ URL::to('/eden/login') }}">
	{{ csrf_field() }}

	<p class="text-center" v-html="traduction('interface.authentification_microsoft.phrase_login')"></p> <br>
	
	@if($errors->count() > 0)
		<div class="alert alert-danger">{{ $errors->first() }}</div>
	@endif
	
	<div class="form-group">
		<div class="col-md-6 col-md-offset-4">
			<a href="/eden/login/connexion_microsoft" class="btn btn-primary btn-lrdl" style="background-color: {{ maquette('background_menus') }}; border-color: {{ maquette('background_sous_menus') }}; width: 100%" v-html="traduction('interface.authentification_microsoft.connexion')"> 
			</a>
		</div>
	</div>

	
</form>
@endsection
						