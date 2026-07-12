<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Support;

use Crumbls\HelpDesk\Models;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use InvalidArgumentException;

class UserForeignKey
{
    public static function add(
        Blueprint $table,
        string $column,
        bool $nullable = false,
        string $onDelete = 'cascade',
    ): void {
        $user = self::userModel();
        $userTable = config('helpdesk.user_keys.table') ?: $user->getTable();
        $userKey = config('helpdesk.user_keys.key') ?: $user->getKeyName();
        $columnConfig = self::columnConfig($column);
        $type = strtolower((string) ($columnConfig['type'] ?? self::inferType($user)));

        $foreign = match ($type) {
            'int', 'bigint' => self::integerForeign($table, $column, $nullable, $userTable, $userKey),
            'uuid' => self::uuidForeign($table, $column, $nullable, $userTable, $userKey),
            'ulid' => self::ulidForeign($table, $column, $nullable, $userTable, $userKey, $columnConfig['length'] ?? null),
            'string' => self::stringForeign($table, $column, $nullable, $userTable, $userKey, $columnConfig),
            'char' => self::charForeign($table, $column, $nullable, $userTable, $userKey, $columnConfig),
            default => throw new InvalidArgumentException("Unsupported helpdesk user key type [{$type}]."),
        };

        match (strtolower($onDelete)) {
            'cascade' => $foreign->cascadeOnDelete(),
            'null', 'set null', 'set_null' => $foreign->nullOnDelete(),
            'restrict' => $foreign->restrictOnDelete(),
            'no action', 'no_action' => $foreign->noActionOnDelete(),
            default => null,
        };
    }

    protected static function userModel(): Model
    {
        $userClass = Models::user();

        return new $userClass;
    }

    protected static function columnConfig(string $column): array
    {
        $default = config('helpdesk.user_keys.column', []);
        $specific = config("helpdesk.user_keys.columns.{$column}", []);

        return array_merge(
            is_array($default) ? $default : [],
            is_array($specific) ? $specific : [],
        );
    }

    protected static function inferType(Model $user): string
    {
        if ($user->getKeyType() === 'int') {
            return 'bigint';
        }

        $traits = class_uses_recursive($user) ?: [];

        if (in_array(HasUlids::class, $traits, true)) {
            return 'ulid';
        }

        return 'uuid';
    }

    protected static function integerForeign(Blueprint $table, string $column, bool $nullable, mixed $userTable, mixed $userKey): mixed
    {
        $definition = $table->foreignId($column);

        if ($nullable) {
            $definition->nullable();
        }

        return $definition->constrained((string) $userTable, (string) $userKey);
    }

    protected static function uuidForeign(Blueprint $table, string $column, bool $nullable, mixed $userTable, mixed $userKey): mixed
    {
        $definition = $table->foreignUuid($column);

        if ($nullable) {
            $definition->nullable();
        }

        return $definition->constrained((string) $userTable, (string) $userKey);
    }

    protected static function ulidForeign(Blueprint $table, string $column, bool $nullable, mixed $userTable, mixed $userKey, mixed $length): mixed
    {
        $definition = $table->foreignUlid($column, is_numeric($length) ? (int) $length : 26);

        if ($nullable) {
            $definition->nullable();
        }

        return $definition->constrained((string) $userTable, (string) $userKey);
    }

    protected static function stringForeign(Blueprint $table, string $column, bool $nullable, mixed $userTable, mixed $userKey, array $config): mixed
    {
        $definition = $table->string($column, self::length($config, 255));

        self::configureStringColumn($definition, $nullable, $config);

        return $table->foreign($column)->references((string) $userKey)->on((string) $userTable);
    }

    protected static function charForeign(Blueprint $table, string $column, bool $nullable, mixed $userTable, mixed $userKey, array $config): mixed
    {
        $definition = $table->char($column, self::length($config, 36));

        self::configureStringColumn($definition, $nullable, $config);

        return $table->foreign($column)->references((string) $userKey)->on((string) $userTable);
    }

    protected static function configureStringColumn(mixed $definition, bool $nullable, array $config): void
    {
        if (! empty($config['collation'])) {
            $definition->collation((string) $config['collation']);
        }

        if ($nullable) {
            $definition->nullable();
        }
    }

    protected static function length(array $config, int $default): int
    {
        return is_numeric($config['length'] ?? null) ? (int) $config['length'] : $default;
    }
}
