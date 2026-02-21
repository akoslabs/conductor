<?php

declare(strict_types=1);

namespace Conductor\Tools;

use InvalidArgumentException;
use Prism\Prism\Contracts\Schema;
use Prism\Prism\Schema\ArraySchema;
use Prism\Prism\Schema\BooleanSchema;
use Prism\Prism\Schema\EnumSchema;
use Prism\Prism\Schema\NumberSchema;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;

final class SchemaMapper
{
    /**
     * Convert a JSON Schema array to a Prism Schema object.
     *
     * @param  string  $name  The parameter name.
     * @param  array<string, mixed>  $schema  The JSON Schema definition.
     *
     * @throws InvalidArgumentException
     */
    public static function toSchema(string $name, array $schema): Schema
    {
        $description = $schema['description'] ?? '';
        $type = $schema['type'] ?? 'string';

        if (isset($schema['enum'])) {
            return new EnumSchema($name, $description, $schema['enum']);
        }

        return match ($type) {
            'string' => new StringSchema($name, $description),
            'number', 'integer' => new NumberSchema($name, $description),
            'boolean' => new BooleanSchema($name, $description),
            'array' => self::toArraySchema($name, $description, $schema),
            'object' => self::toObjectSchema($name, $description, $schema),
            default => throw new InvalidArgumentException("Unsupported JSON Schema type: {$type}"),
        };
    }

    /**
     * Convert a JSON Schema array definition to a Prism ArraySchema.
     *
     * @param  string  $name  The parameter name.
     * @param  string  $description  The parameter description.
     * @param  array<string, mixed>  $schema  The JSON Schema definition.
     */
    private static function toArraySchema(string $name, string $description, array $schema): ArraySchema
    {
        $items = $schema['items'] ?? ['type' => 'string'];

        return new ArraySchema(
            $name,
            $description,
            self::toSchema('item', $items),
        );
    }

    /**
     * Convert a JSON Schema object definition to a Prism ObjectSchema.
     *
     * @param  string  $name  The parameter name.
     * @param  string  $description  The parameter description.
     * @param  array<string, mixed>  $schema  The JSON Schema definition.
     */
    private static function toObjectSchema(string $name, string $description, array $schema): ObjectSchema
    {
        $properties = [];
        $requiredFields = $schema['required'] ?? [];

        foreach ($schema['properties'] ?? [] as $propName => $propSchema) {
            $properties[] = self::toSchema($propName, $propSchema);
        }

        return new ObjectSchema(
            $name,
            $description,
            $properties,
            $requiredFields,
        );
    }
}
