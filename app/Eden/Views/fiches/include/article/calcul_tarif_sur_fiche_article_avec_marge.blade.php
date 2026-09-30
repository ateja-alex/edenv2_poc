<calcul-tarif-sur-fiche-article-avec-marge :article="article" :article_id="article.id"></calcul-tarif-sur-fiche-article-avec-marge>

<?php
/*
<div class="row">
	<div class="col-md-12">
		<div class="card mb-3">
			<div class="card-header d-flex align-items-center">
				<h4>
					Prix de vente
				</h4>
				<span class="css_ajouter_element ml-auto" data-toggle="tooltip" data-placement="left" title="Enregistrer" @click="enregistre_tarif_via_calcul_par_marge">
					<i class="css_action_icon secondaire fa fa-fw fa-save"></i>
				</span>
			</div>
			<div class="card-body">
				<div class="table-responsive css_form">
					<div class="row">
						<div class="col-md-6">Prix d'achat :</div>
						<div class="col-md-6"><input type="text" v-model="article.prix_d_achat" @change="mise_a_jour_prix_de_vente_depuis_prix_achat()" /></div>
					</div>
					<div class="row">
						<div class="col-md-6">Marge (€) :</div>
						<div class="col-md-6"><input type="text" v-model="article.marge_devises" @change="mise_a_jour_prix_de_vente_depuis_marge_devises()" /></div>
					</div>
					<div class="row">
						<div class="col-md-6">Marge (%) :</div>
						<div class="col-md-6"><input type="text" v-model="article.marge_pourcent" @change="mise_a_jour_prix_de_vente_depuis_marge_pourcent()" /></div>
					</div>
					<div class="row">
						<div class="col-md-6">Prix de vente :</div>
						<div class="col-md-6"><input type="text" v-model="article.tarif" @change="mise_a_jour_prix_de_vente_depuis_prix_vente()" /></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>					
      
{{-- <script> --}}
@push('donnees_pour_vuejs_methods')

	enregistre_tarif_via_calcul_par_marge() {
		
		// On affiche le loader
		loading();
		
		// on enregistre les infos du champ libre
		$.post({

			url: "{{ route('element_enregistrer', array('article', $article->id)) }}",
			dataType: "json",
			method: 'POST',
			data: {
				
				prix_d_achat: vue_instance.article.prix_d_achat,
				marge_devises: vue_instance.article.marge_devises,
				marge_pourcent: vue_instance.article.marge_pourcent,
				tarif: vue_instance.article.tarif,
			}
		}).done(async function(donnees) {
			
			// On retire le loader
			loading(false);
			
			if(donnees.retour !== true) {
			
				await erreur(donnees.retour);
				return;
			}
		});
	},

	mise_a_jour_prix_de_vente_depuis_prix_vente: function() {
		
		// on recalcule les marges
		if(this.article.prix_d_achat != '' && this.article.prix_d_achat !== null && this.article.tarif != '' && this.article.tarif !== null) {
			
			var marge_devises = this.article.tarif - this.article.prix_d_achat;
			
			this.article.marge_devises = marge_devises.toFixed(2);
			
			if(this.article.tarif != 0) {
				
				var marge_pourcent = Math.round((this.article.tarif - this.article.prix_d_achat) / this.article.tarif * 10000) / 100;
			
				this.article.marge_pourcent = marge_pourcent.toFixed(2);
			}
		}
		
		return;
	},
	
	mise_a_jour_prix_de_vente_depuis_marge_pourcent: function() {
		
		// on recalcule les marges
		if(this.article.prix_d_achat != '' && this.article.prix_d_achat !== null && this.article.marge_pourcent != '' && this.article.marge_pourcent !== null) {
			
			/*
			// calcul via taux de marge
			var tarif = Math.round(this.article.prix_d_achat * (parseFloat(100) + parseFloat(this.article.marge_pourcent))) / 100;
			
			
			// calcul via taux de marque
			var tarif = Math.round(this.article.prix_d_achat * 100 / (100 - this.article.marge_pourcent) * 100) / 100;
			
			this.article.tarif = tarif.toFixed(2);
			
			this.mise_a_jour_prix_de_vente_depuis_prix_vente();
		}
		
		return;
	},
	
	mise_a_jour_prix_de_vente_depuis_marge_devises: function() {
		
		// on recalcule les marges
		if(this.article.prix_d_achat != '' && this.article.prix_d_achat !== null && this.article.marge_devises != '' && this.article.marge_devises !== null) {
			
			var tarif = Math.round((parseFloat(this.article.prix_d_achat) + parseFloat(this.article.marge_devises)) * 100) / 100;
			
			this.article.tarif = tarif.toFixed(2);
			
			this.mise_a_jour_prix_de_vente_depuis_prix_vente();
		}
		
		return;
	},
	
	mise_a_jour_prix_de_vente_depuis_prix_achat: function() {
		
		// on recalcule le prix de vente final
		if(this.article.prix_d_achat != '' && this.article.prix_d_achat !== null && this.article.marge_pourcent != '' && this.article.marge_pourcent !== null) {
			
			var tarif = Math.round(this.article.prix_d_achat * (parseFloat(100) + parseFloat(this.article.marge_pourcent))) / 100;
			
			this.article.tarif = tarif.toFixed(2);
			
			this.mise_a_jour_prix_de_vente_depuis_prix_vente();
		}
		
		return;
	},
@endpush
*/?>