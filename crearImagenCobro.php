<?php
function crearImagen($msCodCobro, $msConcepto, $msFecha)
{
    // Define las dimensiones de la imagen en pixeles
    $ancho = 500;
    $alto = 500;

    // Crea una nueva imagen en blanco
    $imagen = imagecreatetruecolor($ancho, $alto);

    // Asigna colores
    $blanco = imagecolorallocate($imagen, 255, 255, 255);
    $cafe = imagecolorallocate($imagen, 236, 50, 9);
    $negro = imagecolorallocate($imagen, 0, 0, 0);

    // Rellena el fondo
    //imagefill($imagen, 0, 250, $blanco);
    //imagefill($imagen, 250, 500, $rojo);

    //Rectángulo
    imagefilledrectangle($imagen, 0, 0, 500, 230, $blanco);
    imagefilledrectangle($imagen, 0, 230, 500, 500, $cafe);

    //Logotipo
    $msLogotipo = imagecreatefromjpeg("imagenes/inofeLogo.jpg");
    $mnAlto = imagesy($msLogotipo);
    $mnAncho = imagesx($msLogotipo);
    imagecopyresampled($imagen, $msLogotipo, 170, 40, 0, 0, 160, 150, $mnAncho, $mnAlto);

    // Define la fuente y el texto
    $arialNormal = 'fonts/arial.ttf'; // Reemplaza con la ruta a una fuente TTF válida
    $arialBold = 'fonts/arialbd.ttf';
    $robotoMonoBold = 'fonts/RobotoMono-Bold.ttf';
    $texto = 'INOFE te recuerda tu próximo pago';

    // Agrega el texto a la imagen
    imagettftext($imagen, 20, 0, 20, 300, $negro, $arialNormal, $texto);
    imagettftext_multiline($imagen, 20, 0, 20, 340, $blanco, $robotoMonoBold, $msConcepto, 480);
    imagettftext($imagen, 18, 0, 20, 450, $negro, $arialNormal, "Vencimiento: " . $msFecha);

    // Define el nombre del archivo donde se guardará la imagen
    $nombre_archivo = $msCodCobro . '.png';

    // Guarda la imagen como un archivo PNG en el servidor
    // La función imagepng() ahora acepta un segundo argumento: el nombre del archivo
    imagepng($imagen, $nombre_archivo);

    // Libera la memoria
    imagedestroy($imagen);
}

/**
 * Dibuja un texto multilínea con una fuente TrueType
 *
 * @param resource $image El recurso de la imagen
 * @param int $font_size El tamaño de la fuente en puntos
 * @param int $angle El ángulo de rotación (generalmente 0)
 * @param int $x La coordenada X inicial
 * @param int $y La coordenada Y inicial
 * @param int $color El color del texto
 * @param string $font_file La ruta al archivo de la fuente .ttf
 * @param string $text El texto a dibujar
 * @param int $max_width El ancho máximo permitido para el texto
 */
function imagettftext_multiline($image, $font_size, $angle, $x, $y, $color, $font_file, $text, $max_width) {
    // Dividir el texto en palabras
    $palabras = explode(' ', $text);
    $linea_actual = '';
    $lineas = [];

    foreach ($palabras as $palabra) {
        // Concatenar la palabra a la línea actual
        $test_linea = ($linea_actual ? $linea_actual . ' ' : '') . $palabra;

        // Obtener el ancho del cuadro delimitador de la línea de prueba
        $caja = imagettfbbox($font_size, $angle, $font_file, $test_linea);
        $ancho = abs($caja[2] - $caja[0]); // Ancho total del texto

        if ($ancho <= $max_width) {
            $linea_actual = $test_linea;
        } else {
            // Si la línea excede el ancho, la guardamos y empezamos una nueva
            $lineas[] = $linea_actual;
            $linea_actual = $palabra;
        }
    }
    // Agregar la última línea
    if ($linea_actual !== '') {
        $lineas[] = $linea_actual;
    }
    
    // Altura de una línea de texto
    $caja_altura = imagettfbbox($font_size, $angle, $font_file, 'A');
    $altura_linea = abs($caja_altura[1] - $caja_altura[7]);

    // Dibujar cada línea en la imagen
    foreach ($lineas as $i => $linea) {
        imagettftext($image, $font_size, $angle, $x, $y + ($i * $altura_linea * 1.5), $color, $font_file, $linea);
    }
}
?>