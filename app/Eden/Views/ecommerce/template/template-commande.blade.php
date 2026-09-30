<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <title>AMC - PRODUCTION</title>
    <meta name="description" content="AMC - PRODUCTION" />
    <link rel="icon" href="{{asset('ecommerce-amc/images/favicons.png')}}?v=1.02">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- Opengraph --}}
    <meta property="og:title" content="AMC - PRODUCTION" />
    <meta property="og:type" content="website" />
    {{-- <meta property="og:url" content="{{ route(Route::current()->getName()) }}" /> --}}
    <meta property="og:image" content="{{ asset('ecommerce-amc/images/opengraph.jpg') }}" />
    <meta property="og:description" content="AMC PRODUCTION" />
	
	<!-- Google Tag Manager -->
	<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
	new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
	j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
	'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
	})(window,document,'script','dataLayer','GTM-NJKNSSS');</script>
	
    {{-- Css --}}
    <link rel="stylesheet" href="{{asset('ecommerce-amc/css/bootstrap.min.css')}}">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.2.0/css/all.css" integrity="sha384-hWVjflwFxL6sNzntih27bfxkr27PmbbK/iSvJ+a4+0owXq79v+lsFkW54bOGbiDQ" crossorigin="anonymous">
    <link rel="stylesheet" href="{{asset('ecommerce-amc/css/animate.css')}}">
    <link rel="stylesheet" href="{{asset('ecommerce-amc/css/basictable.css')}}">
    {{-- CDN Css --}}
    <link rel="stylesheet" type="text/css" href="{{asset('ecommerce-amc/css/slick.css')}}"/>
    <!-- Add the slick-theme.css if you want default styling -->
    {{-- <link rel="stylesheet" type="text/css" href="{{asset('ecommerce-amc/css/slick-theme.css')}}"/> --}}
    {{-- Css Perso --}}
    <link rel="stylesheet" href="{{asset('ecommerce-amc/css/styles.css')}}?v={{ time() }}">
    <link rel="stylesheet" href="{{asset('ecommerce-amc/css/responsive.css')}}?v={{ time() }}">
    {{-- Fonts --}}
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i,800,800i|Oswald:300,400,500,600,700" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto+Condensed:300,300i,400,400i,700,700i" rel="stylesheet">
	
	<style>
	/* Countdown */

	#block_compteur {
		
		top: 13px;
		position: relative;
	}

	#countdown_dashboard {
	  width: 100%;
	  text-align: center;
	}

	#countdown_dashboard .countdown-txt {
	  display: block;
	  font-size: 11px;
	  float: none;
	  position: relative;
	  margin-top: 20px;
	}

	ul#countdown {
	  display: inline-block;
	  clear: both;
	  margin: 10px auto;
	  padding: 0;
	}

	ul#countdown li {
	  background-color: #333;
	  border: 2px solid #fff;
	  color: #fff;
	  display: inline;
	  float: left;
	  font: normal 700 23px Arial;
	  line-height: 30px;
	  letter-spacing: 3px;
	  margin-left: 0;
	  text-align: center;
	  margin-left: 5px;
	  border-radius: 4px;
	  box-shadow: 0 0 3px #777;
	}

	ul#countdown span {
	  padding-left: 5px;
	}

	ul#countdown p.timeRef {
	  font: normal normal 10px Arial;
	  letter-spacing: 0;
	  margin: 0;
	  padding-bottom: 2px;
	  line-height: 12px;
	}



	/* Countdown */

	@media (min-width:768px) {
		#countdown_dashboard {
		  display: block;
		  position: relative;
		  float: right;
		}

		#countdown_dashboard .countdown-txt {
		  display: inline;
		  float: left;
		  font-size: 11px;
		  text-align: left;
		  margin-top: 0px;
		}
		ul#countdown {
		  margin: 10px auto;
		  padding: 0;
		}

		ul#countdown li {
		  font-size: 23px;
		}
	}

	@media (min-width: 1024px) {
		
		#countdown_dashboard {
		  position: relative;
		}
		#countdown_dashboard .countdown-txt {
		  float: left;
		  position: relative;
		  font-size: 12px;
		  margin-top: 20px;
		}
		ul#countdown li {
		  font-size:28px;
		}
	}
	</style>
</head>
<body id='bs-or'>
	
	<!-- Google Tag Manager (noscript) -->
	<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NJKNSSS"
	height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
	
	<script type="text/javascript">
		window._mfq = window._mfq || [];
		(function() {
			var mf = document.createElement("script");
			mf.type = "text/javascript"; mf.async = true;
			mf.src = "//cdn.mouseflow.com/projects/aaa8710a-70ec-4ea1-b8ee-574f34ae7ba5.js";
			document.getElementsByTagName("head")[0].appendChild(mf);
		})();
	</script>


    @include('eden::ecommerce.template.header-commande')
    
    @yield('content')

    @include('eden::ecommerce.template.footer-commande')

    {{-- Addons --}}
    @include('eden::ecommerce.addons.menu-mobile')

    {{-- Modals --}}
    @include('eden::ecommerce.modals.selection-devis')
    @include('eden::ecommerce.modals.devis_par_mail')
    {{-- Javascript --}}
    <script type="text/javascript" src="{{asset('ecommerce-amc/js/jquery-3.2.1.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('ecommerce-amc/js/bootstrap.min.js')}}"></script>

    <script type="text/javascript" src="{{asset('ecommerce-amc/js/jquery.countdown.min.js')}}"></script>
    
    <script type="text/javascript" src="{{asset('ecommerce-amc/js/jquery.sticky.js')}}"></script>
    <script type="text/javascript" src="{{asset('ecommerce-amc/js/jquery-scrolltofixed-min.js')}}"></script>
    <script type="text/javascript" src="{{asset('ecommerce-amc/js/slick.min.js')}}"></script>
    <script src="https://cdn.jsdelivr.net/npm/vue@2.5.16/dist/vue.js"></script>
    <script type="text/javascript" src="{{asset('ecommerce-amc/js/jquery.basictable.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('ecommerce-amc/js/vue-color.js')}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script type="text/javascript" src="{{asset('ecommerce-amc/js/script.js')}}?v=2.1"></script>
	
	<script>
	
	// Countdown
	@if(formate_date('Y-m-d',parametre('date_fin_promotion_generale_amc')) > date('Y-m-d'))
		$("#countdown").countdown("{{formate_date('Y-m-d',parametre('date_fin_promotion_generale_amc'))}}", function(event) {
			
			$('.days').text(
				event.strftime('%D')
			);
			$('.hours').text(
				event.strftime('%H')
			);
			$('.minutes').text(
				event.strftime('%M')
			);
			$('.seconds').text(
				event.strftime('%S')
			);
		});
	@endif
	
	</script>
	
	<script type="text/javascript">window.$crisp=[];window.CRISP_WEBSITE_ID="ad046825-e31e-421d-adde-c480df047cbe";(function(){d=document;s=d.createElement("script");s.src="https://client.crisp.chat/l.js";s.async=1;d.getElementsByTagName("head")[0].appendChild(s);})();</script>



    <!-- development version, includes helpful console warnings -->
    {{-- <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script> --}}

    @yield('javascript')

</body>
</html>
