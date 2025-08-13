<?php

namespace Tests\Unit\Invoices\Services;

use Modules\Invoices\Application\DTOs\CreateInvoiceDTO;
use Modules\Invoices\Application\DTOs\InvoiceDTO;
use Modules\Invoices\Application\DTOs\InvoiceProductLineDTO;
use Modules\Invoices\Application\Services\InvoiceService;
use Modules\Invoices\Domain\Entities\Invoice;
use Modules\Invoices\Domain\Entities\InvoiceProductLine;
use Modules\Invoices\Domain\Repositories\InvoiceRepositoryInterface;
use Modules\Invoices\Domain\ValueObjects\Status;
use Modules\Invoices\Domain\ValueObjects\UuidId;
use Modules\Invoices\Infrastructure\DataMappers\InvoiceDataMapper;
use Modules\Notifications\Api\Events\ResourceDeliveredEvent;
use Modules\Notifications\Api\NotificationFacadeInterface;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class InvoiceServiceTest extends TestCase
{
    private InvoiceService $invoiceService;
    private InvoiceRepositoryInterface $invoiceRepository;
    private NotificationFacadeInterface $notificationFacade;
    private InvoiceDataMapper $invoiceDataMapper;

    protected function setUp(): void
    {
        $this->invoiceRepository = $this->createMock(InvoiceRepositoryInterface::class);
        $this->invoiceDataMapper = $this->createMock(InvoiceDataMapper::class);
        $this->notificationFacade = $this->createMock(NotificationFacadeInterface::class);

        $this->invoiceService = new InvoiceService(
            $this->invoiceRepository,
            $this->invoiceDataMapper,
            $this->notificationFacade
        );
    }

    public function testCreateInvoiceSavesNewInvoice(): void
    {
        $validDTO = new CreateInvoiceDTO(
            'Valid Name',
            'valid@email.com',
            [new InvoiceProductLineDTO('', 1, 100, 100)]
        );

        $this->invoiceRepository->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Invoice::class));

        $this->invoiceDataMapper->expects($this->once())
            ->method('mapToDTO');

        $this->invoiceService->createInvoice($validDTO);
    }

    public function testCreateInvoiceThrowsOnInvalidProduct(): void
    {
        $invalidDTO = new CreateInvoiceDTO(
            'Valid Name',
            'valid@email.com',
            [new InvoiceProductLineDTO('', 0, -100, 0)]
        );

        $this->expectException(\InvalidArgumentException::class);

        $this->invoiceService->createInvoice($invalidDTO);
    }

    public function testGetInvoiceMapsFoundInvoice(): void
    {
        $invoiceId = Uuid::uuid4()->toString();
        $invoice = new Invoice(
            id: new UuidId($invoiceId),
            customerName: "Customer Name",
            customerEmail: "customer@example.com",
            status: Status::DRAFT,
            productLines: []
        );

        $this->invoiceRepository->expects($this->once())
            ->method('findById')
            ->with($invoiceId)
            ->willReturn($invoice);

        $this->invoiceDataMapper->expects($this->once())
            ->method('mapToDTO')
            ->with($invoice);

        $this->invoiceService->getInvoice($invoiceId);
    }

    public function testGetInvoiceReturnsNullWhenNotFound(): void
    {
        $this->invoiceRepository->expects($this->once())
            ->method('findById')
            ->willReturn(null);

        $this->assertNull($this->invoiceService->getInvoice(Uuid::uuid4()->toString()));
    }

    public function testSendInvoiceThrowsWhenNotFound(): void
    {
        $this->invoiceRepository->expects($this->once())
            ->method('findById')
            ->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->invoiceService->sendInvoice(Uuid::uuid4()->toString());
    }

    public function testSendInvoiceChangesStatusAndSendsNotification(): void
    {
        $invoiceId = Uuid::uuid4()->toString();
        $productLine = new InvoiceProductLine(
            id: new UuidId(Uuid::uuid4()->toString()),
            name: "Product Name",
            quantity: 1,
            unitPrice: 100,
        );
        $invoice = new Invoice(
            id: new UuidId($invoiceId),
            customerName: "Customer Name",
            customerEmail: "test@example.com",
            status: Status::DRAFT,
            productLines: [$productLine]
        );

        $this->invoiceRepository->expects($this->once())
            ->method('findById')
            ->with($invoiceId)
            ->willReturn($invoice);

        $this->invoiceRepository->expects($this->once())
            ->method('updateStatus')
            ->with($invoice);

        $this->notificationFacade->expects($this->once())
            ->method('notify');

        $expectedDto = new InvoiceDTO(
            id: $invoiceId,
            customerName: 'Test Customer',
            customerEmail: 'test@example.com',
            status: Status::SENDING->value,
            productLines: [
                new InvoiceProductLineDTO(
                    name: 'Test Product',
                    quantity: 1,
                    unitPrice: 1000,
                    totalUnitPrice: 1000
                )
            ],
            totalPrice: 1000
        );
        $this->invoiceDataMapper->expects($this->once())
            ->method('mapToDTO')
            ->with($invoice)
            ->willReturn($expectedDto);

        $result = $this->invoiceService->sendInvoice($invoiceId);
        $this->assertSame($expectedDto, $result);
    }

    public function testHandleResourceDeliveredSuccessfully(): void
    {
        $resourceId = Uuid::uuid4();
        $event = new ResourceDeliveredEvent($resourceId);
        $productLine = new InvoiceProductLine(
            id: new UuidId(Uuid::uuid4()->toString()),
            name: "Product Name",
            quantity: 1,
            unitPrice: 100,
        );
        $invoice = new Invoice(
            id: new UuidId($resourceId),
            customerName: "Customer Name",
            customerEmail: "customer@example.com",
            status: Status::SENDING,
            productLines: [$productLine]
        );

        $this->invoiceRepository->expects($this->once())
            ->method('findById')
            ->with($resourceId->toString())
            ->willReturn($invoice);

        $this->invoiceRepository->expects($this->once())
            ->method('updateStatus')
            ->with($invoice);

        $this->invoiceService->handleResourceDelivered($event);
    }
}
