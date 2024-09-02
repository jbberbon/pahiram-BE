<?php

namespace Tests\Feature;

use Http;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\ItemGroupCategory;

class ItemCategorySearchTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Disable middleware for the test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Seed the database before each test
        $this->withoutMiddleware(); // Or specify a specific seeder class    

    }
    public function test_search_with_partial_category_name()
    {
        $this->withoutExceptionHandling();

        // Arrange: Create some item categories
        ItemGroupCategory::create(['category_name' => 'LAPTOP']);
        ItemGroupCategory::create(['category_name' => 'CAMERA']);
        ItemGroupCategory::create(['category_name' => 'SPEAKER']);

        // Act: Perform a search request with a partial category name
        $response = $this->getJson('/api/search-categories', ['category_name' => 'LAP']);

        // Assert: Verify the response
        $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'data' => [
                'categories' => [
                    [
                        'category_name'
                    ]
                ]
            ]
        ]);
    }
   
    public function test_search_with_exact_category_name()
    {

        $this->withoutExceptionHandling();

        ItemGroupCategory::create(['category_name' => 'LAPTOPS']);
        ItemGroupCategory::create(['category_name' => 'CAMERA']);
        ItemGroupCategory::create(['category_name' => 'SPEAKER']);

        // Act: Perform a search request with the exact category name
        $response = $this->getJson('/api/search-categories', ['category_name' => 'Speaker']);

        // Assert: Verify the response
        $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'data' => [
                'categories' => [
                    [
                        'category_name'
                    ]
                ]
            ]
        ]);
    }

    public function test_search_with_nonexistent_category_name()
    {

        $this->withoutExceptionHandling();

        ItemGroupCategory::create(['category_name' => 'Laptops']);
        ItemGroupCategory::create(['category_name' => 'Desktops']);
        ItemGroupCategory::create(['category_name' => 'Accessories']);

        // Act: Perform a search request with a non-existent category name
        $response = $this->getJson('/api/search-categories', ['category_name' => 'Smartphone']);

        // Assert: Verify the response
        $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'data' => [
                'categories' => [
                    [
                        'category_name'
                    ]
                ]
            ]
        ]);

    }
    public function test_search_with_invalid_category_name()
{
    $this->withoutExceptionHandling();

    // Seed the database with valid categories
    ItemGroupCategory::create(['category_name' => 'Laptops']);
    ItemGroupCategory::create(['category_name' => 'Desktops']);
    ItemGroupCategory::create(['category_name' => 'Accessories']);

    // Act: Perform a search request with an invalid category name (special characters)
    $response = $this->getJson('/api/search-categories', ['category_name' => '$@Smartphone']);

    // Assert: Verify the response
    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'data' => [
                'categories' => [
                    [
                        'category_name'
                    ]
                ]
            ]
        ]);

    // Assert: Verify that the response data does not contain any categories
    $response->assertJson([
        'status' => 'success',
        'data' => [
            'categories' => []
        ]
    ]);
}

    
}
