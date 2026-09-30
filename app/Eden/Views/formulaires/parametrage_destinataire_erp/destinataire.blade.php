@extends('eden::formulaires.parametrage_lien_champ.lien_champ',[
    'type_element' => 'parametrage_destinataire_erp',
])

@section('champ_valeur_dur')
    @champ('parametrage_destinataire_erp','utilisateur_id',2,4)
@endsection