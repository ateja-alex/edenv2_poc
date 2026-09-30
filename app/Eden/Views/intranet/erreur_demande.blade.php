<!DOCTYPE html>

<!--[if lt IE 7 ]><html class="ie ie6" lang="en"> <![endif]-->
<!--[if IE 7 ]><html class="ie ie7" lang="en"> <![endif]-->
<!--[if IE 8 ]><html class="ie ie8" lang="en"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!-->

<html class="not-ie" lang="fr">
    <!--<![endif]-->

    <head>
        <meta charset="utf-8">
        <meta name="description" content="description" />
        <meta name="keywords" content="keywords"/>
        <meta name="author" content="BRKOR" />
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>
        <title>{{ traduction('interface.intranet.titre_th_demande_consultation_conges') }}</title>

        <!-- google web font-->
        <link href='http://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,400,300,600,700' rel='stylesheet' type='text/css'>

        <!-- style sheets-->
        <link rel="stylesheet" media="screen" href="{{ asset('eden/intranet/css/bootstrap.min.css?v=0.1') }}" type="text/css"/>
        
        <link rel="stylesheet" media="screen" href="{{ asset('eden/intranet/css/custom.css') }}" type="text/css"/>
        <link rel="stylesheet" media="screen" href="{{ asset('eden/intranet/css/jquery.mCustomScrollbar.css') }}" type="text/css" />
        <link rel="stylesheet" media="screen" href="{{ asset('eden/intranet/css/style_intranet.css') }}" type="text/css"/>

    </head>
    <body>

       <!-- Header -->
        <header>
            <div class="contenu_intranet">
                <div class="row">
                    <div class="col-xs-6"><br>
                        <span class="glyphicon glyphicon-user" style="font-size: 80px; padding-top: 10px; display: inline-block; float: left; margin-right: 15px;"></span>
                        <p class="header-uptitle" style="margin-top: 25px;">{{ traduction('interface.intranet.bonjour') }}</p>
                        <p class="header-name">{{ session('employe_eden')['nom'].' '.session('employe_eden')['prenom']}}</p>
                    </div>
                </div>
            </div>
        </header>
        <!-- end Header -->

	<div class="errorDate">
        <p>{{ $errors->first() }}<br>{{ traduction('interface.intranet.redirection') }}</p>
		<meta http-equiv='refresh' content="5;{{route('conges_eden')}}"/> 
	</div>
    </body>
</html>
