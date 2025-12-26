<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case Draft = 'draft';
    case Finalized = 'finalized';
    case Voided = 'voided';
}
