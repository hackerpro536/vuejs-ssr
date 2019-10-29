<?php 
return [
    'enable' => env('RENDERING_ENABLE', true),
    'flag_debug' => env('FLAG_DEBUG', 'render'),
    'protocol' => env('PROTOCOL', 'https'),
    'rendering_url' => env('RENDERING_URL'),
    'render_all' => 0,
    'whitelist' =>  !empty(env('WHITE_LIST')) ? explode(',',env('WHITE_LIST')) : [],
    'blacklist' =>  !empty(env('BLACK_LIST')) ? explode(',',env('BLACK_LIST')) : [
        '*.js',
        '*.css',
        '*.xml',
        '*.less',
        '*.png',
        '*.jpg',
        '*.jpeg',
        '*.gif',
        '*.pdf',
        '*.doc',
        '*.txt',
        '*.ico',
        '*.rss',
        '*.zip',
        '*.mp3',
        '*.rar',
        '*.exe',
        '*.wmv',
        '*.doc',
        '*.avi',
        '*.ppt',
        '*.mpg',
        '*.mpeg',
        '*.tif',
        '*.wav',
        '*.mov',
        '*.psd',
        '*.ai',
        '*.xls',
        '*.mp4',
        '*.m4a',
        '*.swf',
        '*.dat',
        '*.dmg',
        '*.iso',
        '*.flv',
        '*.m4v',
        '*.torrent'
    ],
    'crawler_user_agents' => !empty(env('CRAWLER_AGENT')) ? explode(',',env('CRAWLER_AGENT')) : [
        'googlebot',
        'yahoo',
        'bingbot',
        'postman',
        'facebookexternalhit',
        'twitterbot',
        'linkedinbot',
        'embedly',
        'showyoubot',
        'outbrain',
        'pinterest',
        'developers.google.com/+/web/snippet',
        'slackbot',
    ],
];