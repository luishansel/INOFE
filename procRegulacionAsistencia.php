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
	require_once ("funciones/fxRegulacionAsistencia.php");

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
		$PermisoUsuario = fxPermisoUsuario("procRegAsistencia", $mbAgregar, $mbModificar, $mbBorrar, $mbAnular);
		
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
				$msConsulta = "select REGULACION_REL, FECHAELABORACION_160, FECHAACTUALIZACION_160 from KDSA160A where CURSO_REL = ?";
				$mDatos = $m_cnx_MySQL->prepare($msConsulta);
				$mDatos->execute([$codCurso]);
				$mnRegistros = $mDatos->rowCount();
				if ($mnRegistros > 0)
				{
					$Fila = $mDatos->fetch();
					$codCodigo = $Fila["REGULACION_REL"];
					$msFecha = date_create_from_format('Y-m-d H:i:s', $Fila["FECHAELABORACION_160"]);
					$msFechaElaboracion = date_format($msFecha, 'd/m/Y h:i:s a');
					$msFecha = date_create_from_format('Y-m-d H:i:s', $Fila["FECHAACTUALIZACION_160"]);
					$msFechaActualizacion = date_format($msFecha, 'd/m/Y h:i:s a');
				}
				else
				{
					$codCodigo = "";
					$msFechaElaboracion = "";
					$msFechaActualizacion = "";
				}
				$msConsulta = "select concat(NOMBRE_020, ' (', CONVOCATORIA_020, ' / ', 'G', GRUPO_020, ')') as CONVOCATORIA, HORAINI_020, HORAFIN_020, fxDevuelveDias(CURSO_REL) as DIAS from KDSA020A where CURSO_REL = ?";
				$mDatos = $m_cnx_MySQL->prepare($msConsulta);
				$mDatos->execute([$codCurso]);
				$Fila = $mDatos->fetch();
				$msNomCurso = $Fila["CONVOCATORIA"];
				$msHoraIni = date_create($Fila["HORAINI_020"]);
				$msHoraFin = date_create($Fila["HORAFIN_020"]);
				$msDias = $Fila["DIAS"];
				$msHorario = $msDias . " / De " . date_format($msHoraIni, 'h:i:s a') . " a " . date_format($msHoraFin, 'h:i:s a');
			}

			if (isset($_POST["cmdGuardar"]))
            {
				$codCodigo = $_POST["msCodigo"];
				$codCurso = $_POST["msCurso"];

				if (isset($_POST["gridRegulacion"]))
					$gridRegulacion = $_POST["gridRegulacion"];

				if (isset($_POST["gridAusencia"]))
					$gridAusencia = $_POST["gridAusencia"];

				if ($codCodigo == "")
				{
					$codCodigo = fxGuardarRegulacion($codCurso);
					fxAgregarBitacora($_SESSION["gsUsuario"], "KDSA160A", $codCodigo, "", "Agregar");
				}
				else
				{
					fxModificarRegulacion($codCodigo);
					fxAgregarBitacora($_SESSION["gsUsuario"], "KDSA160A", $codCodigo, "", "Modificar");
				}

				foreach($gridRegulacion as $Registro)
				{
					$msMatricula = $Registro['matricula'];
					$mnAusencias = $Registro['ausencias'];
					$msRetirado = $Registro['retirado'];
					$msRazonRetiro = $Registro['razonretiro'];

					if (fxExisteDetRegulacion($msMatricula) == 0)
						fxGuardarDetRegulacion ($msMatricula, $codCodigo, $mnAusencias, $msRetirado, $msRazonRetiro);
					else
						fxModificarDetRegulacion ($msMatricula, $mnAusencias, $msRetirado, $msRazonRetiro);
				}

				$msMatriculaCtrl = "";
				$mbBorrarGrid = true;
				foreach($gridAusencia as $Registro)
				{
					$msMatricula = $Registro['matricula'];
					$msNotas = $Registro['notas'];
					$FechaDividida = explode("-", $Registro['fechas']);
					$Anno = $FechaDividida[2];
					$Mes = $FechaDividida[1];
					$Dia = $FechaDividida[0];
					$msFechas = $Anno . "-" . $Mes . "-" . $Dia;
					$msRazonAusencia = $Registro['razonausencia'];

					if ($msMatriculaCtrl != $msMatricula)
					{
						$msMatriculaCtrl = $msMatricula;
						$mbBorrarGrid = true;
						$detAusencia = 1;
					}
					else
					{
						$detAusencia += 1;
					}

					if ($mbBorrarGrid == true)
					{
						fxBorrarDetAusencia($msMatricula);
						$mbBorrarGrid = false;
					}

					fxGuardarDetAusencia ($msMatricula, $detAusencia, $msNotas, $msFechas, $msRazonAusencia);
				}
			}
		?>
		<div class="container">
        	<div id="DivContenido">
				<div class = "row">
					<div class="col-xs-12 col-md-11">
						<div class="degradado"><strong>Regulación de asistencia</strong></div>
					</div>
				</div>
				
                <div class="row">
                    <div class="col-md-12">
                        <form id="procRegulacion" name="procRegulacion" method="post">
							<div class="row" style="margin-bottom: 3px">
								<div class="col-auto col-md-12">
									<input type="button" id="Guardar" name="Guardar" value="Guardar" class="btn btn-warning" onclick="guardar()" />
									<input type="button" id="Cancelar" name="Cancelar" value="Volver a la pantalla anterior" class="btn btn-warning" onclick="location.href='frmRegulacionAsistencia.php';" />
								</div>
							</div>
							<div class="form-group row">
								<label for="txtCodRegulacion" class="col-sm-12 col-md-2 col-form-label">Código de la Regulación</label>
								<div class="col-sm-12 col-md-3">
									<?php echo('<input type="text" class="form-control" id="txtCodRegulacion" name="txtCodRegulacion" value="' . $codCodigo . '" readonly />'); ?>
								</div>
								<div class="col-auto">
									
									<?php echo('<input type="text" class="form-control" id="txtCodCurso" name="txtCodCurso" value="' . trim($codCurso) . '" style="display: none" />'); ?>
								</div>
							</div>

							<div class="form-group row">
								<label for="txtCurso" class="col-sm-12 col-md-2 col-form-label">Curso</label>
								<div class="col-sm-12 col-md-10">
									<?php echo('<input type="text" class="form-control" id="txtCurso" name="txtCurso" value="' . $msNomCurso . '" readonly />'); ?>
								</div>
								<div class="col-auto">
								</div>
							</div>

							<div class="form-group row">
								<label for="txtHorario" class="col-sm-12 col-md-2 col-form-label">Horario</label>
								<div class="col-sm-12 col-md-10">
									<?php echo('<input type="text" class="form-control" id="txtHorario" name="txtHorario" value="' . $msHorario . '" readonly />'); ?>
								</div>
								<div class="col-auto">
								</div>
							</div>

							<div class="form-group row">
								<label for="txtFechaElaboracion" class="col-sm-12 col-md-2 col-form-label">Fecha de elaboración</label>
								<div class="col-sm-12 col-md-3">
									<?php echo('<input type="text" class="form-control" id="txtFechaElaboracion" name="txtFechaElaboracion" value="' . $msFechaElaboracion . '" readonly />'); ?>
								</div>
								<div class="col-auto">
								</div>
							</div>

							<div class="form-group row">
								<label for="txtFechaActualizacion" class="col-sm-12 col-md-2 col-form-label">Ultima actualización</label>
								<div class="col-sm-12 col-md-3">
									<?php echo('<input type="text" class="form-control" id="txtFechaActualizacion" name="txtFechaActualizacion" value="' . $msFechaActualizacion . '" readonly />'); ?>
								</div>
								<div class="col-auto">
								</div>
							</div>

							<div class="row" style="margin-top: 1%">
								<div class="col-md-12">
								<?php
									$nombreArchivo = fxEscribeJson($codCurso);
								?>
									<table id="dgREG" class="easyui-datagrid" style="width:100%" data-options="iconCls:'icon-edit', toolbar:'#tbREG', nowrap:'false', striped:'true', singleSelect:true, url:'<?php echo(rtrim($nombreArchivo)); ?>', method:'get', onClickCell: onClickCell, onLoadSuccess: onLoadSuccess">
										<thead>
											<tr>
												<th data-options="field:'matricula', hidden:true">Matrícula</th>
												<th data-options="field:'nombrecompleto', width:'20%', align:'left'">Estudiante</th>
												<th data-options="field:'celular', width:'10%', align:'left'">Teléfono</th>
												<th data-options="field:'ausencias', width:'8%', align:'left'">Ausencias</th>
												<th data-options="field:'retirado', width:'7%', align:'left', editor:'text'">Retirado</th>
												<th data-options="field:'razonretiro', width:'15%', align:'left', editor:'text'">Razón del retiro</th>
												<th data-options="field:'fechas', width:'10%', align:'left'">Fechas</th>
												<th data-options="field:'razonausencia', width:'15%', align:'left', editor:'text'">Razón de la ausencia</th>
												<th data-options="field:'notas', width:'15%', align:'left', editor:'text'">Notas</th>
												
											</tr>
										</thead>
									</table>
								</div>
							</div>

							<div id="tbREG" style="height:auto">
								<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-save',plain:true" onclick="acceptit()">Aceptar</a>
								<!--a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onclick="removeit()">Borrar</a-->
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

	$('#dgREG').datagrid({
		onClickRow: function(rowIndex) {
			if (lastIndex != rowIndex) {
				$(this).datagrid('endEdit', lastIndex);
				$(this).datagrid('beginEdit', rowIndex);
			}
			lastIndex = rowIndex;
			onLoadSuccess();
		}
	})

	function endEditing() {
		if (editIndex == undefined) {
			return true
		}
		if ($('#dgREG').datagrid('validateRow', editIndex)) {
			$('#dgREG').datagrid('endEdit', editIndex);
			editIndex = undefined;
			return true;
		} else {
			return false;
		}
	}

	function onClickCell(index, field) {
		if (editIndex != index) {
			if (endEditing()) {
				$('#dgREG').datagrid('selectRow', index)
					.datagrid('beginEdit', index);
				editIndex = index;
			} else {
				setTimeout(function() {
					$('#dgREG').datagrid('selectRow', editIndex);
				}, 0);
			}
		}
		onLoadSuccess();
	}

	function removeit() {
		if (editIndex == undefined) {
			return
		}
		$('#dgREG').datagrid('cancelEdit', editIndex)
			.datagrid('deleteRow', editIndex);
		editIndex = undefined;
	}

	function acceptit() {
		if (endEditing()) {
			$('#dgREG').datagrid('acceptChanges');
		}
		onLoadSuccess();
	}

	function reject() {
		$('#dgREG').datagrid('rejectChanges');
		editIndex = undefined;
	}

	function onLoadSuccess(){
		var i;
		var indice = 0;
		var conteo = 1;
		var matriculaControl = "";
		var gridRegulacion = $('#dgREG').datagrid('getData');
		var registros = $('#dgREG').datagrid('getRows').length - 1;
		var merges = [];

		if (registros >= 0)
		{
			for (i=0; i<=registros; i++) {
				if (matriculaControl != gridRegulacion.rows[i].matricula)
				{
					matriculaControl = gridRegulacion.rows[i].matricula;

					if (conteo > 1)	{
						merges.push({index: indice, rowspan: conteo});
					}
					conteo = 1;
					indice = i;
				}
				else
					conteo ++;
			}
			if (conteo > 1)	{
				merges.push({index: indice, rowspan: conteo});
			}
		}

		for (var i=0; i<merges.length; i++){
			$("#dgREG").datagrid('mergeCells',{
				index: merges[i].index,
				field: 'nombrecompleto',
				rowspan: merges[i].rowspan
			});

			$("#dgREG").datagrid('mergeCells',{
				index: merges[i].index,
				field: 'celular',
				rowspan: merges[i].rowspan
			});

			$("#dgREG").datagrid('mergeCells',{
				index: merges[i].index,
				field: 'ausencias',
				rowspan: merges[i].rowspan
			});

			$("#dgREG").datagrid('mergeCells',{
				index: merges[i].index,
				field: 'retirado',
				rowspan: merges[i].rowspan
			});

			$("#dgREG").datagrid('mergeCells',{
				index: merges[i].index,
				field: 'razonretiro',
				rowspan: merges[i].rowspan
			});
		}
	}

	window.onload = function() {
		$('#dgREG').datagrid({
			rowStyler: function(index,row){
				if (row.retirado.toUpperCase() =='SI')
					return 'color:#f00;'; // return inline style
			}
		});
	}

	function guardar() {
		var datos;
		var texto;
		var i;
		var matriculaCtrl = "";
		var gridRegulacion = $('#dgREG').datagrid('getData');
		var registros = $('#dgREG').datagrid('getRows').length - 1;
		var codRegulacion = document.getElementById('txtCodRegulacion').value;
		var codCurso = document.getElementById('txtCodCurso').value;

		texto = '{"cmdGuardar":"1", ';
		texto += '"msCodigo": "' + codRegulacion + '",';
		texto += '"msCurso": "' + codCurso + '",';
		texto += '"gridRegulacion": [';
		for (i = 0; i <= registros; i++) {
			if (matriculaCtrl != gridRegulacion.rows[i].matricula)
			{
				texto += '{"matricula":"' + gridRegulacion.rows[i].matricula + '", "ausencias":"' + gridRegulacion.rows[i].ausencias;
				texto += '", "retirado":"' + gridRegulacion.rows[i].retirado + '", "razonretiro":"' + gridRegulacion.rows[i].razonretiro;
				texto += '"},';
				matriculaCtrl = gridRegulacion.rows[i].matricula;
			}
			if (i == registros)
				texto = texto.substr(0, texto.length - 1) + '],';
		}
		texto += '"gridAusencia": [';
		for (i = 0; i <= registros; i++) {
			texto += '{"matricula":"' + gridRegulacion.rows[i].matricula + '", "notas":"' + gridRegulacion.rows[i].notas;
			texto += '", "fechas":"' + gridRegulacion.rows[i].fechas + '", "razonausencia":"' + gridRegulacion.rows[i].razonausencia;
			if (i == registros)
				texto += '"}]}';
			else
				texto += '"},';
		}

		datos = JSON.parse(texto);

		$.ajax({
			url: 'procRegulacionAsistencia.php',
			type: 'post',
			data: datos,
			success: function() {
				$('form').submit();
				$.messager.alert('KDSA', 'Registros Guardados.', 'info');
				$.redirect("frmRegulacionAsistencia.php");
			}
		})
	}
