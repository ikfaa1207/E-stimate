<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProjectCommentController extends Controller
{
    /**
     * Store a comment/revision request from an authenticated user.
     */
    public function store(Request $request, Project $project)
    {
        Gate::authorize('view', $project);

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:1000'],
            'project_task_id' => ['nullable', 'exists:project_tasks,id'],
            'estimate_id' => ['nullable', 'exists:estimates,id'],
            'type' => ['required', 'string', 'in:comment,revision_request'],
        ]);

        if ($validated['type'] === 'revision_request') {
            if ($validated['estimate_id'] ?? null) {
                $estimate = \App\Models\Estimate::find($validated['estimate_id']);
                if ($estimate && $estimate->isApproved()) {
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Revisions cannot be requested on an approved estimate proposal.'
                        ], 422);
                    }
                    return redirect()->back()->withErrors(['content' => 'Revisions cannot be requested on an approved estimate proposal.']);
                }
            }
            if ($validated['project_task_id'] ?? null) {
                $hasApprovedEstimate = $project->estimates()->where('status', 'approved')->exists();
                if ($hasApprovedEstimate) {
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Task revisions cannot be requested once the project estimate is approved.'
                        ], 422);
                    }
                    return redirect()->back()->withErrors(['content' => 'Task revisions cannot be requested once the project estimate is approved.']);
                }
            }
        }

        $comment = $project->comments()->create([
            'project_task_id' => $validated['project_task_id'] ?? null,
            'estimate_id' => $validated['estimate_id'] ?? null,
            'author_name' => auth()->user()->name,
            'author_role' => auth()->user()->role,
            'content' => $validated['content'],
            'type' => $validated['type'],
            'status' => $validated['type'] === 'revision_request' ? 'pending' : null,
        ]);

        if ($comment->isRevisionRequest() && $comment->estimate_id) {
            $estimate = \App\Models\Estimate::find($comment->estimate_id);
            if ($estimate && $estimate->status === 'locked') {
                $estimate->update(['status' => 'revision_pending']);
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'comment' => $comment,
                'message' => $comment->isRevisionRequest() 
                    ? 'Revision request submitted successfully.' 
                    : 'Comment posted successfully.',
            ]);
        }

        return redirect()->back()->with('status', 'Comment added successfully.');
    }

    /**
     * Store a comment/revision request from a guest client.
     */
    public function storeGuest(Request $request, string $token)
    {
        $project = Project::where('share_token', $token)->firstOrFail();

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:1000'],
            'author_name' => ['nullable', 'string', 'max:100'],
            'project_task_id' => ['nullable', 'exists:project_tasks,id'],
            'estimate_id' => ['nullable', 'exists:estimates,id'],
            'type' => ['required', 'string', 'in:comment,revision_request'],
        ]);

        $authorName = $validated['author_name'] ?: ($project->client_name ?: 'Guest Client');

        if ($validated['type'] === 'revision_request') {
            if ($validated['estimate_id'] ?? null) {
                $estimate = \App\Models\Estimate::find($validated['estimate_id']);
                if ($estimate && $estimate->isApproved()) {
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Revisions cannot be requested on an approved estimate proposal.'
                        ], 422);
                    }
                    return redirect()->back()->withErrors(['content' => 'Revisions cannot be requested on an approved estimate proposal.']);
                }
            }
            if ($validated['project_task_id'] ?? null) {
                $hasApprovedEstimate = $project->estimates()->where('status', 'approved')->exists();
                if ($hasApprovedEstimate) {
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Task revisions cannot be requested once the project estimate is approved.'
                        ], 422);
                    }
                    return redirect()->back()->withErrors(['content' => 'Task revisions cannot be requested once the project estimate is approved.']);
                }
            }
        }

        $comment = $project->comments()->create([
            'project_task_id' => $validated['project_task_id'] ?? null,
            'estimate_id' => $validated['estimate_id'] ?? null,
            'author_name' => $authorName,
            'author_role' => 'guest',
            'content' => $validated['content'],
            'type' => $validated['type'],
            'status' => $validated['type'] === 'revision_request' ? 'pending' : null,
        ]);

        if ($comment->isRevisionRequest() && $comment->estimate_id) {
            $estimate = \App\Models\Estimate::find($comment->estimate_id);
            if ($estimate && $estimate->status === 'locked') {
                $estimate->update(['status' => 'revision_pending']);
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'comment' => $comment,
                'message' => $comment->isRevisionRequest() 
                    ? 'Revision request submitted successfully.' 
                    : 'Comment posted successfully.',
            ]);
        }

        return redirect()->back()->with('status', 'Comment added successfully.');
    }

    /**
     * Resolve a pending revision request.
     */
    public function resolve(Request $request, Project $project, ProjectComment $comment)
    {
        Gate::authorize('update', $project);
        abort_unless((int) $comment->project_id === (int) $project->id, 404);
        abort_unless($comment->isRevisionRequest(), 400, 'Only revision requests can be resolved.');

        $comment->update(['status' => 'resolved']);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'comment' => $comment,
                'message' => 'Revision request marked as resolved.',
            ]);
        }

        return redirect()->back()->with('status', 'Revision request resolved.');
    }

    /**
     * Delete a comment.
     */
    public function destroy(Request $request, Project $project, ProjectComment $comment)
    {
        Gate::authorize('update', $project);
        abort_unless((int) $comment->project_id === (int) $project->id, 404);

        $comment->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Comment deleted successfully.',
            ]);
        }

        return redirect()->back()->with('status', 'Comment deleted.');
    }
}
