@extends('eden::ecommerce.template.template')

@section('meta')
	<title>{{ $article->titre_seo }}</title>
	<meta name="description" content="{{ $article->description_seo }}" />
	<meta name="keywords" content="{{ $article->mots_cles_seo }}" />
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
									</a>&nbsp;&nbsp;
								@endif
							@endforeach
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <h1 class="css_titre_blog_article js_last_word">
                                {{ $article->titre }}
                            </h1>
                        </div>
                        {{-- Contenu de l'article --}}
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="css_contenu_article_blog">
                                {!! $article->contenu !!}
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
@endsection
@section('javascript')

@endsection
