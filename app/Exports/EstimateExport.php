<?php

namespace App\Exports;

use App\Models\Estimate;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class EstimateExport implements WithMultipleSheets
{
    public function __construct(
        private readonly Estimate $estimate
    ) {}

    public function sheets(): array
    {
        return [
            new EstimateSummarySheet($this->estimate),
            new EstimateBoqSheet($this->estimate),
        ];
    }
}
