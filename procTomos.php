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
	require_once ("funciones/fxTomosCertificacion.php");
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
		$PermisoUsuario = fxPermisoUsuario("procTomos");
		
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
			if (isset($_POST["CodTomo"]))
			{
				$mnCtrl = intval($_POST["txnCtrl"]); //Variable de control. Determina si está Insertando (0) o Modificando (1).
				$Codigo = $_POST["CodTomo"];
				$Descripcion = $_POST["txtDescripcion"];
				$Apertura = $_POST["dtpApertura"];
				$Numero = $_POST["txnNumero"];
				$Tipo = $_POST["cboTipo"];
				$UltimoFolio = $_POST["txnUltimoFolio"];
				$UltimaActa = $_POST["txnUltimaActa"];
				$Cerrado = $_POST["optCerrado"];

				if ($mnCtrl == 0)
				{
				$UltimaActa = $_POST["txnUltimaActa"];
					fxGuardarTomo ($Codigo, $Descripcion, $Apertura, $Numero, $Tipo, $UltimoFolio, $UltimaActa, $Cerrado);
					fxAgregarBitacora ($_SESSION["gsUsuario"], "KDSA180A", $Codigo, "", "Agregar");
				}
				else
				{
					fxModificarTomo ($Codigo, $Descripcion, $Apertura, $Numero, $Tipo, $UltimoFolio, $UltimaActa, $Cerrado);
					fxAgregarBitacora ($_SESSION["gsUsuario"], "KDSA180A", $Codigo, "", "Modificar");
				}
									
				?><meta http-equiv="Refresh" content="0;url=gridTomos.php"/><?php
			}
			else
			{
				if (isset($_POST["KDSA"]))
				{
					$Codigo = $_POST["KDSA"];
					$mnCtrl = 1;
				}
				else
				{
					$Codigo = "";
					$mnCtrl = 0;
				}
				
				if ($Codigo != "")
				{
					$mDatos = fxDevuelveTomo(0, $Codigo);
					$Fila = $mDatos->fetch();
					$Descripcion = $Fila["DESCRIPCION_180"];
					$Apertura = $Fila["APERTURA_180"];
					$Numero = $Fila["NUMERO_180"];
					$Tipo = $Fila["TIPO_180"];
					$UltimoFolio = $Fila["ULTIMOFOLIO_180"];
					$UltimaActa = $Fila["ULTIMAACTA_180"];
					$Cerrado = $Fila["CERRADO_180"];
				}
				else
				{
					$Descripcion = "";
					$Apertura = date('Y-m-d');
					$Numero = 0;
					$Tipo = 0;
					$UltimoFolio = 0;
					$UltimaActa = 0;
					$Cerrado = 0;
				}
	?>
    <div class="container text-left">
    	<div id="DivContenido">
			<div class = "row">
				<div class="col-xs-12 col-md-11">
					<div class="degradado"><strong>Administración de tomos</strong></div>
				</div>
			</div>

			<div class = "row">
                <div class="col-xs-12 col-xs-offset-none col-md-11 col-md-offset-2">
					<form id="procTomos" name="procTomos" action="procTomos.php" onsubmit="return verificarFormulario()" method="post">
						<div class = "form-group row">
							<label for="CodTomo" class="col-sm-12 col-md-2 col-form-label">Tomo</label>
							<div class="col-sm-12 col-md-2">
							<?php
								echo('<input type="text" class="form-control" id="CodTomo" name="CodTomo" value="' . $Codigo . '" />');
								echo('<input type="hidden" class="form-control" id="txnCtrl" name="txnCtrl" value="' . $mnCtrl . '" />'); 
							?>
							</div>
						</div>
						
						<div class = "form-group row">
							<label for="txtDescripcion" class="col-sm-12 col-md-2 col-form-label">Descripción</label>
							<div class="col-sm-12 col-md-6">
							<?php echo('<textarea class="form-control" id="txtDescripcion" name="txtDescripcion" rows="3">' . $Descripcion . '</textarea>'); ?>
							</div>
						</div>

						<div class = "form-group row">
							<label for="dtpApertura" class="col-sm-12 col-md-2 col-form-label">Fecha de apertura</label>
							<div class="col-sm-12 col-md-2">
							<?php echo('<input type="date" class="form-control" id="dtpApertura" name="dtpApertura" value="' . $Apertura . '" />'); ?>
							</div>
						</div>

						<div class = "form-group row">
							<label for="txnNumero" class="col-sm-12 col-md-2 col-form-label">Número de tomo</label>
							<div class="col-sm-12 col-md-2">
							<?php echo('<input type="number" class="form-control" id="txnNumero" name="txnNumero" value="' . $Numero . '" />'); ?>
							</div>
						</div>

						<div class="form-group row">
							<label for="cboTipo" class="col-sm-12 col-md-2 col-form-label">Tipo de tomo</label>
							<div class="col-sm-12 col-md-3">
								<select class="form-control" id="cboTipo" name="cboTipo">
									<?php
										if ($Tipo == 0 or $Codigo == "")
											echo("<option value='0' selected>Seminario</option>");
										else
											echo("<option value='0'>Seminario</option>");
										
										if ($Tipo == 1)
											echo("<option value='1' selected>Curso</option>");
										else
											echo("<option value='1'>Curso</option>");
										
										if ($Tipo == 2)
											echo("<option value='2' selected>Carrera</option>");
										else
											echo("<option value='2'>Carrera</option>");
										
										if ($Tipo == 3)
											echo("<option value='3' selected>Taller</option>");
										else
											echo("<option value='3'>Taller</option>");
											
										if ($Tipo == 4)
											echo("<option value='4' selected>Diplomado</option>");
										else
											echo("<option value='4'>Diplomado</option>");
										if ($Tipo == 5)
											echo("<option value='5' selected>Webinar</option>");
										else
											echo("<option value='5'>Webinar</option>");
										if ($Tipo == 6)
											echo("<option value='6' selected>Workshop</option>");
										else
											echo("<option value='6'>Workshop</option>");
										if ($Tipo == 7)
											echo("<option value='7' selected>Teambuilding</option>");
										else
											echo("<option value='7'>Teambuilding</option>");
									?>
								</select>
							</div>
						</div>

						<div class = "form-group row">
							<label for="txnUltimoFolio" class="col-sm-12 col-md-2 col-form-label">Ultimo folio usado</label>
							<div class="col-sm-12 col-md-2">
							<?php echo('<input type="number" class="form-control" id="txnUltimoFolio" name="txnUltimoFolio" value="' . $UltimoFolio . '" />'); ?>
							</div>
						</div>

						<div class = "form-group row">
							<label for="txnUltimaActa" class="col-sm-12 col-md-2 col-form-label">Ultima acta registrada</label>
							<div class="col-sm-12 col-md-2">
							<?php echo('<input type="number" class="form-control" id="txnUltimaActa" name="txnUltimaActa" value="' . $UltimaActa . '" />'); ?>
							</div>
						</div>

						<div class="form-group row">
							<label for="optCerrado" class="col-sm-12 col-md-2 form-label">Cerrado</label>
							<div class="col-sm-12 col-md-4">
								<div class="radio">
									<?php
									if ($Cerrado == 0 or $Codigo == "")
									{
										echo('<input type="radio" id="optCerrado1" name="optCerrado" value="0" checked="checked" /> No <input type="radio" id="optCerrado2" name="optCerrado" value="1" /> Si');
									}
									else
									{
										echo('<input type="radio" id="optCerrado1" name="optCerrado" value="0" /> No <input type="radio" id="optCerrado2" name="optCerrado" value="1" checked="checked" /> Si');
									}
								?>
								</div>
							</div>
						</div>

						<div class = "row">
							<div class="col-auto col-xs-offset-none col-md-9 col-md-offset-2">
								<input type="submit" id="Guardar" name="Guardar" value="Guardar" class="btn btn-warning" />
								<input type="button" id="Cancelar" name="Cancelar" value="Cancelar" class="btn btn-warning" onclick="location.href='gridTomos.php';"/>
							</div>
						</div>
					</form>
                </div>
			</div>
		</div>
	</div>
	<?php	}
		}
	}
?>
		
</body>
</html>
<script type='text/javascript'>
	function verificarFormulario()
	{
		if(document.getElementById('CodTomo').value=="")
		{
			$.messager.alert('KDSA','Falta el Código del tomo.','warning');
			return false;
		}
		
		return true;
	}
</script>