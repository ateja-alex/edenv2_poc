<div class="header_intranet">
    <div class="header_haut">
        <div class="header_nom_photo">
            @if(!empty(moi()->avatar))
                <img class="header_photo" src="{{ !empty(moi()->avatar) ? asset('storage/'.moi()->avatar) : asset('images/no_avatar.jpg') }}">
            @endif
            <span class="header_message_nom">
                <span class="header_bonjour">@traduction('interface.intranet.bonjour')</span>
                <span class="header_nom">{{moi()->prenom.' '.moi()->nom}}</span>
            </span>
        </div>
        <div class="header_parametres">
            @if(session()->has('eden_usurpation_origine'))
                <a href="{{ route('parametrage.usurpation.retour') }}" style="margin-left: 5px;">[retour à ma session]</a>
            @endif
            @if(editeur())
                <div style="position:relative" class="css_ajouter_element mr-3" data-toggle="tooltip" data-placement="left" :title="traduction('interface.tooltip.eden_parametrage')" onClick="$('#toggle_parametrage_eden').toggle()">
                    <i id="bouton_toggle_parametrage_eden" class="css_btn_action_header fas fa-cog"></i>
                    <div id="toggle_parametrage_eden" class="css_panel_toggle_parametre_eden" style="right: 0;padding: 12px;top: 30px;">
                        <div class="d-flex align-items-center">
                            <span>@traduction('interface.eden_parametrage.mode_traduction') :</span>
                            <label style="margin-left: auto;">
                                <input class="toggle-checkbox css_toggle_traduction" type="checkbox" v-model="mode_traduction"></input>
                                <div class='toggle-slot css_toggle_traduction'>
                                    <div class='sun-icon-wrapper'>
                                        <div class="fa fa-language" style="color: black;"></div>
                                    </div>
                                    <div class='toggle-button'></div>
                                    <div class='moon-icon-wrapper'>
                                        <div class="fa fa-language" style="color: black;"></div>
                                    </div>
                                </div>
                            </label>
                        </div>
                        {{-- Toggle Récupérer les droits du compte initial --}}
                        @if(session()->has('eden_usurpation_origine'))
                            <div class="d-flex align-items-center mt-3">
                                <span>@traduction('interface.eden_parametrage.recup_droits_compte_initial') :</span>
                                <label style="margin-left: auto;">
                                    <input class="toggle-checkbox css_toggle_traduction js_toggle_recuperer_droits_compte_initial" type='checkbox' @if(session()->has('activer_recuperer_droits_compte_initial'))checked="checked"@endif></input>
                                    <div class='toggle-slot css_toggle_traduction'>
                                        <div class='toggle-button'></div>
                                    </div>
                                </label>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
            @if(moi()->autorisation_intranet !== 2)
                <a href="{{route('login')}}" class="css_ajouter_element mr-3" data-toggle="tooltip" data-placement="left" :title="traduction('intranet.tooltip.retour_eden')">
                    <i id="bouton_toggle_version_eden" class="css_btn_action_header icon-eden"></i>
                </a>
            @endif

        </div>
    </div>
    <div class="header_retour">
        <div class="back" @click="bloc_affiche = ''" v-show="bloc_affiche != ''">
            <span class="glyphicon glyphicon-home"></span>
            @traduction('interface.intranet.accueil')
        </div>
    </div>
</div>

@push('donnees_pour_vuejs_mounted')
    $('.js_toggle_recuperer_droits_compte_initial').change(function (){
        $.ajax( {
            url: "{{URL::to('eden/maintenance/recuperer_droits_compte_initial')}}/"+($(this).prop('checked') ? 1 : 0)
        }).done(function() {
            loading(true);
            location.reload();
        });
    });
@endpush
