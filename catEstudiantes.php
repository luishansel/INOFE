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
	require_once ("funciones/fxEstudiantes.php");
	
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
		$PermisoUsuario = fxPermisoUsuario("catEstudiantes");
		
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
			if (isset($_POST["txtEstudiante"]))
			{
				$Codigo = $_POST["txtEstudiante"];
				$Nombres = htmlentities($_POST["txtNombres"]);
				$Apellidos = htmlentities($_POST["txtApellidos"]);
				$Sexo = $_POST["optSexo"];
				$Cedula = $_POST["txtCedula"];
				$FechaNac = $_POST["dtpFechaNac"];
				$Domicilio = htmlentities($_POST["txtDomicilio"]);
				$Direccion = htmlentities($_POST["txtDireccion"]);
				$Telefono = $_POST["txtTelefono"];
				$Celular = $_POST["txtCelular"];
				$Correo = $_POST["txtCorreo"];
				$Emergencia = htmlentities($_POST["txtEmergencia"]);
				$Parentesco = htmlentities($_POST["txtParentesco"]);
				$NivAcademico = htmlentities($_POST["txtNivAcademico"]);
				$PostGrado = $_POST["optPostGrado"];
				$Maestria = $_POST["optMaestria"];
				$LugarTrabajo = htmlentities($_POST["txtLugarTrabajo"]);
				$Puesto = htmlentities($_POST["txtPuesto"]);
				$TelEmpresa = $_POST["txtTelEmpresa"];
				if (isset($_POST["gridDocumentos"]))
				    $gridDocumentos = $_POST["gridDocumentos"];
					
				if ($Codigo == "")
				{
					$Codigo = fxGuardarEstudiantes ($Nombres, $Apellidos, $Sexo, $Cedula, $FechaNac, $Domicilio, $Direccion, $Telefono, $Celular, $Correo, $Emergencia, $Parentesco, $NivAcademico, $PostGrado, $Maestria, $LugarTrabajo, $Puesto, $TelEmpresa);
					fxAgregarBitacora ($_SESSION["gsUsuario"], "KDSA010A", $Codigo, "", "Agregar");
				}
				else
				{
					fxModificarEstudiantes ($Codigo, $Nombres, $Apellidos, $Sexo, $Cedula, $FechaNac, $Domicilio, $Direccion, $Telefono, $Celular, $Correo, $Emergencia, $Parentesco, $NivAcademico, $PostGrado, $Maestria, $LugarTrabajo, $Puesto, $TelEmpresa);
					fxAgregarBitacora ($_SESSION["gsUsuario"], "KDSA010A", $Codigo, "", "Modificar");
				}
				
				?><meta http-equiv="Refresh" content="0;url=gridEstudiantes.php"/><?php
			}
			else
			{
				if (isset($_POST["KDSA"]))
					$Codigo = $_POST["KDSA"];
				else
					$Codigo = "";

				if ($Codigo != "")
				{
					$RecordSet = fxDevuelveEstudiantes(0, $Codigo);
					$Fila = $RecordSet->fetch();
					$Nombres = $Fila["NOMBRES_010"];
					$Apellidos = $Fila["APELLIDOS_010"];
					$Sexo = $Fila["SEXO_010"];
					$Cedula = $Fila["CEDULA_010"];
					$FechaNac = $Fila["FECHANAC_010"];
					$Domicilio = $Fila["DOMICILIO_010"];
					$Direccion = $Fila["DIRECCION_010"];
					$Telefono = $Fila["TELEFONO_010"];
					$Celular = $Fila["CELULAR_010"];
					$Correo = $Fila["CORREO_010"];
					$Emergencia = $Fila["EMERGENCIA_010"];
					$Parentesco = $Fila["PARENTESCO_010"];
					$NivAcademico = $Fila["NIVELACADEMICO_010"];
					$PostGrado = $Fila["POSTGRADO_010"];
					$Maestria = $Fila["MAESTRIA_010"];
					$LugarTrabajo = $Fila["LUGARTRABAJO_010"];
					$Puesto = $Fila["PUESTO_010"];
					$TelEmpresa = $Fila["TELEFONOEMPRESA_010"];
				}
				else
				{
					$Nombres = "";
					$Apellidos = "";
					$Sexo = "";
					$Cedula = "";
					$FechaNac = "";
					$Domicilio = "";
					$Direccion = "";
					$Telefono = "";
					$Celular = "";
					$Correo = "";
					$Emergencia = "";
					$Parentesco = "";
					$NivAcademico = "";
					$PostGrado = 0;
					$Maestria = 0;
					$LugarTrabajo = "";
					$Puesto = "";
					$TelEmpresa = "";
				}
	?>
    <div class="container text-left">
    	<div id="DivContenido">
			<div class = "row">
				<div class="col-xs-12 col-md-11">
					<div class="degradado"><strong>Catálogo de estudiantes</strong></div>
				</div>
			</div>

			<div class = "row">
                <div class="col-xs-12 col-md-12">
					<form name="catEstudiantes" id="catEstudiantes">
						<div class = "row">
							<div class="col-auto col-md-11">
								<input type="submit" id="Guardar" name="Guardar" value="Guardar" class="btn btn-warning"/>
								<input type="button" id="Cancelar" name="Cancelar" value="Cancelar" class="btn btn-warning" onclick="location.href='gridEstudiantes.php';"/>
							</div>
						</div>

						<div class="easyui-tabs tabs-narrow" style="width:100%;height:auto">
							<!--Inicio del DIV de Tabs-->
							<div title="Generales" style="padding-left: 20px; padding-top: 10px">
								<div class="col-xs-auto col-md-12">
									<div class = "form-group row">
										<label for="txtEstudiante" class="col-sm-12 col-md-3 form-label">Código del Estudiante</label>
										<div class="col-sm-12 col-md-3">
										<?php
											echo('<input type="text" class="form-control" id="txtEstudiante" name="txtEstudiante" value="' . $Codigo . '" readonly />'); 
										?>
										</div>
										<div class="col-auto">
										</div>
									</div>
									
									<div class = "form-group row">
										<label for="txtNombres" class="col-sm-12 col-md-3 form-label">Nombres</label>
										<div class="col-sm-12 col-md-7">
										<?php echo('<input type="text" class="form-control" id="txtNombres" name="txtNombres" value="' . $Nombres . '" />'); ?>
										</div>
										<div class="col-auto">
										</div>
									</div>
									
									<div class = "form-group row">
										<label for="txtApellidos" class="col-sm-12 col-md-3 form-label">Apellidos</label>
										<div class="col-sm-12 col-md-7">
										<?php echo('<input type="text" class="form-control" id="txtApellidos" name="txtApellidos" value="' . $Apellidos . '" />'); ?>
										</div>
										<div class="col-auto">
										</div>
									</div>
									
									<div class = "form-group row">
										<label for="optSexo" class="col-sm-12 col-md-3 form-label">Sexo</label>
										<div class="col-sm-12 col-md-3">
											<div class = "radio">
											<?php
												if ($Sexo == "F")
												{
													echo('<input type="radio" id="optSexo1" name="optSexo" value="M" /> Masculino <input type="radio" id="optSexo2" name="optSexo" value="F" checked="checked" /> Femenino');
												}
												else
												{
													echo('<input type="radio" id="optSexo1" name="optSexo" value="M" checked="checked" /> Masculino <input type="radio" id="optSexo2" name="optSexo" value="F" /> Femenino');
												}
											?>
											</div>
										</div>
										<div class="col-auto">
										</div>
									</div>
									
									<div class = "form-group row">
										<label for="txtCedula" class="col-sm-12 col-md-3 form-label">Cédula</label>
										<div class="col-sm-12 col-md-4">
										<?php echo('<input type="text" class="form-control" id="txtCedula" name="txtCedula" value="' . $Cedula . '" />'); ?>
										</div>
										<div class="col-auto">
										</div>
									</div>
									
									<div class = "form-group row">
										<label for="dtpFechaNac" class="col-sm-12 col-md-3 form-label">Fecha de nacimiento</label>
										<div class="col-sm-12 col-md-3">
										<?php echo('<input type="date" class="form-control" id="dtpFechaNac" name="dtpFechaNac" value="' . $FechaNac . '" onchange="calcularEdad()" />'); ?>
										</div>
										<div class="col-auto">
										</div>
									</div>
									
									<div class = "form-group row">
										<label for="txtEdad" class="col-sm-12 col-md-3 form-label">Edad</label>
										<div class="col-sm-12 col-md-3">
										<?php 
											echo('<input type="text" class="form-control" id="txtEdad" name="txtEdad" value="" disabled />');
											echo('<input type="number" style="display: none" id="txtNumEdad" name="txtNumEdad" />'); 
										?>
										</div>
										<div class="col-auto">
										</div>
									</div>
									
									<div class = "form-group row">
										<label for="txtDomicilio" class="col-sm-12 col-md-3 form-label">Domicilio</label>
										<div class="col-sm-12 col-md-4">
										<?php echo('<input type="text" class="form-control" id="txtDomicilio" name="txtDomicilio" value="' . $Domicilio . '" />'); ?>
										</div>
										<div class="col-auto">
										</div>
									</div>
									
									<div class = "form-group row">
										<label for="txtDireccion" class="col-sm-12 col-md-3 form-label">Dirección</label>
										<div class="col-sm-12 col-md-7">
										<?php echo('<textarea class="form-control" id="txtDireccion" name="txtDireccion" rows="3">' . $Direccion . '</textarea>'); ?>
										</div>
										<div class="col-auto">
										</div>
									</div>
									
									<div class = "form-group row">
										<label for="txtCelular" class="col-sm-12 col-md-3 form-label">Celular</label>
										<div class="col-sm-12 col-md-4">
										<?php echo('<input type="text" class="form-control" id="txtCelular" name="txtCelular" value="' . $Celular . '" />'); ?>
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
										<label for="txtNivAcademico" class="col-sm-12 col-md-3 form-label">Nivel académico</label>
										<div class="col-sm-12 col-md-4">
										<?php echo('<input type="text" class="form-control" id="txtNivAcademico" name="txtNivAcademico" value="' . $NivAcademico . '" />'); ?>
										</div>
										<div class="col-auto">
										</div>
									</div>
									
									<div class = "form-group row">
										<label for="optPostGrado" class="col-sm-12 col-md-3 form-label">Post-grado</label>
										<div class="col-sm-12 col-md-3">
											<div class = "radio">
											<?php
												if ($PostGrado == 1)
												{
													echo('<input type="radio" id="optPostGrado1" name="optPostGrado" value="0" /> No <input type="radio" id="optPostGrado2" name="optPostGrado" value="1" checked="checked" /> Si');
												}
												else
												{
													echo('<input type="radio" id="optPostGrado1" name="optPostGrado" value="0" checked="checked" /> No <input type="radio" id="optPostGrado2" name="optPostGrado" value="1" /> Si');
												}
											?>
											</div>
											<div class="col-auto">
											</div>
										</div>
									</div>
									
									<div class = "form-group row">
										<label for="optMaestria" class="col-sm-12 col-md-3 form-label">Maestría</label>
										<div class="col-sm-12 col-md-3">
											<div class = "radio">
											<?php
												if ($Maestria == 1)
												{
													echo('<input type="radio" id="optMaestria1" name="optMaestria" value="0" /> No <input type="radio" id="optMaestria2" name="optMaestria" value="1" checked="checked" /> Si');
												}
												else
												{
													echo('<input type="radio" id="optMaestria1" name="optMaestria" value="0" checked="checked" /> No <input type="radio" id="optMaestria2" name="optMaestria" value="1" /> Si');
												}
											?>
											</div>
											<div class="col-auto">
											</div>
										</div>
									</div>
									
									<div class = "form-group row">
										<label for="txtLugarTrabajo" class="col-sm-12 col-md-3 form-label">Lugar de trabajo</label>
										<div class="col-sm-12 col-md-7">
										<?php echo('<input type="text" class="form-control" id="txtLugarTrabajo" name="txtLugarTrabajo" value="' . $LugarTrabajo . '" />'); ?>
										</div>
										<div class="col-auto">
										</div>
									</div>
									
									<div class = "form-group row">
										<label for="txtPuesto" class="col-sm-12 col-md-3 form-label">Puesto de trabajo</label>
										<div class="col-sm-12 col-md-4">
										<?php echo('<input type="text" class="form-control" id="txtPuesto" name="txtPuesto" value="' . $Puesto . '" />'); ?>
										</div>
										<div class="col-auto">
										</div>
									</div>
									
									<div class = "form-group row">
										<label for="txtTelEmpresa" class="col-sm-12 col-md-3 form-label">Teléfono de la empresa</label>
										<div class="col-sm-12 col-md-4">
										<?php echo('<input type="text" class="form-control" id="txtTelEmpresa" name="txtTelEmpresa" value="' . $TelEmpresa . '" />'); ?>
										</div>
										<div class="col-auto">
										</div>
									</div>
									
									<div class = "form-group row">
										<label for="txtEmergencia" class="col-sm-12 col-md-3 form-label">En caso de emergencia avisar a</label>
										<div class="col-sm-12 col-md-4">
										<?php echo('<input type="text" class="form-control" id="txtEmergencia" name="txtEmergencia" value="' . $Emergencia . '" />'); ?>
										</div>
										<div class="col-auto">
										</div>
									</div>
									
									<div class = "form-group row">
										<label for="txtParentesco" class="col-sm-12 col-md-3 form-label">Parentesco</label>
										<div class="col-sm-12 col-md-4">
										<?php echo('<input type="text" class="form-control" id="txtParentesco" name="txtParentesco" value="' . $Parentesco . '" />'); ?>
										</div>
										<div class="col-auto">
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
								</div>
							</div>
							<!--Fin del DIV de Tab GENERALES-->

							<div title="Documentos" style="padding:10px">
								<!--Inicio del DIV de Tab SOPORTE-->
								<div class="col-xs-auto col-md-12">
									<!--Inicio del DIV Columna SOPORTE-->
									<div style="height:auto; padding-top:1%; padding-bottom:2%">
										<table width="100%">
											<tr>
												<td width="15%" valign="top">Descripción</td>
												<td width="50%">
													<select id="cboDescripcion" class="form-control">
													<option value='0'>Cédula</option>
													<option value='1'>Partida de nacimiento</option>
													<option value='2'>Calificaciones</option>
													<option value='3'>Diploma</option>
													<option value='4'>Constancia</option>
													<option value='5'>Otros</option>
													</select>
												</td>
												<td></td>
											</tr>
											<tr>
												<td width="15%" valign="top">Imagen</td>
												<td width="50%">
													<input type="text" id="txtRutaLocal" class="form-control" readonly>
												</td>
												<td>
													<label for="archivo" style="margin-left:1%; padding:0.5%" data-toggle="tooltip" data-placement="top" title="Agregar imagen">
													<img src="imagenes/imageAdd.png" height="100%" style="cursor:pointer" /></label>
													<input type="file" accept=".pdf, image/*" id="archivo" style="display:none"	onchange="llenaArchivo()" />
													<label id="cmdSubir" data-toggle="tooltip" data-placement="top"	title="Subir imagen">
													<img src="imagenes/imageUp.png" height="100%" style="cursor:pointer" /></label>
												</td>
											</tr>
											<tr>
												<td width="15%"></td>
												<td width="50%">
													<label style="font-size:small; font-style:italic; color:rgb(130,130,130)">El nombre del archivo no debe contener espacios en blanco.</label>
												</td>
												<td></td>
											</tr>
										</table>
									</div>
									<div id="dvSOP" style="height:300px; padding-top:1%; padding-bottom:2%">
										<?php
											$mnCuenta = 0;
											$texto = '<table width="100%">';
											
											$mDatos = fxDevuelveDetDocumento($Codigo);
											while ($Fila = $mDatos->fetch())
											{
												$extensionImg = strtoupper(substr($Fila["ARCHIVO_REL"], -3));
												if ($mnCuenta == 0) {
													$texto .= '<tr>';
												}
												$texto .= '<td width="23%" valign="top" style="margin-left:1%; margin-right:1%">';
												$texto .= '<img src="imagenes/imageDel.png"  id="' . trim($Fila["ARCHIVO_REL"]) . '" style="cursor:pointer" onclick="borrarImagen(this)"/><label style="font-size: small"> Borrar ' . trim($Fila["ARCHIVO_REL"]) . '</label>';
												if ($extensionImg != 'PDF')
													$texto .= '<br/><a href="' . trim($Fila["RUTA_011"]) . '" target="_blank"><img src="' . trim($Fila["RUTA_011"]) . '" style="width:100%"/></a>';
												else
													$texto .= '<br/><a href="' . trim($Fila["RUTA_011"]) . '" target="_blank"><img src="imagenes/pdf.png" style="width:80%"/></a>';
												$texto .= '<br/><div>' . trim($Fila["DESC_011"]) . '</div';
												$texto .= '</td>';
												$mnCuenta++;
												if ($mnCuenta == 4) {
													$texto .= '</tr>';
													$mnCuenta = 0;
												}
											}
											if ($mnCuenta == 1) {
												$texto .= '<td></td><td></td><td></td></tr>';
											}
											if ($mnCuenta == 2) {
												$texto .= '<td></td><td></td></tr>';
											}
											if ($mnCuenta == 3) {
												$texto .= '<td></td></tr>';
											}
											
											$texto .= '</table>';
											
											echo($texto);
										?>
									</div>
								</div>
								<!--Fin del DIV Columna SOPORTE-->
							</div>
							<!--Fin del DIV de Tab SOPORTE-->
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
<script>
	var mCedula;
	var codEstudiante;
	var existeCedula;
	var parametros;
	var datosJson;

	function verificarFormulario()
	{
		var edad = document.getElementById('txtNumEdad').value;

		if(document.getElementById('txtNombres').value=="")
		{
			document.getElementById('txtNombres').focus();
			$.messager.alert('INOFE','Falta el Nombre.','warning');
			return false;
		}
		
		if(document.getElementById('txtApellidos').value=="")
		{
			document.getElementById('txtApellidos').focus();
			$.messager.alert('INOFE','Falta el Apellido.','warning');
			return false;
		}
		
		if (edad >= 18)
		{
			if (document.getElementById('txtCedula').value=="")
			{
				document.getElementById('txtCedula').focus();
				$.messager.alert('INOFE','Falta la Cédula.','warning');
				return false;
			}
			
			if(mCedula.indexOf("-") > -1)
			{
				document.getElementById('txtCedula').focus();
				$.messager.alert('INOFE','Escriba la Cédula sin guiones.','warning');
				return false;
			}		

			if (existeCedula == true)
			{
				document.getElementById('txtCedula').focus();
				$.messager.alert('INOFE','La Cédula ya fue registrada con el estudiante ' + codEstudiante,'warning');
				return false;
			}
		}
		else
		{
			if (document.getElementById('txtCedula').value!="")
			{
				if(mCedula.indexOf("-") > -1)
				{
					document.getElementById('txtCedula').focus();
					$.messager.alert('INOFE','Escriba la Cédula sin guiones.','warning');
					return false;
				}		

				if (existeCedula == true)
				{
					document.getElementById('txtCedula').focus();
					$.messager.alert('INOFE','La Cédula ya fue registrada con el estudiante ' + codEstudiante,'warning');
					return false;
				}
			}
		}
		return true;
	}

	function calcularEdad()
	{
	  var today_date = new Date();
	  var today_year = today_date.getFullYear();
	  var today_month = today_date.getMonth();
	  var today_day = today_date.getDate();
	  var birth_date = document.getElementById("dtpFechaNac").value;
	  var birth_year = parseInt(birth_date.substr(0,4));
	  var birth_month = parseInt(birth_date.substr(5,2));
	  var birth_day = parseInt(birth_date.substr(7,2));

	  var age = today_year - birth_year;
	
	  if (today_month < (birth_month - 1)) {
		age--;
	  }
	  if (((birth_month - 1) == today_month) && (today_day < birth_day)) {
		age--;
	  }
	  document.getElementById("txtEdad").value = age + " años";
	  document.getElementById("txtNumEdad").value = age;
	}

	function llenaArchivo() {
		$('#txtRutaLocal').val($('#archivo')[0].files[0].name); //txtRutaLocal
	}

	function borrarImagen(objeto) {
		var objId = objeto.id;
		var datos = new FormData();
		var estudiante = $('#txtEstudiante').val();
		datos.append('CodEstudiante', estudiante);
		datos.append('CodImagen', objId);

		$.ajax({
			url: 'funciones/fxEstudiantesImagenes.php',
			type: 'post',
			data: datos,
			contentType: false,
			processData: false,
			success: function(response) {
				if (response != 0) {
					document.getElementById('dvSOP').innerHTML = response;
				} else {
					$.messager.alert('KDSA', 'Error en la eliminación de la imagen.', 'warning');
				}
			}
		});
	}
	window.onload=function()
	{
		if (document.getElementById("dtpFechaNac").value != "")
			calcularEdad();
	}

	$(document).ready(function() {
		$('#cmdSubir').on('click', function() {
			if ($('#txtEstudiante').val() == '') {
				$.messager.alert('KDSA', 'Debe guardar la Información General antes de subir los Documentos de soporte.', 'warning');
				return false;
			}

			if ($('#txtRutaLocal').val() == '') {
				$.messager.alert('KDSA', 'Falta el archivo de la imagen.', 'warning');
				return false;
			} else {
				var datos = new FormData();
				var files = $('#archivo')[0].files[0];
				var estudiante = $('#txtEstudiante').val();
				var descripcion = $('#cboDescripcion option:selected').text();
				datos.append('archivo', files);
				datos.append('txtEstudiante', estudiante);
				datos.append('txtDescripcion', descripcion);

				$.ajax({
					url: 'funciones/fxEstudiantesImagenes.php',
					type: 'post',
					data: datos,
					contentType: false,
					processData: false,
					success: function(response) {
						if (response != 0) {
							document.getElementById('dvSOP').innerHTML = response;
							$('#cboDescripcion').val('0');
							$('#txtRutaLocal').val('');
						} else {
							$.messager.alert('KDSA', 'Error en la subida de la imagen.', 'warning');
						}
					}
				});
				return false;
			}
		});
	});

	$('form').submit(function(e){
		e.preventDefault();

		$.when(ejecutarAjax()).done(function(respuesta)
		{
			if (respuesta != "")
				existeCedula = true;
			else
				existeCedula = false;

			codEstudiante = respuesta;
		})
		
		if (verificarFormulario() == true)
		{
			var texto;
			var datos;
			
			texto = '{"txtEstudiante":"' + document.getElementById("txtEstudiante").value + '", ';
			texto += '"txtNombres":"' + document.getElementById("txtNombres").value + '", ';
			texto += '"txtApellidos":"' + document.getElementById("txtApellidos").value + '", ';
		
			if (document.getElementById("optSexo1").checked)
				texto += '"optSexo":"M", ';
			else
				texto += '"optSexo":"F", ';
			
			texto += '"txtCedula":"' + document.getElementById("txtCedula").value + '", ';
			texto += '"dtpFechaNac":"' + document.getElementById("dtpFechaNac").value + '", ';
			texto += '"txtDomicilio":"' + document.getElementById("txtDomicilio").value + '", ';
			texto += '"txtDireccion":"' + document.getElementById("txtDireccion").value + '", ';
			texto += '"txtTelefono":"' + document.getElementById("txtTelefono").value + '", ';
			texto += '"txtCelular":"' + document.getElementById("txtCelular").value + '", ';
			texto += '"txtCorreo":"' + document.getElementById("txtCorreo").value + '", ';
			texto += '"txtEmergencia":"' + document.getElementById("txtEmergencia").value + '", ';
			texto += '"txtParentesco":"' + document.getElementById("txtParentesco").value + '", ';
			texto += '"txtNivAcademico":"' + document.getElementById("txtNivAcademico").value + '", ';
			
			if (document.getElementById("optPostGrado1").checked)
				texto += '"optPostGrado":"0", ';
			else
				texto += '"optPostGrado":"1", ';
				
			if (document.getElementById("optMaestria1").checked)
				texto += '"optMaestria":"0", ';
			else
				texto += '"optMaestria":"1", ';
				
			texto += '"txtLugarTrabajo":"' + document.getElementById("txtLugarTrabajo").value + '", ';
			texto += '"txtPuesto":"' + document.getElementById("txtPuesto").value + '", ';
			texto += '"txtTelEmpresa":"' + document.getElementById("txtTelEmpresa").value + '"}';

			datos = JSON.parse(texto);

			$.ajax({
				url:'catEstudiantes.php',
				type:'post',
				data:datos,
				beforeSend: function(){console.log(datos)}
			})
			.done(function(){location.href="gridEstudiantes.php"})
			.fail(function(){console.log('Error')});
			}
		}
	);
	
	function ejecutarAjax()
	{
		mCedula = document.getElementById('txtCedula').value;
		parametros = '{"cedulaEstudiante":"'+document.getElementById("txtCedula").value+'", "codEstudiante":"' +document.getElementById("txtEstudiante").value+ '"}';
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