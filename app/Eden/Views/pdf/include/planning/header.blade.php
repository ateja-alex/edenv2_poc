<header>
    <img src="{{ $logo_application }}">
    <div class="titre_calendrier">
        <p>{{ traduction('pdf.calendrier.titre', null, [$numero_semaine,$date_debut['format_fr'],$date_fin['format_fr']]) }}</p>
    </div>
</header>