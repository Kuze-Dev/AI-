<?php

return [
    /**
     * The list of models that could be linked to a blueprint's related resource field.
     *
     * array<class-string<\Illimuinate\Database\Eloquent\Model>, array{
     *      title_column: string,
     *      relation_scopes: array<string, array{
     *          title_column: string
     *      }>
     * }>
     */
    'related_resources' => [
        Domain\Collection\Models\Collection::class => [
            'title_column' => 'name'
        ],
        Domain\Collection\Models\CollectionEntry::class => [
            'title_column' => 'title',
            'relation_scopes' => [
                'collection' => [
                    'title_column' => 'name'
                ],
            ]
        ],
        Domain\Form\Models\Form::class => [
            'title_column' => 'name'
        ],
        Domain\Page\Models\Page::class => [
            'title_column' => 'name'
        ],
    ]
];
