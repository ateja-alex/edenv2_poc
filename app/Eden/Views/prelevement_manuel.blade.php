@extends('eden::templates.template')

@section('content')
	
<div class="content-wrapper" >
		<div id="base-content" class="container-fluid">

			<div class="row">
				<div class="col-md-12">
                    @if(session::has('erreur_prelevement_mannuel'))
                        <div class="alert alert-danger">
                            {{ session::get('erreur_prelevement_mannuel') }}
                        </div>

                    @endif
					<div class="card mb-3">
						<div class="card-header">
							<h4>
                                @traduction('interface.prelevement_manuel.titre')
							</h4>
						</div>
						<div class="card-body css_form css_parametrage_formulaire" id="sortable">
                            <form action="{{ route('prelevement_manuel.enregistrer') }}" method="post">
                            
                                <div class="row">
                                    <div class="col-md-12" >
                                        <strong>@traduction('interface.prelevement_manuel.titre_formulaire')</strong>
                                    </div>
                                    <div class="col-md-12" >
                                        {!! management('client', $client_id)->affiche() !!}
                                        <input type="hidden" name="client_id" value="{{ $client_id }}">
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <strong>@traduction('champs_libres.paiement.montant.nom')</strong>
                                            <input type="montant" class="form-control" name="montant" id="" placeholder="">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-success">@traduction('interface.modale.debiter')</button>
                                    </div>
                                </div>
                            </form>

						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

@endsection

@push('donnees_pour_vuejs_data')
    document : {},
@endpush