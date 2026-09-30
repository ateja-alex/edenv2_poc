<h3>{!! traduction('pdf.projet.titre', $langue) !!}</h3>

@if($donnees->isNotEmpty())

	<table class="css_tableau_donnees_fiche_pdf">
		<thead>
			<tr>
				<th>{{ management('projet')->champ('date')->modele->nom }}</th>
				<th>{{ ucfirst(table_libre('projet')->element) }}</th>
				<th>{{ management('projet')->champ('valeur')->modele->nom }}</th>
				<th>{{ management('projet')->champ('statut')->modele->nom }}</th>
			</tr>
		</thead>
		<tbody>
			@foreach($donnees as $donnee)
				<tr>
					<td>{{ $donnee->date }}</td>
					<td>{{ $donnee->nom }}</td>
					<td>{{ management('projet',$donnee->id)->champ('valeur')->affiche() }}{!! maquette('devise_application_symbole') !!}</td>
					<td>{{ management('projet',$donnee->id)->champ('statut')->affiche() }}</td>
				</tr>
			@endforeach
		</tbody>
	</table>

@else
	<h4>{!! traduction('pdf.projet.pas_de_donnees', $langue) !!}</h4>
@endif