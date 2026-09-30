<head>
    <meta content="width=device-width, initial-scale=1" name="viewport" />
    <link rel="stylesheet" href="{{ url('eden/css/eden.css?v=5'.date('Ymd')) }}" type="text/css" media="all" />
    <style>
        body {
            margin: 0;
        }
        @media (max-width: 1023px) {
            html {
                overflow: hidden;
                width: 100vw;
                height: 100vh;
            }
            body {
                margin: 0;
                overflow: hidden;
            }
        }
    </style>
</head>
<body>
    <div id="erreur_http_conteneur">
        <div class="erreur_http_background">
            <img src="{{ asset('eden/images/wave.svg') }}"/>
        </div>
        <div class="erreur_http_title">
            <h1>{{ $erreur }}</h1>
            <h3>{{ $titre_erreur }}</h3>
        </div>
        <div class="erreur_http_subtitle">
            <h3>
                @if($erreur === 404)
                    {{ traduction('interface.erreur_http.erreur_404') }}
                @else
                    {{ traduction('interface.erreur_http.erreur_autre') }}
                @endif
            </h3>
            <h3>
                {{ traduction('interface.erreur_http.contact_admin') }}
            </h3>
            @if(session()->has('eden_usurpation_origine'))
                <span class="erreur_http_subtitle_link" onclick="recuperer_droit_compte_initial()">
                    <span>{{ traduction('interface.eden_parametrage.recup_droits_compte_initial') }}</span>
                </span>
            @endif
            <a class="erreur_http_subtitle_link" href="{{ url()->previous() }}">
                <span>{{ traduction('interface.modales.retour') }}</span>
            </a>
        </div>
        <div class="erreur_http_logo">
            <img src="{{ asset('eden/images/logo-eden.png') }}"/>
        </div>
    </div>
</body>

<script type="text/javascript" src="{{ url('eden/vendors/jquery/jquery.min.js?v=0.1')}}"></script>
@if(session()->has('eden_usurpation_origine') && !session()->has('activer_recuperer_droits_compte_initial'))
    <script>

        function recuperer_droit_compte_initial(){
            $.ajax( {
                url: "{{route('maintenance.recuperer_droits_compte_initial',1)}}",
            }).done(function() {
                location.reload();
            });
        }
    </script>
@endif