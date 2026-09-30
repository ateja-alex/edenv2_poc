@extends('eden::authentification.template')

@section('title')
Accés restreint - ERP
@endsection

@section('formulaire')

    <form class="form-horizontal" method="POST" action="{{ URL::to('/extranet/login') }}">
        {{ csrf_field() }}

        <p class="text-center"> {{ traduction('interface.extranet.acces_restreint.titre') }}</p> <br>
    </form>

    <div class="form-group">
        <div style="text-align:center;">

            @if(isset($_GET['url']))
                <a href="{{ URL::to($_GET['url']) }}">
                    <button class="bouton_acces_restreint">
                        {{ traduction('interface.extranet.acces_restreint.retour_en_arriere') }}
                    </button>
                </a>
            @else
                <a href="https://{{ fonctionnalite('url_extranet') }}{{ maquette('page_accueil') }}">
                    <button class="bouton_acces_restreint">
                        {{ traduction('interface.extranet.acces_restreint.retour_a_accueil') }}
                    </button>
                </a>
            @endif
            <a href="{{ route('deconnexion') }}">
                <button class="bouton_acces_restreint">
                    {{ traduction('interface.extranet.acces_restreint.deconnexion') }}
                </button>
            </a>
        </div>
    </div>

@endsection
                        