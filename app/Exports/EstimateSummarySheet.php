<?php

namespace App\Exports;

use App\Models\Estimate;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class EstimateSummarySheet implements FromArray, WithTitle
{
    public function __construct(
        private readonly Estimate $estimate
    ) {}

    public function array(): array
    {
        $project = $this->estimate->project;
        $requirement = $this->estimate->projectRequirement;

        return [
            ['Field', 'Value'],
            ['Project Name', $project?->name],
            ['Client Name', $project?->client_name],
            ['Lot Area (sqm)', $project?->lot_area],
            ['Number of Floors', $requirement?->number_of_floors],
            ['Finish Level', $this->estimate->finish_level],
            ['Gross Floor Area (sqm)', $this->estimate->gross_floor_area],
            ['Total Construction Cost', $this->estimate->total_cost],
            ['Cost per sqm', $this->estimate->cost_per_sqm],
            ['Generated At', optional($this->estimate->generated_at)->toDateTimeString()],
        ];
    }

    public function title(): string
    {
        return 'Summary';
    }
}
