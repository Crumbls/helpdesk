<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Http\Controllers;

use Crumbls\HelpDesk\Models;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PriorityController extends ApiController
{

    public function getModel(): string
    {
        return Models::priority();
    }

    /**
     * Store a newly created priority.
     *
     * @see docs/api-priorities.md for curl examples
     */
    public function store(Request $request): Response
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'color_background' => ['nullable', 'string'],
            'color_foreground' => ['nullable', 'string'],
            'level' => ['integer', 'min:0'],
            'is_active' => ['boolean'],
            'is_default' => ['boolean'],
        ]);

        $modelClass = $this->getModel();

        if (!empty($validated['is_default'])) {
            $modelClass::where('is_default', true)->update(['is_default' => false]);
        }

        $record = $modelClass::create($validated);

        return $this->buildResponse($record->toArray(), $request, Response::HTTP_CREATED);
    }


    /**
     * Update the specified priority.
     *
     * @see docs/api-priorities.md for curl examples
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
            'level' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'is_default' => ['sometimes', 'boolean'],
        ]);

        if (!empty($validated['is_default'])) {
            $modelClass::where('is_default', true)
                ->where('id', '!=', $record->id)
                ->update(['is_default' => false]);
        }

        $record->update($validated);

        return $this->buildResponse($record->fresh()->toArray(), $request, 200);
    }

    /**
     * Remove the specified priority.
     *
     * @see docs/api-priorities.md for curl examples
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

        return $this->buildResponse(['message' => 'Priority deleted'], $request, 200);
    }
}
