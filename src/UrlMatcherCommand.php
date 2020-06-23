<?php
namespace Masticore\LaravelUrlTestMatcher;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Console\Command;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class UrlMatcherCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'route:match
                            {url : the url to match}
                            {--m|method=get : the method used to match the url}
                            {--s|style=default : default style for the table, can be compact, borderless, box or box-double}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'match a given route against the router, finds it\'s assigned handler, if any';

    /**
     * Execute the console command.
     *
     * @param  \App\DripEmailer  $drip
     * @return mixed
     */
    public function handle(Router $router)
    {
        $tableStyle = $this->option('style');
        $method = $this->option('method');
        $url = $this->argument('url');

        $request = $this->buildRequestObject(
            $method,
            $url
        );

        try {
            $route = $router->getRoutes()->match($request);
        } catch (NotFoundHttpException $th) {
            $this->error('No route matches');
            $this->error('A "'.\strtoupper($method).'" request to "'.$url.'" will throw a "HTTP 404 Not Found"');
            return;
        }

        $data = [];
        $data['uri'] = $route->uri;
        $data['methods'] = $route->methods;
        $data['action']['controller'] = $route->action['controller'];
        $data['action']['middleware'] = $route->action['middleware'];
        $data['action']['where'] = $route->action['where'];
        $data['action']['uses'] = $route->action['uses'];
        $data['action']['prefix'] = $route->action['prefix'];
        $data['action']['namespace'] = $route->action['namespace'];
        $data['parameters'] = $route->parameters;
        $data['originalParameters'] = $route->originalParameters;
        $data['parameterNames'] = $route->parameterNames;
        $data['bindingFields'] = $route->bindingFields;
        $data['isFallback'] = $route->isFallback;

        $headers = ['Property','Value'];
        $rows = [
            ['Uri', $route->uri],
            ['Methods', implode(', ',$route->methods)],
            ['Controller', $route->action['controller'] ],
            ['Middleware', implode(', ',$route->action['middleware']) ],
            ['Where', implode(', ', $route->action['where']) ],
            ['Uses', $route->action['uses'] ],
            ['Prefix', $route->action['prefix'] ],
            ['Namespace', $route->action['namespace'] ],
            ['Parameters', implode(', ', $route->parameters) ],
            ['Original Parameters', implode(', ', $route->originalParameters ?? []) ],
            ['Parameter Names', implode(', ', $route->parameterNames) ],
            ['Binding Fields', implode(', ', $route->bindingFields ?? []) ],
            ['Fallback', $route->isFallback ? 'true' : 'false' ],
        ];

        $this->info('A '.$method.' request to "'.$url.'" matches the following route:');
        $this->table($headers, $rows, $tableStyle);
    }

    /**
     * Based on call() from Illuminate\Foundation\Testing\Concerns\MakesHttpRequests trait
     */
    protected function buildRequestObject($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
    {
        $symfonyRequest = SymfonyRequest::create(
            $this->prepareUrlForRequest($uri),
            $method,
            $parameters,
            $cookies,
            $files,
            $server,
            $content
        );

        return Request::createFromBase($symfonyRequest);
    }

    /**
     * this is a copy of prepareUrlForRequest() from Illuminate\Foundation\Testing\Concerns\MakesHttpRequests trait
     */
    protected function prepareUrlForRequest($uri)
    {
        if (Str::startsWith($uri, '/')) {
            $uri = substr($uri, 1);
        }

        return trim(url($uri), '/');
    }
}