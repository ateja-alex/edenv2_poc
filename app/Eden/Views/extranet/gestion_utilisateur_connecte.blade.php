@extends('eden::templates.template')

@section('title') Gestion utilisateur @stop

@section('content')

<div id="vue_app" >
	<div class="content-wrapper css_form" >
		<div id="base-content" class="container-fluid">
			<div class="row">
				<div class="col-md-12">
					<div class="card mb-3">
						<div class="card-header">
							<h4>
								@traduction('interface.gestion_utilisateur_connecte.titre') : @{{utilisateur.prenom}}  @{{utilisateur.nom}}
							</h4>
						</div>
						<div class="card-body">
							<form action="#" method="post" class="css_form" id="formulaire_utilisateur">
								<div class="row">
									<div class="col-sm-12 css_form_ligne_titre">@traduction('interface.gestion_utilisateur_connecte.titre_module.informations')</div>
								</div>
								<div class="row" >
									<div class="col-sm-2">@traduction('champs_libres.utilisateur.nom.nom')</div>
									<div class="col-sm-4">{!! management('utilisateur')->champ('nom')->cree() !!}</div>
									<div class="col-sm-2">@traduction('champs_libres.utilisateur.prenom.nom')</div>
									<div class="col-sm-4">{!! management('utilisateur')->champ('prenom')->cree() !!}</div>
								</div>
								<div class="row">
									<div class="col-sm-12 css_form_ligne_titre">@traduction('interface.gestion_utilisateur_connecte.titre_module.changer_de_mot_de_passe')</div>
								</div>
								<div class="row" >
									<div class="col-sm-2">@traduction('champs_libres.utilisateur.mot_de_passe.nom')</div>
									<div class="col-sm-4" style="display: grid">
										<Password v-model="mot_de_passe" name="mot_de_passe" input-style="width:100%;" :placeholder="traduction('interface.renouvellement_mot_de_passe.placeholder_mot_de_passe')">
											<template #footer>
												<div style="display: flex; flex-direction: column; gap: 5px;">
													<span>
														<i :class="'fas fa-' + (mot_de_passe.match(/[a-z]/) ? 'check' : 'times')"></i> {{ traduction('interface.renouvellement_mot_de_passe.minuscule') }}
													</span>
													<span>
														<i :class="'fas fa-' + (mot_de_passe.match(/[A-Z]/) ? 'check' : 'times')"></i> {{ traduction('interface.renouvellement_mot_de_passe.majuscule') }}
													</span>
													<span>
														<i :class="'fas fa-' + (mot_de_passe.match(/[0-9]/) ? 'check' : 'times')"></i> {{ traduction('interface.renouvellement_mot_de_passe.nombre') }}
													</span>
													<span>
														<i :class="'fas fa-' + (mot_de_passe.length >= 8 ? 'check' : 'times')"></i> {{ traduction('interface.renouvellement_mot_de_passe.taille_minimale') }}
													</span>
												</div>
											</template>
										</Password>
									</div>
									<div class="col-sm-2">@traduction('interface.gestion_utilisateur_connecte.confirmation')</div>
									<div class="col-sm-4" style="display: grid">
										<Password v-model="mot_de_passe_verification" input-style="width:100%;" name="mot_de_passe_verification" :feedback="false" :placeholder="traduction('interface.reinitialisation_mot_de_passe_oublie.placeholder_confirmation_mot_de_passe')">
										</Password>
										<div class="help-block" v-if="mot_de_passe_verification != '' && mot_de_passe != '' && mot_de_passe_verification !== mot_de_passe">
											{{ traduction('messages.php.connexion.mdp_differents') }}
										</div>
									</div>
								</div>
								<div class="row" >
									<div class="col-sm-2">Connexion à double facteur</div>
									<div class="col-sm-4"> 
										<select name="double_facteur_authentification" @change="changement_double_facteur" v-model="utilisateur.double_facteur_authentification" class="form-control">
											<option :value="null" v-html="$root.traduction('valeurs_listes_formatees.720.valeur_0')"></option>
											<option value="1" v-html="$root.traduction('valeurs_listes_formatees.720.valeur_1')"></option>
											<option value="2" v-html="$root.traduction('valeurs_listes_formatees.720.valeur_2')"></option>
										</select>
									</div>
								</div>
								<div class="row">
									<div class="col-sm-12">

										<div class="row">
											<div class="col-sm-12 css_form_ligne_titre">@traduction('interface.gestion_utilisateur_connecte.titre_categorie.choix_langue')</div>
										</div>
										<div class="row">
											<div class="col-sm-2">
												<select v-model="utilisateur.langue" name="langue">
													<option v-for="langue in langues" :value="langue.id" :key="langue.id">
														@{{ langue.nom }}
													</option>
												</select>
											</div>
										</div>

									</div>
								</div>
							</form>
							<div style="width:100%;text-align:right;">
								<button type="button" class="btn btn-primary" @click="enregistrer">@traduction('interface.modales.enregistrer')</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

@endsection

@push('modales')
	<template v-if="modale_qrcode">
		<transition name="modal">
			<div class="modal-mask">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title">@traduction('interface.utilisateur_connecte.double_authentification_application.qr_code')</h5>
							<button type="button" class="close" @click="modale_qrcode = false" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body">
							<div>@traduction('interface.utilisateur_connecte.double_authentification_application.scanner_code')</div>
							<img :src="qr_code">
							<div>@traduction('interface.utilisateur_connecte.double_authentification_application.ou_code') @{{ code_secret }}</div>
						</div>
					</div>
				</div>
			</div>
		</transition>
	</template>
@endpush

@push('donnees_pour_vuejs_data')

	utilisateur: {!! $utilisateur !!},
	mot_de_passe:'',
	mot_de_passe_verification:'',
	qr_code: null,
	code_secret:null,
	modale_qrcode: false,
	langues : {!! langues() !!},
	langue_par_defaut : '{{maquette('langue_par_defaut')}}',
@endpush

@push('donnees_pour_vuejs_mounted')

	if(this.utilisateur.langue == null)
		this.utilisateur.langue = this.langue_par_defaut != '' || this.langue_par_defaut != null ? this.langue_par_defaut : (this.langues.find(l => l.code === 'fr').id ?? 0);
@endpush

@push('donnees_pour_vuejs_methods')

	enregistrer() {

		loading(true);
		var vue_contexte = this;

		var formulaire = $('#formulaire_utilisateur');

		// on enregistre les infos du champ libre
		$.post({

			url: '{{route('extranet.utilisateur_connecte.enregistrer')}}',
			dataType: "json",
			data: $('#formulaire_utilisateur').serialize(),
		}).done(async (donnees) => {

			loading(false);

			if(donnees.retour !== true) {

				await erreur(donnees.retour);
				return;
			}

			toastr.success('Enregistrement bien effectué');
			this.$root.moi_extranet = donnees.utilisateur;
			this.mot_de_passe = '';
			this.mot_de_passe_verification = '';

		});

	},

	changement_double_facteur : function(){

		if(this.utilisateur.double_facteur_authentification != 2)
			return;

		loading(true);

		$.post({
			url:'{{route('extranet.utilisateur_connecte.double_facteur_application')}}',
			data: {
				utilisateur_id: this.utilisateur.id,
			}
		}).done((retour) => {

			loading(false);

			this.qr_code = retour.qrcode;
			this.code_secret = retour.code_secret;
			this.modale_qrcode = true;
		});

	},

@endpush
