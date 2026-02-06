<?php

return [
    'label' => 'Status',
    'plural' => 'Statuses',

    'fields' => [
        'title' => 'Title',
        'description' => 'Description',
        'color_background' => 'Background Color',
        'color_foreground' => 'Text Color',
        'is_active' => 'Active',
        'is_default' => 'Default',
        'is_closed' => 'Closed State',
        'sort_order' => 'Sort Order',
        'created_at' => 'Created',
        'updated_at' => 'Updated',
    ],

    'messages' => [
        'created' => 'Status created successfully.',
        'updated' => 'Status updated successfully.',
        'deleted' => 'Status deleted successfully.',
        'not_found' => 'Status not found.',
        'has_tickets' => 'Cannot delete status with associated tickets.',
        'default_set' => 'Default status set successfully.',
    ],

    'actions' => [
        'create' => 'Create Status',
        'edit' => 'Edit Status',
        'delete' => 'Delete Status',
        'view' => 'View Status',
        'activate' => 'Activate',
        'deactivate' => 'Deactivate',
        'set_default' => 'Set as Default',
    ],

    'defaults' => [
        'open' => 'Open',
        'pending' => 'Pending',
        'in_progress' => 'In Progress',
        'on_hold' => 'On Hold',
        'resolved' => 'Resolved',
        'closed' => 'Closed',
    ],
];
