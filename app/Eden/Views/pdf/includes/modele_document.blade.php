<html>
    <head>
        <style>
            {{ $css }}
        </style>
    </head>
    <body>
			<header>
				@include('eden::pdf.includes.modele_document_lignes', ['lignes' => $header, 'type' => 'header'])
			</header>



			<footer>
				@include('eden::pdf.includes.modele_document_lignes', ['lignes' => $footer, 'type' => 'footer'])
			</footer>
		<div class="body">
			<main>
				<div class="conteneur_principal">
					@include('eden::pdf.includes.modele_document_lignes', ['lignes' => $body, 'type' => 'body'])
				</div>
			</main>

			<recap_footer class="recap_footer" width="100%" style="position: absolute;bottom: 50px;width: 100%;">
				@include('eden::pdf.includes.modele_document_lignes', ['lignes' => $recap_footer, 'type' => 'recap_footer'])
			</recap_footer>

			<annexe>
				@include('eden::pdf.includes.modele_document_annexes', ['lignes' => $annexes, 'type' => 'annexes'])
			</annexe>

		</div>

    </body>
</html>
