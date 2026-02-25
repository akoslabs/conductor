<?php

declare(strict_types=1);

use Conductor\Tools\SchemaMapper;
use Prism\Prism\Schema\ArraySchema;
use Prism\Prism\Schema\BooleanSchema;
use Prism\Prism\Schema\EnumSchema;
use Prism\Prism\Schema\NumberSchema;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;

it('maps string type to StringSchema', function () {
    $schema = SchemaMapper::toSchema('name', [
        'type' => 'string',
        'description' => 'A name',
    ]);

    expect($schema)->toBeInstanceOf(StringSchema::class)
        ->and($schema->name())->toBe('name');
});

it('maps number type to NumberSchema', function () {
    $schema = SchemaMapper::toSchema('age', [
        'type' => 'number',
        'description' => 'An age',
    ]);

    expect($schema)->toBeInstanceOf(NumberSchema::class)
        ->and($schema->name())->toBe('age');
});

it('maps integer type to NumberSchema', function () {
    $schema = SchemaMapper::toSchema('count', [
        'type' => 'integer',
        'description' => 'A count',
    ]);

    expect($schema)->toBeInstanceOf(NumberSchema::class);
});

it('maps boolean type to BooleanSchema', function () {
    $schema = SchemaMapper::toSchema('active', [
        'type' => 'boolean',
        'description' => 'Is active',
    ]);

    expect($schema)->toBeInstanceOf(BooleanSchema::class)
        ->and($schema->name())->toBe('active');
});

it('maps array type to ArraySchema', function () {
    $schema = SchemaMapper::toSchema('tags', [
        'type' => 'array',
        'description' => 'List of tags',
        'items' => ['type' => 'string'],
    ]);

    expect($schema)->toBeInstanceOf(ArraySchema::class)
        ->and($schema->name())->toBe('tags');
});

it('maps object type to ObjectSchema', function () {
    $schema = SchemaMapper::toSchema('address', [
        'type' => 'object',
        'description' => 'An address',
        'properties' => [
            'street' => ['type' => 'string', 'description' => 'Street'],
            'city' => ['type' => 'string', 'description' => 'City'],
        ],
        'required' => ['street'],
    ]);

    expect($schema)->toBeInstanceOf(ObjectSchema::class)
        ->and($schema->name())->toBe('address');
});

it('maps enum schema from enum property', function () {
    $schema = SchemaMapper::toSchema('status', [
        'description' => 'Status',
        'enum' => ['active', 'inactive', 'pending'],
    ]);

    expect($schema)->toBeInstanceOf(EnumSchema::class)
        ->and($schema->name())->toBe('status');
});

it('throws on unsupported type', function () {
    SchemaMapper::toSchema('bad', ['type' => 'unsupported']);
})->throws(InvalidArgumentException::class, 'Unsupported JSON Schema type: unsupported');
