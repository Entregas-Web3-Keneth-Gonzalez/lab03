<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Librería en PHP para crear documentos .pdf
require('codigos/fpdf.php');

// Clase que extiende FPDF para definir encabezado y pie de página
class PDF extends FPDF
{
    // Cabecera de página
    function Header()
    {
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(0, 10, 'Reporte de Facturas', 0, 1, 'C');
        $this->Ln(10); // Salto de línea
    }

    // Pie de página
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    // Método para imprimir información del cliente
    function ClientInfo($cliente, $contacto, $ubicacion, $fechaInicio, $fechaFin)
    {
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'Cliente: ' . $cliente, 0, 1);
        $this->Cell(0, 10, 'Contacto: ' . $contacto, 0, 1);
        $this->Cell(0, 10, 'Ubicacion: ' . $ubicacion, 0, 1);
        $this->Cell(0, 10, 'Fecha de Consultas: Inicio: ' . $fechaInicio . ' - Final: ' . $fechaFin, 0, 1);
        $this->Ln(5); // Salto de línea
    }

    // Método para imprimir encabezados de facturas
    function InvoiceHeader($factura, $fecha_facturacion, $empleado, $fecha_requerida, $fecha_despachada)
    {
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'Factura #: ' . $factura . ' Fecha Facturación: ' . $fecha_facturacion, 0, 1);
        $this->Cell(0, 10, 'Empleado: ' . $empleado . ' Requerida: ' . $fecha_requerida . ' Despachada: ' . $fecha_despachada, 0, 1);
        $this->Ln(5); // Salto de línea
    }

    // Método para imprimir encabezados de columnas de productos
    function ProductCols()
    {
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(30, 10, 'Codigo', 1);
        $this->Cell(80, 10, 'Nombre', 1);
        $this->Cell(30, 10, 'Cantidad', 1);
        $this->Cell(30, 10, 'Precio Uni', 1);
        $this->Cell(30, 10, 'Descuento', 1);
        $this->Cell(30, 10, 'Total', 1);
        $this->Ln();
    }

    // Método para imprimir detalles de productos
    function ProductDetails($product_code, $product_name, $quantity, $unit_price, $discount)
    {
        $total = ($unit_price * $quantity) * (1 - $discount);
        $this->SetFont('Arial', '', 10);
        $this->Cell(30, 10, $product_code, 1);
        $this->Cell(80, 10, $product_name, 1);
        $this->Cell(30, 10, $quantity, 1);
        $this->Cell(30, 10, number_format($unit_price, 2), 1);
        $this->Cell(30, 10, number_format($discount * 100, 2) . '%', 1);
        $this->Cell(30, 10, number_format($total, 2), 1);
        $this->Ln();
        return $total; // Devuelve el total para calcular la suma
    }
}

// Inicializa el objeto PDF
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Times', '', 12);

// Conexión a la base de datos
include_once("codigos/conexion2.inc");

// Obtener parámetros del formulario
$customerID = isset($_POST['customerID']) ? $_POST['customerID'] : 'ALFKI'; // Valor predeterminado
$fechaInicio = isset($_POST['fechaInicio']) ? $_POST['fechaInicio'] : '1994-08-04'; // Valor predeterminado
$fechaFin = isset($_POST['fechaFin']) ? $_POST['fechaFin'] : '1996-06-05'; // Valor predeterminado

// Obtener información del cliente
$clienteSql = "
    SELECT  
        c.CompanyName AS cliente,  
        CONCAT(c.ContactTitle, ' ', c.ContactName) AS contacto,  
        CONCAT(c.Address, ', ', c.City, ', ', c.Country, ' ', c.PostalCode) AS ubicacion  
    FROM  
        customers c  
    WHERE  
        c.CustomerID = '$customerID'  
    LIMIT 1;
";
$clientResult = mysqli_query($conex, $clienteSql);
$clientData = mysqli_fetch_assoc($clientResult);

// Obtener facturas del cliente
$facturaSql = "
    SELECT 
        o.OrderID AS factura,
        o.OrderDate AS fecha_facturacion,
        CONCAT(e.FirstName, ' ', e.LastName) AS empleado,
        o.RequiredDate AS fecha_requerida,
        o.ShippedDate AS fecha_despachada
    FROM 
        orders o
    JOIN 
        employees e ON o.EmployeeID = e.EmployeeID
    WHERE 
        o.CustomerID = '$customerID'  
        AND o.OrderDate BETWEEN '$fechaInicio' AND '$fechaFin';
";
$invoiceResult = mysqli_query($conex, $facturaSql);

// Imprime la información del cliente
$pdf->ClientInfo(
    $clientData['cliente'],
    $clientData['contacto'],
    $clientData['ubicacion'],
    date('d/M/Y', strtotime($fechaInicio)),
    date('d/M/Y', strtotime($fechaFin))
);

// Inicializa total acumulado
$totalAcumulado = 0;

// Itera sobre las facturas
while ($invoiceData = mysqli_fetch_assoc($invoiceResult)) {
    // Imprime encabezado de la factura
    $pdf->InvoiceHeader(
        $invoiceData['factura'],
        date('d/M/Y', strtotime($invoiceData['fecha_facturacion'])),
        $invoiceData['empleado'],
        date('d/M/Y', strtotime($invoiceData['fecha_requerida'])),
        date('d/M/Y', strtotime($invoiceData['fecha_despachada']))
    );

    // Imprime encabezados de columnas de productos
    $pdf->ProductCols();

    // Obtener detalles de la factura
    $detailsSql = "
        SELECT 
            p.ProductID AS product_code,
            p.ProductName,
            od.Quantity,
            od.UnitPrice,
            od.Discount
        FROM 
            order_details od
        JOIN 
            products p ON od.ProductID = p.ProductID  
        WHERE 
            od.OrderID = " . $invoiceData['factura'] . "; 
    ";
    $detailsResult = mysqli_query($conex, $detailsSql);

    // Imprime detalles de productos
    while ($productData = mysqli_fetch_assoc($detailsResult)) {
        $totalAcumulado += $pdf->ProductDetails(
            $productData['product_code'],
            $productData['ProductName'],
            $productData['Quantity'],
            $productData['UnitPrice'],
            $productData['Discount']
        );
    }

    // Total acumulado por factura
    $pdf->Cell(0, 10, 'Total Factura: ' . number_format($totalAcumulado, 2), 0, 1);
    $totalAcumulado = 0; // Reinicia para la próxima factura
}

// Cierra la conexión
mysqli_close($conex);

// Genera el PDF
$pdf->Output();
?>
