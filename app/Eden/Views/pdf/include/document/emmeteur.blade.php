@if(!empty($entite_management->modele))
	<table style="width: 100%;text-align: left;"cellpadding="0">
		<tbody>
			<td>
				{{ $entite_management->modele->nom }}
				<br>
				{{ $entite_management->modele->adresse }}
				<br>
				{{ !empty($entite_management->modele->adresse_complement) ? $entite_management->modele->adresse_complement.'<br>' : '' }}
				{{ $entite_management->modele->code_postal }} {{ $entite_management->modele->ville }}
				<br>
				{!! traduction('document.pdf.emetteur.telephone') !!} : {{ $entite_management->modele->numero_telephone }}
			</td>
		</tbody>
	</table>
@endif

