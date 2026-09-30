@extends('eden::templates.template')

@section('title')
	Saisie des temps interne
@endsection

@section('styles')
	<style type="text/css">
		/* CSS Mobile */
	    @media (max-width: 767px) {

	    	.table_responsive_saisie_temps{
	    		width: 200% !important;
	    	}

	    	.responsive_saisie_15{
	    		width: 20% !important;
	    	}

	    	.responsive_saisie_25{
	    		width: 50% !important;
	    	}

	    	.responsive_saisie_30{
	    		width: 60% !important;
	    	}
	    }
	    progress {
		  /* Reset the default appearance */
		  -webkit-appearance: none;
		  border-radius: 25px;
		}	   

		progress::-webkit-progress-value {
		  background: red;
		  border-radius: 25px;
		}

		.progress_complete::-webkit-progress-value {
		  background: green;
		  border-radius: 25px;
		}

		progress::-moz-progress-bar {
		  background: #eee;
		  border-radius: 25px;
		}

		progress::-webkit-progress-value {
		  background: red;
		  border-radius: 25px;
		}

		.progress_complete::-webkit-progress-value {
		  background: green;
		  border-radius: 25px;
		}

		progress::-webkit-progress-bar {
		  background: #eee;
		  border-radius: 25px;
		}
	</style>
@endsection

@section('content')
	<div class="content-wrapper" >
		<div id="base-content" class="container-fluid">
			<saisie-des-temps-interne></saisie-des-temps-interne>
		</div>
	</div>
@endsection





