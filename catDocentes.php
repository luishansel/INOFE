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
	require_once ("funciones/fxDocentes.php");
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
		$PermisoUsuario = fxPermisoUsuario("catDocentes");
		
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
			if (isset($_POST["txtCodDocente"]))
			{
				$Codigo = $_POST["txtCodDocente"];
				$Usuario = $_POST["cboUsuario"];
				$Nombre = $_POST["txtNombre"];
				$Cedula = $_POST["txtCedula"];
				$Correo = $_POST["txtCorreo"];
				$Telefono = $_POST["txtTelefono"];
				$Direccion = $_POST["txtDireccion"];
                $Activo = $_POST["optActivo"];
                if (isset($_POST["gridCursos"]))
                    $gridCursos = $_POST["gridCursos"];
                if (isset($_POST["gridAcademica"]))
                    $gridAcademica = $_POST["gridAcademica"];
                if (isset($_POST["gridEspecializacion"]))
                    $gridEspecializacion = $_POST["gridEspecializacion"];
                if (isset($_POST["gridLaboral"]))
                    $gridLaboral = $_POST["gridLaboral"];
                if (isset($_POST["gridDocente"]))
                    $gridDocente = $_POST["gridDocente"];
                if (isset($_POST["gridReferencias"]))
                    $gridReferencias = $_POST["gridReferencias"];
                if (isset($_POST["gridAdicional"]))
                    $gridAdicional = $_POST["gridAdicional"];
                if (isset($_POST["gridSoporte"]))
				    $gridSoporte = $_POST["gridSoporte"];

				{
					if ($Codigo == "")
					{
						$Codigo = fxGuardarDocentes ($Usuario, $Nombre, $Cedula, $Correo, $Telefono, $Direccion, $Activo);
						fxAgregarBitacora ($_SESSION["gsUsuario"], "KDSA100A", $Codigo, "", "Agregar");
					}
					else
					{
						fxModificarDocentes ($Codigo, $Usuario, $Nombre, $Cedula, $Correo, $Telefono, $Direccion, $Activo);
						fxBorrarDetCurso ($Codigo);
						fxBorrarDetAcademica ($Codigo);
						fxBorrarDetEspecializacion ($Codigo);
						fxBorrarDetLaboral ($Codigo);
						fxBorrarDetDocente ($Codigo);
						fxBorrarDetReferencias ($Codigo);
						fxBorrarDetAdicional ($Codigo);
						fxAgregarBitacora ($_SESSION["gsUsuario"], "KDSA100A", $Codigo, "", "Modificar");
					}
				}
				
				$itemId = 1;
				foreach($gridCursos as $Registro)
				{
					$curCurso = $Registro['curso'];
                    fxGuardarDetCurso ($Codigo, $itemId, $curCurso);
                    $itemId++;
				}
				
				$itemId = 1;
				foreach($gridAcademica as $Registro)
				{
					$acaGrado = $Registro['grado'];
					$acaTitulo = $Registro['titulo'];
					$acaCentro = $Registro['centro'];
					$acaAnno = $Registro['anno'];
					fxGuardarDetAcademica ($Codigo, $itemId, $acaGrado, $acaTitulo, $acaCentro, $acaAnno);
					$itemId++;
				}
				
				$itemId = 1;
				foreach($gridEspecializacion as $Registro)
				{
					$espTitulo = $Registro['titulo'];
					$espCentro = $Registro['centro'];
					$espAnno = $Registro['anno'];
					fxGuardarDetEspecializacion ($Codigo, $itemId, $espTitulo, $espCentro, $espAnno);
					$itemId++;
				}
				
				$itemId = 1;
				foreach($gridLaboral as $Registro)
				{
					$labEmpresa = $Registro['empresa'];
					$labFunciones = $Registro['funciones'];
					$labCargo = $Registro['cargo'];
					$labPeriodo = $Registro['periodo'];
					fxGuardarDetLaboral ($Codigo, $itemId, $labEmpresa, $labCargo, $labFunciones, $labPeriodo);
					$itemId++;
				}
				
				$itemId = 1;
				foreach($gridDocente as $Registro)
				{
					$docCentro = $Registro['centro'];
					$docClases = $Registro['clases'];
					$docPeriodo = $Registro['periodo'];
					fxGuardarDetDocente ($Codigo, $itemId, $docCentro, $docClases, $docPeriodo);
					$itemId++;
				}
				
				$itemId = 1;
				foreach($gridReferencias as $Registro)
				{
					$refNombre = $Registro['nombre'];
					$refOcupacion = $Registro['ocupacion'];
					$refTelefono = $Registro['telefono'];
					$refCedula = $Registro['cedula'];
					fxGuardarDetReferencias ($Codigo, $itemId, $refNombre, $refOcupacion, $refTelefono, $refCedula);
					$itemId++;
				}
				
				$itemId = 1;
				foreach($gridAdicional as $Registro)
				{
					$adiInformacion = $Registro['informacion'];
					fxGuardarDetAdicional ($Codigo, $itemId, $adiInformacion);
					$itemId++;
				}
											
				?>
<meta http-equiv="Refresh" content="0;url=gridDocentes.php" /><?php
			}
			else
			{
                if (isset($_POST["KDSA"]))
				    $Codigo = trim($_POST["KDSA"]);
                else
                    $Codigo = "";
                
                if ($Codigo != "")
                {
                    $RecordSet = fxDevuelveDocentes (0, $Codigo);
                    $Fila = $RecordSet->fetch();
                    $Usuario = $Fila["USUARIO_REL"];
                    $Nombre = $Fila["NOMBRE_100"];
                    $Cedula = $Fila["CEDULA_100"];
                    $Correo = $Fila["CORREO_100"];
                    $Telefonos = $Fila["TELEFONOS_100"];
                    $Direccion = $Fila["DIRECCION_100"];
                    $Activo = $Fila["ACTIVO_100"];
                }
                else
                {
                    $Usuario = "";
                    $Nombre = "";
                    $Cedula = "";
                    $Correo = "";
                    $Telefonos = "";
                    $Direccion = "";
                    $Activo = 0;
                }
	?>
<div class="container text-left">
    <div id="DivContenido">
        <div class = "row">
            <div class="col-xs-12 col-md-11">
                <div class="degradado"><strong>Catálogo de docentes</strong></div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-md-12">
                <form id="catDocentes" name="catDocentes">
                    <div class="row">
                        <div class="col-md-12">
                            <input type="submit" id="Guardar" name="Guardar" value="Guardar" class="btn btn-warning" />
                            <input type="button" id="Cancelar" name="Cancelar" value="Cancelar" class="btn btn-warning"  onclick="location.href='gridDocentes.php';" />
                        </div>
                    </div>

                    <div class="easyui-tabs tabs-narrow" style="width:100%;height:auto">
                        <!--Inicio del DIV de Tabs-->
                        <div title="Generales" style="padding:10px">
                            <!--Inicio del DIV de Tab GENERALES-->
                            <div class="col-xs-auto col-xs-offset-none col-md-11 col-md-offset-1">
                                <div class="form-group row">
                                    <label for="txtCodDocente" class="col-sm-auto col-md-2 col-form-label">Código del Docente</label>
                                    <div class="col-sm-12 col-md-3">
                                        <?php echo('<input type="text" class="form-control" id="txtCodDocente" name="txtCodDocente" value="' . $Codigo . '" readonly />'); ?>
                                    </div>
                                    <div class="col-auto">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="cboUsuario" class="col-sm-auto col-md-2 col-form-label">Usuario relacionado</label>
                                    <div class="col-sm-12 col-md-7">
                                        <select class="form-control" id="cboUsuario" name="cboUsuario">
                                        <?php
                                            $msConsulta = "select USUARIO_REL, NOMBRE_002, ACTIVO_002 from KDSA002A order by NOMBRE_002";
                                            $m_cnx_MySQL = fxAbrirConexion();
                                            $mDatos = $m_cnx_MySQL->prepare($msConsulta);
		                                    $mDatos->execute();
                                            while ($Fila = $mDatos->fetch())
                                            {
                                                $Valor = rtrim($Fila["USUARIO_REL"]);
                                                $Texto = rtrim($Fila["USUARIO_REL"]) . " - " . rtrim($Fila["NOMBRE_002"]);
                                                $ActivoUsr = $Fila["ACTIVO_002"];
                                                if ($Codigo == "")
                                                {
                                                    if ($ActivoUsr == 1)
                                                        echo("<option value='" . $Valor . "'>" . $Texto . "</option>");
                                                }
                                                else
                                                {
                                                    if ($Usuario == "")
                                                    {
                                                        echo("<option value='" . $Valor . "'>" . $Texto . "</option>");
                                                        $Usuario = $Valor;
                                                    }
                                                    else
                                                    {
                                                        if ($Usuario == $Valor)
                                                            echo("<option value='" . $Valor . "' selected>" . $Texto . "</option>");
                                                        else
                                                            echo("<option value='" . $Valor . "'>" . $Texto . "</option>");
                                                    }
                                                }
                                            }
                                        ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="txtNombre" class="col-sm-auto col-md-2 col-form-label">Nombre del Docente</label>
                                    <div class="col-sm-12 col-md-7">
                                        <?php echo('<input type="text" class="form-control" id="txtNombre" name="txtNombre" value="' . $Nombre . '" />'); ?>
                                    </div>
                                    <div class="col-auto">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="txtCedula" class="col-sm-auto col-md-2 form-label">Cédula</label>
                                    <div class="col-sm-12 col-md-4">
                                        <?php echo('<input type="text" class="form-control" id="txtCedula" name="txtCedula" value="' . $Cedula . '" />'); ?>
                                    </div>
                                    <div class="col-auto">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="txtCorreo" class="col-sm-auto col-md-2 form-label">Correo
                                        electrónico</label>
                                    <div class="col-sm-12 col-md-4">
                                        <?php echo('<input type="text" class="form-control" id="txtCorreo" name="txtCorreo" value="' . $Correo . '" />'); ?>
                                    </div>
                                    <div class="col-auto">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="txtTelefono" class="col-sm-auto col-md-2 form-label">Teléfono</label>
                                    <div class="col-sm-12 col-md-4">
                                        <?php echo('<input type="text" class="form-control" id="txtTelefono" name="txtTelefono" value="' . $Telefonos . '" />'); ?>
                                    </div>
                                    <div class="col-auto">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="txtDireccion" class="col-sm-auto col-md-2 form-label">Dirección</label>
                                    <div class="col-sm-12 col-md-7">
                                        <?php echo('<textarea class="form-control" id="txtDireccion" name="txtDireccion" rows="3">' . $Direccion . '</textarea>'); ?>
                                    </div>
                                    <div class="col-auto">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="optActivo" class="col-sm-auto col-md-2 form-label">Activo</label>
                                    <div class="col-sm-12 col-md-3">
                                        <div class="radio">
                                            <?php
                                if ($Activo == 1)
                                {
                                    echo('<input type="radio" id="optActivo1" name="optActivo" value="0" /> No <input type="radio" id="optActivo2" name="optActivo" value="1" checked/> Si');
                                }
                                else
                                {
                                    echo('<input type="radio" id="optActivo1" name="optActivo" value="0" checked/> No <input type="radio" id="optActivo2" name="optActivo" value="1" /> Si');
                                }
                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--Fin del DIV de Tab GENERALES-->

                        <div title="Cursos que imparte" style="padding:10px">
                            <!--Inicio del DIV de Tab CURSOS-->
                            <div class="col-xs-auto col-md-12">
                                <!--Inicio del DIV Columna CURSOS-->
                                <div class="form-group row">
                                    <div class="col-sm-auto col-md-12">
                                    <?php 
                                        $nombreArchivoCUR = fxEscribeJsonCurso($Codigo);
                                    ?>
                                        <div id="dvCUR">
                                            <table id="dgCUR" class="easyui-datagrid table"
                                                data-options="iconCls:'icon-edit', toolbar:'#tbCUR', footer:'#ftCUR', singleSelect:true, url:'<?php echo(rtrim($nombreArchivoCUR)); ?>', method:'get', onClickCell: onClickCellCUR">
                                                <thead>
                                                    <tr>
                                                        <th data-options="field:'curso',width:'100%',align:'left'">Curso o Seminario</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div id="tbCUR" style="height:auto; padding-top:1%; padding-bottom:2%">
                                    <table width="100%">
                                        <tr>
                                            <td>Cursos o Seminario que impartirá con KDSA</td>
                                            <td><input id="txtCursoKdsa" class="easyui-textbox" style="width:100%"></td>
                                        </tr>
                                    </table>
                                </div>

                                <div id="ftCUR" style="height:auto">
                                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-add',plain:true" onclick="appendCUR()">Agregar</a>
                                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onclick="removeitCUR()">Borrar</a>
                                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-save',plain:true" onclick="acceptitCUR()">Aceptar</a>
                                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-undo',plain:true" onclick="rejectCUR()">Deshacer</a>
                                </div>
                            </div>
                            <!--Fin del DIV Columna CURSOS-->
                        </div>
                        <!--Fin del DIV de Tab CURSOS-->

                        <div title="Formación académica" style="padding:10px">
                            <!--Inicio del DIV de Tab ACADEMICO-->
                            <div class="col-sm-auto col-md-12">
                                <!--Inicio del DIV Columna ACADEMICO-->
                                <div class="form-group row">
                                    <div class="col-sm-auto col-md-12">
                                    <?php
                                        $nombreArchivoACA = fxEscribeJsonAcademico($Codigo);
                                    ?>
                                        <div id="dvACA">
                                            <table id="dgACA" class="easyui-datagrid table"
                                                data-options="iconCls:'icon-edit', toolbar:'#tbACA', footer:'#ftACA', singleSelect:true, url:'<?php echo(rtrim($nombreArchivoACA)); ?>', method:'get', onClickCell: onClickCellACA">
                                                <thead>
                                                    <tr>
                                                        <th data-options="field:'grado',width:'25%',align:'left',editor:'text'">Grado académico</th>
                                                        <th data-options="field:'titulo',width:'30%',align:'left',editor:'text'">Título obtenido</th>
                                                        <th data-options="field:'centro',width:'30%',align:'left',editor:'text'">Centro educativo</th>
                                                        <th data-options="field:'anno',width:'15%',align:'left',editor:'text'">Año de graduación</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div id="tbACA" style="height:auto; padding-top:1%; padding-bottom:2%">
                                    <table width="100%">
                                        <tr>
                                            <td>Grado académico</td>
                                            <td><input id="txtGrado" class="easyui-textbox" style="width:60%"></td>
                                        </tr>
                                        <tr>
                                            <td>Título obtenido</td>
                                            <td><input id="txtTitulo" class="easyui-textbox" style="width:60%"></td>
                                        </tr>
                                        <tr>
                                            <td>Centro de estudios</td>
                                            <td><input id="txtCentro" class="easyui-textbox" style="width:60%"></td>
                                        </tr>
                                        <tr>
                                            <td>Año de graduación</td>
                                            <td><input id="txtAnno" class="easyui-textbox" style="width:30%"></td>
                                        </tr>
                                    </table>
                                </div>

                                <div id="ftACA" style="height:auto">
                                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-add',plain:true" onclick="appendACA()">Agregar</a>
                                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onclick="removeitACA()">Borrar</a>
                                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-save',plain:true" onclick="acceptitACA()">Aceptar</a>
                                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-undo',plain:true" onclick="rejectACA()">Deshacer</a>
                                </div>
                            </div>
                            <!--Fin del DIV Columna ACADEMICO-->
                        </div>
                        <!--Fin del DIV de Tab ACADEMICO-->

                        <div title="Especialización" style="padding:10px">
                            <!--Inicio del DIV de Tab ESPECIALIZACION-->
                            <div class="col-xs-auto col-md-12">
                                <!--Inicio del DIV Columna ESPECIALIZACION-->
                                <div class="form-group row">
                                    <div class="col-sm-auto col-md-12">
                                    <?php 
                                        $nombreArchivoESP = fxEscribeJsonEspecializacion($Codigo);
                                    ?>
                                        <div id="dvESP">
                                            <table id="dgESP" class="easyui-datagrid table"
                                                data-options="iconCls:'icon-edit', toolbar:'#tbESP', footer:'#ftESP', singleSelect:true, url:'<?php echo(rtrim($nombreArchivoESP)); ?>', method:'get', onClickCell: onClickCellESP">
                                                <thead>
                                                    <tr>
                                                        <th data-options="field:'titulo',width:'40%',align:'left'">Título obtenido</th>
                                                        <th data-options="field:'centro',width:'40%',align:'left'">Institución</th>
                                                        <th data-options="field:'anno',width:'20%',align:'left'">Año de graduación</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div id="tbESP" style="height:auto; padding-top:1%; padding-bottom:2%">
                                    <table width="100%">
                                        <tr>
                                            <td>Título obtenido</td>
                                            <td><input id="txtTituloEsp" class="easyui-textbox" style="width:60%"></td>
                                        </tr>
                                        <tr>
                                            <td>Institución que lo acredita</td>
                                            <td><input id="txtCentroEsp" class="easyui-textbox" style="width:60%"></td>
                                        </tr>
                                        <tr>
                                            <td>Año de graduación</td>
                                            <td><input id="txtAnnoEsp" class="easyui-textbox" style="width:30%"></td>
                                        </tr>
                                    </table>
                                </div>

                                <div id="ftESP" style="height:auto">
                                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-add',plain:true" onclick="appendESP()">Agregar</a>
                                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onclick="removeitESP()">Borrar</a>
                                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-save',plain:true" onclick="acceptitESP()">Aceptar</a>
                                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-undo',plain:true" onclick="rejectESP()">Deshacer</a>
                                </div>
                            </div>
                            <!--Fin del DIV Columna ESPECIALIZACION-->
                        </div>
                        <!--Fin del DIV de Tab ESPECIALIZACION-->

                        <div title="Experiencia laboral" style="padding:10px">
                            <!--Inicio del DIV de Tab LABORAL-->
                            <div class="col-xs-auto col-md-12">
                                <!--Inicio del DIV Columna LABORAL-->
                                <div class="form-group row">
                                    <div class="col-sm-auto col-md-12">
                                    <?php 
                                        $nombreArchivoLAB = fxEscribeJsonLaboral($Codigo);
                                    ?>
                                        <div id="dvLAB">
                                            <table id="dgLAB" class="easyui-datagrid table"
                                                data-options="iconCls:'icon-edit', toolbar:'#tbLAB', footer:'#ftLAB', singleSelect:true, url:'<?php echo(rtrim($nombreArchivoLAB)); ?>', method:'get', onClickCell: onClickCellLAB">
                                                <thead>
                                                    <tr>
                                                        <th data-options="field:'empresa',width:'25%',align:'left'">Empresa</th>
                                                        <th data-options="field:'cargo',width:'25%',align:'left'">Cargo</th>
                                                        <th data-options="field:'funciones',width:'40%',align:'left'">Funciones</th>
                                                        <th data-options="field:'periodo',width:'10%',align:'left'">Período</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div id="tbLAB" style="height:auto; padding-top:1%; padding-bottom:2%">
                                    <table width="100%">
                                        <tr>
                                            <td>Empresa</td>
                                            <td><input id="txtEmpresa" class="easyui-textbox" style="width:60%"></td>
                                        </tr>
                                        <tr>
                                            <td>Cargo</td>
                                            <td><input id="txtCargo" class="easyui-textbox" style="width:60%"></td>
                                        </tr>
                                        <tr>
                                            <td valign="top">Funciones</td>
                                            <td><input id="txtFunciones" class="easyui-textbox"
                                                    data-options="multiline:'true', height:'100'" style="width:60%">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Período</td>
                                            <td><input id="txtPeriodo" class="easyui-textbox" style="width:30%"></td>
                                        </tr>
                                    </table>
                                </div>

                                <div id="ftLAB" style="height:auto">
                                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-add',plain:true" onclick="appendLAB()">Agregar</a>
                                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onclick="removeitLAB()">Borrar</a>
                                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-save',plain:true" onclick="acceptitLAB()">Aceptar</a>
                                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-undo',plain:true" onclick="rejectLAB()">Deshacer</a>
                                </div>
                            </div>
                            <!--Fin del DIV Columna LABORAL-->
                        </div>
                        <!--Fin del DIV de Tab LABORAL-->

                        <div title="Experiencia docente" style="padding:10px">
                            <!--Inicio del DIV de Tab DOCENTE-->
                            <div class="col-xs-auto col-md-12">
                                <!--Inicio del DIV Columna DOCENTE-->
                                <div class="form-group row">
                                    <div class="col-sm-auto col-md-12">
                                    <?php 
                                        $nombreArchivoDOC = fxEscribeJsonDocente($Codigo);
                                    ?>
                                        <div id="dvDOC">
                                            <table id="dgDOC" class="easyui-datagrid table"
                                                data-options="iconCls:'icon-edit', toolbar:'#tbDOC', footer:'#ftDOC', singleSelect:true, url:'<?php echo(rtrim($nombreArchivoDOC)); ?>', method:'get', onClickCell: onClickCellDOC">
                                                <thead>
                                                    <tr>
                                                        <th data-options="field:'centro',width:'40%',align:'left'">Centro educativo</th>
                                                        <th data-options="field:'clases',width:'40%',align:'left'">Clases impartidas</th>
                                                        <th data-options="field:'periodo',width:'20%',align:'left'">Período</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div id="tbDOC" style="height:auto; padding-top:1%; padding-bottom:2%">
                                    <table width="100%">
                                        <tr>
                                            <td>Centro educativo</td>
                                            <td><input id="txtDocCentro" class="easyui-textbox" style="width:60%"></td>
                                        </tr>
                                        <tr>
                                            <td>Clases impartidas</td>
                                            <td><input id="txtDocClases" class="easyui-textbox" style="width:60%"></td>
                                        </tr>
                                        <tr>
                                            <td>Período</td>
                                            <td><input id="txtDocPeriodo" class="easyui-textbox" style="width:30%"></td>
                                        </tr>
                                    </table>
                                </div>

                                <div id="ftDOC" style="height:auto">
                                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-add',plain:true" onclick="appendDOC()">Agregar</a>
                                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onclick="removeitDOC()">Borrar</a>
                                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-save',plain:true" onclick="acceptitDOC()">Aceptar</a>
                                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-undo',plain:true" onclick="rejectDOC()">Deshacer</a>
                                </div>
                            </div>
                            <!--Fin del DIV Columna DOCENTE-->
                        </div>
                        <!--Fin del DIV de Tab DOCENTE-->

                        <div title="Referencias" style="padding:10px">
                            <!--Inicio del DIV de Tab REFERENCIAS-->
                            <div class="col-xs-auto col-md-12">
                                <!--Inicio del DIV Columna REFERENCIAS-->
                                <div class="form-group row">
                                    <div class="col-sm-auto col-md-12">
                                    <?php 
                                        $nombreArchivoREF = fxEscribeJsonReferencias($Codigo);
                                    ?>
                                        <div id="dvREF">
                                            <table id="dgREF" class="easyui-datagrid table"
                                                data-options="iconCls:'icon-edit', toolbar:'#tbREF', footer:'#ftREF', singleSelect:true, url:'<?php echo(rtrim($nombreArchivoREF)); ?>', method:'get', onClickCell: onClickCellREF">
                                                <thead>
                                                    <tr>
                                                        <th data-options="field:'nombre',width:'30%',align:'left'">Nombre</th>
                                                        <th data-options="field:'ocupacion',width:'30%',align:'left'">Ocupación</th>
                                                        <th data-options="field:'telefono',width:'20%',align:'left'">Teléfono</th>
                                                        <th data-options="field:'cedula',width:'20%',align:'left'">Cédula</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div id="tbREF" style="height:auto; padding-top:1%; padding-bottom:2%">
                                    <table width="100%">
                                        <tr>
                                            <td>Nombre</td>
                                            <td><input id="txtRefNombre" class="easyui-textbox" style="width:60%"></td>
                                        </tr>
                                        <tr>
                                            <td>Ocupación</td>
                                            <td><input id="txtRefOcupacion" class="easyui-textbox" style="width:60%">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Teléfono</td>
                                            <td><input id="txtRefTelefono" class="easyui-textbox" style="width:40%">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Cédula</td>
                                            <td><input id="txtRefCedula" class="easyui-textbox" style="width:40%"></td>
                                        </tr>
                                    </table>
                                </div>

                                <div id="ftREF" style="height:auto">
                                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-add',plain:true" onclick="appendREF()">Agregar</a>
                                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onclick="removeitREF()">Borrar</a>
                                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-save',plain:true" onclick="acceptitREF()">Aceptar</a>
                                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-undo',plain:true" onclick="rejectREF()">Deshacer</a>
                                </div>
                            </div>
                            <!--Fin del DIV Columna REFERENCIAS-->
                        </div>
                        <!--Fin del DIV de Tab REFERENCIAS-->

                        <div title="Adicional" style="padding:10px">
                            <!--Inicio del DIV de Tab ADICIONAL-->
                            <div class="col-xs-auto col-md-12">
                                <!--Inicio del DIV Columna ADICIONAL-->
                                <div class="form-group row">
                                    <div class="col-sm-auto col-md-12">
                                    <?php 
                                        $nombreArchivoADI = fxEscribeJsonAdicional($Codigo);
                                    ?>
                                        <div id="dvADI">
                                            <table id="dgADI" class="easyui-datagrid table" data-options="iconCls:'icon-edit', toolbar:'#tbADI', footer:'#ftADI', singleSelect:true, url:'<?php echo(rtrim($nombreArchivoADI)); ?>', method:'get', onClickCell: onClickCellADI">
                                                <thead>
                                                    <tr>
                                                        <th
                                                            data-options="field:'informacion',width:'100%',align:'left'">Información adicional</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div id="tbADI" style="height:auto; padding-top:1%; padding-bottom:2%">
                                    <table width="100%">
                                        <tr>
                                            <td valign="top">Información adicional</td>
                                            <td><input id="txtInformacion" class="easyui-textbox" data-options="multiline:'true', height:'100'" style="width:80%"></td>
                                        </tr>
                                    </table>
                                </div>

                                <div id="ftADI" style="height:auto">
                                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-add',plain:true" onclick="appendADI()">Agregar</a>
                                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onclick="removeitADI()">Borrar</a>
                                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-save',plain:true" onclick="acceptitADI()">Aceptar</a>
                                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-undo',plain:true" onclick="rejectADI()">Deshacer</a>
                                </div>
                            </div>
                            <!--Fin del DIV Columna ADICIONAL-->
                        </div>
                        <!--Fin del DIV de Tab ADICIONAL-->

                        <div title="Documentos" style="padding:10px">
                            <!--Inicio del DIV de Tab SOPORTE-->
                            <div class="col-xs-auto col-md-12">
                                <!--Inicio del DIV Columna SOPORTE-->
                                <div style="height:auto; padding-top:1%; padding-bottom:2%">
                                    <table width="100%">
                                        <tr>
                                            <td valign="top">Descripción</td>
                                            <td><input id="txtDescripcion" class="easyui-textbox" style="width:80%"></td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td valign="top">Imagen</td>
                                            <td>
                                                <input id="txtRutaLocal" class="easyui-textbox" style="width:80%"
                                                    readonly><label for="archivo" style="margin-left:1%; padding:0.5%"
                                                    data-toggle="tooltip" data-placement="top"
                                                    title="Agregar imagen"><img src="imagenes/imageAdd.png"
                                                        height="100%" style="cursor:pointer" /></label><input
                                                    type="file" accept=".pdf, image/*" id="archivo" style="display:none"
                                                    onchange="llenaArchivo()" />
                                                <label id="cmdSubir" data-toggle="tooltip" data-placement="top"
                                                    title="Subir imagen"><img src="imagenes/imageUp.png" height="100%"
                                                        style="cursor:pointer" /></label>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td><label
                                                    style="font-size:small; font-style:italic; color:rgb(130,130,130)">El
                                                    nombre del archivo no debe contener espacios en blanco.</label></td>
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
                            $extensionImg = strtoupper(substr($Fila["IMAGEN_REL"], -3));
							if ($mnCuenta == 0) {
								$texto .= '<tr>';
							}
							$texto .= '<td width="23%" valign="top" style="margin-left:1%; margin-right:1%">';
							$texto .= '<img src="imagenes/imageDel.png"  id="' . trim($Fila["IMAGEN_REL"]) . '" style="cursor:pointer" onclick="borrarImagen(this)"/><label style="font-size: small"> Borrar ' . trim($Fila["IMAGEN_REL"]) . '</label>';
							if ($extensionImg != 'PDF')
                                $texto .= '<br/><a href="' . trim($Fila["RUTA_108"]) . '" target="_blank"><img src="' . trim($Fila["RUTA_108"]) . '" style="width:100%"/></a>';
                            else
                                $texto .= '<br/><a href="' . trim($Fila["RUTA_108"]) . '" target="_blank"><img src="imagenes/pdf.png" style="width:80%"/></a>';
							$texto .= '<br/><div>' . trim($Fila["DESC_108"]) . '</div';
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
                    <!--Fin del DIV de Tabs-->
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
$(document).ready(function() {
    $('#cmdSubir').on('click', function() {
        if ($('#txtCodDocente').val() == '') {
            $.messager.alert('INOFE', 'Debe guardar la Información General antes de subir los Documentos de soporte.', 'warning');
            return false;
        }

        if ($('#_easyui_textbox_input22').val() == '') {
            $.messager.alert('INOFE', 'Falta el archivo de la imagen.', 'warning');
            return false;
        } else {
            var datos = new FormData();
            var files = $('#archivo')[0].files[0];
            var docente = $('#txtCodDocente').val();
            var descripcion = $('#txtDescripcion').val();
            datos.append('archivo', files);
            datos.append('txtCodDocente', docente);
            datos.append('txtDescripcion', descripcion);

            $.ajax({
                url: 'funciones/fxDocentesImagenes.php',
                type: 'post',
                data: datos,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response != 0) {
                        document.getElementById('dvSOP').innerHTML = response;
                        $('#_easyui_textbox_input21').val('');
                        $('#_easyui_textbox_input22').val('');
                    } else {
                        $.messager.alert('INOFE', 'Error en la subida de la imagen.',
                            'warning');
                    }
                }
            });
            return false;
        }
    });
});

