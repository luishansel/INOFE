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
		$PermisoUsuario = fxPermisoUsuario("repDesercion", $mbAgregar, $mbModificar, $mbBorrar, $mbAnular);
		
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
					<div class="degradado"><strong>Deserciones</strong></div>
				</div>
			</div>

            <div class="row">
            	<div class="col-xs-12 col-xs-offset-none col-md-12 col-md-offset-2">
                    <form id="frmDesercion" name="frmDesercion">
						<div class = "form-group row">
                            <label for="optTipo" class="col-sm-12 col-md-2 form-label">Tipo de reporte</label>
                            <div class="col-sm-12 col-md-4">
                                <div class = "radio">
                                    <input type="radio" id="optSalida1" name="optSalida" value="0" onchange="activaControl()" checked /> Cursos activos
                                    <input type="radio" id="optSalida2" name="optSalida" value="1" onchange="activaControl()" /> Fecha de inicio de curso
                                </div>
                            </div>
                        </div>

                        <div class = "form-group row">
						<label for="dtpFechaIni" class="col-sm-12 col-md-2 col-form-label">Fecha inicial</label>
                            <div class="col-sm-12 col-md-2">
                            	<?php echo('<input type="date" class="form-control" id="dtpFechaIni" name="dtpFechaIni" value="' . $FechaIni . '" disabled />');?>
                            </div>
                        </div>
                        
                        <div class = "form-group row">
						<label for="dtpFechaFin" class="col-sm-12 col-md-2 col-form-label">Fecha final</label>
                            <div class="col-sm-12 col-md-2">
                            	<?php echo('<input type="date" class="form-control" id="dtpFechaFin" name="dtpFechaFin" value="' . $FechaFin . '" disabled />');?>
                            </div>
                        </div>
                                            
                        <div class = "form-group form-check row">
                            <div class="col-auto col-xs-offset-none col-md-2 col-md-offset-2">
                                <input type="checkbox" id="chkExcel" name="chkExcel" class="form-check-input" />
								<label class="form-check-label" for="chkExcel">Exportar a Excel</label>
                            </div>
                        </div>

						<div class = "row">
                            <div class="col-auto col-xs-offset-none col-md-2 col-md-offset-2">
                                <input type="submit" id="Aceptar" name="Aceptar" value="Aceptar" class="btn btn-warning" />
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
	
	function activaControl()
	{
		if (document.getElementById('optSalida2').checked)
		{
			document.getElementById('dtpFechaIni').disabled = false;
			document.getElementById('dtpFechaFin').disabled = false;
		}
		else
		{
			document.getElementById('dtpFechaIni').disabled = true;
			document.getElementById('dtpFechaFin').disabled = true;
		}
	}

	$('form').submit(function(e){
		e.preventDefault();

		if (verificarFormulario() == true)
		{
			var fechaIni = document.getElementById("dtpFechaIni").value;
			var fechaFin = document.getElementById("dtpFechaFin").value;
			
			if (document.getElementById("chkExcel").checked)
			{
				if (document.getElementById("optSalida1").checked)
					$.redirect("exlDeserciones.php", {fechaIni: fechaIni, fechaFin: fechaFin, activos: 1}, "POST", "_blank");
				else
					$.redirect("exlDeserciones.php", {fechaIni: fechaIni, fechaFin: fechaFin, activos: 0}, "POST", "_blank");
			}
			else
			{
				if (document.getElementById("optSalida1").checked)
					$.redirect("repDeserciones.php", {fechaIni: fechaIni, fechaFin: fechaFin, activos: 1}, "POST", "_blank");
				else
					$.redirect("repDeserciones.php", {fechaIni: fechaIni, fechaFin: fechaFin, activos: 0}, "POST", "_blank");
			}
		}
	});
</script>