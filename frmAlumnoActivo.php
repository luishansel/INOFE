<?php
	session_start();
	if (!isset($_SESSION["gnVerifica"]) or $_SESSION["gnVerifica"] != 1)
	{
		echo('<meta http-equiv="Refresh" content="0;url=index.php"/>');
		exit('');
	}
	
	include ("MasterWeb.php");
	require_once ("funciones/fxGeneral.php");
	require_once ("funciones/fxUsuarios.php");
    require_once ("funciones/fxFirmas.php");
    $m_cnx_MySQL = fxAbrirConexion();
	$Registro = fxVerificaUsuario();
	
	if ($Registro == 0)
	{
?>

<div class="container text-center">
    <div id="DivContenido">
        <img src="imagenes/errordeacceso.png" />
    </div>
</div>
<?php }
	else
	{
		$Administrador = fxVerificaAdministrador();
		$PermisoUsuario = fxPermisoUsuario("repAlumnoActivo");
		
		if ($Administrador == 0 and $PermisoUsuario == 0)
		{ ?>
            <div class="container text-center">
                <div id="DivContenido">
                    <img src="imagenes/errordeacceso.png" />
                </div>
            </div>
        <?php 
        }
		else
		{
			if (isset($_POST["msCurso"]))
			{
				$msCurso = $_POST["msCurso"];
				$msMatricula = "";
                $mnIdentificacion = $_POST["mnIdentificacion"];
                $msFirma = $_POST["msFirma"];
			}
            else
            {
                $msCurso = "";
                $msMatricula = "";
                $mnIdentificacion = 0;
                $msFirma = "";
            }
        }
	?>
<div class="container text-left">
    <div id="DivContenido">
        <div class = "row">
            <div class="col-xs-12 col-md-11">
                <div class="degradado"><strong>Constancia de alumno activo</strong></div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-xs-offset-none col-md-12 col-md-offset-1">
                <form id="frmAlumnoActivo" name="frmAlumnoActivo">
                    <div class="form-group row">
                        <label for="cboCurso" class="col-sm-12 col-md-2 col-form-label">Curso</label>
                        <div class="col-sm-12 col-md-6">
                            <?php
                                echo('<select class="form-control" id="cboCurso" name="cboCurso" onchange="fxCambiaCurso()">');

                                $msConsulta = "select distinct KDSA020A.CURSO_REL, concat(NOMBRE_020, ' (', CONVOCATORIA_020, '/G', GRUPO_020, ')') as NOMBRE ";
                                $msConsulta .= "from KDSA020A where ACTIVO_020 = 1 and FECHAINI_020 < CURDATE()";
                                $mDatos = $m_cnx_MySQL->prepare($msConsulta);
                                $mDatos->execute();

                                while ($Fila = $mDatos->fetch())
                                {
                                    $Curso = rtrim($Fila["CURSO_REL"]);
                                    $Texto = rtrim($Fila["NOMBRE"]);
                                    
                                    if ($msCurso == "")
                                        $msCurso = $Curso;
                                    
                                    if ($msCurso == $Curso)
                                        echo("<option value='" . $Curso . "' selected>" . $Texto . "</option>");
                                    else
                                        echo("<option value='" . $Curso . "'>" . $Texto . "</option>");
                                }
                            ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="cboMatricula" class="col-sm-12 col-md-2 col-form-label">Estudiantes</label>
                        <div class="col-sm-12 col-md-6">
                            <?php
                                echo('<select class="form-control" id="cboMatricula" name="cboMatricula">');
                                $msConsulta = "select MATRICULA_REL, concat_ws(' ', APELLIDOS_010, NOMBRES_010) as ESTUDIANTE from KDSA030A, KDSA010A ";
                                $msConsulta .= "where KDSA030A.ESTUDIANTE_REL = KDSA010A.ESTUDIANTE_REL and CURSO_REL = ? and ESTADO_030 = 0 order by APELLIDOS_010";
                                $mDatos = $m_cnx_MySQL->prepare($msConsulta);
                                $mDatos->execute([$msCurso]);

                                while ($Fila = $mDatos->fetch())
                                {
                                    $Matricula = rtrim($Fila["MATRICULA_REL"]);
                                    $Estudiante = rtrim($Fila["ESTUDIANTE"]);

                                    $msResponse .= "<option value='" . $Matricula . "'>" . $Estudiante . "</option>";
                                }
                                echo($msResponse);
                            ?>
                            </select>
                        </div>
                    </div>

                    <div class = "form-group row">
                        <label for="optIdentificacion" class="col-sm-12 col-md-2 form-label">Identificación</label>
                        <div class="col-sm-12 col-md-4">
                            <div class = "radio">
                            <?php
                                if ($mnIdentificacion == 0)
                                {
                                    echo('<input type="radio" id="optIdentificacion1" name="optIdentificacion" value="0" checked="checked" /> Cédula de identidad <input type="radio" id="optIdentificacion2" name="optIdentificacion" value="1" /> Cédula de residencia');
                                }
                                else
                                {
                                    echo('<input type="radio" id="optIdentificacion1" name="optIdentificacion" value="0" /> Cédula de identidad <input type="radio" id="optIdentificacion2" name="optIdentificacion" value="1" checked="checked" /> Cédula de residencia');
                                }
                            ?>
                            </div>
                        </div>
                    </div>

                    <div class = "form-group row">
                        <label for="cboFirma" class="col-sm-12 col-md-2 form-label">Firma suscrita</label>
                        <div class="col-sm-12 col-md-6">
                            <select class="form-control" id="cboFirma" name="cboFirma">
                                <?php
                                    $mDatos = fxDevuelveFirma();
                                    
                                    while ($Fila = $mDatos->fetch())
                                    {
                                        $Valor = trim($Fila["FIRMA_REL"]);
                                        $Texto = trim($Fila["NOMBRE_008"]) . ' / ' . trim($Fila["CARGO_008"]);
                                        
                                        if ($msFirma == "")
                                        {
                                            $msFirma = $Valor;
                                            echo("<option value='" . $Valor . "'>" . $Texto . "</option>");
                                        }
                                        else
                                        {
                                            if ($msFirma == $Valor)
                                                echo("<option value='" . $Valor . "' selected>" . $Texto . "</option>");
                                            else
                                                echo("<option value='" . $Valor . "'>" . $Texto . "</option>");
                                        }
                                    }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-auto col-xs-offset-none col-md-12 col-md-offset-2">
                            <button id="print" type="button" class="btn btn-warning"/>Imprimir</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
<?php } ?>
<script>
    $("#print").on("click", function() {
        var codMatricula = $("#cboMatricula").val();
        var codFirma = $("#cboFirma").val();

        if (document.getElementById('optIdentificacion1').checked)
            mnIdentificacion = 0;
        else
            mnIdentificacion = 1;

        $.redirect("repAlumnoActivo.php", {msCodigo: codMatricula, mnIdentificacion: mnIdentificacion, msFirmaRegistro: codFirma}, "POST", "_blank");
    });

    function fxCambiaCurso() {
        var cboCurso = $("#cboCurso").val();
        var cboFirma = $("#cboFirma").val();
        if (document.getElementById('optIdentificacion1').checked)
            mnIdentificacion = 0;
        else
            mnIdentificacion = 1;
 
        $.redirect("frmAlumnoActivo.php", {msCurso: cboCurso, mnIdentificacion: mnIdentificacion, msFirma: cboFirma}, "POST");
    }
</script>