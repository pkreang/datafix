<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Setting;
use App\Models\User;
use App\Support\BranchesSetting;
use App\Support\StructuredAddressValidation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CompanyController extends Controller
{
    // ─── Companies ───────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $query = Company::query();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $perPage = $request->integer('per_page', 15);
        $companies = $query->orderBy('name')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $companies,
        ]);
    }

    public function show(Company $company): JsonResponse
    {
        $company->load(['branches' => fn ($q) => $q->orderBy('code')]);

        return response()->json([
            'success' => true,
            'data' => $company,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $mode = Setting::get('company_mode', 'single');
        if ($mode === 'single' && Company::count() > 0) {
            return response()->json([
                'success' => false,
                'message' => __('company.single_mode_limit'),
            ], 422);
        }

        $validated = $request->validate(array_merge([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:companies,code',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'is_active' => 'boolean',
        ], StructuredAddressValidation::rules()));

        $validated['is_active'] = $request->boolean('is_active', true);

        $company = Company::create($validated);

        return response()->json([
            'success' => true,
            'data' => $company,
            'message' => __('company.company_created'),
        ], 201);
    }

    public function update(Request $request, Company $company): JsonResponse
    {
        $validated = $request->validate(array_merge([
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:50', Rule::unique('companies', 'code')->ignore($company->id)],
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'is_active' => 'boolean',
        ], StructuredAddressValidation::rules()));

        $validated['is_active'] = $request->boolean('is_active', true);

        $company->update($validated);

        return response()->json([
            'success' => true,
            'data' => $company,
            'message' => __('company.company_updated'),
        ]);
    }

    public function destroy(Company $company): JsonResponse
    {
        if ($company->branches()->exists()) {
            return response()->json([
                'success' => false,
                'message' => __('company.cannot_delete_has_branches'),
            ], 422);
        }

        if ($company->logo) {
            Storage::disk('public')->delete($company->logo);
        }

        $company->delete();

        return response()->json([
            'success' => true,
            'message' => __('company.company_deleted'),
        ]);
    }

    // ─── Branches (nested under company) ─────────────────────

    public function branchIndex(Company $company): JsonResponse
    {
        $branches = $company->branches()->orderBy('code')->get();

        return response()->json([
            'success' => true,
            'data' => $branches,
        ]);
    }

    public function branchStore(Request $request, Company $company): JsonResponse
    {
        if (! BranchesSetting::managementEnabled()) {
            return response()->json([
                'success' => false,
                'message' => __('company.branches_management_disabled'),
            ], 403);
        }

        $validated = $request->validate(array_merge([
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('branches', 'code')->where('company_id', $company->id),
            ],
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ], StructuredAddressValidation::rules()));

        $validated['is_active'] = $request->boolean('is_active', true);

        $branch = $company->branches()->create($validated);

        return response()->json([
            'success' => true,
            'data' => $branch,
            'message' => __('company.branch_created'),
        ], 201);
    }

    public function branchUpdate(Request $request, Company $company, Branch $branch): JsonResponse
    {
        if (! BranchesSetting::managementEnabled()) {
            return response()->json([
                'success' => false,
                'message' => __('company.branches_management_disabled'),
            ], 403);
        }

        if ($branch->company_id !== $company->id) {
            return response()->json([
                'success' => false,
                'message' => __('company.branch_not_belongs_company'),
            ], 404);
        }

        $validated = $request->validate(array_merge([
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('branches', 'code')
                    ->where(fn ($q) => $q->where('company_id', $company->id))
                    ->ignore($branch->id),
            ],
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ], StructuredAddressValidation::rules()));

        $validated['is_active'] = $request->boolean('is_active', true);

        $branch->update($validated);

        return response()->json([
            'success' => true,
            'data' => $branch,
            'message' => __('company.branch_updated'),
        ]);
    }

    public function branchDestroy(Company $company, Branch $branch): JsonResponse
    {
        if (! BranchesSetting::managementEnabled()) {
            return response()->json([
                'success' => false,
                'message' => __('company.branches_management_disabled'),
            ], 403);
        }

        if ($branch->company_id !== $company->id) {
            return response()->json([
                'success' => false,
                'message' => __('company.branch_not_belongs_company'),
            ], 404);
        }

        if (User::where('branch_id', $branch->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => __('company.cannot_delete_branch_has_users'),
            ], 422);
        }

        $branch->delete();

        return response()->json([
            'success' => true,
            'message' => __('company.branch_deleted'),
        ]);
    }
}
