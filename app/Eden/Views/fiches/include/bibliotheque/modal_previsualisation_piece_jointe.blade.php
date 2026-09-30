<!-- Modal -->
<div class="modal fade" id="modal_previsualisation_fichier" tabindex="-1" role="dialog" aria-labelledby="modal_edition_pjLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="modal_edition_pjLabel">@traduction('interface.bibliotheque.modal_previsualisation_fichier.edition_piece_jointe')</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
            <div class="modal-body css_form">
                <input type="hidden" name="pj_id" value="">
                <div class="row">
                    <div class="col-sm-12 css_form_ligne_titre">@traduction('interface.bibliotheque.modal_previsualisation_fichier.informations_generales')</div>
                </div>
                <div class="row">
                    <div class="col-sm-2">@traduction('interface.bibliotheque.modal_previsualisation_fichier.titre')</div>
                    <div class="col-sm-10"><input type="text" v-model="piece_jointe.titre" ></div>
                </div>
                <div class="row">
                    <div class="col-sm-2">@traduction('interface.bibliotheque.modal_previsualisation_fichier.extension')</div>
                    <div class="col-sm-10"><input type="text" disabled="true" v-model="piece_jointe.extension" /></div>
                </div>
                <div class="row">
                    <div class="col-sm-2">@traduction('interface.bibliotheque.modal_previsualisation_fichier.poids')</div>
                    <div class="col-sm-10"><input type="text" disabled="true" v-model="piece_jointe.poids" /></div>
                </div>
                <div class="row">
                    <div class="col-sm-2">@traduction('interface.bibliotheque.modal_previsualisation_fichier.url')</div>
                    <div v-if='element_piece_jointe' class="col-sm-10"><input type="text" disabled="true" :value="'{{ URL::to('/eden/fiche')}}/'+type_element+'/'+id_element+'{{('/telecharger_piece_jointe')}}/'+piece_jointe.id" /></div>
                    <div v-else class="col-sm-10"><input type="text" disabled="true" :value="'{{ URL::to('/eden/document')}}/'+piece_jointe.type_element+'/'+piece_jointe.element_id+'{{('/afficher_pdf')}}/'+piece_jointe.nom" /></div>
                </div>
                <div class="row">
                    <div class="col-sm-2">@traduction('interface.bibliotheque.modal_previsualisation_fichier.ajout_pdf_document')</div>
                    <div class="col-sm-10">
                        <select v-model="piece_jointe.lier_au_document">
                            <option value="0">Non</option>
                            <option value="avant_document">@traduction('interface.bibliotheque.modal_previsualisation_fichier.avant_document')</option>
                            <option value="apres_document">@traduction('interface.bibliotheque.modal_previsualisation_fichier.apres_document')</option>
                        </select>
                    </div>
                </div>
			</div>
			<div class="modal-footer">
                <button type="button" v-if='element_piece_jointe' class="btn btn-success" @click="modifier_piece_jointe()">@traduction('interface.modales.enregistrer')</button>
                <a v-if='element_piece_jointe' :href="'{{ URL::to('/eden/fiche')}}/'+type_element+'/'+id_element+'{{('/telecharger_piece_jointe')}}/'+piece_jointe.id" type="button" class="btn btn-primary">@traduction('interface.bibliotheque.modal_previsualisation_fichier.bouton_telecharger')</a>
                <a v-else :href="'{{ URL::to('/eden/document')}}/'+piece_jointe.type_element+'/'+piece_jointe.element_id+'{{('/afficher_pdf')}}/'+piece_jointe.nom" type="button" class="btn btn-primary">@traduction('interface.modales.afficher')</a>
                <button type="button" v-if='element_piece_jointe' class="btn btn-danger" @click="supprimer_fichier(piece_jointe)" >@traduction('interface.modales.supprimer')</button>
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
			</div>
		</div>
	</div>
</div>

@push('donnees_pour_vuejs_methods')

	modal_previsualisation_fichier: function(fichier) {

		this.fichier = fichier;
		$('#modal_previsualisation_fichier').modal('show');
	},

    supprimer_fichier: function(piece_jointe)  {

        //console.log(piece_jointe);

        vue_contexte = this;

        // on enregistre les infos du champ libre
        $.ajax({

	        url: "{{ URL::to('/eden/fiche') }}/"+vue_contexte.type_element+'/'+vue_contexte.id_element+'/supprimer_piece_jointe/'+piece_jointe.id,
	        dataType: "json"
        }).done(async function(donnees) {

            if(donnees.retour !== true) {

                await erreur(donnees.retour);
                return;
            }

            vue_contexte.fichiers.splice(vue_contexte.fichiers.indexOf(piece_jointe), 1);

            $('#modal_previsualisation_fichier').modal('hide');


        });

        return false;

    },
@endpush

@push('donnees_pour_vuejs_data')

    fichier: {},

@endpush