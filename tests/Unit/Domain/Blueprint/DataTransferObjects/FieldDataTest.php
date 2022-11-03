<?php

declare(strict_types=1);

use Domain\Blueprint\DataTransferObjects\FieldData;
use Domain\Blueprint\Enums\FieldType;

it('can parse array to SchemaData', function (FieldType $type, array $data) {
    $fieldData = FieldData::fromArray($data);

    expect($fieldData)->toBeInstanceOf($type->getFieldDataClass());
})->with([
    'datetime field' => [
        'type' => FieldType::DATETIME,
        'data' => [
            'title' => 'Foo',
            'type' => 'datetime',
            'rules' => ['required'],
            'min' => '2022-11-02',
            'max' => '2022-12-02',
            'format' => 'Y/m/d',
        ],
    ],
    'file field' => [
        'type' => FieldType::FILE,
        'data' => [
            'title' => 'Foo',
            'type' => 'file',
            'rules' => ['required'],
            'multiple' => true,
            'reorder' => true,
            'accept' => ['image/*'],
            'min_size' => 1024,
            'max_size' => 2048,
            'min_files' => 1,
            'max_files' => 5,
        ],
    ],
    'markdown field' => [
        'type' => FieldType::MARKDOWN,
        'data' => [
            'title' => 'Foo',
            'type' => 'markdown',
            'rules' => ['required'],
            'buttons' => ['attachFiles', 'bold'],
        ],
    ],
    'richtext field' => [
        'type' => FieldType::RICHTEXT,
        'data' => [
            'title' => 'Foo',
            'type' => 'richtext',
            'rules' => ['required'],
            'buttons' => ['attachFiles', 'blockquote'],
        ],
    ],
    'select field' => [
        'type' => FieldType::SELECT,
        'data' => [
            'title' => 'Foo',
            'type' => 'select',
            'rules' => ['required'],
            'options' => ['foo', 'bar', 'baz'],
            'multiple' => true,
        ],
    ],
    'textarea field' => [
        'type' => FieldType::TEXTAREA,
        'data' => [
            'title' => 'Foo',
            'type' => 'textarea',
            'rules' => ['required'],
            'max_length' => 10,
            'min_length' => 500,
            'rows' => 8,
            'cols' => 256,
        ],
    ],
    'text field' => [
        'type' => FieldType::TEXT,
        'data' => [
            'title' => 'Foo',
            'type' => 'text',
            'rules' => ['required'],
            'min_length' => 0,
            'max_length' => 256,
        ],
    ],
    'email field' => [
        'type' => FieldType::EMAIL,
        'data' => [
            'title' => 'Foo',
            'type' => 'email',
            'rules' => ['required'],
            'min_length' => 0,
            'max_length' => 256,
        ],
    ],
    'number field' => [
        'type' => FieldType::NUMBER,
        'data' => [
            'title' => 'Foo',
            'type' => 'number',
            'rules' => ['required'],
            'min' => 0,
            'max' => 2,
            'step' => 0.1,
        ],
    ],
    'tel field' => [
        'type' => FieldType::TEL,
        'data' => [
            'title' => 'Foo',
            'type' => 'tel',
            'rules' => ['required'],
            'min_length' => 0,
            'max_length' => 256,
        ],
    ],
    'url field' => [
        'type' => FieldType::URL,
        'data' => [
            'title' => 'Foo',
            'type' => 'url',
            'rules' => ['required'],
            'min_length' => 0,
            'max_length' => 256,
        ],
    ],
    'password field' => [
        'type' => FieldType::PASSWORD,
        'data' => [
            'title' => 'Foo',
            'type' => 'password',
            'rules' => ['required'],
            'min_length' => 0,
            'max_length' => 256,
        ],
    ],
    'toggle field' => [
        'type' => FieldType::TOGGLE,
        'data' => [
            'title' => 'Foo',
            'type' => 'toggle',
            'rules' => ['required'],
        ],
    ],
]);
