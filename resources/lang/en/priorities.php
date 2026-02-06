<?php

return [
    'label' => 'Priority',
    'plural' => 'Priorities',

    'fields' => [
        'title' => 'Title',
        'description' => 'Description',
        'color_background' => 'Background Color',
        'color_foreground' => 'Text Color',
        'level' => 'Level',
        'is_active' => 'Active',
        'is_default' => 'Default',
        'sla_response_hours' => 'SLA Response Hours',
        'sla_resolution_hours' => 'SLA Resolution Hours',
        'created_at' => 'Created',
        'updated_at' => 'Updated',
    ],

    'messages' => [
        'created' => 'Priority created successfully.',
        'updated' => 'Priority updated successfully.',
        'deleted' => 'Priority deleted successfully.',
        'not_found' => 'Priority not found.',
        'has_tickets' => 'Cannot delete priority with associated tickets.',
        'default_set' => 'Default priority set successfully.',
    ],

    'actions' => [
        'create' => 'Create Priority',
        'edit' => 'Edit Priority',
        'delete' => 'Delete Priority',
        'view' => 'View Priority',
        'activate' => 'Activate',
        'deactivate' => 'Deactivate',
        'set_default' => 'Set as Default',
    ],

    'defaults' => [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'urgent' => 'Urgent',
        'critical' => 'Critical',
    ],
];
