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
	require_once ("funciones/fxFirmas.php");
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
		$PermisoUsuario = fxPermisoUsuario("catFirmas");
		
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
			if (isset($_POST["CodFirma"]))
			{
				$Codigo = $_POST["CodFirma"];
				$Nombre = $_POST["txtNombre"];
				$Cargo = $_POST["txtCargo"];
				$Sexo = $_POST["cboSexo"];

				{
					if ($Codigo == "")
					{
						fxGuardarFirma ($Codigo, $Nombre, $Cargo, $Sexo);
						fxAgregarBitacora ($_SESSION["gsUsuario"], "KDSA008A", $Codigo, "", "Agregar");
					}
					else
					{
						fxModificarFirma ($Codigo, $Nombre, $Cargo, $Sexo);
						fxAgregarBitacora ($_SESSION["gsUsuario"], "KDSA008A", $Codigo, "", "Modificar");
					}
				}
									
				?><meta http-equiv="Refresh" content="0;url=gridFirmas.php"/><?php
			}
			else
			{
				if (isset($_POST["KDSA"]))
					$Codigo = $_POST["KDSA"];
				else
					$Codigo = "";
				
				if ($Codigo != "")
				{
					$m_cnx_MySQL = fxAbrirConexion();
					$msConsulta = "select NOMBRE_008, CARGO_008, SEXO_008 from KDSA008A where FIRMA_REL = ?";
					$mDatos = $m_cnx_MySQL->prepare($msConsulta);
					$mDatos->execute([$Codigo]);
					$Fila = $mDatos->fetch();
					$Nombre = $Fila["NOMBRE_008"];
					$Cargo = $Fila["CARGO_008"];
					$Sexo = $Fila["SEXO_008"];
				}
				else
				{
					$Nombre = "";
					$Cargo = "";
					$Sexo = "";
				}
	?>
    <div class="container text-left">
		<div class = "row">
            <div class="col-xs-12 col-md-11">
                <div class="degradado"><strong>Firmas autorizadas</strong></div>
            </div>
        </div>

    	<div id="DivContenido">
			<div class = "row">
                <div class="col-xs-12 col-xs-offset-none col-md-12 col-md-offset-2">
				<form id="catFirmas" name="catFirmas" action="catFirmas.php" onsubmit="return verificarFormulario()" method="post">
                	<div class = "form-group row">
                        <label for="CodFirma" class="col-sm-12 col-md-2 col-form-label">Autorizado</label>
                        <div class="col-sm-12 col-md-3">
                        <?php
                            echo('<input type="text" class="form-control" id="CodFirma" name="CodFirma" value="' . $Codigo . '" readonly />'); 
                        ?>
                        </div>
                    </div>
                    
                    <div class = "form-group row">
						<label for="txtNombre" class="col-sm-12 col-md-2 col-form-label">Nombre</label>
                        <div class="col-sm-12 col-md-7">
						<?php echo('<input type="text" class="form-control" id="txtNombre" name="txtNombre" value="' . $Nombre . '" />'); ?>
                        </div>
                    </div>

                    <div class = "form-group row">
						<label for="txtCargo" class="col-sm-12 col-md-2 col-form-label">Cargo</label>
                        <div class="col-sm-12 col-md-7">
						<?php echo('<input type="text" class="form-control" id="txtCargo" name="txtCargo" value="' . $Cargo . '" />'); ?>
                        </div>
                    </div>

					<div class = "form-group row">
						<label for="cboSexo" class="col-sm-12 col-md-2 col-form-label">Sexo</label>
                        <div class="col-sm-12 col-md-3">
							<select class="form-control" name="cboSexo" id="cboSexo">
							<?php
								if ($Sexo == "M")
								{
									echo('<option value="M" selected>Masculino</option>');
									echo('<option value="F" >Femenino</option>');
								}
								else
								{
									echo('<option value="M" >Masculino</option>');
									echo('<option value="F" selected>Femenino</option>');
								}
							?>
							</select>
                        </div>
                    </div>

					<div class = "row">
                    	<div class="col-auto col-xs-offset-none col-md-12 col-md-offset-2">
							<input type="submit" id="Guardar" name="Guardar" value="Guardar" class="btn btn-warning" />
                            <input type="button" id="Cancelar" name="Cancelar" value="Cancelar" class="btn btn-warning" onclick="location.href='gridFirmas.php';"/>
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
		if(document.getElementById('Nombre').value=="")
		{
			$.messager.alert('INOFE','Falta el Nombre.','warning');
			return false;
		}

		if(document.getElementById('Cargo').value=="")
		{
			$.messager.alert('INOFE','Falta el Cargo.','warning');
			return false;
		}
		
		return true;
	}
</script>