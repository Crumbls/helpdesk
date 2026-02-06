<?php

return [
    'label' => 'Type',
    'plural' => 'Types',

    'fields' => [
        'title' => 'Title',
        'description' => 'Description',
        'color_background' => 'Background Color',
        'color_foreground' => 'Text Color',
        'is_active' => 'Active',
        'is_default' => 'Default',
        'created_at' => 'Created',
        'updated_at' => 'Updated',
    ],

    'messages' => [
        'created' => 'Type created successfully.',
        'updated' => 'Type updated successfully.',
        'deleted' => 'Type deleted successfully.',
        'not_found' => 'Type not found.',
        'has_tickets' => 'Cannot delete type with associated tickets.',
        'default_set' => 'Default type set successfully.',
    ],

    'actions' => [
        'create' => 'Create Type',
        'edit' => 'Edit Type',
        'delete' => 'Delete Type',
        'view' => 'View Type',
        'activate' => 'Activate',
        'deactivate' => 'Deactivate',
        'set_default' => 'Set as Default',
    ],

    'defaults' => [
        'question' => 'Question',
        'incident' => 'Incident',
        'problem' => 'Problem',
        'task' => 'Task',
        'feature_request' => 'Feature Request',
    ],
];
