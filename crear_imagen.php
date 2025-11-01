<?php
// Crear una imagen de prueba simple
$width = 400;
$height = 400;

// Crear imagen
$image = imagecreatetruecolor($width, $height);

// Colores
$blue = imagecolorallocate($image, 27, 57, 106);
$light_blue = imagecolorallocate($image, 0, 40, 85);
$yellow = imagecolorallocate($image, 255, 255, 0);
$green = imagecolorallocate($image, 0, 255, 0);
$white = imagecolorallocate($image, 255, 255, 255);

// Fondo
imagefill($image, 0, 0, $blue);

// Crear hexágono
$points = array(
    $width/2, 50,           // Punto superior
    $width-50, 150,          // Punto superior derecho
    $width-50, $height-150,  // Punto inferior derecho
    $width/2, $height-50,    // Punto inferior
    50, $height-150,         // Punto inferior izquierdo
    50, 150                 // Punto superior izquierdo
);

// Dibujar hexágono
imagefilledpolygon($image, $points, 6, $light_blue);

// Texto
$font_size = 5;
$text1 = "TECNOLOGICO NACIONAL DE MEXICO";
$text2 = "Responsabilidad Social";
$text3 = "Formacion Integral";

// Centrar texto
$text1_x = ($width - strlen($text1) * imagefontwidth($font_size)) / 2;
$text2_x = ($width - strlen($text2) * imagefontwidth($font_size)) / 2;
$text3_x = ($width - strlen($text3) * imagefontwidth($font_size)) / 2;

// Dibujar texto
imagestring($image, $font_size, $text1_x, 80, $text1, $white);
imagestring($image, $font_size, $text2_x, 200, $text2, $yellow);
imagestring($image, $font_size, $text3_x, 250, $text3, $green);

// Guardar imagen
imagepng($image, 'imagen/insignia_Responsabilidad Social.png');

// Liberar memoria
imagedestroy($image);

echo "Imagen creada exitosamente: imagen/insignia_Responsabilidad Social.png";
?>
