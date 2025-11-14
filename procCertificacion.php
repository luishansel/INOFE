<?php
	session_start();
	if (!isset($_SESSION["gnVerifica"]) or $_SESSION["gnVerifica"] != 1)
	{
		echo('<meta http-equiv="Refresh" content="0;url=index.php">');
		exit('');
    }
	
	include ("MasterWeb.php");
	require_once ("funciones/fxGeneral.php");
	require_once ("funciones/fxUsuarios.php");
	require_once ("funciones/fxCursos.php");
	require_once ("funciones/fxCertificacion.php");

	$m_cnx_MySQL = fxAbrirConexion();
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
		$PermisoUsuario = fxPermisoUsuario("procCertificacion", $mbAgregar, $mbModificar, $mbBorrar, $mbAnular);
		
		if ($Administrador == 0 and $PermisoUsuario == 0)
		{ ?>
        <div class="container text-center">
        	<div id="DivContenido">
				<img src="imagenes/errordeacceso.png"/>
            </div>
        </div>
		<?php }
		else
		{
			if (isset($_POST["msCurso"]))
			{
				$codCurso = $_POST["msCurso"];
				$msConsulta = "select CERTIFICACION_REL, FECHAELABORACION_170, FECHAACTUALIZACION_170 from KDSA170A where CURSO_REL = ?";
				$mDatos = $m_cnx_MySQL->prepare($msConsulta);
				$mDatos->execute([$codCurso]);
				$mnRegistros = $mDatos->rowCount();
				
				if ($mnRegistros > 0)
				{
					$Fila = $mDatos->fetch();
					$codCodigo = $Fila["CERTIFICACION_REL"];
					$msFecha = date_create_from_format('Y-m-d H:i:s', $Fila["FECHAELABORACION_170"]);
					$msFechaElaboracion = date_format($msFecha, 'd/m/Y h:i:s a');
					$msFecha = date_create_from_format('Y-m-d H:i:s', $Fila["FECHAACTUALIZACION_170"]);
					$msFechaActualizacion = date_format($msFecha, 'd/m/Y h:i:s a');
				}
				else
				{
					$codCodigo = "";
					$msFechaElaboracion = "";
					$msFechaActualizacion = "";
				}
				$msConsulta = "select concat(NOMBRE_020, ' (', CONVOCATORIA_020, ' / ', 'G', GRUPO_020, ')') as CONVOCATORIA, HORAINI_020, HORAFIN_020, fxDevuelveDias(CURSO_REL) as DIAS, ACTIVO_020, CERTIFICAR_020 from KDSA020A where CURSO_REL = ?";
				$mDatos = $m_cnx_MySQL->prepare($msConsulta);
				$mDatos->execute([$codCurso]);
				$Fila = $mDatos->fetch();
				$msNomCurso = $Fila["CONVOCATORIA"];
				$msHoraIni = date_create($Fila["HORAINI_020"]);
				$msHoraFin = date_create($Fila["HORAFIN_020"]);
				$msDias = $Fila["DIAS"];
				$msHorario = $msDias . " / De " . date_format($msHoraIni, 'h:i:s a') . " a " . date_format($msHoraFin, 'h:i:s a');
				$mbActivo = $Fila["ACTIVO_020"];
				$mbCertificar = $Fila["CERTIFICAR_020"];
			}

			if (isset($_POST["cmdGuardar"]))
            {
				$codCodigo = $_POST["msCodigo"];
				$codCurso = $_POST["msCurso"];

				if (isset($_POST["gridDetalles"]))
				{
					$gridDetalles = $_POST["gridDetalles"];
					$mnLinea = 1;
					
					foreach($gridDetalles as $Registro)
					{
						if ($mnLinea == 1)
						{
							$mnMatIniM = $Registro['mujeres'];
							$mnMatIniV = $Registro['varones'];
						}

						if ($mnLinea == 2)
						{
							$mnMatFinM = $Registro['mujeres'];
							$mnMatFinV = $Registro['varones'];
						}

						if ($mnLinea == 3)
						{
							$mnDesercionM = $Registro['mujeres'];
							$mnDesercionV = $Registro['varones'];
						}

						if ($mnLinea == 4)
						{
							$mnCertificadosM = $Registro['mujeres'];
							$mnCertificadosV = $Registro['varones'];
						}

						$mnLinea ++;
					}
				}

				if (isset($_POST["gridCertificacion"]))
					$gridCertificacion = $_POST["gridCertificacion"];

				if ($codCodigo == "")
				{
					$codCodigo = fxGuardarCertificacion($codCurso, $mnMatIniM, $mnMatIniV, $mnMatFinM, $mnMatFinV, $mnDesercionM, $mnDesercionV, $mnCertificadosM, $mnCertificadosV);
					fxAgregarBitacora($_SESSION["gsUsuario"], "KDSA170A", $codCodigo, "", "Agregar");
				}
				else
				{
					fxModificarCertificacion($codCodigo, $mnMatIniM, $mnMatIniV, $mnMatFinM, $mnMatFinV, $mnDesercionM, $mnDesercionV, $mnCertificadosM, $mnCertificadosV);
					fxAgregarBitacora($_SESSION["gsUsuario"], "KDSA170A", $codCodigo, "", "Modificar");
					fxBorrarDetCertificacion($codCodigo);
				}		

				foreach($gridCertificacion as $Registro)
				{
					$msMatricula = $Registro['matricula'];
					$msCedula = $Registro['cedula'];
					$mbAcademicos = $Registro['academicos'];
					$mbNotas = $Registro['notas'];
					$mbArancelCompleto = $Registro['arancelCompleto'];
					$mnAsistencia = substr($Registro['asistencia'], 0, -1);
					$msEstado = $Registro['estado'];
					$msTomoKdsa = $Registro['tomokdsa'];
					$msFolioKdsa = $Registro['foliokdsa'];
					$msActaKdsa = $Registro['actakdsa'];
					
					fxGuardarDetCertificacion ($codCodigo, $msMatricula, $msCedula, $mbAcademicos, $mbNotas, $mbArancelCompleto, $mnAsistencia, $msEstado, $msTomoKdsa, $msFolioKdsa, $msActaKdsa);
				}
			}
		?>
		<div class="container">
        	<div id="DivContenido">
				<div class = "row">
					<div class="col-xs-12 col-md-11">
						<div class="degradado"><strong>Control de certificaciones</strong></div>
					</div>
				</div>

                <div class="row">
                    <div class="col-md-12">
                        <form id="procCertificacion" name="procCertificacion" method="post">
							<div class="row" style="margin-bottom: 3px">
								<div class="col-auto col-md-12">
									<input type="button" id="Guardar" name="Guardar" value="Guardar" class="btn btn-warning" onclick="guardar()" />
									<input type="button" id="Imprimir" name="Imprimir" value="Generar el acta" class="btn btn-warning" onclick="generarActa()" />
									<input type="button" id="Cancelar" name="Cancelar" value="Volver a la pantalla anterior" class="btn btn-warning" onclick="location.href='frmCertificacion.php';" />
								</div>
							</div>
							<div class="form-group row">
								<label for="txtCodCertificacion" class="col-sm-12 col-md-2 col-form-label">Certificación</label>
								<div class="col-sm-12 col-md-3">
									<?php echo('<input type="text" class="form-control" id="txtCodCertificacion" name="txtCodCertificacion" value="' . $codCodigo . '" readonly />'); ?>
								</div>
								<div class="col-auto">
									<?php 
										echo('<input type="text" class="form-control" id="txtCodCurso" name="txtCodCurso" value="' . trim($codCurso) . '" style="display: none" />');
										echo('<input type="number" class="form-control" id="txnActivo" name="txnActivo" value="' . trim($mbActivo) . '" style="display: none" />');
										echo('<input type="number" class="form-control" id="txnCertificar" name="txnCertificar" value="' . trim($mbCertificar) . '" style="display: none" />');
									?>
								</div>
							</div>

							<?php
								//Verifica si aún existen estudiantes sin certificar
								$msConsulta = "select count(MATRICULA_REL) as CONTEO from KDSA030A, KDSA010A where KDSA030A.ESTUDIANTE_REL = KDSA010A.ESTUDIANTE_REL and ESTADO_030 = 0 and CURSO_REL = ?";
								$mDatos = $m_cnx_MySQL->prepare($msConsulta);
								$mDatos->execute([$codCurso]);
								$Fila = $mDatos->fetch();
								$mnSinCertificar = rtrim($Fila["CONTEO"]);
							?>

							<div class="form-group row">
								<label for="cboTomo" class="col-sm-12 col-md-2 col-form-label">Tomo para la certificación</label>
								<div class="col-sm-12 col-md-5">
									<?php
										if ($mnSinCertificar > 0)
											echo ('<select class="form-control" id="cboTomo" name="cboTomo">');
										else
											echo ('<select class="form-control" id="cboTomo" name="cboTomo" disabled>');
											
										$msConsulta = "select TOMO_REL, DESCRIPCION_180 from KDSA180A where CERRADO_180 = 0 order by TOMO_REL desc";
										$mDatos = $m_cnx_MySQL->prepare($msConsulta);
										$mDatos->execute();
										while ($Fila = $mDatos->fetch())
										{
											$Valor = rtrim($Fila["TOMO_REL"]);
											$Texto = rtrim($Fila["DESCRIPCION_180"]);
											echo("<option value='" . $Valor . "'>" . $Texto . "</option>");
										}
										echo('</select>');
									?>
								</div>
							</div>

							<div class="form-group row">
								<label for="txtCurso" class="col-sm-12 col-md-2 col-form-label">Curso</label>
								<div class="col-sm-12 col-md-10">
									<?php echo('<input type="text" class="form-control" id="txtCurso" name="txtCurso" value="' . $msNomCurso . '" readonly />'); ?>
								</div>
							</div>

							<div class="form-group row">
								<label for="txtHorario" class="col-sm-12 col-md-2 col-form-label">Horario</label>
								<div class="col-sm-12 col-md-10">
									<?php echo('<input type="text" class="form-control" id="txtHorario" name="txtHorario" value="' . $msHorario . '" readonly />'); ?>
								</div>
							</div>

							<div class="form-group row">
								<label for="txtFechaElaboracion" class="col-sm-12 col-md-2 col-form-label">Fecha de elaboración</label>
								<div class="col-sm-12 col-md-3">
									<?php echo('<input type="text" class="form-control" id="txtFechaElaboracion" name="txtFechaElaboracion" value="' . $msFechaElaboracion . '" readonly />'); ?>
								</div>
							</div>

							<div class="form-group row">
								<label for="txtFechaActualizacion" class="col-sm-12 col-md-2 col-form-label">Ultima actualización</label>
								<div class="col-sm-12 col-md-3">
									<?php echo('<input type="text" class="form-control" id="txtFechaActualizacion" name="txtFechaActualizacion" value="' . $msFechaActualizacion . '" readonly />'); ?>
								</div>
							</div>

							<div class="form-group row">
								<label class="col-sm-12 col-md-2 col-form-label">Estado del curso</label>
								<div class="col-sm-12 col-md-6">
								<?php
									$nombreArchivo = fxEscribeJsonEstado($codCurso);
								?>
									<table id="dgEST" class="easyui-datagrid" style="width:100%" data-options="iconCls:'icon-edit', nowrap:'false', striped:'true', singleSelect:true, url:'<?php echo(rtrim($nombreArchivo)); ?>', method:'get'">
										<thead>
											<tr>
												<th data-options="field:'concepto', width:'40%', align:'left'"></th>
												<th data-options="field:'mujeres', width:'20%', align:'center'">Mujeres</th>
												<th data-options="field:'varones', width:'20%', align:'center'">Varones</th>
												<th data-options="field:'total', width:'20%', align:'center'">Total</th>
											</tr>
										</thead>
									</table>
								</div>
							</div>

							<div class="row" style="margin-top: 1%">
								<div class="col-md-12">
								<?php
									$nombreArchivo = fxEscribeJson($codCurso);
								?>
									<table id="dgCER" class="easyui-datagrid" style="width:100%" data-options="iconCls:'icon-edit', toolbar:'#tbCER', nowrap:'false', striped:'true', singleSelect:true, url:'<?php echo(rtrim($nombreArchivo)); ?>', method:'get', onClickCell: onClickCell">
										<thead>
											<tr>
												<th rowspan="2" data-options="field:'matricula', hidden:true">Matrícula</th>
												<th rowspan="2" data-options="field:'nombrecompleto', width:'20%', align:'left'">Estudiante</th>
												<th rowspan="2" data-options="field:'notas', width:'12%', align:'center', formatter:function(value){return value==1 ? 'Si' : 'No';}">Notas Completas</th>
												<th rowspan="2" data-options="field:'estado', width:'12%', align:'center'">Estado</th>
												<th rowspan="2" data-options="field:'asistencia', width:'12%', align:'center'">Asistencia</th>
												<th rowspan="2" data-options="field:'arancelcompleto', width:'12%', align:'center', formatter:function(value){return value==1 ? 'Si' : 'No';}">Arancel Completo</th>
												<th colspan="2">Documentacion</th>
												<th colspan="3">INOFE</th>
											</tr>
											<tr>
												<th data-options="field:'cedula', width:'6%', align:'center', formatter:function(value){return value==1 ? 'Si' : 'No';}">Cédula</th>
												<th data-options="field:'academicos', width:'7%', align:'center', formatter:function(value){return value==1 ? 'Si' : 'No';}">Académico</th>
												<th data-options="field:'tomokdsa', width:'6%', align:'left'">Tomo</th>
												<th data-options="field:'foliokdsa', width:'6%', align:'left'">Folio</th>
												<th data-options="field:'actakdsa', width:'6%', align:'left'">Acta</th>
											</tr>
										</thead>
									</table>
								</div>
							</div>

							<div id="tbCER" style="height:auto">
								<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-save',plain:true" onclick="acceptit()">Aceptar</a>
								<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-undo',plain:true" onclick="reject()">Deshacer</a>
							</div>
                        </form>
                    </div>
                </div>
            </div>
    	</div>
<?php }} ?>
</body>
</html>

