<?php

namespace App\Http\Controllers\Api\AxisResponseController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Organization;
use App\Models\Axis;
use App\Models\AxisResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AxisResponseController extends Controller
{
    // Get AxisResponse for an organization and axis
    public function show($orgId, $axisId)
    {
        $organization = Organization::findOrFail($orgId);

        // Authorization: user must own the organization
        if ($organization->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $axis = Axis::findOrFail($axisId);

        $response = AxisResponse::where('organization_id', $organization->id)
            ->where('axis_id', $axis->id)
            ->first();

        return response()->json($response);
    }

    // Create or update AxisResponse
    public function storeOrUpdate(Request $request, $orgId, $axisId)
    {
        $organization = Organization::findOrFail($orgId);

        // Authorization check
        if ($organization->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $axis = Axis::findOrFail($axisId);

        $validator = Validator::make($request->all(), [
            'q1' => 'nullable|boolean',
            'q2' => 'nullable|boolean',
            'q3' => 'nullable|boolean',
            'q4' => 'nullable|boolean',
            'attachment_1' => 'nullable|file|mimes:pdf,docx,jpg,png,xlsx|max:10240',
            'attachment_2' => 'nullable|file|mimes:pdf,docx,jpg,png,xlsx|max:10240',
            'attachment_3' => 'nullable|file|mimes:pdf,docx,jpg,png,xlsx|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        // Find or create record
        $axisResponse = AxisResponse::firstOrNew([
            'organization_id' => $organization->id,
            'axis_id' => $axis->id,
        ]);

        // Handle file uploads
        foreach (['attachment_1', 'attachment_2', 'attachment_3'] as $field) {
            if ($request->hasFile($field)) {
                // Delete old file if exists
                if ($axisResponse->$field && Storage::disk('public')->exists($axisResponse->$field)) {
                    Storage::disk('public')->delete($axisResponse->$field);
                }
                $path = $request->file($field)->store(
                    'axes_attachments/' . $organization->id . '/' . $axis->id,
                    'public'
                );
                $data[$field] = $path;
            }
        }

        $axisResponse->fill($data);
        $axisResponse->save();

        return response()->json([
            'message' => 'Axis response saved successfully',
            'axis_response' => $axisResponse,
        ], 200);
    }
}

