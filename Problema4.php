<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generar Reporte XML</title>
</head>
<body style="background-color: #FFFFCC; color: #800000">
    <h2>Generar Reporte de Películas Alquiladas</h2>

    <form method="post" action="">
        <label for="fecha_inicio">Fecha de Inicio:</label>
        <input type="date" id="fecha_inicio" name="fecha_inicio" required>
        <br>
        <label for="fecha_fin">Fecha de Fin:</label>
        <input type="date" id="fecha_fin" name="fecha_fin" required>
        <br>
        <input type="submit" value="Generar Reporte">
    </form>

    <?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Establecer conexión con la base de datos
        include_once("codigos/conexion.inc");

        // Obtener las fechas ingresadas
        $fecha_inicio = $_POST['fecha_inicio'];
        $fecha_fin = $_POST['fecha_fin'];

        // Consulta para obtener los 10 filmes más solicitados y el total generado
        $AuxSql = "
        SELECT 
            f.film_id AS 'Código', 
            f.title AS 'Nombre', 
            COUNT(r.rental_id) AS 'Veces_Alquilado', 
            SUM(p.amount) AS 'Total_Generado'
        FROM 
            rental r
        JOIN inventory i ON r.inventory_id = i.inventory_id
        JOIN film f ON i.film_id = f.film_id
        JOIN payment p ON r.rental_id = p.rental_id
        WHERE 
            r.rental_date BETWEEN '$fecha_inicio' AND '$fecha_fin'
        GROUP BY 
            f.film_id, f.title
        ORDER BY 
            COUNT(r.rental_id) DESC
        LIMIT 10";

        // Ejecutar la consulta
        $Regis = mysqli_query($conex, $AuxSql) or die(mysqli_error($conex));

        // Crear un arreglo para almacenar los datos
        $filmes = [];
        $total_general = 0;

        // Procesar los resultados
        while ($fila = mysqli_fetch_array($Regis)) {
            $filmes[] = [
                'Codigo' => $fila['Código'],
                'Nombre' => $fila['Nombre'],
                'Veces_Alquilado' => $fila['Veces_Alquilado'],
                'Total_Generado' => $fila['Total_Generado']
            ];
            $total_general += $fila['Total_Generado'];
        }

        // Liberar espacio de la consulta
        mysqli_free_result($Regis);

        // Creación del documento XML
        $xml = "<?xml version='1.0' encoding='utf-8' ?>";
        $xml .= "<?xml-stylesheet type='text/xsl' href='estilos/estilo.xsl'?>";
        $xml .= "<informacion>";
        $xml .= "   <generalidades>";
        $xml .= "      <empresa>";
        $xml .= "         <nombre>Universidad Técnica Nacional</nombre>";
        $xml .= "         <carrera>Tecnologías de la Información</carrera>";
        $xml .= "         <curso>Tecnologías y Sistemas Web2</curso>";
        $xml .= "      </empresa>";
        $xml .= "      <profesor>";
        $xml .= "         <nombre>Jorge Ruiz</nombre>";
        $xml .= "         <experiencia>Profesor en programación desde 1993</experiencia>";
        $xml .= "      </profesor>";
        $xml .= "   </generalidades>";
        $xml .= "   <clasificacion>";

        foreach ($filmes as $filme) {
            $xml .= "      <categoria>";
            $xml .= "         <codigo>{$filme['Codigo']}</codigo>";
            $xml .= "         <nombre>{$filme['Nombre']}</nombre>";
            $xml .= "         <articulos>";
            $xml .= "            <codart>{$filme['Codigo']}</codart>";
            $xml .= "            <nomart>{$filme['Nombre']}</nomart>";
            $xml .= "         </articulos>";
            $xml .= "         <veces_alquilado>{$filme['Veces_Alquilado']}</veces_alquilado>";
            $xml .= "         <total_generado>{$filme['Total_Generado']}</total_generado>";
            $xml .= "      </categoria>";
        }

        $xml .= "   </clasificacion>";
        $xml .= "   <gran_total>{$total_general}</gran_total>";
        $xml .= "</informacion>";

        // Escribir archivo XML
        $ruta = $_SERVER["DOCUMENT_ROOT"] . "/Keneth/lab03/problema4.xml";

        try {
            $archivo = fopen($ruta, "w+");
            fwrite($archivo, $xml);
            fclose($archivo);
            echo "<p>Reporte generado exitosamente.</p>";
            echo "<a href='problema4.xml'>Ver XML Generado</a>";
        } catch (Exception $e) {
            echo "Error:.." . $e->getMessage();
        }
    }
    ?>
</body>
</html>
