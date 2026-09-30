<header id="header" class='header'>
    {{-- Header Desktop --}}
    <div class="container-fluid hidden-xs hidden-sm">
        <div class="row flex-center css_pre_header">
            <div class="col-md-5">
                <a href="{{route('ecommerce.accueil')}}">
                    <div class="inline-flex-center css_header_block_logo_amc">
                        <div class='css_conteneur_deux_logo_amc'>
                            <img class='img-responsive' src="{{ asset('ecommerce-amc/images/logoamc.png') }}" alt="">
                            <span class="oswald css_text_header_subtext">Fabricant Français - Prix Direct Usine</span>
                        </div>
                        <img class='img-responsive' src="{{ asset('ecommerce-amc/images/logo-france.png') }}" alt="">
                        {{-- Afficher image si promo --}}
                        @if(!empty(parametre('date_fin_promotion_generale_amc')) && formate_date('Y-m-d',parametre('date_fin_promotion_generale_amc')) > date('Y-m-d'))
                        <img src="{{ asset('storage/'.parametre("image_promo_amc")) }}" alt="" class="img-responsive">
                        @endif
                    </div>
                </a>
            </div>
            <div class="col-md-7 css_block_droit_header">
				<div class="oswald mr-1 ml-auto" id="block_compteur">
				@if(!empty(parametre('date_fin_promotion_generale_amc')) && formate_date('Y-m-d',parametre('date_fin_promotion_generale_amc')) > date('Y-m-d'))
					<div class="countdown_dashboard_tab" id="countdown_dashboard">
					  <span class="countdown-txt">Fin de <br class='hidden-xs hidden-sm'>promo dans : </span>
					  <ul id="countdown">
						<li>
						  <span class="days">00</span>
						  <p class="timeRef">jours</p>
						</li>
						<li>
						  <span class="hours">00</span>
						  <p class="timeRef">heures</p>
						</li>
						<li>
						  <span class="minutes">00</span>
						  <p class="timeRef">minutes</p>
						</li>
						<li>
						  <span class="seconds">00</span>
						  <p class="timeRef">secondes</p>
						</li>
					  </ul>
					</div>
				@endif
				</div>
                <div class="css_header_block_assistance oswald">
                    @svg('question', 'css_svg_question_header')
                    <p>
                        Besoin d'assistance ?
                        <br>
                        <span>
                            <a href="tel:+33366729188">03 66 72 91 88</a> / <a href="{{ route('engagements-contact') }}" class='css_contacter_nous_header'>Contactez-nous</a>
                        </span>
                    </p>
                </div>
                <div class="css_header_block_compte_panier">
                    @if(session()->get('utilisateur_eden_ecommerce') !== null)
                    <div style="display: flex;align-items: flex-end;justify-content: center;margin-top: 20px;">
                        @else
                    <div style="display: flex;flex-direction: column;align-items: center;justify-content: center;">
                        @endif
                    <a href="{{ route('ecommerce.mon_compte') }}" class='oswald' style="display: flex;flex-direction: column;align-items: center;justify-content: center;color:#3b3b3b;font-weight:500;">
                        @svg('user', 'css_svg_user_header')
                        <span style="display: flex;align-items: center;">
                            @if(session()->get('utilisateur_eden_ecommerce') !== null)
								@if(!empty(modele('client', session()->get('utilisateur_eden_ecommerce'))->adresse_email))
									{{ mb_substr(modele('client', session()->get('utilisateur_eden_ecommerce'))->adresse_email,0,7).'...' }}
								@else
									Mon compte
								@endif
							@else
								Mon compte
							@endif

                        </span>
                    </a>
                    @if(session()->get('utilisateur_eden_ecommerce') !== null)
                        <a href="{{ route('ecommerce.deconnexion') }}" class="btn btn-rouge-amc css_btn_logout_header"><i class="fas fa-sign-out-alt" title="se déconnecter"></i></a>
                        @endif
                    </div>
                    <a href="{{ route('ecommerce.mon_compte') }}" class='oswald' style="display: none">
                        @svg('user-pro', 'css_svg_user_header')
                        Mon Compte Pro
                    </a>
                    <a href="{{ route('panier') }}" class='oswald'>
                        @svg('cart', 'css_svg_cart_header')
                        Mon Panier
						@if(\App\Managements\Ecommerce_management::nombre_articles_dans_panier() > 0)
						<span class="css_bulle_article_dans_panier">
							{{ \App\Managements\Ecommerce_management::nombre_articles_dans_panier() }}
                        </span>
						@endif
                    </a>
                </div>
            </div>
        </div>
        <div class="row">
            <nav class="col-md-12 css_flex_nav_header sticky-header">
                <a href="{{ route('ecommerce.accueil') }}" class="{{ active('ecommerce.accueil') }}">
                    @svg('home', 'css_svg_home_header')
                </a>
                <a href="{{ route('porte-garage') }}" class="{{ active(['guide-de-pose/porte-de-garage', 'porte-garage']) }}">
                    Porte de garage sur mesure
                </a>
                <a href="{{ route('volet-roulant') }}" class="{{ active(['guide-de-pose/volet-roulant', 'volet-roulant']) }}">
                    Volet roulant sur mesure
                </a>
                <a href="{{ route('tablier') }}" class="{{ active(['guide-de-pose/tablier', 'tablier']) }}">
                    Tablier sur mesure
                </a>
                <a href="{{ route('ecommerce.url_ecommerce', 'accessoires') }}" class="{{ active(['accessoires/*']) }}">
                    Accessoires
                </a>
                <a href="" class='btn btn-rouge-amc css_btn_devis_header' data-toggle="modal" data-target="#modal_selection_devis">
                    Obtenir votre devis gratuit
                    <img src="{{ asset('ecommerce-amc/images/pictos/fleche-next.png') }}" alt="">
                </a>
            </nav>
        </div>
    </div>
    {{-- Header Mobile --}}
    <div class="container-fluid visible-xs visible-sm">
        <div class="row">
            <div class="col-xs-8 col-xs-offset-2 col-sm-6 col-sm-offset-3">
                <a href="{{ route('ecommerce.accueil') }}">
                    <div class="css_mobile_logo_header">
                        <img class='img-responsive' src="{{ asset('ecommerce-amc/images/logoamc.png') }}" alt="">
                        <img class='img-responsive' src="{{ asset('ecommerce-amc/images/baseline.png') }}" alt="">
                    </div>
                </a>
            </div>
            <div class="col-xs-12 css_mobile_nav js-sticky-header-mobile">
                <div class="css_mobile_menu_burger_header js_mobile_menu_burger_header">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                {{-- <img class='img-responsive' src="{{ asset('ecommerce-amc/images/icone-amc.png') }}" alt=""> --}}
                <a href="" class='btn btn-rouge-amc css_mobile_btn_devis_header' data-toggle="modal" data-target="#modal_selection_devis">
                    Obtenir votre devis gratuit
                    <img src="{{ asset('ecommerce-amc/images/pictos/fleche-next.png') }}" alt="">
                </a>
            </div>
        </div>
    </div>
</header>
