<div class="row">
    <div class="col-md-12">
        <div class="row">
            <div class="col-md-12">
                <progress id="progress_import_mesure" :value="import_en_cours.nombres_importes" style="width:100%;height:50px;" :max="import_en_cours.nombres_a_importer"></progress>
            </div>
            <div class="col-md-12" style="text-align: center" v-if="import_en_cours.nombres_a_importer != null">
                <span v-html="traduction('interface.import_sur_mesure.nombre_importes',null,[(import_en_cours.nombres_importes != null ? import_en_cours.nombres_importes : 0),import_en_cours.nombres_a_importer])"></span>
            </div>
        </div>
    </div>
</div>

@push('donnees_pour_vuejs_data')
    import_statut_en_cours : false,
    recuperation_nombre : null,
@endpush

@push('donnees_pour_vuejs_methods')

    annuler_import: async function(){

        var vue_instance = this;

		if(!await confirm_eden(vue_instance.traduction('interface.import_sur_mesure.confirmation_arret_import')))
			return false;

        loading(true);

        $.post({

			url: "eden/element/import_en_cours/"+vue_instance.import_en_cours.id+"/enregistrer",
			dataType: "json",
			method: 'POST',
			data: {
				statut: 4,
			}

		}).done(async function(donnees) {

			// On retire le loader
			loading(false);

			if(donnees.retour !== true) {

				await erreur(donnees.retour);
				return;
			}
			else {

				vue_instance.etape_import++;
				vue_instance.import_en_cours.statut = 4;
				vue_instance.import_statut_en_cours = false;
			}

		});

    },
@endpush

@push('donnees_pour_vuejs_watch')

    'import_statut_en_cours': {
		handler: function(nouvelle_valeur, ancienne_valeur) {
            var vue_instance = this;

            if(ancienne_valeur == false && nouvelle_valeur == true){
                this.recuperation_nombre = setInterval(function(){
                    $.ajax({
                        url: '{{"eden/element/import_en_cours"}}/'+vue_instance.import_en_cours.id,
                        dataType: 'json'
                    }).done(function(import_en_cours){

                        vue_instance.import_en_cours = import_en_cours;

                        if(import_en_cours.statut == 3){
                            vue_instance.import_statut_en_cours = false;
                            vue_instance.etape_import++;
                        }
                    });
                }, 5000);
            }
            else if(ancienne_valeur == true && nouvelle_valeur == false){
                clearInterval(this.recuperation_nombre);
            }
        }
    },
@endpush
