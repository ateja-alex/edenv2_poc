<div class="table_planning">
	<table class="table table-bordered" data-height="{{ fonctionnalite('planning_hauteur_ligne') }}">
		<thead>
			<tr>
				<th rowspan="2" class="colonne_utilisateur"></th>
				@if(fonctionnalite('planning_affichage_demi_journee'))
					<th rowspan="2" class="text-center _50"></th>
				@endif
				<th class="colonne_semaine" v-for="semaine in dates.planning.semaine" :colspan="dates.nombre_jours_par_semaine">@traduction('interface.saisie_des_temps.semaine') @{{ semaine.numero_semaine }}</th>
			</tr>
			<tr>
				<template v-for="semaine in dates.planning.semaine">
					<th v-for="(date,index) in semaine.dates" class="text-center css_dates_semaine css_cellule_tache " :style="(index == semaine.dates.length - 1 ? 'border-right: 2px solid lightgrey!important;' : '')" :class="{css_aujourdhui: date == dates.aujourdhui }">
						@{{ date }}
						<span class="badge badge-default" :style="'width: fit-content;background:'+indisponibilite.couleur+';color:'+indisponibilite.couleur_police" v-html="indisponibilite.chaine_affichage" v-for="indisponibilite in dates.indisponibilites[semaine.dates_format[index]]"></span>
					</th>
				</template>
			</tr>
		</thead>
		<tbody>
			
			<template v-for="equipe in utilisateurs_par_equipes">
				<tr class="titre_equipe">
					<td colspan="100%">
						<span class="planning_badge_equipe" v-html="equipe.nom"></span>
					</td>
				</tr>

				<template v-for="utilisateur in equipe.utilisateurs">
					@if(fonctionnalite('planning_affichage_demi_journee'))
						<tr>
							<td class="colonne_utilisateur" :rowspan="verification_semaine_par_utilisateur(utilisateur.id, dates.planning.semaine) ? 1 : 2">
								@include('eden::composants_vue.js.include.planning.utilisateur')
							</td>
							<template v-if="verification_semaine_par_utilisateur(utilisateur.id, dates.planning.semaine)">
								<td class="text-center _50">@traduction('interface.planning.journee_complete')</td>
								<template v-for="semaine in dates.planning.semaine">
									<td v-for="(date,index) in semaine.matins" :style="(index == semaine.dates.length - 1 ? 'border-right: 2px solid lightgrey!important;' : '')" class="css_cellule_tache css_cellule_matin" @dblclick="creer_tache(utilisateur.id, date)">
										@include('eden::composants_vue.js.include.planning.tache')
										@include('eden::composants_vue.js.include.planning.conge')
                                        @include('eden::composants_vue.js.include.planning.ajout_element')
									</td>
								</template>
							</template>
							<template v-else>
								<td class="text-center _50">@traduction('interface.planning.am')</td>
								<template v-for="semaine in dates.planning.semaine">
									<td v-for="(date,index) in semaine.matins" :style="(index == semaine.dates.length - 1 ? 'border-right: 2px solid lightgrey!important;' : '')" class="css_cellule_tache css_cellule_matin" @dblclick="creer_tache(utilisateur.id, date,'am')">
										@include('eden::composants_vue.js.include.planning.tache')
										@include('eden::composants_vue.js.include.planning.conge')
                                        @include('eden::composants_vue.js.include.planning.ajout_element', ['creation_demi_journee' => 'am'])
									</td>
								</template>
							</template>
						</tr>
						<tr v-if="!verification_semaine_par_utilisateur(utilisateur.id, dates.planning.semaine)">
							<td class="text-center _50">@traduction('interface.planning.pm')</td>
							<template v-for="semaine in dates.planning.semaine">
								<td v-for="(date, index) in semaine.apres_midi" :style="(index == semaine.dates.length - 1 ? 'border-right: 2px solid lightgrey!important;' : '')" class="css_cellule_tache css_cellule_apres_midi" @dblclick="creer_tache(utilisateur.id, date,'pm')">
									@include('eden::composants_vue.js.include.planning.tache')
									@include('eden::composants_vue.js.include.planning.conge')
                                    @include('eden::composants_vue.js.include.planning.ajout_element', ['creation_demi_journee' => 'pm'])
								</td>
							</template>
						</tr>
					@else
						<tr>
                            <td class="colonne_utilisateur">
								@include('eden::composants_vue.js.include.planning.utilisateur')
							</td>
                            <template v-for="semaine in dates.planning.semaine">
                                <td v-for="(date,index) in semaine.dates_format" :date="date" :utilisateur_id="utilisateur.id" :style="(index == semaine.dates.length - 1 ? 'border-right: 2px solid lightgrey!important;' : '')" class="cellule_tache css_cellule_tache css_cellule_matin" @dblclick="creer_tache(utilisateur.id, date)">
                                    @include('eden::composants_vue.js.include.planning.tache')
                                    @include('eden::composants_vue.js.include.planning.conge')
                                    @include('eden::composants_vue.js.include.planning.ajout_element')
                                </td>
                            </template>
                        </tr>
					@endif
				</template>
			</template>

		</tbody>
	</table>
</div>

@push('donnees_pour_vuejs_computed')

    utilisateurs_par_equipes: function () {
    
        let utilisateurs_par_equipes = {};
    
        // On ajoute les équipes et utilisateurs au tableau utilisateurs_par_equipes
        this.utilisateurs_affiches.forEach(utilisateur => {
    
            if(utilisateur.equipe == 0)
                utilisateur.equipe = null;
    
            nom_equipe_HTML = this.nom_equipe(utilisateur.equipe, true);
            nom_equipe_sans_html = this.nom_equipe(utilisateur.equipe, false);
    
            if (typeof utilisateurs_par_equipes[utilisateur.equipe] === 'undefined') {
    
                // l'équipe n'existe pas encore, on la crée
                utilisateurs_par_equipes[utilisateur.equipe] = {
                    'nom': nom_equipe_HTML,
                    'nom_equipe': nom_equipe_sans_html,
                    'utilisateurs': []
                };
            }
            utilisateurs_par_equipes[utilisateur.equipe]['utilisateurs'].push(utilisateur);
        });
    
        var tableau = Object.values(utilisateurs_par_equipes);
    
        // tri du nom
        var tri_par_nom = (a, b) => {
    
            var nom1 = a.nom_equipe.toLowerCase();
            var nom2 = b.nom_equipe.toLowerCase();
    
            // Placer "Sans équipe" en dernier
            if (nom1 === "sans équipe") return 1;
            if (nom2 === "sans équipe") return -1;
    
            if (nom1 < nom2) return -1;
            if (nom1 > nom2) return 1;
    
            return 0;
        };
    
        //tri du tableau
        retour = tableau.sort(tri_par_nom);
    
		return retour;
	},

@endpush

@push('donnees_pour_vuejs_methods')

    lien_calendrier : function(utilisateur_id){

        var parametres = {
            filtres_valeurs:{
                affectation:[utilisateur_id]
            },
            parametres:{
                date : this.dates.plage_de_dates.debut_de_semaine,
                format_calendrier: 'semaine_{{fonctionnalite('planning_nombre_jours')}}j'
            }
        };

        for(cle_parametre of Object.keys(this.filtres_tache)){

            parametres.filtres_valeurs[cle_parametre] = this.filtres_tache[cle_parametre];
        }

        var url  = '/eden/calendrier?parametres='+btoa(JSON.stringify(parametres));

        return url;
    },

@endpush