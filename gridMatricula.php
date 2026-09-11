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
	require_once ("funciones/fxMatricula.php");
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\Exception;
	require 'PHPMailer/src/Exception.php';
	require 'PHPMailer/src/PHPMailer.php';
	require 'PHPMailer/src/SMTP.php';
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
		$PermisoUsuario = fxPermisoUsuario("procMatricula", $mbAgregar, $mbModificar, $mbBorrar, $mbAnular);
		
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
			if (isset($_POST["KDSA"]))
            {
				$msMatricula = trim($_POST["KDSA"]);
                fxEnviarCorreo($msMatricula);
				fxNotificacionMatricula($msMatricula, 0);
				fxAgregarBitacora($_SESSION["gsUsuario"], "KDSA030A", $msMatricula, "", "Notificacion");
				?><script>$.messager.alert('KDSA','Notificación enviada.','info');</script><?php
			}
			
			if (isset($_POST["mnOpcion"]) and isset($_POST["mnAnno"])){
				$mnOpcion = $_POST["mnOpcion"];
				$mnAnno = $_POST["mnAnno"];
			}
			else{
				$mnOpcion = 0;
				$mnAnno = 0;
			}
		?>
    	<div class="container">
        	<div id="DivContenido">
				<div id="lateral">
					<?php
						if ($mbAgregar == 1 or $Administrador == 1)
							echo('<label id="agregar" data-toggle="tooltip" data-placement="top" title="Agregar"><img src="imagenes/btnLateralAgregar.png" height="80%" style="cursor:pointer" /></label>');
						else
						echo('<label id="agregarDis" data-toggle="tooltip" data-placement="top" title="Agregar"><img src="imagenes/btnLateralAgregarDis.png" height="80%" style="cursor:default" /></label>');
							
						if ($mbModificar == 1 or $Administrador == 1)
						echo('<label id="editar" data-toggle="tooltip" data-placement="top" title="Editar"><img src="imagenes/btnLateralEditar.png" height="80%" style="cursor:pointer" /></label>');
						else
						echo('<label id="editarDis" data-toggle="tooltip" data-placement="top" title="Editar"><img src="imagenes/btnLateralEditarDis.png" height="80%" style="cursor:default" /></label>');

						echo('<label id="imprimir" data-toggle="tooltip" data-placement="top" title="Imprimir"><img src="imagenes/btnLateralImprimir.png" height="80%" style="cursor:pointer" /></label>');
						//echo('<label id="correo" data-toggle="tooltip" data-placement="top" title="Enviar correo"><img src="imagenes/btnLateralCorreo.png" height="80%" style="cursor:pointer" /></label>');
					?>
				</div>
				<div class="row">
					<div class="col-md-12">
						<?php
							if ($mbAgregar == 1 or $Administrador == 1)
								echo('<button id="append" type="button" class="btn btn-warning">Agregar</button>');
							else
								echo('<button id="appendDis" type="button" class="btn btn-warning" disabled>Agregar</button>');
								
							if ($mbModificar == 1 or $Administrador == 1)
								echo('<button id="edit" type="button" class="btn btn-warning">Editar</button>');
							else
								echo('<button id="editDis" type="button" class="btn btn-warning" disabled>Editar</button>');

							echo('<button id="print" type="button" class="btn btn-warning">Hoja de Matrícula</button>');
							//echo('<button id="mail" type="button" class="btn btn-warning">Enviar correo de confirmación</button>');
						?>

						<div style="float:right; margin-right:1%; display:inline-block">
							<?php
								if ($mnOpcion == 0)
									echo('<input type="radio" name="optFiltro" id="optFiltro1" onchange="fxCambiaOpcion()" checked>Filtrar por año &nbsp;');
								else
									echo('<input type="radio" name="optFiltro" id="optFiltro1" onchange="fxCambiaOpcion()">Filtrar por año &nbsp;');

								$msConsulta = "select distinct year(FECHA_030) as ANNO from KDSA030A order by year(FECHA_030) desc";
								$mDatos = $m_cnx_MySQL->prepare($msConsulta);
								$mDatos->execute();

								echo('<select style="background-color: white" id="cboAnno" name="cboAnno" onchange="fxCambiaOpcion()">');
								while ($mFila = $mDatos->fetch())
								{
									$Valor = trim($mFila["ANNO"]);
                                    $Texto = trim($mFila["ANNO"]);
									
									if ($mnAnno == 0)
									{
										$mnAnno = $Valor;
										echo("<option value='" . $Valor . "' selected>" . $Texto . "</option>");
									}
									else{
										if ($Valor == $mnAnno)
											echo("<option value='" . $Valor . "' selected>" . $Texto . "</option>");
										else
											echo("<option value='" . $Valor . "'>" . $Texto . "</option>");
									}
									
								}
								echo('</select> &nbsp;');
								
								if ($mnOpcion == 1)
									echo('<input type="radio" name="optFiltro" id="optFiltro2" onchange="fxCambiaOpcion()" checked>Todos los registros');
								else
									echo('<input type="radio" name="optFiltro" id="optFiltro2" onchange="fxCambiaOpcion()">Todos los registros');
							?>
						</div>
						<div class="table-responsive">
							<table id="grid" class="table table-condensed table-hover table-striped" data-selection="true" data-multi-select="false" data-row-select="true" data-keep-selection="true" style="font-size:small">
								<thead>
									<tr>
										<th data-column-id="MATRICULA_REL" data-order="desc" data-identifier="true" data-align="left" data-header-align="left" data-width="10%">Matrícula</th>
										<th data-column-id="ESTUDIANTE" data-order="desc" data-align="left" data-header-align="left" data-width="26%">Nombre del Estudiante</th>
										<th data-column-id="NOMBRE_020" data-align="left" data-header-align="left" data-width="40%">Curso matriculado</th>
										<th data-column-id="FECHA_030" data-align="center" data-header-align="center" data-width="15%">Fecha</th>
										<th data-column-id="ESTADO_030" data-align="center" data-header-align="center" data-width="9%">Estado</th>
									</tr>
								</thead>
								<tbody>
								<?php
									if ($mnOpcion == 0)
										$mDatos = fxDevuelveMatricula(1, "", $mnAnno);
									else
										$mDatos = fxDevuelveMatricula(1);

									while ($Fila = $mDatos->fetch())
									{
										echo ("<tr>");
										echo ("<td>" . $Fila["MATRICULA_REL"] . "</td>");
										echo ("<td>" . $Fila["ESTUDIANTE"] . "</td>");
										echo ("<td>" . $Fila["NOMBRE_020"] . "</td>");
										$fecha = date_create_from_format('Y-m-d', $Fila["FECHA_030"]);
										echo ("<td>" . date_format($fecha, 'd-m-Y') . "</td>");
										echo ("<td>" . $Fila["ESTADO_030"] . "</td>");
										echo ("</tr>");
									}
								}
								?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
            </div>
    	</div>
