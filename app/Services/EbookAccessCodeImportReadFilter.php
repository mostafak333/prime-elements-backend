<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

class EbookAccessCodeImportReadFilter implements IReadFilter
{
    private int $startRow = 1;

    private int $endRow = 1;

    public function setRows(int $startRow, int $endRow): void
    {
        $this->startRow = $startRow;
        $this->endRow = $endRow;
    }

    public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool
    {
        return $row === 1 || ($columnAddress === 'A' && $row >= $this->startRow && $row <= $this->endRow);
    }
}
