<template v-if="article.id == null">

    <div class="row">
        <div class="col-sm-12 css_form_ligne_titre">@traduction('formulaire.calcul_tarif_avec_marge.prix_de_vente')</div>
    </div>

    <div class="row">
        <div class="col-md-6">@traduction('formulaire.calcul_tarif_avec_marge.prix_achat')</div>
        <div class="col-md-6"><input type="text" v-model="article.prix_d_achat" name="prix_d_achat" @change="mise_a_jour_prix_de_vente(article,'prix_achat')" /></div>
    </div>
    <div class="row">
        <div class="col-md-6">@traduction('formulaire.calcul_tarif_avec_marge.marge') ({!! maquette('devise_application_symbole') !!})</div>
        <div class="col-md-6"><input type="text" v-model="article.marge_devises" name="marge_devises" @change="mise_a_jour_prix_de_vente(article,'marge_devises')" /></div>
    </div>
    <div class="row">
        <div class="col-md-6">@traduction('formulaire.calcul_tarif_avec_marge.marge') (%)</div>
        <div class="col-md-6"><input type="text" v-model="article.marge_pourcent" name="marge_pourcent" @change="mise_a_jour_prix_de_vente(article,'marge_pourcent')" /></div>
    </div>
    <div class="row">
        <div class="col-md-6">@traduction('formulaire.calcul_tarif_avec_marge.prix_de_vente')</div>
        <div class="col-md-6"><input type="text" v-model="article.tarif" name="tarif" @change="mise_a_jour_prix_de_vente(article)" /></div>
    </div>

</template>

@push('donnees_pour_vuejs_methods')
    mise_a_jour_prix_de_vente(article,modification) {
		
		var tarif = parseFloat(article.tarif);
        var prix_d_achat = parseFloat(article.prix_d_achat);
        var marge_pourcent = parseFloat(article.marge_pourcent);
        var marge_devises = parseFloat(article.marge_devises);

        if (modification == 'prix_achat') {

            if (!isNaN(prix_d_achat)) {

                if (!isNaN(marge_pourcent)) {

                    tarif = Math.round(prix_d_achat * 100 / (100 - marge_pourcent) * 100) / 100;

                } else if (!isNaN(marge_devises)) {

                    tarif = Math.round((parseFloat(prix_d_achat) + parseFloat(marge_devises)) * 100) / 100;

                }
            }
        } else if (modification == "marge_pourcent") {

            if (!isNaN(marge_pourcent)) {

                if (!isNaN(prix_d_achat)) {

                    if(marge_pourcent == 100)
                        tarif = prix_d_achat;

                    else
                        tarif = Math.round(prix_d_achat * 100 / (100 - marge_pourcent) * 100) / 100;

                }
            }
        } else if (modification == "marge_devises") {

            if (!isNaN(marge_devises)) {

                if (!isNaN(prix_d_achat)) {

                tarif = Math.round((parseFloat(prix_d_achat) + parseFloat(marge_devises)) * 100) / 100;

                }

            }
        }

        if (!isNaN(prix_d_achat) && !isNaN(tarif)) {

            marge_devises = (tarif - prix_d_achat).toFixed(2);

            if (tarif != 0) {

                marge_pourcent = (Math.round((tarif - prix_d_achat) / tarif * 10000) / 100).toFixed(2);
            }
            else
                marge_pourcent = 0;
        }

        if(!isNaN(tarif))
            article.tarif = tarif;

        if(!isNaN(marge_pourcent))
            article.marge_pourcent = marge_pourcent;

        if(!isNaN(marge_devises))
            article.marge_devises = marge_devises;
    },
@endpush