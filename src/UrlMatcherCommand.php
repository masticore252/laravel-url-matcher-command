<?php
namespace Masticore\LaravelUrlTestMatcher;

use Closure;
use ReflectionFunction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Console\Command;
use Illuminate\Contracts\Routing\Registrar as Router;
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
                            {method=get : the method used to match the url}
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
        $method = $this->argument('method');
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

        $headers = ['Property','Value'];
        $rows = [
            ['Uri', $route->uri],
            ['Prefix', $route->action['prefix'] ?? 'null'],
            ['Methods', implode(', ',$route->methods)],
            ...$this->getHandler($route),
            ['Middleware', implode(', ',$route->action['middleware']) ?? 'null'],
            ['Namespace', $route->action['namespace'] ?? 'null'],
            ['Parameter Names', implode(', ', $route->parameterNames) ],
            ['Parameters', implode(', ', $route->parameters) ],
            ['Original Parameters', implode(', ', $route->originalParameters ?? []) ],
            ['Binding Fields', implode(', ', $route->bindingFields ?? []) ],
            ['Is Fallback', $route->isFallback ? 'true' : 'false' ],
            ['Where', implode(', ', $route->action['where']) ?? 'null'],
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

    protected function getHandler($route)
    {

        if ($route->action['uses'] instanceof Closure) {
            $reflector = new ReflectionFunction($route->action['uses']);
            return [
                ['Controller' , 'Closure at ' . $reflector->getFileName() . ':' . $reflector->getStartLine()],
            ];
        }


        return [
            ['Controller' , $route->action['controller']],
        ];

    }
}
