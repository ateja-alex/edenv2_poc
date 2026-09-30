@extends('ecommerce.template.template')
@section('content')

	@if ( isset($erreur) )
		<div class="alert alert-danger">
			{{ $erreur }}
		</div>
	@else

	    <section class="css_section_margin">
	        <div class="container">
	            <div class="row flex flex-start">
	                <div class="col-md-8">
	                    <h1 class="css_titre_mon_compte">Commande {{ $facture_vente->modele->reference_document }}</span></h1>
	                </div>
	                <div class="col-md-4">
	                	<a href="{{ route('ecommerce.telecharger_facture', $facture_vente->modele->id) }}" target="_blank"><button class="btn btn-secondary-arrow btn-sm-arrow" style="margin-top:25px;">Télécharger</button></a>
	                </div>
	                <div class="col-md-12">

	                	Commandé le {{ formate_date('d/m/Y', $facture_vente->modele->date) }}

	                    <br><br>TODO : Afficher Adresses de livraison et de facturation



	                    <div class="table-cart b-table-cart">
							<div class="table-responsive">
								<table class="table">
									<thead>
										<tr>
											<th>détails du produit</th>
											<th>prix unitaire</th>
											<th>Quantité</th>
											<th colspan="2">sous-total</th>
										</tr>
									</thead>
									<tbody>

										@foreach($facture_vente->articles() as $article)

											<tr class="wow fadeInLeft ligneArticle<?=$article->article->id?>">
												<td class="product">
													<div class="b-item-card">
														<div class="product-image">
															<a href="{{ route('ecommerce.url_ecommerce', ['url' => $article->article->url]) }}">
																<img src="{{ asset( isset(management('article', $article->article->id)->images()[0]) ? management('article', $article->article->id)->images()[0]['chemin'] : 'uploads/no-image.png' ) }}" class="img-responsive center-block" alt="/">
															</a>
														</div>
														<div class="caption">
															<p class="product-name">
																{{ $article->article->designation }}
															</p>
														</div>
													</div>
												</td>
												<td class="price">
													<div class="caption">
														<p class="product-price">
															{{ number_format($article->article->tarif,2) }} €
														</p>
													</div>
												</td>
												<td class="quantity">
													<div class="caption">
														<p class="product-price">
																  {{ $article->quantite }}
														</p>
													</div>
												</td>
												<td class="subtotal">
													<div class="caption">
														<p class="product-price">
															<span class="subtotal" id="soustotal{{ $article->article->id }}">{{ number_format($article->article->tarif * $article->quantite,2) }} €</span>
														</p>
													</div>
												</td>
											</tr>
											<tr class="spacer ligneArticle<?=$article->article->id?>">
												<td colspan="5"></td>
											</tr>

										@endforeach
									</tbody>
								</table>
							</div>
						</div>
	                </div>



					<div class="b-shipping-total">
						<div class="container">
							<div class="row">
								<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4 col-md-offset-4 wow fadeInLeft">

									<a href="{{ route('ecommerce.telecharger_facture', $facture_vente->modele->id) }}" target="_blank"><button class="btn btn-secondary-arrow btn-sm-arrow" style="margin-top:25px;">Télécharger</button></a>

								</div>
								<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4 wow fadeInRight">
									<div class="b-total">
										<div class="table-total">
											<table class="table">
												<tbody>
													<tr>
														<td class="text-right">
															Total HT
														</td>
														<td class="text-right">
															<strong id="totalPanier">{{ montant($facture_vente->modele->montant_document_ht) }} €</strong>
														</td>
													</tr>
													<tr>
														<td class="text-right">
															TVA
														</td>
														<td class="text-right">
															<strong id="totalPanier">{{ montant($facture_vente->modele->montant_document_tva) }} €</strong>
														</td>
													</tr>
													<tr>
														<td class="text-right">
															Total TTC
														</td>
														<td class="text-right">
															<strong id="totalPanier">{{ montant($facture_vente->modele->montant_document_ttc) }} €</strong>
														</td>
													</tr>
												</tbody>
											</table>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
	            </div>
	        </div>
	    </section>
    @endif
@endsection
