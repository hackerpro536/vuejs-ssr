<?php


namespace LPTech\VueSSR;


use Closure;
use Redirect;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Contracts\Foundation\Application;
use GuzzleHttp\Client as Guzzle;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpFoundation\Response;

class VueSSRMiddleware
{
    private $app;
    private $client;
    private $crawlerUserAgents;
    private $whitelist;
    private $blacklist;
    private $renderingUri;
    private $returnSoftHttpCodes;
    private $flag;
    public function __construct(Application $app, Guzzle $client)
    {
        $this->app = $app;
        $this->returnSoftHttpCodes = true;

        if ($this->returnSoftHttpCodes) {
            $this->client = $client;
        } else {
            // Workaround to avoid following redirects
            $config = $client->getConfig();
            $config['allow_redirects'] = false;
            $this->client = new Guzzle($config);
        }

        $config = $app['config']->get('rendering');
        $this->flag = $config['flag_debug'];
        $this->renderingUri = $config['rendering_url'];
        $this->crawlerUserAgents = $config['crawler_user_agents'];
        $this->whitelist = $config['whitelist'];
        $this->blacklist = $config['blacklist'];
    }

    /**
     * Handles a request and prerender if it should, otherwise call the next middleware.
     *
     * @param $request
     * @param Closure $next
     * @return Response
     * @internal param int $type
     * @internal param bool $catch
     */
    public function handle($request, Closure $next)
    {
        if ($this->RenderingPage($request)) {
            $renderingResponse = $this->getRenderedPageResponse($request);

            if ($renderingResponse) {
                $statusCode = $renderingResponse->getStatusCode();

                if (!$this->returnSoftHttpCodes && $statusCode >= 300 && $statusCode < 400) {
                    return Redirect::to($renderingResponse->getHeaders()["Location"][0], $statusCode);
                }

                return $this->buildSymfonyResponseFromGuzzleResponse($renderingResponse);
            }
        }

        return $next($request);
    }

    /**
     * Returns whether the request must be rendered.
     *
     * @param $request
     * @return bool
     */
    private function RenderingPage($request)
    {
        $userAgent = strtolower($request->server->get('HTTP_USER_AGENT'));
        $bufferAgent = $request->server->get('X-BUFFERBOT');
        $requestUri = $request->getRequestUri();
        $referer = $request->headers->get('Referer');

        $isRequestingPrerenderedPage = false;

        if (!$userAgent) return false;
        if (!$request->isMethod('GET')) return false;
        if ($request->query->has($this->flag)) $isRequestingPrerenderedPage = true;
        // prerender if a crawler is detected
        foreach ($this->crawlerUserAgents as $crawlerUserAgent) {
            if (str_contains($userAgent, strtolower($crawlerUserAgent))) {
                $isRequestingPrerenderedPage = true;
            }
        }

        if ($bufferAgent) $isRequestingPrerenderedPage = true;

        if (!$isRequestingPrerenderedPage) return false;

        // only check whitelist if it is not empty
        if ($this->whitelist) {
            if (!$this->isListed($requestUri, $this->whitelist)) {
                return false;
            }
        }

        // only check blacklist if it is not empty
        if ($this->blacklist) {
            $uris[] = $requestUri;
            // we also check for a blacklisted referer
            if ($referer) $uris[] = $referer;
            if ($this->isListed($uris, $this->blacklist)) {
                return false;
            }
        }

        // Okay! Prerender please.
        return true;
    }

    /**
     * render the page and return the Guzzle Response
     *
     * @param $request
     * @return null|void
     */
    private function getRenderedPageResponse($request)
    {
        $headers = [
            'User-Agent' => $request->server->get('HTTP_USER_AGENT'),
        ];

        $protocol = $request->isSecure() ? 'https' : 'http';
    
        try {
            // Return the Guzzle Response
        $host = $request->getHost();
            $path = $request->Path();
            // Fix "//" 404 error
            if ($path == "/") {
                $path = "";
            }
            return $this->client->get($this->renderingUri . '/' . urlencode($protocol.'://'.$host.'/'.$path), compact('headers'));
        } catch (RequestException $exception) {
            if(!$this->returnSoftHttpCodes && !empty($exception->getResponse()) && $exception->getResponse()->getStatusCode() == 404) {
                \App::abort(404);
            }
            // In case of an exception, we only throw the exception if we are in debug mode. Otherwise,
            // we return null and the handle() method will just pass the request to the next middleware
            // and we do not show a prerendered page.
            if ($this->app['config']->get('app.debug')) {
                throw $exception;
            }
            return null;
        }
    }

    /**
     * Convert a Guzzle Response to a Symfony Response
     *
     * @param ResponseInterface $renderingResponse
     * @return Response
     */
    private function buildSymfonyResponseFromGuzzleResponse(ResponseInterface $renderingResponse)
    {
        return (new HttpFoundationFactory)->createResponse($renderingResponse);
    }

    /**
     * Check whether one or more needles are in the given list
     *
     * @param $needles
     * @param $list
     * @return bool
     */
    private function isListed($needles, $list)
    {
        $needles = is_array($needles) ? $needles : [$needles];

        foreach ($list as $pattern) {
            foreach ($needles as $needle) {
                if (str_is($pattern, $needle)) {
                    return true;
                }
            }
        }
        return false;
    }

}
