<style>

	table {
		border: 1px solid #dedede;
		width: 100%;
		background-color: transparent;
		border-collapse: collapse;
	}

	td {
		border: 1px solid #dedede;
		padding: 6px;
	}
</style>
<table>
	<tr>
	@foreach($colonnes as $colonne)
		<td>{{ $colonne->nom }}</td>
	@endforeach
	</tr>
	@foreach($lignes as $ligne)
		<tr>
			@foreach($colonnes as $colonne)
				<td>{!! $ligne[$colonne->id] !!}</td>
			@endforeach
		</tr>
	@endforeach
</table>