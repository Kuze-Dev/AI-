<?php

return [
    'relations' => [
        'blocks' => Domain\Page\Models\Block::class,
        'contents' => Domain\Content\Models\Content::class,
        'forms' => Domain\Form\Models\Form::class,
        'globals' => Domain\Globals\Models\Globals::class,
        'taxonomies' => Domain\Taxonomy\Models\Taxonomy::class,
    ],

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
        Domain\Content\Models\Content::class => [
            'title_column' => 'name'
        ],
        Domain\Content\Models\ContentEntry::class => [
            'title_column' => 'title',
            'relation_scopes' => [
                'content' => [
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
