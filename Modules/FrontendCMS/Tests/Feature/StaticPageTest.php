<?php

namespace Modules\FrontendCMS\Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\FrontendCMS\Entities\DynamicPage;

class StaticPageTest extends TestCase
{
    use DatabaseTransactions;
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_for_status_change()
    {
        $this->actingAs(User::find(1));

        $this->post('/frontendcms/dynamic-page/store',[
            'title' => 'test title',
            'slug' => 'test-title',
            'description' => 'test description',
            'status' => 0
        ]);
        $page = DynamicPage::orderBy('id','desc')->first();

        $this->post('/frontendcms/dynamic-page/status-update',[
            'status' => 1,
            'id' => $page->id,
        ])->assertStatus(200);
    }

    public function test_for_delete_static_page()
    {
        $this->actingAs(User::find(1));

        $this->post('/frontendcms/dynamic-page/store',[
            'title' => 'test title',
            'slug' => 'test-title',
            'description' => 'test description',
            'status' => 1
        ]);

        $page = DynamicPage::orderBy('id','desc')->first();

        $this->post('/frontendcms/dynamic-page/delete',[
            'id' => $page->id,
        ])->assertStatus(200);
    }

}
