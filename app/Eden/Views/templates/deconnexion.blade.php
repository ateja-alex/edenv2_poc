@if(!empty(moi()))
    <a href="{{ route('deconnexion') }}" :title="traduction('interface.tooltip.eden_deconnexion')" data-toggle="tooltip" data-placement="left" class="">
        <i class="fas fa-sign-out-alt  css_btn_deconnexion_eden" aria-hidden="true" style="color:white !important;"></i>
    </a>
@else
    <a href="{{ route('extranet.deconnexion') }}" :title="traduction('interface.tooltip.eden_deconnexion')" data-toggle="tooltip" data-placement="left" class="">
        <i class="fas fa-sign-out-alt css_btn_deconnexion_eden" aria-hidden="true" style="color:white !important;"></i>
    </a>
@endif