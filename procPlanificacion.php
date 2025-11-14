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
	require_once ("funciones/fxPlanificacion.php");
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
		$PermisoUsuario = fxPermisoUsuario("procPlanificacion", $mbAgregar, $mbModificar, $mbBorrar, $mbAnular);
		
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
			if (isset($_POST["cmdGuardar"]))
            {
				$codPlanificacion = $_POST["txtCodPlanificacion"];
				$codModulo = $_POST["cboModulo"];
				$dtpFecha = $_POST["dtpFecha"];
				if (isset($_POST["gridPlanificacion"]))
					$gridPlanificacion = $_POST["gridPlanificacion"];

				if ($codPlanificacion == "")
				{
					$codPlanificacion = fxGuardarPlanificacion($codModulo, $dtpFecha);
					fxAgregarBitacora($_SESSION["gsUsuario"], "KDSA120A", $codPlanificacion, "", "Agregar");
				}
				else
				{
					fxBorrarDetPlanificacion($codPlanificacion);
					fxAgregarBitacora($_SESSION["gsUsuario"], "KDSA120A", $codPlanificacion, "", "Modificar");
				}

				$itemId = 1;
				foreach($gridPlanificacion as $Registro)
				{
					$fechaPlan = $Registro['fecha'];
					$unidadPlan = $Registro['unidad'];
					$contenidoPlan = $Registro['contenido'];
					$objetivosPlan = $Registro['objetivos'];
					$actividadesPlan = $Registro['actividades'];
					$recursosPlan = $Registro['recursos'];
					$evaluacionPlan = $Registro['evaluacion'];
					$estadoPlan = $Registro['estado'];

					if ($unidadPlan != "" or $contenidoPlan != "" or $objetivosPlan != "" or $actividadesPlan != "" or $recursosPlan != "" or $evaluacionPlan != "") {
						fxGuardarDetPlanificacion ($codPlanificacion, $itemId, $fechaPlan, $unidadPlan, $contenidoPlan, $objetivosPlan, $actividadesPlan, $recursosPlan, $evaluacionPlan, $estadoPlan);
						$itemId++;
					}
				}
			}
			
			if (isset($_POST["cmdBorrar"]))
			{
				$codPlanificacion = $_POST["txtCodPlanificacion"];
				fxBorrarDetPlanificacion ($codPlanificacion);
				fxBorrarPlanificacion ($codPlanificacion);
			}

			if (isset($_POST["cboCurso"]) and isset($_POST["cboModulo"]))
            {
                $codCurso = $_POST["cboCurso"];
				$codModulo = $_POST["cboModulo"];
				
				//Evita la no correspondencia del Módulo con el Curso
				$msConsulta = "select * from KDSA021A where CURSO_REL = ? and MODULO_REL = ?";
				$mRecordSet = $m_cnx_MySQL->prepare($msConsulta);
				$mRecordSet->execute([$codCurso, $codModulo]);
				$mnRegistros = $mRecordSet->rowCount();
				if ($mnRegistros > 0)
				{
					$mRecordSet = fxDevuelvePlanificacion ($codModulo);
					$mnRegistros = $mRecordSet->rowCount();
					if ($mnRegistros > 0)
					{
						$Fila = $mRecordSet->fetch();
						$codPlanificacion = $Fila["PLANIFICACION_REL"];
						$dtpFecha = $Fila["FECHA_120"];
					}
					else
					{
						$codPlanificacion = "";
						$dtpFecha = "1900-01-01";
					}
				}
				else
				{
					$codPlanificacion = "";
					$dtpFecha = "1900-01-01";
				}
			}
			else
			{
				$codCurso = "";
				$codModulo = "";
				$codPlanificacion = "";
				$dtpFecha = "1900-01-01";
			}
		?>
    	<div class="container">
        	<div id="DivContenido">
				<div class = "row">
					<div class="col-xs-12 col-md-11">
						<div class="degradado"><strong>Planificación programática</strong></div>
					</div>
				</div>

                <div class="row">
                    <div class="col-md-12">
                        <?php
                            if ($mbAgregar == 1 or $Administrador == 1)
                                echo('<input id="cmdGuardar" type="submit" class="btn btn-warning" value="Guardar" onclick="guardar()" />');
                            else
                                echo('<input id="cmdGuardar" type="submit" class="btn btn-warning" value="Guardar" onclick="guardar()" disabled> /');
                                
                            if ($mbBorrar == 1 or $Administrador == 1)
                                echo('<input id="cmdBorrar" type="submit" class="btn btn-warning" value="Borrar" onclick="borrar()" />');
                            else
								echo('<input id="cmdBorrar" type="submit" class="btn btn-warning" value="Borrar" onclick="borrar()" disabled />');
								
							echo('<input id="cmdImprimirModulo" type="submit" class="btn btn-warning" value="Imprimir módulo" onclick="imprimir(0)" />');
							echo('<input id="cmdImprimirCurso" type="submit" class="btn btn-warning" value="Imprimir curso" onclick="imprimir(1)" />');
                        ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <form id="procPlanificacion" name="procPlanificacion" method="post">
							<div class="row">
								<div class="col-md-12">
									<div class="form-group" style="margin-top:1%">
										<?php
										if (isset($_POST["esPostBack"]))
											echo('<input type="text" class="form-control" id="txtEsPostBack" name="txtEsPostBack" value=1 style="display:none" />');
										else
											echo('<input type="text" class="form-control" id="txtEsPostBack" name="txtEsPostBack" value=0 style="display:none" />');
										?>
										<label for="cboCurso" class="col-sm-12 col-md-3 form-label">Curso</label>
										<select class="form-control col-sm-12 col-md-5" id="cboCurso" name="cboCurso" onchange="this.form.submit()">
											<?php
												if (trim($_SESSION["gsDocente"]) != "" and $Administrador == 0)
												{
													$mDocente = $_SESSION["gsDocente"];
													$msConsulta = "select distinct KDSA020A.CURSO_REL, concat(NOMBRE_020, ' (', CONVOCATORIA_020, '/G', GRUPO_020, ')') as NOMBRE ";
													$msConsulta .= "from KDSA020A, KDSA021A ";
													$msConsulta .= "where ACTIVO_020 = 1 and KDSA020A.CURSO_REL = KDSA021A.CURSO_REL ";
													$msConsulta .= "and KDSA021A.DOCENTE_REL = ? ";
													$msConsulta .= "order by KDSA020A.CURSO_REL desc";
													$mDatos = $m_cnx_MySQL->prepare($msConsulta);
					                    			$mDatos->execute([$mDocente]);
												}
												else
												{
													$msConsulta = "select distinct KDSA020A.CURSO_REL, concat(NOMBRE_020, ' (', CONVOCATORIA_020, '/G', GRUPO_020, ')') as NOMBRE ";
													$msConsulta .= "from KDSA020A, KDSA021A ";
													$msConsulta .= "where ACTIVO_020 = 1 and KDSA020A.CURSO_REL = KDSA021A.CURSO_REL ";
													$msConsulta .= "order by KDSA020A.CURSO_REL desc";
													$mDatos = $m_cnx_MySQL->prepare($msConsulta);
					                    			$mDatos->execute();
												}
												
												while ($Fila = $mDatos->fetch())
												{
													$Curso = rtrim($Fila["CURSO_REL"]);
													$Texto = rtrim($Fila["NOMBRE"]);
													
													if ($codCurso == "")
														$codCurso = $Curso;
													
													if ($codCurso == $Curso)
														echo("<option value='" . $Curso . "' selected>" . $Texto . "</option>");
													else
														echo("<option value='" . $Curso . "'>" . $Texto . "</option>");
												}
											?>
										</select>
									</div>
								</div>
							</div>
							
							<div class="row">
								<div class="col-md-12">
									<div class="form-group" style="margin-top:1%">
										<label for="cboModulo" class="col-sm-12 col-md-3 form-label">Módulo</label>
										<select class="form-control col-sm-12 col-md-5" id="cboModulo" name="cboModulo" onchange="this.form.submit()">
											<?php
												if ($Administrador == 1 or ($PermisoUsuario <> 0 and $_SESSION["gsDocente"] == ""))
												{
													$msConsulta = "select MODULO_REL, NOMBRE_021 from KDSA021A where CURSO_REL = ? order by NUMERO_021";
													$mDatos = $m_cnx_MySQL->prepare($msConsulta);
					                    			$mDatos->execute([$codCurso]);
												}
												else
												{
													$mDocente = $_SESSION["gsDocente"];
													$msConsulta = "select MODULO_REL, NOMBRE_021 from KDSA021A where CURSO_REL = ? and DOCENTE_REL = ? order by NUMERO_021";
													$mDatos = $m_cnx_MySQL->prepare($msConsulta);
					                    			$mDatos->execute([$codCurso, $mDocente]);
												}
												$msResponse = "";
												$mbPrimeraLinea = true;

												while ($Fila = $mDatos->fetch())
												{
													$Modulo = rtrim($Fila["MODULO_REL"]);
													$Texto = rtrim($Fila["NOMBRE_021"]);

													if ($mbPrimeraLinea == true)
													{
														$PrimerModulo = $Modulo;
														$mbPrimeraLinea = false;
													}
													
													if ($codModulo == $Modulo)
														$msResponse .= "<option value='" . $Modulo . "' selected>" . $Texto . "</option>";
													else
														$msResponse .= "<option value='" . $Modulo . "'>" . $Texto . "</option>";
												}
												echo($msResponse);
											?>
										</select>
									</div>
								</div>
							</div>
							
							<div class="row">
								<div class="col-md-12">
									<div class="form-group" style="margin-top:1%">
										<label for="txtCodPlanificacion" class="col-sm-12 col-md-3 col-form-label">Código de la Planificación</label>
										<?php echo('<input type="text" class="form-control col-sm-12 col-md-2" id="txtCodPlanificacion" name="txtCodPlanificacion" value="' . $codPlanificacion . '" readonly />'); ?>
									</div>
								</div>
							</div>

							<div class="row">
								<div class="col-md-12">
									<div class="form-group" style="margin-top:1%">
										<label for="dtpFecha" class="col-sm-12 col-md-3 col-form-label">Fecha</label>
										<?php
										if ($codPlanificacion == "")
											echo('<input type="date" class="form-control col-sm-12 col-md-2" id="dtpFecha" name="dtpFecha" value="' . date("Y-m-d") . '" readonly />');
										else
											echo('<input type="date" class="form-control col-sm-12 col-md-2" id="dtpFecha" name="dtpFecha" value="' . $dtpFecha . '" readonly />');
										?>
									</div>
								</div>
							</div>

							<div class="row" style="margin-top: 1%">
								<div class="col-md-12">
								<?php
									$nombreArchivo = fxEscribeJson($codPlanificacion);
								?>
									<table id="dgPLAN" class="easyui-datagrid" style="width:100%" data-options="iconCls:'icon-edit', toolbar:'#tbDET', nowrap:'false', striped:'true', singleSelect:true, url:'<?php echo(rtrim($nombreArchivo)); ?>', method:'get', onClickCell: onClickCell, onLoadSuccess: onLoadSuccess">
										<thead>
											<tr>
												<th data-options="field:'planificacion', hidden:true">Planificación</th>
												<th data-options="field:'detalle', hidden:true">Detalle</th>
												<th data-options="field:'fecha', hidden:true">FechaSQL</th>
												<th data-options="field:'fechaGrid', width:'10%', align:'left'">Fecha</th>
												<th data-options="field:'unidad', editor:'text', width:'5%', align:'left'">Unidad</th>
												<th data-options="field:'contenido', editor:'text', width:'15%', align:'left'">Contenidos</th>
												<th data-options="field:'objetivos', editor:'text', width:'20%', align:'left'">Objetivos</th>
												<th data-options="field:'actividades', editor:'text', width:'20%', align:'left'">Actividades</th>
												<th data-options="field:'recursos', editor:'text', width:'15%', align:'left'">Recursos didácticos</th>
												<th data-options="field:'evaluacion', editor:'text', width:'15%', align:'left'">Evaluación</th>
												<th data-options="field:'estado', hidden:true">Estado</th>
											</tr>
										</thead>
									</table>
								</div>
							</div>

							<div id="tbDET" style="height:auto">
								<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-add',plain:true" onclick="append()">Agregar el Calendario de fechas</a>
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

	$('#dgPLAN').datagrid({
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
		if ($('#dgPLAN').datagrid('validateRow', editIndex)) {
			$('#dgPLAN').datagrid('endEdit', editIndex);
			editIndex = undefined;
			return true;
		} else {
			return false;
		}
	}

	function onClickCell(index, field) {
		if (editIndex != index) {
			if (endEditing()) {
				$('#dgPLAN').datagrid('selectRow', index)
					.datagrid('beginEdit', index);
				editIndex = index;
			} else {
				setTimeout(function() {
					$('#dgPLAN').datagrid('selectRow', editIndex);
				}, 0);
			}
		}
		onLoadSuccess();
	}

	function append(){
		if (document.getElementById('txtCodPlanificacion').value == "")
			insertarNuevo();
		else
			insertarExistente();
	}

	function insertarNuevo() {
		var i, j, k;
		var datos = new FormData();
		var modulo = document.getElementById('cboModulo').value;
		datos.append('moduloPlan', modulo);

		$.ajax({
			url: 'funciones/fxDatosExternos.php',
			type: 'post',
			data: datos,
			contentType: false,
			processData: false,
			success: function(response) {
				var texto = "";
				var caracter;
				var mbInsertarFecha;
				var msAnno;
				var msMes;
				var msDia;
				var mdFecha;
				var msFecha;
				var indice = 0;
				var merges = [];
				
				for (i=0; i<response.length; i++)
				{
					mbInsertarFecha = false;
					caracter = response.charAt(i);
					
					switch (caracter)
					{
						case "%":
							msAnno = texto;
							texto = "";
							break;
						case "@":
							msMes = texto;
							texto = "";
							break;
						case "#":
							msDia = texto;
							texto = "";
							msFecha = msAnno + "-" + msMes + "-" + msDia;
							mdFecha = msDia + "/" + msMes + "/" + msAnno;
							mbInsertarFecha = true;
							break;
						default:
							texto += caracter;
					}

					if (mbInsertarFecha)
					{
						merges.push({index: indice, rowspan: 5});
						indice += 5;

						for (j=0; j<5; j++)
						{
							$('#dgPLAN').datagrid('appendRow', {
								planificacion: $('#txtCodPlanificacion').val(),
								detalle: i,
								fecha: msFecha,
								fechaGrid: mdFecha,
								unidad: "",
								contenido: "",
								objetivos: "",
								actividades: "",
								recursos: "",
								evaluacion: "",
								estado: "0"
							});
						}
					}
				}
				
				for (var k=0; k<merges.length; k++){
					$("#dgPLAN").datagrid('mergeCells',{
						index: merges[k].index,
						field: 'fechaGrid',
						rowspan: merges[k].rowspan
					});
				}
			}
		});
	}

	function insertarExistente(){
		var datos = new FormData();
		var planificacion = document.getElementById('txtCodPlanificacion').value;
		datos.append('insertarPlan', planificacion);

		$.ajax({
			url: 'funciones/fxDatosExternos.php',
			type: 'post',
			data: datos,
			contentType: false,
			processData: false,
			success: function(response) {
				$('#dgPLAN').datagrid({
					url: response
				});
				onLoadSuccess();
			}
		})
	}

	function removeit() {
		if (editIndex == undefined) {
			return
		}
		$('#dgPLAN').datagrid('cancelEdit', editIndex)
			.datagrid('deleteRow', editIndex);
		editIndex = undefined;
	}

	function acceptit() {
		if (endEditing()) {
			$('#dgPLAN').datagrid('acceptChanges');
			onLoadSuccess();
		}
	}

	function reject() {
		$('#dgPLAN').datagrid('rejectChanges');
		editIndex = undefined;
	}

	function verificaDatos() {
		var gridPlanificacion = $('#dgPLAN').datagrid('getData');
		var registros = $('#dgPLAN').datagrid('getRows').length - 1;
		var fechaAnterior = '';
		var registroCompleto;

		if (registros < 0){
			$.messager.alert('INOFE', 'Faltan los detalles de la Planificación.', 'warning');
            return false;
		}

		for (i=0; i<=registros; i++) {
			datosFaltantes = 0;

			if (fechaAnterior != gridPlanificacion.rows[i].fecha){
				fechaAnterior = gridPlanificacion.rows[i].fecha;

				if (gridPlanificacion.rows[i].unidad == '')
					datosFaltantes ++;
				
				if (gridPlanificacion.rows[i].contenido == '')
					datosFaltantes ++;
				
				if (gridPlanificacion.rows[i].objetivos == '')
					datosFaltantes ++;

				if (gridPlanificacion.rows[i].actividades == '')
					datosFaltantes ++;

				if (gridPlanificacion.rows[i].recursos == '')
					datosFaltantes ++;

				if (gridPlanificacion.rows[i].evaluacion == '')
					datosFaltantes ++;
			}

			if (datosFaltantes < 6 && datosFaltantes > 0){
				$.messager.alert('INOFE', 'La información del día ' + gridPlanificacion.rows[i].fechaGrid + ' está incompleta.', 'warning');
                return false;
			}

			if (gridPlanificacion.rows[i].unidad.length > 2){
				$.messager.alert('INOFE', 'El número de la Unidad del día ' + gridPlanificacion.rows[i].fechaGrid + ' excede el límite permitido.', 'warning');
                return false;
			}

			if (gridPlanificacion.rows[i].contenido.length > 200){
				$.messager.alert('INOFE', 'La columna de Contenido del día ' + gridPlanificacion.rows[i].fechaGrid + ' excede el límite permitido de 200 caracteres.', 'warning');
                return false;
			}

			if (gridPlanificacion.rows[i].objetivos.length > 400){
				$.messager.alert('INOFE', 'La columna de Contenido del día ' + gridPlanificacion.rows[i].fechaGrid + ' excede el límite permitido de 400 caracteres.', 'warning');
                return false;
			}

			if (gridPlanificacion.rows[i].actividades.length > 400){
				$.messager.alert('INOFE', 'La columna de Actividades del día ' + gridPlanificacion.rows[i].fechaGrid + ' excede el límite permitido de 400 caracteres.', 'warning');
                return false;
			}

			if (gridPlanificacion.rows[i].recursos.length > 200){
				$.messager.alert('INOFE', 'La columna de Recursos del día ' + gridPlanificacion.rows[i].fechaGrid + ' excede el límite permitido de 200 caracteres.', 'warning');
                return false;
			}

			if (gridPlanificacion.rows[i].evaluacion.length > 200){
				$.messager.alert('INOFE', 'La columna de Evaluación del día ' + gridPlanificacion.rows[i].fechaGrid + ' excede el límite permitido de 200 caracteres.', 'warning');
                return false;
			}
		}

		return true;
	}

	function guardar() {
		var datos;
		var texto;
		var i;
		var gridPlanificacion = $('#dgPLAN').datagrid('getData');
		var registros = $('#dgPLAN').datagrid('getRows').length - 1;

		if (verificaDatos())
		{
			texto = '{"cmdGuardar":"1", ';
			texto += '"txtCodPlanificacion":"' + document.getElementById("txtCodPlanificacion").value + '", ';
			texto += '"cboModulo":"' + document.getElementById("cboModulo").value + '", ';
			texto += '"dtpFecha":"' + document.getElementById("dtpFecha").value + '", ';
			texto += '"gridPlanificacion": [';
			for (i = 0; i <= registros; i++) {
				texto += '{"planificacion":"' + gridPlanificacion.rows[i].planificacion + '", "detalle":"' + i + '", "fecha":"' + gridPlanificacion.rows[i].fecha + '", "unidad":"' + gridPlanificacion.rows[i].unidad + '", "contenido":"' + gridPlanificacion.rows[i].contenido + '", "objetivos":"' + gridPlanificacion.rows[i].objetivos + '", "actividades":"' + gridPlanificacion.rows[i].actividades + '", "recursos":"' + gridPlanificacion.rows[i].recursos + '", "evaluacion":"' + gridPlanificacion.rows[i].evaluacion + '", "estado":"' + gridPlanificacion.rows[i].estado;
				if (i == registros)
					texto += '"}]}';
				else
					texto += '"},';
			}

			datos = JSON.parse(texto);

			$.ajax({
				url: 'procPlanificacion.php',
				type: 'post',
				data: datos,
				success: function() {
					$('form').submit();
					$.messager.alert('INOFE', 'Planificación Guardada.', 'info');
				}
			})
		}
	}

	function borrar() {
		$.messager.confirm('KDSA', '¿Desea borrar esta Planificación?', function(response){
			if (response){
				var texto = '{"cmdBorrar":"1", "txtCodPlanificacion":"' + document.getElementById("txtCodPlanificacion").value + '"}';
				datos = JSON.parse(texto);

				$.ajax({
					url: 'procPlanificacion.php',
					type: 'post',
					data: datos,
					success: function() {
						$.messager.alert('INOFE', 'Planificación Borrada.', 'info');
						$('form').submit();
					}
				})
			}
		});
	}

	function onLoadSuccess(){
		var i;
		var indice = 0;
		var conteo = 1;
		var fechaControl = "";
		var gridPlanificacion = $('#dgPLAN').datagrid('getData');
		var registros = $('#dgPLAN').datagrid('getRows').length - 1;
		var merges = [];

		if (registros >= 0)
		{
			for (i=0; i<=registros; i++) {
				if (fechaControl != gridPlanificacion.rows[i].fecha) {
					fechaControl = gridPlanificacion.rows[i].fecha;

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
			$("#dgPLAN").datagrid('mergeCells',{
				index: merges[i].index,
				field: 'fechaGrid',
				rowspan: merges[i].rowspan
			});
		}
	}

	function imprimir (mnTipo){
		var modulo = document.getElementById('cboModulo').value;
		var curso = document.getElementById('cboCurso').value;
		$.redirect("repPlanificacion.php", {mnTipoRep: mnTipo, msCurso: curso, msModulo: modulo}, "POST", "_blank");
	}

	window.onload = function() {
		if (document.getElementById('txtCodPlanificacion').value == "")
		{
			if (document.getElementById('txtEsPostBack').value == 0)
			{
				var datos = new FormData();
				var curso = document.getElementById('cboCurso').value;
				var modulo = document.getElementById('cboModulo').value;
				datos.append('existeModuloPlan', modulo);

				$.ajax({
					url: 'funciones/fxDatosExternos.php',
					type: 'post',
					data: datos,
					contentType: false,
					processData: false,
					success: function(response) {
						if (response != 0)
						{
							$.messager.progress({
								title:'Por favor espere',
								msg:'Cargando datos...',
								timeout:1000
							});
							$.redirect("procPlanificacion.php", {esPostBack: 1, cboCurso: curso, cboModulo: modulo}, "POST");;
						}
					}
				})
			}
		}
	}
</script>

<?php
function fxEscribeJson($Planificacion)
{
	if ($Planificacion == "")
		$nombreArchivo = "PL00000000.json";
	else
		$nombreArchivo = $Planificacion . ".json";

	if (file_exists($nombreArchivo))
		unlink($nombreArchivo);
	
	//Escribe el Json
	$mDatos = fxDevuelveDetPlanificacion($Planificacion);
	$numRegistros = $mDatos->rowCount();

	$archivo = fopen($nombreArchivo, "a");
	
	fwrite($archivo, "[" . PHP_EOL);
	
	for ($i = 1; $i <= $numRegistros; $i++)
	{
		$Fila = $mDatos->fetch();
		fwrite($archivo, '{');
		fwrite($archivo, '"planificacion":"' . rtrim($Fila['PLANIFICACION_REL']) . '", ');
		fwrite($archivo, '"detalle":"' . rtrim($Fila['DETPLANIFICACION_REL']) . '", ');
		$fecha = date_create_from_format('Y-m-d', $Fila["FECHA_121"]);
		fwrite($archivo, '"fechaGrid":"' . date_format($fecha, 'd/m/Y') . '", ');
		fwrite($archivo, '"fecha":"' . rtrim($Fila['FECHA_121']) . '", ');
		fwrite($archivo, '"unidad":"' . rtrim($Fila['UNIDAD_121']) . '", ');
		fwrite($archivo, '"contenido":"' . rtrim($Fila['CONTENIDO_121']) . '", ');
		fwrite($archivo, '"objetivos":"' . rtrim($Fila['OBJETIVOS_121']) . '", ');
		fwrite($archivo, '"actividades":"' . rtrim($Fila['ACTIVIDADES_121']) . '", ');
		fwrite($archivo, '"recursos":"' . rtrim($Fila['RECURSOS_121']) . '", ');
		fwrite($archivo, '"evaluacion":"' . rtrim($Fila['EVALUACION_121']) . '", ');
		fwrite($archivo, '"estado":"' . rtrim($Fila['ESTADO_121']) . '"');
		
		if ($i == $numRegistros)
			fwrite($archivo, '}' . PHP_EOL);
		else
			fwrite($archivo, '},' . PHP_EOL);
	}
	fwrite($archivo, "]");
	fclose($archivo);
	
	return($nombreArchivo);
}