function llenaArchivo() {
    $('#_easyui_textbox_input22').val($('#archivo')[0].files[0].name); //txtRutaLocal
}

function borrarImagen(objeto) {
    var objId = objeto.id;
    var datos = new FormData();
    var docente = $('#txtCodDocente').val();
    datos.append('CodDocente', docente);
    datos.append('CodImagen', objId);

    $.ajax({
        url: 'funciones/fxDocentesImagenes.php',
        type: 'post',
        data: datos,
        contentType: false,
        processData: false,
        success: function(response) {
            if (response != 0) {
                document.getElementById('dvSOP').innerHTML = response;
            } else {
                $.messager.alert('INOFE', 'Error en la eliminación de la imagen.', 'warning');
            }
        }
    });
}

function verificarFormulario() {
    if (document.getElementById('txtNombre').value == "") {
        $.messager.alert('INOFE', 'Falta el Nombre del docente.', 'warning');
        return false;
    }

    if ($('#dgCUR').datagrid('getRows').length <= 0) {
        $.messager.alert('INOFE', 'Faltan los Cursos que imparte el docente.', 'warning');
        return false;
    }

    return true;
}

/*Grid de Cursos que impartirá*/
var editIndexCUR = undefined;
var lastIndexCUR;

$('#dgCUR').datagrid({
    onClickRow: function(rowIndex) {
        if (lastIndexCUR != rowIndex) {
            $(this).datagrid('endEdit', lastIndexCUR);
            $(this).datagrid('beginEdit', rowIndex);
        }
        lastIndexCUR = rowIndex;
    }
});

