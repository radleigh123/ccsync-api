<?php

namespace App\Http\Controllers\Requirement;

use App\Helper\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\Requirement\ComplianceAuditResource;
use App\Models\Compliance;
use App\Models\ComplianceAudit;
use Illuminate\Http\Request;

class ComplianceAuditController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of compliance audits with filtering
     * 
     * Query Parameters:
     * - requirement_id: Filter by requirement ID (via offering)
     * - member_id: Filter by member ID
     * - compliance_id: Filter by compliance ID
     */
    public function index(Request $request)
    {
        try {
            $query = ComplianceAudit::query();

            // Filter by requirement_id if provided
            if ($request->has('requirement_id')) {
                $requirementId = $request->input('requirement_id');
                $query->whereHas('compliance.offering', function ($q) use ($requirementId) {
                    $q->where('requirement_id', $requirementId);
                });
            }

            // Filter by member_id if provided
            if ($request->has('member_id')) {
                $memberId = $request->input('member_id');
                $query->whereHas('compliance', function ($q) use ($memberId) {
                    $q->where('member_id', $memberId);
                });
            }

            // Filter by compliance_id if provided
            if ($request->has('compliance_id')) {
                $query->where('compliance_id', $request->input('compliance_id'));
            }

            // Get the results
            $audits = $query->with(['compliance', 'member'])->get();

            return $this->success(
                ComplianceAuditResource::collection($audits),
                200,
                'Successfully retrieved compliance audits.'
            );
        } catch (\Exception $e) {
            return $this->error(
                message: $e->getMessage(),
                code: 500
            );
        }
    }

    /**
     * Display the specified compliance audit
     */
    public function show(string $id)
    {
        try {
            $audit = ComplianceAudit::with(['compliance', 'member'])->findOrFail($id);

            return $this->success(
                new ComplianceAuditResource($audit),
                200,
                'Successfully retrieved compliance audit.'
            );
        } catch (\Exception $e) {
            return $this->error(
                message: $e->getMessage(),
                code: 404
            );
        }
    }
}
