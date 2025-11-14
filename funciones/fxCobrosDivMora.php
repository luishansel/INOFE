<?php
require_once ("fxGeneral.php");

if (isset($_POST["CodCobro"]) and isset($_POST["CodCurso"]) and isset($_POST["CodMora"])) {
    $msCobro = $_POST["CodCobro"];
    $msCurso = $_POST["CodCurso"];
    $msMora = $_POST["CodMora"];
    $m_cnx_MySQL = fxAbrirConexion();

    $texto = '<div class="form-group row" >'; //DIV Grupo
    $texto .= '<label for="cboCobro" class="col-sm-12 col-md-2 col-form-label">Cuota asociada a la Mora</label>';
    $texto .= '<div class="col-sm-12 col-md-7">'; //DIV Columna
    $texto .= '<select class="form-control" id="cboCobro" name="cboCobro">';
        
    if ($msCobro == "")
        $msConsulta = "select COBRO_REL, CONCEPTO_050 from KDSA050A where TIPO_050 in (0, 6) and ACTIVO_050 = 1 and ANULADO_050 = 0 and CURSO_REL = ?";
    else
        $msConsulta = "select COBRO_REL, CONCEPTO_050 from KDSA050A where TIPO_050 in (0, 6) and CURSO_REL = ?";
    
    $mDatos = $m_cnx_MySQL->prepare($msConsulta);
    $mDatos->execute([$msCurso]);
    while ($Fila = $mDatos->fetch())
    {
        $Valor = rtrim($Fila["COBRO_REL"]);
        $Texto = rtrim($Fila["CONCEPTO_050"]);

        if ($msCobro == "")
        {
            $texto .= "<option value='" . $Valor . "'>" . $Texto . "</option>";
        }
        else
        {
            if ($msMora == $Valor)
                $texto .= "<option value='" . $Valor . "' selected='selected'>" . $Texto . "</option>";
            else
                $texto .= "<option value='" . $Valor . "'>" . $Texto . "</option>";
        }
    }
    
    $texto .= '</select>';
    $texto .= '</div>'; //DIV Columna final
    $texto .= '</div>'; //DIV Grupo final
    
    echo $texto;
} else {
    echo 0;
}
?>