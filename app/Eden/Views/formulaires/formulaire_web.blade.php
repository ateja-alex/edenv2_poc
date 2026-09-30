<html>
<head>
    <link rel="stylesheet" href="{{ url('eden/vendors/bootstrap/css/bootstrap.min.css?v=2'.date('Ymd')) }}" type="text/css" media="all" />
    <style>
        .indicateur_champ_obligatoire {
            display: flex;
            width: 15px;
            height: 15px;
            color: white;
            position: absolute;
            background: #d12222;
            font-size: calc(var(--taille_police)* 19px);
            left: -15px;
            justify-content: center;
        }

        .bloc_champ_formulaire{

            display: flex;
            position:relative;

            input,select,textarea{
                width:100%;
            }

            .champ_obligatoire{
                background : red;
                color: white;
            }
        }

        #message_erreur{
            display: none;
            width: 100%;
            border: 1px solid lightgrey;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            text-align: center;
            background: red;
            color: white;
        }

        #message_succes{
            display: none;
            width: 100%;
            border: 1px solid lightgrey;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            text-align: center;
            background: green;
            color: white;
        }

        #loader_enregistrement{
            display:none;
            height: 30px;
        }

        .bouton_validation{
            display: flex;
            flex-direction: row;
            gap: 5px;
            padding: 9px;
            border-top: 1px solid lightgrey;
            align-items: stretch;
            justify-content: flex-end;
        }

        #base-content{
            padding: 20px;
        }

        form{
            padding: 5px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .champs{
            display: flex;
            flex-wrap: wrap;
            gap: 5px 0px;
        }

        @if(!empty($formulaire->css_personnalise))
            {!! $formulaire->css_personnalise !!}
        @endif
    </style>

    <script src="https://www.google.com/recaptcha/api.js"></script>
    <script type="text/javascript" src="{{ url('eden/vendors/jquery/jquery.min.js?v='.date('YmdH'))}}"></script>
</head>

<body>

<div class="content-wrapper" >
    <div id="base-content" class="container-fluid">
        <form id="formulaire" method="POST">
            <div id="message_erreur"></div>
            <div id="message_succes"></div>
            <div class="champs">
                @foreach($champs as $champ)
                    @if(!empty($champ['taille_avant']))
                        <div class="col-sm-{!! $champ['taille_avant'] !!}"></div>
                    @endif

                    @if(!empty($champ['taille_libelle']))
                        <div class="col-sm-{!! $champ['taille_libelle'] !!} titre_champ">
                            {!! $champ->management->nom_web() !!}
                        </div>
                    @endif

                    <div class="col-sm-{!! $champ['taille_champ'] !!} contenu_champ">
                        {!! $champ->management->cree_web($valeurs_par_defaut[$champ['nom_sql']] ?? false) !!}
                    </div>

                    @if(!empty($champ['taille_apres']))
                        <div class="col-sm-{!! $champ['taille_apres'] !!}"></div>
                    @endif
                @endforeach
            </div>
            <div class="bouton_validation">
                <img id="loader_enregistrement" src="{{asset('eden/images/ajax_loader.gif')}}">
                <button class="g-recaptcha"
                        data-sitekey="{{fonctionnalite('recaptcha_cle_public')}}"
                        data-callback='onSubmit'
                        data-action='submit'>{{ traduction('formulaire.formulaire_web.bouton_envoyer') }}</button>
            </div>
        </form>
    </div>
</div>

</body>

<script>

    function onSubmit() {

        var div_erreur = document.getElementById('message_erreur');
        var div_succes = document.getElementById('message_succes');

        div_erreur.innerText = '';
        div_erreur.style.display = 'none';

        $('.input_obligatoire').removeClass('input_obligatoire');
        $('#loader_enregistrement').show();
        var formData = new FormData($('#formulaire')[0]);
        formData.append("/url_source", document.referrer);

        $.post({
            url:'/valid_form/{{$formulaire->formulaire_web_id}}',
            data:formData,
            contentType: false,
            processData: false,
            dataType:'json'
        }).done((retour) => {

            $('#loader_enregistrement').hide();

            if(retour.erreur === true){

                var message = retour.message;

                div_erreur.innerText = message;
                div_erreur.style.display = 'block';

                if(retour.champs_libres){
                    for(champ_libre of retour.champs_libres){

                        var champ = $('.formulaire_champ_'+champ_libre.nom_sql).children()[0];
                        champ.classList.add("champ_obligatoire");

                        champ.addEventListener('focus',ajout_evenement_obligatoire);
                    }
                }

                return;
            }

            var message = retour.message;

            div_succes.innerText = message;
            div_succes.style.display = 'block';

            setTimeout(() => {
                div_succes.style.display = 'none';
            },2000);

            $('input').val(null);
            $('select').val(null);
            $('textarea').val(null);

            window.parent.postMessage('form_submitted', '*');
        });
    }

    function ajout_evenement_obligatoire(event){
        $(event.target).removeClass('champ_obligatoire');
        event.target.removeEventListener('onfocus',ajout_evenement_obligatoire);
    }
</script>
</html>