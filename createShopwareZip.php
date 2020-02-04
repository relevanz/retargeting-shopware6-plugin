<?php
$stagingDir = __DIR__.'/staging/';
$deployDir = $stagingDir.basename(__DIR__).'/';

system(sprintf('rm -rf %s', $stagingDir));
mkdir($deployDir, 0777, true);
system(sprintf("cp -r %s %s", __DIR__.'/src ', $deployDir.'src'));

$composer = json_decode(file_get_contents(__DIR__.'/composer.json'), true);
$composer['repositories'] = [
    [
        'type' => 'vcs',
        'url' => 'https://github.com/jenslukas/relevanz-core-plugin.git'
    ],
];
$composer['prefer-stable'] = true;
$composer['minimum-stability'] = 'dev';
$orgRequieres = $composer['require'];
foreach (array_keys($composer['require']) as $packageName) {
    if ($packageName !== 'releva/retargeting-base') {
        unset($composer['require'][$packageName]);
    }
}
file_put_contents($deployDir.'composer.json', json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
system(sprintf("composer install --working-dir=%s", $deployDir));
unlink($deployDir.'composer.lock');
unlink($deployDir.'vendor/autoload.php');
system(sprintf('rm -rf %s', $deployDir.'vendor/composer'));
system(sprintf('rm -rf %s', $deployDir.'vendor/releva/retargeting-base/.git*'));
$composer['autoload']['psr-4']['Releva\\Retargeting\\Base\\'] = 'vendor/releva/retargeting-base/lib';
$composer['require'] = [];
foreach ($orgRequieres as $packageName => $packageVersion) {
    if ($packageName !== 'releva/retargeting-base') {
        $composer['require'][$packageName] = $packageVersion;
    }
}
file_put_contents($deployDir.'composer.json', json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
system(sprintf('cd %s;', $stagingDir).'zip '.basename(__DIR__).'.zip -r ./'.basename(__DIR__));
rename($stagingDir.basename(__DIR__).'.zip', __DIR__.'/'.basename(__DIR__).'.zip');
system('rm -r '.$stagingDir);