<!-- Modal -->
<div class="modal fade" id="modal_tablier_sens_verrou" tabindex="-1" role="dialog" aria-labelledby="modal_selection_devisLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content css_modal_amc css_modal_info_amc">
      <div class="modal-header hidden-block">
        <h4 class="modal-title" id="modal_selection_devisLabel"></h4>
      </div>
      <div class="modal-body">
        <button type="button" class="close css_close_modal_amc" data-dismiss="modal" aria-label="Close"><i class="fas fa-times"></i></button>
        <div class="container-fluid">
          <div class="row">
            <div class="col-md-12">
              <h2 class='css_titre_modal_choix_devis oswald'>
                Sens du verrouillage
              </h2>
              <p>
                Merci de nous indiquer le sens d'enroulement de votre tablier de volet roulant.
              </p>
              <table class="table table-bordered text-center">
                <tbody>
                  <tr>
                    <td>Sens d'enroulement vers l'INTERIEUR</td>
                    <td>Sens d'enroulement vers l'EXTERIEUR</td>
                  </tr>
                  <tr>
                    <td>
                      <img src="{{ asset('ecommerce-amc/images/devis/tablier/sens-enroulenement-interieur.png') }}" alt="">
                      <p>
                        Côté creux lames intérieur
                      </p>
                    </td>
                    <td>
                      <img src="{{ asset('ecommerce-amc/images/devis/tablier/sens-enroulenement-exterieur.png') }}" alt="">
                      <p>Coté creux lames extérieur</p>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer hidden-block">
      </div>
    </div>
  </div>
</div>
