@extends('eden::ecommerce.template.template')

@section('meta')
	<title>{{ $famille->titre_seo }}</title>
	<meta name="description" content="{{ $famille->description_seo }}" />
	<meta name="keywords" content="{{ $famille->mots_cles_seo }}" />
@endsection


@section('content')
    <container>

        <section class='css_section_container css_section_margin'>
            <div class="container">
                <div class="row">
					
					@if(!empty(session('erreur')))
						<div class="col-xs-12 col-md-12 alert alert-danger text-center">
							{{ session('erreur') }}
						</div>
					@endif
					@if(!empty(session('information')))
						<div class="col-xs-12 col-md-12 alert alert-success text-center">
							{{ session('information') }}
						</div>
					@endif
					
					<div class="col-md-12">
						<div class="pb-breadcrumb clearfix">
							<div class="breadcrumb clearfix">
								<a class="home" href="{{ route('ecommerce.accueil') }}">
									<i class="fas fa-home"></i>
								</a>
								@if($famille->id != 5)
									<span class="navigation-pipe">&gt;</span>
									<a href="{{ route('ecommerce.url_ecommerce', 'accessoires') }}" class='text-capitalize'>Accessoires</a>
								@endif
								<span class="navigation-pipe">&gt;</span>
								<a href="{{ route('ecommerce.url_ecommerce', [$famille->url]) }}" class='text-capitalize' title="{{ $famille->nom }}" alt="{{ $famille->nom }}">{{ $famille->nom }}</a>
								<span class="navigation-pipe">&gt;</span>
							</div>
						</div>
					</div>
                    {{-- Block d'informations gauche --}}
                    <div class="col-md-3 hidden-xs hidden-sm">
                        @include('eden::ecommerce.addons.block-informations-gauche')
                    </div>

                    {{-- Contenu de la page --}}
                    <div class="col-xs-12 col-md-9">
                        <div class="row flex">
                            <div class="col-xs-12 col-sm-12 col-md-12">
                                <h1 class='css_titre_accessoires'>
									{{ $famille->nom }} 
                                    @if($famille->url != 'accessoires')
                                    <span>({{ count($contenu_famille['articles']) }} produits)</span>
                                    @endif
                                </h1>
                                <p>
									{!! $famille->texte_introduction !!}
                                </p>
                                @if($famille->url != 'accessoires')
                                    <div class="flex-center">
                                        <span>Trier par </span>
                                        <div class="select-amc css_select_trier_articles">
    										<form method="post" action="{{ route('ecommerce.url_ecommerce', [$famille->url]) }}">
    											{!! csrf_field() !!}
    											<select name="tri" onChange="$(this).parents('form').submit();">
    												<option value="tarif_croissant" @if(isset($parametres) && $parametres['tri'] == 'tarif_croissant') selected @endif >Le moins cher</option>
    												<option value="tarif_decroissant" @if(isset($parametres) && $parametres['tri'] == 'tarif_decroissant') selected @endif >Le plus cher</option>
    											</select>
    										</form>
                                        </div>
                                    </div>
                                @endif
                            </div>
							@foreach($contenu_famille['articles'] as $article)
								<div class="col-xs-12 col-sm-4 col-md-3">
									<div class="css_conteneur_block_article_accessoires">
										<a class="css_block_article_accessoires" href="{{ route('ecommerce.url_ecommerce', [$article->url]) }}">
											<div class="css_img_article_accessoire" @if($article->images->first() !== null) style="background-image: url({{ asset('storage/'.basename($article->images->first()->chemin)) }})" @else style="background-image: url({{ asset('eden/images/article_sans_image.png') }})"  @endif ></div>
											<p class='css_description_article_accessoire' title="{{ $article->designation }}">
												{{ $article->designation }}
											</p>
											<span class='css_prix_article_accessoire'>
												@if(!empty($article->promo))
													<strike>{{ number_format($article->tarif, 2, ',', ' ') }} &euro;</strike>
													<span class='css_reduction_prix_fiche_produit' style="font-size: 12px">-{{ $article->promo }}%</span>
													{{ number_format($article->tarif * (100 - $article->promo) / 100, 2, ',', ' ') }} &euro;
												@else
													{{ number_format($article->tarif, 2, ',', ' ') }} &euro;
												@endif
											</span>
										</a>
										@if(App\Eden\Models\Article_declinaison::where('article_id', $article->id)->count() == 0)
											<span class='btn btn-rouge-amc css_btn_articles' onClick="ajoute_produit_au_panier({{ $article->id }});">Ajouter au panier</span>
											<a class="btn btn-gris-amc css_btn_articles" href="{{ route('ecommerce.url_ecommerce', [$article->url]) }}">Voir la fiche produit</a>
										@else
											<a class="btn btn-gris-amc css_btn_articles" href="{{ route('ecommerce.url_ecommerce', [$article->url]) }}">Voir les options</a>
										@endif
									</div>
								</div>
                            @endforeach
                            @if($famille->url == 'accessoires')
								@foreach($sous_categories as $famille)
									<div class="col-xs-12 col-sm-4 col-md-3">
										<div class="css_conteneur_block_article_accessoires">
											<a class="css_block_article_accessoires" href="{{ route('ecommerce.url_ecommerce', [$famille->url]) }}">
												<div class="css_img_article_accessoire" style="background-image: url({{ asset('storage/'.$famille->image) }})"></div>
												<p class='css_description_article_accessoire' title="{{ $famille->nom }}">
													{{ $famille->nom }}
												</p>
											</a>
											<a class="btn btn-gris-amc css_btn_articles" href="{{ route('ecommerce.url_ecommerce', [$famille->url]) }}">Voir les produits</a>
										</div>
									</div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </container>
	
	<form style="display: hidden" id="formulaire_ajout_produit" action="{{route('ecommerce.ajoute_produit_au_panier')}}" method="post">
		<input type="hidden" name="article_id" id="ajout_produit_article_id" />
		<input type="hidden" name="quantite" value="1" />
		<input type="hidden" name="url_retour" value="{{ $famille->url }}" />
	</form>
@endsection

@push('javascript')
	
	<script>
	function ajoute_produit_au_panier(article_id) {
		
		$('#ajout_produit_article_id').val(article_id);
		
		$('#formulaire_ajout_produit').submit();
	}
	
	</script>
@endpush
