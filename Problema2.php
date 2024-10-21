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
        $this->Cell(20, 10, utf8_decode('Laboratorio 3 impresión de películas'), 0, 0, 'C');

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

    // Método para imprimir categorías
    function DatCategorias($nombreCategoria)
    {
        // Ancho de las columnas
        $ancho = array(10, 60);
        $this->SetFont('Arial', 'B', 12); // Negrita
        $this->Cell($ancho[0], 10, '');
        $this->Cell($ancho[1], 10, $nombreCategoria, 0, 1); // Escribe categoría
        $this->SetFont('Times', '', 12);
    }

    // Método para imprimir películas
    function films($film, $stock, $year)
    {
        $this->Ln(3); // Salto de línea
        $this->SetFont('Arial', '', 12);
        $this->SetFillColor(200, 220, 255); // Color para los datos de films

        // Anchos entre los datos
        $anchoTitulo = 70;
        $anchoStock = 30;
        $anchoYear = 20;

        // Imprimir datos de la película
        $this->Cell($anchoTitulo, 6, $film, 0, 0, 'L', true);
        $this->Cell($anchoStock, 6, $stock, 0, 0, 'C', true);
        $this->Cell($anchoYear, 6, $year, 0, 1, 'C', true);
        $this->Ln(4); // Salto de línea después de imprimir la fila
    }

    // Método para imprimir encabezados de columnas
    function cols()
    {
        // Ancho de las columnas
        $anchoTitulo = 60;
        $anchoStock = 40;
        $anchoYear = 30;

        $this->Ln(3); // Salto de línea
        $this->SetFont('Arial', 'B', 12); // Fuente negrita
        $this->SetFillColor(255, 255, 255); // Color de fondo blanco

        // Encabezados en una sola fila
        $this->Cell($anchoTitulo, 6, "Film", 0, 0, 'L', true); 
        $this->Cell($anchoStock, 6, "Stock", 0, 0, 'C', true);
        $this->Cell($anchoYear, 6, "Year", 0, 1, 'C', true);
        $this->Ln(4); // Salto de línea después de los encabezados
    }
}

// Inicializa el objeto PDF
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage(); // Agrega una página
$pdf->SetFont('Times', '', 12); // Establece la fuente

// Conexión a la base de datos
include_once("codigos/conexion.inc");

// Consulta para obtener las categorías de películas y stock
$AuxSql = "
    SELECT
        c.name AS category,
        f.title AS film_title,
        COUNT(i.inventory_id) AS stock,
        f.release_year
    FROM
        category c
    JOIN
        film_category fc ON c.category_id = fc.category_id
    JOIN
        film f ON fc.film_id = f.film_id
    JOIN
        inventory i ON f.film_id = i.film_id
    GROUP BY
        c.name, f.title, f.release_year
    ORDER BY
        c.name, f.title;
";

// Valida la conexión y ejecuta la consulta
$Regis = mysqli_query($conex, $AuxSql);

// Verifica si hay resultados
if (mysqli_num_rows($Regis) > 0) {
    $ultimaCategoria = ''; // Variable para controlar la última categoría
    // Itera sobre los registros
    while ($row_Regis = mysqli_fetch_assoc($Regis)) {
        // La categoría ha cambiado?
        if ($ultimaCategoria !== $row_Regis['category']) {
            // Si ha cambiado, imprime la nueva categoría
            $pdf->DatCategorias($row_Regis['category']);
            // Actualiza la variable de categoría
            $ultimaCategoria = $row_Regis['category'];
            // Imprime encabezados de columnas
            $pdf->cols();
        }
        // Función para impresión de películas
        $pdf->films($row_Regis['film_title'], $row_Regis['stock'], $row_Regis['release_year']);
    }
} else {
    echo "No se encontraron registros.";
}

// Cerrar el documento PDF
$pdf->Output();
