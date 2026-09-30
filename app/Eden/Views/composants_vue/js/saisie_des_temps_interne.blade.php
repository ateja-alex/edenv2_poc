<script>
const saisie_des_temps_interne = Vue.component('saisie-des-temps-interne', {
    template: `
        <div>
            <div class="row">
				<div class="col-md-12">
					<div class="card mb-3">
						<div class="card-header row">
							<h4 style="width: 100%" class="d-flex justify-content-between col-sm-12 col-xs-12 col-md-8">
								@traduction('interface.saisie_des_temps_interne.titre')
							</h4>
							<div class="col-sm-12 col-xs-12 col-md-4">
								<div class="row">
									<button class="btn btn-secondary btn-sm col-sm-2 col-xs-2" style="padding:5px;border:solid 1px #666;border-radius:0px;" @click="changement_date('-7')">&lt;&lt;</button>
									<button class="btn btn-secondary btn-sm col-sm-2 col-xs-2" style="padding:5px;border:solid 1px #666;border-radius:0px;" @click="changement_date('-1')">&lt;</button>
									<input type="date" class="col-sm-4 col-xs-12" v-model="date_selectionne" @change="changement_date()">
									<button class="btn btn-secondary btn-sm col-sm-2 col-xs-2" style="padding:5px;border:solid 1px #666;border-radius:0px;" @click="changement_date('+1')">&gt;</button>
									<button class="btn btn-secondary btn-sm col-sm-2 col-xs-2" style="padding:5px;border:solid 1px #666;border-radius:0px;" @click="changement_date('+7')">&gt;&gt;</button>
								</div>
							</div>
						</div>
						<div class="card-body">

							{{-- Filtre date --}}

							{{-- Progress --}}
							<div class="row" style="margin-top: 4%;">
								<div class="col-sm-12">
									<progress :class="total_journee >= 7 ? 'progress_complete' : ''" :value="total_journee" max="7" style="width: 80%;margin-left: 10%;height: 23px;"></progress>
								</div>
								<div class="col-sm-12" style="text-align: center;">
									<h5><span style="color: red;" v-if="total_journee < 7">@{{ total_journee }}</span> <span v-if="total_journee >= 7">@{{ total_journee }}</span> / 7h</h5>
								</div>
								<div class="col-sm-12" style="text-align: center;margin-top: 2%">
									<span class="badge badge-succes" @click="ajouter_scrum()">Scrum</span>
								</div>
							</div>

							{{-- Tableau --}}
							<div class="table-responsive" style="margin-top: 2%">
                            	<table class="table table-hover table_responsive_saisie_temps" style="width: 100%" cellspacing="0" >
                                    <thead>
                                        <tr class="css_table_titre" style="font-weight: bold" >
                                            <td style="width: 5%"></td>
                                            <td style="width: 25%">@traduction('interface.saisie_des_temps_interne.colonnes.projet')</td>
                                            <td style="width: 30%">@traduction('interface.saisie_des_temps_interne.colonnes.description')</td>
                                            <td style="width: 15%">@traduction('interface.saisie_des_temps_interne.colonnes.temps')</td>
                                            <td style="width: 10%">@traduction('interface.saisie_des_temps_interne.colonnes.actions')</td>
                                        </tr>
                                    </thead>
                                        <tbody >
                                        	{{-- Nouvelle saisie --}}
                                            <tr class="css_table_contenu js_table_contenu" >
                                            	<td></td>
												<td>
													<select style="width: 100%" @change="nouvelle_feuille_de_temps_interne(nouvelle_feuille_de_temps)" v-model="nouvelle_feuille_de_temps.element_id">
														<option value=""></option>
														<option v-for="projet in projets" :value="projet.id"> @{{ projet.affichage_select }}</option>
													</select>
												</td>
												<td>
													<input type="text" name="" style="width: 100%" @change="nouvelle_feuille_de_temps_interne(nouvelle_feuille_de_temps)" v-model="nouvelle_feuille_de_temps.tache">
												</td>
												<td>
                                                  <champ-montant
                                                      :modele="nouvelle_feuille_de_temps"
                                                      nom_sql="duree"
                                                      style_input="width: 100%"
                                                  ></champ-montant>
												</td>
												<td></td>
											</tr>

											{{-- Feuilles de temps déjà saisis --}}
											<tr class="css_table_contenu js_table_contenu" v-for="(feuille_de_temps_saisie,index) in feuilles_de_temps">
												<td>
                                                    <i class="far fa-copy" title="Copie le projet dans la nouvelle saisie" data-toggle="tooltip" @click="nouvelle_feuille_de_temps.element_id = feuille_de_temps_saisie.element_id"></i>
                                                </td>
												<td>
													<select style="width: 100%" v-model="feuille_de_temps_saisie.element_id" @change="nouvelle_feuille_de_temps_interne(feuille_de_temps_saisie)">
														<option value=""></option>
                                                        <option v-for="projet in projets" :value="projet.id"> @{{ projet.affichage_select }}</option>
													</select>
												</td>
												<td>
													<input type="text" name="" style="width: 100%" v-model="feuille_de_temps_saisie.tache" @change="nouvelle_feuille_de_temps_interne(feuille_de_temps_saisie)">
												</td>
												<td>
                                                  <champ-montant
                                                      :modele="feuille_de_temps_saisie"
                                                      nom_sql="duree"
                                                      style_input="width: 100%"
                                                  ></champ-montant>
												</td>
												<td>
                                                    <i class="fa fa-trash" title="Supprimer" data-toggle="tooltip" @click="supprimer_feuille_de_temps(feuille_de_temps_saisie, feuilles_de_temps, index);"></i>
                                                </td>
											</tr>
                                        </tbody>
                                </table>
                            </div>
						</div>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-md-12">
					<div class="card mb-3">
						<div class="card-header">
							<h4 style="width: 100%" class="d-flex justify-content-between">
								@traduction('interface.saisie_des_temps_interne.temps_de_la_semaine')
							</h4>
						</div>
						<div class="card-body">
							<div class="table-responsive">
                            	<table class="table table-bordered table-hover" id="tableau_recapitulatif_creance" width="100%" cellspacing="0" >
                                    <thead>
                                        <tr class="css_table_titre" style="font-weight: bold" >
                                            <td></td>
                                            <td v-for="(jour,index) in tableau_semaine" v-if="index != 'total_semaine'" :class="{'table-warning' : jour.date == date_selectionne }">
                                                @{{ jour.nom_jour }}
                                            </td>
                                            <td>@traduction('interface.saisie_des_temps_interne.total')</td>
                                        </tr>
                                    </thead>
                                        <tbody >
                                        	<tr class="css_table_contenu js_table_contenu" v-for="ligne_projet in projets_presents">
												<td><b v-for="projet in projets" v-if="projet.id == ligne_projet">@{{ projet.chaine_affichage }}</b></td>
												<td>@{{ tableau_semaine[0]['heures'][ligne_projet] > 0 ? parseFloat(tableau_semaine[0]['heures'][ligne_projet]) : 0 }}h</td>
												<td>@{{ tableau_semaine[1]['heures'][ligne_projet] > 0 ? parseFloat(tableau_semaine[1]['heures'][ligne_projet]) : 0 }}h</td>
												<td>@{{ tableau_semaine[2]['heures'][ligne_projet] > 0 ? parseFloat(tableau_semaine[2]['heures'][ligne_projet]) : 0 }}h</td>
												<td>@{{ tableau_semaine[3]['heures'][ligne_projet] > 0 ? parseFloat(tableau_semaine[3]['heures'][ligne_projet]) : 0 }}h</td>
												<td>@{{ tableau_semaine[4]['heures'][ligne_projet] > 0 ? parseFloat(tableau_semaine[4]['heures'][ligne_projet]) : 0 }}h</td>
												<td>@{{ totaux_projets.totaux_semaine_projet[ligne_projet] > 0 ? parseFloat(totaux_projets.totaux_semaine_projet[ligne_projet]) : 0 }}h</td>
											</tr>
                                            <tr class="css_table_contenu js_table_contenu" style="font-weight: bold;">
												<td>@traduction('interface.saisie_des_temps_interne.total')</td>
												<td v-for="(jour,index) in tableau_semaine" v-if="index != 'total_semaine'">
													<span style="color: red" v-if="jour.heures_journee < 7">@{{ parseFloat(jour.heures_journee) }}h</span>
													<span v-if="jour.heures_journee >= 7">@{{ parseFloat(jour.heures_journee) }}h</span>
												</td>
												<td>
													<span style="color: red" v-if="tableau_semaine.total_semaine < 35">@{{ parseFloat(tableau_semaine.total_semaine) }}h</span>
													<span v-if="tableau_semaine.total_semaine >= 35">@{{ parseFloat(tableau_semaine.total_semaine) }}h</span>
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
    `,
    props:{
    },
    data : function(){

        return {
            projets: {},
            feuilles_de_temps: {},
            date_selectionne: null,
            modele_feuille_de_temps : {
                date: '',
                utilisateur_id: this.$root.moi.id,
                type_element: 'projet',
                element_id: '',
                duree: '',
                tache: '',
            },
            nouvelle_feuille_de_temps: {},
            tableau_semaine: {},
            projets_presents: {},
            totaux_projets: {},
        }
    },
    mounted : function(){

        this.charger_donnees(true);

        this.$on('maj_champ_montant',(donnees) => {

            this.nouvelle_feuille_de_temps_interne(donnees.modele);
        });
    },
    methods: {

        nouvelle_feuille_de_temps_interne: function(nouvelle_feuille_de_temps_saisie) {

            var url = '{{ route('base_eden.element.creer','feuille_de_temps', false) }}';

            if(nouvelle_feuille_de_temps_saisie.id > 0)
                url = "eden/element/feuille_de_temps/"+nouvelle_feuille_de_temps_saisie.id+"/enregistrer/";

            if(nouvelle_feuille_de_temps_saisie.duree == '' || nouvelle_feuille_de_temps_saisie.tache == '' || nouvelle_feuille_de_temps_saisie.element_id == '')
                return;

            loading(true);

            // on récupère le projet
            $.post({

                url: url,
                dataType: "json",
                data: nouvelle_feuille_de_temps_saisie,
            }).done(async (retour) => {

                if(retour.retour === true) {
                    if(nouvelle_feuille_de_temps_saisie.id > 0)
                        this.feuilles_de_temps.push(retour.element);
                }
                else
                    await erreur(retour.retour);

                this.charger_donnees();

                loading(false);
            });
        },

        supprimer_feuille_de_temps: async function(feuille_de_temps, infos_projet, index) {

            if(!await confirm_eden('{!! traduction('interface.alerte.attention') !!}',"Êtes-vous certain ?",'{!! traduction('interface.modales.oui') !!}','{!! traduction('interface.modales.non') !!}'))
                return false;

            loading(true);

            // on récupère le projet
            $.get({

                url: "eden/element/feuille_de_temps/"+feuille_de_temps.id+"/supprimer",
                dataType: "json"
            }).done(function() {

                infos_projet.splice(index, 1);

                loading(false);

                vue_instance.charger_donnees();

            });
        },

        changement_date: function(changement = false){

            if(changement !== false){

                date = new Date(this.date_selectionne);
                date.setDate(eval('date.getDate() '+changement));
                this.date_selectionne = date.getFullYear()+'-'+(1+date.getMonth()).toString().padStart(2, '0')+'-'+date.getDate().toString().padStart(2, '0')
            }

            this.charger_donnees();
        },

        charger_donnees: function(initialisation = false){

            loading(true);

            if(this.date_selectionne == null){

                this.$set(this,'date_selectionne',this.$root.aujourdhui);

                this.modele_feuille_de_temps.date = this.date_selectionne;
            }

            this.nouvelle_feuille_de_temps = structuredClone(this.modele_feuille_de_temps);

            // on récupère le tableau bien mis en forme
            $.get({

                url: "eden/saisie_des_temps/interne/informations/"+this.date_selectionne+"/"+initialisation,
                dataType: "json"
            }).done((retour) => {

                if(initialisation)
                    this.projets = retour.projets;

                this.tableau_semaine = retour.recap;
                this.feuilles_de_temps = retour.feuilles_de_temps;
                this.projets_presents = retour.projets_presents;
                this.totaux_projets = retour.totaux_projets;

                loading(false);
            });
        },

        ajouter_scrum: function() {

            var nouvelle_feuille_de_temps = structuredClone(this.modele_feuille_de_temps);

            nouvelle_feuille_de_temps.element_id = 56;
            nouvelle_feuille_de_temps.tache = "Scrum";
            nouvelle_feuille_de_temps.duree = 0.25;

            this.nouvelle_feuille_de_temps_interne(nouvelle_feuille_de_temps);
        },

    },
    computed: {

        total_journee: function() {

            somme = 0;

            $.each(this.feuilles_de_temps, function(key, feuille_de_temps) {

                somme += parseFloat(feuille_de_temps.duree);
            });

            return Math.round(somme*100)/100;
        },
    },
});
</script>
