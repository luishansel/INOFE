<?php
	session_start();
	if (!isset($_SESSION["gnVerifica"]) or $_SESSION["gnVerifica"] != 1)
	{
		echo('<meta http-equiv="Refresh" content="0;url=index.php">');
		exit('');
    }
	
	include ("MasterWeb.php");
	require_once ("funciones/fxGeneral.php");
    $m_cnx_MySQL = fxAbrirConexion();  
?>
    <style>
        thead{
            color: white;
            background-color: rgb(207, 85, 22);
            vertical-align: middle;
        }
    </style>
    <div class="container">
        <div id="DivContenido">
        	<div class = "row">
            	<div class="col-xs-12 col-md-12">
            		<div class="degradado">
                		<strong><?php echo($_SESSION["gsNombre"]) ?></strong>
                    </div>
                </div>
            </div>
            
        	<div class = "row">
            	<div class="col-xs-4 col-md-4">
                	<div class="divBotonInicio">
    				<a href="catCursos.php"><img src="imagenes/btnCurso.png" style="border-radius:5%" width="100%" /></a>
                    </div>
            	</div>
                <div class="col-xs-4 col-md-4">
                	<div class="divBotonInicio">
    				<a href="gridEstudiantes.php"><img src="imagenes/btnAlumno.png" style="border-radius:5%" width="100%" /></a>
                    </div>
            	</div>
                <div class="col-xs-4 col-md-4">
                	<div class="divBotonInicio">
    				<a href="procMatricula.php"><img src="imagenes/btnMatricula.png" style="border-radius:5%" width="100%" /></a>
                    </div>
            	</div>
            </div>
            <div class = "row">
                <div class="col-xs-4 col-md-4">
                	<div class="divBotonInicio">
    				<a href="frmHojaMatricula.php"><img src="imagenes/btnImpMatricula.png" style="border-radius:5%" width="100%" /></a>
                    </div>
            	</div>
                <div class="col-xs-4 col-md-4">
                	<div class="divBotonInicio">
    				<a href="gridPagos.php"><img src="imagenes/btnPagos.png" style="border-radius:5%" width="100%" /></a>
                    </div>
             	</div>
                <div class="col-xs-4 col-md-4">
                	<div class="divBotonInicio">
	    			<a href="frmEstadoCuentas.php"><img src="imagenes/btnEstCta.png" style="border-radius:5%" width="100%" /></a>
                    </div>
            	</div>
            </div>
            
            <div class = "row" style="margin-top:2%">
                <div class="col-xs-12 col-md-6">
                    <div class = "row">
                    <div class="col-xs-12 col-md-12">
                        <div class="degradado">
                            <strong>Cursos Activos</strong>
                        </div>
                    </div>
                    </div>
                    
                    <div class = "row">
                    <div class="col-12">
                        <table id="dgActivos" class="table table-striped" width="100%">
                        <thead>
                            <th width="40%">Curso</th>
                            <th style="text-align:center" width="20%">Inicio</th>
                            <th width="20%">Días</th>
                            <th width="20%">Horario</th>
                        </thead>
                        <?php
                            $msConsulta = "select NOMBRE_020, FECHAINI_020, fxDevuelveDias(KDSA020A.CURSO_REL) as DIASCLASE, HORAINI_020, HORAFIN_020 from KDSA020A where ACTIVO_020 = 1 and FECHAINI_020 <= date(NOW())";
                            $mDatos = $m_cnx_MySQL->prepare($msConsulta);
				            $mDatos->execute();
							while ($Fila = $mDatos->fetch())
							{
								echo ("<tr>");
								echo ("<td>" . $Fila["NOMBRE_020"] . "</td>");
								$fecha = date_create_from_format('Y-m-d', $Fila["FECHAINI_020"]);
								echo ("<td style='text-align:center'>" . date_format($fecha, 'd-m-Y') . "</td>");
                                echo ("<td>" . $Fila["DIASCLASE"] . "</td>");
                                $HoraIni = date_create($Fila["HORAINI_020"]);
                                $HoraFin = date_create($Fila["HORAFIN_020"]);
                                $Horario = "De " . date_format($HoraIni, 'h:i a') . " a " . date_format($HoraFin, 'h:i a');
								echo ("<td>" . trim($Horario) . "</td>");
								echo ("</tr>");
							}
                        ?>
                        </table>
                    </div>
                    </div>
                </div>
                
                <div class="col-xs-12 col-md-6">
                    <div class = "row">
                    <div class="col-xs-12 col-md-12">
                        <div class="degradado">
                            <strong>Próximos Cursos</strong>
                        </div>
                    </div>
                    </div>
                    
                    <div class = "row">
                    <div class="col-12">
                        <table id="dgProximos" class="table table-striped" width="100%">
                        <thead>
                            <th width="40%">Curso</th>
                            <th style="text-align:center" width="20%">Inicio</th>
                            <th width="20%">Días</th>
                            <th width="20%">Horario</th>
                        </thead>
                        <?php
							$msConsulta = "select NOMBRE_020, FECHAINI_020, fxDevuelveDias(KDSA020A.CURSO_REL) as DIASCLASE, HORAINI_020, HORAFIN_020 from KDSA020A where ACTIVO_020 = 1 and FECHAINI_020 > date(NOW())";
                            $mDatos = $m_cnx_MySQL->prepare($msConsulta);
				            $mDatos->execute();
							while ($Fila = $mDatos->fetch())
							{
								echo ("<tr>");
								echo ("<td>" . $Fila["NOMBRE_020"] . "</td>");
								$fecha = date_create_from_format('Y-m-d', $Fila["FECHAINI_020"]);
								echo ("<td style='text-align:center'>" . date_format($fecha, 'd-m-Y') . "</td>");
								echo ("<td>" . $Fila["DIASCLASE"] . "</td>");
								$HoraIni = date_create($Fila["HORAINI_020"]);
                                $HoraFin = date_create($Fila["HORAFIN_020"]);
                                $Horario = "De " . date_format($HoraIni, 'h:i a') . " a " . date_format($HoraFin, 'h:i a');
								echo ("<td>" . trim($Horario) . "</td>");
								echo ("</tr>");
							}
                        ?>
                        </table>
                    </div>
                    </div>
                </div>
            </div>
    	</div>
    </div>
</body>
</html>