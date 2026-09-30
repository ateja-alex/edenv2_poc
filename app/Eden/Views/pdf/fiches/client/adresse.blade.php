<h3>{!! traduction('pdf.adresse.titre_client', $langue) !!}</h3>
@if($donnees->isNotEmpty())

	<table class="css_tableau_donnees_fiche_pdf">
		<thead>
			<tr>
				<th>{{ management('client')->champ('nom')->modele->nom }}</th>
				<th>{{ management('client')->champ('adresse')->modele->nom }}</th>
				<th>{{ management('adresse')->champ('adresse_complement')->modele->nom }}</th>
				<th>{{ management('client')->champ('code_postal')->modele->nom }}</th>
				<th>{{ management('client')->champ('ville')->modele->nom }}</th>
			</tr>
		</thead>
		<tbody>
			@foreach($donnees as $donnee)
				<tr>
					<td>{{ $donnee->societe }}</td>
					<td>{{ $donnee->adresse }}</td>
					<td>{{ $donnee->adresse_complement }}</td>
					<td>{{ $donnee->code_postal }}</td>
					<td>{{ $donnee->ville }}</td>
				</tr>
			@endforeach
		</tbody>
	</table>

@else
	<h4>{!! traduction('pdf.adresse.pas_de_donnees', $langue) !!}</h4>
@endif