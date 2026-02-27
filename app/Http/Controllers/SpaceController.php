<?php

namespace App\Http\Controllers;

use App\Models\Space;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SpaceController extends Controller
{
    public function index(): View
    {
        $spaces = Space::query()
            ->orderBy('name')
            ->paginate(15);

        return view('spaces.index', compact('spaces'));
    }

    public function create(): View
    {
        return view('spaces.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:spaces,name'],
            'default_area_sqm' => ['required', 'numeric', 'min:0'],
            'category' => ['required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = (bool) ($validated['is_active'] ?? false);

        Space::query()->create($validated);

        return redirect()
            ->route('spaces.index')
            ->with('status', 'Space standard created.');
    }

    public function show(Space $space): RedirectResponse
    {
        return redirect()->route('spaces.edit', $space);
    }

    public function edit(Space $space): View
    {
        return view('spaces.edit', compact('space'));
    }

    public function update(Request $request, Space $space): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:spaces,name,'.$space->id],
            'default_area_sqm' => ['required', 'numeric', 'min:0'],
            'category' => ['required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = (bool) ($validated['is_active'] ?? false);

        $space->update($validated);

        return redirect()
            ->route('spaces.index')
            ->with('status', 'Space standard updated.');
    }

    public function destroy(Space $space): RedirectResponse
    {
        $space->delete();

        return redirect()
            ->route('spaces.index')
            ->with('status', 'Space standard deleted.');
    }
}
