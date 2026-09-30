@extends('eden::ecommerce.template.template')
@section('content')
    <section class="css_section_margin">
        <div class="container">
			
			@if(!empty(session('erreur')))
				<div class="row">
					<div class="col-xs-12 col-md-12 alert alert-danger text-center">
						{{ session('erreur') }}
					</div>
				</div>
			@endif
			
			@if(!empty(session('succes')))
				<div class="row">
					<div class="col-xs-12 col-md-12 alert alert-success text-center">
						{{ session('succes') }}
					</div>
				</div>
			@endif
		
            <div class="row flex flex-start">
                <div class="col-xs-12 col-md-12">
					<div class="css_block_login_suivi">
						<form action="{{ route('ecommerce.reinitialisation_mot_de_passe_post') }}" method="post">
							<input type="hidden" name="id_client" value="{{ $id_client }}" />
							<input type="hidden" name="token" value="{{ $token }}" />
							<div class="css_picto_utilisateur_login">
								<img src="{{ asset('ecommerce-amc/images/pictos/user-blanc.png') }}" alt="">
							</div>
							<h3 class='oswald'>
								Réinitialiser votre mot de passe
							</h3>
							<input type="password" id="mot_de_passe" name="mot_de_passe" placeholder='Mot de passe'>
							<input type="password" id="mot_de_passe_confirmation" name="mot_de_passe_confirmation" placeholder='Veuillez resaisir le mot de passe'>
							<button class="btn btn-rouge-amc" type="submit">
								Enregistrer
							</button>
						</form>
					</div>
                </div>
                
            </div>
        </div>
    </section>
@endsection
