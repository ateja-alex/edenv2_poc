<div id="popover-recherche" class="card d-none mt-3 mr-3 mb-3" @if(maquette('utiliser_menu_app') !== true) style="margin-left: 276px;" @endif>
	<div id="filtres-popover-recherche" class="card-header">
		<span id="filtres-popover-recherche-top">
			<h4>@traduction('interface.resultats_recherche.titre')</h4>
			<button id="close_recherche" type="button" class="close">
				<span aria-hidden="true">&times;</span>
			</button>
		</span>
		<br/>
		<span>@traduction('interface.resultats_recherche.filtrer_mes_resultats') :</span>
		<span v-for="(filtre, type_element) in types_elements_recherche_globale" class="badge mx-2" :class="{'badge-success': filtres_affichage_recherche_globale[type_element] === true, 'badge-default': filtres_affichage_recherche_globale[type_element] !== true}" @click="filtre_modification(type_element)">
			@{{ filtre }}
		</span>
	</div>
	<div class="card-body">
		<div class="js_conteneur_card_loading css_conteneur_card_loading">
			<div id="loading_card">
					<img src="{{ asset('eden/images/loading.svg') }}" />

					@if(super_admin())
						<br/>
						<br/>
						<span class="btn btn-primary mb-3" onClick="loading_card_recherche_global(false)">@traduction('interface.loader.fermer')</span>
					@endif
			</div>
		</div>
		<div v-show="resultat_recherche_globale.length >= 100">
			<img src="{{ asset('eden/images/aide.png') }}" width="30" style="float: left; margin-right: 10px;" />
			<b><big>@traduction('interface.resultats_recherche.attention')</big><br/>
				@traduction('interface.resultats_recherche.explication_troncage')</b>
			<br/>
			<br/>
		</div>

		<table class="table table-bordered table-hover" id="table_recherche" width="100%" cellspacing="0">
			<thead>
				@if(fonctionnalite('recherche_par_defaut') != '' && fonctionnalite('recherche_par_defaut') != 'tout')
					<th>@traduction('interface.resultats_recherche.resultats')</th>
				@else
					<tr>
						<th>@traduction('interface.resultats_recherche.tableau_resultat.type_element')</th>
						<th>@traduction('interface.resultats_recherche.tableau_resultat.lien')</th>
					</tr>
				@endif

			</thead>
			<tbody>
				@if(fonctionnalite('recherche_par_defaut') != '' && fonctionnalite('recherche_par_defaut') != 'tout')
					<tr v-for="resultat in resultat_recherche_globale" v-show="filtres_affichage_recherche_globale[resultat.type_element] === true">
						<td v-html="resultat.lien"></td>
					</tr>
				@else
					<tr v-for="resultat in resultat_recherche_globale" v-show="filtres_affichage_recherche_globale[resultat.type_element] === true">
						<td>@{{ resultat.nom_element }}</td>
						<td v-html="resultat.lien"></td>
					</tr>

				@endif
			</tbody>
		</table>
	</div>
</div>

@push('donnees_pour_vuejs_data')
	recherche_en_cours:false,
	resultat_recherche_globale: [],
	types_elements_recherche_globale: ['Client'],
	filtres_affichage_recherche_globale: [],
@endpush
