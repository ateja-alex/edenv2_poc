<div class="row">
	<div class="col-md-12">
		<div class="card mb-3">
			<div class="card-header">
				<h4>
					@traduction('module_sur_fiche.entreprise.liste_clients.clients') <a href="#" @click.prevent="creer_client" class="css__lien"><span class="fa fa-plus"></span> @traduction('module_sur_fiche.entreprise.liste_clients.nouveau_client')</a>
				</h4>
			</div>
			<div class="card-body">

				<div class="table-responsive">
					<table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
						<thead>
							<tr>
								<th scope="col">@traduction('module_sur_fiche.entreprise.liste_clients.ref')</th>
								<th scope="col">@traduction('module_sur_fiche.entreprise.liste_clients.client')</th>
							</tr>
						</thead>
						<tbody>
							<template v-for="client in clients">
								<tr>
									<td><a :href="'{{ URL::to('/eden/fiche/client/') }}/'+client.id+'/afficher'">@{{ client.id }}</a></td>
									<td>@{{ client.prenom }} @{{ client.nom }}  <span class="badge badge-warning" v-show="client.archive == 1">@traduction('module_sur_fiche.entreprise.liste_clients.archive')</span></td>
								</tr>
							</template>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="modal_ajout_client" tabindex="-1" role="dialog" aria-labelledby="modal_ajout_clientLabel" aria-hidden="true">
  	<div class="modal-dialog modal-lg" role="document">
    	<div class="modal-content">
      		<div class="modal-header">
        		<h5 class="modal-title" id="modal_ajout_clientLabel">@traduction('module_sur_fiche.entreprise.liste_clients.titre_modal')</h5>
       			 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          			<span aria-hidden="true">&times;</span>
        		</button>
      		</div>
      		<div class="modal-body">

			  	<form action="#" method="post" class="css_form" id="formulaire_creer_client" enctype="multipart/form-data">
					@include("eden::formulaires.client")
				</form>
      		</div>
      		<div class="modal-footer">
        		<button type="button" class="btn btn-secondary css_btn_responsive" data-dismiss="modal">{{traduction('interface.modales.fermer')}}</button>
        		<button type="button" class="btn btn-primary css_btn_responsive" @click="enregistrer_client">{{traduction('interface.modales.enregistrer')}}</button>
      		</div>
    	</div>
  	</div>
</div>


@push('donnees_pour_vuejs_data')
	clients: {!! collect($clients) !!},
	client : {!! management('client')->modele_par_defaut() !!},
@endpush

@push('donnees_pour_vuejs_methods')

	creer_client() {

		$('#modal_ajout_client').modal('show');
	},

	enregistrer_client() {

		var formulaire = $('#formulaire_creer_client');
		// On afficher le loader
		loading();

		var $this = this;
		var url = 'eden/element/client/creer'

		$.post({

			url: url,
			dataType: "json",
			method: 'POST',
			// data: formulaire.serialize()
			data: new FormData(formulaire[0]),
			cache: false,
			contentType: false,
			processData: false,
			// Custom XMLHttpRequest
			xhr: function() {
				var myXhr = $.ajaxSettings.xhr();
				if (myXhr.upload) {
					// For handling the progress of the upload
					myXhr.upload.addEventListener('progress', function(e) {
						if (e.lengthComputable) {
							$('progress').attr({
								value: e.loaded,
								max: e.total,
							});
						}
					} , false);
				}
				return myXhr;
			}
		}).done(async function(donnees) {
			
			// On retire le loader
			loading(false);
			
			if(donnees.retour !== true) {
			
				await erreur(donnees.retour);
				return;
			}

			// on ajoute le nouveau client dans le tableau
			$this.clients.push(donnees.element);

			// on reset le formulaire
			$this.client = {!! management('client')->modele_par_defaut() !!};

			$('#modal_ajout_client').modal('hide');
		});
	},

	
@endpush