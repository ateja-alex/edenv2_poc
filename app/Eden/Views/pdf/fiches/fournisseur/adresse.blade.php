<h3>{!! traduction('pdf.adresse.titre_fournisseur', $langue) !!}</h3>
@if($donnees->isNotEmpty())

	<table class="css_tableau_donnees_fiche_pdf">
		<tbody>
			@foreach($donnees as $donnee)
				<tr>
				 	<td style="font-weight: bold;">{{ management('fournisseur')->champ('adresse')->modele->nom }}</td>
					<td colspan="2">{{ $donnee->adresse }}</td>
					
				</tr>
				<tr>
					<td style="font-weight: bold;">{{ management('fournisseur')->champ('code_postal')->modele->nom }} & {{ management('fournisseur')->champ('ville')->modele->nom }}</td>
					<td>{{ $donnee->code_postal }} {{ $donnee->ville }}</td>
				</tr>
			@endforeach
		</tbody>
	</table>

@else
	<h4>{!! traduction('pdf.adresse.pas_de_donnees', $langue) !!}</h4>
@endif