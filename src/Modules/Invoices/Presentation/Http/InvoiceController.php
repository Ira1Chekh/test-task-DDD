<?php

namespace Modules\Invoices\Presentation\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Invoices\Application\Services\InvoiceService;
use Modules\Invoices\Presentation\Requests\CreateInvoiceRequest;

class InvoiceController extends Controller
{
    public function __construct(
        private InvoiceService $invoiceService
    ) {}

    public function show(string $id): JsonResponse
    {
        $invoice = $this->invoiceService->getInvoice($id);

        if (!$invoice) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        return response()->json($invoice);
    }

    public function store(CreateInvoiceRequest $request): JsonResponse
    {
        $requestDto = $request->toDTO();
        $invoice = $this->invoiceService->createInvoice($requestDto);

        return response()->json($invoice, 201);
    }

    public function send(string $id): JsonResponse
    {
        try {
            $invoice = $this->invoiceService->sendInvoice($id);
            return response()->json($invoice);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
