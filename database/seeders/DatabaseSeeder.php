<?php

namespace Database\Seeders;

use App\Models\Assembly;
use App\Models\AssemblyMapping;
use App\Models\Item;
use App\Models\Space;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User', 'password' => bcrypt('password')]
        );

        collect([
            [
                'name' => 'Bedroom',
                'default_area_sqm' => 12,
                'category' => 'bedroom',
            ],
            [
                'name' => 'Bathroom',
                'default_area_sqm' => 4,
                'category' => 'bathroom',
            ],
            [
                'name' => 'Kitchen',
                'default_area_sqm' => 10,
                'category' => 'kitchen',
            ],
            [
                'name' => 'Garage',
                'default_area_sqm' => 18,
                'category' => 'garage',
            ],
            [
                'name' => 'Living Room',
                'default_area_sqm' => 16,
                'category' => 'living_room',
            ],
        ])->each(function (array $space): void {
            Space::updateOrCreate(
                ['category' => $space['category']],
                $space + ['is_active' => true]
            );
        });

        $items = collect([
            [
                'name' => 'CHB',
                'type' => Item::TYPE_MATERIAL,
                'unit' => 'pcs',
                'unit_cost' => 20,
            ],
            [
                'name' => 'Cement',
                'type' => Item::TYPE_MATERIAL,
                'unit' => 'bag',
                'unit_cost' => 280,
            ],
            [
                'name' => 'Roofing Sheet',
                'type' => Item::TYPE_MATERIAL,
                'unit' => 'sqm',
                'unit_cost' => 650,
            ],
            [
                'name' => 'Masonry Labor',
                'type' => Item::TYPE_LABOR,
                'unit' => 'hour',
                'unit_cost' => 120,
            ],
            [
                'name' => 'Concrete Labor',
                'type' => Item::TYPE_LABOR,
                'unit' => 'hour',
                'unit_cost' => 130,
            ],
            [
                'name' => 'Scaffold Rental',
                'type' => Item::TYPE_EQUIPMENT,
                'unit' => 'day',
                'unit_cost' => 900,
            ],
        ])->mapWithKeys(function (array $item): array {
            $record = Item::updateOrCreate(['name' => $item['name']], $item);

            return [$item['name'] => $record];
        });

        $assemblies = collect([
            [
                'name' => 'CHB Wall',
                'unit' => 'sqm',
                'items' => [
                    ['name' => 'CHB', 'qty_per_unit' => 12],
                    ['name' => 'Cement', 'qty_per_unit' => 0.35],
                    ['name' => 'Masonry Labor', 'qty_per_unit' => 0.6],
                    ['name' => 'Scaffold Rental', 'qty_per_unit' => 0.03],
                ],
            ],
            [
                'name' => 'Roofing',
                'unit' => 'sqm',
                'items' => [
                    ['name' => 'Roofing Sheet', 'qty_per_unit' => 1.05],
                    ['name' => 'Masonry Labor', 'qty_per_unit' => 0.25],
                ],
            ],
            [
                'name' => 'Concrete Slab',
                'unit' => 'sqm',
                'items' => [
                    ['name' => 'Cement', 'qty_per_unit' => 0.5],
                    ['name' => 'Concrete Labor', 'qty_per_unit' => 0.35],
                ],
            ],
        ])->mapWithKeys(function (array $assemblyData) use ($items): array {
            $assembly = Assembly::updateOrCreate(
                ['name' => $assemblyData['name']],
                ['unit' => $assemblyData['unit']]
            );

            $assembly->assemblyItems()->delete();

            foreach ($assemblyData['items'] as $itemData) {
                $item = $items->get($itemData['name']);

                if (! $item) {
                    continue;
                }

                $assembly->assemblyItems()->create([
                    'item_id' => $item->id,
                    'qty_per_unit' => $itemData['qty_per_unit'],
                ]);
            }

            return [$assemblyData['name'] => $assembly];
        });

        collect([
            'wall_area' => 'CHB Wall',
            'roof_area' => 'Roofing',
            'slab_area' => 'Concrete Slab',
        ])->each(function (string $assemblyName, string $metricName) use ($assemblies): void {
            $assembly = $assemblies->get($assemblyName);

            if (! $assembly) {
                return;
            }

            AssemblyMapping::updateOrCreate(
                ['metric_name' => $metricName],
                ['assembly_id' => $assembly->id]
            );
        });
    }
}
