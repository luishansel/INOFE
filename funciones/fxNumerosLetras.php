<?php
	function fxNumerosLetras($numero)
	{
        $unidad = array('', 'uno', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve');
        $decena = array('', 'diez', 'veinte', 'treinta', 'cuarenta', 'cincuenta', 'sesenta', 'setenta', 'ochenta', 'noventa');
        $especiales = array(11 => 'once', 12 => 'doce', 13 => 'trece', 14 => 'catorce', 15 => 'quince', 16 => 'dieciséis', 17 => 'diecisiete', 18 => 'dieciocho', 19 => 'diecinueve');
        $centena = array('', 'ciento', 'doscientos', 'trescientos', 'cuatrocientos', 'quinientos', 'seiscientos', 'setecientos', 'ochocientos', 'novecientos');

        // Función interna para convertir números de 1 a 99
        $decenas = function($num) use ($unidad, $decena, $especiales) {
            if ($num == 0) return '';
            if ($num < 10) return $unidad[$num];
            if ($num > 10 && $num < 20) return $especiales[$num];
            if ($num % 10 == 0) return $decena[intval($num / 10)];
            if ($num < 30) return 'veinti' . $unidad[$num % 10];
            return $decena[intval($num / 10)] . ' y ' . $unidad[$num % 10];
        };

        // Función interna para convertir números de 1 a 999
        $centenas = function($num) use ($centena, $decenas) {
            if ($num == 100) return 'cien';
            if ($num > 100) {
                return $centena[intval($num / 100)] . ' ' . $decenas($num % 100);
            }
            return $decenas($num);
        };

        // Función para convertir números de 1 a 999999
        $miles = function($num) use ($centenas) {
            if ($num < 1000) return $centenas($num);
            if ($num < 2000) return 'mil ' . $centenas($num % 1000);
            return $centenas(intval($num / 1000)) . ' mil ' . $centenas($num % 1000);
        };

        // Función para convertir números de 1 a 999999999
        $millones = function($num) use ($miles) {
            if ($num < 1000000) return $miles($num);
            if ($num < 2000000) return 'un millón ' . $miles($num % 1000000);
            return $miles(intval($num / 1000000)) . ' millones ' . $miles($num % 1000000);
        };

        // Separar parte entera y decimal con seguridad
        $partes = explode('.', number_format(floatval($numero), 2, '.', ''));

        $entero = isset($partes[0]) ? intval($partes[0]) : 0;
        $decimal = isset($partes[1]) ? intval($partes[1]) : 0;

        $letras_entero = trim($millones($entero));

        // Manejo especial para cero
        if ($entero == 0) {
            $letras_entero = 'cero';
        }

        // Formatear la parte decimal como fracción de 100
        $letras_decimal = $decimal > 0 ? 'con ' . str_pad($decimal, 2, '0', STR_PAD_LEFT) . '/100' : '';

        $msResultado = ucfirst($letras_entero) . ' ' . $letras_decimal;

        return $msResultado;
    }
?>