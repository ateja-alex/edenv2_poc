<h3>{!! traduction('pdf.echange.titre', $langue) !!}</h3>

@if($donnees->isNotEmpty())

<table class="css_tableau_donnees_fiche_pdf" style="width: 100%; table-layout: fixed">
		<tbody>
			<tr>
				<td style="width: 20%"><b>{{ management('echange')->champ('type')->modele->nom }}</b></td>
				<td style="width: 60% !important;"><b>{{ management('echange')->champ('description')->modele->nom }}</b></td>
				<td style="width: 20%"><b>{{ management('echange')->champ('date')->modele->nom }}</b></td>
			</tr>
			@foreach($donnees as $donnee)
				<tr>
					<td style="width: 20%">{!! management('echange')->champ('type')->affiche($donnee->type) !!}</td>
					<td style="width: 60% !important;"><div style="width:100%;overflow: hidden; text-overflow: ellipsis;white-space: nowrap;">{!! $donnee->description !!}</div></td>
                    <td style="width: 20%">{{ formate_date('d/m/Y', $donnee->date) }}</td>

				</tr>
			@endforeach
		</tbody>
	</table>

@else
	<h4>{!! traduction('pdf.echange.pas_de_donnees', $langue) !!}</h4>
@endif