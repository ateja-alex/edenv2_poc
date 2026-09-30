<div>
	<div class="row">
		<div class="col-md-12">
			<div class="card mb-3">
				<div class="card-header js_fermeture_bloc">
					<h4 class="d-flex align-items-center">
						<span>@traduction('module_sur_fiche.sante_financiere.nom_module')</span>
					</h4>
				</div>
				<div class="card-body">
					<template v-if="si_module_sante_financiere_ok()">
						<img :src="'https://www.score3.fr/score3.spng?siren='+si_module_sante_financiere_ok()" />
					</template>
					<template v-else>
						@traduction('composant.sante_financiere.siren_invalide')
					</template>
				</div>
			</div>
		</div>
	</div>
</div>

@push('donnees_pour_vuejs_methods')
	
	si_module_sante_financiere_ok: function() {
		
		if(this.client.siren === null && this.client.siret === null)
			return false;
		
		var siren_a_utiliser = this.client.siren;
		
		if(siren_a_utiliser === null)
			siren_a_utiliser = this.client.siret;
		
		siren_a_utiliser = siren_a_utiliser.replaceAll(/\D/g,'');
		
		if(siren_a_utiliser.length < 9)
			return false;
		
		return siren_a_utiliser.substring(0,9);
	},
	
@endpush