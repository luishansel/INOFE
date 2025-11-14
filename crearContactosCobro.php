<?php
    require_once ("funciones/fxGeneral.php");

    function crearContactos($msCobro)
    {
        $m_cnx_MySQL = fxAbrirConexion();
        $nombreArchivo = $msCobro . ".csv";

        $msConsulta = "select KDSA051A.MATRICULA_REL, NOMBRES_010, APELLIDOS_010, CELULAR_010, CONCEPTO_050, FECHAPREVISTA_050 ";
        $msConsulta .= "from KDSA050A, KDSA051A, KDSA030A, KDSA010A ";
        $msConsulta .= "where KDSA051A.COBRO_REL = ? and PAGADO_051 = 0 and EXONERADO_051 = 0 and ANULADO_051 = 0 ";
        $msConsulta .= "and KDSA051A.COBRO_REL = KDSA050A.COBRO_REL ";
        $msConsulta .= "and KDSA051A.MATRICULA_REL = KDSA030A.MATRICULA_REL ";
        $msConsulta .= "and KDSA030A.ESTUDIANTE_REL = KDSA010A.ESTUDIANTE_REL";
        $mDatos = $m_cnx_MySQL->prepare($msConsulta);
        $mDatos->execute([$msCobro]);
        $numRegistros = $mDatos->rowCount();

        $archivo = fopen($nombreArchivo, "w");
        fwrite($archivo, "Number;Value1;Value2;Value3;Value4;Value5" . PHP_EOL);

        while ($mFila = $mDatos->fetch())
        {
            $msMatricula = $mFila["MATRICULA_REL"];
            $msNombre = $mFila["NOMBRES_010"] . ' ' . $mFila["APELLIDOS_010"];
            $msCelular = $mFila["CELULAR_010"];
            $msConcepto = $mFila["CONCEPTO_050"];

            $FechaDividida = explode("-", $mFila["FECHAPREVISTA_050"]);
            $Anno = $FechaDividida[0];
            $Mes = $FechaDividida[1];
            $Dia = $FechaDividida[2];

            switch ($Mes)
            {
                case "01":
                    $NombreMes = "enero";
                    break;
                case "02":
                    $NombreMes = "febrero";
                    break;
                case "03":
                    $NombreMes = "marzo";
                    break;
                case "04":
                    $NombreMes = "abril";
                    break;
                case "05":
                    $NombreMes = "mayo";
                    break;
                case "06":
                    $NombreMes = "junio";
                    break;
                case "07":
                    $NombreMes = "julio";
                    break;
                case "08":
                    $NombreMes = "agosto";
                    break;
                case "09":
                    $NombreMes = "septiembre";
                    break;
                case "10":
                    $NombreMes = "octubre";
                    break;
                case "11":
                    $NombreMes = "noviembre";
                    break;
                case "12":
                    $NombreMes = "diciembre";
                    break;
            }
            $msFechaPrevista = $Dia . " de " . $NombreMes . " de " . $Anno;

            fwrite($archivo, $msCelular . ';¡Hola ' . $msNombre . '!;Te recordamos que tu próximo pago es el ' . $msFechaPrevista . '.;Mantente al día para evitar incurrir en mora.;;' . PHP_EOL);
        }

        fclose($archivo);
    }
?>