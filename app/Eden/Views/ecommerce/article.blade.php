@extends('eden::ecommerce.template.template')

@section('meta')
	<title>{{ $article->titre_seo }}</title>
	<meta name="description" content="{{ $article->description_seo }}" />
	<meta name="keywords" content="{{ $article->mots_cles_seo }}" />
@endsection


@section('content')
    <container>
		<div id="vue">
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
						
						{{-- Contenu de la page --}}
						<div class="col-md-10 col-md-offset-1">
							<div class="pb-breadcrumb clearfix">
								<div class="breadcrumb clearfix">
									<a class="home" href="{{ route('ecommerce.accueil') }}">
										<i class="fas fa-home"></i>
									</a>
									<span class="navigation-pipe">&gt;</span>
									<a href="{{ route('ecommerce.url_ecommerce', 'accessoires') }}" class='text-capitalize'>Accessoires</a>
									<span class="navigation-pipe">&gt;</span>
									<a href="{{ route('ecommerce.url_ecommerce', $famille->url) }}" class='text-capitalize'>{{ $famille->nom }}</a>
									<span class="navigation-pipe">&gt;</span>
									{{ $article->designation }}
								</div>
							</div>

							<div class="row flex">
								<div class="col-xs-12 col-sm-5 col-md-4">
									<div class="css_img_produit_fiche_article" style="text-align: center;">
										@if($images->first() !== null)
											<img class='img-responsive' v-show="article.declinaison == 0" style="display: inline;" src="{{ asset('storage/'.basename($images->first()->chemin)) }}" alt="">
										@else										
											<img class='img-responsive' v-show="article.declinaison == 0" src="{{ asset('eden/images/article_sans_image.png') }}" alt="">
										@endif
										
										<img class='img-responsive' v-show="article.declinaison != 0" :src="'{{ asset('storage') }}/'+declinaison_image" alt="">
									</div>
								</div>
								<div class="col-xs-12 col-sm-7 col-md-8">
									<h1 class='css_titre_fiche_accessoire'>
										<span>{{ $article->designation }}</span>
									</h1>
									<p> {!! $article->description_courte !!} </p>

									<form action="{{ route('ecommerce.ajoute_produit_au_panier') }}" method="post" class="css_form_ajout_panier_fiche_produit form-horizontal">
										<div class="flex-end">
											<div class="css_block_quantite_article_produit">

												@if($article->type_declinaison == 1)
													@if($declinaisons->count() > 0)
														
														<div class="form-group">
															<label for="">Déclinaison</label>
															<select name="declinaison_id" v-model="article.declinaison">
																@foreach($declinaisons as $declinaison)
																	<option value="{{ $declinaison->id }}">{{ $declinaison->designation }} ({{ montant($declinaison->tarif) }} €)</option>
																@endforeach
															</select>
														</div>
													@endif
												@elseif($article->type_declinaison == 2) 
													
													<template v-for="(declinaison_multiple, famille, index) in declinaisons_mulitples">
														<div class="form-group">
															<label for="">@{{famille}}</label>
															<select name="declinaison_id[]" v-model="article.declinaison_mulitple[index]" @change="mise_a_jour_tarif(famille, index)">
																<option v-for="declinaison in declinaison_multiple" :value="declinaison">@{{ declinaison }} </option>
															</select>
														</div>
													</template>
												@endif

												<div class="form-group">
													<label for="">Quantité :</label>
													<input type="number" value="1" min="1" name="quantite" @wheel.prevent @keydown.up.prevent @keydown.down.prevent>
												</div>

												@if($article->taille == 1)
													<div class="form-group" v-if="declinaison_tarif > 0">
														<label for="">Taille :</label>
														<input v-model="article.taille" type="number" value="1" min="1" name="taille" @wheel.prevent @keydown.up.prevent @keydown.down.prevent>
													</div>
												@endif

												<div class="css_block_alignement_ajout_panier_prix_fiche_produit hidden-xs">

													<button v-if="declinaison_tarif > 0" type="submit" class="btn btn-rouge-amc css_btn_ajouter_produit_fiche_produit">
														Ajouter au panier
													</button>
													<button v-else @click.prevent="" class="btn disabled css_btn_ajouter_produit_fiche_produit">
														Ajouter au panier 
													</button>
												</div>
												<input type="hidden" value="{{ $article->id }}" name="article_id">
												<input type="hidden" value="{{ $article->tarif * (100 - $article->promo) / 100 }}" name="tarif_ht">
											</div>
											<div class="css_block_affichage_tarif_fiche_produit">
												{{-- SI PRIX NORMAL RETIRER CLASSE SOLDE --}}
												
												@if(!empty($article->promo))
													<span class='css_affichage_prix_fiche_produit solde'>@{{ declinaison_tarif.toFixed(2) }} &euro; TTC</span>
													<span class='css_reduction_prix_fiche_produit'>-{{ $article->promo }}%</span>
													<span class='css_affichage_prix_reduit_fiche_produit'>@{{ (declinaison_tarif * (100 - <?php echo $article->promo; ?>) / 100).toFixed(2) }} &euro; TTC</span>
												@else
													<span class='css_affichage_prix_reduit_fiche_produit'>@{{ declinaison_tarif.toFixed(2) }} &euro; TTC</span>
												@endif
											</div>
										</div>
										<div class="css_block_alignement_ajout_panier_prix_fiche_produit visible-xs">
											<button class="btn btn-rouge-amc css_btn_ajouter_produit_fiche_produit">
												Ajouter au panier
											</button>
										</div>
									</form>
								</div>
								<div class="col-xs-12 col-sm-4 col-md-3">
									<ul class="css_menu_fiche_produit">
										<li class='js_item_menu_fiche_produit active' data-info='avantage' @if(empty($article->description_longue)) style="display: none" @endif>
											Avantages
										</li>
										<li class='js_item_menu_fiche_produit' data-info='caracteristique' @if(empty($article->caracteristiques)) style="display: none" @endif>
											Caractéristiques
										</li>
										<li class='js_item_menu_fiche_produit' data-info='document' @if(empty($article->fiche_article_amc)) style="display: none" @endif>
											Documentation
										</li>
									</ul>
								</div>
								<div class="col-xs-12 col-sm-8 col-md-9">
									<div class="css_block_info_fiche_produit">
										<span class='js_afficher_text_info_avantage'>{!! str_replace('storage/', asset('storage').'/', $article->description_longue) !!}</span>
										<span class='js_afficher_text_info_caracteristique' style="display: none;">{!! str_replace('storage/', asset('storage').'/', $article->caracteristiques) !!}</span>
										<span class='js_afficher_text_info_document' style="display: none;"><a href="{{ asset('storage/'.$article->fiche_article_amc) }}">{!! $article->fiche_article_amc !!}</a></span>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</section>
		</div>

    </container>