<script>
	var editIndex = undefined;
	var lastIndex;

	function verificarFormulario()
	{
		var gridCertificacion = $('#dgCER').datagrid('getData');
		var registros = $('#dgCER').datagrid('getRows').length - 1;
		var activo = document.getElementById("txnActivo").value;
		var certificar = document.getElementById("txnCertificar").value;

		if (activo == 1 && certificar == 0)
		{
			$.messager.alert('INOFE', 'No se puede certificar un curso activo. Cambie la configuración del curso si desea hacerlo.', 'warning');
			return false;
		}

		if (registros < 0)
		{
			$.messager.alert('INOFE', 'Faltan los Estudiantes', 'warning');
			return false;
		}

		for (i = 0; i <= registros; i++) 
		{
			if (gridCertificacion.rows[i].tomokdsa != "" || gridCertificacion.rows[i].foliokdsa != "" || gridCertificacion.rows[i].actakdsa != "")
			{
				if (gridCertificacion.rows[i].tomokdsa != "" && gridCertificacion.rows[i].foliokdsa == "" && gridCertificacion.rows[i].actakdsa == "")
				{
					$.messager.alert('INOFE', 'Falta el Folio y Acta de ' + gridCertificacion.rows[i].nombrecompleto + ' en INOFE', 'warning');
					return false;
				}

				if (gridCertificacion.rows[i].tomokdsa == "" && gridCertificacion.rows[i].foliokdsa != "" && gridCertificacion.rows[i].actakdsa == "")
				{
					$.messager.alert('INOFE', 'Falta el Tomo y Acta de ' + gridCertificacion.rows[i].nombrecompleto + ' en INOFE', 'warning');
					return false;
				}

				if (gridCertificacion.rows[i].tomokdsa == "" && gridCertificacion.rows[i].foliokdsa == "" && gridCertificacion.rows[i].actakdsa != "")
				{
					$.messager.alert('INOFE', 'Falta el Tomo y Folio de ' + gridCertificacion.rows[i].nombrecompleto + ' en INOFE', 'warning');
					return false;
				}

				if (gridCertificacion.rows[i].tomokdsa == "" && gridCertificacion.rows[i].foliokdsa != "" && gridCertificacion.rows[i].actakdsa != "")
				{
					$.messager.alert('INOFE', 'Falta el Tomo de ' + gridCertificacion.rows[i].nombrecompleto + ' en INOFE', 'warning');
					return false;
				}

				if (gridCertificacion.rows[i].tomokdsa != "" && gridCertificacion.rows[i].foliokdsa == "" && gridCertificacion.rows[i].actakdsa != "")
				{
					$.messager.alert('INOFE', 'Falta el Folio de ' + gridCertificacion.rows[i].nombrecompleto + ' en INOFE', 'warning');
					return false;
				}

				if (gridCertificacion.rows[i].tomokdsa != "" && gridCertificacion.rows[i].foliokdsa != "" && gridCertificacion.rows[i].actakdsa == "")
				{
					$.messager.alert('INOFE', 'Falta el Acta de ' + gridCertificacion.rows[i].nombrecompleto + ' en INOFE', 'warning');
					return false;
				}
			}
		}

		return true;
	}

	$('#dgCER').datagrid({
		onClickRow: function(rowIndex) {
			if (lastIndex != rowIndex) {
				$(this).datagrid('endEdit', lastIndex);
				$(this).datagrid('beginEdit', rowIndex);
			}
			lastIndex = rowIndex;
		}
	})

	function endEditing() {
		if (editIndex == undefined) {
			return true
		}
		if ($('#dgCER').datagrid('validateRow', editIndex)) {
			$('#dgCER').datagrid('endEdit', editIndex);
			editIndex = undefined;
			return true;
		} else {
			return false;
		}
	}

	function onClickCell(index, field) {
		if (editIndex != index) {
			if (endEditing()) {
				$('#dgCER').datagrid('selectRow', index)
					.datagrid('beginEdit', index);
				editIndex = index;
			} else {
				setTimeout(function() {
					$('#dgCER').datagrid('selectRow', editIndex);
				}, 0);
			}
		}
	}

	function removeit() {
		if (editIndex == undefined) {
			return
		}
		$('#dgCER').datagrid('cancelEdit', editIndex)
			.datagrid('deleteRow', editIndex);
		editIndex = undefined;
	}

	function acceptit() {
		if (endEditing()) {
			$('#dgCER').datagrid('acceptChanges');
		}
	}

	function reject() {
		$('#dgCER').datagrid('rejectChanges');
		editIndex = undefined;
	}

	function guardar(tipoRedireccion) {
		if (verificarFormulario())
		{
			var datos;
			var texto;
			var i;
			var gridCertificacion = $('#dgCER').datagrid('getData');
			var gridDetalles = $('#dgEST').datagrid('getData');
			var registros = $('#dgCER').datagrid('getRows').length - 1;
			var codCertificacion = document.getElementById('txtCodCertificacion').value;
			var codCurso = document.getElementById('txtCodCurso').value;

			texto = '{"cmdGuardar":"1", ';
			texto += '"msCodigo": "' + codCertificacion + '",';
			texto += '"msCurso": "' + codCurso + '",';
			texto += '"gridCertificacion": [';
			for (i = 0; i <= registros; i++) {
				texto += '{"matricula":"' + gridCertificacion.rows[i].matricula + '", "cedula":"' + gridCertificacion.rows[i].cedula;
				texto += '", "academicos":"' + gridCertificacion.rows[i].academicos + '", "notas":"' + gridCertificacion.rows[i].notas;
				texto += '", "arancelCompleto":"' + gridCertificacion.rows[i].arancelcompleto + '", "estado":"' + gridCertificacion.rows[i].estado;
				texto += '", "asistencia":"' + gridCertificacion.rows[i].asistencia;
				texto += '", "tomokdsa":"' + gridCertificacion.rows[i].tomokdsa + '", "foliokdsa":"' + gridCertificacion.rows[i].foliokdsa + '", "actakdsa":"' + gridCertificacion.rows[i].actakdsa;
				texto += '"},';

				if (i == registros)
					texto = texto.substr(0, texto.length - 1) + '],';
			}
			texto += '"gridDetalles": [';
			texto += '{"mujeres":"' + gridDetalles.rows[0].mujeres + '", "varones":"' + gridDetalles.rows[0].varones + '"},';
			texto += '{"mujeres":"' + gridDetalles.rows[1].mujeres + '", "varones":"' + gridDetalles.rows[1].varones + '"},';
			texto += '{"mujeres":"' + gridDetalles.rows[2].mujeres + '", "varones":"' + gridDetalles.rows[2].varones + '"},';
			texto += '{"mujeres":"' + gridDetalles.rows[3].mujeres + '", "varones":"' + gridDetalles.rows[3].varones + '"}]}';
			
			datos = JSON.parse(texto);

			$.ajax({
				url: 'procCertificacion.php',
				type: 'post',
				data: datos,
				success: function() {
					$('form').submit();
					{
						$.messager.alert('INOFE', 'Registros Guardados.', 'info');
						$.redirect("frmCertificacion.php");
					}
				}
			})
		}
	}

	function generarActa()
	{
		var certificacion = document.getElementById("txtCodCertificacion").value;
		var tomo = document.getElementById("cboTomo").value;

		if (certificacion == "")
			$.messager.alert('INOFE', 'Ejecute la Certificación antes de emitir el acta', 'info');
		else
		{
			$.redirect("repGenerarActa.php", {codigo: certificacion, tomo: tomo}, "POST", "_blank");
			$.redirect("frmCertificacion.php");
		}
	}
