<?php

namespace App\Http\Controllers;

use App\Models\Assembly;
use App\Models\Item;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AssemblyController extends Controller
{
    public function index(): View
    {
        $assemblies = Assembly::query()
            ->with(['assemblyItems.item'])
            ->withCount('assemblyItems')
            ->latest()
            ->paginate(15);

        return view('assemblies.index', compact('assemblies'));
    }

    public function create(): View
    {
        $items = Item::query()->orderBy('name')->get();

        return view('assemblies.create', compact('items'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:assemblies,name'],
            'unit' => ['required', 'string', 'max:50'],
            'items' => ['nullable', 'array'],
            'items.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($validated): void {
            $assembly = Assembly::query()->create([
                'name' => $validated['name'],
                'unit' => $validated['unit'],
            ]);

            $this->syncItems($assembly, $validated['items'] ?? []);
        });

        return redirect()
            ->route('assemblies.index')
            ->with('status', 'Assembly created.');
    }

    public function show(Assembly $assembly): RedirectResponse
    {
        return redirect()->route('assemblies.edit', $assembly);
    }

    public function edit(Assembly $assembly): View
    {
        $assembly->load('assemblyItems');
        $items = Item::query()->orderBy('name')->get();
        $selected = $assembly->assemblyItems->pluck('qty_per_unit', 'item_id');

        return view('assemblies.edit', compact('assembly', 'items', 'selected'));
    }

    public function update(Request $request, Assembly $assembly): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:assemblies,name,'.$assembly->id],
            'unit' => ['required', 'string', 'max:50'],
            'items' => ['nullable', 'array'],
            'items.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($assembly, $validated): void {
            $assembly->update([
                'name' => $validated['name'],
                'unit' => $validated['unit'],
            ]);

            $this->syncItems($assembly, $validated['items'] ?? []);
        });

        return redirect()
            ->route('assemblies.index')
            ->with('status', 'Assembly updated.');
    }

    public function destroy(Assembly $assembly): RedirectResponse
    {
        $assembly->delete();

        return redirect()
            ->route('assemblies.index')
            ->with('status', 'Assembly deleted.');
    }

    private function syncItems(Assembly $assembly, array $items): void
    {
        $assembly->assemblyItems()->delete();

        foreach ($items as $itemId => $qtyPerUnit) {
            $qty = (float) $qtyPerUnit;

            if ($qty <= 0) {
                continue;
            }

            $assembly->assemblyItems()->create([
                'item_id' => (int) $itemId,
                'qty_per_unit' => $qty,
            ]);
        }
    }
}