@endsection

@section('javascript')
		<script>

			var vue_instance = new Vue({
				el: '#vue',
				data: {
					declinaisons: {!! $declinaisons !!},
					@if($declinaisons->first() !== null)
						article: {declinaison: {{$declinaisons->first()->id}},declinaison_mulitple: [], taille : 1},
					@else
						article: {declinaison: 0, declinaison_mulitple: [], taille : 1},
					@endif
					article_declinaison: {!! $article_declinaison !!},
					declinaisons_mulitples: {!! collect($declinaisons_mulitples) !!},
					declinaisons_mulitples_historique : {!! collect($declinaisons_mulitples) !!},
					prix_declinaison_multiple : 0,

				},
				computed: {
					
					declinaison_image: function() {
						
						if(this.article.declinaison == 0)
							return '';	
						
						if(this.declinaisons[this.article.declinaison] == undefined)
							return '';	
						
						return this.declinaisons[this.article.declinaison].image;
					},
					
					declinaison_tarif: function() {

						var taille = parseFloat(this.article.taille)
						
						if(this.article.declinaison == 0)
							return {{ $article->tarif }};	
						
						if(this.declinaisons[this.article.declinaison] == undefined)
							return {{ $article->tarif }};
						
						if(this.article.declinaison == 1)
							return this.declinaisons[this.article.declinaison].tarif;

						
						var familles = Object.keys(this.declinaisons_mulitples_historique)
						var context = this;

						if(familles.length == this.article.declinaison_mulitple.length) {

							$.post({

								url: "{{ route('ecommerce.retourne_prix_en_fonction_declinaison') }}",
								dataType: "json",
								data: {
									
									article_declinaison: this.article_declinaison,
									article: this.article,
									article_id : {{$article->id}}
								}
								}).done(function(donnees) {

									if(donnees.succes) {

										if(donnees.retour != null) {

											context.prix_declinaison_multiple = donnees.retour.tarif;
										}
										else {
											context.prix_declinaison_multiple = 0;

										}

									}
								});
						}
		
						return (this.prix_declinaison_multiple * taille);
					},
				},

				methods : {

					mise_a_jour_tarif: function(famille, index) {


						var numero_de_la_declinaison = (index + 1);
						var context = this;

						var declinaison_a_afficher = [];

						this.article_declinaison.map(function(declinaison) {

							if(declinaison['valeur_declinaison_'+numero_de_la_declinaison] == context.article.declinaison_mulitple[index]) {
								declinaison_a_afficher.push(declinaison)

							}
						})


						var familles = Object.keys(this.declinaisons_mulitples_historique)

						var reset = {};
							



						var tab = []
					


						declinaison_a_afficher.map(function(de){


							familles.map(function(f, index){

								index = (index + 1)
								if(tab[index] == undefined) {

									tab[index] = []
								}
								if(!tab[index].includes(de['valeur_declinaison_'+index])) {
									tab[index].push(de['valeur_declinaison_'+index])

								}

							})

							
						})

						familles.map(function(famille_declinaison, index){

							if(famille == famille_declinaison) {
								
								reset[famille_declinaison] = context.declinaisons_mulitples_historique[famille_declinaison]
							}
							else {

								reset[famille_declinaison] = tab[(index + 1)];
							}
						})




						this.declinaisons_mulitples = reset;


		
						 

					}
				}

			});
			
		</script>
@endsection
