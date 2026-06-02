<?php

namespace App\Http\Middleware;

use App\Models\Project;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifySharePasscode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->route('token');
        if (!$token) {
            abort(404, 'Project not found.');
        }

        $project = Project::where('share_token', $token)->firstOrFail();

        // Auto-generate share_passcode if it is empty (for existing legacy projects)
        if (empty($project->share_passcode)) {
            $project->share_passcode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $project->save();
        }

        // Check if session has verified this project
        if (!session("project_share_verified_{$project->id}")) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated share session. Verification required.',
                ], 401);
            }

            return redirect()->route('projects.share.auth', $token);
        }

        return $next($request);
    }
}
