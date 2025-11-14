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
		$PermisoUsuario = fxPermisoUsuario("catUsuarios");
		
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
				$Codigo = $_POST["CodUsuario"];
				$Nombre = $_POST["NomUsuario"];
				$Correo = $_POST["Correo"];
				$Clave = $_POST["Clave"];
				$Clave1 = $_POST["Clave1"];
				$Academico = $_POST["Academico"];
				$Supervisor = $_POST["Supervisor"];
				$Activo = $_POST["Activo"];
				
				if ($Clave != $Clave1)
				{
					?>
					<script>
						$.messager.alert('INOFE','La Contraseña no se confirmó correctamente.','warning');
						$("a").click(function(){window.location="catUsuarios.php"});
					</script>
					<?php
				}
				else
				{
					if (isset($_POST["Guardar"]))
						{
							if (fxExisteUsuario($Codigo) == 0)
							{
								fxGuardarUsuario ($Codigo, $Nombre, $Correo, $Clave, $Academico, $Supervisor);
								fxAgregarBitacora ($_SESSION["gsUsuario"], "KDSA002A", $Codigo, "", "Agregar");
							}
							else
							{
								fxModificarUsuario ($Codigo, $Nombre, $Correo, $Clave, $Academico, $Supervisor, $Activo);
								fxAgregarBitacora ($_SESSION["gsUsuario"], "KDSA002A", $Codigo, "", "Modificar");
							}
						}
						
					?><meta http-equiv="Refresh" content="0;url=gridUsuarios.php"/><?php
				}
		}
			else
			{
				if (isset($_POST["KDSA"]))
					$Codigo = $_POST["KDSA"];
				else
					$Codigo = "";
				
				if ($Codigo != "")
				{
					$RecordSet = fxDevuelveUsuario(0, $Codigo);
					$Fila = $RecordSet->fetch();
					$Nombre = $Fila["NOMBRE_002"];
					$Correo = $Fila["CORREO_002"];
					$Clave = $Fila["CLAVE_002"];
					$Academico = $Fila["ACADEMICO_002"];
					$Supervisor = $Fila["SUPERVISOR_002"];
					$Activo = $Fila["ACTIVO_002"];
				}
				else
				{
					$Nombre = "";
					$Correo = "";
					$Clave = "";
					$Academico = 0;
					$Supervisor = 0;
					$Activo = 0;
				}
	?>
    <div class="container text-left">
    	<div id="DivContenido">
			<div class = "row">
				<div class="col-xs-12 col-md-11">
					<div class="degradado"><strong>Catálogo de usuarios</strong></div>
				</div>
			</div>

        	<div class = "row">
            	<div class="col-xs-12 col-xs-offset-none col-md-12 col-md-offset-3">
                    <form name="catUsuarios" action="catUsuarios.php" method="post" onsubmit="return verificarFormulario()">
                        <div class = "form-group row">
                            <label for="CodUsuario" class="col-sm-12 col-md-2 form-label">Código del Usuario</label>
                            <div class="col-sm-12 col-md-3">
                                <?php
                                    if (trim($Codigo) != "")
                                        echo('<input type="text" class="form-control" id="CodUsuario" name="CodUsuario" value="' . $Codigo . '"  readonly />'); 
                                    else
                                        echo('<input type="text" class="form-control" id="CodUsuario" name="CodUsuario" value="' . $Codigo . '" />'); 
                                ?>
                            </div>
                        </div>
                        <div class = "form-group row">
                            <label for="NomUsuario" class="col-sm-12 col-md-2 form-label">Nombre del Usuario</label>
                            <div class="col-sm-12 col-md-5">
                                <?php echo('<input type="text" class="form-control" id="NomUsuario" name="NomUsuario" value="' . $Nombre . '" />'); ?>
                            </div>
                        </div>
                        <div class = "form-group row">
                            <label for="Correo" class="col-sm-12 col-md-2 form-label">Correo electrónico</label>
                            <div class="col-sm-12 col-md-5">
                                <?php echo('<input type="text" class="form-control" id="Correo" name="Correo" value="' . $Correo . '" />'); ?>
                            </div>
                        </div>
                        <div class = "form-group row">
                            <label for="Clave" class="col-sm-12 col-md-2 col-form-label">Clave del Usuario</label>
                            <div class="col-sm-12 col-md-3">
                                <?php echo('<input type="password" class="form-control" id="Clave" name="Clave" value="' . $Clave . '" />'); ?>
                            </div>
                        </div>
                        <div class = "form-group row">
                            <label for="Clave1" class="col-sm-12 col-md-2 form-label">Confirme la Clave</label>
                            <div class="col-sm-12 col-md-3">
                                <?php echo('<input type="password" class="form-control" id="Clave1" name="Clave1" value="' . $Clave . '" />'); ?>
                            </div>
                        </div>
                        <div class = "form-group row">
                            <label for="Supervisor" class="col-sm-12 col-md-2 form-label">Académico</label>
                            <div class="col-sm-12 col-md-3">
                                <div class = "radio">
                                <?php
                                    if ($Academico == 1)
                                    {
                                        echo('<input type="radio" id="Academico1" name="Academico" value="0" /> No <input type="radio" id="Academico2" name="Academico" value="1" checked="checked" /> Si');
                                    }
                                    else
                                    {
                                        echo('<input type="radio" id="Academico1" name="Academico" value="0" checked="checked" /> No <input type="radio" id="Academico2" name="Academico" value="1" /> Si');
                                    }
                                ?>
                                </div>
                            </div>
                        </div>
						<div class = "form-group row">
                            <label for="Supervisor" class="col-sm-12 col-md-2 form-label">Supervisor</label>
                            <div class="col-sm-12 col-md-3">
                                <div class = "radio">
                                <?php
                                    if ($Supervisor == 1)
                                    {
                                        echo('<input type="radio" id="Supervisor1" name="Supervisor" value="0" /> No <input type="radio" id="Supervisor2" name="Supervisor" value="1" checked="checked" /> Si');
                                    }
                                    else
                                    {
                                        echo('<input type="radio" id="Supervisor1" name="Supervisor" value="0" checked="checked" /> No <input type="radio" id="Supervisor2" name="Supervisor" value="1" /> Si');
                                    }
                                ?>
                                </div>
                            </div>
                        </div>
                        <div class = "form-group row">
                            <label for="Activo" class="col-sm-12 col-md-2 form-label">Activo</label>
                            <div class="col-sm-12 col-md-3">
                                <div class = "radio">
                                <?php
                                    if ($Activo == 1)
                                    {
                                        echo('<input type="radio" id="Activo1" name="Activo" value="0" /> No <input type="radio" id="Activo2" name="Activo" value="1" checked="checked" /> Si');
                                    }
                                    else
                                    {
                                        echo('<input type="radio" id="Activo1" name="Activo" value="0" checked="checked" /> No <input type="radio" id="Activo2" name="Activo" value="1" /> Si');
                                    }
                                ?>
                                </div>
                            </div>
                        </div>
                        <div class = "row">
                            <div class="col-auto col-xs-offset-none col-md-12 col-md-offset-2">
                                <input type="submit" id="Guardar" name="Guardar" value="Guardar" class="btn btn-warning"/>
                                <input type="button" id="Cancelar" name="Cancelar" value="Cancelar" class="btn btn-warning" onclick="location.href='gridUsuarios.php';"/>
                            </div>
                        </div>
                    </form>			
	<?php	}
		}
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
		if (document.getElementById('CodUsuario').value=="")
		{
			$.messager.alert('INOFE','Falta el Código del Usuario.','warning');
			return false;
		}
		
		if(document.getElementById('NomUsuario').value=="")
		{
			$.messager.alert('INOFE','Falta el Nombre del Usuario.','warning');
			return false;
		}
		
		return true;
	}
</script>