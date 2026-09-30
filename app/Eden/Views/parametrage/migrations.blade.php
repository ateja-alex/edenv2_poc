@extends('eden::templates.template')

@section('title') Migrations @stop

@include('eden::modales_alertes.rappels_versions')

@section('content')

    <div class="content-wrapper migrations">
        <div id="base-content" class="container-fluid">

            <div class="row">
                <div class="offset-md-3 col-md-6">
                    <div class="card-header">
                        <h4>
                            Lancement migrations
                        </h4>
                    </div>
                    <div class="card-body">

                        <div class="row">
                            <div class="col-md-6">
                                <button class="btn btn-success css_background_couleur_primaire" :disabled="lancement_migrations" @click="lancer_migrations()">Lancer les migrations</button>
                            </div>

							<div class="col-md-12 fin_migration" v-if="erreur_migration !== false">
                                <div class="erreur_migration">
                                    <p class="mb-2">Erreur lors de la migration</p>
									<p><strong><span v-text="'Fichier : ' + erreur_migration.fichier"></span></strong></p>
									<p><strong><span v-text="'Ligne : ' + erreur_migration.ligne"></span></strong></p>
									<p><strong><span v-html="'Erreur : ' + erreur_migration.message"></span></strong></p>
									<p><strong>Stacktrace :</strong> 
										<i :class="'css_pointer fas fa-chevron-'+(erreur_migration.affichage_stacktrace ? 'up' : 'down')" @click="$set(erreur_migration,'affichage_stacktrace',!erreur_migration.affichage_stacktrace)"></i>
									</p>
									<div v-if="erreur_migration.affichage_stacktrace" v-html="erreur_migration.stacktrace.replaceAll('\n','<br>')"></div>
                                </div>
                            </div>
                            <div class="col-md-6 fin_migration" v-else-if="redirection">
								<div class="redirection">Redirection...</div>
							</div>
							<div class="col-md-6 fin_migration" v-else-if="temps_execution != null">
								<div class="success">Migrations terminées avec succès en @{{temps_execution}}s !</div>
							</div>
                        </div>

                    </div>

                    <div class="ligne">
						<div class="fonction">
							<span class="badge badge-success" @click="tout_selectionner_deselectionner(true)">Tout cocher</span>
							<span class="badge badge-secondary" @click="tout_selectionner_deselectionner(false)">Tout décocher</span>
						</div>
                    </div>

                    <label class="ligne" :for="migration.fonction" v-for="migration in migrations">

						<div class="fonction">
							<input type="checkbox" v-model="migration.a_lancer" :id="migration.fonction"/>
							@{{ migration.nom }}
						</div>

						<div class="etat_fonction">
							<img v-if="migration.loader" src="{{ asset('eden/images/ajax_loader.gif') }}"  />
							<template v-else-if="migration.status === 1">
								<span>Durée de @{{ migration.duree }} s</span>
								<i class="fas fa-check"></i> 
							</template>
							<i v-else-if="migration.status === 2" class="fas fa-times" ></i>
						</div>
					</label>

                    <label class="test_apres_migrations" for="test_apres_migrations">
						<input type="checkbox" v-model="lancer_test_apres_migration" id="test_apres_migrations">
						Passer aux tests URL une fois terminé
                    </label>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('donnees_pour_vuejs_data')

	migrations: [],
    fonctions_migrations: [
        { fonction: "mise_a_jour_composer", nom: "Mise à jour du composer" },
        { fonction: "lancement_script_avant", nom: "Lancer les scripts avant migration" },
        { fonction: "maj_version_eden", nom: "Mise à jour des versions EDEN" },
        { fonction: "maj_traductions", nom: "Traductions" },
        { fonction: "generer_tables_champs_libres", nom: "Tables et champs libres" },
        { fonction: "maj_vue_sql", nom: "Vue SQL" },
        { fonction: "maj_rapports_libres", nom: "Rapports" },
        { fonction: "generer_listes_libres", nom: "Listes libres" },
        { fonction: "maj_formulaires_libres", nom: "Formulaires libres" },
        { fonction: "maj_sous_formulaires", nom: "Sous-formulaires" },
        { fonction: "maj_utilisateurs_easydev", nom: "Utilisateurs Ateja" },
        { fonction: "maj_crons", nom: "Crons" },
        { fonction: "generer_licences", nom: "Licences" },
        { fonction: "lancement_script_apres", nom: "Lancer les scripts après migration" },
        { fonction: "generer_fichiers_composants", nom: "Générer les composants modules" },
        { fonction: "generer_fichiers_composants_listes", nom: "Générer les composants listes" }
    ],
	erreur_migration:false,
    lancer_test_apres_migration: true,
    lancement_migrations: false,
    temps_execution: null,
	redirection : false,

@endpush
@push('donnees_pour_vuejs_mounted')

	attributs_migration = {
		a_lancer : true,
		loader: false,
		duree : null,
		status : 0,
	};

	this.migrations = this.fonctions_migrations.map(migration => {
		return {
			...attributs_migration,
			fonction: migration.fonction,
			nom: migration.nom
		};
    });
		
@endpush
@push('donnees_pour_vuejs_methods')

	lancer_migrations: async function(event) {

		this.lancement_migrations = true;

		this.migrations.map((m) => {
			m.status = 0;
			m.loader = false;
			m.duree = null;
			return m;
		})

		// paramètres communs
		var parametres = {
			url: "{{ route('maintenance.migrations_traitement') }}",
			dataType: "json",
			method: "POST"
		};

		this.temps_execution = null;
		this.erreur_migration = false;
		
		//parcours des migrations cochées
		var fonctions_a_lancer = this.migrations.filter(migration => migration.a_lancer === true);

		for (fonction of fonctions_a_lancer) {

			fonction.loader = true;
		
			var data = {
				fonction : fonction.fonction,
			};

			const debut = performance.now();
			
			try{
				var retour = await $.ajax({
					...parametres,
					data: data
				});
			
				//calcul de la durée de chaque migrations et de toutes les migrations
				const fin = performance.now();
				fonction.duree = Math.round(((fin - debut) / 1000) * 100) / 100;
				fonction.status = retour.success ? 1 : 2;

				if (!retour.success) {
					this.erreur_migration = retour.erreur;
					break;
				}
			}
			catch (error) {
				this.erreur_migration = {
					fichier: 'N/A',
					ligne: 'N/A',
					message: 'Une erreur est survenue',
					stacktrace: error.responseText || 'Pas de stacktrace disponible'
				};
				break;
			} 
			finally {
				fonction.loader = false;
			}
		}

		if(this.erreur_migration === false) {
			var duree_totale = fonctions_a_lancer.reduce((total, migration) => total + (migration.duree || 0), 0);
			duree_totale = Math.round(duree_totale * 100) / 100
			this.temps_execution = duree_totale;
		} 
	
		if(this.lancer_test_apres_migration && this.erreur_migration === false){

			@if(in_array(env('APP_ENV'), array('prod', 'production', 'PROD', 'PRODUCTION')))
				setTimeout('document.location = "{{ route('maintenance.test_url.liste') }}";', 500);
			@else
				setTimeout('document.location = "{{ route('maintenance.test_url.liste', ['tous']) }}";', 500);
			@endif

			this.redirection = true;
		}
		else
			await this.recuperer_rappels_version();

		this.lancement_migrations = false;

	},


	tout_selectionner_deselectionner: function(tout_cocher) {

		this.migrations.map((m) => {
			m.a_lancer = tout_cocher;
			return m;
		});

		this.lancer_test_apres_migration = tout_cocher;
	},

@endpush