function endEditingCUR() {
    if (editIndexCUR == undefined) {
        return true
    }
    if ($('#dgCUR').datagrid('validateRow', editIndexCUR)) {
        $('#dgCUR').datagrid('endEdit', editIndexCUR);
        editIndexCUR = undefined;
        return true;
    } else {
        return false;
    }
}

function onClickCellCUR(index, field) {
    if (editIndexCUR != index) {
        if (endEditingCUR()) {
            $('#dgCUR').datagrid('selectRow', index)
                .datagrid('beginEdit', index);
            editIndexCUR = index;
        } else {
            setTimeout(function() {
                $('#dgCUR').datagrid('selectRow', editIndexCUR);
            }, 0);
        }
    }
}

function appendCUR() {
    if (endEditingCUR()) {
        $('#dgCUR').datagrid('appendRow', {
            curso: $('#txtCursoKdsa').val()
        });
        editIndexCUR = $('#dgCUR').datagrid('getRows').length;
        $('#dgCUR').datagrid('selectRow', editIndexCUR).datagrid('beginEdit', editIndexCUR);
        /*Usa los ID de EasyUI para borrar los TextBox*/
        $('#_easyui_textbox_input1').val(''); //txtCursoKdsa
    }
}

function removeitCUR() {
    if (editIndexCUR == undefined) {
        return
    }
    $('#dgCUR').datagrid('cancelEdit', editIndexCUR)
        .datagrid('deleteRow', editIndexCUR);
    editIndexCUR = undefined;
}

