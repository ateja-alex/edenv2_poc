@extends('eden::ecommerce.template.template-commande')
@section('content')

<section class="css_section_commande_valide">
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <h1 class='css_titre_h1_page_presentation'>
                    <span>Votre commande a été</span> validée
                </h1>
                <p>
                    <b>Félicitations, votre commande a été validé !</b><br/><br/>
                    Vous allez recevoir un récapitulatif de votre commande par mail.
                    L'équipe de AMC-production vous remercie pour votre confiance
                    <br/><br/>
                </p>
                <h2>Votre devis</h2>
                <p>
                    Vous pouvez visualiser votre devis ci-dessous, l’imprimer ou le télécharger.

                    A toute fin utile, vous pouvez le télécharger directement en cliquant sur le lien : <a href="{{ route('bibliotheque_url_publique', ['devis_vente', 'pdf', session()->get('id_devis'), md5('eden'.session()->get('id_devis'))]) }}">télécharger mon devis</a>.
                    <br>      
                </p>
                <p class="text-center">
                    <a href="{{ route('ecommerce.accueil') }}" class="btn btn-rouge-amc css_btn_revenir_accueil_commande">Revenir à l'accueil</a>
                </p>
            </div>
        </div>
    </section>

    @endsection
