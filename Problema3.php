<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Ingresos</title>
    <link rel="stylesheet" href="estilos/problema5.css">
</head>
<body>
    <h1>Generar Reporte de Ingresos</h1>
    <form action="pdfProblema3.php" method="post">
        <label for="fecha_inicio">Fecha de Inicio:</label>
        <input type="date" id="fecha_inicio" name="fecha_inicio" required>
        <br><br>
        <label for="fecha_fin">Fecha de Fin:</label>
        <input type="date" id="fecha_fin" name="fecha_fin" required>
        <br><br>
        <input type="submit" value="Generar Reporte">
    </form>
</body>
</html>
