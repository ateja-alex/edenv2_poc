@extends('eden::listes.includes.details_ligne_stocks',$liste_stock)

@push('informations_supplementaire')
	<h3>{{ traduction('interface.details_lignes.article.commandes_en_cours') }}</h3>
	<table class="table table-bordered" width="100%" cellspacing="0" colspan="8">
		<thead>
			<tr>
				<th>{{ traduction('interface.details_lignes.article.type') }}</th>
				<th>{{ traduction('interface.details_lignes.article.client_fournisseur') }}</th>
				<th>{{ traduction('interface.details_lignes.article.reference_document') }}</th>
				<th>{{ traduction('interface.details_lignes.article.date_de_commande') }}</th>
				<th>{{ traduction('interface.details_lignes.article.quantite') }}</th>


			</tr>
		</thead>
		<tbody>

			@foreach($commandes_en_cours as $ligne)
				<tr>
					<td>{{ $ligne['type'] }}</td>
					<td>{!! $ligne['tiers'] !!}</td>
					<td>{!! $ligne['reference_document'] !!}</td>
					<td>{{ $ligne['date'] }}</td>
					<td>{{ round($ligne['quantite']) }}</td>

				</tr>
			@endforeach
		</tbody>
	</table>


	<h3>{{ traduction('interface.details_lignes.article.approvisionnement') }}</h3>

	<table class="table table-bordered" width="100%" cellspacing="0" colspan="8">
		<thead>
			<tr>
				<th>{{ traduction('interface.details_lignes.article.conditionnement') }}</th>
				<th>{{ traduction('interface.details_lignes.article.fournisseur') }}</th>
				<th>{{ traduction('interface.details_lignes.article.tarif') }}</th>
				<th>{{ traduction('interface.details_lignes.article.disponibilite') }}</th>


			</tr>
		</thead>
		<tbody>

			@foreach($appro_fournisseur as $ligne)
				<tr>
					<td>{{ $ligne['conditionnement'] }}</td>
					<td>{!! $ligne['fournisseur'] !!}</td>
					<td>{{ $ligne['tarif'] }}</td>
					<td>{{ $ligne['disponibilite'] }}</td>

				</tr>
			@endforeach
		</tbody>
	</table>
@endpush
