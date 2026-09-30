<h3>{!! traduction('pdf.tache.titre', $langue) !!}</h3>

@if($donnees->isNotEmpty())
<table class="css_tableau_donnees_fiche_pdf">
	<tbody>
		<tr>
			<td><b>{{ management('tache')->champ('titre')->modele->nom }}</b></td>
			<td><b>{{ management('tache')->champ('affectation')->modele->nom }}</b></td>
			<td><b>{{ management('tache')->champ('commentaire')->modele->nom }}</b></td>
		</tr>
		@foreach($donnees as $donnee)

			<tr>
				<td>{{$donnee->titre}}</td>
				<td>{{ management('tache')->champ('affectation')->affiche($donnee->affectation) }}</td>
				<td>{{ $donnee->commentaire }}</td>
			</tr>
		@endforeach
	</tbody>
</table>

@else
	<h4>{!! traduction('pdf.tache.pas_de_donnees', $langue) !!}</h4>

@endif

