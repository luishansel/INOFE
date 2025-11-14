<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
if (!isset($_SESSION["gnVerifica"]) or $_SESSION["gnVerifica"] != 1)
	{
		echo('<meta http-equiv="Refresh" content="0;url=index.php"/>');
		exit('');
	}
require_once ("funciones/fxGeneral.php");
require_once ("funciones/fxUsuarios.php");
require_once ("fpdf181/fpdf.php");

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
class PDF extends FPDF
{
	public $Nombres;
	public $Apellidos;
	public $Fecha;
	public $Sexo;
	public $Cedula;
	public $Edad;
	public $Domicilio;
	public $Direccion;
	public $Correo;
	public $Celular;
	public $Emergencia;
	public $Parentesco;
	public $Telefono;
	public $NivelAcademico;
	public $PostGrado;
	public $Maestria;
	public $LugarTrabajo;
	public $Puesto;
	public $TelEmpresa;
	public $NombreCurso;
	public $TipoEstudio;
	public $Turno;
	public $TipoAsistencia;
	public $Horario;
	public $FechaIni;
	public $DiasClase;
	public $Descuento;
	public $Motivo;
	public $Medio;
	public $PrimeraVez;
	public $FuenteIngreso;
	public $Estado;
	
	// Page header
	function Header()
	{
		// Logos
		$this->Image('imagenes/headerLogin.jpg',15,14,0,15);
		if ($this->Estado == 4) {$this->Image('imagenes/INOFE_Anulado.jpg',70,6,0,35);}
		// Title
		$this->Line(120,10,120,22);
		$this->SetFont('arial','B',15);
		$Titulo = utf8_decode('FICHA DE INSCRIPCION');
		$this->Text(132, 15, $Titulo);
		$this->SetFont('arial','',8);
		$Titulo = utf8_decode('eMail: info@institutoinofe.com');
		$this->Text(145, 20, $Titulo);

		$Linea = 28;
		
		$this->SetFillColor(0,100,255);
		
		$this->SetFont('arial','B',8);
		$this->SetTextColor(0,0,0);
		$this->Text(150, $Linea, utf8_decode('Fecha de Matrícula:'));
		$this->SetFont('arial','',8);
		$this->Text(180, $Linea, $this->Fecha);
		$this->Line(178, $Linea + 2,200,$Linea + 2);
		
		//DATOS PERSONALES
		$Linea += 5;
		$this->SetFont('arial','B',10);
		$this->SetTextColor(255,255,255);
		$this->SetXY(15,$Linea);
		$this->Cell(40,6,'DATOS PERSONALES',0,0,'L',true);
		
		$Linea += 10;
		$this->SetTextColor(0,0,0);
		$this->SetFont('arial','B',8);
		$this->Text(15, $Linea, 'Nombres:');
		$this->Line(30, $Linea + 2,100,$Linea + 2);
		$this->SetFont('arial','',8);
		$this->Text(30, $Linea, utf8_decode(html_entity_decode($this->Nombres)));
		
		$this->SetFont('arial','B',8);
		$this->Text(115, $Linea, 'Apellidos:');
		$this->Line(130, $Linea + 2,200,$Linea + 2);
		$this->SetFont('arial','',8);
		$this->Text(130, $Linea, utf8_decode(html_entity_decode($this->Apellidos)));
		
		$Linea += 6;
		$this->SetFont('arial','B',8);
		$this->Text(15, $Linea, 'Sexo:');
		$this->SetFont('arial','',8);

		if ($this->Sexo == "M")
		{
			$this->Image('imagenes/check.jpg',30,$Linea-3,0,4);
			$this->Text(36,$Linea,"Masculino");
			$this->Image('imagenes/uncheck.jpg',54,$Linea-3,0,4);
			$this->Text(60,$Linea,"Femenino");
		}
		else
		{
			$this->Image('imagenes/uncheck.jpg',30,$Linea-3,0,4);
			$this->Text(36,$Linea,"Masculino");
			$this->Image('imagenes/check.jpg',54,$Linea-3,0,4);
			$this->Text(60,$Linea,"Femenino");
		}
		
		$Linea += 6;
		$this->SetFont('arial','B',8);
		$this->Text(15, $Linea, utf8_decode("Cédula de identidad:"));
		$this->SetFont('arial','',8);
		$this->Text(45,$Linea,$this->Cedula);
		$this->Line(45, $Linea + 2,80,$Linea + 2);
		$this->SetFont('arial','B',8);
		$this->Text(85, $Linea, utf8_decode("Edad:"));
		$this->Line(94, $Linea + 2,108,$Linea + 2);
		$this->SetFont('arial','',8);
		$this->Text(94, $Linea, $this->Edad . utf8_decode(" años"));
		$this->SetFont('arial','B',8);
		$this->Text(115, $Linea, utf8_decode("Domicilio:"));
		$this->Line(130, $Linea + 2,200,$Linea + 2);
		$this->SetFont('arial','',8);
		$this->Text(130, $Linea, utf8_decode(html_entity_decode($this->Domicilio)));
		
		$Linea += 6;
		$this->SetFont('arial','B',8);
		$this->Text(15, $Linea, utf8_decode("Dirección:"));
		$this->Line(30, $Linea + 2,200,$Linea + 2);
		$this->SetFont('arial','',8);
		$this->Text(30, $Linea, utf8_decode(html_entity_decode($this->Direccion)));
		
		$Linea += 6;
		$this->SetFont('arial','B',8);
		$this->Text(15, $Linea, utf8_decode("Correo:"));
		$this->Line(30, $Linea + 2,100,$Linea + 2);
		$this->SetFont('arial','',8);
		$this->Text(30, $Linea, utf8_decode($this->Correo));
		$this->SetFont('arial','B',8);
		$this->Text(115, $Linea, utf8_decode("Celular:"));
		$this->Line(130, $Linea + 2,200,$Linea + 2);
		$this->SetFont('arial','',8);
		$this->Text(130, $Linea, utf8_decode($this->Celular));
		
		$Linea += 6;
		$this->SetFont('arial','B',8);
		$this->Text(15, $Linea, utf8_decode("En caso de emergencia:"));
		$this->Line(50, $Linea + 2,120,$Linea + 2);
		$this->SetFont('arial','',8);
		$this->Text(50, $Linea, utf8_decode(html_entity_decode($this->Emergencia)));
		$this->SetFont('arial','B',8);
		$this->Text(124, $Linea, utf8_decode("Parentesco:"));
		$this->Line(142, $Linea + 2,165,$Linea + 2);
		$this->SetFont('arial','',8);
		$this->Text(142, $Linea, utf8_decode(html_entity_decode($this->Parentesco)));
		$this->SetFont('arial','B',8);
		$this->Text(168, $Linea, utf8_decode("Teléfono:"));
		$this->Line(183, $Linea + 2,200,$Linea + 2);
		$this->SetFont('arial','',8);
		$this->Text(183, $Linea, utf8_decode($this->Telefono));
		
		//DATOS PROFESIONALES
		$Linea += 5;
		$this->SetFont('arial','B',10);
		$this->SetTextColor(255,255,255);
		$this->SetXY(15,$Linea);
		$this->Cell(46,6,'DATOS PROFESIONALES',0,0,'L',true);
		
		$Linea += 10;
		$this->SetTextColor(0,0,0);
		$this->SetFont('arial','B',8);
		$this->Text(15, $Linea, utf8_decode("Nivel académico:"));
		$this->Line(40, $Linea + 2,100,$Linea + 2);
		$this->SetFont('arial','',8);
		$this->Text(40, $Linea, utf8_decode(html_entity_decode($this->NivelAcademico)));
		
		if ($this->PostGrado == 0)
		{
			$this->SetFont('arial','B',8);
			$this->Image('imagenes/uncheck.jpg',110,$Linea-3,0,4);
			$this->Text(116,$Linea,"Post Grados");
		}
		else
		{
			$this->SetFont('arial','B',8);
			$this->Image('imagenes/check.jpg',110,$Linea-3,0,4);
			$this->Text(116,$Linea,"Post Grados");
		}
		
		if ($this->Maestria == 0)
		{
			$this->SetFont('arial','B',8);
			$this->Image('imagenes/uncheck.jpg',140,$Linea-3,0,4);
			$this->Text(146,$Linea,utf8_decode("Maestrías"));
		}
		else
		{
			$this->SetFont('arial','B',8);
			$this->Image('imagenes/check.jpg',140,$Linea-3,0,4);
			$this->Text(146,$Linea,utf8_decode("Maestrías"));
		}
		
		$Linea += 6;
		$this->SetFont('arial','B',8);
		$this->Text(15, $Linea, utf8_decode("Lugar de trabajo:"));
		$this->Line(40, $Linea + 2,100,$Linea + 2);
		$this->SetFont('arial','',8);
		$this->Text(40, $Linea, utf8_decode(html_entity_decode($this->LugarTrabajo)));
		$this->SetFont('arial','B',8);
		$this->Text(105, $Linea, utf8_decode("Puesto:"));
		$this->Line(117, $Linea + 2,148,$Linea + 2);
		$this->SetFont('arial','',8);
		$this->Text(117, $Linea, utf8_decode(html_entity_decode($this->Puesto)));
		$this->SetFont('arial','B',8);
		$this->Text(149, $Linea, utf8_decode("Teléfono de la empresa:"));
		$this->Line(183, $Linea + 2,200,$Linea + 2);
		$this->SetFont('arial','',8);
		$this->Text(183, $Linea, utf8_decode($this->TelEmpresa));
		
		//ESTUDIO AL QUE APLICA
		$Linea += 5;
		$this->SetFont('arial','B',10);
		$this->SetTextColor(255,255,255);
		$this->SetXY(15,$Linea);
		$this->Cell(47,6,'ESTUDIO AL QUE APLICA',0,0,'L',true);
		
		$Linea += 10;
		$this->SetTextColor(0,0,0);
		$this->SetFont('arial','B',8);
		$this->Text(15, $Linea, utf8_decode("Nombre:"));
		$this->Line(30, $Linea + 2,200,$Linea + 2);
		$this->SetFont('arial','',8);
		$this->Text(30, $Linea, utf8_decode(html_entity_decode($this->NombreCurso)));
		$Linea += 6;
		$this->SetFont('arial','B',8);
		$this->Text(15, $Linea, utf8_decode("Tipo de estudio:"));

		switch ($this->TipoEstudio)
		{
			case 0:
				$this->Image('imagenes/check.jpg',42,$Linea-3,0,4);
				$this->Text(48,$Linea,utf8_decode("Seminario"));
				$this->Image('imagenes/uncheck.jpg',67,$Linea-3,0,4);
				$this->Text(73,$Linea,utf8_decode("Curso"));
				$this->Image('imagenes/uncheck.jpg',88,$Linea-3,0,4);
				$this->Text(94,$Linea,utf8_decode("Carrera"));
				$this->Image('imagenes/uncheck.jpg',112,$Linea-3,0,4);
				$this->Text(118,$Linea,utf8_decode("Taller"));
				$this->Image('imagenes/uncheck.jpg',133,$Linea-3,0,4);
				$this->Text(139,$Linea,utf8_decode("Diplomado"));
				break;
			case 1:
				$this->Image('imagenes/uncheck.jpg',42,$Linea-3,0,4);
				$this->Text(48,$Linea,utf8_decode("Seminario"));
				$this->Image('imagenes/check.jpg',67,$Linea-3,0,4);
				$this->Text(73,$Linea,utf8_decode("Curso"));
				$this->Image('imagenes/uncheck.jpg',88,$Linea-3,0,4);
				$this->Text(94,$Linea,utf8_decode("Carrera"));
				$this->Image('imagenes/uncheck.jpg',112,$Linea-3,0,4);
				$this->Text(118,$Linea,utf8_decode("Taller"));
				$this->Image('imagenes/uncheck.jpg',133,$Linea-3,0,4);
				$this->Text(139,$Linea,utf8_decode("Diplomado"));
				break;
			case 2:
				$this->Image('imagenes/uncheck.jpg',42,$Linea-3,0,4);
				$this->Text(48,$Linea,utf8_decode("Seminario"));
				$this->Image('imagenes/uncheck.jpg',67,$Linea-3,0,4);
				$this->Text(73,$Linea,utf8_decode("Curso"));
				$this->Image('imagenes/check.jpg',88,$Linea-3,0,4);
				$this->Text(94,$Linea,utf8_decode("Carrera"));
				$this->Image('imagenes/uncheck.jpg',112,$Linea-3,0,4);
				$this->Text(118,$Linea,utf8_decode("Taller"));
				$this->Image('imagenes/uncheck.jpg',133,$Linea-3,0,4);
				$this->Text(139,$Linea,utf8_decode("Diplomado"));
				break;
			case 3:
				$this->Image('imagenes/uncheck.jpg',42,$Linea-3,0,4);
				$this->Text(48,$Linea,utf8_decode("Seminario"));
				$this->Image('imagenes/uncheck.jpg',67,$Linea-3,0,4);
				$this->Text(73,$Linea,utf8_decode("Curso"));
				$this->Image('imagenes/uncheck.jpg',88,$Linea-3,0,4);
				$this->Text(94,$Linea,utf8_decode("Carrera"));
				$this->Image('imagenes/check.jpg',112,$Linea-3,0,4);
				$this->Text(118,$Linea,utf8_decode("Taller"));
				$this->Image('imagenes/uncheck.jpg',133,$Linea-3,0,4);
				$this->Text(139,$Linea,utf8_decode("Diplomado"));
				break;
			case 4:
				$this->Image('imagenes/uncheck.jpg',42,$Linea-3,0,4);
				$this->Text(48,$Linea,utf8_decode("Seminario"));
				$this->Image('imagenes/uncheck.jpg',67,$Linea-3,0,4);
				$this->Text(73,$Linea,utf8_decode("Curso"));
				$this->Image('imagenes/uncheck.jpg',88,$Linea-3,0,4);
				$this->Text(94,$Linea,utf8_decode("Carrera"));
				$this->Image('imagenes/uncheck.jpg',112,$Linea-3,0,4);
				$this->Text(118,$Linea,utf8_decode("Taller"));
				$this->Image('imagenes/check.jpg',133,$Linea-3,0,4);
				$this->Text(139,$Linea,utf8_decode("Diplomado"));
				break;
		}
		
		$Linea += 6;
		$this->SetFont('arial','B',8);
		$this->Text(15, $Linea, utf8_decode("Turno de estudio:"));
		
		switch ($this->Turno)
		{
			case 0:
				$this->Image('imagenes/check.jpg',42,$Linea-3,0,4);
				$this->Text(48,$Linea,utf8_decode("Nocturno"));
				$this->Image('imagenes/uncheck.jpg',67,$Linea-3,0,4);
				$this->Text(73,$Linea,utf8_decode("Sabatino"));
				$this->Image('imagenes/uncheck.jpg',88,$Linea-3,0,4);
				$this->Text(94,$Linea,utf8_decode("Dominical"));
				$this->Image('imagenes/uncheck.jpg',112,$Linea-3,0,4);
				$this->Text(118,$Linea,utf8_decode("Matutino"));
				$this->Image('imagenes/uncheck.jpg',133,$Linea-3,0,4);
				$this->Text(139,$Linea,utf8_decode("Vespertino"));
				break;
			case 1:
				$this->Image('imagenes/uncheck.jpg',42,$Linea-3,0,4);
				$this->Text(48,$Linea,utf8_decode("Nocturno"));
				$this->Image('imagenes/check.jpg',67,$Linea-3,0,4);
				$this->Text(73,$Linea,utf8_decode("Sabatino"));
				$this->Image('imagenes/uncheck.jpg',88,$Linea-3,0,4);
				$this->Text(94,$Linea,utf8_decode("Dominical"));
				$this->Image('imagenes/uncheck.jpg',112,$Linea-3,0,4);
				$this->Text(118,$Linea,utf8_decode("Matutino"));
				$this->Image('imagenes/uncheck.jpg',133,$Linea-3,0,4);
				$this->Text(139,$Linea,utf8_decode("Vespertino"));
				break;
			case 2:
				$this->Image('imagenes/uncheck.jpg',42,$Linea-3,0,4);
				$this->Text(48,$Linea,utf8_decode("Nocturno"));
				$this->Image('imagenes/uncheck.jpg',67,$Linea-3,0,4);
				$this->Text(73,$Linea,utf8_decode("Sabatino"));
				$this->Image('imagenes/check.jpg',88,$Linea-3,0,4);
				$this->Text(94,$Linea,utf8_decode("Dominical"));
				$this->Image('imagenes/uncheck.jpg',112,$Linea-3,0,4);
				$this->Text(118,$Linea,utf8_decode("Matutino"));
				$this->Image('imagenes/uncheck.jpg',133,$Linea-3,0,4);
				$this->Text(139,$Linea,utf8_decode("Vespertino"));
				break;
			case 3:
				$this->Image('imagenes/uncheck.jpg',42,$Linea-3,0,4);
				$this->Text(48,$Linea,utf8_decode("Nocturno"));
				$this->Image('imagenes/uncheck.jpg',67,$Linea-3,0,4);
				$this->Text(73,$Linea,utf8_decode("Sabatino"));
				$this->Image('imagenes/uncheck.jpg',88,$Linea-3,0,4);
				$this->Text(94,$Linea,utf8_decode("Dominical"));
				$this->Image('imagenes/check.jpg',112,$Linea-3,0,4);
				$this->Text(118,$Linea,utf8_decode("Matutino"));
				$this->Image('imagenes/uncheck.jpg',133,$Linea-3,0,4);
				$this->Text(139,$Linea,utf8_decode("Vespertino"));
				break;
			case 4:
				$this->Image('imagenes/uncheck.jpg',42,$Linea-3,0,4);
				$this->Text(48,$Linea,utf8_decode("Nocturno"));
				$this->Image('imagenes/uncheck.jpg',67,$Linea-3,0,4);
				$this->Text(73,$Linea,utf8_decode("Sabatino"));
				$this->Image('imagenes/uncheck.jpg',88,$Linea-3,0,4);
				$this->Text(94,$Linea,utf8_decode("Dominical"));
				$this->Image('imagenes/uncheck.jpg',112,$Linea-3,0,4);
				$this->Text(118,$Linea,utf8_decode("Matutino"));
				$this->Image('imagenes/check.jpg',133,$Linea-3,0,4);
				$this->Text(139,$Linea,utf8_decode("Vespertino"));
				break;
		}
		
		$Linea += 6;
		$this->SetFont('arial','B',8);
		$this->Text(15, $Linea, utf8_decode("Tipo de asistencia:"));
		switch ($this->TipoAsistencia)
		{
			case 0:
				$this->Image('imagenes/check.jpg',42,$Linea-3,0,4);
				$this->Text(48,$Linea,utf8_decode("Presencial"));
				$this->Image('imagenes/uncheck.jpg',67,$Linea-3,0,4);
				$this->Text(73,$Linea,utf8_decode("Virtual"));
				$this->Image('imagenes/uncheck.jpg',88,$Linea-3,0,4);
				$this->Text(94,$Linea,utf8_decode("On-line"));
			break;

			case 1:
				$this->Image('imagenes/uncheck.jpg',42,$Linea-3,0,4);
				$this->Text(48,$Linea,utf8_decode("Presencial"));
				$this->Image('imagenes/check.jpg',67,$Linea-3,0,4);
				$this->Text(73,$Linea,utf8_decode("Virtual"));
				$this->Image('imagenes/uncheck.jpg',88,$Linea-3,0,4);
				$this->Text(94,$Linea,utf8_decode("On-line"));
			break;

			case 2:
				$this->Image('imagenes/uncheck.jpg',42,$Linea-3,0,4);
				$this->Text(48,$Linea,utf8_decode("Presencial"));
				$this->Image('imagenes/uncheck.jpg',67,$Linea-3,0,4);
				$this->Text(73,$Linea,utf8_decode("Virtual"));
				$this->Image('imagenes/check.jpg',88,$Linea-3,0,4);
				$this->Text(94,$Linea,utf8_decode("On-line"));
			break;
		}
		
		$Linea += 6;
		$this->SetFont('arial','B',8);
		$this->Text(15, $Linea, utf8_decode("Horario:"));
		$this->Line(30, $Linea + 2,60,$Linea + 2);
		$this->SetFont('arial','',8);
		$this->Text(30, $Linea, utf8_decode($this->Horario));
		$this->SetFont('arial','B',8);
		$this->Text(65, $Linea, utf8_decode("Fecha de inicio:"));
		$this->Line(88, $Linea + 2,112,$Linea + 2);
		$this->SetFont('arial','',8);
		$this->Text(90, $Linea, utf8_decode($this->FechaIni));
		$this->Text(120, $Linea, utf8_decode("Días de clases:"));
		$this->Line(141, $Linea + 2,187,$Linea + 2);
		$this->SetFont('arial','',8);
		$this->Text(141, $Linea, utf8_decode(html_entity_decode($this->DiasClase)));
		
		$Linea += 6;
		$this->SetFont('arial','B',8);
		$this->Text(15, $Linea, utf8_decode("Descuento:"));
		$this->Line(53, $Linea + 2,63,$Linea + 2);
		$this->SetFont('arial','',8);
		if ($this->Descuento == 0)
		{
			$this->Image('imagenes/uncheck.jpg',32,$Linea-3,0,4);
			$this->Text(38,$Linea,utf8_decode("Si"));
			$this->Image('imagenes/check.jpg',42,$Linea-3,0,4);
			$this->Text(48,$Linea,utf8_decode("No"));
			$this->Text(64, $Linea, "%");
		}
		else
		{
			$this->Image('imagenes/check.jpg',32,$Linea-3,0,4);
			$this->Text(38,$Linea,utf8_decode("Si"));
			$this->Image('imagenes/uncheck.jpg',42,$Linea-3,0,4);
			$this->Text(48,$Linea,utf8_decode("No"));
			$this->Text(54, $Linea, $this->Descuento);
			$this->Text(64, $Linea, "%");
		}
		$this->SetFont('arial','B',8);
		$this->Text(71, $Linea, utf8_decode("Motivo:"));
		$this->Line(83, $Linea + 2,140,$Linea + 2);
		$this->SetFont('arial','',8);
		$this->Text(83, $Linea, utf8_decode(html_entity_decode($this->Motivo)));
		$this->SetFont('arial','B',8);
		$this->Text(143, $Linea, utf8_decode("Firma de Autorizado:"));
		$this->Line(172, $Linea + 2,200,$Linea + 2);
		
		//VARIOS
		$Linea += 5;
		$this->SetFont('arial','B',10);
		$this->SetTextColor(255,255,255);
		$this->SetXY(15,$Linea);
		$this->Cell(16,6,'VARIOS',0,0,'L',true);
		
		$Linea += 10;
		$this->SetTextColor(0,0,0);
		$this->SetFont('arial','B',8);
		$this->Text(15, $Linea, utf8_decode("Medio por el cual se enteró del curso:"));
		$this->Line(68, $Linea + 2,120,$Linea + 2);
		$this->SetFont('arial','',8);
		$this->Text(68, $Linea, utf8_decode(html_entity_decode($this->Medio)));
		$this->SetFont('arial','B',8);
		$this->Text(122, $Linea, utf8_decode("Primera vez que estudia en INOFE:"));
		
		if ($this->PrimeraVez == 0)
		{
			$this->Image('imagenes/uncheck.jpg',170,$Linea-3,0,4);
			$this->Text(176,$Linea,utf8_decode("Si"));
			$this->Image('imagenes/check.jpg',182,$Linea-3,0,4);
			$this->Text(188,$Linea,utf8_decode("No"));
		}
		else
		{
			$this->Image('imagenes/check.jpg',170,$Linea-3,0,4);
			$this->Text(176,$Linea,utf8_decode("Si"));
			$this->Image('imagenes/uncheck.jpg',182,$Linea-3,0,4);
			$this->Text(188,$Linea,utf8_decode("No"));
		}
		
		$Linea += 6;
		$this->SetFont('arial','B',8);
		$this->Text(15, $Linea, utf8_decode("Fuente de ingreso para pagar el estudio:"));
		
		switch ($this->FuenteIngreso)
		{
			case 0:
				$this->Image('imagenes/check.jpg',73,$Linea-3,0,4);
				$this->Text(79,$Linea,utf8_decode("Propios"));
				$this->Image('imagenes/uncheck.jpg',94,$Linea-3,0,4);
				$this->Text(100,$Linea,utf8_decode("Empresa"));
				break;
			case 1:
				$this->Image('imagenes/uncheck.jpg',73,$Linea-3,0,4);
				$this->Text(79,$Linea,utf8_decode("Propios"));
				$this->Image('imagenes/check.jpg',94,$Linea-3,0,4);
				$this->Text(100,$Linea,utf8_decode("Empresa"));
				break;
/*LHVG 20240227
			case 2:
				$this->Image('imagenes/uncheck.jpg',73,$Linea-3,0,4);
				$this->Text(79,$Linea,utf8_decode("Propios"));
				$this->Image('imagenes/uncheck.jpg',94,$Linea-3,0,4);
				$this->Text(100,$Linea,utf8_decode("Empresa"));
				$this->Image('imagenes/check.jpg',116,$Linea-3,0,4);
				$this->Text(122,$Linea,utf8_decode("Papás"));
				$this->Image('imagenes/uncheck.jpg',135,$Linea-3,0,4);
				$this->Text(141,$Linea,utf8_decode("Familiares"));
				break;
			case 3:
				$this->Image('imagenes/uncheck.jpg',73,$Linea-3,0,4);
				$this->Text(79,$Linea,utf8_decode("Propios"));
				$this->Image('imagenes/uncheck.jpg',94,$Linea-3,0,4);
				$this->Text(100,$Linea,utf8_decode("Empresa"));
				$this->Image('imagenes/uncheck.jpg',116,$Linea-3,0,4);
				$this->Text(122,$Linea,utf8_decode("Papás"));
				$this->Image('imagenes/check.jpg',135,$Linea-3,0,4);
				$this->Text(141,$Linea,utf8_decode("Familiares"));
				break;
*/
		}
		
		//CONDICIONES GENERALES DE LA MATRICULA
		$Linea += 5;
		$this->SetFont('arial','B',10);
		$this->SetTextColor(255,255,255);
		$this->SetXY(15,$Linea);
		$this->Cell(85,6,'CONDICIONES GENERALES DE LA MATRICULA',0,0,'L',true);
		
		$Linea += 10;
		$this->SetTextColor(0,0,0);
		$this->SetFont('arial','',6);
		$Texto = "1.- Para ser certificado, el estudiante debe asistir al 80% de los encuentros de clases. El 20% de faltas permitidas se contarán tenga o no justificación el estudiante.";
		$this->Text(15, $Linea, utf8_decode($Texto));
		
		$Linea += 3;
		$Texto = "2.- Al no cancelar las cuotas (aranceles) de curso en las fechas indicadas en calendario de pago se le generará recargo diario del 0.33% sobre la base de un 10% mensual del valor principal de la cuota.";
		$this->Text(15, $Linea, utf8_decode($Texto));

		$Linea += 3;
		$Texto = "El máximo de recargo a cobrar será el 10% del principal, aunque el retraso exceda los 30 días.";
		$this->Text(15, $Linea, utf8_decode($Texto));
		
		$Linea += 3;
		$Texto = "3.- Las evaluaciones deben realizarse en las fechas establecidas, día final de modulo formativo.";
		$this->Text(15, $Linea, utf8_decode($Texto));
		
		$Linea += 3;
		$Texto = "4.- En ningún caso habrá devolución de dinero por pagos realizados.";
		$this->Text(15, $Linea, utf8_decode($Texto));
		
		$Linea += 3;
		$Texto = "5.- En caso de no completarse el número mínimo de participantes para dar inicio al curso o seminario, INOFE tendrá 45 días hábiles para reporgramar la fecha de inicio. De no lograrse el número";
		$this->Text(15, $Linea, utf8_decode($Texto));
		
		$Linea += 3;
		$Texto = "de participantes, se procederá a la devolución de aranceles pagados por los inscritos a la fecha.";
		$this->Text(15, $Linea, utf8_decode($Texto));
		
		$Linea += 3;
		$Texto = "6.- Los aranceles cancelados son válidos únicamente para el curso y horario inscrito en la presente matrícula. No aplica para traslados, reingreso, reubicación o situaciones similares. Por traslado";
		$this->Text(15, $Linea, utf8_decode($Texto));
		
		$Linea += 3;
		$Texto = "o reubicaciones deberá cancelar la suma de quince dólares (U$ 15.00)";
		$this->Text(15, $Linea, utf8_decode($Texto));
		
		$Linea += 3;
		$Texto = "7.- El estudiante deberá aprobar satisfactoriamente con notas superiores a 70 puntos todos los módulos del programa de estudio. En caso contrario no se extenderá certificado, cartas de egresado,";
		$this->Text(15, $Linea, utf8_decode($Texto));

		$Linea += 3;
		$Texto = "cartas de pasantía u otros similares.";
		$this->Text(15, $Linea, utf8_decode($Texto));
		
		$Linea += 3;
		$Texto = "8.- Para la entrega del certificado del curso, los alumnos deberán estar solventes con el 100% de los aranceles establecidos, así como aprobados con 70 puntos cada uno de los módulos";
		$this->Text(15, $Linea, utf8_decode($Texto));
		
		$Linea += 3;
		$Texto = "desarrollados, además de completo su expediente estudiantil (Cédula y soporte de nivel académico).";
		$this->Text(15, $Linea, utf8_decode($Texto));
		
		$Linea += 3;
		$Texto = "9.- Los certificados de participación del centro de estudio se entregarán el ultimo día de clases, una vez completado los requisitos del numeral 8. La entrega es personal al estudiante. No se";
		$this->Text(15, $Linea, utf8_decode($Texto));
		
		$Linea += 3;
		$Texto = "entregarán certificados a padres, madres, hermanos(as), parientes, esposos(as) o amistades. Toda certificación posterior deberá cancelar el arancel por tramite extemporáneo";
		$this->Text(15, $Linea, utf8_decode($Texto));
		
		$Linea += 3;
		$Texto = "10.- Los documentos adjuntos a la matrícula (Cédula y Diploma), deben de ser remitidos en formato de imagen (PNG o JPG) debidamente digitalizados al correo electrónico";
		$this->Text(15, $Linea, utf8_decode($Texto));
		$Linea += 3;
		$Texto = "registro@institutoinofe.com";
		$this->Text(15, $Linea, utf8_decode($Texto));

		$Linea += 3;
		$Texto = "11.- Prohibido ingresar al aula de clases con alimentos, niños o acompañantes.";
		$this->Text(15, $Linea, utf8_decode($Texto));

		$Linea += 3;
		$Texto = "12.- Se prohíbe fumar, ingerir licor u otras sustancias análogas, dentro de las instalaciones de INOFE.";
		$this->Text(15, $Linea, utf8_decode($Texto));
		
		$Linea += 3;
		$Texto = "13.- INOFE es una entidad privada inscrita bajo las leyes de la república para operar bajo la razón social relacionada. La formación técnica que impartimos en la modalidad de cursos libres no";
		$this->Text(15, $Linea, utf8_decode($Texto));

		$Linea += 3;
		$Texto = "está acreditada por INATEC. El estudiante recibirá exclusivamente certificación de estudios de parte INOFE.";
		$this->Text(15, $Linea, utf8_decode($Texto));

		$Linea += 3;
		$Texto = "14.- Alumnos inscritos en cursos de salud (Técnicas Básicas de Enfermería, Auxiliar de Farmacia o afines). Las formaciones que impartimos son de nivel básico. INOFE no extiende, ni tramita";
		$this->Text(15, $Linea, utf8_decode($Texto));

		$Linea += 3;
		$Texto = "código MINSA. El egresado de curso recibirá certificado de participación única y exclusivamente de INOFE. No extendemos certificados de notas en cursos relacionados a temas de salud.";
		$this->Text(15, $Linea, utf8_decode($Texto));

		$Linea += 3;
		$Texto = "15.- En los cursos con módulos de inyección por las vías parenterales, el alumno participa a entera voluntad, reconociendo y asumiendo los riesgos, liberando de responsabilidad a INOFE y al";
		$this->Text(15, $Linea, utf8_decode($Texto));

		$Linea += 3;
		$Texto = "profesional que imparte la temática. Cabe resaltar que este modulo es desarrollado bajo estrictas medidas de supervisión, cuido y vigilancia de un profesional de la Salud.";
		$this->Text(15, $Linea, utf8_decode($Texto));

		/* LHVG 20230707 Eliminación de la condición por solicitud de Lic. Humberto Cárdenas
		$this->SetFont('arial','B',6);
		$Linea += 3;
		$Texto = "14.- El uso de mascarilla es obligatorio. No asista a clases presenciales si presenta síntomas como gripe, tos, fiebre, afecciones respiratorias o similares a COVID-19. En tal caso puede";
		$this->Text(15, $Linea, utf8_decode($Texto));
		$Linea += 3;
		$Texto = "solicitar con un día de anticipación, el usuario y contraseña temporales para que reciba su clase desde casa.";
		$this->Text(15, $Linea, utf8_decode($Texto));*/
		
		$Linea += 3;
		$Texto = "16.- La fecha de finalización establecida para el curso es tentativa y podrá ser reprogramada ante cualquier eventualidad con el fin de cumplir el número de sesiones definidas para el curso.";
		$this->Text(15, $Linea, utf8_decode($Texto));

		$Linea += 3;
		$Texto = "17.- Las condiciones generales establecidas en la presente matricula, constituyen el mínimo de reglas en la relación entre la institución educativa y el estudiante; sin embargo, las situaciones no";
		$this->Text(15, $Linea, utf8_decode($Texto));

		$Linea +=3;
		$Texto = "previstas en los numerales que anteceden, es regulado por el reglamento institucional, al cual queda sujeto todo estudiante.";
		$this->Text(15, $Linea, utf8_decode($Texto));

		//$Linea += 18; LHVG 20200815
		$Linea += 10;
		$this->Line(50, $Linea + 2,100,$Linea + 2);
		$this->Line(110, $Linea + 2,160,$Linea + 2);
		$Linea += 6;
		$this->SetFont('arial','',8);
		$this->Text(60, $Linea, utf8_decode("Firma y sello de INOFE"));
		$this->Text(120, $Linea, utf8_decode("Firma del estudiante"));
	}
	// Page footer
	function Footer()
	{
		// Position at 1.5 cm from bottom
		$this->SetY(-15);
		// Arial italic 8
		$this->SetFont('Arial','I',8);
		// Page number
		$this->Cell(0,10,utf8_decode('Página ').$this->PageNo().'/{nb}',0,0,'L');
		$this->Cell(0,10,'Emitido: ' . date("d/m/Y h:i:s a") . '',0,0,'R');
	}
}

