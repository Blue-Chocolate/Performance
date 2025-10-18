<?php


namespace App\Http\Controllers\Api\OrganizationController;


use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\Request;


class OrganizationController extends Controller
{
public function store(Request $req)
{
$data = $req->validate([
'name' => 'required|string',
'sector' => 'nullable|string',
'established_at' => 'nullable|date',
'email' => 'nullable|email',
'phone' => 'nullable|string',
'address' => 'nullable|string',
]);


$data['user_id'] = $req->user()->id;
$org = Organization::create($data);
return response()->json($org, 201);
}


public function show(Organization $org)
{
$this->authorize('view', $org);
return response()->json($org);
}


public function score(Organization $org)
{
$this->authorize('view', $org);
return response()->json(['final_score' => $org->final_score]);
}
}