# VueJS SSR
Render Vuejs and JS Framework by Prerender node system. This module working for laravel and [renderNode](https://github.com/hackerpro536/renderNode.git) module check out this code pls
# Publish Provider
```
php artisan vendor:publish --provider="LPTech\VueSSR\VueSSRProvider"
```
# User Guide by Manual
```
cd /vendor
git clone https://github.com/hackerpro536/vuejs-ssr.git
composer dump-autoload -a
```
# User Guide by Composer install
```
composer require lptech/vue-ssr
composer dump-autoload -a
```

# Environment info
```
# Value 1 is Enable, value 0 is Disable: Default = 1
RENDERING_ENABLE = ""
# URL of Node Rendering Server
RENDERING_URL = ""
# Flag to enable debuger, add param ?debug below query url to test.
FLAG_DEBUG = debug
# Protocol is http or https or website
PROTOCOL = ""
# Blacklist of link want to rendering, the link in this will be exclude when render format is string sepatare by comma, Ex: "/,*.js,*.css,*.xml,*.less"
BLACK_LIST = ""
WHITE_LIST = ""
# This is user-agent want to rendering ex: google, bing, yahoo, baidu, yandex ..
CRAWLER_AGENT = ""
```