function DevuelveFecha($Fecha)
{
	$FechaDividida = explode("-", $Fecha);
	
	$Anno = $FechaDividida[0];
	$Mes = $FechaDividida[1];
	$Dia = $FechaDividida[2];
	
	switch ($Mes)
		{
			case "01":
				$NombreMes = "Ene";
				break;
			case "02":
				$NombreMes = "Feb";
				break;
			case "03":
				$NombreMes = "Mar";
				break;
			case "04":
				$NombreMes = "Abr";
				break;
			case "05":
				$NombreMes = "May";
				break;
			case "06":
				$NombreMes = "Jun";
				break;
			case "07":
				$NombreMes = "Jul";
				break;
			case "08":
				$NombreMes = "Ago";
				break;
			case "09":
				$NombreMes = "Sep";
				break;
			case "10":
				$NombreMes = "Oct";
				break;
			case "11":
				$NombreMes = "Nov";
				break;
			case "12":
				$NombreMes = "Dic";
				break;
		}
	return ($Dia . "-" . $NombreMes . "-" . $Anno);
}

function CalculaEdad($Fecha)
{
	//fecha actual
	$annoHoy=date("Y");
	$mesHoy=date("n");
	$diaHoy=date("j");

	//fecha de nacimiento
	$FechaDividida = explode("-", $Fecha);
	$annoNac = $FechaDividida[0];
	$mesNac = $FechaDividida[1];
	$diaNac = $FechaDividida[2];
	
	$edad= $annoHoy-$annoNac;

	if ($mesHoy < ($mesNac - 1))
		$edad -= 1;
		
	if (($mesNac - 1) == $mesHoy and $diaHoy < $diaNac)
		$edad -= 1;	
	
    return $edad;
}

