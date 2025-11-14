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
	require_once ("funciones/fxCursosInatec.php");
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
		$PermisoUsuario = fxPermisoUsuario("catCursosInatec");
		
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
				$Codigo = $_POST["txtCodCurso"];
				$Nombre = htmlentities($_POST["txtNomCurso"]);
				$HorasClase = $_POST["txnHorasClase"];
				$CodInatec = $_POST["txtCodInatec"];
				$Acuerdo = $_POST["txtAcuerdo"];
				$FechaVenc = $_POST["dtpFechaVenc"];
				$Activo = $_POST["optActivo"];

				{
					if ($Codigo == "")
					{
						$Codigo = fxGuardarCursosInatec ($Nombre, $HorasClase, $CodInatec, $Acuerdo, $FechaVenc, $Activo);
						fxAgregarBitacora ($_SESSION["gsUsuario"], "KDSA070A", $Codigo, "", "Agregar");
					}
					else
					{
						fxModificarCursosInatec ($Codigo, $Nombre, $HorasClase, $CodInatec, $Acuerdo, $FechaVenc, $Activo);
						fxAgregarBitacora ($_SESSION["gsUsuario"], "KDSA070A", $Codigo, "", "Modificar");
					}
				}
				
					
				?><meta http-equiv="Refresh" content="0;url=gridCursosInatec.php"/><?php
			}
			else
			{
				if (isset($_POST["KDSA"]))
					$Codigo = $_POST["KDSA"];
				else
					$Codigo = "";
				
				if ($Codigo != "")
				{
					$RecordSet = fxDevuelveCursosInatec(0, $Codigo);
					$Fila = $RecordSet->fetch();
					$Nombre = $Fila["NOMBRE_070"];
					$HorasClase = $Fila["HORASCLASE_070"];
					$CodInatec = $Fila["CODIGO_070"];
					$Acuerdo = $Fila["ACUERDO_070"];
					$FechaVenc = $Fila["FECHAVENC_070"];
					$Activo = $Fila["ACTIVO_070"];
				}
				else
				{
					$Nombre = "";
					$HorasClase = 0;
					$CodInatec = "";
					$Acuerdo = "";
					$FechaVenc = "";
					$Activo = 0;
				}
	?>
    <div class="container text-left">
		<div class = "row">
            <div class="col-xs-12 col-md-11">
                <div class="degradado"><strong>Catálogo de cursos de INATEC</strong></div>
            </div>
		</div>
			
    	<div id="DivContenido">
			<div class = "row">
                <div class="col-xs-12 col-xs-offset-none col-md-12 col-md-offset-2">
				<form id="catCursosInatec" name="catCursosInatec" action="catCursosInatec.php" method="post" onsubmit="return verificarFormulario()">
                	<div class = "form-group row">
                        <label for="txtCodCurso" class="col-sm-12 col-md-2 col-form-label">Código del Curso</label>
                        <div class="col-sm-12 col-md-3">
                        <?php echo('<input type="text" class="form-control" id="txtCodCurso" name="txtCodCurso" value="' . $Codigo . '" readonly />'); ?>
                        </div>
                        <div class="col-auto">
                        </div>
                    </div>
                    
                    <div class = "form-group row">
						<label for="txtNomCurso" class="col-sm-12 col-md-2 col-form-label">Nombre del Curso</label>
                        <div class="col-sm-12 col-md-8">
						<?php echo('<input type="text" class="form-control" id="txtNomCurso" name="txtNomCurso" value="' . $Nombre . '" />'); ?>
                        </div>
                        <div class="col-auto">
                        </div>
                    </div>
                    
                    <div class = "form-group row">
						<label for="txnHorasClase" class="col-sm-12 col-md-2 col-form-label">Horas Clase</label>
                        <div class="col-sm-12 col-md-2">
						<?php
							if ($Codigo == "")
								echo('<input type="number" style="text-align:right" class="form-control" id="txnHorasClase" name="txnHorasClase" value="0" />');
							else
								echo('<input type="number" style="text-align:right" class="form-control" id="txnHorasClase" name="txnHorasClase" value="' . $HorasClase . '" />');
						?>
                        </div>
                        <div class="col-auto">
                        </div>
                    </div>
                    
                    <div class = "form-group row">
						<label for="txtCodInatec" class="col-sm-12 col-md-2 col-form-label">Código INATEC</label>
                        <div class="col-sm-12 col-md-5">
						<?php echo('<input type="text" class="form-control" id="txtCodInatec" name="txtCodInatec" value="' . $CodInatec . '" />'); ?>
                        </div>
                        <div class="col-auto">
                        </div>
                    </div>
                
                    <div class = "form-group row">
                        <label for="txtAcuerdo" class="col-sm-12 col-md-2 col-form-label">Acuerdo</label>
                        <div class="col-sm-12 col-md-5">
                        <?php echo('<input type="text" class="form-control" id="txtAcuerdo" name="txtAcuerdo" value="' . $Acuerdo . '" />'); ?>
                        </div>
                        <div class="col-auto">
                        </div>
                    </div>
                                                       
                    <div class = "form-group row">
						<label for="dtpFechaVenc" class="col-sm-12 col-md-2 col-form-label">Fecha de Vencimiento</label>
                        <div class="col-sm-12 col-md-3">
						<?php
							if ($Codigo == "")
								echo('<input type="date" class="form-control" id="dtpFechaVenc" name="dtpFechaVenc" value="' . date("Y-m-d") . '" />');
							else
								echo('<input type="date" class="form-control" id="dtpFechaVenc" name="dtpFechaVenc" value="' . $FechaVenc . '" />');
						?>
                        </div>
                        <div class="col-auto">
                        </div>
                    </div>
                     
                    <div class = "form-group row">
                        <label for="optActivo" class="col-sm-12 col-md-2 form-label">Activo</label>
                        <div class="col-sm-12 col-md-4">
                            <div class = "radio">
                            <?php
                                if ($Activo == 1 or $Codigo == "")
                                {
                                    echo('<input type="radio" id="Opcion1" name="optActivo" value="0" /> No <input type="radio" id="Opcion2" name="optActivo" value="1" checked="checked" /> Si');
                                }
                                else
                                {
                                    echo('<input type="radio" id="Opcion1" name="optActivo" value="0" checked="checked" /> No <input type="radio" id="Opcion2" name="optActivo" value="1" /> Si');
                                }
                            ?>
                            </div>
                        </div>
                    </div>
                    
					<div class = "row">
                    	<div class="col-auto col-xs-offset-none col-md-12 col-md-offset-2">
							<input type="submit" id="Guardar" name="Guardar" value="Guardar" class="btn btn-warning" />
                            <input type="button" id="Cancelar" name="Cancelar" value="Cancelar" class="btn btn-warning" onclick="location.href='gridCursosInatec.php';"/>
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
		if (document.getElementById('txtNomCurso').value=="")
		{
			$.messager.alert('INOFE','Falta el Nombre del Curso.','warning');
			return false;
		}
		
		if (document.getElementById('txnHorasClase').value==0)
		{
			$.messager.alert('INOFE','Falta las Horas de clase.','warning');
			return false;
		}
		
		if (document.getElementById('txtCodInatec').value=="")
		{
			$.messager.alert('INOFE','Falta el Código de INATEC.','warning');
			return false;
		}
		
		if (document.getElementById('txtAcuerdo').value=="")
		{
			$.messager.alert('INOFE','Falta el Acuerdo de INATEC.','warning');
			return false;
		}
		
		return true;
	}
</script>