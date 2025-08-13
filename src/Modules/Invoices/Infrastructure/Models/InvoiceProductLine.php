<?php

namespace Modules\Invoices\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceProductLine extends Model
{
    protected $table = 'invoice_product_lines';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'invoice_id', 'name', 'quantity', 'price'
    ];
}