</script>

<?php
function fxEscribeJson($msCurso)
{
	$m_cnx_MySQL = fxAbrirConexion();
	$nombreArchivo = "CER" . date('YmdHis') . ".json";
	$archivo = fopen($nombreArchivo, "w");
	
	//Escribe el Json
	$msConsulta = "select MATRICULA_REL, DOCIDENTIDAD_030, DOCACADEMICO_030, ESTADO_030, concat(NOMBRES_010, ' ', APELLIDOS_010) as NOMBRECOMPLETO, CELULAR_010 ";
	$msConsulta .= "from KDSA030A, KDSA010A where ESTADO_030 <> 4 and KDSA010A.ESTUDIANTE_REL = KDSA030A.ESTUDIANTE_REL and CURSO_REL = ? ";
	$msConsulta .= "order by MATRICULA_REL";
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute([$msCurso]);
	$numRegistros = $mDatos->rowCount();

	fwrite($archivo, "[" . PHP_EOL);
	
	if ($numRegistros > 0)
	{
		for ($i = 1; $i <= $numRegistros; $i++)
		{
			$Fila = $mDatos->fetch();
			$msMatricula = trim($Fila['MATRICULA_REL']);
			$mbCedula = $Fila['DOCIDENTIDAD_030'];
			$mbAcademicos = $Fila['DOCACADEMICO_030'];
			$mnEstado = intval($Fila['ESTADO_030']);
			$msNombreCompleto = trim($Fila['NOMBRECOMPLETO']);
			$msCelular = trim($Fila['CELULAR_010']);
			
			switch ($mnEstado)
			{
				case 0:
					$msEstado = "Activo";
					break;
				case 1:
					$msEstado = "Inactivo";
					break;
				case 2:
					$msEstado = "Deserción";
					break;
				case 3:
					$msEstado = "Certificado";
					break;
				case 4:
					$msEstado = "Anulado";
				case 5:
					$msEstado = "Baja";
			}

			//Aranceles
			$msConsulta = "select count(MATRICULA_REL) as CONTEO FROM KDSA051A, KDSA050A where KDSA050A.COBRO_REL = KDSA051A.COBRO_REL and PAGADO_051 = 0 and EXONERADO_051 = 0 and ANULADO_051 = 0 and MATRICULA_REL = ?";
			$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
			$mAuxiliar->execute([$msMatricula]);
			$fAux = $mAuxiliar->fetch();
			$mnConteo = $fAux["CONTEO"];

			if ($mnConteo > 0)
				$mbACompleto = 0;
			else
				$mbACompleto = 1;

			//Calificaciones
			$msConsulta = "select KDSA021A.MODULO_REL, ifnull(CALIFICACION_REL, '') as CALIFICACION_REL from KDSA021A left join KDSA150A ";
			$msConsulta .= "on KDSA021A.MODULO_REL = KDSA150A.MODULO_REL where CURSO_REL = ?;";
			$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
			$mAuxiliar->execute([$msCurso]);
			$mbNotas = 1;
			if ($mAuxiliar->rowCount() == 0)
				$mbNotas = 0;
			else
			{
				while ($fAux = $mAuxiliar->fetch())
				{
					$msCalificacion = $fAux["CALIFICACION_REL"];

					if ($msCalificacion == '')
						$mbNotas = 0;
					else
					{
						$msConsulta = "select PUNTAJE_151 from KDSA151A where CALIFICACION_REL = ? and MATRICULA_REL = ?";
						$mNotas = $m_cnx_MySQL->prepare($msConsulta);
						$mNotas->execute([$msCalificacion, $msMatricula]);
						$mnCuenta = $mNotas->rowCount();

						if ($mnCuenta == 0)
							$mbNotas = 0;
						else
						{
							$fNotas = $mNotas->fetch();
							$mnPuntaje = intval($fNotas["PUNTAJE_151"]);
							if ($mnPuntaje < 70)
								$mbNotas = 0;
						}
					}
				}
			}

			//Asistencias
			$msConsulta = "select count(*) as CONTEO from KDSA141A where ESTADO_141 = 0 and MATRICULA_REL = ?";
			$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
			$mAuxiliar->execute([$msMatricula]);
			if ($mAuxiliar->rowCount() > 0)
			{
				$fAux = $mAuxiliar->fetch();
				$mnCuentaAsistencias = $fAux["CONTEO"];
			}
			else
				$mnCuentaAsistencias = 0;

			$msConsulta = "select FECHAINI_020, FECHAFIN_020, DOMINGO_020, LUNES_020, MARTES_020, MIERCOLES_020, JUEVES_020, VIERNES_020, SABADO_020 from KDSA020A where CURSO_REL = ?";
			$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
			$mAuxiliar->execute([$msCurso]);
			$fAux = $mAuxiliar->fetch();
			$mdFechaIni = strtotime($fAux["FECHAINI_020"]);
			$mdFechaFin = strtotime($fAux["FECHAFIN_020"]);
			$mbDomingo = $fAux["DOMINGO_020"];
			$mbLunes = $fAux["LUNES_020"];
			$mbMartes = $fAux["MARTES_020"];
			$mbMiercoles = $fAux["MIERCOLES_020"];
			$mbJueves = $fAux["JUEVES_020"];
			$mbViernes = $fAux["VIERNES_020"];
			$mbSabado = $fAux["SABADO_020"];
			$mnSesiones = 0; //Cuenta las sesiones dentro del curso

			for ($j = $mdFechaIni; $j <= $mdFechaFin; $j+=86400) //86400 segundos en el día
			{
				$diaClase = date("w", $j);

				if ($mbDomingo == 1 and $diaClase == 0)
				{
					$mnSesiones++;
				}

				if ($mbLunes == 1 and $diaClase == 1)
				{
					$mnSesiones++;
				}

				if ($mbMartes == 1 and $diaClase == 2)
				{
					$mnSesiones++;
				}

				if ($mbMiercoles == 1 and $diaClase == 3)
				{
					$mnSesiones++;
				}

				if ($mbJueves == 1 and $diaClase == 4)
				{
					$mnSesiones++;
				}

				if ($mbViernes == 1 and $diaClase == 5)
				{
					$mnSesiones++;
				}

				if ($mbSabado == 1 and $diaClase == 6)
				{
					$mnSesiones++;
				}
			}

			$msConsulta = "select IFNULL(COUNT(*),0) as CONTEO from KDSA022A where CURSO_REL = ?";
			$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
			$mAuxiliar->execute([$msCurso]);
			$fAux = $mAuxiliar->fetch();
			$mnFeriados = $fAux["CONTEO"];
			$mnSesiones -= $mnFeriados; //Cantidad real de sesiones (Sin los feriados)
			if ($mnSesiones == 0)
				$mnAsistencia = 0;
			else
				$mnAsistencia = ($mnCuentaAsistencias * 100) / $mnSesiones;

			//Detalle de la certificación
			$msConsulta = "select TOMO_KDSA_171, FOLIO_KDSA_171, ACTA_KDSA_171 from KDSA171A where MATRICULA_REL = ?";
			$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
			$mAuxiliar->execute([$msMatricula]);
			
			if ($mAuxiliar->rowCount() > 0)
			{
				$fAux = $mAuxiliar->fetch();
				$msTomoKdsa = trim($fAux["TOMO_KDSA_171"]);
				$msFolioKdsa = trim($fAux["FOLIO_KDSA_171"]);
				$msActaKdsa = trim($fAux["ACTA_KDSA_171"]);
			}
			else
			{
				$msTomoKdsa = "";
				$msFolioKdsa = "";
				$msActaKdsa = "";
			}
			fwrite($archivo, '{"matricula":"' . trim($msMatricula) . '", ');
			fwrite($archivo, '"nombrecompleto":"' . trim($msNombreCompleto) . '", ');
			fwrite($archivo, '"cedula":"' . trim($mbCedula) . '", ');
			fwrite($archivo, '"academicos":"' . trim($mbAcademicos) . '", ');
			fwrite($archivo, '"notas":"' . trim($mbNotas) . '", ');
			fwrite($archivo, '"arancelcompleto":"' . trim($mbACompleto) . '", ');
			fwrite($archivo, '"asistencia":"' . round($mnAsistencia, 0) . '%", ');
			fwrite($archivo, '"estado":"' . trim($msEstado) . '", ');
			fwrite($archivo, '"tomokdsa":"' . trim($msTomoKdsa) . '", ');
			fwrite($archivo, '"foliokdsa":"' . trim($msFolioKdsa) . '", ');
			fwrite($archivo, '"actakdsa":"' . trim($msActaKdsa) . '"');

			if ($i == $numRegistros)
			{
				fwrite($archivo, "}" . PHP_EOL);
			}
			else
			{
				fwrite($archivo, "}," . PHP_EOL);
			}
		}
	}
	fwrite($archivo, "]");
	fclose($archivo);
	
	return($nombreArchivo);
}

