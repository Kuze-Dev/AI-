<?php

return [
    'related_resources' => [
        /**
         * The list of models that could be linked to a blueprint's relate resource field.
         */
        'models' => [
            Domain\Collection\Models\Collection::class => [
                'title_column' => 'name'
            ],
            Domain\Collection\Models\CollectionEntry::class => [
                'title_column' => 'title'
            ],
            Domain\Form\Models\Form::class => [
                'title_column' => 'name'
            ],
            Domain\Page\Models\Page::class => [
                'title_column' => 'name'
            ],
        ],

        /**
         * Relation scopes for the models.
         */
        'relation_scopes' => [
            Domain\Collection\Models\CollectionEntry::class => [
                'collection' => [
                    'title_column' => 'name'
                ],
            ]
        ]
    ]
];
