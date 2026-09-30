@if(fonctionnalite('email_utiliser_identifiants_comptes_emails') === true)
    <div class="row">
        @champ('compte_email', 'mot_de_passe_smtp', 4,8)
    </div>
@endif