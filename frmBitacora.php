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
		$PermisoUsuario = fxPermisoUsuario("repBitacora", $mbAgregar, $mbModificar, $mbBorrar, $mbAnular);
		
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
						<div class="degradado"><strong>Bitácora</strong></div>
					</div>
				</div>

				<div class="row">
					<div class="col-xs-12 col-xs-offset-none col-md-12 col-md-offset-2">
						<form name="frmBitacora" id="frmBitacora">
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
								<label for="optTipo" class="col-sm-12 col-md-2 form-label"></label>
								<div class="col-sm-12 col-md-4">
									<div class = "radio">
										<input type="radio" id="optTipo1" name="optTipo" value="0" onchange="activaCombo()" checked /> Todos los usuarios &ensp; <input type="radio" id="optTipo2" name="optTipo" value="1" onchange="activaCombo()" /> De un usuario
									</div>
								</div>
							</div>

							<div class = "form-group row">
								<label for="cboUsuario" class="col-sm-12 col-md-2 col-form-label">Usuario</label>
								<div class="col-sm-12 col-md-5">
									<select class="form-control" id="cboUsuario" name="cboUsuario" disabled>
										<?php
											$mDatos = fxDevuelveUsuario(1);
											while ($Fila = $mDatos->fetch())
											{
												$Valor = rtrim($Fila["USUARIO_REL"]);
												$Texto = rtrim($Fila["NOMBRE_002"]);
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
		var dtpFechaIni = document.getElementById('dtpFechaIni').value
		var dtpFechaFin = document.getElementById('dtpFechaFin').value

		if (dtpFechaIni > dtpFechaFin)
		{
			$.messager.alert('INOFE','La Fecha Inicial es mayor que la Final.','warning');
			return false;
		}
		
		return true;
	}

	function activaCombo()
	{
		if (document.getElementById('optTipo2').checked)
			document.getElementById('cboUsuario').disabled = false;
		else
			document.getElementById('cboUsuario').disabled = true;
	}

	$('form').submit(function(e){
		e.preventDefault();

		if (verificarFormulario())
			if (document.getElementById('optTipo2').checked)
				$.redirect("repBitacora.php", {dtpFechaIni: document.getElementById('dtpFechaIni').value, dtpFechaFin: document.getElementById('dtpFechaFin').value, txtUsuario: document.getElementById('cboUsuario').value}, "POST", "_blank");
			else
				$.redirect("repBitacora.php", {dtpFechaIni: document.getElementById('dtpFechaIni').value, dtpFechaFin: document.getElementById('dtpFechaFin').value, txtUsuario: ''}, "POST", "_blank");
	});
</script>