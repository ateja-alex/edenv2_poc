<footer>
    <section class="css_footer_commande">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-2">
                    <img class='css_img_fabrication_fr_footer_commande' src="{{ asset('ecommerce-amc/images/logo-france.png') }}" alt="">
                </div>
                <div class="col-md-2">
                    <div class="css_flex_col_item_footer_commande">
                        @if (Route::current()->getName() == 'devis-porte-garage')
                            <img class="css_img_tablier_footer_commande" src="{{ asset('ecommerce-amc/images/pictos/porte-garage.png') }}" alt="">
                            <span>
                                Porte de garage
                                <br>
                                expédiée sous 3 semaines
                            @elseif (Route::current()->getName() == 'devis-tablier')
                                <img class="css_img_tablier_footer_commande" src="{{ asset('ecommerce-amc/images/pictos/tablier.png') }}" alt="">
                                <span>
                                    Tablier de volets roulants
                                    <br>
                                    expédié sous 3 semaines
                                @else
                                    <img class="css_img_tablier_footer_commande" src="{{ asset('ecommerce-amc/images/pictos/sur-mesure.png') }}" alt="">
                                    <span>
                                        Produits sur mesure
                                        <br>
                                        expédiés sous 3 semaines
                                @endif
                            </span>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="css_flex_col_item_footer_commande">
                            <img src="{{ asset('ecommerce-amc/images/pictos/paiement_gris.png') }}" alt="">
                            <span>
                                Un large choix
                                <br>
                                de modalités de paiement
                            </span>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="css_flex_col_item_footer_commande">
                            <img src="{{ asset('ecommerce-amc/images/pictos/garantie_gris.png') }}" alt="">
                            <span>
                                Garantie
                                <br>
                                5 ans
                            </span>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="css_flex_col_item_footer_commande">
                            <img src="{{ asset('ecommerce-amc/images/pictos/cadenas.png') }}" alt="">
                            <span>
                                Transaction
                                <br>
                                sécurisée
                            </span>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="css_flex_col_item_footer_commande">
                            <img src="{{ asset('ecommerce-amc/images/pictos/check_gris.png') }}" alt="">
                            <span>
                                @if (Route::current()->getName() == 'devis-porte-garage')
                                    <span class="text-bold">
                                        Livraison OFFERTE à partir de 250&euro;
                                    </span>
                                @elseif (Route::current()->getName() == 'devis-tablier')
                                    Livraison offerte à partir de 250&euro;
                                @else
                                    Livraison offerte à partir de 250&euro;
                                @endif
                            </span>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <p class="css_info_text_footer_commande">
                            Nos produits sur mesure étant confectionnés selons les spécifications du consommateur,
                            <br>
                            aucun droit de rétractation ne sera possible après réception de votre commande.
                        </p>
                    </div>
                    <div class="col-md-4">
                        <img class="pull-right" src="{{ asset('ecommerce-amc/images/pictos/paiement_footer.png') }}" alt="">
                    </div>
                </div>
            </div>
        </section>
    </footer>
