<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CustomField extends Model
{
    use SoftDeletes;

    public const TYPE_TEXT = 'text';

    public const TYPE_TEXTAREA = 'textarea';

    public const TYPE_EMAIL = 'email';

    public const TYPE_NUMBER = 'number';

    public const TYPE_URL = 'url';

    public const TYPE_SELECT = 'select';

    public const TYPE_RADIO = 'radio';

    public const TYPE_CHECKBOX = 'checkbox';

    public const TYPE_BOOLEAN = 'boolean';

    public const TYPE_DATE = 'date';

    public const TYPE_DATETIME = 'datetime';

    public const TYPES = [
        self::TYPE_TEXT,
        self::TYPE_TEXTAREA,
        self::TYPE_EMAIL,
        self::TYPE_NUMBER,
        self::TYPE_URL,
        self::TYPE_SELECT,
        self::TYPE_RADIO,
        self::TYPE_CHECKBOX,
        self::TYPE_BOOLEAN,
        self::TYPE_DATE,
        self::TYPE_DATETIME,
    ];

    protected $table = 'helpdesk_custom_fields';

    protected $fillable = [
        'key',
        'label',
        'type',
        'description',
        'options',
        'validation_rules',
        'default_value',
        'department_id',
        'ticket_type_id',
        'is_required',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'options' => 'array',
        'validation_rules' => 'array',
        'default_value' => 'array',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(TicketType::class, 'ticket_type_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForTicketContext(Builder $query, ?int $departmentId, ?int $ticketTypeId): Builder
    {
        return $query
            ->where(function (Builder $query) use ($departmentId) {
                $query->whereNull('department_id')
                    ->orWhere('department_id', $departmentId);
            })
            ->where(function (Builder $query) use ($ticketTypeId) {
                $query->whereNull('ticket_type_id')
                    ->orWhere('ticket_type_id', $ticketTypeId);
            });
    }

    public static function validationRulesForTicket(?int $departmentId, ?int $ticketTypeId): array
    {
        return static::query()
            ->active()
            ->forTicketContext($departmentId, $ticketTypeId)
            ->orderBy('sort_order')
            ->get()
            ->mapWithKeys(fn (self $field): array => $field->validationRules())
            ->all();
    }

    public function validationRules(): array
    {
        $rules = [$this->is_required ? 'required' : 'nullable'];

        $rules = array_merge($rules, match ($this->type) {
            self::TYPE_EMAIL => ['email'],
            self::TYPE_NUMBER => ['numeric'],
            self::TYPE_URL => ['url'],
            self::TYPE_BOOLEAN => ['boolean'],
            self::TYPE_DATE, self::TYPE_DATETIME => ['date'],
            self::TYPE_SELECT, self::TYPE_RADIO => $this->optionValues() === []
                ? ['string']
                : [Rule::in($this->optionValues())],
            self::TYPE_CHECKBOX => ['array'],
            default => ['string'],
        });

        $rules = array_merge($rules, $this->validation_rules ?? []);
        $fieldRules = ["custom_fields.{$this->key}" => $rules];

        if ($this->type === self::TYPE_CHECKBOX && $this->optionValues() !== []) {
            $fieldRules["custom_fields.{$this->key}.*"] = [Rule::in($this->optionValues())];
        }

        return $fieldRules;
    }

    protected function optionValues(): array
    {
        $options = $this->options ?? [];

        if (array_is_list($options)) {
            return array_values($options);
        }

        return array_keys($options);
    }

    public static function validateTicketValues(?int $departmentId, ?int $ticketTypeId, array $payload): void
    {
        $rules = static::validationRulesForTicket($departmentId, $ticketTypeId);

        if ($rules === []) {
            return;
        }

        Validator::make($payload, $rules)->validate();
    }
}
