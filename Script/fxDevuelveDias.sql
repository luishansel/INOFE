/*La funcion devuelve los Días de Clase del Curso*/
CREATE FUNCTION fxDevuelveDias(psCURSO VARCHAR(10) CHARSET latin1) RETURNS VARCHAR(100) CHARSET latin1
BEGIN
	DECLARE mnDomingo TINYINT(1);
	DECLARE mnLunes TINYINT(1);
	DECLARE mnMartes TINYINT(1);
	DECLARE mnMiercoles TINYINT(1);
	DECLARE mnJueves TINYINT(1);
	DECLARE mnViernes TINYINT(1);
	DECLARE mnSabado TINYINT(1);
	DECLARE msDias VARCHAR(100);
	
	set mnDomingo = (SELECT DOMINGO_020 from KDSA020A where CURSO_REL = psCURSO);
	set mnLunes = (SELECT LUNES_020 from KDSA020A where CURSO_REL = psCURSO);
	set mnMartes = (SELECT MARTES_020 from KDSA020A where CURSO_REL = psCURSO);
	set mnMiercoles = (SELECT MIERCOLES_020 from KDSA020A where CURSO_REL = psCURSO);
	set mnJueves = (SELECT JUEVES_020 from KDSA020A where CURSO_REL = psCURSO);
	set mnViernes = (SELECT VIERNES_020 from KDSA020A where CURSO_REL = psCURSO);
	set mnSabado = (SELECT SABADO_020 from KDSA020A where CURSO_REL = psCURSO);
	
	set msDias = "";
	
	if mnDomingo = 1 then
		set msDias = "Domingo";
	end if;
		
	if mnLunes = 1 then
		if msDias = "" then
			set msDias = "Lunes";
		else
			set msDias = CONCAT(msDias, ", Lunes");
		end if;
	end if;
	
	if mnMartes = 1 then
		if msDias = "" then
			set msDias = "Martes";
		else
			set msDias = CONCAT(msDias, ", Martes");
		end if;
	end if;
	
	if mnMiercoles = 1 then
		if msDias = "" then
			set msDias = "Miércoles";
		else
			set msDias = CONCAT(msDias, ", Miércoles");
		end if;
	end if;
	
	if mnJueves = 1 then
		if msDias = "" then
			set msDias = "Jueves";
		else
			set msDias = CONCAT(msDias, ", Jueves");
		end if;
	end if;
	
	if mnViernes = 1 then
		if msDias = "" then
			set msDias = "Viernes";
		else
			set msDias = CONCAT(msDias, ", Viernes");
		end if;
	end if;
	
	if mnSabado = 1 then
		if msDias = "" then
			set msDias = "Sábado";
		else
			set msDias = CONCAT(msDias, ", Sábado");
		end if;
	end if;
	
	RETURN msDias;
END