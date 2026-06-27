<?php
header("Expires: Sat, 1 Jul 2000 05:00:00 GMT"); // Fecha en el pasado
header('Cache-Control: no-store, no-cache, must-revalidate'); // HTTP/1.1
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');

//Sólo para efectos de depuración
set_time_limit (0);
?>
<!DOCTYPE html>
<html lang="ES-NI">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="description" content="Control Administrativo y Académico de INOFE."/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
<link rel="icon" href="imagenes/favicon.png" />
<link rel="stylesheet" type="text/css" href="css/bootstrap441.css" />
<link rel="stylesheet" type="text/css" href="css/bootstrap.css" />
<link rel="stylesheet" type="text/css" href="css/prmenu.css" />
<link rel="stylesheet" type="text/css" href="bootstrap/css/jquery.bootgrid.css" />
<link rel="stylesheet" type="text/css" href="css/easyui.css" />
<link rel="stylesheet" type="text/css" href="css/icon.css" />
<link rel="stylesheet" type="text/css" href="css/StyleINOFE.css"/>

<script src="js/jquery.min.js"></script>
<script src="js/jquery-1.9.1.min.js"></script>
<script src="js/prmenu.min.js"></script>
<script src="js/jquery.easyui.min.js"></script>
<script src="js/datagrid-detailview.js"></script>
<script src="js/jquery.redirect.js"></script>
<script src="bootstrap/js/bootstrap.bundle.js"></script>
<script src="bootstrap/js/bootstrap.js"></script>
<script src="bootstrap/js/moderniz.2.8.1.js"></script>

<script>
    $(document).ready(function(){
        $('#top-menu').prmenu(
            {
                "case": "capitalize",
                "linkbgcolor": "#cf5516",
                "linktextcolor": "#ffffff",
            }
        );
    });
</script>

<title>Aplicación web INOFE</title>
</head>

