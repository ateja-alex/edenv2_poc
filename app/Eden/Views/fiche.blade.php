@extends('eden::templates.template')

@section('title')Fiche {{  table_libre($type_element)->element }} @if(!empty($management_element->affiche())) {{ $management_element->affiche() }} @endif @stop

@push('donnees_pour_vuejs_data')
	contexte: 'fiche_{{ $type_element }}',
	type_element: '{{ $type_element }}',
	element_id: '{{ $management_element->modele->id }}',
@endpush

@push('donnees_pour_vuejs_methods')

	suppression_fiche: async function(type_element,id_element){

		if(!await confirm_eden())
			return false;

		loading(true);

		var retour_post_suppression = true;

		await $.ajax({
			url: "{{URL::to('/eden/element')}}/"+type_element+"/"+id_element+"/supprimer",
		}).done(async (donnees) => {

			loading(false);

			if(donnees.retour !== true) {

				await erreur(donnees.retour);

				retour_post_suppression = false;

				return;
			}

			await this.$root.$emit('suppression_fiche');

			@if(empty($structure['options']['actions_apres_evenement']) || array_search('suppression_fiche', array_column($structure['options']['actions_apres_evenement'], 'evenement')) === false )
				document.location=document.location;
			@endif
		});

		return retour_post_suppression;
	},
	
@endpush

@push('scripts')
	<script>
		
		@if(isset($structure) && isset($structure['colonne_droite']) && !empty($structure['colonne_droite']))
			window.onscroll = function (e) {  
				
				if(window.scrollY > 500 && partie_droite_repliee === false) {
					
					replie_partie_droite_fiche();
					partie_droite_repliee = true;
				}
			} 
		@endif


        $('.datepicker').datepicker({

            language: "fr",
            locale: "fr",
            orientation: "top left"
        });

		$(function() {

			$( "#fiche_liste_images" ).on( "sortstop", function( event, ui ) {

				var ordre_images = [];
				$('.fiche_liste_images').each(function() {
					
					ordre_images.push($(this).attr('id_image'));
				});
				
				
				//console.log(ordre_images);
				tri(ordre_images);
				//console.log('après');
				//console.log(ordre_images);
				
			});

			$( "#fiche_liste_images" ).disableSelection();

			function tri(ordre_images) {

				// console.log(ordre_images)

				$.ajax({

					type: 'POST',
					url : "{{ route('base_eden.fiche.index_post', [$management_element->_type_element, $management_element->modele->id, 'tri_image']) }}",
					data : { ordre_images: ordre_images },
					success: function (result) {

						// console.log(result)
					}
				});
            }

		});

	</script>

    @yield('script_supplement')

	@yield('scripts_logo')
@endpush