function acceptitCUR() {
    if (endEditingCUR()) {
        $('#dgCUR').datagrid('acceptChanges');
    }
}

function rejectCUR() {
    $('#dgCUR').datagrid('rejectChanges');
    editIndexCUR = undefined;
}

/*Grid de Formación Académica*/
var editIndexACA = undefined;
var lastIndexACA;

$('#dgACA').datagrid({
    onClickRow: function(rowIndex) {
        if (lastIndexACA != rowIndex) {
            $(this).datagrid('endEdit', lastIndexACA);
            $(this).datagrid('beginEdit', rowIndex);
        }
        lastIndexACA = rowIndex;
    }
});

function endEditingACA() {
    if (editIndexACA == undefined) {
        return true
    }
    if ($('#dgACA').datagrid('validateRow', editIndexACA)) {
        $('#dgACA').datagrid('endEdit', editIndexACA);
        editIndexACA = undefined;
        return true;
    } else {
        return false;
    }
}

function onClickCellACA(index, field) {
    if (editIndexACA != index) {
        if (endEditingACA()) {
            $('#dgACA').datagrid('selectRow', index)
                .datagrid('beginEdit', index);
            editIndexACA = index;
        } else {
            setTimeout(function() {
                $('#dgACA').datagrid('selectRow', editIndexACA);
            }, 0);
        }
    }
}

