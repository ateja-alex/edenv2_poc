<h3>{!! traduction('pdf.fournisseur.informations', $langue) !!}</h3>

<table class="css_tableau_donnees_fiche_pdf">
	<tbody>
		<tr>
			<td style="font-weight: bold;">{{ management('fournisseur')->champ('nom')->modele->nom }}</td>
			<td>{{$donnees->nom}} {{$donnees->prenom}}</td>
		</tr>
		<tr>
			<td style="font-weight: bold;">{{ management('fournisseur')->champ('adresse_email')->modele->nom }}</td>
			<td>{{$donnees->adresse_email}}</td>
		</tr>
		<tr>
			<td style="font-weight: bold;">{{ management('fournisseur')->champ('telephone')->modele->nom }}</td>
			<td>{{$donnees->telephone}}</td>
		</tr>
	</tbody>
</table>

