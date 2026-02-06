<?php

return [
    'label' => 'Department',
    'plural' => 'Departments',

    'fields' => [
        'title' => 'Title',
        'description' => 'Description',
        'color_background' => 'Background Color',
        'color_foreground' => 'Text Color',
        'is_active' => 'Active',
        'users' => 'Users',
        'tickets' => 'Tickets',
        'created_at' => 'Created',
        'updated_at' => 'Updated',
    ],

    'messages' => [
        'created' => 'Department created successfully.',
        'updated' => 'Department updated successfully.',
        'deleted' => 'Department deleted successfully.',
        'not_found' => 'Department not found.',
        'has_tickets' => 'Cannot delete department with associated tickets.',
    ],

    'actions' => [
        'create' => 'Create Department',
        'edit' => 'Edit Department',
        'delete' => 'Delete Department',
        'view' => 'View Department',
        'activate' => 'Activate',
        'deactivate' => 'Deactivate',
        'assign_user' => 'Assign User',
        'remove_user' => 'Remove User',
    ],
];
