<footer class=''>
    @yield('footer-content')
    <section class="css_pre_footer_homepage">
        <div class="css_service_client_footer">
            <div class="css_bg_service_client">
                <div>
                    <p>
                        Un service client
                        <br>
                        <span>
                            à votre écoute
                        </span>
                    </p>
                    <p>
                        Nos techniciens
                        <br>
                        vous conseillent
                        <br>
                        à chaque étape
                        <br>
                        de votre commande
                    </p>
                </div>
            </div>
            <div class="css_block_tel_service_client">
                <p class='css_text_contact_tel_footer_hp'>
                    Du lundi au vendredi
                    <br>
                    de 9h30 à 12h00 et de 14h00 à 18h00
                </p>
                <a href="tel:0366729188" class='css_tel_contact_footer_hp roboto'>
                    03 66 72 91 88
                </a>
                <a href="{{ route('engagements-contact') }}" class="btn btn-rouge-amc">
                    Contactez-nous
                </a>
                <p class='css_text_contact_tel_footer_hp'>
                    AMC Production
                    <br>
                    121 rue de Chanzy 59 800 Lille (France)
                </p>
            </div>
        </div>
        <div class="css_navigation_footer">
            <div class="container-fluid css_padding_30_15">
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-8">
                        <div class="row">
                            <div class="col-xs-12 col-sm-4 col-md-4">
                                <h4 class="css_titre_nav_footer_hp">
                                    Portes de garage
                                    <br>
                                    sur mesure
                                </h4>
                                <ul class='css_liste_lien_nav_footer_hp'>
                                    <li>
                                        <a href="{{ route('porte-garage') }}">Présentation / Avantages</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('caracteristiques-garage') }}">Caractéristiques techniques</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('guide-porte-garage') }}">Guide de pose</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('devis-porte-garage') }}">Configurateur en ligne</a>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-xs-12 col-sm-4 col-md-4">
                                <h4 class="css_titre_nav_footer_hp">
                                    Volets roulants
                                    <br>
                                    sur mesure
                                </h4>
                                <ul class='css_liste_lien_nav_footer_hp'>
                                    <li>
                                        <a href="{{ route('volet-roulant') }}">Présentation / Avantages</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('caracteristiques-volet-roulant') }}">Caractéristiques techniques</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('guide-volet-roulant') }}">Guide de pose</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('devis-volet-roulant') }}">Configurateur en ligne</a>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-xs-12 col-sm-4 col-md-4">
                                <h4 class="css_titre_nav_footer_hp">
                                    Tabliers
                                    <br>
                                    sur mesure
                                </h4>
                                <ul class='css_liste_lien_nav_footer_hp'>
                                    <li>
                                        <a href="{{ route('tablier') }}">Présentation / Avantages</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('caracteristiques-tablier') }}">Caractéristiques techniques</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('guide-tablier') }}">Guide de pose</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('devis-tablier') }}">Configurateur en ligne</a>
                                    </li>
                                </ul>
                            </div>
                            <div class="clearfix"></div>
                            <div class="col-xs-12 col-sm-4 col-md-4">
                                <h4 class="css_titre_nav_footer_hp">
                                    QUI SOMMES-NOUS ?
                                </h4>
                                <ul class='css_liste_lien_nav_footer_hp'>
                                    <li>
                                        <a href="{{ route('engagements-qui') }}">Entreprises</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('faq') }}">FAQ</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('blog.index') }}">Le blog</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4">
                        <h4 class="css_titre_nav_footer_hp">Accessoires</h4>
                        <ul class='css_liste_lien_nav_footer_hp'>
                            <li>
                                <a href="{{ route('ecommerce.url_ecommerce', array('motorisation-porte-de-garage')) }}">Motorisation de portes de garage</a>
                            </li>
                            <li>
                                <a href="{{ route('ecommerce.url_ecommerce', array('commande-porte-de-garage')) }}">Commande de portes de garage</a>
                            </li>
                            <li>
                                <a href="{{ route('ecommerce.url_ecommerce', array('composant-porte-de-garage')) }}">Composants de portes de garage</a>
                            </li>
                            <li>
                                <a href="{{ route('ecommerce.url_ecommerce', array('motorisation-volet-roulant')) }}">Motorisation de volets roulants</a>
                            </li>
                            <li>
                                <a href="{{ route('ecommerce.url_ecommerce', array('commande-volet-roulant')) }}">Commande de volet roulants</a>
                            </li>
                            <li>
                                <a href="{{ route('ecommerce.url_ecommerce', array('composant-volet-roulant')) }}">Composants de volet roulants</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="css_bandeau_reassurance_footer_hp">
                <div class="css_item_reassurance_footer_hp">
                    <a href="{{ route('engagements-livraison') }}">
                        <img class='' src="{{ asset('ecommerce-amc/images/pictos/surmesure.png') }}" alt="">
                        <p>
                            Produits sur mesure
                            <br>
                            expédiés sous 2 semaines
                        </p>
                    </a>
                </div>
                <div class="css_item_reassurance_footer_hp">
                    <a href="{{ route('engagements-paiement') }}">
                        <img class='' src="{{ asset('ecommerce-amc/images/pictos/paiement.png') }}" alt="">
                        <p>
                            Un large choix
                            <br>
                            de modalités de paiement
                        </p>
                    </a>
                </div>
                <div class="css_item_reassurance_footer_hp">
                    <a href="{{ route('engagements-garanties') }}">
                        <img class='' src="{{ asset('ecommerce-amc/images/pictos/garantie.png') }}" alt="">
                        <p>
                            Garantie
                            <br>
                            5 ans
                        </p>
                    </a>
                </div>
                <div class="css_item_reassurance_footer_hp">
                    <a href="{{ route('engagements-suivi-commande') }}">
                        <img class='' src="{{ asset('ecommerce-amc/images/pictos/suivi-commande.png') }}" alt="">
                        <p>
                            Suivi de commande
                            <br>
                            en ligne
                        </p>
                    </a>
                </div>
            </div>
        </div>
    </section>
    <div class="css_menu_footer">
        <ul>
            <li>
                <a href="{{ route('cgv') }}">CGV</a>
            </li>
            <li>
                <a href="{{ route('engagements-paiement') }}">Paiement</a>
            </li>
            <li>
                <a href="{{ route('engagements-livraison') }}">Livraison</a>
            </li>
            <li>
                <a href="{{ route('engagements-garanties') }}">Garantie</a>
            </li>
            <li>
                <a href="{{ route('mentions-legales') }}">Mentions légales</a>
            </li>
        </ul>
        <img class='' src="{{ asset('ecommerce-amc/images/pictos/moyen_paiement.png') }}" alt="">
    </div>
</footer>
