<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant\Page;

use Domain\Page\Database\Factories\PageFactory;
use Illuminate\Database\Seeder;
use Support\MetaData\Database\Factories\MetaDataFactory;
use Support\RouteUrl\Database\Factories\RouteUrlFactory;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        PageFactory::new([
            'name' => 'Home',
            'visibility' => 'public',
            'published_at' => now(),
        ])
            ->has(RouteUrlFactory::new([
                'url' => '/',
            ]))
            ->has(MetaDataFactory::new([
                'keywords' => null,
                'author' => null,
                'description' => null,
            ]))
            ->published()
            ->create();
    }
}
