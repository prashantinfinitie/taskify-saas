<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Models\Plan;
use Illuminate\Http\Request;
use App\Services\DeletionService;
use App\Http\Controllers\Controller;

class PlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $plans = Plan::all();
        return view('superadmin.plans.list', ['plans' => $plans]);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('superadmin.plans.create');
    }
    public function list()
    {
        $search = request('search');
        $sort = (request('sort')) ? request('sort') : "id";
        $order = (request('order')) ? request('order') : "DESC";
        $plans = Plan::orderBy($sort, $order);
        $status = request('status');
        if ($status) {
            $tags = $plans->where('status', $status);
        }
        $type = request('type');
        if ($type) {
            $tags = $plans->where('plan_type', $type);
        }
        if ($search) {
            $plans = $plans->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('id', 'like', '%' . $search . '%')
                    ->orWhere('plan_type', 'like', '%' . $search . '%')
                    ->orWhere('modules', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }
        $total = $plans->count();
        $plans = $plans->paginate(request("limit"));
        $plans = $plans->map(function ($plan) {
            $modules = json_decode($plan->modules);
            $moduleBadges = collect($modules)->map(function ($module) {
                return '<span class="badge bg-label-dark">' . $module . '</span>';
            })->implode(' ');
            $statusBadge = ($plan->status == 'active') ? '<span class="badge bg-success">
            ' . ucfirst($plan->status) . '
            </span>' : '<span class="badge bg-danger">' . ucfirst($plan->status) . '</span>';
            $planTypeBadge = ($plan->plan_type == 'free') ? '<span class="badge bg-success">' . ucfirst($plan->plan_type) . '</span>' : '<span class="badge bg-warning">' . ucfirst($plan->plan_type) . '</span>';
            return [
                'id' => $plan->id,
                'name' => ucfirst($plan->name),
                'description' => ucfirst($plan->description),
                'max_projects' => ($plan->max_projects == -1) ? get_label('unlimited', 'Unlimited') : $plan->max_projects,
                'max_clients' => ($plan->max_clients == -1) ? get_label('unlimited', 'Unlimited') : $plan->max_clients,
                'max_team_members' => ($plan->max_team_members == -1) ? get_label('unlimited', 'Unlimited') : $plan->max_team_members,
                'max_workspaces' => ($plan->max_worksapces == -1) ? get_label('unlimited', 'Unlimited') : $plan->max_worksapces,
                'plan_type' => $planTypeBadge,
                'monthly_price' => format_currency($plan->monthly_price),
                'monthly_discounted_price' => format_currency($plan->monthly_discounted_price),
                'yearly_price' => format_currency($plan->yearly_price),
                'yearly_discounted_price' => format_currency($plan->yearly_discounted_price),
                'lifetime_price' => format_currency($plan->lifetime_price),
                'lifetime_discounted_price' => format_currency($plan->lifetime_discounted_price),
                'status' => $statusBadge,
                'modules' => $moduleBadges, // Add the HTML badges for modules
            ];
        });
        return response()->json([
            "rows" => $plans,
            "total" => $total,
        ]);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'max_projects' => 'required|integer|min:-1',
            'max_clients' => 'required|integer|min:-1',
            'max_team_members' => 'required|integer|min:-1',
            'max_workspaces' => 'required|integer|min:-1',
            'modules' => 'required|string',
            'tenurePrices' => 'required|json',
            'discountedPrices' => 'nullable|json',
            'planType' => 'required|string|in:free,paid',
            'status' => 'required|string|in:active,inactive',
            'plan_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,avif|max:2048',
        ], [
            'name.required' => 'The plan name is required.',
            'name.max' => 'The plan name must not exceed 255 characters.',
            'description.required' => 'The plan description is required.',
            'description.max' => 'The plan description must not exceed 1000 characters.',
            'max_projects.required' => 'The maximum number of projects is required.',
            'max_projects.integer' => 'The maximum number of projects must be an integer.',
            'max_projects.min' => 'The maximum number of projects must be at least -1.',
            'max_clients.required' => 'The maximum number of clients is required.',
            'max_clients.integer' => 'The maximum number of clients must be an integer.',
            'max_clients.min' => 'The maximum number of clients must be at least -1.',
            'max_team_members.required' => 'The maximum number of team members is required.',
            'max_team_members.integer' => 'The maximum number of team members must be an integer.',
            'max_team_members.min' => 'The maximum number of team members must be at least -1.',
            'max_workspaces.required' => 'The maximum number of workspaces is required.',
            'max_workspaces.integer' => 'The maximum number of workspaces must be an integer.',
            'max_workspaces.min' => 'The maximum number of workspaces must be at least -1.',
            'modules.required' => 'The modules field is required.',
            'tenurePrices.required' => 'The tenure prices are required.',
            'tenurePrices.json' => 'The tenure prices must be a valid JSON string.',
            'discountedPrices.json' => 'The discounted prices must be a valid JSON string.',
            'planType.required' => 'The plan type is required.',
            'planType.in' => 'The plan type must be either "free" or "paid".',
            'status.required' => 'The status is required.',
            'status.in' => 'The status must be either "active" or "inactive".',
            'plan_image.image' => 'The plan image must be an image file.',
            'plan_image.mimes' => 'The plan image must be a file of type: jpeg, png, jpg, gif, svg, avif.',
            'plan_image.max' => 'The plan image must not be larger than 2048 kilobytes.',
        ]);

        if ($request->hasFile('plan_image')) {
            $imagePath = $request->file('plan_image')->store('plan_images', 'public');
            $validatedData['plan_image'] = $imagePath;
        }

        $plan = new Plan();
        $plan->name = $validatedData['name'];
        $plan->description = $validatedData['description'];
        $plan->max_projects = $validatedData['max_projects'];
        $plan->max_clients = $validatedData['max_clients'];
        $plan->max_team_members = $validatedData['max_team_members'];
        $plan->max_worksapces = $validatedData['max_workspaces'];
        $plan->modules = $validatedData['modules'];
        $plan->plan_type = $validatedData['planType'];
        $plan->status = $validatedData['status'];
        $plan->image = $validatedData['plan_image'] ?? null;

        $tenurePrices = json_decode($validatedData['tenurePrices'], true);
        $discountedPrices = json_decode($validatedData['discountedPrices'] ?? '[]', true);

        foreach (json_decode($validatedData['tenurePrices'], true) as $tenurePrice) {
            $plan->{$tenurePrice['tenure']} = $tenurePrice['price'];
        }
        foreach (json_decode($validatedData['discountedPrices'], true) as $discountedPrice) {
            $plan->{$discountedPrice['tenure']} = $discountedPrice['discountedPrice'];
        }

        if ($plan->save()) {
            return response()->json([
                'error' => false,
                'message' => 'Plan created successfully',
                'plan' => $plan,
                'redirect_url' => route('plans.index')
            ], 201);
        } else {
            return response()->json([
                'error' => true,
                'message' => 'An error occurred while saving the plan',
            ], 500);
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $plan = Plan::findOrFail($id);
        $plan->modules = json_decode($plan->modules);
        return view('superadmin.plans.update', ['plan' => $plan]);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {

        $validatedData = $request->validate([
            'name' => 'required|string',
            'description' => 'required|string',
            'max_projects' => 'required|integer|min:-1',
            'max_clients' => 'required|integer|min:-1',
            'max_team_members' => 'required|integer|min:-1',
            'max_workspaces' => 'required|integer|min:-1',
            'modules' => 'required|string',
            'tenurePrices' => 'required',
            'discountedPrices' => 'nullable',
            'plan_type' => 'required|string',
            'status' => 'required|string',
            'plan_image' => 'sometimes|nullable|mimes:jpeg,png,jpg,gif,svg,avif|max:2048',
        ]);

        if ($request->hasFile('plan_image')) {
            $imagePath = $request->file('plan_image')->store('plan_images', 'public');
            $validatedData['plan_image'] = $imagePath;
        } else {
            $validatedData['plan_image'] = null;
        }



        $plan = Plan::findOrFail($id);
        $plan->name = $validatedData['name'];
        $plan->description = $validatedData['description'];
        $plan->max_projects = $validatedData['max_projects'];
        $plan->max_clients = $validatedData['max_clients'];
        $plan->max_team_members = $validatedData['max_team_members'];
        $plan->max_worksapces = $validatedData['max_workspaces'];
        $plan->modules = $validatedData['modules'];
        if ($validatedData['plan_image']) {
            $plan->image = $validatedData['plan_image'];
        }
        $plan->plan_type = $validatedData['plan_type'];
        $plan->status = $validatedData['status'];
        foreach (json_decode($validatedData['tenurePrices'], true) as $tenurePrice) {
            $plan->{$tenurePrice['tenure']} = $tenurePrice['price'];
        }
        foreach (json_decode($validatedData['discountedPrices'], true) as $discountedPrice) {
            $plan->{$discountedPrice['tenure']} = $discountedPrice['discountedPrice'];
        }
        if ($plan->update()) {
            return response()->json(['redirect_url' => route('plans.index'),  'error' => false, 'message' => 'Plan Updated successfully', 'plan' => $plan], 201);
        } else {
            return response()->json(['error' => 'true', 'message' => 'Error Occured', 'plan' => $plan], 201);
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $response = DeletionService::delete(Plan::class, $id, 'Record');
        return $response;
    }
    public function destroy_multiple(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'ids' => 'required|array', // Ensure 'ids' is present and an array
            'ids.*' => 'integer|exists:activity_logs,id' // Ensure each ID in 'ids' is an integer and exists in the table
        ]);
        $ids = $validatedData['ids'];
        // Perform deletion using validated IDs
        foreach ($ids as $id) {
            DeletionService::delete(Plan::class, $id, 'Record');
        }
        return response()->json(['error' => false, 'message' => 'Record(s) deleted successfully.']);
    }
}
