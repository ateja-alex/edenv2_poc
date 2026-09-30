<!-- Modal -->
<div class="modal fade" id="login_modal" tabindex="-1" role="dialog" aria-labelledby="login_modalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-vertical-center">
    <div class="modal-content">
      <div class="modal-header hidden">
        <h4 class="modal-title" id="login_modalLabel"></h4>
      </div>
      <div class="modal-body" style="padding:0;">
        <button type="button" class="btn btn-rouge-amc css_btn_close_modal_absolute" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		<input type="hidden" name="redirection_apres_connexion" value="ecommerce.panier" />
		@include('eden::ecommerce.addons.login')
	  </div>
      <div class="modal-footer hidden">
      </div>
    </div>
  </div>
</div>