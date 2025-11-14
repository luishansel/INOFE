<?php
    require_once ("funciones/fxGeneral.php");
    $m_cnx_MySQL = fxAbrirConexion();

    $msConsulta = "select KDSA051A.COBRO_REL, MATRICULA_REL, ADEUDADO_051, ABONADO_051 from KDSA051A, KDSA050A ";
	$msConsulta .= "where KDSA051A.COBRO_REL = KDSA050A.COBRO_REL and ";
	$msConsulta .= "ADEUDADO_051 > 0 and ANULADO_051 = 0 and EXONERADO_051 = 0 and FECHAPREVISTA_050 <= '2023-11-30' ";
    $msConsulta .= "and TIPO_050 = 1 order by COBRO_REL, MATRICULA_REL";
    
    $mDatos = $m_cnx_MySQL->prepare($msConsulta);
    $mDatos->execute();
    while ($Fila = $mDatos->fetch())
    {
        $msCobro = $Fila["COBRO_REL"];
        $msMatricula = $Fila["MATRICULA_REL"];
        $mnAdeudado = $Fila["ADEUDADO_051"];
        $mnAbonado = $Fila["ABONADO_051"];

        if ($mnAbonado > 0)
            $msConsulta = "update KDSA051A set ADEUDADO_051 = 0, PAGADO_051 = 1 where COBRO_REL = ? and MATRICULA_REL = ?";
        else
            $msConsulta = "update KDSA051A set ANULADO_051 = 1 where COBRO_REL = ? and MATRICULA_REL = ?";
        
        $mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
        $mAuxiliar->execute([$msCobro, $msMatricula]);

        $msConsulta = "select KDS_COBRO_REL, ANULADO_050 from KDSA050A where COBRO_REL = ? limit 1";
        $mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
        $mAuxiliar->execute([$msCobro]);
        $auxFila = $mAuxiliar->fetch();
        $msCobro050 = $auxFila["KDS_COBRO_REL"];
        $mbAnulado050 = $auxFila["ANULADO_050"];

        if ($mbAnulado050 == 0)
        {
            $msConsulta = "select ABONADO_051 from KDSA051A where COBRO_REL = ? and MATRICULA_REL = ? and ADEUDADO_051 > 0 and ANULADO_051 = 0 and EXONERADO_051 = 0";
            $mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
            $mAuxiliar->execute([$msCobro050, $msMatricula]);
            $mnRegistros = $mAuxiliar->rowCount();

            if ($mnRegistros > 0)
            {
                $msConsulta = "select ABONADO_051 from KDSA051A where COBRO_REL = ? and MATRICULA_REL = ? and ADEUDADO_051 > 0 and ANULADO_051 = 0 and EXONERADO_051 = 0";
                $mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
                $mAuxiliar->execute([$msCobro050, $msMatricula]);
                $auxFila = $mAuxiliar->fetch();
                $mnAbonado051 = $auxFila["ABONADO_051"];

                if ($mnAbonado051 > 0)
                    $msConsulta = "update KDSA051A set ADEUDADO_051 = 0, PAGADO_051 = 1 where COBRO_REL = ? and MATRICULA_REL = ?";
                else
                    $msConsulta = "update KDSA051A set ANULADO_051 = 1 where COBRO_REL = ? and MATRICULA_REL = ?";

                $mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
                $mAuxiliar->execute([$msCobro050, $msMatricula]);
            }

            $msConsulta = "update KDSA050A set ANULADO_050 = 1 where COBRO_REL = ?";
            $mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
            $mAuxiliar->execute([$msCobro050]);
        }
    }
?>