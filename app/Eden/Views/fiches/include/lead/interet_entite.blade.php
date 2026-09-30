<div class="card mb-3">
	<div class="card-header d-flex align-items-center js_fermeture_bloc">

		<h4 class="d-flex align-items-center">
			@traduction('module_sur_fiche.fiche.lead.interet_entite.titre')
		</h4>
		
		<a data-toggle="tooltip" data-placement="left" class="css_ajouter_element ml-auto" data-original-title="{{traduction('module_sur_fiche.fiche.lead.interet_article.ajouter')}}" @click="modale_ajouter_interet_entite_au_lead()"><i class="css_action_icon secondaire fa fa-fw fa-plus-square" aria-hidden="true"></i></a>
		
	</div>
	<div class="card-body" @if(isset($afficher_par_defaut) && $afficher_par_defaut === false) style="display: none;" @endif>
		
		<div class="row">
			<div class="col-sm-7">
				<b>@traduction('module_sur_fiche.fiche.lead.interet_entite.entite')</b>
			</div>
			<div class="col-sm-3">
				<b>@traduction('module_sur_fiche.fiche.lead.interet_article.interet')</b>
			</div>
			<div class="col-sm-2">
				<b>@traduction('module_sur_fiche.fiche.lead.interet_article.options')</b>
			</div>
		</div>
		<div class="row" v-for="(interet_lead_entite, index) in interets_leads_entite">
			<div class="col-sm-7">
				<span class="css__lien" @click="modifier_interet_entite(interet_lead_entite)">@{{ interet_lead_entite.entite_id | affiche_entite }}</span>
			</div>
			<div class="col-sm-3">
				<span class="badge badge-default" v-show="interet_lead_entite.interet == 0">@traduction('module_sur_fiche.fiche.lead.interet_article.non_precise')</span>
				<span class="badge badge-default" v-show="interet_lead_entite.interet == 1" style="background: #adc0e4">@traduction('module_sur_fiche.fiche.lead.interet_article.froid')</span>
				<span class="badge badge-default" v-show="interet_lead_entite.interet == 2" style="background: #f4e956">@traduction('module_sur_fiche.fiche.lead.interet_article.tiede')</span>
				<span class="badge badge-default" v-show="interet_lead_entite.interet == 3" style="background: #f2c646">@traduction('module_sur_fiche.fiche.lead.interet_article.chaud')</span>
				<span class="badge badge-default" v-show="interet_lead_entite.interet == 4" style="background: #f47658">@traduction('module_sur_fiche.fiche.lead.interet_article.bouillant')</span>
			</div>
			<div class="col-sm-2">
				<span class="css__lien" @click="supprimer_interet_entite_du_lead(interet_lead_entite, index)">@traduction('module_sur_fiche.fiche.lead.interet_article.supprimer')</span>
			</div>
		</div>
	
		
	</div>
</div>

<!-- modale pour ajouter des crédits -->
<div class="modal fade" id="modal_ajout_interet_entite_au_lead" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">@traduction('module_sur_fiche.fiche.lead.interet_article.titre_modal')</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			
			<div class="modal-body">
				<form action="#" id="formulaire_ajout_interet_entite_au_lead" method="post" class="css_form">
					<input type="hidden" name="lead_id" value="{{ $management_element->modele->id }}" />
					<div class="row">
						<div class="col-md-6">@traduction('module_sur_fiche.fiche.lead.interet_entite.entite')</div>
						<div class="col-md-6">{!! management('interet_lead_entite')->champ('entite_id')->cree() !!}</div>
					</div>
					<div class="row">
						<div class="col-md-6">@traduction('module_sur_fiche.fiche.lead.interet_article.interet')</div>
						<div class="col-md-6">{!! management('interet_lead_entite')->champ('interet')->cree() !!}</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" @click="ajouter_interet_entite_au_lead">{{traduction('interface.modales.enregistrer')}}</button>
			</div>
		</div>
	</div>
</div>	

@push('donnees_pour_vuejs_data')
	interets_leads_entite: {!! collect($interets_leads_entite) !!},
	interet_lead_entite: {
		
		lead_id: {{ $management_element->modele->id }}
	},
@endpush

@push('donnees_pour_vuejs_methods')

	supprimer_interet_entite_du_lead: function(interet_lead_entite, index) {
		
		loading(true);
		
		$.get({

			url: '{{ URL::to('eden/element/interet_lead_entite') }}/'+interet_lead_entite.id+'/supprimer',
			dataType: "json",
		}).done(async function(donnees) {
			
			// On retire le loader
			loading(false);
			
			if(donnees.retour !== true) {
			
				await erreur(donnees.retour);
				return;
			}

			// on reload la liste des credits
			vue_instance.interets_leads_entite_actualiser();
		});
	},
	
	modale_ajouter_interet_entite_au_lead: function() {
		
		$('#modal_ajout_interet_entite_au_lead').modal('show')
	},
	
	modifier_interet_entite: function(interet_lead_entite) {
		
		this.interet_lead_entite = interet_lead_entite;
		
		$('#modal_ajout_interet_entite_au_lead').modal('show');
	},
	
	ajouter_interet_entite_au_lead: function() {
		
		loading(true);
		
		if(this.interet_lead_entite.id != undefined) {
			
			var url = '{{ URL::to('eden/element/interet_lead_entite') }}/'+this.interet_lead_entite.id+'/enregistrer';
		}
		else {
			
			var url = '{{ route('base_eden.element.creer', ['interet_lead_entite']) }}';
		}
		
		$.post({
			data:$('#formulaire_ajout_interet_entite_au_lead').serialize(),
			url: url,
			dataType: "json",
		}).done(async function(donnees) {
			
			// On retire le loader
			loading(false);
			
			if(donnees.retour !== true) {
			
				await erreur(donnees.retour);
				return;
			}
			
			// on reload la liste des credits
			vue_instance.interets_leads_entite_actualiser();
			
			vue_instance.interet_lead_entite = {
				
				lead_id: {{ $management_element->modele->id }}
			};
		});
	},
	
	interets_leads_entite_actualiser: function() {
		
		loading(true);
		
		$.get({

			url: 'eden/fiche/lead/{{ $management_element->modele->id }}/interets_entites',
			dataType: "json"
		}).done(function(interets_leads_entite) {
			
			// On retire le loader
			loading(false);
			
			vue_instance.interets_leads_entite = interets_leads_entite;
			
			$('#modal_ajout_interet_entite_au_lead').modal('hide');
		});
	},	
@endpush