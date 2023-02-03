<?php

return [
    'related_resources' => [
        /**
         * The list of models that could be linked to a blueprint's relate resource field.
         */
        'models' => [
            Domain\Collection\Models\Collection::class,
            Domain\Collection\Models\CollectionEntry::class,
            Domain\Form\Models\Form::class,
            Domain\Page\Models\Page::class,
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
