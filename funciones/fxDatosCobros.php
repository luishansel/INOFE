<?php
    require_once ("fxGeneral.php");

    if (isset($_POST["msCurso"]))
    {
        $m_cnx_MySQL = fxAbrirConexion();
        $msCurso = $_POST["msCurso"];
        $msResultado = "[";

        $msConsulta = "select COBRO_REL, CONCEPTO_050, FECHAPREVISTA_050 FROM KDSA050A where CURSO_REL = ? and TIPO_050 <> 1";
        $mDatos = $m_cnx_MySQL->prepare($msConsulta);
        $mDatos->execute([$msCurso]);

        while ($mFila = $mDatos->fetch())
        {
            $msCobro = trim($mFila["COBRO_REL"]);
            $msConcepto = $mFila["CONCEPTO_050"];
            $FechaDividida = explode("-", $mFila["FECHAPREVISTA_050"]);
            $Anno = $FechaDividida[0];
            $Mes = $FechaDividida[1];
            $Dia = $FechaDividida[2];

            switch ($Mes)
            {
                case "01":
                    $NombreMes = "Ene";
                    break;
                case "02":
                    $NombreMes = "Feb";
                    break;
                case "03":
                    $NombreMes = "Mar";
                    break;
                case "04":
                    $NombreMes = "Abr";
                    break;
                case "05":
                    $NombreMes = "May";
                    break;
                case "06":
                    $NombreMes = "Jun";
                    break;
                case "07":
                    $NombreMes = "Jul";
                    break;
                case "08":
                    $NombreMes = "Ago";
                    break;
                case "09":
                    $NombreMes = "Sep";
                    break;
                case "10":
                    $NombreMes = "Oct";
                    break;
                case "11":
                    $NombreMes = "Nov";
                    break;
                case "12":
                    $NombreMes = "Dic";
                    break;
            }
            $msFechaPrevista = $Dia . "-" . $NombreMes . "-" . $Anno;
            $msFecha = $Anno . "-" . $Mes . "-" . $Dia;
                
            $msResultado .= '{';
            $msResultado .= '"COBRO_REL":"' . $msCobro . '",';
            $msResultado .= '"CONCEPTO_050":"' . $msConcepto . '",';
            $msResultado .= '"FECHAPREVISTA_050":"' . $msFechaPrevista . '",';
            $msResultado .= '"FECHA":"' . $msFecha . '"';
            $msResultado .= '},';
        }
        $msResultado = substr($msResultado, 0, -1);
        $msResultado .= "]";
        echo ($msResultado);
    }
?>