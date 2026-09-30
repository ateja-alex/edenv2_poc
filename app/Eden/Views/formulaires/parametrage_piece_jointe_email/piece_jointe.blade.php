@extends('eden::formulaires.parametrage_lien_champ.lien_champ',[
    'type_element' => 'parametrage_piece_jointe_email',
])

@section('champ_valeur_dur')
    @champ('parametrage_piece_jointe_email','piece_jointe',2,4)
@endsection