<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="html" encoding="UTF-8" indent="yes"/>
    <xsl:template match="/">
        <html>
            <head>
                <title>Informe de Alquileres</title>
                <style>
                    body { font-family: Arial, sans-serif; background-color: #FFFFCC; color: #800000; }
                    h2 { color: #800000; }
                    table { width: 100%; border-collapse: collapse; }
                    th, td { border: 1px solid #800000; padding: 8px; text-align: left; }
                    th { background-color: #FFE6A6; }
                </style>
            </head>
            <body>
                <h2>Informe de Películas Más Solicitadas</h2>
                <table>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Veces Alquilado</th>
                        <th>Total Generado</th>
                    </tr>
                    <xsl:for-each select="informacion/clasificacion/categoria">
                        <tr>
                            <td><xsl:value-of select="codigo"/></td>
                            <td><xsl:value-of select="nombre"/></td>
                            <td><xsl:value-of select="veces_alquilado"/></td>
                            <td><xsl:value-of select="total_generado"/></td>
                        </tr>
                    </xsl:for-each>
                </table>
                <h3>Gran Total Generado: <xsl:value-of select="informacion/gran_total"/></h3>
            </body>
        </html>
    </xsl:template>
</xsl:stylesheet>
