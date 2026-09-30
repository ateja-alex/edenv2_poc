<script>

const recapitulatif_campagne_prospection = Vue.component('recapitulatif_campagne_prospection', {

    template: `

  <div class="row">
    <div class="col-md-12">
      <div class="card mb-3">
        <div class="card-header">
          <h4 style="width:100%">
            @traduction('module_sur_fiche.campagne_de_prospection.recapitulatif')
          </h4>
        </div>
        <div class="card-body">
            <div style="display:flex;align-items: center;">
                <div class="col-md-2" :style="'display: flex;justify-content: center;flex-direction: column;align-items: center;gap: 10px;color:'+couleur_score">
                    <v-progress-circular
                          :size="75"
                          :width="10"
                          :value="tableau_recapitulatif.score_avancement"
                        >@{{ tableau_recapitulatif.score_avancement }} %</v-progress-circular>
                </div>
                <div class="col-md-10">
                    <div class="table-responsive" >
                        <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                          <thead>
                            <tr>
                              <th scope="col" v-for="nom_colonne in tableau_recapitulatif.tableau_header"><b v-html="nom_colonne"></b></th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr v-for="(colonnes, nom_ligne) in tableau_recapitulatif.valeurs" v-if="nom_ligne !== 'score_avancement'">
                              <td>
                                <b>@{{ nom_ligne }}</b>
                              </td>
                              <td v-for="colonne in colonnes">
                                @{{ colonne }}
                              </td>
                            </tr>
                          </tbody>
                        </table>
                    </div>
                </div>
          </div>
        </div>
      </div>
    </div>
  </div>

     `,

    props: { 

        campagne_id: '',

	},

    data: function(){

		return {
			
			tableau_recapitulatif: {}, 
		}

	},
	methods:{

		charge_donnees: function(){

			var vue_composant = this;

			$.post({
				
				url: "/eden/campagne_de_prospection/recapitulatif/" + vue_composant.campagne_id,
				dataType: "json",
				data:{},

			}).done(function(donnees) {

				vue_composant.tableau_recapitulatif = donnees;
			});
		},
	},    

	mounted: function() {
		
		this.charge_donnees();
	},

	computed: {

		couleur_score: function(){

            var couleur = 'green';

            var vue_composant = this;

            if (vue_composant.tableau_recapitulatif.score_avancement < 20)
                couleur = 'red';
            else if (vue_composant.tableau_recapitulatif.score_avancement < 60)
                couleur = 'orange';

            return couleur;
        }

	}
});
</script>