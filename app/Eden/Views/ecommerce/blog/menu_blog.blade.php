<div class="col-xs-12 col-sm-4 col-md-3 css_block_haut_mobile_engagement">
    <h1 class='css_titre_engagements_amc_prod oswald'>
        Nos engagements
        <br>
        AMC PRODUCTION
    </h1>
    <nav id="navEngagement" class="visible-xs">
        <div class="css_categorie_mobile_engagement js_categorie_mobile_engagement">
            {{-- <span>Catégorie</span> --}}
            <span>@{{ titreEngagement }}</span>
            <i class="fas fa-angle-down"></i>
        </div>
        <div class="css_select_menu_engagement hidden-block js_select_menu_engagement">
            <a class="css_item_menu_engagement {{ active('engagements-livraison') }}" href="{{ route('engagements-livraison') }}">
                Expédition & livraison
            </a>
            <a class="css_item_menu_engagement {{ active('engagements-paiement') }}" href="{{ route('engagements-paiement') }}">
                Paiement & transaction
            </a>
            <a class="css_item_menu_engagement {{ active('engagements-garanties') }}" href="{{ route('engagements-garanties') }}">
                Garanties produits
            </a>
            <a class="css_item_menu_engagement {{ active('engagements-suivi-commande') }}" href="{{ route('engagements-suivi-commande') }}">
                Suivi de commande en ligne
            </a>
            <a class="css_item_menu_engagement {{ active('engagements-factures') }}" href="{{ route('engagements-factures') }}">
                Facture en ligne
            </a>
            <a class="css_item_menu_engagement {{ active('cgv') }}" href="{{ route('cgv') }}">
                Conditions générales de vente
            </a>
            <a class="css_item_menu_engagement {{ active('mentions-legales') }}" href="{{ route('mentions-legales') }}">
                Mentions légales
            </a>
            <a class="css_item_menu_engagement {{ active('engagements-qui') }}" href="{{ route('engagements-qui') }}">
                Qui sommes-nous
            </a>
            <a class="css_item_menu_engagement {{ active('faq') }}" href="{{ route('faq') }}">
                FAQ
            </a>
            <a class="css_item_menu_engagement {{ active(['blog.index', 'blog.url_blog']) }}" href="{{ route('blog.index') }}">
                Le blog
            </a>
            <a class="css_item_menu_engagement {{ active('engagements-contact') }}" href="{{ route('engagements-suivi-commande') }}">
                Contact
            </a>
        </div>
    </nav>
    <nav class='hidden-xs'>
        <ul class='css_menu_navigations_engagements'>
            <li class="{{ active('engagements-livraison') }}">
                <a href="{{ route('engagements-livraison') }}">Expédition & livraison</a>
            </li>
            <li class="{{ active('engagements-paiement') }}">
                <a href="{{ route('engagements-paiement') }}">Paiement & transaction</a>
            </li>
            <li class="{{ active('engagements-garanties') }}">
                <a href="{{ route('engagements-garanties') }}">Garanties produits</a>
            </li>
            <li class="{{ active('engagements-suivi-commande') }}">
                <a href="{{ route('engagements-suivi-commande') }}">Suivi de commande en ligne</a>
            </li>
            <li class="{{ active('engagements-factures') }}">
                <a href="{{ route('engagements-factures') }}">Facture en ligne</a>
            </li>
            <li class="{{ active('cgv') }}">
                <a href="{{ route('cgv') }}">Conditions générales de vente</a>
            </li>
            <li class="{{ active('mentions-legales') }}">
                <a href="{{ route('mentions-legales') }}">Mentions légales</a>
            </li>
            <li class="{{ active('engagements-qui') }}">
                <a href="{{ route('engagements-qui') }}">Qui sommes-nous</a>
            </li>
            <li class="{{ active('faq') }}">
                <a href="{{ route('faq') }}">FAQ</a>
            </li>
            <li class="{{ active(['blog.index', 'blog.url_blog']) }}">
                <a href="{{ route('blog.index') }}">Le blog</a>
            </li>
            <li class="{{ active('engagements-contact') }}">
                <a href="{{ route('engagements-contact') }}">Contact</a>
            </li>
        </ul>
    </nav>
</div>