function appendACA() {
    if (endEditingACA()) {
        $('#dgACA').datagrid('appendRow', {
            grado: $('#txtGrado').val(),
            titulo: $('#txtTitulo').val(),
            centro: $('#txtCentro').val(),
            anno: $('#txtAnno').val()
        });
        editIndexACA = $('#dgACA').datagrid('getRows').length;
        $('#dgACA').datagrid('selectRow', editIndexACA).datagrid('beginEdit', editIndexACA);
        /*Usa los ID de EasyUI para borrar los TextBox*/
        $('#_easyui_textbox_input2').val(''); //txtGrado
        $('#_easyui_textbox_input3').val(''); //txtTitulo
        $('#_easyui_textbox_input4').val(''); //txtCentro
        $('#_easyui_textbox_input5').val(''); //txtAnno
    }
}

function removeitACA() {
    if (editIndexACA == undefined) {
        return
    }
    $('#dgACA').datagrid('cancelEdit', editIndexACA)
        .datagrid('deleteRow', editIndexACA);
    editIndexACA = undefined;
}

function acceptitACA() {
    if (endEditingACA()) {
        $('#dgACA').datagrid('acceptChanges');
    }
}

function rejectACA() {
    $('#dgACA').datagrid('rejectChanges');
    editIndexACA = undefined;
}

/*Grid de Especialización*/
var editIndexESP = undefined;
var lastIndexESP;

$('#dgESP').datagrid({
    onClickRow: function(rowIndex) {
        if (lastIndexESP != rowIndex) {
            $(this).datagrid('endEdit', lastIndexESP);
            $(this).datagrid('beginEdit', rowIndex);
        }
        lastIndexESP = rowIndex;
    }
});

function endEditingESP() {
    if (editIndexESP == undefined) {
        return true
    }
    if ($('#dgESP').datagrid('validateRow', editIndexESP)) {
        $('#dgESP').datagrid('endEdit', editIndexESP);
        editIndexESP = undefined;
        return true;
    } else {
        return false;
    }
}

function onClickCellESP(index, field) {
    if (editIndexESP != index) {
        if (endEditingESP()) {
            $('#dgESP').datagrid('selectRow', index)
                .datagrid('beginEdit', index);
            editIndexESP = index;
        } else {
            setTimeout(function() {
                $('#dgESP').datagrid('selectRow', editIndexESP);
            }, 0);
        }
    }
}

function appendESP() {
    if (endEditingESP()) {
        $('#dgESP').datagrid('appendRow', {
            titulo: $('#txtTituloEsp').val(),
            centro: $('#txtCentroEsp').val(),
            anno: $('#txtAnnoEsp').val()
        });
        editIndexESP = $('#dgESP').datagrid('getRows').length;
        $('#dgESP').datagrid('selectRow', editIndexESP).datagrid('beginEdit', editIndexESP);
        /*Usa los ID de EasyUI para borrar los TextBox*/
        $('#_easyui_textbox_input6').val(''); //txtTituloEsp
        $('#_easyui_textbox_input7').val(''); //txtCentroEsp
        $('#_easyui_textbox_input8').val(''); //txtAnnoEsp
    }
}

function removeitESP() {
    if (editIndexESP == undefined) {
        return
    }
    $('#dgESP').datagrid('cancelEdit', editIndexESP)
        .datagrid('deleteRow', editIndexESP);
    editIndexESP = undefined;
}

function acceptitESP() {
    if (endEditingESP()) {
        $('#dgESP').datagrid('acceptChanges');
    }
}

function rejectESP() {
    $('#dgESP').datagrid('rejectChanges');
    editIndexESP = undefined;
}

/*Grid de Experiencia Laboral*/
var editIndexLAB = undefined;
var lastIndexLAB;

$('#dgLAB').datagrid({
    onClickRow: function(rowIndex) {
        if (lastIndexLAB != rowIndex) {
            $(this).datagrid('endEdit', lastIndexLAB);
            $(this).datagrid('beginEdit', rowIndex);
        }
        lastIndexLAB = rowIndex;
    }
});

function endEditingLAB() {
    if (editIndexLAB == undefined) {
        return true
    }
    if ($('#dgLAB').datagrid('validateRow', editIndexLAB)) {
        $('#dgLAB').datagrid('endEdit', editIndexLAB);
        editIndexLAB = undefined;
        return true;
    } else {
        return false;
    }
}

function onClickCellLAB(index, field) {
    if (editIndexLAB != index) {
        if (endEditingLAB()) {
            $('#dgLAB').datagrid('selectRow', index)
                .datagrid('beginEdit', index);
            editIndexLAB = index;
        } else {
            setTimeout(function() {
                $('#dgLAB').datagrid('selectRow', editIndexLAB);
            }, 0);
        }
    }
}

