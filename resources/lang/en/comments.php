<?php

return [
    'label' => 'Comment',
    'plural' => 'Comments',

    'fields' => [
        'content' => 'Content',
        'is_private' => 'Private',
        'is_resolution' => 'Resolution',
        'user' => 'Author',
        'ticket' => 'Ticket',
        'created_at' => 'Created',
        'updated_at' => 'Updated',
    ],

    'messages' => [
        'created' => 'Comment added successfully.',
        'updated' => 'Comment updated successfully.',
        'deleted' => 'Comment deleted successfully.',
        'not_found' => 'Comment not found.',
    ],

    'actions' => [
        'create' => 'Add Comment',
        'edit' => 'Edit Comment',
        'delete' => 'Delete Comment',
        'view' => 'View Comment',
        'make_private' => 'Make Private',
        'make_public' => 'Make Public',
        'mark_resolution' => 'Mark as Resolution',
    ],

    'types' => [
        'public' => 'Public Comment',
        'private' => 'Private Note',
        'resolution' => 'Resolution',
    ],
];
