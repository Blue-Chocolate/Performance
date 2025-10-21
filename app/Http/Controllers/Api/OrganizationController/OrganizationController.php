<?php

namespace App\Http\Controllers\Api\OrganizationController;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrganizationController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sector' => 'nullable|string|max:255',
            'established_at' => 'nullable|date',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
        ]);

        $organization = Organization::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'sector' => $validated['sector'] ?? null,
            'established_at' => $validated['established_at'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
        ]);

        return response()->json([
            'message' => 'Organization created successfully.',
            'organization' => $organization
        ], 201);
    }

    public function index()
    {
        $orgs = Organization::where('user_id', Auth::id())->get();
        return response()->json($orgs);
    }

    public function show(Organization $organization)
    {
        // $this->authorize('view', $organization);
        return response()->json($organization);
    }

}