function appendLAB() {
    if (endEditingLAB()) {
        $('#dgLAB').datagrid('appendRow', {
            empresa: $('#txtEmpresa').val(),
            cargo: $('#txtCargo').val(),
            funciones: $('#txtFunciones').val(),
            periodo: $('#txtPeriodo').val()
        });
        editIndexLAB = $('#dgLAB').datagrid('getRows').length;
        $('#dgLAB').datagrid('selectRow', editIndexLAB).datagrid('beginEdit', editIndexLAB);
        /*Usa los ID de EasyUI para borrar los TextBox*/
        $('#_easyui_textbox_input9').val(''); //txtEmpresa
        $('#_easyui_textbox_input10').val(''); //txtCargo
        $('#_easyui_textbox_input11').val(''); //txtFunciones
        $('#_easyui_textbox_input12').val(''); //txtPeriodo
    }
}

function removeitLAB() {
    if (editIndexLAB == undefined) {
        return
    }
    $('#dgLAB').datagrid('cancelEdit', editIndexLAB)
        .datagrid('deleteRow', editIndexLAB);
    editIndexLAB = undefined;
}

function acceptitLAB() {
    if (endEditingLAB()) {
        $('#dgLAB').datagrid('acceptChanges');
    }
}

function rejectLAB() {
    $('#dgLAB').datagrid('rejectChanges');
    editIndexLAB = undefined;
}

/*Grid de Experiencia Docente*/
var editIndexDOC = undefined;
var lastIndexDOC;

$('#dgDOC').datagrid({
    onClickRow: function(rowIndex) {
        if (lastIndexDOC != rowIndex) {
            $(this).datagrid('endEdit', lastIndexDOC);
            $(this).datagrid('beginEdit', rowIndex);
        }
        lastIndexDOC = rowIndex;
    }
});

function endEditingDOC() {
    if (editIndexDOC == undefined) {
        return true
    }
    if ($('#dgDOC').datagrid('validateRow', editIndexDOC)) {
        $('#dgDOC').datagrid('endEdit', editIndexDOC);
        editIndexDOC = undefined;
        return true;
    } else {
        return false;
    }
}

function onClickCellDOC(index, field) {
    if (editIndexDOC != index) {
        if (endEditingDOC()) {
            $('#dgDOC').datagrid('selectRow', index)
                .datagrid('beginEdit', index);
            editIndexDOC = index;
        } else {
            setTimeout(function() {
                $('#dgDOC').datagrid('selectRow', editIndexDOC);
            }, 0);
        }
    }
}

function appendDOC() {
    if (endEditingDOC()) {
        $('#dgDOC').datagrid('appendRow', {
            centro: $('#txtDocCentro').val(),
            clases: $('#txtDocClases').val(),
            periodo: $('#txtDocPeriodo').val()
        });
        editIndexDOC = $('#dgDOC').datagrid('getRows').length;
        $('#dgDOC').datagrid('selectRow', editIndexDOC).datagrid('beginEdit', editIndexDOC);
        /*Usa los ID de EasyUI para borrar los TextBox*/
        $('#_easyui_textbox_input13').val(''); //txtDocCentro
        $('#_easyui_textbox_input14').val(''); //txtDocClases
        $('#_easyui_textbox_input15').val(''); //txtDocPeriodo
    }
}

function removeitDOC() {
    if (editIndexDOC == undefined) {
        return
    }
    $('#dgDOC').datagrid('cancelEdit', editIndexDOC)
        .datagrid('deleteRow', editIndexDOC);
    editIndexDOC = undefined;
}

function acceptitDOC() {
    if (endEditingDOC()) {
        $('#dgDOC').datagrid('acceptChanges');
    }
}

function rejectDOC() {
    $('#dgDOC').datagrid('rejectChanges');
    editIndexDOC = undefined;
}

/*Grid de Referencias*/
var editIndexREF = undefined;
var lastIndexREF;

$('#dgREF').datagrid({
    onClickRow: function(rowIndex) {
        if (lastIndexREF != rowIndex) {
            $(this).datagrid('endEdit', lastIndexREF);
            $(this).datagrid('beginEdit', rowIndex);
        }
        lastIndexREF = rowIndex;
    }
});

function endEditingREF() {
    if (editIndexREF == undefined) {
        return true
    }
    if ($('#dgREF').datagrid('validateRow', editIndexREF)) {
        $('#dgREF').datagrid('endEdit', editIndexREF);
        editIndexREF = undefined;
        return true;
    } else {
        return false;
    }
}

function onClickCellREF(index, field) {
    if (editIndexREF != index) {
        if (endEditingREF()) {
            $('#dgREF').datagrid('selectRow', index)
                .datagrid('beginEdit', index);
            editIndexREF = index;
        } else {
            setTimeout(function() {
                $('#dgREF').datagrid('selectRow', editIndexREF);
            }, 0);
        }
    }
}

function appendREF() {
    if (endEditingREF()) {
        $('#dgREF').datagrid('appendRow', {
            nombre: $('#txtRefNombre').val(),
            ocupacion: $('#txtRefOcupacion').val(),
            telefono: $('#txtRefTelefono').val(),
            cedula: $('#txtRefCedula').val()
        });
        editIndexREF = $('#dgREF').datagrid('getRows').length;
        $('#dgREF').datagrid('selectRow', editIndexREF).datagrid('beginEdit', editIndexREF);
        /*Usa los ID de EasyUI para borrar los TextBox*/
        $('#_easyui_textbox_input16').val(''); //txtRefNombre
        $('#_easyui_textbox_input17').val(''); //txtRefOcupacion
        $('#_easyui_textbox_input18').val(''); //txtRefTelefono
        $('#_easyui_textbox_input19').val(''); //txtRefCedula
    }
}

function removeitREF() {
    if (editIndexREF == undefined) {
        return
    }
    $('#dgREF').datagrid('cancelEdit', editIndexREF)
        .datagrid('deleteRow', editIndexREF);
    editIndexREF = undefined;
}

function acceptitREF() {
    if (endEditingREF()) {
        $('#dgREF').datagrid('acceptChanges');
    }
}

function rejectREF() {
    $('#dgREF').datagrid('rejectChanges');
    editIndexREF = undefined;
}

/*Grid de Información adicional*/
var editIndexADI = undefined;
var lastIndexADI;

$('#dgADI').datagrid({
    onClickRow: function(rowIndex) {
        if (lastIndexADI != rowIndex) {
            $(this).datagrid('endEdit', lastIndexADI);
            $(this).datagrid('beginEdit', rowIndex);
        }
        lastIndexADI = rowIndex;
    }
});

function endEditingADI() {
    if (editIndexADI == undefined) {
        return true
    }
    if ($('#dgADI').datagrid('validateRow', editIndexADI)) {
        $('#dgADI').datagrid('endEdit', editIndexADI);
        editIndexADI = undefined;
        return true;
    } else {
        return false;
    }
}

function onClickCellADI(index, field) {
    if (editIndexADI != index) {
        if (endEditingADI()) {
            $('#dgADI').datagrid('selectRow', index)
                .datagrid('beginEdit', index);
            editIndexADI = index;
        } else {
            setTimeout(function() {
                $('#dgADI').datagrid('selectRow', editIndexADI);
            }, 0);
        }
    }
}

function appendADI() {
    if (endEditingADI()) {
        $('#dgADI').datagrid('appendRow', {
            informacion: $('#txtInformacion').val()
        });
        editIndexADI = $('#dgADI').datagrid('getRows').length;
        $('#dgADI').datagrid('selectRow', editIndexADI).datagrid('beginEdit', editIndexADI);
        /*Usa los ID de EasyUI para borrar los TextBox*/
        $('#_easyui_textbox_input20').val(''); //txtInformacion
    }
}

function removeitADI() {
    if (editIndexADI == undefined) {
        return
    }
    $('#dgADI').datagrid('cancelEdit', editIndexADI)
        .datagrid('deleteRow', editIndexADI);
    editIndexADI = undefined;
}

function acceptitADI() {
    if (endEditingADI()) {
        $('#dgADI').datagrid('acceptChanges');
    }
}

function rejectADI() {
    $('#dgADI').datagrid('rejectChanges');
    editIndexADI = undefined;
}

