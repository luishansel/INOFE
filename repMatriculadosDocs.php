<?php
session_start();
if (!isset($_SESSION["gnVerifica"]) or $_SESSION["gnVerifica"] != 1)
{
    echo('<meta http-equiv="Refresh" content="0;url=index.php"/>');
    exit('');
}

include ("MasterWeb.php");
require_once ("funciones/fxGeneral.php");
$m_cnx_MySQL = fxAbrirConexion();

$codCurso = trim($_POST["KDSA"]);
$msConsulta = "select KDSA030A.MATRICULA_REL, KDSA030A.ESTUDIANTE_REL, concat(trim(APELLIDOS_010), ', ', trim(NOMBRES_010)) as NOMBRECOMPLETO ";
$msConsulta .= "from KDSA030A, KDSA010A where KDSA030A.ESTUDIANTE_REL = KDSA010A.ESTUDIANTE_REL and KDSA030A.ESTADO_030 <> 4 ";
$msConsulta .= "and KDSA030A.CURSO_REL = ?";
$rsEstudiantes = $m_cnx_MySQL->prepare($msConsulta);
$rsEstudiantes->execute([$codCurso]);

$msConsulta = "select concat(NOMBRE_020, ' (', CONVOCATORIA_020, '/G', GRUPO_020, ')') as CURSO from KDSA020A where CURSO_REL = ?";
$mDatos = $m_cnx_MySQL->prepare($msConsulta);
$mDatos->execute([$codCurso]);
$Fila = $mDatos->fetch();
$NombreCurso = $Fila["CURSO"];

?>
<div class="container text-left">
    <div id="DivContenido">
        <div class="row">
            <div class="col-xs-12 col-md-12">
                <form id="repMatriculaDocs" name="repMatriculaDocs">
                    <div class="row">
                        <div class="col-xs-12 col-md-12">
                            <label class="col-sm-auto col-md-12 col-form-label" style="color:blue; font-size:x-large; font-weight: bold">DOCUMENTOS DE LOS ESTUDIANTES MATRICULADOS</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 col-md-12">
                            <?php echo('<label class="col-sm-auto col-md-12 col-form-label" style="color:red; font-size:large; font-weight: bold">' . trim($NombreCurso) . '</label>'); ?>
                        </div>
                    </div>

                    <div class="col-xs-12 col-md-12">
                        <?php
                            while ($fEstudiantes = $rsEstudiantes->fetch())
                            {
                                $msCodMatricula = $fEstudiantes["MATRICULA_REL"];
                                $msCodEstudiante = $fEstudiantes["ESTUDIANTE_REL"];
                                $msNomEstudiante = $fEstudiantes["NOMBRECOMPLETO"];

                                echo('<div class="row">');
                                echo('<label class="col-sm-auto col-md-12 col-form-label">' . trim($msCodMatricula) . '-' . trim($msNomEstudiante) . '</label>');
                                echo('</div>');
                                echo('<div class="row">');

                                $msConsulta = "select DESC_011, RUTA_011 from KDSA011A where ESTUDIANTE_REL = ?";
                                $rsDocumentos = $m_cnx_MySQL->prepare($msConsulta);
                                $rsDocumentos->execute([$msCodEstudiante]);
                                $Registros = $rsDocumentos->rowCount();

                                if ($Registros == 0)
                                {
                                    echo('<div class="col-md-auto">');
                                    echo('<label class="col-form-label">[Sin documentos]</label>');
                                    echo('</div>');
                                }
                                else
                                {
                                    while ($fDocumentos = $rsDocumentos->fetch())
                                    {
                                        $msRuta = $fDocumentos["RUTA_011"];
                                        echo('<div class="col-xs-4 col-md-3">');
                                        echo('<a href="' . trim($msRuta) . '" target="_blank"><img src="' . trim($msRuta) . '" width="100%" height="150px" /></a>');
                                        echo('</div>');
                                    }
                                }

                                echo('</div>');
                            }
                        ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>