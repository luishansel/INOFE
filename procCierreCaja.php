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
	require_once ("funciones/fxCierreCaja.php");
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
		$PermisoUsuario = fxPermisoUsuario("procCierreCaja", $mbAgregar, $mbModificar, $mbBorrar, $mbAnular);
		
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
				if (isset($_POST["dtpFecha"]))
				{
					$Fecha = $_POST["dtpFecha"];
					$Operacion = $_POST["optOperacion"];
					if ($Operacion == 0)
					{
						fxGuardarCierreCaja($Fecha);
						echo("<script>$.messager.alert('KDSA','Fecha cerrada.','warning');</script>");
					}
					else
					{
						fxBorrarCierreCaja($Fecha);
						echo("<script>$.messager.alert('KDSA','Fecha reabierta.','warning');</script>");
					}
				}
				else
					$Fecha = date("Y-m-d");
			?>
			<div class="container">
				<div id="DivContenido">
				<div class = "row">
					<div class="col-xs-12 col-md-11">
						<div class="degradado"><strong>Cierre de caja</strong></div>
					</div>
				</div>

				<div class="row">
					<div class="col-xs-12 col-xs-offset-none col-md-12 col-md-offset-2">
						<form name="procCierreCaja" id="procCierreCaja">
							<div class = "form-group row">
							<label for="dtpFechaIni" class="col-sm-12 col-md-2 col-form-label">Fecha de cierre</label>
								<div class="col-sm-12 col-md-3">
									<?php echo('<input type="date" class="form-control" id="dtpFecha" name="dtpFecha" value="' . $Fecha . '" onchange="fechaCerrada()" />');?>
									<input type="hidden" class="form-control" id="txnFechaCerrada" name="txnFechaCerrada" value="" />
								</div>
							</div>

							<div class = "form-group row">
							<label for="optOperacion" class="col-sm-12 col-md-2 col-form-label">Operación</label>
								<div class="col-sm-12 col-md-7">
									<div class = "radio">
										<?php
											if ($Administrador == 1)
											{
												echo('<input type="radio" id="optOperacion1" name="optOperacion" value="0" checked /> Cierre&nbsp;');
												echo('<input type="radio" id="optOperacion2" name="optOperacion" value="1" /> Reapertura');
											}
											else
											{
												echo('<input type="radio" id="optOperacion1" name="optOperacion" value="0" checked disabled/> Cierre&nbsp;');
												echo('<input type="radio" id="optOperacion2" name="optOperacion" value="1" disabled /> Reapertura');
											}
										?>
									</div>
								</div>
							</div>
							
							<div class = "row">
								<div class="col-auto col-xs-offset-none col-md-2 col-md-offset-2">
									<input type="submit" id="Aceptar" name="Aceptar" value="Aceptar" class="btn btn-warning" />
									<input type="button" id="Imprimir" name="Imprimir" value="Imprimir" class="btn btn-warning" onclick="imprimir()" />
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
<script>
	function fechaCerrada()
	{
		var datos = new FormData();
		var mdFecha = document.getElementById('dtpFecha').value;
		datos.append('fechaCierre', mdFecha);

		$.ajax({
			url: 'funciones/fxDatosCierreCaja.php',
			type: 'post',
			data: datos,
			contentType: false,
			processData: false,
			success: function(response) {
				document.getElementById('txnFechaCerrada').value = response;
			}
		});
    	return false;
	}

	window.onload = function() {
		fechaCerrada();
	}

	function imprimir()
	{
		if (document.getElementById('txnFechaCerrada').value == 1)
			$.redirect("repCierreCaja.php", {KDSA: document.getElementById('dtpFecha').value}, "POST", "_blank");
		else
			$.messager.alert('KDSA','La fecha no se ha cerrado.','warning');
	}

	$('form').submit(function(e){
		e.preventDefault();
		if (document.getElementById('txnFechaCerrada').value == 1)
		{
			if (document.getElementById('optOperacion1').checked)
				$.messager.alert('KDSA','La fecha ya se ha cerrado.','warning');
			else
				$.redirect("procCierreCaja.php", {dtpFecha: document.getElementById('dtpFecha').value, optOperacion: 1}, "POST");
		}
		else
		{
			if (document.getElementById('optOperacion1').checked)
				$.redirect("procCierreCaja.php", {dtpFecha: document.getElementById('dtpFecha').value, optOperacion: 0}, "POST");
			else
				$.messager.alert('KDSA','La fecha no se ha cerrado.','warning');
		}
	});
</script>