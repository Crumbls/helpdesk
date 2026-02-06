<?php

return [
    'ticket' => [
        'title' => [
            'required' => 'The ticket title is required.',
            'max' => 'The ticket title must not exceed :max characters.',
        ],
        'description' => [
            'required' => 'The ticket description is required.',
        ],
        'priority_id' => [
            'required' => 'Please select a priority.',
            'exists' => 'The selected priority is invalid.',
        ],
        'status_id' => [
            'required' => 'Please select a status.',
            'exists' => 'The selected status is invalid.',
        ],
        'department_id' => [
            'exists' => 'The selected department is invalid.',
        ],
        'type_id' => [
            'exists' => 'The selected type is invalid.',
        ],
        'due_at' => [
            'date' => 'The due date must be a valid date.',
            'after' => 'The due date must be in the future.',
        ],
    ],

    'comment' => [
        'content' => [
            'required' => 'The comment content is required.',
        ],
        'ticket_id' => [
            'required' => 'A ticket must be specified.',
            'exists' => 'The specified ticket does not exist.',
        ],
    ],

    'department' => [
        'title' => [
            'required' => 'The department title is required.',
            'max' => 'The department title must not exceed :max characters.',
            'unique' => 'A department with this title already exists.',
        ],
    ],

    'priority' => [
        'title' => [
            'required' => 'The priority title is required.',
            'max' => 'The priority title must not exceed :max characters.',
            'unique' => 'A priority with this title already exists.',
        ],
        'level' => [
            'required' => 'The priority level is required.',
            'integer' => 'The priority level must be an integer.',
            'min' => 'The priority level must be at least :min.',
        ],
    ],

    'status' => [
        'title' => [
            'required' => 'The status title is required.',
            'max' => 'The status title must not exceed :max characters.',
            'unique' => 'A status with this title already exists.',
        ],
    ],

    'type' => [
        'title' => [
            'required' => 'The type title is required.',
            'max' => 'The type title must not exceed :max characters.',
            'unique' => 'A type with this title already exists.',
        ],
    ],

    'common' => [
        'color' => [
            'hex' => 'The color must be a valid hex color code.',
        ],
        'is_active' => [
            'boolean' => 'The active field must be true or false.',
        ],
        'is_default' => [
            'boolean' => 'The default field must be true or false.',
        ],
    ],
];
