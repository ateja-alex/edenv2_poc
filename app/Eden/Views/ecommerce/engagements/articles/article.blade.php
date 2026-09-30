@extends('eden::ecommerce.template.template')
@section('content')
    <section class='css_section_container css_section_margin'>
        <div class="container-fluid">
            <div class="row">
                @include('eden::ecommerce.addons.menu-engagements')
                <div class="col-xs-12 col-sm-8 col-md-9">
                    <h2 class="css_titre_h2_amc_prod">
                        <span>Blog</span> et conseil
                    </h2>
                    <div class="css_categorie_blog_header">
                        <span class="css_text_categorie_blog">Catégorie du blog :</span>
                        <div class="css_liste_filtre_blog">
                            @if (isset($articles_par_categorie['2']))
                                <a class="btn btn-rouge-amc" href="{{ route('engagements-blog-categorie', ['id' => '2']) }}">
                                    Motorisation <span class="badge">
                                        {{$articles_par_categorie['2']}}
                                    </span>
                                </a>
                            @endif
                            @if (isset($articles_par_categorie['3']))
                                <a class="btn btn-rouge-amc" href="{{ route('engagements-blog-categorie', ['id' => '3']) }}">
                                    Porte de garage enroulable - coulissante<span class="badge">
                                        {{$articles_par_categorie['3']}}
                                    </span>
                                </a>
                            @endif
                            @if (isset($articles_par_categorie['4']))
                                <a class="btn btn-rouge-amc" href="{{ route('engagements-blog-categorie', ['id' => '4']) }}">
                                    Porte de garage latérale - coulissante <span class="badge">
                                        {{$articles_par_categorie['4']}}
                                    </span>
                                </a>
                            @endif
                            @if (isset($articles_par_categorie['5']))
                                <a class="btn btn-rouge-amc" href="{{ route('engagements-blog-categorie', ['id' => '5']) }}">
                                    Porte de garage sectionnelle - coulissante <span class="badge">
                                        {{$articles_par_categorie['5']}}
                                    </span>
                                </a>
                            @endif
                            @if (isset($articles_par_categorie['6']))
                                <a class="btn btn-rouge-amc" href="{{ route('engagements-blog-categorie', ['id' => '6']) }}">
                                    Tablier volet roulant <span class="badge">
                                        {{$articles_par_categorie['6']}}
                                    </span>
                                </a>
                            @endif
                            @if (isset($articles_par_categorie['7']))
                                <a class="btn btn-rouge-amc" href="{{ route('engagements-blog-categorie', ['id' => '7']) }}">
                                    Volet roulant <span class="badge">
                                        {{$articles_par_categorie['7']}}
                                    </span>
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 col-sm-8 col-md-12">
                            <h1 class="css_titre_blog_article js_last_word">
                                {{ $articleBlog->titre }}
                            </h1>
                            @if ($categorieArticle->id == 2 || $categorieArticle->id == 3 || $categorieArticle->id == 4 || $categorieArticle->id == 5)
                                <p>
                                    Une porte de garage est par essence encombrante et difficile à transporter,
                                    en raison de ses dimensions et de son poids. Toutefois, si vous envisagez
                                    d’installer vous-même votre porte, il sera nécessaire de faire livrer la dite
                                    porte à votre domicile, puis de la manipuler pour la mettre en place. Alors
                                    entre une porte livrée entièrement montée, et une porte livrée en plusieurs
                                    composants à assembler, quelle est la meilleure solution ?
                                </p>
                                <div class="text-center">
                                    {{-- Si porte de garage --}}
                                    <a href="{{ route('devis-porte-garage') }}" class="btn btn-rouge-amc css_btn_devis_blog_article">
                                        Obtenir mon devis en ligne gratuit de porte de garage
                                    </a>
                                </div>
                                {{-- Si tablier de volet roulant --}}
                            @elseif ($categorieArticle->id == 6)
                                <p>
                                    TO DO : Texte intro tablier de volet roulant
                                </p>
                                <div class="text-center">
                                    <a href="{{ route('devis-tablier') }}" class="btn btn-rouge-amc css_btn_devis_blog_article">
                                        Obtenir mon devis en ligne gratuit de tablier de volet roulant
                                    </a>
                                </div>
                                {{-- Si volet roulant --}}
                            @elseif ($categorieArticle->id == 7)
                                <p>TO DO : texte d'intro volet roulant</p>
                                <div class="text-center">
                                    <a href="{{ route('devis-volet-roulant') }}" class="btn btn-rouge-amc css_btn_devis_blog_article">
                                        Obtenir mon devis en ligne gratuit de volet roulant
                                    </a>
                                </div>
                            @endif
                        </div>
                        {{-- Contenu de l'article --}}
                        <div class="col-xs-12 col-sm-8 col-md-9">
                            <div class="css_contenu_article_blog">
                                {!! $articleBlog->contenu !!}
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
