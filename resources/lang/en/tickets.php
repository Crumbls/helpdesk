<?php

return [
    'label' => 'Ticket',
    'plural' => 'Tickets',

    'fields' => [
        'title' => 'Title',
        'description' => 'Description',
        'status' => 'Status',
        'priority' => 'Priority',
        'department' => 'Department',
        'type' => 'Type',
        'submitter' => 'Submitter',
        'assignee' => 'Assignee',
        'assignees' => 'Assignees',
        'watchers' => 'Watchers',
        'source' => 'Source',
        'resolution' => 'Resolution',
        'due_at' => 'Due Date',
        'closed_at' => 'Closed Date',
        'created_at' => 'Created',
        'updated_at' => 'Updated',
        'parent_ticket' => 'Parent Ticket',
        'child_tickets' => 'Child Tickets',
    ],

    'messages' => [
        'created' => 'Ticket created successfully.',
        'updated' => 'Ticket updated successfully.',
        'deleted' => 'Ticket deleted successfully.',
        'not_found' => 'Ticket not found.',
        'assigned' => 'Ticket assigned successfully.',
        'unassigned' => 'User removed from ticket.',
        'closed' => 'Ticket closed successfully.',
        'reopened' => 'Ticket reopened successfully.',
    ],

    'actions' => [
        'create' => 'Create Ticket',
        'edit' => 'Edit Ticket',
        'delete' => 'Delete Ticket',
        'view' => 'View Ticket',
        'assign' => 'Assign',
        'unassign' => 'Unassign',
        'close' => 'Close',
        'reopen' => 'Reopen',
        'add_comment' => 'Add Comment',
        'add_watcher' => 'Add Watcher',
    ],

    'sources' => [
        'web' => 'Web',
        'email' => 'Email',
        'phone' => 'Phone',
        'chat' => 'Chat',
        'api' => 'API',
    ],
];
