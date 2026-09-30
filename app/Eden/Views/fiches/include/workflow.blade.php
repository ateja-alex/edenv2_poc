<?php
use App\Eden\Models\Champ_libre;

$type_element = $management_element->_type_element;
$nom_sql = substr($module,9);
$champ_libre = Champ_libre::where('type_element',$type_element)->where('nom_sql',$nom_sql)->first();

?>

<workflow :champ_libre="{{$module}}_champ" :modele_element="{{$type_element}}"></workflow>

@push('donnees_pour_vuejs_data')

    {{$module}}_champ : {!! collect($champ_libre) !!},
@endpush