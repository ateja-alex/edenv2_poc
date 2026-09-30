<h3>{!! traduction('pdf.paiement.titre', $langue) !!}</h3>

@if($donnees->isNotEmpty())

<table class="css_tableau_donnees_fiche_pdf">
		<tbody>
			<tr>
				<td><b>{{ management('paiement')->champ('date')->modele->nom }}</b></td>
				<td><b>{{ management('paiement')->champ('titre')->modele->nom }}</b></td>
				<td><b>{{ management('paiement')->champ('montant')->modele->nom }}</b></td>
			</tr>
			@foreach($donnees as $donnee)
				<tr>
					<td>{{ formate_date('d/m/Y', $donnee->date) }}</td>
					<td>{{ $donnee->titre }}</td>
					<td>{{ management('paiement')->champ('montant')->affiche($donnee->montant) }}{!! maquette('devise_application_symbole') !!}</td>

				</tr>
			@endforeach
		</tbody>
	</table>

@else
	<h4>{!! traduction('pdf.paiement.pas_de_donnees', $langue) !!}</h4>
@endif