<?php

namespace Tests\Feature;

use App\Models\Assembly;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssemblyCostTest extends TestCase
{
    use RefreshDatabase;

    public function test_assembly_calculates_base_unit_cost_correctly(): void
    {
        $item1 = Item::query()->create([
            'name' => 'Item A',
            'type' => Item::TYPE_MATERIAL,
            'unit' => 'pcs',
            'unit_cost' => 10.00,
        ]);

        $item2 = Item::query()->create([
            'name' => 'Item B',
            'type' => Item::TYPE_LABOR,
            'unit' => 'hour',
            'unit_cost' => 25.00,
        ]);

        $assembly = Assembly::query()->create([
            'name' => 'Test Assembly',
            'unit' => 'sqm',
        ]);

        $assembly->assemblyItems()->create([
            'item_id' => $item1->id,
            'qty_per_unit' => 3.5, // 3.5 * 10.00 = 35.00
        ]);

        $assembly->assemblyItems()->create([
            'item_id' => $item2->id,
            'qty_per_unit' => 2.0, // 2.0 * 25.00 = 50.00
        ]);

        $this->assertEquals(85.00, $assembly->base_unit_cost);
    }

    public function test_index_view_shows_base_unit_cost(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $item = Item::query()->create([
            'name' => 'Cement Bag',
            'type' => Item::TYPE_MATERIAL,
            'unit' => 'bag',
            'unit_cost' => 100.00,
        ]);

        $assembly = Assembly::query()->create([
            'name' => 'Concrete Mix',
            'unit' => 'cum',
        ]);

        $assembly->assemblyItems()->create([
            'item_id' => $item->id,
            'qty_per_unit' => 5.0, // 5 * 100 = 500.00
        ]);

        $response = $this
            ->actingAs($admin)
            ->get(route('assemblies.index'));

        $response->assertStatus(200);
        $response->assertSee('Concrete Mix');
        $response->assertSee('500.00');
    }
}
