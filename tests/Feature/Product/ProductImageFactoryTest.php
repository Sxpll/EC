<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use App\Models\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductImageFactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_product_image()
    {
        $image = ProductImage::factory()->create(); // Tworzy ProductImage z powiązanym Product

        $this->assertDatabaseHas('product_images', [
            'id' => $image->id,
            'product_id' => $image->product_id, // Upewnij się, że product_id nie jest nullem
        ]);
    }
}
