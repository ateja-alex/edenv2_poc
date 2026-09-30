@extends('eden::authentification.template')

@section('title')
Login - ERP
@endsection

@section('formulaire')

    <form class="form-horizontal" method="POST" action="{{ URL::to('/eden/login') }}">
        {{ csrf_field() }}

        <p class="text-center" v-html="traduction('interface.acces_restreint.phrase')"></p> <br>
    </form>
    
    <div class="form-group">
        <div class="col-md-8 col-md-offset-4">

            <a class="btn btn-primary btn-lrdl" href="{{ route('deconnexion') }}" v-html="traduction('interface.acces_restreint.bouton_deconnexion')">
            </a>
        </div>
    </div>

@endsection
                        