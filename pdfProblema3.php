<?php
// Librería en PHP para crear documentos .pdf
require('codigos/fpdf.php');

// Clase que extiende FPDF para definir encabezado y pie de página
class PDF extends FPDF
{
    // Cabecera de página
    function Header()
    {
        // Declaración de la fuente 
        $this->SetFont('Arial', 'B', 15);
        // Movernos a la derecha
        $this->Cell(80);
        // Título
        $this->Cell(20, 10, utf8_decode('Reporte de Ingresos'), 0, 0, 'C');
        // Salto de línea
        $this->Ln(20);
    }

    // Pie de página
    function Footer()
    {
        // Posición: a 1.5 cm del borde inferior
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        // Número de página
        $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    // Método para imprimir encabezados de columnas
    function cols()
    {
        // Ancho de las columnas
        $anchoNombre = 80;
        $anchoGenero = 50;
        $anchoMonto = 30;

        $this->Ln(3); // Salto de línea
        $this->SetFont('Arial', 'B', 12); // Fuente negrita
        $this->SetFillColor(255, 255, 255); // Color de fondo blanco

        // Encabezados en una sola fila
        $this->Cell($anchoNombre, 6, "Nombre", 0, 0, 'L', true); 
        $this->Cell($anchoGenero, 6, "Genero", 0, 0, 'C', true);
        $this->Cell($anchoMonto, 6, "Monto", 0, 1, 'C', true);
        $this->Ln(4); // Salto de línea después de los encabezados
    }

    // Método para imprimir datos de ingresos
    function ingreso($nombre, $genero, $monto)
    {
        $this->Ln(3); // Salto de línea
        $this->SetFont('Arial', '', 12);
        $this->SetFillColor(200, 220, 255); // Color para los datos de ingresos

        // Anchos entre los datos
        $anchoNombre = 80;
        $anchoGenero = 50;
        $anchoMonto = 30;

        // Imprimir datos de ingreso
        $this->Cell($anchoNombre, 6, $nombre, 0, 0, 'L', true);
        $this->Cell($anchoGenero, 6, $genero, 0, 0, 'C', true);
        $this->Cell($anchoMonto, 6, number_format($monto, 2), 0, 1, 'C', true);
    }
}

// Inicializa el objeto PDF
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage(); // Agrega una página
$pdf->SetFont('Times', '', 12); // Establece la fuente

// Conexión a la base de datos
include_once("codigos/conexion.inc");

// Obtiene las fechas ingresadas por el usuario
$fecha_inicio = $_POST['fecha_inicio'];
$fecha_fin = $_POST['fecha_fin'];

// Consulta para obtener los ingresos
$AuxSql = "
SELECT
    s.store_id AS id,
    CONCAT(a.address, ', ', a.district) AS nombre,
    c.name AS genero,
    SUM(p.amount) AS monto
FROM
    payment p
JOIN
    rental r ON p.rental_id = r.rental_id
JOIN
    inventory i ON r.inventory_id = i.inventory_id
JOIN
    film f ON i.film_id = f.film_id
JOIN
    film_category fc ON f.film_id = fc.film_id
JOIN
    category c ON fc.category_id = c.category_id
JOIN
    store s ON i.store_id = s.store_id
JOIN
    address a ON s.address_id = a.address_id
WHERE
    r.rental_date BETWEEN '$fecha_inicio' AND '$fecha_fin'
GROUP BY
    s.store_id, nombre, c.name
ORDER BY
    monto DESC;";

// Valida la conexión y ejecuta la consulta
$Regis = mysqli_query($conex, $AuxSql);

// Verifica si hay resultados
if (mysqli_num_rows($Regis) > 0) {
    // Imprime encabezados de columnas
    $pdf->cols();
    
    // Itera sobre los registros
    while ($row_Regis = mysqli_fetch_assoc($Regis)) {
        // Función para impresión de ingresos
        $pdf->ingreso($row_Regis['nombre'], $row_Regis['genero'], $row_Regis['monto']);
    }
} else {
    echo "No se encontraron registros.";
}

// Cerrar el documento PDF
$pdf->Output();
