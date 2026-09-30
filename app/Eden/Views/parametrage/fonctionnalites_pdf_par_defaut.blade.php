@extends('eden::templates.template')

@section('title') Configuration fonctionnalités maquette @stop

@section('content')

	<div class="content-wrapper" >
		<div id="base-content" class="container-fluid">
			<div class="row">
				<div class="col-md-12">
					<div class="card mb-3">
						<div class="card-header">
							<h4>
								Gestion des pdfs par défaut
								<span class="css_ajouter_element css__lien" @click="enregistrer_enregistre_pdf_par_defaut"><i class="fa fa-fw fa-plus-square"></i> Enregistrer</span>

							</h4>
						</div>
						<div class="card-body">
							<div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th scope="col">Type element</th>
                                            <th scope="col">PDF par défaut</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template v-for="(pdfs, type_element, index) in pdfs_types_elements">
                                            <tr> 
                                                <td> @{{type_element}}</td>
                                                <td>
                                                    <select v-model="pdfs.pdf_par_defaut"  name="" id="">
                                                        <option value="">Sans valeur</option>
                                                        <option :value="id_pdf" v-for="(pdf, id_pdf, index) in pdfs.pdf">
                                                            @{{ pdf }}
                                                        </option>
                                                    </select>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
								</table>
                            </div>
                        </div>
					</div>
				</div>
			</div>
		</div>
	</div>

@endsection

@push('donnees_pour_vuejs_data')

    'pdfs_types_elements' : {!! collect($pdfs_types_elements) !!},
@endpush

<script>
@push('donnees_pour_vuejs_methods')

    enregistrer_enregistre_pdf_par_defaut: function() {
        
        loading(true);
        
        var pdfs_types_elements = this.pdfs_types_elements;
        
        $.post({

            url: '{{ route('parametrage.pdf_par_defaut.enregistrer') }}',
            data: {pdfs_types_elements},
            success: function(data) {

                loading(false);

            }
        });
    },

@endpush
</script>
