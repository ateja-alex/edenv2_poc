<table>
	<tr>
	@foreach($colonnes as $colonne)
		<td>{{ $colonne}}</td>
	@endforeach
	</tr>
	@foreach($articles as $article)
		<tr>
			@foreach($colonnes as $colonne)
				<td>{{ strip_tags($article[$colonne]) }}</td>
			@endforeach
		</tr>
	@endforeach
</table>