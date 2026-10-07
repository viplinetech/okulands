<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

/** A plain headed table, used by every report export. */
class TableExport implements FromArray, ShouldAutoSize, WithHeadings
{
    /** @param array<int, string> $headings @param array<int, array<int, mixed>> $rows */
    public function __construct(private array $headings, private array $rows) {}

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return $this->headings;
    }
}
