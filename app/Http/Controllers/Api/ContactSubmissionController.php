<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactSubmissionRequest;
use App\Http\Resources\ContactSubmissionResource;
use App\Services\ContactSubmissionService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ContactSubmissionController extends Controller
{
    public function __construct(
        private readonly ContactSubmissionService $service,
    ) {}

    public function __invoke(
        StoreContactSubmissionRequest $request,
    ): JsonResponse {
        $submission = $this->service->submit(
            data: $request->toData(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        $response = (new ContactSubmissionResource($submission))
            ->additional([
                'success' => true,
                'message' => 'Contact submission accepted.',
            ])
            ->response();

        $response->setStatusCode(Response::HTTP_CREATED);

        return $response;
    }
}
