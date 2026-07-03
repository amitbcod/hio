<?php

namespace Tests\Feature;

use App\Models\StaticPage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StaticPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $databasePath = base_path('database/database.sqlite');
        if (!file_exists($databasePath)) {
            file_put_contents($databasePath, '');
        }

        config(['database.connections.sqlite.database' => $databasePath]);
        DB::purge('sqlite');

        if (!Schema::hasTable('static_pages')) {
            Schema::create('static_pages', function ($table) {
                $table->id();
                $table->string('title');
                $table->string('slug')->unique();
                $table->text('meta_description')->nullable();
                $table->longText('content_en')->nullable();
                $table->longText('content_fr')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function test_frontend_renders_static_page_in_selected_locale(): void
    {
        $page = StaticPage::create([
            'title' => 'About Holidays.io',
            'slug' => 'about-holidays-io',
            'meta_description' => 'A sample page',
            'content_en' => '<p>Welcome in English</p>',
            'content_fr' => '<p>Bienvenue en français</p>',
            'is_active' => true,
        ]);

        app()->setLocale('fr');

        $response = $this->get('/pages/' . $page->slug);

        $response->assertStatus(200);
        $response->assertSee('Bienvenue en français');
    }
}
