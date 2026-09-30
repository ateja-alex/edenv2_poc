@extends('eden::templates.template')

@section('title') Email @stop

@php $ids_listes = App\Eden\Models\Liste_libre::whereIn('type_element', [
    'modele_email', 
    'parametrage_destinataire_email', 
    'parametrage_piece_jointe_email',
    'parametrage_balise_publipostage'
    ])->where(function($requete){
        $requete->whereNull('id_rapport')->orWhere('id_rapport','');
    })->pluck('id','type_element'); @endphp
@section('content')

<div class="content-wrapper" >
    <div id="base-content" class="container-fluid">
        @include('eden::includes.fil_ariane', ['fil_ariane' => array(
                array('nom' => 'Email')
            )])
        <div class="row">
            <div class="col-md-12">
                <div class="card mb-3">
                    <div class="card-header d-flex align-items-center">
                        <h5>Paramétrage des emails</h5>
                    </div>
                    <div class="card-body">
                        <parametrage-email
                            :ids_listes="{ 
                                modele_email : {{ $ids_listes['modele_email'] }},
                                parametrage_destinataire_email : {{ $ids_listes['parametrage_destinataire_email'] }},
                                parametrage_piece_jointe_email : {{ $ids_listes['parametrage_piece_jointe_email'] }},
                                parametrage_balise_publipostage : {{ $ids_listes['parametrage_balise_publipostage'] }}
                            }"
                        ></parametrage-email>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('composants_vue')
    @foreach($ids_listes as $id_liste)
        <script type="text/javascript" src="{{ asset('storage/composants/liste_libre_'.$id_liste.'.js') }}"></script>
    @endforeach
@endpush