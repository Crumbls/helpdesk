<?php

use Crumbls\HelpDesk\Models\Attachment;
use Crumbls\HelpDesk\Models\Ticket;
use Crumbls\HelpDesk\Tests\Fixtures\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('allows the ticket submitter to view attachment metadata', function () {
    $submitter = User::query()->create([
        'name' => 'Submitter',
        'email' => 'submitter@example.com',
    ]);

    $ticket = Ticket::factory()->create(['submitter_id' => $submitter->id]);

    $attachment = Attachment::factory()->create([
        'attachable_type' => Ticket::class,
        'attachable_id' => $ticket->id,
        'user_id' => $submitter->id,
    ]);

    $response = $this
        ->actingAs($submitter)
        ->getJson("/api/helpdesk/attachments/{$attachment->id}");

    $response->assertOk();
    $response->assertJsonFragment([
        'id' => $attachment->id,
        'original_filename' => $attachment->original_filename,
    ]);
});

it('denies attachment metadata to users without ticket access', function () {
    $submitter = User::query()->create([
        'name' => 'Submitter',
        'email' => 'submitter@example.com',
    ]);

    $stranger = User::query()->create([
        'name' => 'Stranger',
        'email' => 'stranger@example.com',
    ]);

    $ticket = Ticket::factory()->create(['submitter_id' => $submitter->id]);

    $attachment = Attachment::factory()->create([
        'attachable_type' => Ticket::class,
        'attachable_id' => $ticket->id,
        'user_id' => $submitter->id,
    ]);

    $response = $this
        ->actingAs($stranger)
        ->getJson("/api/helpdesk/attachments/{$attachment->id}");

    $response->assertForbidden();
});

it('downloads attachment files for users with ticket access', function () {
    Storage::fake('local');

    $submitter = User::query()->create([
        'name' => 'Submitter',
        'email' => 'submitter@example.com',
    ]);

    $ticket = Ticket::factory()->create(['submitter_id' => $submitter->id]);
    $filename = 'helpdesk-attachments/example.pdf';

    Storage::disk('local')->put($filename, 'fake pdf contents');

    $attachment = Attachment::factory()->create([
        'attachable_type' => Ticket::class,
        'attachable_id' => $ticket->id,
        'user_id' => $submitter->id,
        'filename' => $filename,
        'original_filename' => 'example.pdf',
        'disk' => 'local',
    ]);

    $response = $this
        ->actingAs($submitter)
        ->get("/api/helpdesk/attachments/{$attachment->id}/download");

    $response->assertOk();
    $response->assertHeader('content-disposition');
});
