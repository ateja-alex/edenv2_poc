@extends('eden::rapports.rapport_base')

@section('contenu_rapport')

	<div class="css_fiche_bloc_filtres">

		<span class="badge" :class="{'badge-success': aujourdhui_seulement === true, 'badge-default': aujourdhui_seulement === false}" @click="aujourdhui_seulement = !aujourdhui_seulement;en_retard_seulement = false;seulement_7j = false;">@traduction('rapport.mes_taches.aujourdhui')</span>

		<span class="badge" :class="{'badge-success': en_retard_seulement === true, 'badge-default': en_retard_seulement === false}" @click="en_retard_seulement = !en_retard_seulement;aujourdhui_seulement = false;seulement_7j = false;">@traduction('rapport.mes_taches.en_retard')</span>

		<span class="badge" :class="{'badge-success': seulement_7j === true, 'badge-default': seulement_7j === false}" @click="seulement_7j = !seulement_7j;en_retard_seulement = false;aujourdhui_seulement = false;">@traduction('rapport.mes_taches.7_prochains_jours')</span>

		<span class="badge" :class="{'badge-success': taches_en_cours_seulement === true, 'badge-default': taches_en_cours_seulement === false}" @click="taches_en_cours_seulement = !taches_en_cours_seulement">@traduction('rapport.mes_taches.en_cours_seulement')</span>
	</div>
	<br/>

	<div class="row">
		<div class="col-sm-12" v-show="taches.length == 0">
			@traduction('rapport.mes_taches.aucune_tache_cliquez_sur')
			&laquo; <span class="css__lien" @click="tache_creer">@traduction('rapport.mes_taches.nouvelle_tache')</span> &raquo;
			@traduction('rapport.mes_taches.ci_dessus_pour_en_ajouter_une')
		</div>
	</div>

	<div v-for="tache in taches"

		v-show="
			(
				mes_taches_seulement === false
				||
				tache.affectation == {{id_utilisateur}}
			)
				&&
			(
				tache.terminee != 1
				||
				taches_en_cours_seulement === false
			)
				&&
			(
				( en_retard_seulement === true && tache.retard < 0 )
				||
				( seulement_7j === true && tache.sept_jours < 0 && tache.sept_jours >= -7 )
				||
				( aujourdhui_seulement === true && tache.retard == 0  )
				||
				( aujourdhui_seulement === false && en_retard_seulement === false && seulement_7j === false  )
			)"

		:id="'tache' + tache.id">
		<hr class="light-grey-hr">

		<div style="display: flex;justify-content: space-between;padding:10px;">
			<div>
				<span @click="terminer_tache(tache)">
					<i class="fa fa-check-square" style="font-size:20px;color:#2ecd99;cursor:pointer;" v-show="tache.terminee == 1"></i>
					<i class="fa fa-minus-square" style="font-size:20px;color:#ed6f56;cursor:pointer;" v-show="tache.terminee != 1"></i>
				</span>
				<span @click="ouvrir_tache(tache)" style="cursor: pointer;" :class="{'estTermine' : tache.terminee}">
					@{{ tache.titre }}
					<span v-show="tache.client !== null">
						(@{{ tache.client }})
					</span>
				</span>

			</div>
			<div>
				<span> @{{ tache.date_de_debut }} </span>
			</div>
		</div>
	</div>


<!-- Modal ajout tache -->
    <div id="modal_ajout_tache" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@traduction('rapport.mes_taches.gestion_des_taches')</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <input type="hidden" name="id" v-model="tache.id" />

                <div class="modal-body">

                    <form action="#" id="formulaire_ajout_tache" method="post" class="css_form">

                        <div v-if="this.$root.type_element == 'client'">
                            <input type="hidden" :name="this.$root.type_element+'_id'" :value="this.$root.element_id" />
                        </div>

                        <div v-else>
                            <input type="hidden" name="type_element" :value="this.$root.type_element" />
                            <input type="hidden" name="element_id" :value="this.$root.element_id" />
                        </div>

                        {!! formulaire('tache') !!}

                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" @click="terminee(1)" v-show="tache.id != '' && tache.id != undefined && tache.terminee == 0">@traduction('rapport.mes_taches.terminee')</button>
                    <button type="button" class="btn btn-warning" @click="terminee(0)" v-show="tache.id != '' && tache.id != undefined && tache.terminee == 1">@traduction('rapport.mes_taches.annuler_terminee')</button>
                    <button type="button" class="btn btn-danger" @click="supprimer" v-show="tache.id != ''">@traduction('rapport.mes_taches.supprimer')</button>
                    <button type="button" class="btn btn-primary" @click="enregistrer">@traduction('rapport.mes_taches.enregistrer')</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('header_rapport_'.$id_rapport)

	<div class="card-header">
		<h4>
			<span class="fa fa-tasks"></span>
			 @traduction('rapport.mes_taches.taches')
			<span class="css__lien" @click="tache_creer"><i class="fa fa-fw fa-plus-square"></i> @traduction('rapport.mes_taches.nouvelle_tache')</span>

		</h4>
	</div>
@endsection

@push('donnees_pour_vuejs_data')
	taches: {!! $taches !!},
	tache: {

		id: '',
		utilisateur_id: {{id_utilisateur}},
		date_de_debut: '{{date('Y-m-d H:00:00')}}',
		date_de_fin: '{{date('Y-m-d H:00:00', strtotime("now +1 hour "))}}',
	},
	nouvelle_tache: {

		id: '',
		utilisateur_id: {{id_utilisateur}},
		date_de_debut: '{{date('Y-m-d H:00:00')}}',
		date_de_fin: '{{date('Y-m-d H:00:00', strtotime("now +1 hour "))}}',
	},
	taches_en_cours_seulement: true,
	mes_taches_seulement: true,
	aujourdhui_seulement: true,
	en_retard_seulement: false,
	seulement_7j: false,