$codMatricula = trim($_POST["KDSA"]);

//Obtención de datos
$msConsulta = "select MATRICULA_REL, FECHA_030, DESCUENTO_030, MOTIVO_030, MEDIO_030, PRIMERAVEZ_030, FUENTEINGRESO_030, ESTADO_030, NOMBRES_010, ";
$msConsulta .= "APELLIDOS_010, SEXO_010, CEDULA_010, FECHANAC_010, DOMICILIO_010, DIRECCION_010, CORREO_010, CELULAR_010, EMERGENCIA_010, PARENTESCO_010, ";
$msConsulta .= "TELEFONO_010, NIVELACADEMICO_010, POSTGRADO_010, MAESTRIA_010, LUGARTRABAJO_010, PUESTO_010, TELEFONOEMPRESA_010, NOMBRE_020, TIPO_020, ";
$msConsulta .= "TIPOASISTENCIA_020, TIPOASISTENCIA_030, TURNO_020, HORAINI_020, HORAFIN_020, FECHAINI_020, fxDevuelveDias(KDSA020A.CURSO_REL) as DIASCLASE ";
$msConsulta .= "from KDSA030A, KDSA020A, KDSA010A where KDSA030A.CURSO_REL = KDSA020A.CURSO_REL and ";
$msConsulta .= "KDSA030A.ESTUDIANTE_REL = KDSA010A.ESTUDIANTE_REL and MATRICULA_REL = ?";

