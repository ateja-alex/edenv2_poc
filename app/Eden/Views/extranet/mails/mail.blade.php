@extends('eden::mails.template_v2')
@section('contenu')

    @if(!empty($contenu_mail))

        @section('explication')
            {!! $contenu_mail !!}
        @endsection
    @else

        @section('explication')
            <p>Suite à votre demande de mot de passe oublié , voici le lien vous permettant de modifier votre mot de passe :</p>
        @endsection

        @section('bouton')
            <a href="{{ $url_mdp_oublie }}" style="color: white; text-decoration: none; display: block;">Modifier mon mot de passe</a>
        @endsection
    @endif

@endsection