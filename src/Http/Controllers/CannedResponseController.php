<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Http\Controllers;

use Crumbls\HelpDesk\Models;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CannedResponseController extends ApiController
{
    public function getModel(): string
    {
        return Models::cannedResponse();
    }

    /**
     * List canned responses, optionally filtered by department.
     *
     * GET /api/helpdesk/canned-responses?department_id=1
     */
    public function index(Request $request): Response
    {
        $modelClass = $this->getModel();
        $query = $modelClass::query();

        if ($request->has('department_id')) {
            $query->forDepartment((int) $request->input('department_id'));
        }

        $query->active()->orderBy('sort_order');

        $records = $query->with('department')->get();

        return $this->buildResponse($records->toArray(), $request, Response::HTTP_OK);
    }

    /**
     * Show a single canned response.
     *
     * GET /api/helpdesk/canned-responses/{id}
     */
    public function show(Request $request, string $id): Response
    {
        $modelClass = $this->getModel();
        $record = $modelClass::with('department')->find($id);

        if (!$record) {
            return $this->buildResponse([
                'error' => [
                    'message' => 'Canned response not found',
                    'status' => Response::HTTP_NOT_FOUND,
                ],
            ], $request, Response::HTTP_NOT_FOUND);
        }

        return $this->buildResponse($record->toArray(), $request, Response::HTTP_OK);
    }
}
