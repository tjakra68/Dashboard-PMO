<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('products.index'))->assertRedirect(route('login'));
    }

    public function test_products_index_is_displayed(): void
    {
        $products = Product::factory()->count(3)->create();

        $response = $this->actingAs($this->user)->get(route('products.index'));

        $response->assertOk();

        foreach ($products as $product) {
            $response->assertSee($product->name);
        }
    }

    public function test_product_can_be_created(): void
    {
        $data = [
            'name' => 'Laptop ThinkPad',
            'category' => 'Elektronik',
            'price' => 15000000,
            'stock' => 10,
            'description' => 'Laptop untuk kerja.',
        ];

        $response = $this->actingAs($this->user)->post(route('products.store'), $data);

        $response->assertRedirect(route('products.index'));
        $this->assertDatabaseHas('products', [
            'name' => 'Laptop ThinkPad',
            'category' => 'Elektronik',
            'stock' => 10,
        ]);
    }

    public function test_product_creation_requires_valid_data(): void
    {
        $response = $this->actingAs($this->user)
            ->from(route('products.create'))
            ->post(route('products.store'), [
                'name' => '',
                'category' => '',
                'price' => -5,
                'stock' => 'abc',
            ]);

        $response->assertRedirect(route('products.create'));
        $response->assertSessionHasErrors(['name', 'category', 'price', 'stock']);
        $this->assertDatabaseCount('products', 0);
    }

    public function test_product_can_be_viewed(): void
    {
        $product = Product::factory()->create();

        $response = $this->actingAs($this->user)->get(route('products.show', $product));

        $response->assertOk();
        $response->assertSee($product->name);
        $response->assertSee($product->category);
    }

    public function test_product_can_be_updated(): void
    {
        $product = Product::factory()->create();

        $response = $this->actingAs($this->user)->put(route('products.update', $product), [
            'name' => 'Nama Baru',
            'category' => 'Kategori Baru',
            'price' => 99999,
            'stock' => 5,
            'description' => 'Deskripsi baru.',
        ]);

        $response->assertRedirect(route('products.index'));
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Nama Baru',
            'category' => 'Kategori Baru',
            'stock' => 5,
        ]);
    }

    public function test_product_can_be_deleted(): void
    {
        $product = Product::factory()->create();

        $response = $this->actingAs($this->user)->delete(route('products.destroy', $product));

        $response->assertRedirect(route('products.index'));
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_products_can_be_searched_by_name(): void
    {
        Product::factory()->create(['name' => 'Kopi Arabika Gayo']);
        Product::factory()->create(['name' => 'Teh Hijau Melati']);

        $response = $this->actingAs($this->user)
            ->get(route('products.index', ['search' => 'Kopi']));

        $response->assertOk();
        $response->assertSee('Kopi Arabika Gayo');
        $response->assertDontSee('Teh Hijau Melati');
    }

    public function test_products_can_be_filtered_by_category(): void
    {
        Product::factory()->create(['name' => 'Produk Elektronik Satu', 'category' => 'Elektronik']);
        Product::factory()->create(['name' => 'Produk Makanan Satu', 'category' => 'Makanan']);

        $response = $this->actingAs($this->user)
            ->get(route('products.index', ['category' => 'Elektronik']));

        $response->assertOk();
        $response->assertSee('Produk Elektronik Satu');
        $response->assertDontSee('Produk Makanan Satu');
    }

    public function test_products_can_be_searched_by_name_and_category(): void
    {
        Product::factory()->create(['name' => 'Kopi Robusta', 'category' => 'Makanan']);
        Product::factory()->create(['name' => 'Kopi Arabika', 'category' => 'Minuman']);
        Product::factory()->create(['name' => 'Teh Hitam', 'category' => 'Minuman']);

        $response = $this->actingAs($this->user)
            ->get(route('products.index', ['search' => 'Kopi', 'category' => 'Minuman']));

        $response->assertOk();
        $response->assertSee('Kopi Arabika');
        $response->assertDontSee('Kopi Robusta');
        $response->assertDontSee('Teh Hitam');
    }
}