$m_cnx_MySQL = fxAbrirConexion();
$mDatos = $m_cnx_MySQL->prepare($msConsulta);
$mDatos->execute([$codMatricula]);
$Registros = $mDatos->rowCount();
$Fila = $mDatos->fetch();
$NombreEstudiante = utf8_decode($Fila["NOMBRES_010"]);
$ApellidoEstudiante = utf8_decode($Fila["APELLIDOS_010"]);
$FechaMatricula = $Fila["FECHA_030"];
$Sexo = $Fila["SEXO_010"];
$Cedula = $Fila["CEDULA_010"];
$Edad = CalculaEdad($Fila["FECHANAC_010"]);
$Domicilio = $Fila["DOMICILIO_010"];
$Direccion = $Fila["DIRECCION_010"];
$Correo = $Fila["CORREO_010"];
$Celular = $Fila["CELULAR_010"];
$Emergencia = $Fila["EMERGENCIA_010"];
$Parentesco = $Fila["PARENTESCO_010"];
$Telefono = $Fila["TELEFONO_010"];
$NivelAcademico = $Fila["NIVELACADEMICO_010"];
$PostGrado = $Fila["POSTGRADO_010"];
$Maestria = $Fila["MAESTRIA_010"];
$LugarTrabajo = $Fila["LUGARTRABAJO_010"];
$Puesto = $Fila["PUESTO_010"];
$TelEmpresa = $Fila["TELEFONOEMPRESA_010"];
$TipoEstudio = $Fila["TIPO_020"];
$TipoAsistenciaCur = $Fila["TIPOASISTENCIA_020"];
switch ($TipoAsistenciaCur)
{
	case 0:
		$NombreCurso = $Fila["NOMBRE_020"] . " (Presencial)";
	break;

	case 1:
		$NombreCurso = $Fila["NOMBRE_020"] . " (Virtual)";
	break;

	case 2:
		$NombreCurso = $Fila["NOMBRE_020"] . " (On-line)";
}
$TipoAsistenciaMat = $Fila["TIPOASISTENCIA_030"];
$Turno = $Fila["TURNO_020"];
$HoraIni = date_create($Fila["HORAINI_020"]);
$HoraFin = date_create($Fila["HORAFIN_020"]);
$Horario = "De " . date_format($HoraIni, 'h:i a') . " a " . date_format($HoraFin, 'h:i a');
$FechaIni = $Fila["FECHAINI_020"];
$DiasClase = $Fila["DIASCLASE"];
$Descuento = $Fila["DESCUENTO_030"];
$Motivo = $Fila["MOTIVO_030"];
$Medio = $Fila["MEDIO_030"];
$PrimeraVez = $Fila["PRIMERAVEZ_030"];
$FuenteIngreso = $Fila["FUENTEINGRESO_030"];
$Estado = $Fila["ESTADO_030"];

