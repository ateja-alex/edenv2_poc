<h3>{!! traduction('pdf.contact.informations', $langue) !!}</h3>
@if($donnees->isNotEmpty())

<table class="css_tableau_donnees_fiche_pdf">
	<thead>
		<tr>
			<th>{{ ucfirst(table_libre('contact')->element) }}</th>
			<th>{{ management('contact')->champ('poste')->modele->nom }}</th>
			<th>{{ management('contact')->champ('telephone')->modele->nom }}</th>
			<th>{{ management('contact')->champ('adresse_email')->modele->nom }}</th>
		</tr>
	</thead>
	<tbody>
		@foreach($donnees as $donnee)
			<tr>
				<td>{{ $donnee->prenom }} {{ $donnee->nom }}</td>
				<td>{{ $donnee->poste }}</td>
				<td>{{ $donnee->telephone }}</td>
				<td>{{ $donnee->adresse_email }}</td>
			</tr>
		@endforeach
	</tbody>
</table>

@else
	<h4>{!! traduction('pdf.contact.pas_de_donnees', $langue) !!}</h4>
@endif