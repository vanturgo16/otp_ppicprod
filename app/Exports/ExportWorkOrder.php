<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExportWorkOrder implements FromCollection, WithHeadings, WithStyles, WithEvents
{
    protected $data;

    public function __construct(Collection $data)
    {
        $this->data = $data;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->data->map(function ($item, $index) {
            return [
                'no' => $index + 1, // Menambahkan nomor urut mulai dari 1
                'wo_number' => $item->wo_number,
                'so_number' => $item->so_number,
                'date' => $item->date,
                'product' => $item->product_code . ' | ' . $item->description . ' | Perforasi' . $item->perforasi,
                'process_production' => $item->process,
                'work_center' => $item->work_center,
                'qty_proccess' => $item->qty,
                'unit_proccess' => $item->unit,
                'product_needed' => $item->pc_needed . ' | ' . $item->dsc . ' | Perforasi' . $item->perforasi_needed,
                'qty_needed' => $item->qty_needed,
                'unit_needed' => $item->unit_needed,
                'note' => $item->note,
                'status' => $item->status,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'No',
            'WO Number',
            'SO Number',
            'Date',
            'Product',
            'Proccess Production',
            'Work Center',
            'Qty Proccess',
            'Unit Proccess',
            'Product Needed',
            'Qty Needed',
            'Unit Needed',
            'Note',
            'Status',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $cellRange = 'A1:N' . ($this->data->count() + 1); // Adjust the cell range as needed
                $styleArray = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ];

                $event->sheet->getDelegate()->getStyle($cellRange)->applyFromArray($styleArray);

                // Auto size columns
                foreach (range('A', 'N') as $columnID) {
                    $event->sheet->getDelegate()->getColumnDimension($columnID)->setAutoSize(true);
                }

                // Wrap text for 'progress' column
                $event->sheet->getDelegate()->getStyle('E')->getAlignment()->setWrapText(true);
                $event->sheet->getDelegate()->getStyle('J')->getAlignment()->setWrapText(true);
            },
        ];
    }
}
