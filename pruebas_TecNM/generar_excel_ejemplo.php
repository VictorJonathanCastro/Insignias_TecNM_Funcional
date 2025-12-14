<?php
/**
 * Script para generar un archivo Excel de ejemplo con 3 insignias otorgadas
 */

require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Crear un nuevo Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Nombre de la hoja
$sheet->setTitle('Insignias Otorgadas');

// Headers (primera fila)
$headers = ['Codigo_Insignia', 'Destinatario', 'Fecha_Emision', 'Periodo_Emision', 'Responsable_Emision', 'Estatus'];
$col = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($col . '1', $header);
    $col++;
}

// Datos de ejemplo (3 filas)
$datos = [
    ['TECNM-OFCM-2025-ART-001', 'Juan Pérez Gómez', '2025-01-15', 1, 1, 1],
    ['TECNM-OFCM-2025-EMB-002', 'María González López', '2025-01-16', 1, 1, 1],
    ['TECNM-OFCM-2025-TAL-003', 'Carlos Ramírez Martínez', '2025-01-17', 1, 1, 1],
];

// Agregar datos
$fila = 2;
foreach ($datos as $row) {
    $col = 'A';
    foreach ($row as $valor) {
        $sheet->setCellValue($col . $fila, $valor);
        $col++;
    }
    $fila++;
}

// Ajustar ancho de columnas
foreach (range('A', 'F') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Estilo para headers
$headerStyle = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => ['rgb' => '1e3c72']
    ],
    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
];
$sheet->getStyle('A1:F1')->applyFromArray($headerStyle);

// Guardar archivo
$filename = 'ejemplo_insignias_otorgadas.xlsx';
$writer = new Xlsx($spreadsheet);

// Enviar al navegador para descarga
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer->save('php://output');
exit;

?>

