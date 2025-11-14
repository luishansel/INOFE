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
	require_once ("funciones/fxPlanClase.php");
    $m_cnx_MySQL = fxAbrirConexion();
	$Registro = fxVerificaUsuario();
	
	if ($Registro == 0)
	{
?>

<div class="container text-center">
    <div id="DivContenido">
        <img src="imagenes/errordeacceso.png" />
    </div>
</div>
<?php }
	else
	{
        $Administrador = fxVerificaAdministrador();
        $Academico = fxVerificaAcademico();
		$PermisoUsuario = fxPermisoUsuario("procPlanClase");
		
		if ($Administrador == 0 and $PermisoUsuario == 0)
		{?>
<div class="container text-center">
    <div id="DivContenido">
        <img src="imagenes/errordeacceso.png" />
    </div>
</div>
<?php }
		else
		{
			if (isset($_POST["txtCodPlanClase"]))
			{
				$mnOperacion = intval($_POST["Operacion"]);
				$Codigo = $_POST["txtCodPlanClase"];
				$msModulo = $_POST["cboModulo"];
				$Fecha = $_POST["dtpFecha"];
				$FechaClase = $_POST["cboFechaClase"];
				$Contenidos = $_POST["txtContenidos"];
				$Asignaciones = $_POST["txtAsignaciones"];
				$gridObjetivos = $_POST["gridObjetivos"];
                $gridActividades = $_POST["gridActividades"];
                $gridMateriales = $_POST["gridMateriales"];
                if (isset($_POST["gridURL"]))
                    $gridURL = $_POST["gridURL"];

				{
					if ($mnOperacion == 0)
					{
						$Codigo = fxGuardarPlanClase ($msModulo, $Fecha, $FechaClase, $Contenidos, $Asignaciones);
						fxAgregarBitacora ($_SESSION["gsUsuario"], "KDSA130A", $Codigo, "", "Agregar");
					}
					else
					{
						fxModificarPlanClase ($Codigo, $msModulo, $Fecha, $FechaClase, $Contenidos, $Asignaciones);
						fxBorrarDetObjetivos ($Codigo);
                        fxBorrarDetActividades ($Codigo);
                        fxBorrarDetMateriales ($Codigo);
                        fxBorrarDetSitio($Codigo);
						fxAgregarBitacora ($_SESSION["gsUsuario"], "KDSA130A", $Codigo, "", "Modificar");
					}
				}
                
                $itemId = 1;
				foreach($gridObjetivos as $Registro)
				{
					$Objetivo = $Registro['objetivo'];
                    fxGuardarDetObjetivos ($Codigo, $itemId, $Objetivo);
                    $itemId++;
				}
				
				$itemId = 1;
				foreach($gridActividades as $Registro)
				{
					$Actividad = $Registro['actividad'];
					fxGuardarDetActividades ($Codigo, $itemId, $Actividad);
					$itemId++;
                }
                
                $itemId = 1;
				foreach($gridMateriales as $Registro)
				{
					$Material = $Registro['material'];
					fxGuardarDetMateriales ($Codigo, $itemId, $Material);
					$itemId++;
                }
                
                $itemId = 1;
				foreach($gridURL as $Registro)
				{
                    $Descripcion = $Registro['descripcion'];
                    $Sitio = $Registro['url'];
					fxGuardarDetSitio($Codigo, $itemId, $Descripcion, $Sitio);
					$itemId++;
				}
								
				?>
                <meta http-equiv="Refresh" content="0;url=gridPlanClase.php" />
                <?php
			}
			else
			{
                if (isset($_POST["KDSA"]))
				    $Codigo = $_POST["KDSA"];
                else
                    $Codigo = "";

				$RecordSet = fxDevuelvePlanClase (0, $Codigo);
                $mnRegistros = $RecordSet->rowCount();
                if ($mnRegistros > 0)
                {
                    $Fila = $RecordSet->fetch();
                    $msModulo = $Fila["MODULO_REL"];
                    $Fecha = $Fila["FECHA_130"];
                    $FechaClase = $Fila["FECHACLASE_130"];
                    $Contenidos = $Fila["CONTENIDOS_130"];
                    $Asignaciones = $Fila["ASIGNACIONES_130"];
                }
                else 
                {
                    $msModulo = "";
                    $Fecha = "";
                    $FechaClase = "";
                    $Contenidos = "";
                    $Asignaciones = "";
                }
	?>
<div class="container text-left">
    <div id="DivContenido">
        <div class = "row">
			<div class="col-xs-12 col-md-11">
				<div class="degradado"><strong>Planificación de clases</strong></div>
			</div>
		</div>

        <div class="row">
            <div class="col-xs-12 col-md-12">
                <form id="procPlanClase" name="procPlanClase">
                    <div class="row">
                        <div class="col-auto col-md-8">
                            <input type="submit" id="Guardar" name="Guardar" value="Guardar" class="btn btn-warning" />
                            <input type="button" id="Cancelar" name="Cancelar" value="Cancelar" class="btn btn-warning" onclick="location.href='gridPlanClase.php';" />
                        </div>
                    </div>
                    
                    <div class="easyui-tabs tabs-narrow" style="width:100%;height:auto">
                        <!--Inicio del DIV de Tab GENERALES-->
                        <div title="Generales" style="padding:10px">
                            <div class="col-xs-auto col-xs-offset-none col-md-11 col-md-offset-1">
                                <div class="form-group row">
                                    <label for="txtCodPlanClase" class="col-sm-12 col-md-2 col-form-label">Código del Plan</label>
                                    <div class="col-sm-12 col-md-2">
                                        <?php echo('<input type="text" class="form-control" id="txtCodPlanClase" name="txtCodPlanClase" value="' . $Codigo . '" readonly />'); ?>
                                        <input type="hidden" class="form-control" id="txnCalificaciones" name="txnCalificaciones" value="0" />
                                    </div>
                                    <div class="col-auto">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="cboCurso" class="col-sm-12 col-md-2 col-form-label">Curso</label>
                                    <div class="col-sm-12 col-md-6">
                                        <?php
                                            if ($Codigo == "")
                                            {
                                                echo('<select class="form-control" id="cboCurso" name="cboCurso" onchange="llenaModulos(this.value, ' . $Administrador . ')">');
                                                
                                                if (trim($_SESSION["gsDocente"]) != "" and $Administrador == 0)
                                                {
                                                    $mDocente = $_SESSION["gsDocente"];
                                                    $msConsulta = "select distinct KDSA020A.CURSO_REL, concat(NOMBRE_020, ' (', CONVOCATORIA_020, '/G', GRUPO_020, ')') as NOMBRE ";
                                                    $msConsulta .= "from KDSA020A, KDSA021A ";
                                                    $msConsulta .= "where ACTIVO_020 = 1 and KDSA020A.CURSO_REL = KDSA021A.CURSO_REL ";
                                                    $msConsulta .= "and KDSA021A.DOCENTE_REL = ? order by KDSA020A.CURSO_REL desc";
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
                                            }
                                            else
                                            {
                                                echo('<select class="form-control" id="cboCurso" name="cboCurso" disabled>');

                                                $msConsulta = "select KDSA020A.CURSO_REL, concat(NOMBRE_020, ' (', CONVOCATORIA_020, '/G', GRUPO_020, ')') as NOMBRE ";
                                                $msConsulta .= "from KDSA020A, KDSA021A where KDSA020A.CURSO_REL = KDSA021A.CURSO_REL and MODULO_REL = ?";
                                                $mDatos = $m_cnx_MySQL->prepare($msConsulta);
                                                $mDatos->execute([$msModulo]);
                                            }

                                            $codCurso = "";

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

                                <div class="form-group  row">
                                    <label for="cboModulo" class="col-sm-12 col-md-2 col-form-label">Módulo</label>
                                    <div class="col-sm-12 col-md-6">
                                        <?php
                                            if ($Codigo == "")
                                                echo('<select class="form-control" id="cboModulo" name="cboModulo" onchange="funcionFechas(this.value)">');
                                            else
                                                echo('<select class="form-control" id="cboModulo" name="cboModulo" disabled>');

                                            if ($Administrador == 1 or $_SESSION["gsDocente"] == "")
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

                                            while ($Fila = $mDatos->fetch())
                                            {
                                                $codModulo = rtrim($Fila["MODULO_REL"]);
                                                $Texto = rtrim($Fila["NOMBRE_021"]);

                                                if ($msModulo == "")
                                                {
                                                    $msModulo = $codModulo;
                                                }
                                                
                                                if ($msModulo == $codModulo)
                                                    $msResponse .= "<option value='" . $codModulo . "' selected>" . $Texto . "</option>";
                                                else
                                                    $msResponse .= "<option value='" . $codModulo . "'>" . $Texto . "</option>";
                                            }
                                            echo($msResponse);
                                        ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="dtpFecha" class="col-sm-12 col-md-2 col-form-label">Fecha</label>
                                    <div class="col-sm-12 col-md-2">
                                        <?php
                                        if ($Codigo == "")
                                            echo('<input type="date" class="form-control" id="dtpFecha" name="dtpFecha" value="' . date("Y-m-d") . '" readonly />');
                                        else
                                            echo('<input type="date" class="form-control" id="dtpFecha" name="dtpFecha" value="' . $Fecha . '" readonly />');
                                        ?>
                                    </div>
                                    <div class="col-auto">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="cboFechaClase" class="col-sm-12 col-md-2 col-form-label">Fecha de Clase</label>
                                    <div class="col-sm-12 col-md-2">
                                        <?php
                                            if ($Codigo == "")
                                                echo('<select class="form-control" id="cboFechaClase" name="cboFechaClase">');
                                            else
                                                echo('<select class="form-control" id="cboFechaClase" name="cboFechaClase" disabled>');
                                            

                                            $msConsulta = "select DOMINGO_020, LUNES_020, MARTES_020, MIERCOLES_020, JUEVES_020, VIERNES_020, SABADO_020, FECHAINI_021, FECHAFIN_021 from KDSA021A, KDSA020A where KDSA021A.CURSO_REL = KDSA020A.CURSO_REL and KDSA021A.MODULO_REL = ?";
                                            $mDatos = $m_cnx_MySQL->prepare($msConsulta);
                                            $mDatos->execute([$msModulo]);

                                            $Fila = $mDatos->fetch();
                                            $domingo = $Fila["DOMINGO_020"];
                                            $lunes = $Fila["LUNES_020"];
                                            $martes = $Fila["MARTES_020"];
                                            $miercoles = $Fila["MIERCOLES_020"];
                                            $jueves = $Fila["JUEVES_020"];
                                            $viernes = $Fila["VIERNES_020"];
                                            $sabado = $Fila["SABADO_020"];
                                            $fecha = trim($Fila["FECHAINI_021"]);
                                            $fechaFin = trim($Fila["FECHAFIN_021"]);

                                            while ($fecha <= $fechaFin)
                                            {
                                                $escribirFecha = false;
                                                $diaSemana = date("l", strtotime($fecha));

                                                if ($diaSemana == "Sunday" and $domingo == 1)
                                                    $escribirFecha = true;

                                                if ($diaSemana == "Monday" and $lunes == 1)
                                                    $escribirFecha = true;

                                                if ($diaSemana == "Tuesday" and $martes == 1)
                                                    $escribirFecha = true;

                                                if ($diaSemana == "Wednesday" and $miercoles == 1)
                                                    $escribirFecha = true;

                                                if ($diaSemana == "Thursday" and $jueves == 1)
                                                    $escribirFecha = true;

                                                if ($diaSemana == "Friday" and $viernes == 1)
                                                    $escribirFecha = true;

                                                if ($diaSemana == "Saturday" and $sabado == 1)
                                                    $escribirFecha = true;

                                                if ($escribirFecha)
                                                {
                                                    //Verifica que no sea un día Feriado
                                                    $msConsulta = "select DETFECHA_REL from KDSA021A, KDSA022A where KDSA021A.CURSO_REL = KDSA022A.CURSO_REL and KDSA021A.MODULO_REL = ? and FECHA_022 = ?";
                                                    $mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
                                                    $mAuxiliar->execute([$msModulo, $fecha]);
                                                    $mnRegistros = $mAuxiliar->rowCount();

                                                    if ($mnRegistros == 0)
                                                    {
                                                        if ($Codigo == "")
                                                        {
                                                            //Verifica que no exista una planificación en la fecha
                                                            $msConsulta = "select CLASE_REL from KDSA130A where MODULO_REL = ? and FECHACLASE_130 = ?";
                                                            $mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
                                                            $mAuxiliar->execute([$msModulo, $fecha]);
                                                            $mnRegistros = $mAuxiliar->rowCount();

                                                            if ($mnRegistros == 0)
                                                            {
                                                                $fechaBD = date_create_from_format('Y-m-d', $fecha);
                                                                $Valor = date_format($fechaBD, 'Y-m-d');
                                                                $Texto = date_format($fechaBD, 'd / m / Y');

                                                                //Agrega la fecha en el ComboBox
                                                                echo("<option value='" . $Valor . "'>" . $Texto . "</option>");
                                                            }
                                                        }
                                                        else
                                                        {
                                                            $fechaBD = date_create_from_format('Y-m-d', $fecha);
                                                            $Valor = date_format($fechaBD, 'Y-m-d');
                                                            $Texto = date_format($fechaBD, 'd / m / Y');

                                                            if ($FechaClase == $Valor)
                                                                echo("<option value='" . $Valor . "' selected>" . $Texto . "</option>");
                                                            else
                                                                echo("<option value='" . $Valor . "'>" . $Texto . "</option>");
                                                        }
                                                    }
                                                }

                                                $fecha = date("Y-m-d", strtotime($fecha . "+ 1 days"));
                                            }
                                            echo("</select>");
                                        ?>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="txtContenidos" class="col-sm-12 col-md-2 form-label">Contenidos</label>
                                    <div class="col-sm-12 col-md-8">
                                        <?php echo('<textarea class="form-control" id="txtContenidos" name="txtContenidos" rows="2" maxlength="500">' . $Contenidos . '</textarea>'); ?>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="dgOBJ" class="col-sm-12 col-md-2 form-label">Objetivos de la clase</label>
                                    <div class="col-sm-auto col-md-8">
                                        <div id="dvOBJ">
                                            <table id="dgOBJ" class="easyui-datagrid table" data-options="iconCls:'icon-edit', onClickCell: onClickCellOBJ">
                                                <thead>
                                                    <tr>
                                                        <th data-options="field:'clase', hidden:'true'">clase</th>
                                                        <th data-options="field:'detalle', hidden:'true'">detalle</th>
                                                        <th data-options="field:'objetivo',width:'100%',align:'left'">Objetivo</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                <?php
                                                    $mDatos = fxDevuelveDetObjetivos($Codigo);
                                                    while($Fila = $mDatos->fetch())
                                                    {
                                                        echo('<tr>');
                                                        echo('<td>' . rtrim($Fila['CLASE_REL']) . '</td>');
                                                        echo('<td>' . rtrim($Fila['DETOBJETIVOS_REL']) . '</td>');
                                                        echo('<td>' . rtrim($Fila['DESC_131']) . '</td>');
                                                        echo('</tr>');
                                                    }
                                                ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div id="tbOBJ" style="height:auto; padding-top:1%; padding-bottom:2%">
                                    <table width="100%">
                                        <tr>
                                            <td><input id="txtObjetivo" class="easyui-textbox" data-options="prompt: 'Escriba un objetivo...'" style="width:90%" maxlength="400"></td>
                                        </tr>
                                    </table>
                                </div>

                                <div id="ftOBJ" style="height:auto">
                                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-add',plain:true" onclick="appendOBJ()">Agregar</a>
                                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onclick="removeitOBJ()">Borrar</a>
                                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-save',plain:true" onclick="acceptitOBJ()">Aceptar</a>
                                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-undo',plain:true" onclick="rejectOBJ()">Deshacer</a>
                                </div>

                                <div class="form-group row">
                                    <label for="dgACT" class="col-sm-12 col-md-2 form-label">Actividades de enseñanza</label>
                                    <div class="col-sm-auto col-md-8">
                                        <div id="dvACT">
                                            <table id="dgACT" class="easyui-datagrid table" data-options="iconCls:'icon-edit', onClickCell: onClickCellACT">
                                                <thead>
                                                    <tr>
                                                        <th data-options="field:'clase', hidden:'true'">clase</th>
                                                        <th data-options="field:'detalle', hidden:'true'">detalle</th>
                                                        <th data-options="field:'actividad',width:'100%',align:'left'">Actividad</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                <?php
                                                    $mDatos = fxDevuelveDetActividades($Codigo);
                                                    while ($Fila = $mDatos->fetch())
                                                    {
                                                        echo('<tr>');
                                                        echo('<td>' . rtrim($Fila['CLASE_REL']) . '</td>');
                                                        echo('<td>' . rtrim($Fila['DETACTIVIDADES_REL']) . '</td>');
                                                        echo('<td>' . rtrim($Fila['DESC_132']) . '</td>');
                                                        echo('</tr>');
                                                    }
                                                ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div id="tbACT" style="height:auto; padding-top:1%; padding-bottom:2%">
                                    <table width="100%">
                                        <tr>
                                            <td><input id="txtActividad" class="easyui-textbox" data-options="prompt: 'Escriba una actividad...'" style="width:90%" maxlength="400"></td>
                                        </tr>
                                    </table>
                                </div>

                                <div id="ftACT" style="height:auto">
                                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-add',plain:true" onclick="appendACT()">Agregar</a>
                                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onclick="removeitACT()">Borrar</a>
                                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-save',plain:true" onclick="acceptitACT()">Aceptar</a>
                                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-undo',plain:true" onclick="rejectACT()">Deshacer</a>
                                </div>

                                <div class="form-group row">
                                    <label for="dgMAT" class="col-sm-12 col-md-2 form-label">Materiales de apoyo</label>
                                    <div class="col-sm-auto col-md-8">
                                        <div id="dvMAT">
                                            <table id="dgMAT" class="easyui-datagrid table" data-options="iconCls:'icon-edit', onClickCell: onClickCellMAT">
                                                <thead>
                                                    <tr>
                                                        <th data-options="field:'clase', hidden:'true'">clase</th>
                                                        <th data-options="field:'detalle', hidden:'true'">detalle</th>
                                                        <th data-options="field:'material',width:'100%',align:'left'">Material</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                        $mDatos = fxDevuelveDetMateriales($Codigo);
                                                        while ($Fila = $mDatos->fetch())
                                                        {
                                                            echo('<tr>');
                                                            echo('<td>' . rtrim($Fila['CLASE_REL']) . '</td>');
                                                            echo('<td>' . rtrim($Fila['DETMATERIALES_REL']) . '</td>');
                                                            echo('<td>' . rtrim($Fila['DESC_133']) . '</td>');
                                                            echo('</tr>');
                                                        }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div id="tbMAT" style="height:auto; padding-top:1%; padding-bottom:2%">
                                    <table width="100%">
                                        <tr>
                                            <td><input id="txtMaterial" class="easyui-textbox" data-options="prompt: 'Ingrese un material...'" style="width:90%" maxlength="400"></td>
                                        </tr>
                                    </table>
                                </div>

                                <div id="ftMAT" style="height:auto">
                                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-add',plain:true" onclick="appendMAT()">Agregar</a>
                                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onclick="removeitMAT()">Borrar</a>
                                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-save',plain:true" onclick="acceptitMAT()">Aceptar</a>
                                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-undo',plain:true" onclick="rejectMAT()">Deshacer</a>
                                </div>

                                <div class="form-group row">
                                    <label for="txtAsignaciones" class="col-sm-12 col-md-2 form-label">Asignaciones</label>
                                    <div class="col-sm-12 col-md-8">
                                        <?php echo('<textarea class="form-control" id="txtAsignaciones" name="txtAsignaciones" rows="2">' . $Asignaciones . '</textarea>'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--Fin del DIV de Tab GENERALES-->

                        <!--Inicio del DIV de Tab ARCHIVOS-->
                        <div title="Archivos de apoyo" style="padding:10px">
                            <div class="col-xs-auto col-xs-offset-none col-md-11 col-md-offset-1">
                                <div class="form-group row">
                                    <div class="col-sm-auto col-md-10">
                                        <div id="dvARCH">
                                            <table id="dgARCH" class="easyui-datagrid table" data-options="iconCls:'icon-edit', onClickCell: onClickCellARCH">
                                                <thead>
                                                    <tr>
                                                        <th data-options="field:'clase', hidden:'true'">clase</th>
                                                        <th data-options="field:'apoyo', hidden:'true'">apoyo</th>
                                                        <th data-options="field:'ruta', hidden:'true'">ruta</th>
                                                        <th data-options="field:'tipo', width:'20%', align:'left'">Tipo</th>
                                                        <th data-options="field:'descripcion', width:'80%', align:'left'">Archivo</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                        $mDatos = fxDevuelveDetApoyo($Codigo);
                                                        while ($Fila = $mDatos->fetch())
                                                        {
                                                            echo('<tr>');
                                                            echo('<td>' . rtrim($Fila['CLASE_REL']) . '</td>');
                                                            echo('<td>' . rtrim($Fila['APOYO_REL']) . '</td>');
                                                            echo('<td>' . rtrim($Fila['RUTA_134']) . '</td>');
                                                            if ($Fila['TIPO_134'] == 0)
                                                                echo('<td>Teoría</td>');
                                                            else
                                                                echo('<td>Ejercicio</td>');
                                                            echo('<td>' . rtrim($Fila['DESC_134']) . '</td>');
                                                            echo('</tr>');
                                                        }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div id="tbARCH" style="height:auto; padding-top:1%; padding-bottom:2%">
                                    <table width="100%">
                                        <tr>
                                            <td width="10%">Tipo</td>
                                            <td width="90%"><input type="radio" name="optTipo" id="optTipo1" checked>Teoría &nbsp;<input type="radio" name="optTipo" id="optTipo2">Ejercicio</td>
                                        </tr>
                                        <tr>
                                            <td width="10%">Archivo</td>
                                            <td width="90%"><input type="file" id="fbArchivo" name="fbArchivo"></td>
                                        </tr>
                                    </table>
                                </div>

                                <div id="ftARCH" style="height:auto">
                                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-add',plain:true" onclick="appendARCH()">Agregar</a>
                                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onclick="removeitARCH()">Borrar</a>
                                </div>
                            </div>
                        </div>
                        <!--Fin del DIV de Tab ARCHIVOS-->

                        <!--Inicio del DIV de Tab URL's-->
                        <div title="Sitios web de apoyo" style="padding:10px">
                            <div class="col-xs-auto col-xs-offset-none col-md-11 col-md-offset-1">
                                <div class="form-group row">
                                    <div class="col-sm-auto col-md-10">
                                        <div id="dvURL">
                                            <table id="dgURL" class="easyui-datagrid table" data-options="iconCls:'icon-edit', onClickCell: onClickCellURL">
                                                <thead>
                                                    <tr>
                                                        <th data-options="field:'clase', hidden:'true'">clase</th>
                                                        <th data-options="field:'sitio', hidden:'true'">sitio</th>
                                                        <th data-options="field:'descripcion', width:'50%', align:'left'">Descripción</th>
                                                        <th data-options="field:'url', width:'50%', align:'left'">URL</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                        $mDatos = fxDevuelveDetSitio($Codigo);
                                                        while ($Fila = $mDatos->fetch())
                                                        {
                                                            echo('<tr>');
                                                            echo('<td>' . rtrim($Fila['CLASE_REL']) . '</td>');
                                                            echo('<td>' . rtrim($Fila['SITIO_REL']) . '</td>');
                                                            echo('<td>' . rtrim($Fila['DESC_135']) . '</td>');
                                                            echo('<td>' . rtrim($Fila['URL_135']) . '</td>');
                                                            echo('</tr>');
                                                        }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div id="tbURL" style="height:auto; padding-top:1%; padding-bottom:2%">
                                    <table width="100%">
                                        <tr>
                                            <td width="15%">Descripción</td>
                                            <td><input id="txtDesc" class="easyui-textbox" data-options="prompt: 'Ingrese una descripción...'" style="width:85%" maxlength="200"></td>
                                        </tr>
                                        <tr>
                                            <td width="15%">Dirección web</td>
                                            <td><input id="txtURL" class="easyui-textbox" data-options="prompt: 'Ingrese una URL...'" style="width:85%" maxlength="400"></td>
                                        </tr>
                                    </table>
                                </div>

                                <div id="ftURL" style="height:auto">
                                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-add',plain:true" onclick="appendURL()">Agregar</a>
                                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onclick="removeitURL()">Borrar</a>
                                </div>
                            </div>
                        </div>
                        <!--Fin del DIV de Tab URL's-->
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
window.onload = function() {
    var modulo = document.getElementById('cboModulo').value;
    verificaCalificaciones(modulo);
    $('#txtContenidos').keypress(function(e) {
        if (e.which == 13) {
            return false;
        }
    });

    $('#dgOBJ').datagrid({
        striped: true,
        singleSelect: true,
        toolbar:'#tbOBJ',
        footer:'#ftOBJ',
        method:'get' 
    });

    $('#dgACT').datagrid({
        striped: true,
        singleSelect: true,
        toolbar:'#tbACT',
        footer:'#ftACT',
        method:'get' 
    });

    $('#dgMAT').datagrid({
        striped: true,
        singleSelect: true,
        toolbar:'#tbMAT',
        footer:'#ftMAT',
        method:'get' 
    });

    $('#dgARCH').datagrid({
        striped: true,
        singleSelect: true,
        toolbar:'#tbARCH',
        footer:'#ftARCH',
        method:'get' 
    });

    $('#dgURL').datagrid({
        striped: true,
        singleSelect: true,
        toolbar:'#tbURL',
        footer:'#ftURL',
        method:'get' 
    });
}

function verificarFormulario() {
    var regOBJ = $('#dgOBJ').datagrid('getRows').length;
    var regACT = $('#dgACT').datagrid('getRows').length;
    var regMAT = $('#dgMAT').datagrid('getRows').length;
    var gridObjetivos = $('#dgOBJ').datagrid('getData');
    var gridActividades = $('#dgACT').datagrid('getData');
    var calificaciones = document.getElementById('txnCalificaciones').value;
    var fechaHoy = new Date();
    var anno = fechaHoy.getFullYear();
    var mes = fechaHoy.getMonth() + 1;
    var dia = fechaHoy.getDate();
    var administrador = <?php echo($Administrador) ?>;
    var academico = <?php echo($Academico) ?>;
    var texto = "";

    if (dia < 10)
    {
        if (mes < 10)
            var fechaAhora = anno + "-0" + mes + "-0" + dia;
        else
            var fechaAhora = anno + "-" + mes + "-0" + dia;
    }
    else
    {
        if (mes < 10)
            var fechaAhora = anno + "-0" + mes + "-" + dia;
        else
            var fechaAhora = anno + "-" + mes + "-" + dia;
    }

    if (document.getElementById('cboFechaClase').value == "") {
        $.messager.alert('INOFE', 'Falta la Fecha de Clase.', 'warning');
        return false;
    }

    if (calificaciones != 0) {
        $.messager.alert('KDSA', 'Las calificaciones están incompletas. No podrá guardar la Planificación de Clases hasta que ingrese las calificaciones de los módulos anteriores.', 'warning');
        return false;
    }
/*
    if (administrador == 0 && academico == 0)
    {
        if (document.getElementById('cboFechaClase').value < fechaAhora) {
            if (document.getElementById('txtCodPlanClase').value == "")
                $.messager.alert('INOFE', 'La Planificación no puede ingresarse porque la fecha de clase ya transcurrió.', 'warning');
            else
                $.messager.alert('INOFE', 'La Planificación no puede modificarse porque la clase ya fue impartida.', 'warning');
            return false;
        }
    }
*/
    if (document.getElementById('txtContenidos').value == "") {
        $.messager.alert('INOFE', 'Falta el Contenido a desarrollar.', 'warning');
        return false;
    }

    if (regOBJ == 0) {
        $.messager.alert('INOFE', 'Faltan los Objetivos de la clase.', 'warning');
        return false;
    }

    for (i = 0; i < regOBJ; i++) {
        texto = gridObjetivos.rows[i].objetivo;
        if (texto.length > 400)
        {
            $.messager.alert('INOFE', 'El texto del objetivo ' + (i + 1) + ' supera la longitud permitida.', 'warning');
            return false;
        }
    }

    if (regACT == 0) {
        $.messager.alert('INOFE', 'Faltan las Actividades de enseñanza.', 'warning');
        return false;
    }

    for (i = 0; i < regACT; i++) {
        texto = gridActividades.rows[i].actividad;
        if (texto.length > 400)
        {
            $.messager.alert('INOFE', 'El texto de la actividad ' + (i + 1) + ' supera la longitud permitida.', 'warning');
            return false;
        }
    }

    if (regMAT == 0) {
        $.messager.alert('INOFE', 'Faltan los Materiales de apoyo.', 'warning');
        return false;
    }

    return true;
}

function verificaCalificaciones (modulo)
{
    var datos = new FormData();
    datos.append('msModulo', modulo);
    datos.append('mbAdministrador', '<?php echo($Administrador) ?>');
    datos.append('mbAcademico', '<?php echo($Academico) ?>');

    $.ajax({
        url: 'funciones/fxDatosPlanClase.php',
        type: 'post',
        data: datos,
        contentType: false,
        processData: false,
        success: function(response){
            document.getElementById('txnCalificaciones').value = response;
        }
    })
}

function llenaModulos (curso, administrador)
{
    var datos = new FormData();
    datos.append('modulosCurso', curso);
    datos.append('modulosDocente', '<?php echo($_SESSION["gsDocente"]) ?>');
    datos.append('mbAdministrador', administrador);
    datos.append('mnTipo', 0);

    $.ajax({
        url: 'funciones/fxDatosPlanClase.php',
        type: 'post',
        data: datos,
        contentType: false,
        processData: false,
        success: function(response){
            var modulo;
            document.getElementById('cboModulo').innerHTML = response
            modulo = document.getElementById('cboModulo').value;
            funcionFechas(modulo);
        }
    })
}

function funcionFechas (modulo) {
    llenaFechas (modulo);
    verificaCalificaciones(modulo);
}

function llenaFechas (modulo) {
    var datos = new FormData();
    datos.append('moduloPlanClase', modulo);

    $.ajax({
        url: 'funciones/fxDatosPlanClase.php',
        type: 'post',
        data: datos,
        contentType: false,
        processData: false,
        success: function(response) {
            document.getElementById('cboFechaClase').innerHTML = response;
        }
    })
}

/*Grid de Objetivos*/
var editIndexOBJ = undefined;
var lastIndexOBJ;

$('#dgOBJ').datagrid({
    onClickRow: function(rowIndex) {
        if (lastIndexOBJ != rowIndex) {
            $(this).datagrid('endEdit', lastIndexOBJ);
            $(this).datagrid('beginEdit', rowIndex);
        }
        lastIndexOBJ = rowIndex;
    }
});

function endEditingOBJ() {
    if (editIndexOBJ == undefined) {
        return true
    }
    if ($('#dgOBJ').datagrid('validateRow', editIndexOBJ)) {
        $('#dgOBJ').datagrid('endEdit', editIndexOBJ);
        editIndexOBJ = undefined;
        return true;
    } else {
        return false;
    }
}

function onClickCellOBJ(index, field) {
    if (editIndexOBJ != index) {
        if (endEditingOBJ()) {
            $('#dgOBJ').datagrid('selectRow', index)
                .datagrid('beginEdit', index);
            editIndexOBJ = index;
        } else {
            setTimeout(function() {
                $('#dgOBJ').datagrid('selectRow', editIndexOBJ);
            }, 0);
        }
    }
}

function appendOBJ() {
    if (endEditingOBJ()) {
        var datos = $('#dgOBJ').datagrid('getData');

        $('#dgOBJ').datagrid('appendRow', {
            objetivo: $('#txtObjetivo').val()
        });
        editIndexOBJ = $('#dgOBJ').datagrid('getRows').length;
        $('#dgOBJ').datagrid('selectRow', editIndexOBJ).datagrid('beginEdit', editIndexOBJ);
    }
}

function removeitOBJ() {
    if (editIndexOBJ == undefined) {
        return
    }
    $('#dgOBJ').datagrid('cancelEdit', editIndexOBJ)
        .datagrid('deleteRow', editIndexOBJ);
    editIndexOBJ = undefined;
}

function acceptitOBJ() {
    if (endEditingOBJ()) {
        $('#dgOBJ').datagrid('acceptChanges');
    }
}

function rejectOBJ() {
    $('#dgOBJ').datagrid('rejectChanges');
    editIndexOBJ = undefined;
}

/*Grid de Actividades de enseñanza*/
var editIndexACT = undefined;
var lastIndexACT;

$('#dgACT').datagrid({
    onClickRow: function(rowIndex) {
        if (lastIndexACT != rowIndex) {
            $(this).datagrid('endEdit', lastIndexACT);
            $(this).datagrid('beginEdit', rowIndex);
        }
        lastIndexACT = rowIndex;
    }
});

function endEditingACT() {
    if (editIndexACT == undefined) {
        return true
    }
    if ($('#dgACT').datagrid('validateRow', editIndexACT)) {
        $('#dgACT').datagrid('endEdit', editIndexACT);
        editIndexACT = undefined;
        return true;
    } else {
        return false;
    }
}

function onClickCellACT(index, field) {
    if (editIndexACT != index) {
        if (endEditingACT()) {
            $('#dgACT').datagrid('selectRow', index)
                .datagrid('beginEdit', index);
            editIndexACT = index;
        } else {
            setTimeout(function() {
                $('#dgACT').datagrid('selectRow', editIndexACT);
            }, 0);
        }
    }
}

function appendACT() {
    if (endEditingACT()) {
        $('#dgACT').datagrid('appendRow', {
            actividad: $('#txtActividad').val()
        });
        editIndexACT = $('#dgACT').datagrid('getRows').length;
        $('#dgACT').datagrid('selectRow', editIndexACT).datagrid('beginEdit', editIndexACT);
    }
}

function removeitACT() {
    if (editIndexACT == undefined) {
        return
    }
    $('#dgACT').datagrid('cancelEdit', editIndexACT)
        .datagrid('deleteRow', editIndexACT);
    editIndexACT = undefined;
}

function acceptitACT() {
    if (endEditingACT()) {
        $('#dgACT').datagrid('acceptChanges');
    }
}

function rejectACT() {
    $('#dgACT').datagrid('rejectChanges');
    editIndexACT = undefined;
}

/*Grid de Materiales de apoyo*/
var editIndexMAT = undefined;
var lastIndexMAT;

$('#dgMAT').datagrid({
    onClickRow: function(rowIndex) {
        if (lastIndexMAT != rowIndex) {
            $(this).datagrid('endEdit', lastIndexMAT);
            $(this).datagrid('beginEdit', rowIndex);
        }
        lastIndexMAT = rowIndex;
    }
});

function endEditingMAT() {
    if (editIndexMAT == undefined) {
        return true
    }
    if ($('#dgMAT').datagrid('validateRow', editIndexMAT)) {
        $('#dgMAT').datagrid('endEdit', editIndexMAT);
        editIndexMAT = undefined;
        return true;
    } else {
        return false;
    }
}

function onClickCellMAT(index, field) {
    if (editIndexMAT != index) {
        if (endEditingMAT()) {
            $('#dgMAT').datagrid('selectRow', index)
                .datagrid('beginEdit', index);
            editIndexMAT = index;
        } else {
            setTimeout(function() {
                $('#dgMAT').datagrid('selectRow', editIndexMAT);
            }, 0);
        }
    }
}

function appendMAT() {
    if (endEditingMAT()) {
        $('#dgMAT').datagrid('appendRow', {
            material: $('#txtMaterial').val()
        });
        editIndexMAT = $('#dgMAT').datagrid('getRows').length;
        $('#dgMAT').datagrid('selectRow', editIndexMAT).datagrid('beginEdit', editIndexMAT);
    }
}

function removeitMAT() {
    if (editIndexMAT == undefined) {
        return
    }
    $('#dgMAT').datagrid('cancelEdit', editIndexMAT)
        .datagrid('deleteRow', editIndexMAT);
    editIndexMAT = undefined;
}

function acceptitMAT() {
    if (endEditingMAT()) {
        $('#dgMAT').datagrid('acceptChanges');
    }
}

function rejectMAT() {
    $('#dgMAT').datagrid('rejectChanges');
    editIndexMAT = undefined;
}

/*Grid de Archivos de apoyo*/
var editIndexARCH = undefined;
var lastIndexARCH;

$('#dgARCH').datagrid({
    onClickRow: function(rowIndex) {
        if (lastIndexARCH != rowIndex) {
            $(this).datagrid('endEdit', lastIndexARCH);
            $(this).datagrid('beginEdit', rowIndex);
        }
        lastIndexARCH = rowIndex;
    }
});

function endEditingARCH() {
    if (editIndexARCH == undefined) {
        return true
    }
    if ($('#dgARCH').datagrid('validateRow', editIndexARCH)) {
        $('#dgARCH').datagrid('endEdit', editIndexARCH);
        editIndexARCH = undefined;
        return true;
    } else {
        return false;
    }
}

function onClickCellARCH(index, field) {
    if (editIndexARCH != index) {
        if (endEditingARCH()) {
            $('#dgARCH').datagrid('selectRow', index)
                .datagrid('beginEdit', index);
            editIndexARCH = index;
        } else {
            setTimeout(function() {
                $('#dgARCH').datagrid('selectRow', editIndexARCH);
            }, 0);
        }
    }
}

function appendARCH() {
    var msArchivo = $('#fbArchivo').val();
    var msPlanClase = $('#txtCodPlanClase').val();
    var regARCH = $('#dgARCH').datagrid('getRows').length;
    var gridArch = $('#dgARCH').datagrid('getData');
    var mbExisteArchivo = false;

    if (msPlanClase == ""){
        $.messager.alert('KDSA', 'Guarde primero el plan de clase y luego suba los archivos.', 'warning');
        return false;
    }

    if (msArchivo == ""){
        $.messager.alert('KDSA', 'No ha seleccionado el archivo.', 'warning');
        return false;
    }

    for (i=0; i<regARCH; i++)
    {
        if (msArchivo == gridArch.rows[i].descripcion)
            mbExisteArchivo = true;
    }

    if (mbExisteArchivo == false)
    {
        if (document.getElementById('optTipo1').checked){
            msTipo = "Teoría";
            mnTipo = 0;
        }
        else{
            msTipo = "Ejercicio";
            mnTipo = 1;
        }

        var datos = new FormData();
        var files = $('#fbArchivo')[0].files[0];
        var planClase = $('#txtCodPlanClase').val();
        datos.append('archivo', files);
        datos.append('txtPlanClase', planClase);
        datos.append('optTipo', mnTipo);

        $.ajax({
            url: 'funciones/fxDatosPlanClase.php',
            type: 'post',
            data: datos,
            contentType: false,
            processData: false,
            success: function(response) {
                if (response != "") {
                    datos = JSON.parse(response);
                    $('#dgARCH').datagrid({data: datos});
                    $('#dgARCH').datagrid('reload');
                    document.getElementById('fbArchivo').value = "";
                } else {
                    $.messager.alert('KDSA', 'Error en la subida del archivo.', 'warning');
                }
            }
        });
        return false;   
    }
    else
        $.messager.alert('KDSA', 'El archivo ya ha sido ingresado.', 'warning');
}

function removeitARCH() {
    if (editIndexARCH == undefined) {
        return
    }
    var datos = new FormData();
    var planClase = $('#txtCodPlanClase').val();
    var filas = $('#dgARCH').datagrid('getRows');
    var detApoyo = filas[editIndexARCH].apoyo;
    var detRuta = filas[editIndexARCH].ruta;

    datos.append('CodPlanClase', planClase);
    datos.append('CodApoyo', detApoyo);
    datos.append('Ruta', detRuta);

    $.ajax({
        url: 'funciones/fxDatosPlanClase.php',
        type: 'post',
        data: datos,
        contentType: false,
        processData: false,
        success: function(response) {
            if (response != "") {
                datos = JSON.parse(response);
                $('#dgARCH').datagrid({data: datos});
                $('#dgARCH').datagrid('reload');
            } else {
                $.messager.alert('KDSA', 'Error en la eliminación del archivo.', 'warning');
            }
        }
    });
    return false;
}

/*Grid de Sitios web*/
var editIndexURL = undefined;
var lastIndexURL;

$('#dgURL').datagrid({
    onClickRow: function(rowIndex) {
        if (lastIndexURL != rowIndex) {
            $(this).datagrid('endEdit', lastIndexURL);
            $(this).datagrid('beginEdit', rowIndex);
        }
        lastIndexURL = rowIndex;
    }
});

function endEditingURL() {
    if (editIndexURL == undefined) {
        return true
    }
    if ($('#dgURL').datagrid('validateRow', editIndexURL)) {
        $('#dgURL').datagrid('endEdit', editIndexURL);
        editIndexURL = undefined;
        return true;
    } else {
        return false;
    }
}

function onClickCellURL(index, field) {
    if (editIndexURL != index) {
        if (endEditingURL()) {
            $('#dgURL').datagrid('selectRow', index)
                .datagrid('beginEdit', index);
            editIndexURL = index;
        } else {
            setTimeout(function() {
                $('#dgURL').datagrid('selectRow', editIndexURL);
            }, 0);
        }
    }
}

function appendURL() {
    var msPlanClase = $('#txtCodPlanClase').val();
    if (msPlanClase == ""){
        $.messager.alert('KDSA', 'Guarde primero el plan de clase y luego agregue los sitios web.', 'warning');
        return false;
    }

    if (endEditingURL()) {
        var datos = $('#dgURL').datagrid('getData');

        $('#dgURL').datagrid('appendRow', {descripcion: $('#txtDesc').val(), url: $('#txtURL').val()});
        editIndexURL = $('#dgURL').datagrid('getRows').length;
        $('#dgURL').datagrid('selectRow', editIndexURL).datagrid('beginEdit', editIndexURL);
    }
}

function removeitURL() {
    if (editIndexURL == undefined) {
        return
    }
    $('#dgURL').datagrid('cancelEdit', editIndexURL)
        .datagrid('deleteRow', editIndexURL);
    editIndexURL = undefined;
}

function acceptitURL() {
    if (endEditingURL()) {
        $('#dgURL').datagrid('acceptChanges');
    }
}

function rejectURL() {
    $('#dgURL').datagrid('rejectChanges');
    editIndexURL = undefined;
}

$('form').submit(function(e) {
    e.preventDefault();

    if (verificarFormulario()) {
        var texto;
        var datos;
        var registros;
        var i;
        var gridObjetivos = $('#dgOBJ').datagrid('getData');
        var gridActividades = $('#dgACT').datagrid('getData');
        var gridMateriales = $('#dgMAT').datagrid('getData');
        var gridURL = $('#dgURL').datagrid('getData');

        texto = '{"txtCodPlanClase":"' + document.getElementById("txtCodPlanClase").value + '", ';
        if (document.getElementById("txtCodPlanClase").value == "")
            texto += '"Operacion":"0", ';
        else
            texto += '"Operacion":"1", ';
        texto += '"cboModulo":"' + document.getElementById("cboModulo").value + '", ';
        texto += '"dtpFecha":"' + document.getElementById("dtpFecha").value + '", ';
        texto += '"cboFechaClase":"' + document.getElementById("cboFechaClase").value + '", ';
        texto += '"txtContenidos":"' + document.getElementById("txtContenidos").value + '", ';
        texto += '"txtAsignaciones":"' + document.getElementById("txtAsignaciones").value + '", ';

        registros = $('#dgOBJ').datagrid('getRows').length - 1;

        if (registros >= 0) {
            texto += '"gridObjetivos": [';
            for (i = 0; i <= registros; i++) {
                texto += '{"objetivo":"' + gridObjetivos.rows[i].objetivo;
                if (i == registros)
                    texto += '"}],';
                else
                    texto += '"},';
            }
        }

        registros = $('#dgACT').datagrid('getRows').length - 1;

        if (registros >= 0) {
            texto += '"gridActividades": [';
            for (i = 0; i <= registros; i++) {
                texto += '{"actividad":"' + gridActividades.rows[i].actividad;
                if (i == registros)
                    texto += '"}],';
                else
                    texto += '"},';
            }
        }

        registros = $('#dgURL').datagrid('getRows').length - 1;

        if (registros >= 0) {
            texto += '"gridURL": [';
            for (i = 0; i <= registros; i++) {
                texto += '{"descripcion":"' + gridURL.rows[i].descripcion + '", "url":"' + gridURL.rows[i].url;
                if (i == registros)
                    texto += '"}],';
                else
                    texto += '"},';
            }
        }

        registros = $('#dgMAT').datagrid('getRows').length - 1;

        if (registros >= 0) {
            texto += '"gridMateriales": [';
            for (i = 0; i <= registros; i++) {
                texto += '{"material":"' + gridMateriales.rows[i].material;
                if (i == registros)
                    texto += '"}]}';
                else
                    texto += '"},';
            }
        }

        datos = JSON.parse(texto);

        $.ajax({
            url: 'procPlanClase.php',
            type: 'post',
            data: datos,
        })
        .done(function() {
            location.href = "gridPlanClase.php";
        })
        .fail(function() {
            console.log('Error')
        });
    }
});
</script>