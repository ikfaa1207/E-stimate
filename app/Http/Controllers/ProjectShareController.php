<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectShareController extends Controller
{
    /**
     * Display the shared guest dashboard for the project.
     */
    public function show(string $token): View
    {
        $project = Project::where('share_token', $token)->firstOrFail();

        $project->load([
            'compliances',
            'phases.tasks.comments',
            'comments',
            'estimates' => fn ($query) => $query
                ->with(['lines', 'breakdowns', 'adjustments', 'comments'])
                ->latest('generated_at'),
        ]);

        $latestEstimate = $project->estimates->first();

        return view('projects.share', compact('project', 'latestEstimate'));
    }

    /**
     * Allow guest clients to approve the latest proposed/locked estimate.
     */
    public function approve(string $token): RedirectResponse
    {
        $project = Project::where('share_token', $token)->firstOrFail();
        $latestEstimate = $project->estimates()->latest('generated_at')->first();

        abort_unless($latestEstimate, 404, 'No estimate found for this project.');
        abort_unless($latestEstimate->isLocked(), 400, 'Only proposed estimates can be approved.');

        $latestEstimate->update(['status' => 'approved']);

        return redirect()
            ->route('projects.share.show', $token)
            ->with('status', 'Estimate proposal approved successfully!');
    }

    /**
     * Show the passcode verification screen for guest share access.
     */
    public function showAuth(string $token): View
    {
        $project = Project::where('share_token', $token)->firstOrFail();

        return view('projects.share-auth', compact('project'));
    }

    /**
     * Verify the passcode for guest share access.
     */
    public function verifyAuth(Request $request, string $token): RedirectResponse
    {
        $project = Project::where('share_token', $token)->firstOrFail();

        $validated = $request->validate([
            'passcode' => ['required', 'string', 'size:6'],
        ]);

        if ($validated['passcode'] === $project->share_passcode) {
            session(["project_share_verified_{$project->id}" => true]);

            return redirect()->route('projects.share.show', $token);
        }

        return redirect()->back()->withErrors([
            'passcode' => 'The passcode you entered is incorrect.',
        ]);
    }
}
