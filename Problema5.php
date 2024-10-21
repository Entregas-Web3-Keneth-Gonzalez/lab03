<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Elenco de Película</title>
    <link rel="stylesheet" href="estilos/problema5.css">
</head>

<body>
    <h2>Detalles del Elenco de una Película</h2>

    <form method="post" action="">
        <label for="film_id">Código de la Película:</label>
        <input type="number" id="film_id" name="film_id" required>
        <input type="submit" value="Generar Reporte">
    </form>

    <?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Establecer conexión con la base de datos
        include_once("codigos/conexion.inc");

        // Obtener el código de la película ingresado
        $film_id = $_POST['film_id'];

        // Consulta para obtener los datos de la película y los actores
        $AuxSql = "
        SELECT 
            f.film_id AS 'Código', 
            f.title AS 'Nombre', 
            f.description AS 'Descripción', 
            GROUP_CONCAT(DISTINCT c.name ORDER BY c.name ASC) AS 'Categorías', 
            f.release_year AS 'Año',
            GROUP_CONCAT(DISTINCT CONCAT(a.first_name, ' ', a.last_name) ORDER BY a.first_name ASC SEPARATOR ', ') AS 'Actores'
        FROM 
            film f
        LEFT JOIN 
            film_category fc ON f.film_id = fc.film_id
        LEFT JOIN 
            category c ON fc.category_id = c.category_id
        LEFT JOIN 
            film_actor fa ON f.film_id = fa.film_id
        LEFT JOIN 
            actor a ON fa.actor_id = a.actor_id
        WHERE 
            f.film_id = $film_id
        GROUP BY 
            f.film_id, f.title, f.description, f.release_year";

        // Ejecutar la consulta
        $Regis = mysqli_query($conex, $AuxSql) or die(mysqli_error($conex));

        // Procesar los resultados
        $filme = mysqli_fetch_array($Regis);

        // Crear el documento XML
        $xml = "<?xml version='1.0' encoding='utf-8' ?>";
        $xml .= "<informacion>";
        $xml .= "   <pelicula>";
        $xml .= "      <codigo>{$filme['Código']}</codigo>";
        $xml .= "      <nombre>{$filme['Nombre']}</nombre>";
        $xml .= "      <descripcion>{$filme['Descripción']}</descripcion>";
        $xml .= "      <categorias>{$filme['Categorías']}</categorias>";
        $xml .= "      <ano>{$filme['Año']}</ano>";
        $xml .= "   </pelicula>";
        $xml .= "   <elenco>";

        // Repetir para cada actor
        $actors = explode(",", $filme['Actores']);
        foreach ($actors as $actor) {
            $xml .= "      <actor>{$actor}</actor>";
        }

        $xml .= "   </elenco>";
        $xml .= "</informacion>";

        // Liberar espacio de la consulta
        mysqli_free_result($Regis);

        // Guardar el XML en un archivo
        $filePath = "problema5.xml";
        file_put_contents($filePath, $xml);

        // Mostrar los datos en la página
        echo "<div class='movie-info'>";
        echo "<h3>Detalles de la Película</h3>";
        echo "<p><strong>Código:</strong> {$filme['Código']}</p>";
        echo "<p><strong>Nombre:</strong> {$filme['Nombre']}</p>";
        echo "<p><strong>Descripción:</strong> {$filme['Descripción']}</p>";
        echo "<p><strong>Categorías:</strong> {$filme['Categorías']}</p>";
        echo "<p><strong>Año de Lanzamiento:</strong> {$filme['Año']}</p>";
        echo "</div>";

        // Lista de actores
        echo "<p>Reporte generado exitosamente.</p>";
        echo "<a href='problema5.xml'>Ver XML Generado</a>";
        echo "</div>";
    }
    ?>
</body>

</html>
