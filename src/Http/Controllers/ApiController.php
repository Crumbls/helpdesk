<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Http\Controllers;

use Crumbls\HelpDesk\Models;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

abstract class ApiController extends Controller
{
    abstract public function getModel(): string;

	public function __construct()
	{
		$snakeName = Str::snake(class_basename($this->getModel()));
		if (config('helpdesk.api.'.$snakeName.'.usePolicy', false)) {
			$camelName = Str::camel(class_basename($this->getModel()));
			$this->authorizeResource(Models::$camelName(), 'priority');
		}
	}


	/**
	 * Display a listing of priorities.
	 *
	 * @see docs/api-priorities.md for curl examples
	 */
	public function index(Request $request): Response
	{
		$modelClass = $this->getModel();

		$query = $modelClass::query();

		return $this->buildResponse($query->paginate()->toArray(), $request, 200);
	}

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id): Response
    {
        $modelClass = $this->getModel();

        $record = $modelClass::find($id);

        if (!$record) {
            return $this->buildResponse([
                'error' => [
                    'message' => 'Record not found',
                    'status' => Response::HTTP_NOT_FOUND,
                ],
            ], $request, Response::HTTP_NOT_FOUND);
        }

        return $this->buildResponse($record->toArray(), $request, 200);
    }
}
