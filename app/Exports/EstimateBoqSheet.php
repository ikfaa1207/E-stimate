<?php

namespace App\Exports;

use App\Models\Estimate;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class EstimateBoqSheet implements FromArray, WithTitle
{
    public function __construct(
        private readonly Estimate $estimate
    ) {}

    public function array(): array
    {
        $rows = [
            ['Item', 'Quantity', 'Unit', 'Unit Cost', 'Total Cost'],
        ];

        foreach ($this->estimate->lines as $line) {
            $rows[] = [
                $line->item_name,
                (float) $line->quantity,
                $line->unit,
                (float) $line->unit_cost,
                (float) $line->line_total,
            ];
        }

        if ($this->estimate->adjustments->isNotEmpty()) {
            $rows[] = ['', '', '', '', ''];
            $rows[] = ['Custom Adjustments', '', '', '', ''];
            foreach ($this->estimate->adjustments as $adjustment) {
                $rows[] = [
                    $adjustment->name . ' (' . ucfirst($adjustment->type) . ')',
                    '',
                    '',
                    '',
                    (float) $adjustment->amount,
                ];
            }
        }

        $rows[] = ['', '', '', '', ''];
        $rows[] = ['Adjusted Grand Total', '', '', '', (float) $this->estimate->total_cost];

        return $rows;
    }

    public function title(): string
    {
        return 'BOQ';
    }
}
