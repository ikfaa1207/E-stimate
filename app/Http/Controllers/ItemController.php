<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ItemController extends Controller
{
    public function index(Request $request): View
    {
        $query = Item::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%');
        }

        if ($request->filled('type') && in_array($request->input('type'), Item::TYPES)) {
            $query->where('type', $request->input('type'));
        }

        $items = $query->latest()->paginate(15)->withQueryString();

        $filters = [
            'search' => $request->input('search'),
            'type' => $request->input('type'),
        ];

        return view('items.index', compact('items', 'filters'));
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

    public function bulkUpdate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['nullable', 'string', 'in:' . implode(',', Item::TYPES)],
            'adjustment_type' => ['required', 'string', 'in:percentage,fixed'],
            'direction' => ['required', 'string', 'in:increase,decrease'],
            'amount' => ['required', 'numeric', 'min:0'],
        ]);

        $query = Item::query();
        if ($validated['type']) {
            $query->where('type', $validated['type']);
        }

        $amount = (float) $validated['amount'];
        $isIncrease = $validated['direction'] === 'increase';

        \Illuminate\Support\Facades\DB::transaction(function () use ($query, $validated, $amount, $isIncrease) {
            if ($validated['adjustment_type'] === 'percentage') {
                $multiplier = $isIncrease ? (1 + $amount / 100) : (1 - $amount / 100);
                
                if ($multiplier < 0) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'amount' => 'A percentage decrease cannot exceed 100%.',
                    ]);
                }

                $query->update([
                    'unit_cost' => \Illuminate\Support\Facades\DB::raw('unit_cost * ' . $multiplier)
                ]);
            } else {
                $difference = $isIncrease ? $amount : -$amount;

                $query->update([
                    'unit_cost' => \Illuminate\Support\Facades\DB::raw(
                        'CASE WHEN (unit_cost + ' . $difference . ') < 0 THEN 0 ELSE (unit_cost + ' . $difference . ') END'
                    )
                ]);
            }
        });

        return redirect()
            ->route('items.index')
            ->with('status', 'Bulk cost update applied successfully.');
    }
}