function fxEscribeJsonEstado($msCurso)
{
	$m_cnx_MySQL = fxAbrirConexion();
	$nombreArchivo = "EST" . date('YmdHis') . ".json";
	$archivo = fopen($nombreArchivo, "w");
	
	//Escribe el Json
	$msConsulta = "select MATRICULA_REL, ESTADO_030, SEXO_010 from KDSA030A, KDSA010A where KDSA030A.ESTUDIANTE_REL = KDSA010A.ESTUDIANTE_REL and ESTADO_030 <> 4 and CURSO_REL = ?";
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute([$msCurso]);

	$mnMatIniV = 0;
	$mnMatIniM = 0;
	$mnMatFinV = 0;
	$mnMatFinM = 0;
	$mnDesV = 0;
	$mnDesM = 0;
	$mnCertV = 0;
	$mnCertM = 0;
	$mnTotalMatIni = 0;
	$mnTotalMatFin = 0;
	$mnTotalDes = 0;
	$mnTotalCert = 0;

	while ($Fila = $mDatos->fetch())
	{
		$mnEstado = intval($Fila["ESTADO_030"]);
		$msSexo = trim($Fila["SEXO_010"]);

		if ($msSexo == "F")
		{
			$mnMatIniM++;

			if ($mnEstado != 2)
				$mnMatFinM++;

			if ($mnEstado == 2)
				$mnDesM++;

			if ($mnEstado == 3)
				$mnCertM++;
		}
		else
		{
			$mnMatIniV++;

			if ($mnEstado != 2)
				$mnMatFinV++;

			if ($mnEstado == 2)
				$mnDesV++;

			if ($mnEstado == 3)
				$mnCertV++;
		}
	}

	$mnTotalMatIni = $mnMatIniM + $mnMatIniV;
	$mnTotalMatFin = $mnMatFinM + $mnMatFinV;
	$mnTotalDes = $mnDesM + $mnDesV;
	$mnTotalCert = $mnCertM + $mnCertV;

	fwrite($archivo, "[" . PHP_EOL);
	fwrite($archivo, '{"concepto":"Matrícula inicial", ');
	fwrite($archivo, '"mujeres":"' . trim($mnMatIniM) . '", ');
	fwrite($archivo, '"varones":"' . trim($mnMatIniV) . '", ');
	fwrite($archivo, '"total":"' . trim($mnTotalMatIni) . '"},' . PHP_EOL);
	fwrite($archivo, '{"concepto":"Matrícula final", ');
	fwrite($archivo, '"mujeres":"' . trim($mnMatFinM) . '", ');
	fwrite($archivo, '"varones":"' . trim($mnMatFinV) . '", ');
	fwrite($archivo, '"total":"' . trim($mnTotalMatFin) . '"},' . PHP_EOL);
	fwrite($archivo, '{"concepto":"Deserciones", ');
	fwrite($archivo, '"mujeres":"' . trim($mnDesM) . '", ');
	fwrite($archivo, '"varones":"' . trim($mnDesV) . '", ');
	fwrite($archivo, '"total":"' . trim($mnTotalDes) . '"},' . PHP_EOL);
	fwrite($archivo, '{"concepto":"Certificaciones", ');
	fwrite($archivo, '"mujeres":"' . trim($mnCertM) . '", ');
	fwrite($archivo, '"varones":"' . trim($mnCertV) . '", ');
	fwrite($archivo, '"total":"' . trim($mnTotalCert) . '"}' . PHP_EOL);
	fwrite($archivo, "]");
	fclose($archivo);
	
	return($nombreArchivo);
}
?>