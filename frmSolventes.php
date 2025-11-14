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
		$PermisoUsuario = fxPermisoUsuario("repSolventes", $mbAgregar, $mbModificar, $mbBorrar, $mbAnular);
		
		if ($Administrador == 0 and $PermisoUsuario == 0)
		{?>
        <div class="container text-center">
            <div id="DivContenido">
                <img src="imagenes/errordeacceso.png"/>
            </div>
        </div>
		<?php }
		else
			{
				$FechaFin = date("Y-m-d", time());
			?>
			<div class="container">
            <div id="DivContenido">
			<div class = "row">
				<div class="col-xs-12 col-md-11">
					<div class="degradado"><strong>Estudiantes solventes</strong></div>
				</div>
			</div>
            <div class="row">
            	<div class="col-xs-12 col-xs-offset-none col-md-12 col-md-offset-2">
                    <form name="frmSolventes" action="repSolventes.php" target="_blank" method="post" onsubmit="return verificarFormulario()">
                        <div class = "form-group row">
						<label for="dtpFechaFin" class="col-sm-12 col-md-2 col-form-label">Hasta la fecha</label>
                            <div class="col-sm-12 col-md-3">
                            	<?php echo('<input type="date" class="form-control" id="dtpFechaFin" name="dtpFechaFin" value="' . $FechaFin . '" />');?>
                            </div>
                        </div>
                        
                        <div class="form-group row">
                        <label for="cboCurso" class="col-xs-12 col-md-2 col-form-label">Curso</label>
                            <div class="col-sm-12 col-md-7">
                                <select class="form-control" id="cboCurso" name="cboCurso">
                                    <?php
										$msConsulta = "select CURSO_REL, NOMBRE_020, CONVOCATORIA_020, GRUPO_020 from KDSA020A where TIPO_020 in (1, 2, 4) order by CURSO_REL desc";
                                        $m_cnx_MySQL = fxAbrirConexion();
										$mDatos = $m_cnx_MySQL->prepare($msConsulta);
                                        $mDatos->execute();
										
                                        while ($Fila = $mDatos->fetch())
                                        {
                                            $Valor = rtrim($Fila["CURSO_REL"]);
                                            $Texto = rtrim($Fila["NOMBRE_020"]) . " (" . rtrim($Fila["CONVOCATORIA_020"]) . " / G" . rtrim($Fila["GRUPO_020"]) . ")";
											
                                           	echo("<option value='" . $Valor . "'>" . $Texto . "</option>");
                                        }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class = "row">
                            <div class="col-auto col-xs-offset-none col-md-2 col-md-offset-2">
                                <input type="submit" id="Imprimir" name="Imprimir" value="Imprimir" class="btn btn-warning" />
                            </div>
                        </div>
                    </form>			
			<?php	}
			}
		?>
        	</div>
        </div>
		</div>
        </div>
</body>
</html>
<script type='text/javascript'>
	function verificarFormulario()
	{
		var fechaHoy = new Date();
		var yySQL = fechaHoy.getFullYear();
		var mmSQL = fechaHoy.getMonth() +1;
		var ddSQL = fechaHoy.getDate();
		var fechaSQL;
		
		if (mmSQL < 10) {mmSQL = '0' + mmSQL}
		if (ddSQL < 10) {ddSQL = '0' + ddSQL}
		fechaSQL = yySQL + '-' + mmSQL + '-' + ddSQL;

		if (document.getElementById('dtpFechaFin').value > fechaSQL)
		{
			$.messager.alert('INOFE','La Fecha debe ser menor o igual que Hoy.','warning');
			return false;
		}
		
		return true;
	}
</script>