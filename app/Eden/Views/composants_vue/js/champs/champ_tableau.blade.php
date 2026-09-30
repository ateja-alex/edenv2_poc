<script>
const champ_tableau = Vue.component('champ-tableau', {
    template: `<div>
             <div class="row">
		<div class="col-md-12">
			<!-- tableau -->
			<table class="table table-bordered table-hover" width="100%">
				<thead>

					<tr>
						<template v-for="colonne in colonnes">
							<th>@{{ colonne }}</th>
						</template>

						<th v-if="!lecture_seule">@traduction('composant.champ_tableau.actions')</th>

					</tr>

				</thead>

				<tbody>

					<template v-for="(ligne,index_ligne) in modele[nom_sql]">
						<tr>
							<td v-for="(colonne,index_colonne) in colonnes">
								<input type="text" :name="name+'['+index_ligne+']['+index_colonne+']'" v-model="modele[nom_sql][index_ligne][index_colonne]" v-if="!lecture_seule">
								<span v-else>@{{ modele[nom_sql][index_ligne][index_colonne] }}</span>
							</td>
							<td v-if="!lecture_seule">
								<i class="fa fa-trash" @click="supprimer_ligne(index_ligne)" aria-hidden="true"></i>
							</td>
						</tr>
					</template>

				</tbody>
			</table>
		</div>

		<div class="col-md-12" style="text-align: left;" v-if="!lecture_seule">
			<i class="css_action_icon mineur fa fa-plus-circle" @click="ouvrir_modale()" aria-hidden="true"></i>
			{{-- <i class="css_action_icon mineur fa fa-floppy-o" @click="enregistrer_modification()" aria-hidden="true"></i> --}}
		</div>

		<!-- Modal ajout element -->
		<div class="modal fade" :id="id_random" tabindex="-1" role="dialog" aria-hidden="true">
			<div class="modal-dialog modal-lg" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">@traduction('composant.champ_tableau.ajout_dune_ligne')</h5>
					</div>
					<div class="modal-body css_form">
						<div class="row">
							<template v-for="(colonne,index) in colonnes">
								<div class="col-md-2">
									@{{ colonne }}
								</div>
								<div class="col-md-4">
									<input type="text" v-model="nouvelle_ligne[index]">
								</div>
							</template>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-dismiss="modal">@traduction('composant.champ_tableau.fermer')</button>
						<div class="btn btn-primary" @click="ajouter_nouvelle_ligne()">@traduction('composant.champ_tableau.enregistrer')</div>
					</div>
				</div>
			</div>
		</div>
	</div>
        </div>`,
   props: {

		nom_sql: '',
        name: '',
		type_element: '',
		lecture_seule: {
            type: Boolean | Number,
            default : false,
        },
		modele: '',
	},
	data: function () {
		return {

			colonnes : {},
			contenu : {},
			nouvelle_ligne : {},
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

		contenu_tableau_json: function(){

			return JSON.stringify(this.modele[this.nom_sql]);
		},
	},
	methods: {

		ouvrir_modale: function(){

			var id_random = this.id_random;

			$('#'+id_random).modal('show');
		},

		ajouter_nouvelle_ligne: function() {

			var id_random = this.id_random;

			var nouvelle_ligne = this.nouvelle_ligne;

			var cle = Object.keys(this.modele[this.nom_sql]).length;
			this.modele[this.nom_sql][cle] = nouvelle_ligne;
			this.nouvelle_ligne = {};

		},

		supprimer_ligne: function(index) {

            this.$delete(this.modele[this.nom_sql], index);

			this.$forceUpdate();
		},
	},
	created: function() {

		var composant = this;

		var modele_id = this.modele.id;

		$.get({

			url: "/eden/champ/tableau/"+this.type_element+"/"+modele_id+"/"+this.nom_sql,
		}).done(function(donnees) {

			var donnees = JSON.parse(donnees);
			composant.colonnes = donnees.colonnes;

			composant.modele[composant.nom_sql] = {};

			var tableau = donnees.donnees_tableau;

			if(tableau.length > 0)
				composant.modele[composant.nom_sql] = tableau;

		});
	},

});
</script>
