<h3>{!! traduction('pdf.contact.informations', $langue) !!}</h3>
@if($donnees->isNotEmpty())

<table class="css_tableau_donnees_fiche_pdf">
	<tbody>
		@foreach($donnees as $donnee)
			<tr>
				<td colspan="2" style="font-weight: bold;">{{ $donnee->nom }}</td>
			</tr>
			<tr>
				<td>{{ management('fournisseur')->champ('telephone')->modele->nom }} </td>
				<td>{{ $donnee->telephone }}</td>
			</tr>
			<tr>
				<td>{{ management('fournisseur')->champ('adresse_email')->modele->nom }}</td>
				<td>{{ $donnee->adresse_email }}</td>
			</tr>
		@endforeach
	</tbody>
</table>

@else
	<h4>{!! traduction('pdf.contact.pas_de_donnees', $langue) !!}</h4>
@endif