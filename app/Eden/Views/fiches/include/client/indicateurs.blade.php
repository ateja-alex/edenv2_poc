<div class="card mb-3">
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-8" style="font-weight: 900; font-size: 20px;">
                        @traduction('module_sur_fiche.client.indicateurs.ca')
                    </div>
                    <div class="col-md-4" style="font-weight: 900; font-size: 20px; text-align: right;">
                        {{ montant($indicateurs['chiffre_affaire'],0) }}{{ maquette('devise_application_symbole') }}
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-8" style="font-weight: 900; font-size: 20px;">
                        @traduction('module_sur_fiche.client.indicateurs.encours')
                    </div>
                    <div class="col-md-4" style="font-weight: 900; font-size: 20px; text-align: right;">
                        {{ montant($indicateurs['encours'],0) }}{{ maquette('devise_application_symbole') }}
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-8" style="font-weight: 900; font-size: 20px;">
                        @traduction('module_sur_fiche.client.indicateurs.montant_devis')
                    </div>
                    <div class="col-md-4" style="font-weight: 900; font-size: 20px; text-align: right;">
                        {{ montant($indicateurs['montant_devis'],0) }}{{ maquette('devise_application_symbole') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>