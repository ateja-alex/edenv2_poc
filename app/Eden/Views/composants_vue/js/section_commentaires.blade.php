<script>
const section_commentaires = Vue.component('section-commentaires', {
    template: `<div><div class="ibox ">
		<div class="ibox-title">
			<h5 class="d-flex align-items-center">
				@traduction('composant.section_commentaires.commentaires')

				@if(fonctionnalite('gestion_de_projet'))
					<select class="js_filtre_projets" style="width: 130px;font-size:12px;margin-left:5px" v-model="projet_selectionne" v-show="this.champ_parent == 'client_id'" @change="charge_donnees">
						<option value="-1" v-html="$root.traduction('composant.section_commentaires.tous_les_projets')"></option>
						<option v-for="projet in projets" :value="projet.id" >@{{ projet.nom }}</option>
					</select>
				@endif

				<span @click="creer()" class="css_ajouter_element ml-auto" data-toggle="tooltip" data-placement="top" :title="this.$root.traduction('composant.section_commentaires.nouveau')">
					<i class="css_action_icon secondaire fa fa-fw fa-plus-square"></i>
				</span>

			</h5>
		</div>
		<div class="ibox-content">
			<div>
				<div class="feed-activity-list flex-commentaires-fiche">
                    <div class="feed-element css_fiche_commentaire" v-for="commentaire in commentaires" v-if="commentaire.commentaire !== '' && commentaire.commentaire !== null">
						<div class="media-body">
							<div class="row">
								<div class="col-md-2" style="text-align: center;">
									<img alt="image" class="rounded-circle" style="max-width: 50px; max-height: 50px; margin-left: 15px;" :src="commentaire.cree_par | affiche_utilisateur_avatar" />
								</div>
								<div class="col-md-8 css_fiche_commentaire_commentaire">
									<strong>@{{ commentaire.cree_par | affiche_utilisateur }}</strong>
									-
									<span class="fa fa-pencil-alt" style="color: #878787;cursor:pointer;" @click="modifier(commentaire.id)"></span>
									&nbsp;
									<span class="fa fa-trash" style="color: #878787;cursor:pointer;" @click="supprimer(commentaire)"></span><br>
									<span v-html="$options.filters.nl2br(commentaire.commentaire)"></span>

									<br/><span style="color: #878787;">@{{ commentaire.cree_le | datetime_relatif }}</span>
								</div>
							</div>

						</div>
					</div>
				</div>
				<!--
				<br/>
				<button class="btn btn-primary btn-xs btn-block m-t"><i class="fa fa-arrow-down"></i> Afficher plus</button>
				-->

			</div>

		</div>
        </div>

        <div :id="'modale_commentaires_'+id_random">
            <!-- modale edition commentaire -->
            <template v-if="modale_commentaires">
                <transition name="modal">
                    <div class="modal-mask">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">@traduction('composant.section_commentaires.commentaires')</h5>
                                    <button type="button" class="close" @click="modale_commentaires = false;" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body css_form" >
                                        {!! management('message')->champ('commentaire')->cree() !!}
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" @click="modale_commentaires = false;" >@traduction('composant.section_commentaires.fermer')</button>
                                    <div class="btn btn-primary" @click="enregistrer()">@traduction('composant.section_commentaires.enregistrer')</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </transition>
            </template>
        </div>
        </div>`,
   props: {

		type_element_parent: '',
		parent_id: 0,
		champ_parent: {},
	},
	data: function () {
		return {
			commentaires: {},
			message: {!! modele_par_defaut('message') !!},
			projets: {},
			projet_selectionne: -1,
            modale_commentaires: false,
		}
	},
	computed: {

		id_random: function() {

			length = 15;

			var chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz'.split('');

			if (! length) {
				length = Math.floor(Math.random() * chars.length);
			}

			var str = '';
			for (var i = 0; i < length; i++) {
				str += chars[Math.floor(Math.random() * chars.length)];
			}

			return str;
		}
	},
	methods: {

		creer: function() {

			this.message = {!! modele_par_defaut('message') !!};

			this.modale_commentaires = true;
		},

		modifier: function(commentaire_id) {

			var composant = this;

			// on va chercher l'élément
			$.get({

				url: "/eden/element/message/"+commentaire_id,
			}).done((donnees) => {

				composant.message = donnees;
                this.modale_commentaires = true;

            });

		},

		enregistrer: function() {

			loading(true);

			var composant = this;
			var id_random = this.id_random;

			var data = {

				commentaire: composant.message.commentaire,
				type_element: composant.type_element_parent,
				element_id: composant.parent_id,
			}

			$.post({

				url: "/eden/element/message/"+composant.message.id+"/enregistrer",
				dataType: "json",
				method: "post",
				data: data
			}).done(async (donnees) => {

				loading(false);

				if(donnees.retour !== true) {

					await erreur(donnees.retour);
					return;
				}

				composant.charge_donnees();

                this.modale_commentaires = false;
			});
		},

		supprimer: async function(commentaire) {

			var composant = this;

			if (!await confirm_eden(this.$root.traduction('composant.section_commentaires.voulez_vous_supprimer_ce_commentaire')))
				return;

			loading(true);

			var data = {

				commentaire: commentaire.id,
				type_element: this.type_element_parent,
				element_id: this.parent_id,
			}

			$.get({

				url: "/eden/element/message/"+commentaire.id+"/supprimer",
				dataType: "json",
				data: data
			}).done(async function(donnees) {

				if(donnees.retour !== true) {

					await erreur(donnees.retour);
					return;
				}

				composant.charge_donnees();
            });


		},

		charge_donnees: function() {


			var type_element_parent = this.type_element_parent;
			var element_parent_id = this.parent_id;
			var composant = this;

			if(this.champ_parent != undefined) {

				var element_parent_champ = this.champ_parent;
				var type_element_parent = element_parent_champ.replace('_id', '');
			}

			// on va chercher la liste des commentaires
			var url = "/eden/fiche/"+type_element_parent+"/"+element_parent_id+"/commentaires";

			if(this.projet_selectionne != -1) {

				url = "/eden/fiche/"+type_element_parent+"/"+element_parent_id+"/commentaires/"+this.projet_selectionne;
			}

			$.get({

				url: url,
			}).done(function(retour) {

				composant.commentaires = retour.donnees;
				composant.projets = retour.projets;

				loading(false);

			});
		}
	},
	created: function() {

		this.charge_donnees();

		/*<img alt="image" class="rounded-circle" style="max-width: 50px; max-height: 50px;" :src="$options.filters.utilisateur_avatar(commentaire.cree_par)">*/
	},
	mounted: function(){

		$('#stack_modales_composants').append($('#modale_commentaires_'+this.id_random));

		this.$forceUpdate();

	},
});

</script>
