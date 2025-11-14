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
		$PermisoUsuario = fxPermisoUsuario("repIngresos", $mbAgregar, $mbModificar, $mbBorrar, $mbAnular);
		
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
					<div class="degradado"><strong>Ingresos</strong></div>
				</div>
			</div>

            <div class="row">
            	<div class="col-xs-12 col-xs-offset-none col-md-12 col-md-offset-2">
                    <form id="frmIngresos" name="frmIngresos">
						<div class = "form-group row">
                            <label for="optTipo" class="col-sm-12 col-md-2 form-label">Tipo de salida</label>
                            <div class="col-sm-12 col-md-4">
                                <div class = "radio">
                                    <input type="radio" id="optSalida1" name="optSalida" value="0" onchange="activaControl()" checked /> Reporte
                                    <input type="radio" id="optSalida2" name="optSalida" value="1" onchange="activaControl()" /> Consulta dinámica
                                </div>
                            </div>
                        </div>

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
                            <label for="optMoneda" class="col-sm-12 col-md-2 form-label">Moneda</label>
                            <div class="col-sm-12 col-md-4">
                                <div class = "radio">
                                    <input type="radio" id="optMoneda1" name="optMoneda" value="0" checked /> Córdobas
                                    <input type="radio" id="optMoneda2" name="optMoneda" value="1" /> Dólares
                                </div>
                            </div>
                        </div>

						<div id="divParametros">
							<div class = "form-group row">
								<label for="optSerie" class="col-sm-12 col-md-2 form-label">Serie</label>
								<div class="col-sm-12 col-md-4">
									<div class = "radio">
										<input type="radio" id="optSerie1" name="optSerie" value="A" checked /> A
										<input type="radio" id="optSerie2" name="optSerie" value="B" /> B
									</div>
								</div>
							</div>
							
							<div class = "form-group row">
								<label for="optTipo" class="col-sm-12 col-md-2 form-label">Tipo de reporte</label>
								<div class="col-sm-12 col-md-4">
									<div class = "radio">
										<input type="radio" id="optTipo1" name="optTipo" value="0" checked /> Ingresos generales
										<input type="radio" id="optTipo2" name="optTipo" value="1" /> Ingresos por mora
									</div>
								</div>
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
			$.messager.alert('INOFE','La Fecha Inicial es mayor que la Final.','warning');
			return false;
		}
		
		return true;
	}
	
	function activaControl()
	{
		if (document.getElementById('optSalida2').checked)
			document.getElementById('divParametros').hidden = true;
		else
			document.getElementById('divParametros').hidden = false;
	}

	$('form').submit(function(e){
		e.preventDefault();

		if (verificarFormulario() == true)
		{
			var fechaIni;
			var fechaFin;
			var moneda;
			var serie;
			
			fechaIni = document.getElementById("dtpFechaIni").value;
			fechaFin = document.getElementById("dtpFechaFin").value;
			
			if (document.getElementById("optMoneda1").checked)
				moneda = '0';
			else
				moneda = '1';
				
			if (document.getElementById("optSerie1").checked)
				serie = 'A';
			else
				serie = 'B';

			if (document.getElementById("optSalida1").checked)
			{
				if (document.getElementById("optTipo1").checked) {
					$.redirect("repIngresos.php", {dtpFechaIni: fechaIni, dtpFechaFin: fechaFin, optSerie: serie, optMoneda: moneda}, "POST", "_blank");
				}
				else {
					$.redirect("repIngresosMora.php", {dtpFechaIni: fechaIni, dtpFechaFin: fechaFin, optSerie: serie, optMoneda: moneda}, "POST", "_blank");
				}
			}
			else
			{
				$.redirect("consIngresos.php", {dtpFechaIni: fechaIni, dtpFechaFin: fechaFin, optSerie: serie, optMoneda: moneda}, "POST", "_blank");
			}
		}
	});
</script>