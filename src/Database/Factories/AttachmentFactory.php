<?php

namespace Crumbls\HelpDesk\Database\Factories;

use Crumbls\HelpDesk\Models\Attachment;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttachmentFactory extends Factory
{
    protected $model = Attachment::class;

    public function definition(): array
    {
        return [
            'attachable_id' => null,
            'attachable_type' => null,
            'user_id' => null,
            'filename' => 'helpdesk-attachments/' . fake()->uuid() . '.pdf',
            'original_filename' => fake()->word() . '.pdf',
            'mime_type' => 'application/pdf',
            'size' => fake()->numberBetween(1024, 1048576),
            'disk' => 'local',
        ];
    }
}
