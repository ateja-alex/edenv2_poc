<h5 class="mb-2">
	@traduction('interface.sous_totaux_listes.faire_sous_totaux') :
</h5>

<?php

/**

@note: refaire le système de cache qui bugue

*/

if(session()->has('cache.champs_libres_pour_sous_totaux.'.$type_element) && cache_actif() && false) {
	
	$champs_libres_pour_sous_totaux = session()->has('cache.champs_libres_pour_sous_totaux.'.$type_element);
	$champs_libres_pour_sous_totaux_nombres = session()->has('cache.champs_libres_pour_sous_totaux_nombres.'.$type_element);
}
else {
	
	$champs_libres_pour_sous_totaux = table_libre($type_element)->champs_libres()->get()->toArray();
	$champs_libres_pour_sous_totaux_nombres = table_libre($type_element)->champs_libres()->whereIn('type', array(2,3))->get()->toArray();
	
	session()->put('cache.champs_libres_pour_sous_totaux.'.$type_element, $champs_libres_pour_sous_totaux);
	session()->put('cache.champs_libres_pour_sous_totaux_nombres.'.$type_element, $champs_libres_pour_sous_totaux_nombres);
}



?>

@for($i=1; $i<=3; $i++)
	<div class="row">
		<div class="col-md-2">
			@traduction('interface.sous_totaux_listes.sous_total_niveau') {{ $i }} :
		</div>
		<div class="col-md-10">
			@traduction('interface.sous_totaux_listes.faire_sous_total') :
			<select name="sous_total_niveau_{{ $i }}" class="js_sous_totaux_sur_liste" v-model="liste.sous_total_sur_liste_{{$i}}">
				<option value=""></option>
				@foreach($champs_libres_pour_sous_totaux as $champ_libre)
					<option value="{{ $champ_libre->nom_sql }}">{{ $champ_libre->nom }}</option>
				@endforeach	
			</select>
			@traduction('interface.sous_totaux_listes.calcule_avec') :
			<select name="sous_total_niveau_{{ $i }}_champ" class="js_sous_totaux_sur_liste" v-model="liste.sous_total_sur_liste_{{$i}}_champ">
				<option value=""></option>
				@foreach($champs_libres_pour_sous_totaux_nombres as $champ_libre)
					<option value="{{ $champ_libre->nom_sql }}">{{ $champ_libre->nom }}</option>
				@endforeach	
			</select>
		</div>
	</div>
@endfor