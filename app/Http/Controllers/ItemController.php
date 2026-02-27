<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ItemController extends Controller
{
    public function index(): View
    {
        $items = Item::query()->latest()->paginate(15);

        return view('items.index', compact('items'));
    }

    public function create(): View
    {
        return view('items.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:'.implode(',', Item::TYPES)],
            'unit' => ['required', 'string', 'max:50'],
            'unit_cost' => ['required', 'numeric', 'min:0'],
        ]);

        Item::query()->create($validated);

        return redirect()
            ->route('items.index')
            ->with('status', 'Item created.');
    }

    public function show(Item $item): RedirectResponse
    {
        return redirect()->route('items.edit', $item);
    }

    public function edit(Item $item): View
    {
        return view('items.edit', compact('item'));
    }

    public function update(Request $request, Item $item): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:'.implode(',', Item::TYPES)],
            'unit' => ['required', 'string', 'max:50'],
            'unit_cost' => ['required', 'numeric', 'min:0'],
        ]);

        $item->update($validated);

        return redirect()
            ->route('items.index')
            ->with('status', 'Item updated.');
    }

    public function destroy(Item $item): RedirectResponse
    {
        $item->delete();

        return redirect()
            ->route('items.index')
            ->with('status', 'Item deleted.');
    }
}
