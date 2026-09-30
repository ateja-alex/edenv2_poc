@extends('eden::authentification.template')

@section('title')
Mot de passe oublié - ERP
@endsection

@section('formulaire')
<form class="form-horizontal" method="POST" action="{{route('reinitialisation_mot_de_passe_post') }}">
	{{ csrf_field() }}

	<p class="text-center" v-html="traduction('interface.reinitialisation_mot_de_passe_oublie.phrase')"></p> <br>
	
	@if($errors->count() > 0)
		<div class="alert alert-danger">{{ $errors->first() }}</div>
	@endif
	
	<div class="form-group">
		<label for="email" class="col-md-4 control-label" v-html="traduction('interface.reinitialisation_mot_de_passe_oublie.mot_de_passe')"></label>

		<div class="col-md-6" style="display: grid">
			<Password v-model="mot_de_passe" name="mot_de_passe" input-style="width:100%;" :placeholder="traduction('interface.renouvellement_mot_de_passe.placeholder_mot_de_passe')">
				<template #footer>
					<div style="display: flex; flex-direction: column; gap: 5px;">
						<span>
							<i :class="'fas fa-' + (mot_de_passe.match(/[a-z]/) ? 'check' : 'times')"></i> <span v-html="traduction('interface.renouvellement_mot_de_passe.minuscule')"></span>
						</span>
						<span>
							<i :class="'fas fa-' + (mot_de_passe.match(/[A-Z]/) ? 'check' : 'times')"></i> <span v-html="traduction('interface.renouvellement_mot_de_passe.majuscule')"></span>
						</span>
						<span>
							<i :class="'fas fa-' + (mot_de_passe.match(/[0-9]/) ? 'check' : 'times')"></i> <span v-html="traduction('interface.renouvellement_mot_de_passe.nombre')"></span>
						</span>
						<span>
							<i :class="'fas fa-' + (mot_de_passe.length >= 8 ? 'check' : 'times')"></i> <span v-html="traduction('interface.renouvellement_mot_de_passe.taille_minimale')"></span>
						</span>
					</div>
				</template>
			</Password>
		</div>
	</div>

	<div class="form-group">
		<label for="email" class="col-md-4 control-label" v-html="traduction('interface.reinitialisation_mot_de_passe_oublie.confirmation_mot_de_passe')"></label>

		<div class="col-md-6" style="display: grid">
			<Password v-model="mot_de_passe_confirmation" input-style="width:100%;" name="mot_de_passe_confirmation" :feedback="false" :placeholder="traduction('interface.reinitialisation_mot_de_passe_oublie.placeholder_confirmation_mot_de_passe')">
			</Password>
			<div class="help-block" v-if="mot_de_passe_confirmation != '' && mot_de_passe != '' && mot_de_passe_confirmation !== mot_de_passe">
				<span v-html="traduction('messages.php.connexion.mdp_differents')"></span>
			</div>
		</div>
	</div>

	<div class="form-group">
		<div class="col-md-6 col-md-offset-4">
			<button type="submit" class="btn btn-primary btn-lrdl" style="background-color: #038d8d; border-color: #527d1e; width: 100%">
				<span v-html="traduction('interface.reinitialisation_mot_de_passe_oublie.connexion')"></span>
			</button>
		</div>
	</div>

	<input type="hidden" name="id_utilisateur" value="{{ $id_utilisateur }}" />
	<input type="hidden" name="token" value="{{ $token }}" />
</form>
@endsection
				
@push('donnees_pour_vuejs_data')
	mot_de_passe : '',
	mot_de_passe_confirmation : '',
@endpush