@endpush

@push('donnees_pour_vuejs_methods')

	tri_par_date_de_fin(arrays) {

		var date_du_jour = new Date();
		var taches_a_afficher = [];

		//on tries les taches par date
	  	var taches = arrays.slice().sort(function(a, b){
	    	return (a.date_de_fin > b.date_de_fin) ? 1 : -1;
		});

		taches.forEach(function(tache){

			var date_de_fin = new Date(tache.date_de_fin);
			//on récupère la différence de jour grace au time
			var difference_en_time = date_du_jour.getTime() - date_de_fin.getTime();
			var difference_en_jours = Math.round(difference_en_time / (1000 * 3600 * 24));
			//console.log(difference_en_jours);

			if(tache.terminee){

				if(difference_en_jours < 30) {

					taches_a_afficher.push(tache);
				}
			}
			else {

				taches_a_afficher.push(tache);
			}
		})

	  	return taches_a_afficher
	},


	affichage_date: function(date_de_debut, date_de_fin) {

		moment.locale('fr');
        console.log(date_de_debut, date_de_fin);
		if ( date_de_debut.substr(0,10) == date_de_fin.substr(0,10) ) {

			return moment(date_de_debut, 'YYYY-MM-DD hh:mm:ss').format('dddd Do MMMM YYYY [de] hh:mm') + ' à ' + moment(date_de_fin, 'YYYY-MM-DD hh:mm:ss').format('hh:mm');
		}

		return 'Du ' + moment(date_de_debut, 'YYYY-MM-DD hh:mm:ss').format('dddd Do MMMM YYYY hh:mm') + ' jusqu\'au ' + moment(date_de_fin, 'YYYY-MM-DD hh:mm:ss').format('dddd Do MMMM YYYY [à] hh:mm');
	},

    terminee: function(statut_terminee) {

        var vue_composant = this;

        loading(true);

        var url = "eden/element/tache/"+$('#modal_ajout_tache input[name=id]').val()+"/enregistrer";

        // on enregistre la modification
        $.post({

            url: url,
            dataType: "json",
            method: 'POST',
            data: {

                terminee: statut_terminee
            }
        }).done(function(donnees) {

            // On retire le loader
            loading(false);

            $('#modal_ajout_tache').modal('hide');

            vue_composant.tache_actualiser();
        });

    },

    supprimer: async function() {

        var vue_composant = this;

        if(!await confirm_eden(vue_composant.$root.traduction('interface.listes.etes_vous_certain')))
            return false;

        loading(true);

        // on fait un appel ajax pour supprimer
        $.get({

            url: "eden/element/tache/"+vue_composant.$data.tache.id+"/supprimer",
            dataType: "json",
            method: 'GET'
        }).done(async function(donnees) {

            if(donnees.retour !== true) {

                loading(false);

                await erreur(donnees.retour);
                return;
            }

            $('#modal_ajout_tache').modal('hide');

            // on actualise la liste
			vue_composant.tache_actualiser();
        });
    },

    enregistrer: function() {

        var vue_composant = this;

        // on fait un appel ajax

        if($('#modal_ajout_tache input[name=id]').val() != '' && $('#modal_ajout_tache input[name=id]').val() != '0')
            var url = "eden/element/tache/"+$('#modal_ajout_tache input[name=id]').val()+"/enregistrer";
        else
            var url = "eden/element/tache/creer";

        // On affiche le loader
        loading();

        // on enregistre les infos du champ libre
        $.post({

            url: url,
            dataType: "json",
            method: 'POST',
            data: $('#formulaire_ajout_tache').serialize()
        }).done(async function(donnees) {

            // On retire le loader
            loading(false);

            if(donnees.retour !== true) {

                await erreur(donnees.retour);
                return;
            }

            $('#modal_ajout_tache').modal('hide');

	        vue_composant.tache_actualiser();
        });
    },

	terminer_tache: function(tache) {


		if(tache.terminee == 1) {

			tache.terminee = 0;
		}
		else {

			tache.terminee = 1;
			tache.justeterminee = 1;
		}

		var url = "eden/element/tache/"+tache.id+"/enregistrer";

		var nouveau_statut = tache.terminee;

		// on enregistre la modification
		$.post({

			url: url,
			dataType: "json",
			method: 'POST',
			data: {

				terminee: nouveau_statut
			}
		});

	},

	tache_creer: function() {

		$('#modal_ajout_tache').modal('show');

		// on réinitialise le contact
		this.tache = this.nouvelle_tache;
	},

	ouvrir_tache: function(tache) {

		$('#modal_ajout_tache').modal('show');

		this.tache = tache
	},

	tache_modifier: function(id) {

		$.each(vue_instance.taches, function(osef, tache) {

			if(tache.id == id) {

				vue_instance.tache = tache;
			}
		});

		$('#modal_ajout_tache').modal('show');
	},

	tache_actualiser: function() {

		loading(true);

		$.get({

			url: 'eden/accueil/commercial/taches_actualiser',
			dataType: "json"
		}).done(function(taches) {

			// On retire le loader
			loading(false);

			vue_instance.taches = taches;
		});
	},

@endpush


