create function fxNombreTabla(psTABLA VARCHAR(10) CHARSET latin1) RETURNS varchar(100) CHARSET latin1
BEGIN
	DECLARE msNombre VARCHAR(100);
	
	set msNombre = "";
	
	if psTABLA = "KDSA000A" then
		set msNombre = "Bitácora";
	end if;
	
	if psTABLA = "KDSA001A" then
		set msNombre = "Días feriados";
	end if;

	if psTABLA = "KDSA002A" then
		set msNombre = "Usuarios";
	end if;
	
	if psTABLA = "KDSA003A" then
		set msNombre = "Grupos de Usuarios";
	end if;
	
	if psTABLA = "KDSA010" or psTABLA = "KDSA010A" then
		set msNombre = "Estudiantes";
	end if;
	
	if psTABLA = "KDSA020A" then
		set msNombre = "Cursos";
	end if;
	
	if psTABLA = "KDSA030A" then
		set msNombre = "Matrícula";
	end if;
	
	if psTABLA = "KDSA040A" then
		set msNombre = "Pagos";
	end if;
	
	if psTABLA = "KDSA050A" then
		set msNombre = "Cobros";
	end if;
	
	if psTABLA = "KDSA051A" then
		set msNombre = "Cobros individuales";
	end if;
	
	if psTABLA = "KDSA052A" then
		set msNombre = "Cobros empresariales";
	end if;
	
	if psTABLA = "KDSA054A" then
		set msNombre = "Cobros INATEC";
	end if;
	
	if psTABLA = "KDSA060A" then
		set msNombre = "Prospectos";
	end if;
	
	if psTABLA = "KDSA070A" then
		set msNombre = "Cursos INATEC";
	end if;
	
	if psTABLA = "KDSA080A" then
		set msNombre = "Seguimiento de prospectos";
	end if;
	
	if psTABLA = "KDSA090A" then
		set msNombre = "Proformas";
	end if;
	
	if psTABLA = "KDSA100A" then
		set msNombre = "Docentes";
	end if;
	
	if psTABLA = "KDSA110A" then
		set msNombre = "Configuración de módulos";
	end if;
	
	if psTABLA = "KDSA120A" then
		set msNombre = "Planificación programática";
	end if;
	
	if psTABLA = "KDSA130A" then
		set msNombre = "Planificación de clases";
	end if;
	
	if psTABLA = "KDSA140A" then
		set msNombre = "Asistencia";
	end if;
	
	if psTABLA = "KDSA150A" then
		set msNombre = "Calificaciones";
	end if;
	
	if psTABLA = "KDSA160A" then
		set msNombre = "Regulación de asistencias";
	end if;
	
	if psTABLA = "KDSA170A" then
		set msNombre = "Certificación";
	end if;
	
	RETURN msNombre;
END