<?php 

namespace LPTech\VueSSR;
use App;
use GuzzleHttp\Client;
use Illuminate\Foundation\Http\Kernel;
use Illuminate\Support\ServiceProvider;

class VueSSRProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    protected $package = 'lptech/vue-ssr';

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/rendering.php' => config_path('rendering.php')
        ], 'config');

        if ($this->app['config']->get('rendering.enable')) {
            /** @var Kernel $kernel */
            $kernel = $this->app['Illuminate\Contracts\Http\Kernel'];
            $kernel->pushMiddleware(VueSSRMiddleware::class);
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/rendering.php', 'rendering');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [VueSSRProvider::class];
    }
}
