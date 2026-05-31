$u = App\Models\User::find(25);
$svc = app(App\Services\Mobile\AcademyService::class);
echo '--- SCOPE COUNTS ---' . PHP_EOL;
foreach ($svc->scopeChipsFor($u, 'en') as $c) {
    echo '  ' . $c['key'] . ' (' . $c['label'] . ') = ' . $c['count'] . PHP_EOL;
}
foreach (['all', 'special', 'general'] as $scope) {
    echo '--- LIST scope=' . $scope . ' ---' . PHP_EOL;
    foreach ($svc->listAvailable($u, null, null, 50, $scope)->items() as $c) {
        $title = is_array($c->title) ? ($c->title['en'] ?? reset($c->title)) : $c->title;
        echo '  #' . $c->id . " '" . $title . "' for_public=" . var_export($c->for_public, true) . PHP_EOL;
    }
}
