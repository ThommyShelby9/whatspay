<?php

namespace App\Http\Middleware;

use App\Services\TaskAssignmentService;
use Closure;
use Illuminate\Http\Request;

class SubmissionWindowCheck
{
    protected $assignmentService;
    
    public function __construct(TaskAssignmentService $assignmentService)
    {
        $this->assignmentService = $assignmentService;
    }
    
    /**
     * Vérifie si la requête est dans la fenêtre de soumission (11h-12h)
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$this->assignmentService->isInSubmissionWindow()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => true,
                    'message' => 'Les soumissions ne sont acceptées qu\'entre 11h et 12h.'
                ], 403);
            }
            
            return redirect()->back()->with('type', 'danger')
                ->with('message', 'Les soumissions ne sont acceptées qu\'entre 11h et 12h.');
        }
        
        return $next($request);
    }
}