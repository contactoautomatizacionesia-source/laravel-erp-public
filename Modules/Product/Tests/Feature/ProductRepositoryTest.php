<?php

namespace Modules\Product\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Product\Entities\Brand;
use Modules\Product\Entities\Category;
use Modules\Product\Entities\Product;
use Tests\TestCase;

class ProductRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Verifica que getByAjax retorna productos que coinciden con el término de búsqueda.
     */
    public function test_get_by_ajax_returns_matching_products()
    {
        $user = User::find(1);
        $this->actingAs($user);

        $response = $this->get('/products/get-by-ajax?search=a');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => ['id', 'text']
        ]);
    }

    /**
     * Verifica que getByAjax sin término de búsqueda retorna todos los productos aprobados.
     */
    public function test_get_by_ajax_returns_all_products_when_search_is_empty()
    {
        $user = User::find(1);
        $this->actingAs($user);

        $response = $this->get('/products/get-by-ajax?search=');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => ['id', 'text']
        ]);
    }

    /**
     * Verifica que getByAjax retorna array vacío cuando no hay coincidencias.
     */
    public function test_get_by_ajax_returns_empty_when_no_match()
    {
        $user = User::find(1);
        $this->actingAs($user);

        $response = $this->get('/products/get-by-ajax?search=xxxxxxxxxxx_no_existe_99999');

        $response->assertStatus(200);
        $response->assertExactJson([]);
    }
}
