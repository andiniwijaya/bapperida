<?php

namespace App\Http\Controllers\Api\Registration;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\RegistrationRequest\FilterRegistrationRequest;
use App\Http\Resources\RegistrationRequestResource;
use App\Models\RegistrationRequest;
use App\Services\Registration\ApproveRegistrationService;
use App\Services\Registration\RejectRegistrationService;
use App\Http\Requests\Api\RegistrationRequest\RejectRegistrationRequest;
use App\Support\ListOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class RegistrationApprovalController extends ApiController
{
    public function __construct(
        protected ApproveRegistrationService $approveService,
        protected RejectRegistrationService $rejectService,
    ) {
    }

    /**
     * Menampilkan seluruh permintaan registrasi.
     */
    public function index(FilterRegistrationRequest $request): JsonResponse
    {
        $this->authorize('viewAny', RegistrationRequest::class);

        $query = RegistrationRequest::query()->with('user');

        $query = ListOrder::apply($query, $request->input('order'), 'created_at');

        $requests = $query->paginate($request->integer('per_page', 15));

        return $this->success([
            'data' => RegistrationRequestResource::collection($requests),
            'meta' => [
                'current_page' => $requests->currentPage(),
                'last_page' => $requests->lastPage(),
                'per_page' => $requests->perPage(),
                'total' => $requests->total(),
            ],
        ], 'Data registrasi berhasil diambil.');
    }

    /**
     * Approve registrasi.
     */
    public function approve(
        RegistrationRequest $registrationRequest
    ): JsonResponse {

        $this->authorize('approve', $registrationRequest);

        $this->approveService->handle(
            $registrationRequest,
            Auth::id()
        );

        return $this->success(
            null,
            'Registrasi berhasil disetujui.'
        );
    }

    /**
     * Reject registrasi.
     */
    public function reject(
        RejectRegistrationRequest $request,
        RegistrationRequest $registrationRequest
    ): JsonResponse {

        $this->authorize('reject', $registrationRequest);

        $this->rejectService->handle(
            $registrationRequest,
            Auth::id(),
            $request->validated()['rejection_reason']
        );

        return $this->success(
            null,
            'Registrasi berhasil ditolak.'
        );
    }
}
