<?php

namespace App\Http\Controllers\OParl\V10;

use App\Http\Controllers\Controller;
use Illuminate\Routing\Router;

class RootController extends Controller
{
    public function index(Router $router)
    {
//        $routes = collect($router->getRoutes()->get('GET'))
//            ->filter(function (Route $route) {
//                return starts_with($route->getName(), 'api.oparl.v1');
//            });
//
//        return view('server::overview', compact('routes'));
        return redirect()->route('api.oparl.v1.system.index', ['format' => 'html']);
    }
}