$('form').submit(function(e) {
    e.preventDefault();

    if (verificarFormulario() == true) {
        var texto;
        var datos;
        var registros;
        var i;
        var sinCursos = true;
        var sinAcademica = true;
        var sinEspecializacion = true;
        var sinLaboral = true;
        var sinDocente = true;
        var sinReferencias = true;
        var gridCursos = $('#dgCUR').datagrid('getData');
        var gridAcademica = $('#dgACA').datagrid('getData');
        var gridEspecializacion = $('#dgESP').datagrid('getData');
        var gridLaboral = $('#dgLAB').datagrid('getData');
        var gridDocente = $('#dgDOC').datagrid('getData');
        var gridReferencias = $('#dgREF').datagrid('getData');
        var gridAdicional = $('#dgADI').datagrid('getData');

        texto = '{"txtCodDocente":"' + document.getElementById("txtCodDocente").value + '", ';
        texto += '"cboUsuario":"' + document.getElementById("cboUsuario").value + '", ';
        texto += '"txtNombre":"' + document.getElementById("txtNombre").value + '", ';
        texto += '"txtCedula":"' + document.getElementById("txtCedula").value + '", ';
        texto += '"txtCorreo":"' + document.getElementById("txtCorreo").value + '", ';
        texto += '"txtTelefono":"' + document.getElementById("txtTelefono").value + '", ';
        texto += '"txtDireccion":"' + document.getElementById("txtDireccion").value + '", ';

        if (document.getElementById("optActivo1").checked)
            texto += '"optActivo":"0", ';
        else
            texto += '"optActivo":"1", ';

        /*CURSOS*/
        registros = $('#dgCUR').datagrid('getRows').length - 1;

        if (registros >= 0) {
            sinCursos = false;
            texto += '"gridCursos": [';
            for (i = 0; i <= registros; i++) {
                texto += '{"curso":"' + gridCursos.rows[i].curso;
                if (i == registros)
                    texto += '"}],';
                else
                    texto += '"},';
            }
        }

        /*ACADEMICA*/
        registros = $('#dgACA').datagrid('getRows').length - 1;

        if (registros >= 0) {
            sinAcademica = false;
            texto += '"gridAcademica": [';
            for (i = 0; i <= registros; i++) {
                texto += '{"grado":"' + gridAcademica.rows[i].grado + '", "titulo":"' + gridAcademica.rows[i]
                    .titulo + '", "centro":"' + gridAcademica.rows[i].centro + '", "anno":"' + gridAcademica
                    .rows[i].anno;

                if (i == registros)
                    texto += '"}],';
                else
                    texto += '"},';
            }
        } 
        else 
        {
            if (texto.slice(-1) == ',')
            {
                if (sinCursos == true)
                    texto = texto.substr(0, texto.length - 2) + '}'
                else
                    texto = texto.substr(0, texto.length - 1) + '}'
            }
        }

        /*ESPECIALIZACION*/
        registros = $('#dgESP').datagrid('getRows').length - 1;

        if (registros >= 0) {
            sinEspecializacion = false;
            texto += '"gridEspecializacion": [';
            for (i = 0; i <= registros; i++) {
                texto += '{"titulo":"' + gridEspecializacion.rows[i].titulo + '", "centro":"' +
                    gridEspecializacion.rows[i].centro + '", "anno":"' + gridEspecializacion.rows[i].anno;

                if (i == registros)
                    texto += '"}],';
                else
                    texto += '"},';
            }
        } 
        else 
        {
            if (texto.slice(-1) == ',')
            {
                if (sinAcademica == true)
                    texto = texto.substr(0, texto.length - 2) + '}'
                else
                    texto = texto.substr(0, texto.length - 1) + '}'
            }
        }

        /*LABORAL*/
        registros = $('#dgLAB').datagrid('getRows').length - 1;

        if (registros >= 0) {
            sinLaboral = false;
            texto += '"gridLaboral": [';
            for (i = 0; i <= registros; i++) {
                texto += '{"empresa":"' + gridLaboral.rows[i].empresa + '", "cargo":"' + gridLaboral.rows[i]
                    .cargo + '", "funciones":"' + gridLaboral.rows[i].funciones + '", "periodo":"' + gridLaboral
                    .rows[i].periodo;

                if (i == registros)
                    texto += '"}],';
                else
                    texto += '"},';
            }
        } 
        else 
        {
            if (texto.slice(-1) == ',')
            {
                if (sinEspecializacion == true)
                    texto = texto.substr(0, texto.length - 2) + '}'
                else
                    texto = texto.substr(0, texto.length - 1) + '}'
            }
        }

        /*DOCENTE*/
        registros = $('#dgDOC').datagrid('getRows').length - 1;

        if (registros >= 0) {
            sinDocente = false;
            texto += '"gridDocente": [';
            for (i = 0; i <= registros; i++) {
                texto += '{"centro":"' + gridDocente.rows[i].centro + '", "clases":"' + gridDocente.rows[i]
                    .clases + '", "periodo":"' + gridDocente.rows[i].periodo;

                if (i == registros)
                    texto += '"}],';
                else
                    texto += '"},';
            }
        }
        else
        {
            if (texto.slice(-1) == ',')
            {
                if (sinLaboral == true)
                    texto = texto.substr(0, texto.length - 2) + '}'
                else
                    texto = texto.substr(0, texto.length - 1) + '}'
            }
        }

        /*REFERENCIAS*/
        registros = $('#dgREF').datagrid('getRows').length - 1;

        if (registros >= 0) {
            sinReferencias = false;
            texto += '"gridReferencias": [';
            for (i = 0; i <= registros; i++) {
                texto += '{"nombre":"' + gridReferencias.rows[i].nombre + '", "ocupacion":"' + gridReferencias
                    .rows[i].ocupacion + '", "telefono":"' + gridReferencias.rows[i].telefono +
                    '", "cedula":"' + gridReferencias.rows[i].cedula;

                if (i == registros)
                    texto += '"}],';
                else
                    texto += '"},';
            }
        } 
        else 
        {
            if (texto.slice(-1) == ',')
            {
                if (sinDocente == true)
                    texto = texto.substr(0, texto.length - 2) + '}'
                else
                    texto = texto.substr(0, texto.length - 1) + '}'
            }
        }

        /*ADICIONAL*/
        registros = $('#dgADI').datagrid('getRows').length - 1;

        if (registros >= 0) {
            texto += '"gridAdicional": [';
            for (i = 0; i <= registros; i++) {
                texto += '{"informacion":"' + gridAdicional.rows[i].informacion;

                if (i == registros)
                    texto += '"}]}';
                else
                    texto += '"},';
            }
        } 
        else 
        {
            if (texto.slice(-1) == ',')
            {
                if (sinReferencias == true)
                    texto = texto.substr(0, texto.length - 2) + '}'
                else
                    texto = texto.substr(0, texto.length - 1) + '}'
            }
        }

        datos = JSON.parse(texto);

        $.ajax({
                url: 'catDocentes.php',
                type: 'post',
                data: datos,
                beforeSend: function() {
                    console.log(datos)
                }
            })
            .done(function() {
                location.href = "gridDocentes.php";
            })
            .fail(function() {
                console.log('Error')
            });
    }
});
</script>

<?php
function fxEscribeJsonCurso($Docente)
{
	if ($Docente == "")
		$nombreArchivo = "DC000000A.json";
	else
		$nombreArchivo = $Docente . "A.json";

	if (file_exists($nombreArchivo))
		unlink($nombreArchivo);
	
	//Escribe el Json
	$mDatos = fxDevuelveDetCurso($Docente);
	$numRegistros = $mDatos->rowCount();

	$archivo = fopen($nombreArchivo, "w");
	
	fwrite($archivo, "[" . PHP_EOL);
	
	for ($i = 1; $i <= $numRegistros; $i++)
	{
		$Fila = $mDatos->fetch();
		fwrite($archivo, "{");
		fwrite($archivo, '"curso":"' . rtrim($Fila['DESC_101']) . '"');

		if ($i == $numRegistros)
			fwrite($archivo, "}" . PHP_EOL);
		else
			fwrite($archivo, "}," . PHP_EOL);
	}
	fwrite($archivo, "]");
	fclose($archivo);
	
	return($nombreArchivo);
}

