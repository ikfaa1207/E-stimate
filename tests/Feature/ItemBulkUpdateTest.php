<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemBulkUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_page_filters_items_by_name_and_type(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        Item::query()->create([
            'name' => 'Cement Ordinary',
            'type' => Item::TYPE_MATERIAL,
            'unit' => 'bag',
            'unit_cost' => 100.00,
        ]);

        Item::query()->create([
            'name' => 'Steel Bar',
            'type' => Item::TYPE_MATERIAL,
            'unit' => 'pcs',
            'unit_cost' => 150.00,
        ]);

        Item::query()->create([
            'name' => 'Masonry Labor Hour',
            'type' => Item::TYPE_LABOR,
            'unit' => 'hour',
            'unit_cost' => 50.00,
        ]);

        // 1. Test search by name
        $response = $this->actingAs($admin)->get(route('items.index', ['search' => 'Steel']));
        $response->assertStatus(200);
        $response->assertSee('Steel Bar');
        $response->assertDontSee('Cement Ordinary');
        $response->assertDontSee('Masonry Labor Hour');

        // 2. Test filter by type
        $response = $this->actingAs($admin)->get(route('items.index', ['type' => 'labor']));
        $response->assertStatus(200);
        $response->assertSee('Masonry Labor Hour');
        $response->assertDontSee('Steel Bar');
        $response->assertDontSee('Cement Ordinary');
    }

    public function test_non_admins_cannot_perform_bulk_cost_updates(): void
    {
        $estimator = User::factory()->create(['role' => User::ROLE_ESTIMATOR]);

        $response = $this->actingAs($estimator)->post(route('items.bulk-update'), [
            'type' => 'material',
            'adjustment_type' => 'percentage',
            'direction' => 'increase',
            'amount' => 10,
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_bulk_update_costs_by_percentage(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $item1 = Item::query()->create([
            'name' => 'CHB 4',
            'type' => Item::TYPE_MATERIAL,
            'unit' => 'pcs',
            'unit_cost' => 10.00,
        ]);

        $item2 = Item::query()->create([
            'name' => 'Scaffold Rent',
            'type' => Item::TYPE_EQUIPMENT,
            'unit' => 'day',
            'unit_cost' => 100.00,
        ]);

        // Bulk update: increase Material costs by 15%
        $response = $this->actingAs($admin)->post(route('items.bulk-update'), [
            'type' => 'material',
            'adjustment_type' => 'percentage',
            'direction' => 'increase',
            'amount' => 15, // 10.00 * 1.15 = 11.50
        ]);

        $response->assertRedirect(route('items.index'));
        $this->assertEquals(11.50, (float) $item1->refresh()->unit_cost);
        $this->assertEquals(100.00, (float) $item2->refresh()->unit_cost); // Equipment not updated
    }

    public function test_admin_can_bulk_update_costs_by_fixed_amount(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $item1 = Item::query()->create([
            'name' => 'Concrete Labor',
            'type' => Item::TYPE_LABOR,
            'unit' => 'hour',
            'unit_cost' => 30.00,
        ]);

        $item2 = Item::query()->create([
            'name' => 'Cement Bag',
            'type' => Item::TYPE_MATERIAL,
            'unit' => 'bag',
            'unit_cost' => 250.00,
        ]);

        // Bulk update: decrease Labor costs by $5.00
        $response = $this->actingAs($admin)->post(route('items.bulk-update'), [
            'type' => 'labor',
            'adjustment_type' => 'fixed',
            'direction' => 'decrease',
            'amount' => 5, // 30 - 5 = 25
        ]);

        $response->assertRedirect(route('items.index'));
        $this->assertEquals(25.00, (float) $item1->refresh()->unit_cost);
        $this->assertEquals(250.00, (float) $item2->refresh()->unit_cost); // Material not updated
    }

    public function test_bulk_update_does_not_set_negative_unit_costs(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $item = Item::query()->create([
            'name' => 'CHB 4',
            'type' => Item::TYPE_MATERIAL,
            'unit' => 'pcs',
            'unit_cost' => 10.00,
        ]);

        // Try decreasing cost by 15.00 (which is more than current 10.00 cost)
        $this->actingAs($admin)->post(route('items.bulk-update'), [
            'type' => 'material',
            'adjustment_type' => 'fixed',
            'direction' => 'decrease',
            'amount' => 15,
        ]);

        // Should clamp to 0.00
        $this->assertEquals(0.00, (float) $item->refresh()->unit_cost);
    }
}
