<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant\Page;

use Domain\Page\Database\Factories\BlockFactory;
use Domain\Page\Database\Factories\PageFactory;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        PageFactory::new([
            'name' => 'Home Page',
            'visibility' => 'public',
        ])
            ->addBlockContent(BlockFactory::new()->withDummyBlueprint())
            ->addRouteUrl([
                'url' => '/',
            ])
            ->addMetaData([
                'title' => 'Home Page',
                'author' => 'System',
                'description' => 'This the home page of the application',
                'keywords' => 'Home page, home, index, front page',
            ])
            // ->bypassFactoryCallback(true)
            ->published()
            ->create();
    }
}