$pdf = new PDF('P','mm','Letter','Hoja de Matrícula ' . trim($codMatricula));
$pdf->AliasNbPages();
$pdf->Nombres=$NombreEstudiante;
$pdf->Apellidos=$ApellidoEstudiante;
$pdf->Fecha=DevuelveFecha($FechaMatricula);
$pdf->Sexo=$Sexo;
$pdf->Cedula=$Cedula;
$pdf->Edad=$Edad;
$pdf->Domicilio=$Domicilio;
$pdf->Direccion=$Direccion;
$pdf->Correo=$Correo;
$pdf->Celular=$Celular;
$pdf->Emergencia=$Emergencia;
$pdf->Parentesco=$Parentesco;
$pdf->Telefono=$Telefono;
$pdf->NivelAcademico=$NivelAcademico;
$pdf->PostGrado=$PostGrado;
$pdf->Maestria=$Maestria;
$pdf->LugarTrabajo=$LugarTrabajo;
$pdf->Puesto=$Puesto;
$pdf->TelEmpresa=$TelEmpresa;
$pdf->NombreCurso=$NombreCurso;
$pdf->TipoEstudio=$TipoEstudio;
$pdf->TipoAsistencia=$TipoAsistenciaMat;
$pdf->Turno=$Turno;
$pdf->Horario=$Horario;
$pdf->FechaIni=DevuelveFecha($FechaIni);
$pdf->DiasClase=$DiasClase;
$pdf->Descuento=$Descuento;
$pdf->Motivo=$Motivo;
$pdf->Medio=$Medio;
$pdf->PrimeraVez=$PrimeraVez;
$pdf->FuenteIngreso=$FuenteIngreso;
$pdf->Estado=$Estado;

$pdf->AddPage();
$pdf->SetTextColor(0,0,0);
$pdf->SetFont('arial','',10);
$pdf->Output();
}
?>