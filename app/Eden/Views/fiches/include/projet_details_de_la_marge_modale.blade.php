<div class="modal fade" id="modal_details_marge" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">@traduction('module_sur_fiche.projet_details_de_la_marge_modale.details_des_marges')</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body" style="font-size: 18px;">
				<div class="row">
					<div class="col-md-6" style="text-align: right;">@traduction('module_sur_fiche.projet_details_de_la_marge_modale.ca')</div>
					<div class="col-md-6">{{ montant_lisible($marge_projet['montant_total']) }}{!! maquette('devise_application_symbole') !!}</div>
				</div>
                @if(fonctionnalite('calcul_marges_achats_saisis_sur_documents'))
                    <div class="row">
                        <div class="col-md-6" style="text-align: right;">@traduction('module_sur_fiche.projet_details_de_la_marge_modale.achats_saisis_sur_les_documents')</div>
                        <div class="col-md-6">{{ montant_lisible($marge_projet['achats_sur_document']) }}{!! maquette('devise_application_symbole') !!}</div>
                    </div>
                @endif
				<div class="row">
					<div class="col-md-6" style="text-align: right;">@traduction('module_sur_fiche.projet_details_de_la_marge_modale.achats_fournisseurs')</div>
					<div class="col-md-6">{{ montant_lisible($marge_projet['achats']) }}{!! maquette('devise_application_symbole') !!}</div>
				</div>
				<div class="row">
					<div class="col-sm-6 css_form_ligne_titre" style="text-align: right;">@traduction('module_sur_fiche.projet_details_de_la_marge_modale.marge_brute')</div>
					<div class="col-sm-6 css_form_ligne_titre">{{ montant_lisible($marge_projet['marge_brute']) }}{!! maquette('devise_application_symbole') !!}</div>
				</div>
				@foreach($marge_projet['details_cout_par_utilisateur'] as $cout_utilisateur_id => $cout_par_utilisateur)
					<div class="row" style="font-size: 13px;">
						<div class="col-md-6" style="text-align: right;">{{ modele('utilisateur', $cout_utilisateur_id)->prenom }} {{ modele('utilisateur', $cout_utilisateur_id)->nom }}</div>
						<div class="col-md-6">{{ montant_lisible($cout_par_utilisateur) }}{!! maquette('devise_application_symbole') !!}</div>
					</div>
				@endforeach
				<div class="row">
					<div class="col-md-6" style="text-align: right;">@traduction('module_sur_fiche.projet_details_de_la_marge_modale.couts_rh')</div>
					<div class="col-md-6">{{ montant_lisible($marge_projet['couts_rh']) }}{!! maquette('devise_application_symbole') !!}</div>
				</div>
				<div class="row">
					<div class="col-sm-6 css_form_ligne_titre" style="text-align: right;">@traduction('module_sur_fiche.projet_details_de_la_marge_modale.marge_nette')</div>
					<div class="col-sm-6 css_form_ligne_titre">{{ montant_lisible($marge_projet['marge_net']) }}{!! maquette('devise_application_symbole') !!}</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">@traduction('module_sur_fiche.projet_details_de_la_marge_modale.fermer')</button>
			</div>
		</div>
	</div>
</div>