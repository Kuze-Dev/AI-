<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant\Page;

use Domain\Page\Database\Factories\PageFactory;
use Domain\Support\MetaData\Database\Factories\MetaDataFactory;
use Domain\Support\RouteUrl\Database\Factories\RouteUrlFactory;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        PageFactory::new([
            'name' => 'Home',
            'visibility' => 'public',
        ])
            ->has(RouteUrlFactory::new([
                'url' => '/',
            ]))
            ->has(MetaDataFactory::new([
                'title' => 'Home Page',
                'author' => 'System',
                'description' => 'This the home page of the application',
                'keywords' => 'Home page, home, index, front page',
            ]))
            ->published()
            ->create();
    }
}
