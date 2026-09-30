@extends('eden::ecommerce.template.template')
@section('content')

  <div class="container confirmation_body">
    <div class="row">
		<div class="col-md-2"></div>
		<div class="col-md-8" style="text-align: center;">
			<br/><br/>
			<img src="{{asset('images/no_ok.png')}}" /><br/><br/>

			<h1 class='confirmation_message text-center' style="color: #da291c">OUPS, le paiement ne fonctionne pas...</h1>
			
			<p>
			Visiblement, votre paiement n’a pas été accepté par notre banque.<br/>
			Votre commande n’est donc pas finalisée.<br/><br/>

			Il est possible que votre carte bleue ait un plafond.  Dans tel cas, nous vous invitons à contacter votre banque.<br/>
			Sachez également que vous pouvez régler par chèque votre commande en envoyant votre devis imprimé avec votre règlement à l’adresse :<br/><br/>

			AMC PRODUCTION<br/>
			79/81 rue du pré-Catelan<br/>
			59 110 La Madeleine<br/><br/>

			N’hésitez pas à contacter notre assistance téléphonique au<br/>
			03 66 72 91 88<br/><br/>

			</p>
		</div>
      
    </div>
	<br/>
	<br/>
	<br/>
	<br/>
  </div>
@endsection