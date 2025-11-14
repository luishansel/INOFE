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
		$PermisoUsuario = fxPermisoUsuario("repPlanesClases", $mbAgregar, $mbModificar, $mbBorrar, $mbAnular);
		
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
		?>
    	<div class="container">
        	<div id="DivContenido">
                <div class = "row">
                    <div class="col-xs-12 col-md-11">
                        <div class="degradado"><strong>Planes de clases</strong></div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <button id="printCUR" type="button" class="btn btn-warning" onclick="Imprimir(0)" >Imprimir Curso</button>
                        <button id="printMOD" type="button" class="btn btn-warning" onclick="Imprimir(1)" >Imprimir Módulo</button>
                        
                        <div id="tabPlan" class="easyui-tabs tabs-narrow" style="width:100%;height:auto">
                            <div title="Activos" style="padding:10px">
                                <?php
                                    $nombreArchivo = fxEscribeJsonPrincipal(1);
                                ?>
                                <table id="dgACT" class="easyui-datagrid table" data-options="iconCls:'icon-edit', singleSelect:true, url:'<?php echo(rtrim($nombreArchivo)); ?>', method:'get'">
                                    <thead>
                                        <tr>
                                            <th data-options="field:'curso',width:'15%',align:'left'">Curso</th>
                                            <th data-options="field:'nombreCurso',width:'85%',align:'left'">Nombre del Curso</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                            <div title="Inactivos" style="padding:10px">
                                <?php
                                    $nombreArchivo = fxEscribeJsonPrincipal(0);
                                ?>
                                <table id="dgINA" class="easyui-datagrid table" data-options="iconCls:'icon-edit', singleSelect:true, url:'<?php echo(rtrim($nombreArchivo)); ?>', method:'get'">
                                    <thead>
                                        <tr>
                                            <th data-options="field:'curso',width:'15%',align:'left'">Curso</th>
                                            <th data-options="field:'nombreCurso',width:'85%',align:'left'">Nombre del Curso</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    	</div>
<?php }} ?>

<script>
    $(function(){
        $('#dgACT').datagrid({
            view: detailview,
            detailFormatter:function(index,row){
                return '<div style="padding:2px;position:relative;"><table id="detACT"></table></div>';
            },
            onExpandRow: function(index,row){
                var detalle = $(this).datagrid('getRowDetail',index).find('#detACT');
                detalle.datagrid({
                    url:'dataPlanesClases.php?KDSA='+row.curso,
                    singleSelect:true,
                    height:'auto',
                    loadMsg:'',
                    columns:[[
                        {field:'modulo',title:'Módulo', width:100},
                        {field:'nombreModulo',title:'Nombre del Módulo',width:500},
                        {field:'docente',title:'Docente',width:400}
                    ]],
                    onResize:function(){
                        $('#dgACT').datagrid('fixDetailRowHeight',index);
                    },
                    onLoadSuccess:function(){
                        setTimeout(function(){
                            $('#dgACT').datagrid('fixDetailRowHeight',index);
                        },0);
                    }
                });
                $('#dgACT').datagrid('fixDetailRowHeight',index);
            }
        });
    });

    $(function(){
        $('#dgINA').datagrid({
            view: detailview,
            detailFormatter:function(index,row){
                return '<div style="padding:2px;position:relative;"><table id="detINA"></table></div>';
            },
            onExpandRow: function(index,row){
                var detalle = $(this).datagrid('getRowDetail',index).find('#detINA');
                detalle.datagrid({
                    url:'dataPlanesClases.php?KDSA='+row.curso,
                    singleSelect:true,
                    height:'auto',
                    loadMsg:'',
                    columns:[[
                        {field:'modulo',title:'Módulo', width:100},
                        {field:'nombreModulo',title:'Nombre del Módulo',width:500},
                        {field:'docente',title:'Docente',width:400}
                    ]],
                    onResize:function(){
                        $('#dgINA').datagrid('fixDetailRowHeight',index);
                    },
                    onLoadSuccess:function(){
                        setTimeout(function(){
                            $('#dgINA').datagrid('fixDetailRowHeight',index);
                        },0);
                    }
                });
                $('#dgINA').datagrid('fixDetailRowHeight',index);
            }
        });
    });

    function Imprimir(mnTipo){
        var tab = $('#tabPlan').tabs('getSelected');
        var index = $('#tabPlan').tabs('getTabIndex',tab);
        var indice;
        var filaCur;
        var filaMod;
        var curso;
        var modulo;
        var detalle;

        if (mnTipo == 0){
            if (index == 0){
                filaCur = $('#dgACT').datagrid('getSelected');
                curso = filaCur.curso;
            }
            else{
                filaCur = $('#dgINA').datagrid('getSelected');
                curso = filaCur.curso;
            }
            modulo = '';
        }
        else{
            if (index == 0){
                filaCur = $('#dgACT').datagrid('getSelected');
                indice = $('#dgACT').datagrid('getRowIndex', filaCur);
                detalle = $('#dgACT').datagrid('getRowDetail', indice).find('#detACT');
                filaMod = detalle.datagrid('getSelected');
                modulo = filaMod.modulo;
            }
            else{
                filaCur = $('#dgINA').datagrid('getSelected');
                indice = $('#dgINA').datagrid('getRowIndex', filaCur);
                detalle = $('#dgINA').datagrid('getRowDetail', indice).find('#detINA');
                filaMod = detalle.datagrid('getSelected');
                modulo = filaMod.modulo;
            }
            curso = '';
        }

        $.redirect("repPlanesClases.php", {msCurso: curso, msModulo: modulo}, "POST", "_blank");
    }
</script>
</body>
</html>
<?php
    function fxEscribeJsonPrincipal($mnTipo)
    {
        if ($mnTipo == 0)
            $nombreArchivo = "PlanesClasesActivos.json";
        else
            $nombreArchivo = "PlanesClasesInactivos.json";

        if (file_exists($nombreArchivo))
            unlink($nombreArchivo);
        
        //Escribe el Json
        $msConsulta = "select distinct KDSA020A.CURSO_REL, concat(NOMBRE_020, ' (', CONVOCATORIA_020, '/G', GRUPO_020, '/', ";
        $msConsulta .= "(case TURNO_020 when 0 then 'Nocturno' when 1 then 'Sabatino' when 2 then 'Dominical' when 3 then 'Matutino' when 4 then 'Vespertino' end), ')') as NOMBRE_020 ";
        $msConsulta .= "from KDSA020A join KDSA021A on KDSA020A.CURSO_REL = KDSA021A.CURSO_REL where ACTIVO_020 = ?";
        $m_cnx_MySQL = fxAbrirConexion();
        $mDatos = $m_cnx_MySQL->prepare($msConsulta);
        $mDatos->execute([$mnTipo]);
        $numRegistros = $mDatos->rowCount();

        $archivo = fopen($nombreArchivo, "w");
        
        fwrite($archivo, "[" . PHP_EOL);
        
        for ($i = 1; $i <= $numRegistros; $i++)
        {
            $Fila = $mDatos->fetch();
            fwrite($archivo, "{");
            fwrite($archivo, '"curso":"' . rtrim($Fila['CURSO_REL']) . '", ');
            fwrite($archivo, '"nombreCurso":"' . rtrim($Fila['NOMBRE_020']) . '"');
            
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