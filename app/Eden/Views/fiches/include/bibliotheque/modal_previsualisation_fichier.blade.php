<!-- Modal ajout élément -->
<div class="modal fade" id="modal_previsualisation_fichier" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">@traduction('interface.bibliotheque.modal_previsualisation_fichier.gestion_bibliotheque')</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body css_form">
				<div class="row">
					<div class="col-sm-12 css_form_ligne_titre">@traduction('interface.bibliotheque.modal_previsualisation_fichier.informations_generales')</div>
				</div>
				<div class="row">
					<div class="col-sm-2">@traduction('interface.bibliotheque.modal_previsualisation_fichier.nom')</div>
					<div class="col-sm-10"><input type="text" disabled="true" v-model="fichier.chemin" /></div>
				</div>
				<div class="row">
					<div class="col-sm-2">@traduction('interface.bibliotheque.modal_previsualisation_fichier.nom_original')</div>
					<div class="col-sm-10"><input type="text" disabled="true" v-model="fichier.nom_original" /></div>
				</div>
				<div class="row">
					<div class="col-sm-2">@traduction('interface.bibliotheque.modal_previsualisation_fichier.dimensions')</div>
					<div class="col-sm-10"><input type="text" disabled="true" v-model="fichier.dimensions" /></div>
				</div>
				<div class="row">
					<div class="col-sm-2">@traduction('interface.bibliotheque.modal_previsualisation_fichier.extension')</div>
					<div class="col-sm-10"><input type="text" disabled="true" v-model="fichier.type" /></div>
				</div>
				<div class="row">
					<div class="col-sm-2">@traduction('interface.bibliotheque.modal_previsualisation_fichier.poids')</div>
					<div class="col-sm-10"><input type="text" disabled="true" v-model="fichier.poids" /></div>
				</div>
				<div class="row">
					<div class="col-sm-2">@traduction('interface.bibliotheque.modal_previsualisation_fichier.url')</div>
					<div class="col-sm-10">
						<input v-if="fichier.sharepoint" type="text" disabled="true" :value="fichier.chemin" />
						<input v-else type="text" disabled="true" :value="'{{URL::to('storage')}}/'+fichier.chemin" />
					</div>
				</div>

			</div>
			<div class="modal-footer">
                <a :href="'{{ URL::to('/eden/bibliotheque/fichier')}}/'+fichier.id+'{{('/telecharger')}}'" type="button" class="btn btn-primary">@traduction('interface.bibliotheque.modal_previsualisation_fichier.bouton_telecharger')</a>
				@if(!empty(moi()))
					<button type="button" class="btn btn-danger" @click="supprimer_fichier(fichier)" >@traduction('interface.modales.supprimer')</button>
				@endif
				<button type="button" class="btn btn-secondary" data-dismiss="modal">@traduction('interface.modales.fermer')</button>
			</div>

		</div>
	</div>
</div>

@push('donnees_pour_vuejs_methods')

	modal_previsualisation_fichier: function(fichier) {

		this.fichier = fichier;
		$('#modal_previsualisation_fichier').modal('show');
	},

    supprimer_fichier : function(fichier)  {

        vue_contexte = this;

        // on enregistre les infos du champ libre
        $.ajax({

	        url: "{{ URL::to('/eden/bibliotheque/fichier') }}/"+fichier.id+"/supprimer",
	        dataType: "json"

        }).done(async function(donnees) {

	        if(donnees.retour !== true) {

		        await erreur(donnees.retour);
		        return;
	        }

	        vue_contexte.fichiers.splice(vue_contexte.fichiers.indexOf(fichier), 1);

	        $('#modal_previsualisation_fichier').modal('hide');
        });

        return false;

    },

@endpush

@push('donnees_pour_vuejs_data')

    fichier: {},

@endpush