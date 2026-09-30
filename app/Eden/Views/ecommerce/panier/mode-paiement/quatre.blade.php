<div class="css_paiement_comptant hidden-block js_paiement_all js_paiement_quatre">
    <img src="{{ asset('ecommerce-amc/images/cofidis_4x.png') }}" alt="logo cofidis 4x" class="css_logo_cofidis js_logo_codifis_4x">
    <h4 class='css_titre_choix_paiement'>
        Paiement en 4x
    </h4>
    <div class="css_flex_choix_mode_paiement">
        <p>
            Vous avez choisi le paiement 4x.
            <br>
            <br>
            Des frais de 2.2% sur le total de votre commande sont appliqués, soit 
			<span class="text-bold"></span>{{ number_format($donnees_vue_js->total_panier_avec_frais_de_livraison * 0.022, 2, ',', ' ') }} €</span>.
            <br>
            Montant de votre commande : <span class="text-bold">{{ number_format($donnees_vue_js->total_panier_avec_frais_de_livraison * 1.022, 2, ',', ' ') }} € TTC</span>.
            <br>
            <br>
            Il ne vous reste plus qu'à choisir votre mode de paiement :
        </p>
        <div class="css_block_checkbox_mode_paiement trois">
            <div class='radio'>
                <label class='css_radio_custom_amc'>
                    <input type='radio' name='mode_paiement_4_fois' class="js_radio_paiement_quatre mode_paiement_comptant_monetico" data-mode="cb4x" value='1' checked>
                    Carte bancaire
                    <span class="css_check_radio_amc"></span>
                </label>
            </div>
            
        </div>

    </div>
    <div class='checkbox'>
        <label>
            <input type='checkbox' class="cgv" name='cgv' value='1'>
            Je déclare connaître et accepter de plein droit les <a href="{{ route('cgv') }}" class="basic-link">conditions générales de vente</a>.
        </label>
    </div>
</div>