<body>
<div id="cabecera">
    <div class="container-fluid">
        <div class="row">
            <img src="imagenes/header.png" width="100%" />
        </div>
    
        <div class="row">
            <div class="col-md-9">
                <ul id="top-menu" style="z-index: 1;">
                    <li><a href="frmInicio.php" class="active">Inicio</a></li>
                    <li><a href="#">Catálogos</a>
                        <ul>
                            <li><a href="#">Académico »</a>
                                <ul>
                                    <li><a href="gridEstudiantes.php">Estudiantes</a></li>
                                    <li><a href="gridCursos.php">Cursos</a></li>
                                    <!--
                                    <li><a href="gridCursosInatec.php">Cursos INATEC</a></li>
                                    -->
                                    <li><a href="gridDocentes.php">Docentes</a></li>
                                </ul>
                            </li>
                            <li><a href="gridCobros.php">Cobros</a></li>
                            <li><a href="gridProspectos.php">Prospectos</a></li>
                        </ul>
                    </li>

                    <li><a href="#">Procesos</a>
                        <ul>
                            <li><a href="#">Financiero »</a>
                                <ul>
                                    <li><a href="procCobrosIndividuales.php">Cobros individuales</a></li>
                                    <li><a href="gridPagos.php">Pagos de los estudiantes</a></li>
                                    <li><a href="gridOtrosIngresos.php">Otros ingresos</a></li>
                                    <li><a href="gridCobrosEmpresa.php">Cobros empresariales</a></li>
                                    <li><a href="gridPagosEmpresa.php">Pagos empresariales</a></li>
                                    <li><a href="procCierreCaja.php">Cierre de caja</a></li>
                                    <!--
                                    <li><a href="gridCobrosInatec.php">Cobros INATEC</a></li>
                                    <li><a href="gridPagosInatec.php">Pagos INATEC</a></li>
                                    -->
                                </ul>
                            </li>
                            <li><a href="#">Docencia »</a>
                                <ul>
                                    <li><a href="gridPlanClase.php">Planificación de clases</a></li>
                                    <li><a href="gridAsistencia.php">Asistencias</a></li>
                                    <li><a href="gridCalificaciones.php">Calificaciones</a></li>
                                    <li><a href="gridIncidencias.php">Incidencias</a></li>
                                </ul>
                            </li>
                            <li><a href="#">Certificación »</a>
                                <ul>
                                    <li><a href="gridTomos.php">Administración de tomos</a></li>
                                    <li><a href="frmCertificacion.php">Control de certificaciones</a></li>
                                </ul>
                            </li>
                            <li><a href="gridMatricula.php">Matrícula</a></li>
                            <li><a href="gridEstadoMatricula.php">Estado de las matrículas</a></li>
                            <li><a href="gridProformas.php">Proformas</a></li>
                            <li><a href="gridSeguimiento.php">Seguimiento de prospectos</a></li>
                            <li><a href="frmRegulacionAsistencia.php">Regulación de asistencias</a></li>
			                <li><a href="frmCertificacion.php">Certificación</a></li>
                        </ul>
                    </li>

                    <li><a href="#">Reportes y Consultas</a>
                        <ul>
                            <li><a href="#">Académico »</a>
                                <ul>
                                    <li><a href="frmHojaMatricula.php">Hoja de matrícula</a></li>
                                    <li><a href="frmMatriculados.php">Estudiantes matriculados</a></li>
                                    <li><a href="frmMatPeriodo.php">Matriculados por período</a></li>
                                    <li><a href="frmAsistencia.php">Asistencia estudiantil</a></li>
                                    <li><a href="frmCalificaciones.php">Calificaciones</a></li>
                                    <li><a href="frmPlanesClases.php">Planes de Clases</a></li>
                                    <li><a href="consCursos.php">Cursos activos</a></li>
                                    <li><a href="frmAsistenciaSemanal.php">Asistencia por período</a></li>
                                    <li><a href="frmMatGeneral.php">Matrícula general</a></li>
                                    <li><a href="frmAlumnoActivo.php">Constancia de alumno activo</a></li>
                                    <li><a href="frmEstadoMatricula.php">Estado de las matrículas</a></li>
                                    <li><a href="frmDeserciones.php">Deserciones</a></li>
                                </ul>
                            </li>
                            <li><a href="#">Financiero »</a>
                                <ul>
                                    <li><a href="frmEstadoCuentas.php">Estado de cuentas del estudiante</a></li>
                                    <li><a href="frmIngresos.php">Ingresos</a></li>
                                    <li><a href="frmProyecciones.php">Proyecciones</a></li>
                                    <li><a href="frmCtasPorCobrar.php">Cuentas por cobrar</a></li>
                                    <li><a href="frmSolventes.php">Estudiantes solventes</a></li>
                                    <li><a href="frmProximosPagos.php">Próximos Pagos de Estudiantes</a></li>
                                    <li><a href="frmAsistenciaPeriodo.php">Asistencias de los Docentes</a></li>
                                    <li><a href="frmPagosDocentes.php">Soporte de Pagos a Docentes</a></li>
                                    <li><a href="frmCobrosCurso.php">Cobros de los cursos</a></li>
                                </ul>
                            </li>
                            <li><a href="#">Certificación »</a>
                                <ul>
                                    <li><a href="frmLibroActas.php">Libro de actas</a></li>
                                </ul>
                            </li>
                            <li><a href="frmBitacora.php">Bitácora</a></li>
                        </ul>
                    </li>

                    <li><a href="#">Herramientas</a>
                        <ul>
                            <li><a href="gridUsuarios.php">Usuarios</a></li>
                            <li><a href="gridGrupos.php">Grupos</a></li>
                            <li><a href="gridCfgModulo.php">Módulos de los Cursos</a></li>
                            <li><a href="gridFeriados.php">Días no hábiles</a></li>
                            <li><a href="hrrMatriculaEnLinea.php">Enlace para Matrícula en Linea</a></li>
                            <!--li><a href="hrrCobroIndividual.php">Ajuste de Cobros individuales</a></li-->
                            <li><a href="gridFirmas.php">Firmas para constancia de alumno</a></li>
                            <li><a href="hrrCobros.php">Envío masivo de cobros</a></li>
                            <li><a href="gridDocCurso.php">Documentos obligatorios de los cursos</a></li>
                        </ul>
                    </li>

                    <li><a href="index.php">Cerrar sesión</a></li>
                </ul>
            </div>
            <div class="col-md-3 text-right">
                <div style="display:inline-block; vertical-align:middle; margin-left:1%; margin-top:4%">
                    <img src="imagenes/user.png" width="90%" />
                </div>
                <div style="display:inline-block; vertical-align:middle; margin-top:3%; color: rgb(255, 255, 255)">
                    <?php echo($_SESSION["gsNombre"]) ?>
                </div>
            </div>
        </div>
	</div>
</div>