<?php }?>
<script src="bootstrap/lib/jquery-1.11.1.min.js"></script>
<script src="bootstrap/js/bootstrap.js"></script>
<script src="bootstrap/js/moderniz.2.8.1.js"></script>
<script src="bootstrap/dist/jquery.bootgrid.js"></script>
<script src="bootstrap/dist/jquery.bootgrid.fa.js"></script>
<script src="js/jquery.redirect.js"></script>
<script>
	$(function() {
		$(window).scroll(function() {
            var scroll = $(window).scrollTop();
            if (scroll >= 100) {
            $("#lateral").addClass("entra");
            } else {
            $("#lateral").removeClass("entra");
            }
        });

		function init() {
			$("#grid").bootgrid({
				formatters: {
					"link": function(column, row) {
						return "<a href=\"#\">" + column.id + ": " + row.id + "</a>";
					}
				},
				/*rowCount: [-1, 10, 50, 75]*/
				rowCount: [50, 75, 100]
			});
		}

		init();

		$("#append").on("click", function() {
			$.redirect("procMatricula.php", {mAccion: 0, mEstudiante: ''}, "POST");
		});

		$("#agregar").on("click", function() {
			$.redirect("procMatricula.php", {mAccion: 0, mEstudiante: ''}, "POST");
		});
		
		$("#edit").on("click", function() {
			if ($.trim($("#grid").bootgrid("getSelectedRows")) != "")
			{
				var msCodigo = $.trim($("#grid").bootgrid("getSelectedRows"));
				$.redirect("procMatricula.php", {mAccion: 0, mCodigo: msCodigo}, "POST");
			}
		});

		$("#editar").on("click", function() {
			if ($.trim($("#grid").bootgrid("getSelectedRows")) != "")
			{
				var msCodigo = $.trim($("#grid").bootgrid("getSelectedRows"));
				$.redirect("procMatricula.php", {mAccion: 0, mCodigo: msCodigo}, "POST");
			}
		});

		$("#print").on("click", function() {
			if ($.trim($("#grid").bootgrid("getSelectedRows")) != "")
			{
				var msCodigo = $.trim($("#grid").bootgrid("getSelectedRows"));
				$.redirect("repHojaMatricula.php", {KDSA: msCodigo}, "POST", "_blank");
			}
		});

		$("#imprimir").on("click", function() {
			if ($.trim($("#grid").bootgrid("getSelectedRows")) != "")
			{
				var msCodigo = $.trim($("#grid").bootgrid("getSelectedRows"));
				$.redirect("repHojaMatricula.php", {KDSA: msCodigo}, "POST", "_blank");
			}
		});

		$("#mail").on("click", function() {
			if ($.trim($("#grid").bootgrid("getSelectedRows")) != "")
			{
				var codMatricula = $.trim($("#grid").bootgrid("getSelectedRows"));
				$.redirect("gridMatricula.php", {KDSA: codMatricula}, "POST");
			}
        });

		$("#correo").on("click", function() {
			if ($.trim($("#grid").bootgrid("getSelectedRows")) != "")
			{
				var codMatricula = $.trim($("#grid").bootgrid("getSelectedRows"));
				$.redirect("gridMatricula.php", {KDSA: codMatricula}, "POST");
			}
        });
	});

	function fxCambiaOpcion(){
		var mnOpcion;
		var mnAnno;

		if (document.getElementById("optFiltro1").checked == true){
			mnOpcion = 0;
			mnAnno = $("#cboAnno").val();
		}
		else{
			mnOpcion = 1;
			mnAnno = 0;
		}

		$.redirect("gridMatricula.php", {mnOpcion: mnOpcion, mnAnno: mnAnno}, "POST");
	}
