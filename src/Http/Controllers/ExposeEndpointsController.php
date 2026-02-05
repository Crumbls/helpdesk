<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Http\Controllers;

use Crumbls\HelpDesk\Models;
use Illuminate\Http\Request;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

class ExposeEndpointsController extends Controller
{

	public function __invoke(Request $request) : Response {

	    $currentPrefix = request()->route()?->getAction('prefix');

		$data = [];

	    $data['endpoints'] = collect(Route::getRoutes())
		    ->filter(fn ($route) => $route->getAction('prefix') === $currentPrefix)
		    ->map(fn ($route) => '/' . $route->uri())
		    ->values();

		return $this->buildResponse($data, $request, Response::HTTP_OK);
    }
}
