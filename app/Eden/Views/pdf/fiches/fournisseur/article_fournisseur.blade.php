<?php
$management_article_fournisseur = management('article_fournisseur');
?>

<h3>{!! traduction('pdf.article_fournisseur.titre', $langue) !!}</h3>
@if(!empty($donnees))

	<table class="css_tableau_donnees_fiche_pdf">
		<thead>
			<tr>
				<!-- <th>Désignation Interne</th> -->
				<th>{!! traduction('pdf.article_fournisseur.designation', $langue) !!}</th>
				<th>{!! traduction('pdf.article_fournisseur.designation_fournisseur', $langue) !!}</th>
				<th>{!! traduction('pdf.article_fournisseur.reference_fournisseur', $langue) !!}</th>
				<th>{!! traduction('pdf.article_fournisseur.dispo', $langue) !!}</th>
				<th>{!! traduction('pdf.article_fournisseur.conditionnement', $langue) !!}</th>
				<th>{!! traduction('pdf.article_fournisseur.actuel', $langue) !!}</th>
				<th>{!! traduction('pdf.article_fournisseur.previsionnel', $langue) !!}</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			@php 
				$famille_id_precedent = 0;
			@endphp
			@foreach($donnees as $donnee)
				@php 
					$famille = modele('famille',$donnee->famille_id);
					$management_article = management('article', $donnee->id);

					// Calcul des stocks
					$stock_actuel = $management_article->stock_actuel();
					$stock_previsionnel = $management_article->stock_previsionnel($donnee,$stock_actuel);
					
					if(!empty($donnee->infos_article_fournisseur->tarif))
						$prix_achat = $donnee->infos_article_fournisseur->tarif;
					else
						$prix_achat = $donnee->prix_d_achat;
				@endphp
				@if($famille_id_precedent != $famille->id)
					@php
						$famille_id_precedent = $famille->id;
					@endphp
					<tr style="background: #ddd">
						<td colspan="8" style="line-height: 15px;">{{ $famille->nom }}</td>
					</tr>
				@endif
				<tr>
					<td colspan="1">
						<span style="font-size: 9px;">
						{{ $donnee->designation }}
						</span>
					</td>
					<td colspan="1">
						<span style="font-size: 9px;">
						{{ $donnee->infos_article_fournisseur->designation }}
						</span>
					</td>
					<!-- 
					<td>
						<span style="font-size: 11px;">
						@if($donnee->infos_article_fournisseur->designation != null && $donnee->infos_article_fournisseur->designation != "")
							{{ $donnee->infos_article_fournisseur->designation }}
						@else
							{{ $donnee->designation }}
						@endif
						<span style="font-size: 9px;">
						(
						Réf. @if($donnee->infos_article_fournisseur->reference != null && $donnee->infos_article_fournisseur->reference != "")
							{{ $donnee->infos_article_fournisseur->reference }}
						@endif
						@if(!empty($donnee->infos_article_fournisseur->disponibilite))
						, sous {{ $donnee->infos_article_fournisseur->disponibilite }}
						@endif
						@if(!empty($donnee->infos_article_fournisseur->conditionnement))
						, par {{ $donnee->infos_article_fournisseur->conditionnement }} {{ $management_article_fournisseur->champ('unite')->affiche($donnee->infos_article_fournisseur->unite) }}
						@endif
						
						)
						</span>
						
						</span>
					</td>
					-->
{{--					<!-- <td colspan="1">{{ montant($prix_achat, 2) }}&euro;</td> -->--}}
					<td colspan="1" style="font-size: 9px;">
						@if($donnee->infos_article_fournisseur->reference != null && $donnee->infos_article_fournisseur->reference != "")
							{{ $donnee->infos_article_fournisseur->reference }}
						@endif
					</td>
					<td colspan="1" style="font-size: 9px;">
						@if(!empty($donnee->infos_article_fournisseur->disponibilite))
							{{ $donnee->infos_article_fournisseur->disponibilite }}
						@endif
					</td>
					<td colspan="1" style="font-size: 9px;">
						{{ $donnee->infos_article_fournisseur->conditionnement }} {{ $management_article_fournisseur->champ('unite')->affiche($donnee->infos_article_fournisseur->unite) }}
					</td>
					<td colspan="1" style="font-size: 9px;">{{ $stock_actuel }}</td>
					<td colspan="1" style="font-size: 9px;">{{ $stock_previsionnel }}</td>
					<td colspan="1"><div style="padding: 0px 4px;">&nbsp;</div></td>
				</tr>
			@endforeach
		</tbody>
	</table>

@else
	<h4>{!! traduction('pdf.article_fournisseur.pas_de_donnees', $langue) !!}</h4>
@endif