<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Http\Controllers;

use Crumbls\HelpDesk\Models;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DepartmentController extends ApiController
{
    public function getModel(): string
    {
        return Models::department();
    }

    /**
     * Store a newly created department.
     *
     * @see docs/api-departments.md for curl examples
     */
    public function store(Request $request): Response
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'color_background' => ['nullable', 'string'],
            'color_foreground' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $modelClass = $this->getModel();

        $record = $modelClass::create($validated);

        return $this->buildResponse($record->toArray(), $request, Response::HTTP_CREATED);
    }

    /**
     * Update the specified department.
     *
     * @see docs/api-departments.md for curl examples
     */
    public function update(Request $request, string $id): Response
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

        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'color_background' => ['nullable', 'string'],
            'color_foreground' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $record->update($validated);

        return $this->buildResponse($record->fresh()->toArray(), $request, 200);
    }

    /**
     * Remove the specified department.
     *
     * @see docs/api-departments.md for curl examples
     */
    public function destroy(Request $request, string $id): Response
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

        $record->delete();

        return $this->buildResponse(['message' => 'Department deleted'], $request, 200);
    }
}
