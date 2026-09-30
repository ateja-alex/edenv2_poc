@extends('eden::ecommerce.template.template')

@section('meta')
	<title>Le blog de la menuiserie sur mesure</title>
	<meta name="description" content="Retrouvez de nombreux articles sur ce blog, concernant la porte de votre garage (isolation, installation,...), ou les volets roulants (pose…)" />
	<meta name="keywords" content="" />
@endsection


@section('content')
    <section class='css_section_container css_section_margin'>
        <div class="container-fluid">
            <div class="row">
                @include('eden::ecommerce.blog.menu_blog')
                <div class="col-xs-12 col-sm-8 col-md-9">
                    <h2 class="css_titre_h2_amc_prod">
                        <span>Blog</span> et conseil
                    </h2>
                    <div class="css_categorie_blog_header">
                        <span class="css_text_categorie_blog">Catégories du blog :</span>
                        <div class="css_liste_filtre_blog">
                            @foreach($categories as $categorie)
								@if($categorie->articles->count() > 0)
									<a class="btn btn-rouge-amc" href="{{ route('blog.url_blog', ['url' => $categorie->url]) }}">
										{{ $categorie->nom }} 
										<span class="badge">
											{{ $categorie->articles->count() }}
										</span>
									</a>
								@endif
							@endforeach
                        </div>
                    </div>
                    <div class="row">
                        {{-- Conteneur liste des articles --}}
                        @foreach ($articles as $article)
                            <div class="col-xs-12 col-sm-12 col-md-4">
                                <div class="css_item_block_article">
                                    <div class="css_block_lien_article">
                                        <a href="{{route('blog.url_blog', ['url' => $article->url])}}" class="btn btn-rouge-amc">Lire la suite</a>
                                    </div>
                                    <a href="">
                                        <img class="css_img_banniere_article" src="{{ asset('storage/'.$article->image_principale) }}" alt="">
                                        <h4>
                                            {{ $article->titre }}
                                        </h4>
                                    </a>
                                    <p class="css_text_article_blog_preview">
                                        {!! $article->description_seo !!}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                        <div class="col-xs-12 col-sm-12 col-md-12 text-center">
                            {{ $articles->links() }}
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
@endsection
@section('javascript')

@endsection
