Vue.component( 'calendrier-a-afficher', {
	
	props: ['typeaffichagecalendrier', 'date'],
	data: function () {
		
		return {
			
			date_dans_les_data: [],
			dates: [],
			debut: [],
			fin: [],
			agenda:[],
			heures:[],
			largeur_cellule: '',
			creneaux:[],
			affichage_prefere: '',
			liste_utilisateurs_proposes:[],
		}
	},
	
	methods:{
		met_a_jour_les_dates: function(bouton,initialisation) {

			var vue_composant = this;
			
			var url = 'eden/calendrier/recuperer_dates/'+this.typeaffichagecalendrier+'/'+this.date_dans_les_data+'/'+initialisation;
			
			if(bouton !== false) {
				
				var url = 'eden/calendrier/recuperer_dates/'+this.typeaffichagecalendrier+'/'+this.date_dans_les_data+'/'+initialisation+'/'+bouton;
			}

			this.liste_utilisateurs_proposes.forEach(function(utilisateur_propose){
				if( utilisateur_propose.id === true)
				{
					
				}
			})
			$.post({
				url: url,
				dataType: "json",
				data: $('#formulaire_utilisateurs').serialize(),
			}).done(function(informations) {
	
				vue_composant.dates = informations.dates;
				vue_composant.date_dans_les_data = informations.date;
				vue_composant.debut = informations.debut;
				vue_composant.fin = informations.fin;
				vue_composant.heures = informations.heures;
				vue_composant.agenda = informations.agenda;
				vue_composant.creneaux = informations.creneaux;
				vue_composant.affichage_prefere = informations.affichage_prefere;
				vue_composant.liste_utilisateurs_proposes = informations.liste_utilisateurs_proposes;
				
				//Calcul de la largeur des cellules du calendrier
				setTimeout(function() {
					
					vue_composant.largeur_cellule = $('#div_calendrier').find("td").eq(1).width();
					
				}, 250);

			});

		},
		
	},
	mounted: function() {
		
		this.date_dans_les_data = this.date;
		this.met_a_jour_les_dates(false,true);
		
	},
	template: `<div class="row">
	<div class="col-md-12">
		<div class="card mb-3">
			<div class="card-header align-items-center justify-content-between" style="display:flex; text-align:center">

				<h4 >
					<span class="css_action_icon secondaire" @click="met_a_jour_les_dates('precedent',false)" style="padding: 6px; position:relative; top:-3px"> &lt;&lt; </span>
					Du {{ debut}} au {{fin}} 
					<span class="css_action_icon secondaire" @click="met_a_jour_les_dates('suivant',false)" style="padding: 6px; position:relative; top:-3px"> &gt;&gt; </span>
				</h4>

				<div style="float:right">
					<select v-model="typeaffichagecalendrier" @change="met_a_jour_les_dates(false,false)">
						<option value="semaine_5j"> Semaine de 5 jours </option>
						<option value="semaine_7j"> Semaine de 7 jours </option>
						<option value="mois"> Mois entier </option>
					</select>

					<span class="fa fa-user dropdown-toggle css_dropdown_sans_fleche_vers_le_bas" data-toggle="dropdown" aria-expanded="false"></span>
					<div class="dropdown-menu">
						<form id="formulaire_utilisateurs">
						<div v-for="utilisateur in liste_utilisateurs_proposes">
							<label class="dropdown-item">
								<span> 
									<input type="checkbox" v-model="utilisateur.est_demande" :name="'utilisateurs_demandes['+utilisateur.id+']'" @click="met_a_jour_les_dates(false,false)"> 
									</input> 
									{{ utilisateur.nom + ', ' + utilisateur.prenom }} 
								</span> 
							
							</label>
						</div>
						</form>
					</div>
				</div>
			</div>

			<div class="card-body">
				<div id="div_calendrier" class="table-responsive">
					<table id="calendrier_5j" v-if="typeaffichagecalendrier == 'semaine_5j'" class="table table-bordered table-hover" width="100%" cellspacing="0">
						<thead>
							<tr>
								<th> </th>
								<th v-for="date in dates"> {{ date['format_affichage'] }} </th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="heure in heures" style="text-align:center">
								<td> {{ heure }} </td>
								
								<template v-for="date in dates">
									<td>
										<div v-for="(tache,cle) in agenda[date.format_us][heure]":style="{height: 32 * tache.nombre_demies_heures + 'px', width: largeur_cellule / tache.nombre_taches_paralleles +'px', marginLeft:largeur_cellule / tache.nombre_taches_paralleles *(cle) +'px'}" style="background: #eee; padding: 5px; margin: 5px; position: absolute; margin-top: -14px;">{{ tache.titre }}</div>
									</td>
								</template>
							</tr>
						</tbody>
					</table>

					<table id="calendrier_7j" v-if="typeaffichagecalendrier == 'semaine_7j'" class="table table-bordered table-hover" width="100%" cellspacing="0">
						<thead>
							<tr>
								<th> </th>
								<th v-for="date in dates"> {{ date['format_affichage'] }} </th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="heure in heures" style="text-align:center">
								<td> {{ heure }} </td>	

								<template v-for="date in dates">
									<td>
										<div v-for="(tache,cle) in agenda[date.format_us][heure]":style="{height: 32 * tache.nombre_demies_heures + 'px', width: largeur_cellule/tache.nombre_taches_paralleles+'px', marginLeft:largeur_cellule / tache.nombre_taches_paralleles *(cle) +'px'}" style="background: #eee; padding: 5px; margin: 5px; position: absolute; margin-top: -14px;">{{ tache.titre }}</div>
									</td>
								</template>
							</tr>
						</tbody>
					</table>
						
					<table id="calendrier_mois" v-if="typeaffichagecalendrier == 'mois'" class="table table-bordered table-hover" width="100%" cellspacing="0">
						<thead>
							<tr>
								<th> Lundi </th>
								<th> Mardi </th>
								<th> Mercredi </th>
								<th> Jeudi </th>
								<th> Vendredi </th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="semaine in agenda" >
								<td v-for="jour in semaine" v-if="jour['date'] == null" > </td>
								<td v-for="jour in semaine" v-if="jour['date'] != null" > 
									<div style="float: right; background: #eee; padding: 2px; margin-top: -6px; margin-right: -6px">
										{{ jour['date']['format_date_du_jour'] }} 
									</div>
									<div v-for="evenement in jour['taches']" style="background: #eee; padding: 5px; margin: 5px;">{{ evenement.titre }}</div>
								</td>
							</tr>
						</tbody>
					</table>
				</div> <!-- fin div "calendrier" -->
			</div> <!-- fin div "card-body" -->
		</div> <!--fin div "card mb-3" -->
	</div> <!-- fin div "col-md-12" -->
</div> <!--fin div "row" -->` 
});



