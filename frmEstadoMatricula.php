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
		$PermisoUsuario = fxPermisoUsuario("repEstMatricula", $mbAgregar, $mbModificar, $mbBorrar, $mbAnular);
		
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
					<div class="degradado"><strong>Estado de las matrículas</strong></div>
				</div>
			</div>

            <div class="row">
            	<div class="col-xs-12 col-xs-offset-none col-md-12 col-md-offset-2">
                    <form id="frmIngresos" name="frmIngresos">
						<div class = "form-group row">
                            <label for="optTipo" class="col-sm-12 col-md-3 form-label">Tipo de reporte</label>
                            <div class="col-sm-12 col-md-6">
                                <div class = "radio">
                                    <input type="radio" id="optTipo1" name="optTipo" value="0" checked onclick="activaFechas()" /> Cursos activos (Por fecha inicial del curso)<br>
									<input type="radio" id="optTipo2" name="optTipo" value="1" onclick="activaFechas()" /> Cursos activos (Por fecha final del curso)<br>
                                    <input type="radio" id="optTipo3" name="optTipo" value="2" onclick="activaFechas()" /> Cursos inactivos (Por fecha inicial del curso)<br>
									<input type="radio" id="optTipo4" name="optTipo" value="3" onclick="activaFechas()" /> Cursos inactivos (Por fecha final del curso)
                                </div>
                            </div>
                        </div>

						<div class = "form-group row">
							<label for="chkExcel" class="col-sm-12 col-md-3 col-form-label">Exportar hacia Excel</label>
							<div class="col-sm-12 col-md-2">
								<input type="checkbox" id="chkExcel" name="chkExcel" value="1" />
							</div>
						</div>

                        <div class = "form-group row">
							<label for="dtpFechaIni1" class="col-sm-12 col-md-3 col-form-label">Cursos que iniciaron entre</label>
                            <div class="col-sm-12 col-md-2">
                            	<?php echo('<input type="date" class="form-control" id="dtpFechaIni1" name="dtpFechaIni1" value="' . $FechaIni . '" />');?>
                            </div>
                        </div>
                        
                        <div class = "form-group row">
						<label for="dtpFechaFin1" class="col-sm-12 col-md-3 col-form-label">&nbsp;</label>
                            <div class="col-sm-12 col-md-2">
                            	<?php echo('<input type="date" class="form-control" id="dtpFechaFin1" name="dtpFechaFin1" value="' . $FechaFin . '" />');?>
                            </div>
                        </div>

						<div class = "form-group row">
							<label for="dtpFechaIni2" class="col-sm-12 col-md-3 col-form-label">Cursos que finalizaron entre</label>
                            <div class="col-sm-12 col-md-2">
                            	<?php echo('<input type="date" class="form-control" id="dtpFechaIni2" name="dtpFechaIni2" value="' . $FechaIni . '" disabled />');?>
                            </div>
                        </div>
                        
                        <div class = "form-group row">
						<label for="dtpFechaFin2" class="col-sm-12 col-md-3 col-form-label">&nbsp;</label>
                            <div class="col-sm-12 col-md-2">
                            	<?php echo('<input type="date" class="form-control" id="dtpFechaFin2" name="dtpFechaFin2" value="' . $FechaFin . '" disabled />');?>
                            </div>
                        </div>

                        <div class = "row">
                            <div class="col-auto col-xs-offset-none col-md-3 col-md-offset-3">
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
		if (document.getElementById('optTipo2').checked && document.getElementById('dtpFechaIni1').value > document.getElementById('dtpFechaFin1').value)
		{
			$.messager.alert('KDSA','La Fecha Inicial es mayor que la Final.','warning');
			return false;
		}

		if (document.getElementById('optTipo3').checked && document.getElementById('dtpFechaIni2').value > document.getElementById('dtpFechaFin2').value)
		{
			$.messager.alert('KDSA','La Fecha Inicial es mayor que la Final.','warning');
			return false;
		}
		
		return true;
	}

	function activaFechas() {
		if (document.getElementById('optTipo1').checked)
		{
			document.getElementById('dtpFechaIni1').disabled = false;
			document.getElementById('dtpFechaFin1').disabled = false;
			document.getElementById('dtpFechaIni2').disabled = true;
			document.getElementById('dtpFechaFin2').disabled = true;
		}

		if (document.getElementById('optTipo2').checked)
		{
			document.getElementById('dtpFechaIni1').disabled = true;
			document.getElementById('dtpFechaFin1').disabled = true;
			document.getElementById('dtpFechaIni2').disabled = false;
			document.getElementById('dtpFechaFin2').disabled = false;
		}

		if (document.getElementById('optTipo3').checked)
		{
			document.getElementById('dtpFechaIni1').disabled = false;
			document.getElementById('dtpFechaFin1').disabled = false;
			document.getElementById('dtpFechaIni2').disabled = true;
			document.getElementById('dtpFechaFin2').disabled = true;
		}

		if (document.getElementById('optTipo4').checked)
		{
			document.getElementById('dtpFechaIni1').disabled = true;
			document.getElementById('dtpFechaFin1').disabled = true;
			document.getElementById('dtpFechaIni2').disabled = false;
			document.getElementById('dtpFechaFin2').disabled = false;
		}
	}
	
	$('form').submit(function(e){
		e.preventDefault();

		if (verificarFormulario() == true)
		{
			var mdFechaIni;
			var mdFechaFin;
			
			if (document.getElementById("optTipo1").checked)
			{
				mdFechaIni = document.getElementById("dtpFechaIni1").value;
				mdFechaFin = document.getElementById("dtpFechaFin1").value;
				if (document.getElementById("chkExcel").checked)
					$.redirect("exlEstadoMatActivos.php", {tipoRep: 0, fechaIni: mdFechaIni, fechaFin: mdFechaFin}, "POST", "_blank");
				else
					$.redirect("repEstadoMatActivos.php", {tipoRep: 0, fechaIni: mdFechaIni, fechaFin: mdFechaFin}, "POST", "_blank");
			}

			if (document.getElementById("optTipo2").checked)
			{
				mdFechaIni = document.getElementById("dtpFechaIni1").value;
				mdFechaFin = document.getElementById("dtpFechaFin1").value;
				if (document.getElementById("chkExcel").checked)
					$.redirect("exlEstadoMatActivos.php", {tipoRep: 1, fechaIni: mdFechaIni, fechaFin: mdFechaFin}, "POST", "_blank");
				else
					$.redirect("repEstadoMatActivos.php", {tipoRep: 1, fechaIni: mdFechaIni, fechaFin: mdFechaFin}, "POST", "_blank");
			}

			if (document.getElementById("optTipo3").checked)
			{
				mdFechaIni = document.getElementById("dtpFechaIni1").value;
				mdFechaFin = document.getElementById("dtpFechaFin1").value;
				if (document.getElementById("chkExcel").checked)
					$.redirect("exlEstadoMatInactivos.php", {tipoRep: 0, fechaIni: mdFechaIni, fechaFin: mdFechaFin}, "POST", "_blank");
				else
					$.redirect("repEstadoMatInactivos.php", {tipoRep: 0, fechaIni: mdFechaIni, fechaFin: mdFechaFin}, "POST", "_blank");
			}

			if (document.getElementById("optTipo4").checked)
			{
				mdFechaIni = document.getElementById("dtpFechaIni2").value;
				mdFechaFin = document.getElementById("dtpFechaFin2").value;
				if (document.getElementById("chkExcel").checked)
					$.redirect("exlEstadoMatInactivos.php", {tipoRep: 1, fechaIni: mdFechaIni, fechaFin: mdFechaFin}, "POST", "_blank");
				else
					$.redirect("repEstadoMatInactivos.php", {tipoRep: 1, fechaIni: mdFechaIni, fechaFin: mdFechaFin}, "POST", "_blank");
			}
		}
	});
</script>