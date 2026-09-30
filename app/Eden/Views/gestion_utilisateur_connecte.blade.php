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
							<formulaire ref="formulaire" nom_formulaire="preferences"></formulaire>
							<button type="button" class="btn btn-primary" @click="enregistrer">@traduction('interface.modales.enregistrer')</button>
						</div>
					</div>
				</div>
				<div class="col-md-12">
					@php
						$id_liste_compte_email = \App\Eden\Models\Liste_libre::where('type_element','compte_email')->first()->id;
					@endphp
					<liste-libre-{{$id_liste_compte_email}}
						ref="liste_libre_{{$id_liste_compte_email}}"

						:filtres_pour_fiche="{'utilisateur_id' : {{$utilisateur->id}}}"
						:modele_par_defaut="modele_par_defaut_compte_email"

						:mode_parametrage=1
					>
					</liste-libre-{{$id_liste_compte_email}}>
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

@push('composants_vue')
    <script type="text/javascript" src="{{ asset('storage/composants/liste_libre_'.$id_liste_compte_email.'.js') }}"></script>
@endpush

@push('donnees_pour_vuejs_data')
	utilisateur: {!! $utilisateur !!},
    modele_par_defaut_compte_email : {!! modele_par_defaut('compte_email') !!},
	modale_qrcode: false,
@endpush

@push('donnees_pour_vuejs_mounted')
    this.modele_par_defaut_compte_email.utilisateur_id = {{$utilisateur->id}};
	this.$once('formulaire_charger',() => {
		this.$refs.formulaire.element = this.utilisateur;
    });
	
@endpush

@push('donnees_pour_vuejs_methods')
	enregistrer: async function() {

		loading(true);

		var donnees = await this.$refs.formulaire.enregistrer({}, '{{route('base_eden.utilisateur_connecte.enregistrer')}}');

		loading(false);

		if(donnees.retour !== true) {

			await erreur(donnees.retour);
			return;
		}

		else
			toastr.success('Enregistrement bien effectué');
			
	},
@endpush