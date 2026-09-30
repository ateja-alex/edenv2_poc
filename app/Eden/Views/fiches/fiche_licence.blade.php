@extends('eden::fiches.fiche_generique')

@section('fil_ariane')

    >

    <a href="{{ route('parametrage.index') }}" style="color: #212121;" onmouseover="this.style.textDecoration='underline';" onmouseout="this.style.textDecoration='none';">
        Paramétrage
    </a>

    >

    <a href="{{ route('parametrage.licence.index') }}" style="color: #212121;" onmouseover="this.style.textDecoration='underline';" onmouseout="this.style.textDecoration='none';">
        @traduction(table_libre('licence')->index_traduction,"nom_table")
    </a>

    >

    <span style="color: #a3a3a3;">{!! traduction(table_libre('licence')->index_traduction.'.element').' - ' . str_replace(array('<br/>', '<br>'), ', ', management($type_element, $id_element)->affiche()) !!}</span>
@endsection