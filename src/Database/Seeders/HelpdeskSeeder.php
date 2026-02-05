<?php

namespace Crumbls\HelpDesk\Database\Seeders;

use Illuminate\Database\Seeder;
use Crumbls\HelpDesk\Models\Priority;
use Crumbls\HelpDesk\Models\TicketStatus;
use Crumbls\HelpDesk\Models\TicketType;
use Crumbls\HelpDesk\Models\Department;

class HelpdeskSeeder extends Seeder
{
    public function run(): void
    {
        $priorities = [
            [
                'title' => 'Urgent',
                'description' => 'Critical issues requiring immediate attention',
                'color_background' => '#EF4444',
                'color_foreground' => '#ffffff',
                'level' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'High',
                'description' => 'Important issues that need quick resolution',
                'color_background' => '#F59E0B',
                'color_foreground' => '#000000',
                'level' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Normal',
                'description' => 'Standard priority issues',
                'color_background' => '#3B82F6',
                'color_foreground' => '#ffffff',
                'level' => 3,
                'is_active' => true,
                'is_default' => true,
            ],
            [
                'title' => 'Low',
                'description' => 'Non-urgent issues',
                'color_background' => '#3B82F6',
                'color_foreground' => '#ffffff',
                'level' => 4,
                'is_active' => true,
            ],
        ];

        foreach ($priorities as $priority) {
            Priority::firstOrCreate(
                ['title' => $priority['title']],
                $priority
            );
        }

        $statuses = [
            [
                'title' => 'New',
                'description' => 'Newly created ticket',
                'color_background' => '#3B82F6',
                'color_foreground' => '#ffffff',
                'is_active' => true,
                'is_default' => true,
            ],
            [
                'title' => 'In Progress',
                'description' => 'Ticket is being worked on',
                'color_background' => '#3B82F6',
                'color_foreground' => '#ffffff',
                'is_active' => true,
            ],
            [
                'title' => 'Waiting for Customer',
                'description' => 'Awaiting customer response',
                'color_background' => '#F59E0B',
                'color_foreground' => '#000000',
                'is_active' => true,
            ],
            [
                'title' => 'Resolved',
                'description' => 'Issue has been resolved',
                'color_background' => '#10B981',
                'color_foreground' => '#ffffff',
                'is_active' => true,
                'is_closed' => true,
            ],
            [
                'title' => 'Closed',
                'description' => 'Ticket has been closed',
                'color_background' => '#6B7280',
                'color_foreground' => '#ffffff',
                'is_active' => true,
                'is_closed' => true,
            ],
        ];

        foreach ($statuses as $status) {
            TicketStatus::firstOrCreate(
                ['title' => $status['title']],
                $status
            );
        }

        $types = [
            [
                'title' => 'Bug Report',
                'description' => 'Report of software defects or issues',
                'color_background' => '#EF4444',
                'color_foreground' => '#ffffff',
                'is_active' => true,
            ],
            [
                'title' => 'Feature Request',
                'description' => 'Request for new features or enhancements',
                'color_background' => '#3B82F6',
                'color_foreground' => '#ffffff',
                'is_active' => true,
            ],
            [
                'title' => 'Support',
                'description' => 'General support inquiries',
                'color_background' => '#3B82F6',
                'color_foreground' => '#ffffff',
                'is_active' => true,
            ],
            [
                'title' => 'Question',
                'description' => 'General questions about the product',
                'color_background' => '#F59E0B',
                'color_foreground' => '#000000',
                'is_active' => true,
            ],
        ];

        foreach ($types as $type) {
            TicketType::firstOrCreate(
                ['title' => $type['title']],
                $type
            );
        }

        $departments = [
            [
                'title' => 'Technical Support',
                'description' => 'Technical support and troubleshooting',
                'color_background' => '#3B82F6',
                'color_foreground' => '#ffffff',
                'is_active' => true,
            ],
            [
                'title' => 'Billing',
                'description' => 'Billing and payment related inquiries',
                'color_background' => '#10B981',
                'color_foreground' => '#ffffff',
                'is_active' => true,
            ],
            [
                'title' => 'Sales',
                'description' => 'Sales related inquiries',
                'color_background' => '#3B82F6',
                'color_foreground' => '#ffffff',
                'is_active' => true,
            ],
            [
                'title' => 'General',
                'description' => 'General inquiries',
                'color_background' => '#6B7280',
                'color_foreground' => '#ffffff',
                'is_active' => true,
            ],
        ];

        foreach ($departments as $department) {
            Department::firstOrCreate(
                ['title' => $department['title']],
                $department
            );
        }
    }
}