</script>
</body>
</html>

<?php
function fxEnviarCorreo($msMatricula)
{
	$email = new PHPMailer(TRUE);
	try 
	{
		$msConsulta = "select CONCAT_WS(' ',NOMBRES_010,APELLIDOS_010) as ESTUDIANTE, NOMBRE_020, fxDevuelveDias(KDSA030A.CURSO_REL) as DIAS,";
		$msConsulta .= "CONCAT_WS(' ', 'De',TIME_FORMAT(HORAINI_020,'%h:%i %p'),'a',TIME_FORMAT(HORAFIN_020,'%h:%i %p')) as HORARIO, CORREO_010 ";
		$msConsulta .= "from KDSA030A, KDSA020A, KDSA010A where KDSA030A.CURSO_REL = KDSA020A.CURSO_REL and KDSA030A.ESTUDIANTE_REL = KDSA010A.ESTUDIANTE_REL ";
		$msConsulta .= "and KDSA030A.MATRICULA_REL = ?";
		$m_cnx_MySQL = fxAbrirConexion();
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msMatricula]);
		$mFila = $mDatos->fetch();
		$msEstudiante = $mFila["ESTUDIANTE"];
		$msCurso = $mFila["NOMBRE_020"];
		$msDias = $mFila["DIAS"];
		$msHorario = $mFila["HORARIO"];
		$msCorreo = $mFila["CORREO_010"];

		$email_subject = utf8_decode("Solicitud de confirmación de Matrícula de KDSA");
		$email_message = "<html><head><meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>";
		$email_message .= "<title>" . utf8_decode(html_entity_decode("Matr&iacute;cula ")) . trim($msMatricula) . "</title></head><body>";
		$email_message .= "<img src='https://appAdmin.institutoinofe.com/imagenes/headerLogin.jpg' width='200px'>";
		$email_message .= "<h2>" . utf8_decode(html_entity_decode("Matr&iacute;cula ")) . trim($msMatricula) . "</h2>";
		$email_message .= utf8_decode(html_entity_decode("Usted ha recibido este correo porque realiz&oacute; una matr&iacute;cula en el Centro de Capacitaci&oacute;n KDSA")) . "<br><br>";
		$email_message .= "Estudiante: <strong>" . utf8_decode(html_entity_decode($msEstudiante)) . "</strong><br>";
		$email_message .= "Curso: <strong>" . utf8_decode(html_entity_decode($msCurso)) . "</strong><br>";
		$email_message .= utf8_decode(html_entity_decode("D&iacute;as de asistencia: ")) . "<strong>" . utf8_decode(html_entity_decode($msDias)) . "</strong><br>";
		$email_message .= "Horario: <strong>" . trim($msHorario) . "</strong><br><br>";
		$email_message .= utf8_decode(html_entity_decode("Lea las Condiciones generales de la matr&iacute;cula y confirme la misma "));
		//$email_message .= '<a href="https://demoAdmin.institutoinofe.com/frmCondicionesMatricula.php?KDSA=' . trim($msMatricula) . '">' . utf8_decode(html_entity_decode("aqu&iacute;")) . '</a>';
		$email_message .= '<a href="https://appAdmin.institutoinofe.com/frmCondicionesMatricula.php?KDSA=' . trim($msMatricula) . '">' . utf8_decode(html_entity_decode("aqu&iacute;")) . '</a>';
		$email_message .= "<h3><em>No responda este correo</em></h3>";
		$email_message .= "</body></html>";
		$email->setLanguage('es');
		$email->isSMTP();
		$email->Host = 'single-6020.banahosting.com';
		$email->Port = 587;
		$email->SMTPAuth = true;
		$email->SMTPSecure = 'tls';
		$email->Username = 'notificaciones@institutoinofe.com';
		$email->Password = 'LGkPoyGF]sig';
		$email->setFrom('notificaciones@institutoinofe.com', 'Notificaciones INOFE');
		//$email->addAddress($msCorreo, $msEstudiante);
		$email->addAddress('luishansel@yahoo.com', 'Luis Hansel Vallecillo G.');
		$email->Subject = $email_subject;
		$email->isHTML(TRUE);
		$email->Body = $email_message;
		$email->send();

		//fxNotificacionMatricula($msMatricula);
	}
	catch (Exception $e)
	{
		echo $e->errorMessage();
	}
	catch (Exception $e)
	{
		echo $e->getMessage();
	}	
}
?>