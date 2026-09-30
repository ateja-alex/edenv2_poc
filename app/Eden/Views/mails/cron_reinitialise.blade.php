@extends('eden::mails.template_v2')

@section('titre')
    Un cron a été réinitialisé.
@endsection

@section('explication')
    Le cron "{{ $cron }}" a été réinitialisé sur le projet {{ $projet }} car il a dépassé sa durée limite d'exécution.
    Veuillez vérifier s'il n'y a pas eu de problème avec le serveur ou s'il n'y a pas eu d'erreur lors de l'exécution de celui-ci.
@endsection