</script>

<?php
	function fxEscribeJson($msCurso)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$nombreArchivo = "REG" . date('YmdHis') . ".json";
		$archivo = fopen($nombreArchivo, "w");
		
		//Escribe el Json
		$msConsulta = "select distinct KDSA141A.MATRICULA_REL from KDSA141A join KDSA030A on KDSA141A.MATRICULA_REL = KDSA030A.MATRICULA_REL where ESTADO_141 <> 0 and CURSO_REL = ?";
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

				$msConsulta = "select concat(NOMBRES_010, ' ', APELLIDOS_010) as NOMBRECOMPLETO, CELULAR_010 from KDSA010A, KDSA030A where KDSA010A.ESTUDIANTE_REL = KDSA030A.ESTUDIANTE_REL and MATRICULA_REL = ?";
				$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
				$mAuxiliar->execute([$msMatricula]);
				$auxFila = $mAuxiliar->fetch();
				$msNombreCompleto = trim($auxFila['NOMBRECOMPLETO']);
				$msCelular = trim($auxFila['CELULAR_010']);

				$msConsulta = "select RETIRADO_161, RAZONRETIRO_161 from KDSA161A where MATRICULA_REL = ?";
				$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
				$mAuxiliar->execute([$msMatricula]);
				$numRegAux = $mAuxiliar->rowCount();

				if ($numRegAux > 0)
				{
					$auxFila = $mAuxiliar->fetch();
					$msRetirado = trim($auxFila['RETIRADO_161']);
					$msRazonRetiro = trim($auxFila['RAZONRETIRO_161']);
				}
				else
				{
					$msConsulta = "select ESTADO_030 from KDSA030A where MATRICULA_REL = ?";
					$mAuxEstado = $m_cnx_MySQL->prepare($msConsulta);
					$mAuxEstado->execute([$msMatricula]);
					$auxEstadoFila = $mAuxEstado->fetch();
					$mnEstado = $auxEstadoFila['ESTADO_030'];

					if ($mnEstado == 0)
					{
						$msRetirado = "NO";
						$msRazonRetiro = "";
					}
					else
					{
						$msRetirado = "SI";
						switch($mnEstado)
						{
							case 1:
								$msRazonRetiro = "Estudiante inactivo";
								break;
							case 2:
								$msRazonRetiro = "Deserción";
								break;
							case 3:
								$msRazonRetiro = "Estudiante certificado";
								break;
							case 4:
								$msRazonRetiro = "Matrícula anulada";
								break;
							case 5:
								$msRazonRetiro = "Estudiante de baja";
								break;
						}
					}
				}

				$msConsulta = "select FECHA_140, JUSTIFICACION_141 from KDSA140A, KDSA141A where KDSA140A.ASISTENCIA_REL = KDSA141A.ASISTENCIA_REL and ESTADO_141 <> 0 and MATRICULA_REL = ?";
				$mAuxFechas = $m_cnx_MySQL->prepare($msConsulta);
				$mAuxFechas->execute([$msMatricula]);
				$mnAusencias = $mAuxFechas->rowCount();
				$j = 0;

				while ($auxFilaFechas = $mAuxFechas->fetch())
				{
					$fechaDB = date_create_from_format('Y-m-d', $auxFilaFechas["FECHA_140"]);
					$fecha = date_format($fechaDB, 'Y-m-d');
					$msFechas = date_format($fechaDB, 'd-m-Y');
					$msRazonAusencia = trim($auxFilaFechas['JUSTIFICACION_141']);

					$msConsulta = "select RAZONAUSENCIA_162, OBSERVACION_162 from KDSA162A where MATRICULA_REL = ? and FECHA_162 = ?";
					$mAuxAusencias = $m_cnx_MySQL->prepare($msConsulta);
					$mAuxAusencias->execute([$msMatricula, $fecha]);
					$numAusenciaAux = $mAuxAusencias->rowCount();
					if ($numAusenciaAux > 0)
					{
						$auxFilaAusencias = $mAuxAusencias->fetch();
						$msAuxRazonAusencia = trim($auxFilaAusencias['RAZONAUSENCIA_162']);
						$msObservacion = trim($auxFilaAusencias['OBSERVACION_162']);
					}
					else
					{
						$msAuxRazonAusencia = "";
						$msObservacion = "";
					}

					fwrite($archivo, '{"matricula":"' . trim($msMatricula) . '", ');
					fwrite($archivo, '"nombrecompleto":"' . trim($msNombreCompleto) . '", ');
					fwrite($archivo, '"celular":"' . trim($msCelular) . '", ');
					fwrite($archivo, '"retirado":"' . trim($msRetirado) . '", ');
					fwrite($archivo, '"razonretiro":"' . trim($msRazonRetiro) . '", ');
					fwrite($archivo, '"ausencias":"' . trim($mnAusencias) . '", ');
					fwrite($archivo, '"fechas":"' . trim($msFechas) . '", ');
					if (trim($msAuxRazonAusencia) == "")
						fwrite($archivo, '"razonausencia":"' . trim($msRazonAusencia) . '", ');
					else
						fwrite($archivo, '"razonausencia":"' . trim($msAuxRazonAusencia) . '", ');
					fwrite($archivo, '"notas":"' . trim($msObservacion) . '"');

					$j++;

					if ($i == $numRegistros and $j == $mnAusencias)
						fwrite($archivo, '}' . PHP_EOL);
					else
						fwrite($archivo, '},' . PHP_EOL);
				}
			}
		}
		fwrite($archivo, "]");
		fclose($archivo);
		
		return($nombreArchivo);
	}
?>