function fxEscribeJsonAcademico($Docente)
{
	if ($Docente == "")
		$nombreArchivo = "DC000000B.json";
	else
		$nombreArchivo = $Docente . "B.json";

	if (file_exists($nombreArchivo))
		unlink($nombreArchivo);
	
	//Escribe el Json
	$mDatos = fxDevuelveDetAcademica($Docente);
	$numRegistros = $mDatos->rowCount();

	$archivo = fopen($nombreArchivo, "w");
	
	fwrite($archivo, "[" . PHP_EOL);
	
	for ($i = 1; $i <= $numRegistros; $i++)
	{
		$Fila = $mDatos->fetch();
		fwrite($archivo, "{");
		fwrite($archivo, '"grado":"' . rtrim($Fila['GRADO_102']) . '", ');
		fwrite($archivo, '"titulo":"' . rtrim($Fila['TITULO_102']) . '", ');
		fwrite($archivo, '"centro":"' . rtrim($Fila['CENTRO_102']) . '", ');
		fwrite($archivo, '"anno":"' . rtrim($Fila['ANNO_102']) . '"');
		
		if ($i == $numRegistros)
			fwrite($archivo, "}" . PHP_EOL);
		else
			fwrite($archivo, "}," . PHP_EOL);
	}
	fwrite($archivo, "]");
	fclose($archivo);
	
	return($nombreArchivo);
}

function fxEscribeJsonEspecializacion($Docente)
{
	if ($Docente == "")
		$nombreArchivo = "DC000000C.json";
	else
		$nombreArchivo = $Docente . "C.json";

	if (file_exists($nombreArchivo))
		unlink($nombreArchivo);
	
	//Escribe el Json
	$mDatos = fxDevuelveDetEspecializacion($Docente);
	$numRegistros = $mDatos->rowCount();

	$archivo = fopen($nombreArchivo, "w");
	
	fwrite($archivo, "[" . PHP_EOL);
	
	for ($i = 1; $i <= $numRegistros; $i++)
	{
		$Fila = $mDatos->fetch();
		fwrite($archivo, "{");
		fwrite($archivo, '"titulo":"' . rtrim($Fila['TITULO_103']) . '", ');
		fwrite($archivo, '"centro":"' . rtrim($Fila['CENTRO_103']) . '", ');
		fwrite($archivo, '"anno":"' . rtrim($Fila['ANNO_103']) . '"');
		
		if ($i == $numRegistros)
			fwrite($archivo, "}" . PHP_EOL);
		else
			fwrite($archivo, "}," . PHP_EOL);
	}
	fwrite($archivo, "]");
	fclose($archivo);

	return($nombreArchivo);
}

function fxEscribeJsonLaboral($Docente)
{
	if ($Docente == "")
		$nombreArchivo = "DC000000D.json";
	else
		$nombreArchivo = $Docente . "D.json";

	if (file_exists($nombreArchivo))
		unlink($nombreArchivo);
	
	//Escribe el Json
	$mDatos = fxDevuelveDetLaboral($Docente);
	$numRegistros = $mDatos->rowCount();

	$archivo = fopen($nombreArchivo, "w");
	
	fwrite($archivo, "[" . PHP_EOL);
	
	for ($i = 1; $i <= $numRegistros; $i++)
	{
		$Fila = $mDatos->fetch();
		fwrite($archivo, "{");
		fwrite($archivo, '"empresa":"' . rtrim($Fila['EMPRESA_104']) . '", ');
		fwrite($archivo, '"cargo":"' . rtrim($Fila['CARGO_104']) . '", ');
		fwrite($archivo, '"funciones":"' . rtrim($Fila['FUNCIONES_104']) . '", ');
		fwrite($archivo, '"periodo":"' . rtrim($Fila['PERIODO_104']) . '"');
		
		if ($i == $numRegistros)
			fwrite($archivo, "}" . PHP_EOL);
		else
			fwrite($archivo, "}," . PHP_EOL);
	}
	fwrite($archivo, "]");
	fclose($archivo);

	return($nombreArchivo);
}

function fxEscribeJsonDocente($Docente)
{
	if ($Docente == "")
		$nombreArchivo = "DC000000E.json";
	else
		$nombreArchivo = $Docente . "E.json";

	if (file_exists($nombreArchivo))
		unlink($nombreArchivo);
	
	//Escribe el Json
	$mDatos = fxDevuelveDetDocente($Docente);
	$numRegistros = $mDatos->rowCount();

	$archivo = fopen($nombreArchivo, "w");
	
	fwrite($archivo, "[" . PHP_EOL);
	
	for ($i = 1; $i <= $numRegistros; $i++)
	{
		$Fila = $mDatos->fetch();
		fwrite($archivo, "{");
		fwrite($archivo, '"centro":"' . rtrim($Fila['CENTRO_105']) . '", ');
		fwrite($archivo, '"clases":"' . rtrim($Fila['CLASES_105']) . '", ');
		fwrite($archivo, '"periodo":"' . rtrim($Fila['PERIODO_105']) . '"');
		
		if ($i == $numRegistros)
			fwrite($archivo, "}" . PHP_EOL);
		else
			fwrite($archivo, "}," . PHP_EOL);
	}
	fwrite($archivo, "]");
	fclose($archivo);

	/* cerrar el resulset */
	$mDatos->closeCursor();
	
	return($nombreArchivo);
}

function fxEscribeJsonReferencias($Docente)
{
	if ($Docente == "")
		$nombreArchivo = "DC000000F.json";
	else
		$nombreArchivo = $Docente . "F.json";

	if (file_exists($nombreArchivo))
		unlink($nombreArchivo);
	
	//Escribe el Json
	$mDatos = fxDevuelveDetReferencias($Docente);
	$numRegistros = $mDatos->rowCount();

	$archivo = fopen($nombreArchivo, "w");
	
	fwrite($archivo, "[" . PHP_EOL);
	
	for ($i = 1; $i <= $numRegistros; $i++)
	{
		$Fila = $mDatos-fetch();
		fwrite($archivo, "{");
		fwrite($archivo, '"nombre":"' . rtrim($Fila['NOMBRE_106']) . '", ');
		fwrite($archivo, '"ocupacion":"' . rtrim($Fila['OCUPACION_106']) . '", ');
		fwrite($archivo, '"telefono":"' . rtrim($Fila['TELEFONO_106']) . '", ');
		fwrite($archivo, '"cedula":"' . rtrim($Fila['CEDULA_106']) . '"');
		
		if ($i == $numRegistros)
			fwrite($archivo, "}" . PHP_EOL);
		else
			fwrite($archivo, "}," . PHP_EOL);
	}
	fwrite($archivo, "]");
	fclose($archivo);

	return($nombreArchivo);
}

function fxEscribeJsonAdicional($Docente)
{
	if ($Docente == "")
		$nombreArchivo = "DC000000G.json";
	else
		$nombreArchivo = $Docente . "G.json";

	if (file_exists($nombreArchivo))
		unlink($nombreArchivo);
	
	//Escribe el Json
	$mDatos = fxDevuelveDetAdicional($Docente);
	$numRegistros = $mDatos->rowCount();

	$archivo = fopen($nombreArchivo, "w");
	
	fwrite($archivo, "[" . PHP_EOL);
	
	for ($i = 1; $i <= $numRegistros; $i++)
	{
		$Fila = $mDatos->fetch();
		fwrite($archivo, "{");
		fwrite($archivo, '"informacion":"' . rtrim($Fila['INFORMACION_107']) . '"');
		
		if ($i == $numRegistros)
			fwrite($archivo, "}" . PHP_EOL);
		else
			fwrite($archivo, "}," . PHP_EOL);
	}
	fwrite($archivo, "]");
	fclose($archivo);

	/* cerrar el resulset */
	$mDatos->closeCursor();
	
	return($nombreArchivo);
}
?>