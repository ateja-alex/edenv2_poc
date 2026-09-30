@extends('eden::mails.template_v2')

@section('explication')

Bonjour, {{ $prenom }} {{ $nom }}
<br/>
<br/>
Voici votre nouveau mot de passe pour vous connecter sur le site : {{ $nouveau_mdp }}
<br>
<br>
A bientôt !

@endsection


