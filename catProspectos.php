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
	require_once ("funciones/fxProspectos.php");
	
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
		$PermisoUsuario = fxPermisoUsuario("catProspectos");
		
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
			if (isset($_POST["txtProspecto"]))
			{
				$Codigo = $_POST["txtProspecto"];
				$Nombre = htmlentities($_POST["txtNombre"]);
				$Tipo = $_POST["optTipo"];
				$Telefono = $_POST["txtTelefono"];
				$Correo = $_POST["txtCorreo"];
				$FechaIngreso = $_POST["dtpFechaIngreso"];
				$FechaVenc = $_POST["dtpFechaVenc"];
				$CedulaRuc = $_POST["txtCedulaRuc"];
				$NombreContacto = htmlentities($_POST["txtNombreContacto"]);
				$TelContacto = $_POST["txtTelContacto"];
				$Patronal = $_POST["txtPatronal"];
				$Usuario = $_POST["txtUsuario"];
				$Activo = $_POST["optActivo"];
					
				if ($Codigo == "")
				{
					$Codigo = fxGuardarProspectos ($Nombre, $Tipo, $Telefono, $Correo, $FechaIngreso, $FechaVenc, $CedulaRuc, $NombreContacto, $TelContacto, $Patronal, $Usuario, $Activo);
					fxAgregarBitacora ($_SESSION["gsUsuario"], "KDSA060A", $Codigo, "", "Agregar");
				}
				else
				{
					fxModificarProspectos ($Codigo, $Nombre, $Tipo, $Telefono, $Correo, $FechaIngreso, $FechaVenc, $CedulaRuc, $NombreContacto, $TelContacto, $Patronal, $Activo);
					fxAgregarBitacora ($_SESSION["gsUsuario"], "KDSA060A", $Codigo, "", "Modificar");
				}
				
				?><meta http-equiv="Refresh" content="0;url=gridProspectos.php"/><?php
			}
			else
			{
				if (isset($_POST["KDSA"]))
					$Codigo = $_POST["KDSA"];
				else
					$Codigo = "";
				
				if ($Codigo != "")
				{
					$RecordSet = fxDevuelveProspectos(0, $Codigo);
					$Fila = $RecordSet->fetch();
					$Nombre = $Fila["NOMBRE_060"];
					$Tipo = $Fila["TIPO_060"];
					$Telefono = $Fila["TELEFONO_060"];
					$Correo = $Fila["CORREO_060"];
					$FechaIngreso = $Fila["FECHAINGRESO_060"];
					$FechaVenc = $Fila["FECHAVENC_060"];
					$CedulaRuc = $Fila["CEDULARUC_060"];
					$NombreContacto = $Fila["NOMBRECONTACTO_060"];
					$TelContacto = $Fila["TELEFONOCONTACTO_060"];
					$Patronal = $Fila["PATRONAL_060"];
					$Usuario = $Fila["USUARIO_060"];
					$Activo = $Fila["ACTIVO_060"];
				}
				else
				{
					$Nombre = "";
					$Tipo = 0;
					$Telefono = "";
					$Correo = "";
					$FechaIngreso = "";
					$FechaVenc = "";
					$CedulaRuc = "";
					$NombreContacto = "";
					$TelContacto = "";
					$Patronal = "";
					$Usuario = "";
					$Activo = 0;
				}
	?>
    <div class="container text-left">
    	<div id="DivContenido">
			<div class = "row">
				<div class="col-xs-12 col-md-11">
					<div class="degradado"><strong>Catálogo de prospectos</strong></div>
				</div>
			</div>

			<div class = "row">
                <div class="col-xs-12 col-xs-offset-none col-md-12 col-md-offset-1">
				<form name="catProspectos" id="catProspectos">
                	<div class = "form-group row">
                        <label for="txtProspecto" class="col-sm-12 col-md-3 form-label">Código del Prospecto</label>
                        <div class="col-sm-12 col-md-3">
                        <?php
                            echo('<input type="text" class="form-control" id="txtProspecto" name="txtProspecto" value="' . $Codigo . '" readonly />'); 
                        ?>
                        </div>
                        <div class="col-auto">
                        </div>
                    </div>
                    
                    <div class = "form-group row">
						<label for="txtNombre" class="col-sm-12 col-md-3 form-label">Nombre</label>
                        <div class="col-sm-12 col-md-7">
						<?php echo('<input type="text" class="form-control" id="txtNombre" name="txtNombre" value="' . $Nombre . '" />'); ?>
                        </div>
                        <div class="col-auto">
                        </div>
                    </div>
                    
                    <div class = "form-group row">
                        <label for="optTipo" class="col-sm-12 col-md-3 form-label">Tipo</label>
                        <div class="col-sm-12 col-md-3">
                            <div class = "radio">
                            <?php
                                if ($Tipo == 0)
                                {
                                    echo('<input type="radio" id="optTipo1" name="optTipo" value="0" checked /> Natural <input type="radio" id="optTipo2" name="optTipo" value="1" /> Empresa');
                                }
                                else
                                {
                                    echo('<input type="radio" id="optTipo1" name="optTipo" value="0" /> Natural <input type="radio" id="optTipo2" name="optTipo" value="1" checked /> Empresa');
                                }
                            ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class = "form-group row">
						<label for="txtTelefono" class="col-sm-12 col-md-3 form-label">Telefono</label>
                        <div class="col-sm-12 col-md-4">
						<?php echo('<input type="text" class="form-control" id="txtTelefono" name="txtTelefono" value="' . $Telefono . '" />'); ?>
                        </div>
                        <div class="col-auto">
                        </div>
                    </div>
                    
                    <div class = "form-group row">
						<label for="txtCorreo" class="col-sm-12 col-md-3 form-label">Correo electrónico</label>
                        <div class="col-sm-12 col-md-4">
						<?php echo('<input type="text" class="form-control" id="txtCorreo" name="txtCorreo" value="' . $Correo . '" />'); ?>
                        </div>
                        <div class="col-auto">
                        </div>
                    </div>
                    
                    <div class = "form-group row">
						<label for="dtpFechaIngreso" class="col-sm-12 col-md-3 form-label">Fecha de ingreso</label>
                        <div class="col-sm-12 col-md-3">
                        <?php
							if ($Codigo == "")
								echo('<input type="date" class="form-control" id="dtpFechaIngreso" name="dtpFechaIngreso" value="' . date("Y-m-d") . '" />');
							else
								echo('<input type="date" class="form-control" id="dtpFechaIngreso" name="dtpFechaIngreso" value="' . $FechaIngreso . '" />');
						?>
                        </div>
                        <div class="col-auto">
                        </div>
                    </div>

					<div class = "form-group row">
						<label for="dtpFechaVenc" class="col-sm-12 col-md-3 form-label">Fecha de vencimiento</label>
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
						<label for="txtCedulaRuc" class="col-sm-12 col-md-3 form-label">Cédula / RUC</label>
                        <div class="col-sm-12 col-md-4">
						<?php echo('<input type="text" class="form-control" id="txtCedulaRuc" name="txtCedulaRuc" value="' . $CedulaRuc . '" />'); ?>
                        </div>
                        <div class="col-auto">
                        </div>
                    </div>
                                   
                    <div class = "form-group row">
						<label for="txtNombreContacto" class="col-sm-12 col-md-3 form-label">Nombre de Contacto <p style="color:rgb(150,150,150)"><i><small>Para Prospectos Empresariales</small></i></p></label>
                        <div class="col-sm-12 col-md-7">
						<?php echo('<input type="text" class="form-control" id="txtNombreContacto" name="txtNombreContacto" value="' . $NombreContacto . '" />'); ?>
                        </div>
                        <div class="col-auto">
                        </div>
                    </div>
     
                    <div class = "form-group row">
						<label for="txtTelContacto" class="col-sm-12 col-md-3 form-label">Teléfono de Contacto <p style="color:rgb(150,150,150)"><i><small>Para Prospectos Empresariales</small></i></p></label>
                        <div class="col-sm-12 col-md-4">
						<?php echo('<input type="text" class="form-control" id="txtTelContacto" name="txtTelContacto" value="' . $TelContacto . '" />'); ?>
                        </div>
                        <div class="col-auto">
                        </div>
                    </div>
                    
                    <div class = "form-group row">
						<label for="txtPatronal" class="col-sm-12 col-md-3 form-label">Patronal <p style="color:rgb(150,150,150)"><i><small>Para Prospectos Empresariales</small></i></p></label>
                        <div class="col-sm-12 col-md-4">
						<?php echo('<input type="text" class="form-control" id="txtPatronal" name="txtPatronal" value="' . $Patronal . '" />'); ?>
                        </div>
                        <div class="col-auto">
                        </div>
                    </div>
                    
                    <div class = "form-group row">
						<label for="dgDET" class="col-sm-12 col-md-3 form-label">Matrículas del Prospecto</label>
                        <div class="col-sm-auto col-md-7">
                            <?php
								$nombreArchivo = fxEscribeJson($Codigo);
							?>
                            <div id="dvDET">
                            <table id="dgDET" class="easyui-datagrid table" data-options="iconCls:'icon-edit', singleSelect:true, url:'<?php echo(rtrim($nombreArchivo)); ?>', method:'get'">
                                <thead>
                                    <tr>
                                        <th data-options="field:'matricula',width:'20%',align:'left'">Matrícula</th>
                                        <th data-options="field:'estudiante',width:'40%',align:'left'">Estudiante</th>
                                        <th data-options="field:'curso',width:'40%',align:'left'">Curso</th>
                                    </tr>
                                </thead>
                            </table>
                            </div>
                        </div>
                        <div class="col-auto">
                        </div>
                    </div>
                    
                    <div class = "form-group row">
						<label for="txtUsuario" class="col-sm-12 col-md-3 form-label">Usuario</label>
                        <div class="col-sm-12 col-md-4">
						<?php
							if ($Codigo == "")
								echo('<input type="text" class="form-control" id="txtUsuario" name="txtUsuario" value="' . $_SESSION["gsUsuario"] . '" readonly />');
							else
								echo('<input type="text" class="form-control" id="txtUsuario" name="txtUsuario" value="' . $Usuario . '" readonly />');
						?>
                        </div>
                        <div class="col-auto">
                        </div>
                    </div>
                    
                    <div class = "form-group row">
                        <label for="Activo" class="col-sm-12 col-md-3 form-label">Activo</label>
                        <div class="col-sm-12 col-md-3">
                            <div class = "radio">
                            <?php
                                if ($Activo == 1 or $Codigo == "")
                                {
                                    echo('<input type="radio" id="optActivo1" name="optActivo" value="0" /> No <input type="radio" id="optActivo2" name="optActivo" value="1" checked /> Si');
                                }
                                else
                                {
                                    echo('<input type="radio" id="optActivo1" name="optActivo" value="0" checked /> No <input type="radio" id="optActivo2" name="optActivo" value="1" /> Si');
                                }
                            ?>
                            </div>
                        </div>
                    </div>
                                        
					<div class = "row">
                    	<div class="col-auto col-xs-offset-none col-md-12 col-md-offset-3">
							<input type="submit" id="Guardar" name="Guardar" value="Guardar" class="btn btn-warning"/>
                            <input type="button" id="Cancelar" name="Cancelar" value="Cancelar" class="btn btn-warning" onclick="location.href='gridProspectos.php';"/>
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
	var mCedula;
	var codProspecto;
	var existeNombre;
	var existeCedula;
	var parametros;
	var datosJson;
	
	function verificarFormulario()
	{
		var bResultado;

		if (document.getElementById('dtpFechaVenc').value < document.getElementById('dtpFechaIngreso').value)
		{
			$.messager.alert('INOFE','La fecha de Vencimiento es anterior a la fecha de Ingreso.','warning');
			return false;
		}
		
		if (document.getElementById('txtNombre').value == "")
		{
			$.messager.alert('INOFE','Falta el Nombre del Prospecto.','warning');
			return false;
		}
		
		if (existeNombre == true)
		{
			document.getElementById('txtNombre').focus();
			if (!confirm('El Nombre del prospecto es similar al ' + codProspecto + ' ¿Desea continuar?'))
				return false;
		}
		
		if (document.getElementById('txtTelefono').value == "")
		{
			$.messager.alert('INOFE','Falta el Teléfono del Prospecto.','warning');
			return false;
		}
		
		if (document.getElementById('optTipo2').checked && document.getElementById('txtPatronal').value == "")
		{
			$.messager.alert('INOFE','Falta el Patronal.','warning');
			return false;
		}
		
		if (document.getElementById('txtCedulaRuc').value != "")
		{
			if(mCedula.indexOf("-") > -1)
			{
				document.getElementById('txtCedula').focus();
				$.messager.alert('INOFE','Escriba la Cédula / RUC sin guiones.','warning');
				return false;
			}		
	
			if (existeCedula == true)
			{
				document.getElementById('txtCedulaRuc').focus();
				$.messager.alert('INOFE','La Cédula / RUC ya fue registrada con el prospecto ' + codProspecto,'warning');
				return false;
			}
		}
		
		return true;
	}
	
	$('form').submit(function(e){
	e.preventDefault();

	$.when(ejecutarNombre()).done(function(respuesta)
	{
		if (respuesta != "")
			existeNombre = true;
		else
			existeNombre = false;

		codProspecto = respuesta;
	})
	
	if (document.getElementById('txtCedulaRuc').value != "")
	{
		$.when(ejecutarCedula()).done(function(respuesta)
		{
			if (respuesta != "")
				existeCedula = true;
			else
				existeCedula = false;
	
			codProspecto = respuesta;
		})
	}
	
	if (verificarFormulario() == true)
	{
		var texto;
		var datos;
						
		texto = '{"txtProspecto":"' + document.getElementById("txtProspecto").value + '", ';
		texto += '"txtNombre":"' + document.getElementById("txtNombre").value + '", ';
	
		if (document.getElementById("optTipo1").checked)
			texto += '"optTipo":"0", ';
		else
			texto += '"optTipo":"1", ';
		
		texto += '"txtTelefono":"' + document.getElementById("txtTelefono").value + '", ';
		texto += '"txtCorreo":"' + document.getElementById("txtCorreo").value + '", ';
		texto += '"dtpFechaIngreso":"' + document.getElementById("dtpFechaIngreso").value + '", ';
		texto += '"dtpFechaVenc":"' + document.getElementById("dtpFechaVenc").value + '", ';
		texto += '"txtCedulaRuc":"' + document.getElementById("txtCedulaRuc").value + '", ';
		texto += '"txtNombreContacto":"' + document.getElementById("txtNombreContacto").value + '", ';
		texto += '"txtTelContacto":"' + document.getElementById("txtTelContacto").value + '", ';
		texto += '"txtPatronal":"' + document.getElementById("txtPatronal").value + '", ';
		texto += '"txtUsuario":"' + document.getElementById("txtUsuario").value + '", ';

		if (document.getElementById("optActivo1").checked)
			texto += '"optActivo":"0"}';
		else
			texto += '"optActivo":"1"}';

		datos = JSON.parse(texto);

		$.ajax({
			url:'catProspectos.php',
			type:'post',
			data:datos,
			beforeSend: function(){console.log(datos)}
		})
		.done(function(){location.href="gridProspectos.php"})
		.fail(function(){console.log('Error')});
		}
	});
	
	function ejecutarNombre()
	{
		parametros = '{"nombreProspecto":"'+document.getElementById("txtNombre").value+'", "codProspecto":"' +document.getElementById("txtProspecto").value+ '"}';
		datosJson = JSON.parse(parametros);
	
		return $.ajax({
			url:'funciones/fxDatosExternos.php',
			type:'post',
			async: false,
			data:datosJson,
			beforeSend: function(){console.log(datosJson)}
		})
	}
	
	function ejecutarCedula()
	{
		mCedula = document.getElementById('txtCedulaRuc').value;
		parametros = '{"cedulaProspecto":"'+document.getElementById("txtCedulaRuc").value+'", "codProspecto":"' +document.getElementById("txtProspecto").value+ '"}';
		datosJson = JSON.parse(parametros);
	
		return $.ajax({
			url:'funciones/fxDatosExternos.php',
			type:'post',
			async: false,
			data:datosJson,
			beforeSend: function(){console.log(datosJson)}
		})
	}
</script>
<?php
function fxEscribeJson($Prospecto)
{
	if ($Prospecto == "")
		$nombreArchivo = "PT00000000.json";
	else
		$nombreArchivo = $Prospecto . ".json";

	if (file_exists($nombreArchivo))
		unlink($nombreArchivo);
	
	//Escribe el Json
	$mDatos = fxDevuelveDetProspectos($Prospecto);
	$numRegistros = $mDatos->rowCount();

	$archivo = fopen($nombreArchivo, "w");
	
	fwrite($archivo, "[" . PHP_EOL);
	
	for ($i = 1; $i <= $numRegistros; $i++)
	{
		$Fila = $mDatos->fetch();
		fwrite($archivo, "{");
		fwrite($archivo, '"matricula":"' . rtrim($Fila['MATRICULA_REL']) . '", ');
		fwrite($archivo, '"estudiante":"' . rtrim($Fila['ESTUDIANTE']) . '", ');
		fwrite($archivo, '"curso":"' . rtrim($Fila['NOMBRE_020']) . '"');
		
		if ($i == $numRegistros)
			fwrite($archivo, "}" . PHP_EOL);
		else
			fwrite($archivo, "}," . PHP_EOL);
	}
	fwrite($archivo, "]");
	fclose($archivo);

	return($nombreArchivo);
}
?>