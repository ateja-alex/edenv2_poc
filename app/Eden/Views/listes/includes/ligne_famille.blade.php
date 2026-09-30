<tr>
	<td><span class="css__lien" @click="modifier({{ $famille->id }})">{{ $famille->id }}</a></td>
	<td style="padding-left: {{ 10 + $rang * 50}}px;">{{ $famille->nom }}</td>
	<td>{{ modele('article')->where('famille_id', $famille->id)->count() }}</td>
	<td></td>
</tr>
		
@foreach(modele('famille')->where('parent_id', $famille->id)->get() as $famille)

	@include('eden::listes.includes.ligne_famille', ['famille' => $famille, 'rang' => $rang +1])
	
@endforeach
	