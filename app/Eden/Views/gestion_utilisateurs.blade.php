@extends('eden::templates.template')

@section('title') Gestion des utilisateurs @stop

@section('content')

	<div id="vue_app" >
		<div class="content-wrapper" >
			<div id="base-content" class="container-fluid">

				@include('eden::includes.fil_ariane', ['fil_ariane' => array(
					array('nom' => 'Configuration'),
					array('nom' => 'Utilisateurs')
				)])

				<div class="row">
					<div class="col-md-12">
						<div class="card mb-3">
							<div class="card-header d-flex align-items-center">
								<h4>
									@traduction('interface.gestion_utilisateurs.titre')
								</h4>
								<span class="css_ajouter_element ml-auto" data-toggle="tooltip" data-placement="left" :title="traduction('interface.gestion_utilisateurs.bouton_ajouter.titre')" @click="ajouter">
									<i class="css_action_icon fa fa-fw fa-plus-square"></i>
								</span>
							</div>
							<div class="card-body">
								<div class="container-fluid">

									@if(editeur())
										<div class="row mb-3">
											<div class="col-12">
												<div class="css_rang_utilisateurs">
													@traduction('interface.gestion_utilisateurs.titre_categorie.editeurs')
												</div>
											</div>
											<div class="col-12 col-md-2 css_block_utilisateur_gestion_users"
												 v-for="utilisateur in utilisateurs"
												 v-if="utilisateur.type_utilisateur == 2 || utilisateur.super_admin == 1"
												 :style="utilisateur.autorise_a_se_connecter == 1 ? '' : 'opacity: 0.2; filter: grayscale(80%);'">
												<div class="css_contenu_block_utilisateur">
													<div class="css_img_utilisateur">
														<img @click="modifier(utilisateur)" :id_utilisateur="utilisateur.id" :src="utilisateur.avatar != false && utilisateur.avatar != undefined ? 'storage/'+utilisateur.avatar : 'eden/images/no_avatar.jpg'">
													</div>
													<div class="css_nom_utilisateur_gestion_users" @click="modifier(utilisateur)" :id_utilisateur="utilisateur.id">
														@{{ utilisateur.prenom }} @{{ utilisateur.nom }}
													</div>
													<div class="css_infos_utilisateur_gestion_users">
                                                        <i class="far fa-envelope"></i> <a class="js_encoyer_mail" href="#" @click="click_envoyer_par_email(utilisateur.id)">@{{ utilisateur.email }}</a>													</div>
												</div>
												<div class="css_bordure_bas_block_utilisateur"></div>
											</div>
										</div>
									@endif

									<div class="row mb-3">
										<div class="col-12">
											<div class="css_rang_utilisateurs">
												@traduction('interface.gestion_utilisateurs.titre_categorie.administrateurs')
											</div>
										</div>
										<div class="col-12 col-md-2 css_block_utilisateur_gestion_users" v-for="utilisateur in utilisateurs"
											 v-if="utilisateur.type_utilisateur == 1"
											 :style="utilisateur.autorise_a_se_connecter == 1 ? '' : 'opacity: 0.2; filter: grayscale(80%);'">
											<div class="css_contenu_block_utilisateur">
												<div class="css_img_utilisateur">
													<img @click="modifier(utilisateur)" :id_utilisateur="utilisateur.id" :src="utilisateur.avatar != false && utilisateur.avatar != undefined ? 'storage/'+utilisateur.avatar : 'eden/images/no_avatar.jpg'">
												</div>
												<div class="css_nom_utilisateur_gestion_users" @click="modifier(utilisateur)" :id_utilisateur="utilisateur.id">
													@{{ utilisateur.prenom }} @{{ utilisateur.nom }}
												</div>
												<div class="css_infos_utilisateur_gestion_users">
                                                    <i class="far fa-envelope"></i> <a class="js_encoyer_mail" href="#" @click="click_envoyer_par_email(utilisateur.id)">@{{ utilisateur.email }}</a>												</div>
											</div>
											<div class="css_bordure_bas_block_utilisateur"></div>
										</div>
									</div>

									@foreach($profils as $profil)
										<div class="row mb-3">
											<div class="col-12">
												<div class="css_rang_utilisateurs">
													{{ $profil->nom }}
												</div>
											</div>
											<div class="col-12 col-md-2 css_block_utilisateur_gestion_users"
												 v-for="utilisateur in utilisateurs"
												 v-if="utilisateur.type_utilisateur == 0 && utilisateur.profil_id == {{ $profil->id }}"
												 :style="utilisateur.autorise_a_se_connecter == 1 ? '' : 'opacity: 0.2; filter: grayscale(80%);'">
												<div class="css_contenu_block_utilisateur">
													<div class="css_img_utilisateur">
														<img @click="modifier(utilisateur)" :id_utilisateur="utilisateur.id" :src="utilisateur.avatar != false && utilisateur.avatar != undefined ? 'storage/'+utilisateur.avatar : 'eden/images/no_avatar.jpg'">
													</div>
													<div class="css_nom_utilisateur_gestion_users" @click="modifier(utilisateur)" :id_utilisateur="utilisateur.id">
														@{{ utilisateur.prenom }} @{{ utilisateur.nom }}
													</div>
													<div class="css_infos_utilisateur_gestion_users">
                                                        <i class="far fa-envelope"></i> <a class="js_encoyer_mail" href="#" @click="click_envoyer_par_email(utilisateur.id)">@{{ utilisateur.email }}</a>													</div>
												</div>
												<div class="css_bordure_bas_block_utilisateur"></div>
											</div>
										</div>
									@endforeach

									<div class="row mb-3">
										<div class="col-12">
											<div class="css_rang_utilisateurs">
												@traduction('interface.gestion_utilisateurs.titre_categorie.utilisateurs')
											</div>
										</div>
										<div class="col-12 col-md-2 css_block_utilisateur_gestion_users"
											 v-for="utilisateur in utilisateurs"
											 v-if="utilisateur.type_utilisateur == '0' && (utilisateur.profil_id == 0 || utilisateur.profil_id == null)"
											 :style="utilisateur.autorise_a_se_connecter == 1 ? '' : 'opacity: 0.2; filter: grayscale(80%);'">
											<div class="css_contenu_block_utilisateur">
												<div class="css_img_utilisateur">
													<img @click="modifier(utilisateur)" :id_utilisateur="utilisateur.id" :src="utilisateur.avatar != false && utilisateur.avatar != undefined ? 'storage/'+utilisateur.avatar : 'eden/images/no_avatar.jpg'">
												</div>
												<div class="css_nom_utilisateur_gestion_users" @click="modifier(utilisateur)" :id_utilisateur="utilisateur.id">
													@{{ utilisateur.prenom }} @{{ utilisateur.nom }}
												</div>
												<div class="css_infos_utilisateur_gestion_users">
                                                    <i class="far fa-envelope"></i> <a class="js_encoyer_mail" href="#" @click="click_envoyer_par_email(utilisateur.id)">@{{ utilisateur.email }}</a>												</div>
											</div>
											<div class="css_bordure_bas_block_utilisateur"></div>
										</div>
									</div>

									<div class="row mb-3">
										<div class="col-12">
											<div class="css_rang_utilisateurs">
												@traduction('interface.gestion_utilisateurs.titre_categorie.ressources')
											</div>
										</div>
										<div class="col-12 col-md-2 css_block_utilisateur_gestion_users"
											 v-for="utilisateur in utilisateurs"
											 v-if="utilisateur.type_utilisateur == 3">
											<div class="css_contenu_block_utilisateur">
												<div class="css_img_utilisateur">
													<img @click="modifier(utilisateur)" :id_utilisateur="utilisateur.id" :src="utilisateur.avatar != false && utilisateur.avatar != undefined ? 'storage/'+utilisateur.avatar : 'eden/images/no_avatar.jpg'">
												</div>
												<div class="css_nom_utilisateur_gestion_users" @click="modifier(utilisateur)" :id_utilisateur="utilisateur.id">
                                                    @{{ utilisateur.prenom }} @{{ utilisateur.nom }}
												</div>
											</div>
											<div class="css_bordure_bas_block_utilisateur"></div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
        @if(fonctionnalite('microsoft_utiliser_connexion') && !empty(moi()->id_microsoft))
            <div class="content-wrapper" v-if="utilisateurs_microsoft.length > 0">
                <div id="base-content" class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card mb-3">
                                <div class="card-header d-flex align-items-center">
                                    <h4>
                                        @traduction('interface.gestion_utilisateurs.titre_categorie.utilisateurs_microsoft')
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <div class="container-fluid">
                                        <div class="css_gestion_utilisateurs_microsoft">
                                            <div class="css_bloc_utilisateur_microsoft" v-for="utilisateur_microsoft in utilisateurs_microsoft" @click="activer_via_microsoft(utilisateur_microsoft)">
                                                <div class="css_img_utilisateur">
													<img :src="utilisateur_microsoft.photo !== null ? 'data:'+utilisateur_microsoft.photo.type+';base64,'+utilisateur_microsoft.photo.stream : 'eden/images/no_avatar.jpg'">
                                                </div>
                                                <div class="css_nom_utilisateur_gestion_users" >

                                                    <span v-if="utilisateur_microsoft.prenom != null && utilisateur_microsoft.nom != null">@{{ utilisateur_microsoft.prenom }} @{{ utilisateur_microsoft.nom }}</span><br>
                                                    <span v-if="utilisateur_microsoft.mail != null">@{{ utilisateur_microsoft.mail }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
       @endif

        @push('modales')
        <template v-if="modal_modification_utilisateur">
            <transition name="modal">
                <div id="modal_modification_utilisateur" class="modal-mask">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">@traduction('interface.gestion_utilisateurs.modale_utilisateurs.titre')</h5>
                                <button type="button" class="close" @click="modal_modification_utilisateur = false" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <formulaire ref="formulaire" nom_formulaire="utilisateur"></formulaire>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" @click="modal_modification_utilisateur = false">@traduction('interface.modales.fermer')</button>
                                @if(admin()  && moi()->droit_usurpation == 1)

                                    <a :href="'{{ URL::to('eden/parametrage/usurpation') }}/'+utilisateur.id" class="btn btn-warning" v-show="utilisateur.id != undefined" v-if="utilisateur.type_utilisateur != 3 && utilisateur.service != 1"><span class="fa fa-user-shield"></span>@traduction('interface.gestion_utilisateurs.modale_utilisateurs.se_connecter_en_tant') @{{ utilisateur.prenom }}</a>
                                @endif
                                <button type="button" class="btn btn-danger" @click="supprimer" v-show="utilisateur.id != undefined">@traduction('interface.modales.supprimer')</button>
                                <button type="button" class="btn btn-primary" @click="enregistrer(false)">@traduction('interface.modales.enregistrer')</button>
                                <button type="button" class="btn btn-primary" @click="enregistrer(true)" v-show="utilisateur.id == undefined">@traduction('interface.gestion_utilisateurs.modale_utilisateurs.enregistrer_envoyer_identifiants')</button>
                            </div>
                        </div>
                    </div>
                </div>
            </transition>
        </template>
        @endpush
	</div>

@endsection

<script>
@push('donnees_pour_vuejs_data')
	utilisateurs: {!! $utilisateurs !!},
	utilisateurs_microsoft: {!! collect($utilisateurs_microsoft) !!},
    modal_modification_utilisateur: false,
	utilisateur : {},

@endpush

@push('donnees_pour_vuejs_methods')

	ajouter(event) {
        this.modal_modification_utilisateur = true;
		this.utilisateur = {};
	},

	modifier(utilisateur) {

		this.utilisateur = utilisateur;

        this.$once('formulaire_charger',() => {
			this.$refs.formulaire.element = utilisateur;
        });

		this.modal_modification_utilisateur = true;

	},

	enregistrer: async function(envoie_identifiants) {

		loading(true);

		var informations_supp = {};

		if(envoie_identifiants)
			informations_supp = {"envoyer_identifiants" : envoie_identifiants};

		var donnees = await this.$refs.formulaire.enregistrer(informations_supp);

		loading(false);

		if(donnees.retour !== true) {

			await erreur(donnees.retour);
			return;
		}

		this.utilisateur.mot_de_passe = null;
		this.utilisateur.mot_de_passe_verification = null;

		this.modal_modification_utilisateur = false;
	},

	async supprimer() {

		if(!await confirm_eden("Êtes-vous certain de vouloir désactiver cet utilisateur ?"))
			return;

		loading(true);

		var vue_contexte = this;

		// on enregistre les infos du champ libre
		$.ajax({

			url: "{{ URL::to('/eden/parametrage/utilisateur') }}/"+this.utilisateur.id+"/supprimer",
			dataType: "json"
		}).done(async function(donnees) {

			if(donnees.retour !== true) {

				await erreur(donnees.retour);
				return;
			}

            vue_contexte.modal_modification_utilisateur = false;

			loading(false);

			vue_contexte.utilisateurs = donnees.utilisateurs;
		});

		return false;
	},

	activer_via_microsoft(utilisateur) {

		this.ajouter();

		this.utilisateur.nom = utilisateur.nom;
		this.utilisateur.prenom = utilisateur.prenom;
		this.utilisateur.email = utilisateur.mail;
	},

    click_envoyer_par_email(utilisateur_id) {
        const parametres = {
            type_element: 'utilisateur',
            id_element: utilisateur_id,
        };

		this.$root.$emit('envoie_email',parametres);
    },


@endpush

</script>
