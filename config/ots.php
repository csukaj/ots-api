<?php

$ots_conf = [
    'midday_separation_time' => '12:00:00',
    'media_image_dir' => 'modules/stylersmedia/images',
    'media_width_breakpoints' => [
        'xs' => 576,
        'sm' => 768,
        'md' => 1024,
        'lg' => 1280
    ],
    'site_languages' => [
        'seychelle-szigetek.hu' => 'hu'
    ],
    'site_locales' => [
        'seychelle-szigetek.hu' => 'hu-HU'
    ]
];

if (env('APP_ENV') == 'local' || env('APP_ENV') == 'testing') {
    $ots_conf['site_languages']['localhost'] = 'hu';
    $ots_conf['site_languages']['ots.local'] = 'hu';
    $ots_conf['site_languages']['ots.stylersdev.com'] = 'hu'; //demo server is currently on 'local' env. too bad...
    $ots_conf['site_languages']['gasztrokereso.hu'] = 'hu'; //@ivan - ez a fizetes tesztelesehez kell, amig nem lesz kulturaltabb megoldasunk

    $ots_conf['site_locales']['localhost'] = 'hu-HU';
    $ots_conf['site_locales']['ots.local'] = 'hu-HU';
    $ots_conf['site_locales']['ots.stylersdev.com'] = 'hu-HU'; //demo server is currently on 'local' env. too bad...
    $ots_conf['site_locales']['gasztrokereso.hu'] = 'hu-HU'; //@ivan - ez a fizetes tesztelesehez kell, amig nem lesz kulturaltabb megoldasunk

} elseif (env('APP_ENV') == 'demo') {
    $ots_conf['site_languages']['ots.stylersdev.com'] = 'hu';
    $ots_conf['site_locales']['ots.stylersdev.com'] = 'hu-HU';
} elseif (env('APP_ENV') == 'staging') {
    $ots_conf['site_languages']['ots-staging.stylersdev.com'] = 'en';
    $ots_conf['site_locales']['ots-staging.stylersdev.com'] = 'hu-HU';
}

return $ots_conf;
