<script>
const section_activites = Vue.component('section-activites', {
    template: `<div>
           <div class="ibox">
		<div class="ibox-title">
			<h5 style="margin-bottom: 10px;" class="d-flex align-items-center">
				Activités
			</h5>
		</div>
		<div class="ibox-content">
			<div>
				<div class="feed-activity-list" style="padding-left: 20px;">
					<div class="feed-element css_fiche_commentaire" v-for="(activites_par_date, date) in activites">
						<div style="padding-left: 10px; font-size: 15px; padding: 10px;"><b><i class="fa fa-calendar-alt" style="margin-right: 5px;"></i> @{{ date | date_relatif_sans_heure }}</b></div>
						<div class="feed-element css_fiche_commentaire" v-for="activite in activites_par_date">
							<div class="media-body" style="padding: 0px 20px;">
								<div class="css_fiche_commentaire_commentaire" style="border-bottom: 0px solid white;">
									<img alt="image" class="rounded-circle" style="margin-right: 11px; max-width: 36px; max-height: 36px; margin-left: 15px; float: left;" :src="activite.cree_par | affiche_utilisateur_avatar" />
									<strong>@{{ activite.cree_par | affiche_utilisateur }} </strong> <span style="color: #878787;">à @{{ activite.cree_le | time }}</span>

									<br/><span v-html="$options.filters.nl2br(activite.commentaire)"></span>
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
        </div>`,
   props: {

		type_element: '',
		element_id: 0,
		contexte: '',
	},
	data: function () {
		return {
			activites: {},
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
		},


	},
	methods: {

		charge_donnees: function() {

			var type_element = this.type_element;
			var element_id = this.element_id;

			var composant = this;

			var url = "/eden/fiche/"+type_element+"/"+element_id+"/activites";

			// on va chercher la liste des échanges
			$.get({

				url: url,
			}).done(function(retour) {

				composant.activites = retour;
			});

		},

	},
	created: function() {

		this.charge_donnees();
	},
});
</script>
