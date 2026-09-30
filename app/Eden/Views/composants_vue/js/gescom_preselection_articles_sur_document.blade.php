<script>
const gescom_preselection_articles_sur_document = Vue.component('gescom-preselection-articles-sur-document', {
    template: `<div>
            <li>
		<div class="css_header_type_categorie">
			<span @click="toggle" v-bind:class="{ active: open }">@{{model.modele_famille.nom}} <i class="fa fa-arrow-down" aria-hidden="true"></i></span>
		</div>
		<div class="css_contenu_type_categorie" v-show="open">
			<div style="padding-left: 100px;">
				<gescom-preselection-articles-sur-document v-for="(sous_famille, index) in model.sous_familles" :key="index" :model="sous_famille"></gescom-preselection-articles-sur-document>
			</div>
			<div>
				<div class="col-md-12" v-for="article in model.articles">
					<div class="custom-control custom-checkbox">
						<input type="checkbox" class="js_selection_produit_pour_document" :value="article.id" :famille="article.famille_id" v-on:click="choix_articles_preselectionnes(article)" :id="article.id">
						<label class="custom-control-label" :for="article.id">@{{article.designation}}</label>
					</div>
					{{-- <input type="checkbox" class="js_selection_produit_pour_document" :value="article.id" :famille="article.famille_id" v-on:click="choix_articles_preselectionnes(article)" /> <span style="color:red"> @{{article.designation}}</span> --}}
				</div>
			</div>
		</div>
	</li>
        </div>`,
   props: {
		model: Object
	},
	data: function() {
		return {
			open: false,
			// famille_active: []
		}
	},
	computed: {
		isFolder: function () {
			return true
		}
	},
	methods: {

		toggle: function () {
			if (this.isFolder) {
				this.open = !this.open;
			}
		},
		choix_articles_preselectionnes: function(article) {

			// on recupère l'index de l'article
			var index = vue_instance.articles_preselection.indexOf(article.id);

			// l'id n'existe pas dans le tableau, donc on l'ajoute, sinon on supprime
			if(index == -1) {
				vue_instance.articles_preselection.push(article.id)
			}
			else {
				vue_instance.articles_preselection.splice(index, 1)
			}

		},

	},
});
</script>