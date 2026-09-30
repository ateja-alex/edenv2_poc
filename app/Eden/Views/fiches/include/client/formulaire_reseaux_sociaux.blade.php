@if(App\Eden\Models\Formulaire::where('nom_formulaire', 'formulaire_reseaux_sociaux_'.$type_element)->first() !== null)
	@include('eden::fiches.include.formulaire_libre_sur_fiche', ['module' => 'formulaire_reseaux_sociaux_'.$type_element])
@endif