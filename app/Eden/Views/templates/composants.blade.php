@include('eden::composants_vue.composants_specifique')
<?php // temps_execution('après composants_specifique'); ?>

<?php
$route = asset('storage/composants');
$route_storage = storage_path('app/public/composants');
$route_menu = asset('storage/composants');

if (!empty(moi_extranet()) || $_SERVER['HTTP_HOST'] == fonctionnalite('url_extranet')) {
    $route_menu = asset('storage/composants/extranet');
}

if (!isset($version_composants))
    $version_composants = parametre('version_composants');

if (!isset($version_composants_menus))
    $version_composants_menus = parametre('version_composants_menus');

$repertoires = [
    app_path('Eden/Views/composants_vue/js'),
    resource_path('views/vendor/eden/composants_vue/js')
];

$modules = [];

foreach ($repertoires as $repertoire) {

    if(!is_dir($repertoire))
        continue;

    $fichiers = scandir($repertoire);

    \App\Eden\Managements\Cache_management::recupere_modules_repertoire($fichiers, $modules, $repertoire);

}

foreach($modules as $index => $module){
    $modules[$index] = str_replace(['composants_vue.js.', '.'], ['', '/'], $module) . '.js';
}

?>

@foreach($modules as $module)

    @if(file_exists($route_storage . '/' . $module))
        <script type="text/javascript" src="{{ $route . '/' . $module }}?version={{$version_composants}}"></script>
    @endif

@endforeach

{{--Pour les menus--}}
<script type="text/javascript" src="{{  asset($route_menu.'/menus.js') }}?version={{$version_composants_menus}}"></script>
