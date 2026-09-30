@extends('eden::authentification.template')

@section('title')
	Mot de passe oublié - ERP
@endsection

@section('formulaire')
	<div class="form-horizontal">

		@if($retour === true)
			<div class="alert alert-success" v-html="traduction('interface.mot_de_passe_oublie_ok.phrase')"></div>
		@else
			<div class="alert alert-danger">{{$retour}}</div>
		@endif

		<div class="form-group">
			<div class="col-md-8 col-md-offset-4">
				<a class="btn btn-link btn-link-lrdl" href="{{ route('login') }}" style="color: #ee7767" v-html="traduction('interface.modales.retour_accueil')"> 
				</a>
			</div>
		</div>
	</div>
@endsection
						