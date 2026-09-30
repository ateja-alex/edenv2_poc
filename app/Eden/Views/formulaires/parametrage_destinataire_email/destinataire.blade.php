@extends('eden::formulaires.parametrage_lien_champ.lien_champ',[
    'type_element' => 'parametrage_destinataire_email',
])

@section('champ_valeur_dur')
    @champ('parametrage_destinataire_email','adresse_mail',2,4)
@endsection