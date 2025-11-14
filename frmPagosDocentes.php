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
		$PermisoUsuario = fxPermisoUsuario("repPagosDocentes", $mbAgregar, $mbModificar, $mbBorrar, $mbAnular);
		
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
				$FechaIni = date("Y-m-d", time());
				$FechaFin = date("Y-m-d", time());
			?>
			<div class="container">
            <div id="DivContenido">
			<div class = "row">
				<div class="col-xs-12 col-md-11">
					<div class="degradado"><strong>Soporte de pagos a docentes</strong></div>
				</div>
			</div>

            <div class="row">
            	<div class="col-xs-12 col-xs-offset-none col-md-12 col-md-offset-2">
                    <form name="frmPagosDocentes" action="repPagosDocentes.php" target="_blank" method="post" onsubmit="return verificarFormulario()">
                        <div class = "form-group row">
						<label for="dtpFechaIni" class="col-sm-12 col-md-2 col-form-label">Fecha inicial</label>
                            <div class="col-sm-12 col-md-3">
                            	<?php echo('<input type="date" class="form-control" id="dtpFechaIni" name="dtpFechaIni" value="' . $FechaIni . '" />');?>
                            </div>
                        </div>
                        
                        <div class = "form-group row">
						<label for="dtpFechaFin" class="col-sm-12 col-md-2 col-form-label">Fecha final</label>
                            <div class="col-sm-12 col-md-3">
                            	<?php echo('<input type="date" class="form-control" id="dtpFechaFin" name="dtpFechaFin" value="' . $FechaFin . '" />');?>
                            </div>
                        </div>
                        
                        <div class = "form-group row">
                        	<label for="cboDocente" class="col-sm-2 col-md-2 col-form-label">Docente</label>
                        	<div class="col-sm-12 col-md-5">
								<select class="form-control" id="cboDocente" name="cboDocente">
									<?php
										$msConsulta = "select DOCENTE_REL, NOMBRE_100 from KDSA100A where ACTIVO_100 = 1 order by NOMBRE_100";
										$m_cnx_MySQL = fxAbrirConexion();
										$mDatos = $m_cnx_MySQL->prepare($msConsulta);
										$mDatos->execute();
										while ($Fila = $mDatos->fetch())
										{
											$Valor = rtrim($Fila["DOCENTE_REL"]);
											$Texto = rtrim($Fila["NOMBRE_100"]);
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
		if (document.getElementById('dtpFechaIni').value > document.getElementById('dtpFechaFin').value)
		{
			$.messager.alert('KDSA','La Fecha Inicial es mayor que la Final.','warning');
			return false;
		}
		
		return true;
	}
</script>