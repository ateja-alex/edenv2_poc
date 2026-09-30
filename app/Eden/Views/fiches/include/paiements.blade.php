<div class="card mb-3">
	<div class="card-header js_fermeture_bloc">
		@if(isset($afficher_par_defaut))
			@if($afficher_par_defaut === true)
				<span class="" style="float: right; margin-top: 3px; cursor: pointer;">
					<span class="fa fa-chevron-up"></span>
				</span>

			@else
				<span class="" style="float: right; margin-top: 3px; cursor: pointer;">
					<span class="fa fa-chevron-down"></span>
				</span>
			@endif
		@endif
		<h4>@traduction('module_sur_fiche.paiements.paiements')</h4>
	</div>
	<div class="card-body" @if(isset($afficher_par_defaut) && $afficher_par_defaut === false) style="display: none;" @endif>
		
		<div class="row">
			<div class="col-sm-3">
				<b>@traduction('module_sur_fiche.paiements.date')</b>
			</div>
			<div class="col-sm-3">
				<b>@traduction('module_sur_fiche.paiements.montant')</b>
			</div>
			<div class="col-sm-3">
				<b>@traduction('module_sur_fiche.paiements.mode')</b>
			</div>
			<div class="col-sm-3">
				<b>@traduction('module_sur_fiche.paiements.document')</b>
			</div>
			
		</div>
		<div class="row" v-for="paiement in paiements">
			<div class="col-sm-3">
				@{{ paiement.date }}
			</div>
			<div class="col-sm-3">
				@{{ paiement.montant | montant }} {{ maquette('devise_application_symbole') }}
			</div>
			<div class="col-sm-3">
				@{{ paiement.mode_paiement_id }}
			</div>
			<div class="col-sm-3" v-html="paiement.lien_document"></div>
		</div>
		
	</div>
</div>

@push('donnees_pour_vuejs_data')
	paiements: {!! $paiements !!},
@endpush