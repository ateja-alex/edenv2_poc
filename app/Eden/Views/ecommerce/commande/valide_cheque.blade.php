@extends('eden::ecommerce.template.template-commande')
@section('content')

<section class="css_section_commande_valide">
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <h1 class='css_titre_h1_page_presentation'>
                    <span>Verifiez votre</span> boite mail
                </h1>
                <p>
                    Vous venez de recevoir un email contenant votre devis à nous retourner signé et accompagné du chèque pour passer votre commande en fabrication.<br/><br/>

                    Trois semaines après la validation de votre commande (réception du chèque), votre commande est expédiée. Vous recevez alors un email et notre transporteur prend ensuite contact avec vous pour définir la demi-journée de livraison qui vous convient.<br/><br/>
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
