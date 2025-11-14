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
	require_once ("funciones/fxFeriados.php");
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
		$PermisoUsuario = fxPermisoUsuario("hrrFeriados");
		
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
			if (isset($_POST["Guardar"]))
			{
				$Codigo = $_POST["dtpFecha"];
				$Descripcion = $_POST["txtDesc"];
				$Dia = $_POST["txtDia"];

				{
					if (fxExisteFeriado($Codigo) == 0)
					{
						fxGuardarFeriado ($Codigo, $Descripcion, $Dia);
						fxAgregarBitacora ($_SESSION["gsUsuario"], "KDSA001A", $Codigo, "", "Agregar");
					}
					else
					{
						fxModificarFeriado ($Codigo, $Descripcion, $Dia);
						fxAgregarBitacora ($_SESSION["gsUsuario"], "KDSA001A", $Codigo, "", "Modificar");
					}
				}
				
				?><meta http-equiv="Refresh" content="0;url=gridFeriados.php"/><?php
			}
			else
			{
				if (isset($_POST["KDSA"]))
					$Codigo = $_POST["KDSA"];
				else
					$Codigo = "";
				
				if ($Codigo == "")
				{
					$Fecha = "";
					$RecordSet = fxDevuelveFeriado(0);
					$Descripcion = "";
				}
				else
				{
					$FechaDividida = explode("-", $Codigo);
					$Anno = $FechaDividida[2];
					$Mes = $FechaDividida[1];
					$Dia = $FechaDividida[0];
					$Fecha = $Anno . "-" . $Mes . "-" . $Dia;
					$RecordSet = fxDevuelveFeriado(0, $Fecha);
					$Fila = $RecordSet->fetch();
					$Descripcion = $Fila["DESC_001"];
				}
	?>
    <div class="container text-left">
    	<div id="DivContenido">
			<div class = "row">
                <div class="col-xs-12 col-xs-offset-none col-md-12 col-md-offset-2">
				<form id="hrrFeriados" name="hrrFeriados" method="POST" action="hrrFeriados.php" onsubmit="return verificarFormulario()">
                	<div class = "form-group row">
                        <label for="dtpFecha" class="col-sm-12 col-md-2 col-form-label">Fecha</label>
                        <div class="col-sm-12 col-md-2">
                        <?php
							if ($Codigo == "")
								echo('<input type="date" class="form-control" id="dtpFecha" name="dtpFecha" value="' . date("Y-m-d") . '" onchange="diaDeLaSemana(this.value)" />');
							else
								echo('<input type="date" class="form-control" id="dtpFecha" name="dtpFecha" value="' . $Fecha . '" readonly />');
                        ?>
                        </div>
                        <div class="col-auto">
                        </div>
                    </div>
                    
                    <div class = "form-group row">
						<label for="txtDia" class="col-sm-12 col-md-2 col-form-label">Día</label>
                        <div class="col-sm-12 col-md-2">
							<input type="text" class="form-control" id="txtDia" name="txtDia" value="" readonly />
                        </div>
                        <div class="col-auto">
                        </div>
                    </div>

					<div class = "form-group row">
						<label for="txtDesc" class="col-sm-12 col-md-2 col-form-label">Descripción</label>
                        <div class="col-sm-12 col-md-7">
						<?php echo('<input type="text" class="form-control" id="txtDesc" name="txtDesc" value="' . $Descripcion . '" />'); ?>
                        </div>
                        <div class="col-auto">
                        </div>
                    </div>
                    
					<div class = "row">
                    	<div class="col-auto col-xs-offset-none col-md-12 col-md-offset-2">
							<input type="submit" id="Guardar" name="Guardar" value="Guardar" class="btn btn-warning" />
                            <input type="button" id="Cancelar" name="Cancelar" value="Cancelar" class="btn btn-warning" onclick="location.href='gridFeriados.php';"/>
                        </div>
                    </div>
				</form>
                </div>
	<?php	}
		}
	}
?>
			</div>
		</div>
	</div>
</body>
</html>
<script type='text/javascript'>
	function verificarFormulario()
	{
		if(document.getElementById('txtDesc').value=="")
		{
			$.messager.alert('INOFE','Falta la Descripción.','warning');
			return false;
		}
		
		return true;
	}

	function diaDeLaSemana(){
		var fecha = document.getElementById('dtpFecha').value;
		var anno = parseInt(fecha.substr(0,4));
		var mes = parseInt(fecha.substr(5,2));
		var dia = parseInt(fecha.substr(8,2));
		var date = new Date(mes + ', ' + dia + ', ' + anno + ' 12:00:00');
		var dias = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
		document.getElementById('txtDia').value = dias[date.getUTCDay()];
	}

	window.onload = function() {
		diaDeLaSemana();
	}
</script>