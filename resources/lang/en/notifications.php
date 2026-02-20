<?php

return [
    'guest_login' => [
        'subject' => 'Your ticket: :ticket',
        'greeting' => 'Hi :name,',
        'line1' => 'A new support ticket has been created: ":ticket".',
        'line2' => 'Click below to log in and view your tickets.',
        'action' => 'Log In & View Tickets',
        'line3' => 'This link expires in :minutes minutes.',
    ],

    'admin_new_ticket' => [
        'subject' => '[Ticket #:id] :title',
        'greeting' => 'New support ticket submitted.',
        'line1' => '**:title** — submitted by :submitter (:email)',
        'line2' => 'Department: :department | Priority: :priority | Source: :source',
    ],

    'admin_new_comment' => [
        'subject' => '[Ticket #:id] New comment on: :title',
        'greeting' => 'New comment on a support ticket.',
        'line1' => ':user commented on **:title**:',
    ],

    'ticket_confirmation' => [
        'subject' => 'Ticket #:id received',
        'greeting' => 'Hi :name,',
        'line1' => 'Your ticket **#:id — :title** has been received.',
        'line2' => 'We\'ll get back to you as soon as possible.',
    ],

    'comment_reply' => [
        'subject' => '[Ticket #:id] Reply on: :title',
        'greeting' => 'Hi :name,',
        'line1' => 'There\'s a new reply on your ticket **:title**:',
    ],

    'ticket_status_updated' => [
        'subject' => '[Ticket #:id] Status changed to :status',
        'greeting' => 'Hi :name,',
        'line1' => 'Your ticket **:title** status changed from :old to **:new**.',
    ],

    'ticket_lookup' => [
        'subject' => 'Your support tickets',
        'greeting' => 'Hi :name,',
        'line1' => 'You have :count ticket(s) on file.',
        'action' => 'View My Tickets',
        'line2' => 'This link expires in :minutes minutes.',
    ],

    'ticket_assigned' => [
        'subject' => '[Ticket #:id] Assigned: :title',
        'greeting' => 'Hi :name,',
        'line1' => 'You\'ve been assigned to **:title** as :role.',
        'line2' => 'Priority: :priority | Department: :department',
    ],

    'satisfaction_rating' => [
        'subject' => '[Ticket #:id] Satisfaction rating received: :title',
        'greeting' => 'New satisfaction rating received.',
        'line1' => ':user rated ticket **:title** as :rating/5 stars.',
        'comment' => 'Comment: ":comment"',
    ],
];
