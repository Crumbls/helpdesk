<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Http\Controllers;

use Crumbls\HelpDesk\Models;
use Crumbls\HelpDesk\Models\CustomField;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class CustomFieldController extends ApiController
{
    public function getModel(): string
    {
        return Models::customField();
    }

    /**
     * Store a newly created custom field.
     */
    public function store(Request $request): Response
    {
        $validated = $this->validateCustomField($request);

        $modelClass = $this->getModel();

        $record = $modelClass::create($validated);

        return $this->buildResponse($record->toArray(), $request, Response::HTTP_CREATED);
    }

    /**
     * Update the specified custom field.
     */
    public function update(Request $request, string $id): Response
    {
        $modelClass = $this->getModel();
        $record = $modelClass::find($id);

        if (! $record) {
            return $this->buildResponse([
                'error' => [
                    'message' => 'Record not found',
                    'status' => Response::HTTP_NOT_FOUND,
                ],
            ], $request, Response::HTTP_NOT_FOUND);
        }

        $validated = $this->validateCustomField($request, $record->id, $record);

        $record->update($validated);

        return $this->buildResponse($record->fresh()->toArray(), $request, Response::HTTP_OK);
    }

    /**
     * Remove the specified custom field.
     */
    public function destroy(Request $request, string $id): Response
    {
        $modelClass = $this->getModel();
        $record = $modelClass::find($id);

        if (! $record) {
            return $this->buildResponse([
                'error' => [
                    'message' => 'Record not found',
                    'status' => Response::HTTP_NOT_FOUND,
                ],
            ], $request, Response::HTTP_NOT_FOUND);
        }

        $record->delete();

        return $this->buildResponse(['message' => 'Custom field deleted'], $request, Response::HTTP_OK);
    }

    protected function validateCustomField(Request $request, ?int $ignoreId = null, ?object $record = null): array
    {
        $validated = $request->validate([
            'key' => [
                $ignoreId ? 'sometimes' : 'required',
                'string',
                'max:100',
                'regex:/^[a-z][a-z0-9_]*$/',
                Rule::unique('helpdesk_custom_fields', 'key')->ignore($ignoreId),
            ],
            'label' => [$ignoreId ? 'sometimes' : 'required', 'string', 'max:255'],
            'type' => [$ignoreId ? 'sometimes' : 'required', 'string', Rule::in(CustomField::TYPES)],
            'description' => ['nullable', 'string'],
            'options' => ['nullable', 'array'],
            'options.*' => ['nullable', 'string', 'max:255'],
            'validation_rules' => ['nullable', 'array'],
            'validation_rules.*' => ['string', 'max:255'],
            'default_value' => ['nullable'],
            'department_id' => ['nullable', 'integer', 'exists:helpdesk_departments,id'],
            'ticket_type_id' => ['nullable', 'integer', 'exists:helpdesk_ticket_types,id'],
            'is_required' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer'],
        ]);

        $type = $validated['type'] ?? $record?->type;
        $options = $validated['options'] ?? $record?->options ?? [];

        if (in_array($type, [CustomField::TYPE_SELECT, CustomField::TYPE_RADIO, CustomField::TYPE_CHECKBOX], true)
            && empty($options)
        ) {
            throw ValidationException::withMessages([
                'options' => 'Options are required for select, radio, and checkbox custom fields.',
            ]);
        }

        return $validated;
    }
}
