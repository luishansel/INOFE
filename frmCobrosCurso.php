<?php
	session_start();
	if (!isset($_SESSION["gnVerifica"]) or $_SESSION["gnVerifica"] != 1)
	{
		echo('<meta http-equiv="Refresh" content="0;url=index.php">');
		exit('');
    }
	
	include ("MasterWeb.php");
	require_once ("funciones/fxGeneral.php");
	require_once ("funciones/fxUsuarios.php");
	$Registro = fxVerificaUsuario();
	
	if ($Registro == 0)
	{
?>
    <div class="container text-center">
    	<div id="DivContenido">
        	<img src="imagenes/errordeacceso.png"/>
        </div>
    </div>
<?php }
	else
	{
		$Administrador = fxVerificaAdministrador();
		$PermisoUsuario = fxPermisoUsuario("repCobrosCurso", $mbAgregar, $mbModificar, $mbBorrar, $mbAnular);
		
		if ($Administrador == 0 and $PermisoUsuario == 0)
		{ ?>
            <div class="container text-center">
                <div id="DivContenido">
                    <img src="imagenes/errordeacceso.png"/>
                </div>
            </div>
		<?php }
		else
		{
		?>
    	<div class="container">
        	<div id="DivContenido">
                <div class = "row">
                    <div class="col-xs-12 col-md-11">
                        <div class="degradado"><strong>Cobros de los cursos</strong></div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 col-xs-offset-none col-md-offset-1">
                        <button id="print" type="button" class="btn btn-warning" >Imprimir</button>
                        
                        <div class = "form-group row">
                            <label for="optTipo" class="col-sm-12 col-md-2 form-label">Tipo de reporte</label>
                            <div class="col-sm-12 col-md-4">
                                <div class = "radio">
                                    <input type="radio" id="optSalida1" name="optSalida" value="0" onchange="activaControl()" checked /> De un curso
                                    <input type="radio" id="optSalida2" name="optSalida" value="1" onchange="activaControl()" /> Por período
                                </div>
                            </div>
                        </div>

                        <div class = "form-group row">
                            <label for="cboCurso" class="col-sm-12 col-md-2 col-form-label">Curso</label>
                            <div class="col-sm-12 col-md-9">
                                <select class="col-sm-12 col-md-10 form-control" id="cboCurso" name="cboCurso">
                                    <?php
                                        $msConsulta = "select CURSO_REL, concat(NOMBRE_020, ' (', CONVOCATORIA_020, '/G', GRUPO_020, ')') as NOMBRE ";
                                        $msConsulta .= "from KDSA020A where ACTIVO_020 = 1 order by CURSO_REL desc";
                                        $m_cnx_MySQL = fxAbrirConexion();
                                        $mDatos = $m_cnx_MySQL->prepare($msConsulta);
			                            $mDatos->execute();
                                        
                                        while ($Fila = $mDatos->fetch())
                                        {
                                            $Curso = rtrim($Fila["CURSO_REL"]);
                                            $Texto = rtrim($Fila["NOMBRE"]);
                                            
                                            echo("<option value='" . $Curso . "'>" . $Texto . "</option>");
                                        }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class = "form-group row">
						    <label for="dtpFechaIni" class="col-sm-12 col-md-2 col-form-label">Fecha inicial</label>
                            <div class="col-sm-12 col-md-2">
                            	<?php echo('<input type="date" class="form-control" id="dtpFechaIni" name="dtpFechaIni" value="' . date('Y-m-d') . '" disabled/>');?>
                            </div>
                        </div>
                        
                        <div class = "form-group row">
						    <label for="dtpFechaFin" class="col-sm-12 col-md-2 col-form-label">Fecha final</label>
                            <div class="col-sm-12 col-md-2">
                            	<?php echo('<input type="date" class="form-control" id="dtpFechaFin" name="dtpFechaFin" value="' . date('Y-m-d') . '" disabled/>');?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    	</div>
<?php }} ?>
<script>
function verificarFormulario()
	{
		if (document.getElementById('dtpFechaIni').value > document.getElementById('dtpFechaFin').value)
		{
			$.messager.alert('INOFE','La Fecha Inicial es mayor que la Final.','warning');
			return false;
		}
		
		return true;
	}
	
	function activaControl()
	{
		if (document.getElementById('optSalida2').checked){
            document.getElementById('cboCurso').disabled = true;
			document.getElementById('dtpFechaIni').disabled = false;
            document.getElementById('dtpFechaFin').disabled = false;
        }
		else{
            document.getElementById('cboCurso').disabled = false;
            document.getElementById('dtpFechaIni').disabled = true;
			document.getElementById('dtpFechaFin').disabled = true;
        }
	}

    $("#print").on("click", function() {
        var codCurso = $('#cboCurso').val();
        var nomCurso = $('#cboCurso option:selected').text();
        var mdFechaIni = $('#dtpFechaIni').val();
        var mdFechaFin = $('#dtpFechaFin').val();
        
        if (document.getElementById('optSalida2').checked){
            if (verificarFormulario())
                $.redirect("repCobrosPeriodo.php", {mdFechaIni: mdFechaIni, mdFechaFin: mdFechaFin}, "POST", "_blank");
        }
        else
            $.redirect("repCobrosCurso.php", {codCurso: codCurso, nomCurso: nomCurso}, "POST", "_blank");
    });
</script>
</body>
</html>
