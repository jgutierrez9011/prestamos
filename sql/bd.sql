--
-- PostgreSQL database dump
--

-- Dumped from database version 17.4 (Debian 17.4-1.pgdg120+2)
-- Dumped by pg_dump version 17.4 (Debian 17.4-1.pgdg120+2)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
--SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: anular_y_eliminar_abono(integer, integer, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.anular_y_eliminar_abono(p_id_abono integer, p_usuario_anulo integer, p_motivo text) RETURNS void
    LANGUAGE plpgsql
    AS $$
DECLARE
    v_id_prestamo INTEGER;
    v_fecha_abono DATE;
    v_monto NUMERIC(10,2);
    v_es_prorroga BOOLEAN;
    v_pagos JSONB;
BEGIN
    -- Obtener datos del abono
    SELECT id_prestamo, fecha_abono, monto_abonado, es_prorroga
    INTO v_id_prestamo, v_fecha_abono, v_monto, v_es_prorroga
    FROM abono
    WHERE id_abono = p_id_abono;

    -- Guardar pagos afectados como JSONB
    SELECT jsonb_agg(
        jsonb_build_object(
            'id_pago', id_pago,
            'monto_aplicado', monto_aplicado
        )
    )
    INTO v_pagos
    FROM abono_cuota
    WHERE id_abono = p_id_abono;

    -- Revertir el saldo del préstamo
    UPDATE prestamo
    SET saldo = saldo + v_monto,
        fecha_modifico = CURRENT_TIMESTAMP,
        usuario_modifico = p_usuario_anulo
    WHERE id_prestamo = v_id_prestamo;

    -- Revertir cada cuota afectada
    UPDATE calendariopago c
    SET saldo_cuota = saldo_cuota + ac.monto_aplicado,
        estado = CASE
            WHEN saldo_cuota + ac.monto_aplicado > 0 THEN 'Pendiente'
            ELSE estado
        END,
        fecha_modifico = CURRENT_TIMESTAMP,
        usuario_modifico = p_usuario_anulo
    FROM abono_cuota ac
    WHERE ac.id_abono = p_id_abono AND ac.id_pago = c.id_pago;

    -- Guardar historial
    INSERT INTO abono_anulado (
        id_abono_original,
        id_prestamo,
        fecha_abono,
        monto_abonado,
        es_prorroga,
        usuario_anulo,
        motivo,
        pagos_afectados
    ) VALUES (
        p_id_abono,
        v_id_prestamo,
        v_fecha_abono,
        v_monto,
        v_es_prorroga,
        p_usuario_anulo,
        p_motivo,
        v_pagos
    );

    -- Eliminar registros de abono_cuota
    DELETE FROM abono_cuota WHERE id_abono = p_id_abono;

    -- Eliminar el abono
    DELETE FROM abono WHERE id_abono = p_id_abono;

END;
$$;


ALTER FUNCTION public.anular_y_eliminar_abono(p_id_abono integer, p_usuario_anulo integer, p_motivo text) OWNER TO postgres;

--
-- Name: fn_reporte_movimiento_por_cartera(date, date); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_reporte_movimiento_por_cartera(fechainicio date DEFAULT NULL::date, fechafin date DEFAULT NULL::date) RETURNS TABLE(idcartera integer, descripcion character varying, id_prestamo integer, cod_solicitud integer, montotal numeric, monto_abonado numeric, saldo_total_cartera numeric, interes_pendiente numeric)
    LANGUAGE plpgsql
    AS $$
BEGIN
    RETURN QUERY
    SELECT 
        a.idcartera,
        c.descripcion,
        b.id_prestamo,
        a.cod_solicitud,
        COALESCE(b.montotal, 0.00) AS montotal,
        COALESCE(e.total_abonado, 0.00) AS monto_abonado,
        COALESCE(b.montotal, 0.00) - COALESCE(e.total_abonado, 0.00) AS saldo_total_cartera,
        COALESCE(f.total_interes_pendiente, 0.00) AS interes_pendiente
    FROM 
        solicitudprestamo a 
    INNER JOIN 
        prestamo b ON a.id_solicitud = b.id_solicitud
    INNER JOIN 
        tblcatcartera c ON a.idcartera = c.idcartera
    LEFT JOIN (
        SELECT 
            ab.id_prestamo, 
            SUM(ac.monto_aplicado) AS total_abonado
        FROM 
            abono ab 
        INNER JOIN 
            abono_cuota ac ON ab.id_abono = ac.id_abono
        WHERE 
            (
			(fechainicio IS NULL OR ac.fecha_registro::date >= fechainicio)
            AND 
			(fechafin IS NULL OR ac.fecha_registro::date <= fechafin)
			)
        GROUP BY 
            ab.id_prestamo
    ) e ON b.id_prestamo = e.id_prestamo
    LEFT JOIN (
        SELECT 
            vp.id_prestamo, 
            SUM(vp.interes_pendiente) AS total_interes_pendiente 
        FROM 
            vista_interes_pendiente_cuotas vp
        WHERE 
            vp.estado_abono IN ('Abono parcial','Sin abonos')
            AND (
          (fechainicio IS NULL OR fechafin IS NULL)
          OR
          (vp.fecha_pago BETWEEN fechainicio AND fechafin)
          OR
          (vp.fecha_pago < fechainicio AND vp.interes_pendiente > 0)
        )
        GROUP BY 
            vp.id_prestamo
    ) f ON b.id_prestamo = f.id_prestamo
    WHERE 
        a.idestatus IN (3,5,6)
        /*AND COALESCE(b.montotal, 0.00) - COALESCE(e.total_abonado, 0.00) > 0
		AND (
		    (fechainicio IS NULL OR a.fecha_solicitud >= fechainicio)
            AND 
			(fechafin IS NULL OR a.fecha_solicitud<= fechafin)
			)*/
    ORDER BY 
        a.idcartera, 
        b.id_prestamo ASC;
END;
$$;


ALTER FUNCTION public.fn_reporte_movimiento_por_cartera(fechainicio date, fechafin date) OWNER TO postgres;

--
-- Name: insertar_mora_diaria(date); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.insertar_mora_diaria(fecha_param date DEFAULT NULL::date) RETURNS integer
    LANGUAGE plpgsql
    AS $$
DECLARE
    r RECORD;
    fecha_evaluar DATE := COALESCE(fecha_param, CURRENT_DATE);
    registros_insertados INTEGER := 0;
BEGIN
    FOR r IN
        SELECT c.id_pago AS id_cuota,
               c.id_prestamo,
               c.fecha_pago,
               c.monto_cuota,
               c.estado
        FROM calendariopago c
        WHERE c.estado = 'Pendiente'
          AND c.fecha_pago < fecha_evaluar
    LOOP
        BEGIN
            INSERT INTO mora_diaria (
                id_prestamo,
                id_cuota,
                fecha_pago,
                fecha,
                dias_mora,
                monto_mora,
                estado_cuota
            )
            VALUES (
                r.id_prestamo,
                r.id_cuota,
                r.fecha_pago,
                fecha_evaluar,
                fecha_evaluar - r.fecha_pago,
                r.monto_cuota,
                r.estado
            )
            ON CONFLICT (id_cuota, fecha) DO NOTHING;

            -- Contar sólo si se insertó
            IF FOUND THEN
                registros_insertados := registros_insertados + 1;
            END IF;

        EXCEPTION WHEN OTHERS THEN
            RAISE NOTICE 'Error procesando cuota %: %', r.id_cuota, SQLERRM;
        END;
    END LOOP;

    RETURN registros_insertados;
END;
$$;


ALTER FUNCTION public.insertar_mora_diaria(fecha_param date) OWNER TO postgres;

--
-- Name: registrar_abono_y_actualizar_cuotas(integer, numeric, integer, boolean, date); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.registrar_abono_y_actualizar_cuotas(p_id_prestamo integer, p_monto_abonado numeric, p_usuario_creo integer, p_es_prorroga boolean DEFAULT false, p_fecha_abono date DEFAULT CURRENT_DATE) RETURNS void
    LANGUAGE plpgsql SECURITY DEFINER
    AS $$
DECLARE
    v_saldo_restante NUMERIC(10,2) := p_monto_abonado;
    v_id_abono INTEGER;
    v_pago_pendiente RECORD;
    v_abono_aplicado NUMERIC(10,2);
    v_saldo_cuota NUMERIC(10,2);
	v_saldo_abono_cuota NUMERIC(10,2);
    v_fecha_actual TIMESTAMP WITH TIME ZONE := CURRENT_TIMESTAMP;
BEGIN
    -- Validación básica de parámetros
    IF p_monto_abonado <= 0 THEN
        RAISE EXCEPTION 'El monto abonado debe ser mayor que cero';
    END IF;
    
    -- Registrar el abono en la tabla abono con todos los campos
    INSERT INTO abono (id_prestamo, fecha_abono, monto_abonado, es_prorroga, usuario_creo, fecha_creo) 
	VALUES (p_id_prestamo,p_fecha_abono,p_monto_abonado,p_es_prorroga,p_usuario_creo,v_fecha_actual) RETURNING id_abono INTO v_id_abono;
    
    -- Actualizar el saldo del préstamo
    UPDATE prestamo 
    SET 
        saldo = saldo - p_monto_abonado,
        fecha_modifico = v_fecha_actual,
        usuario_modifico = p_usuario_creo
    WHERE id_prestamo = p_id_prestamo;
    
    -- Procesar abono contra las cuotas pendientes en orden cronológico
    FOR v_pago_pendiente IN 
        SELECT 
            id_pago, 
            monto_cuota, 
            estado,
            saldo
        FROM calendariopago
        WHERE id_prestamo = p_id_prestamo 
        AND estado IN ('Pendiente', 'Prorroga')
        ORDER BY fecha_pago
    LOOP
        -- Si ya no hay saldo restante del abono, salir del bucle
        EXIT WHEN v_saldo_restante <= 0;

		--Verificando si existen otros abonos aplicados a la cuota
		v_saldo_abono_cuota := (select coalesce(sum(monto_aplicado),0) abonos_a_cuota from abono_cuota where id_pago = v_pago_pendiente.id_pago);
        
        -- Calcular saldo pendiente de la cuota
        v_saldo_cuota := (v_pago_pendiente.monto_cuota - v_saldo_abono_cuota);
        
        -- Calcular cuánto se puede aplicar a esta cuota
        v_abono_aplicado := LEAST(v_saldo_restante, v_saldo_cuota);

        -- Actualizar el calendario de pagos
        UPDATE calendariopago
        SET 
            saldo_cuota = (monto_cuota - (v_abono_aplicado+v_saldo_abono_cuota)),
            estado = CASE 
                WHEN (monto_cuota - (v_abono_aplicado+v_saldo_abono_cuota)) <= 0 THEN 'Pagado'
                WHEN p_es_prorroga THEN 'Prorroga'
                ELSE estado
            END,
            fecha_modifico = v_fecha_actual,
            usuario_modifico = p_usuario_creo
        WHERE id_pago = v_pago_pendiente.id_pago;
        
        -- Registrar la relación entre abono y cuota (opcional, si se necesita tracking detallado)
        INSERT INTO abono_cuota (id_abono, id_pago, monto_aplicado) 
        VALUES (v_id_abono, v_pago_pendiente.id_pago, v_abono_aplicado);
        
        -- Reducir el saldo restante del abono
        v_saldo_restante := v_saldo_restante - v_abono_aplicado;
		
    END LOOP;
    
    -- Manejo de saldo restante (pago anticipado)
    IF v_saldo_restante > 0 THEN
        -- Opción 1: Crear un registro de pago anticipado
        -- Opción 2: Aplicar a la próxima cuota no vencida
        -- En este ejemplo, simplemente registramos un notice
        RAISE NOTICE 'Pago anticipado de % aplicado al préstamo %', v_saldo_restante, p_id_prestamo;
        
        -- Opcional: Podrías actualizar el préstamo con un campo de pago anticipado
        -- UPDATE prestamo SET pago_anticipado = pago_anticipado + v_saldo_restante
        -- WHERE id_prestamo = p_id_prestamo;
    END IF;
    
    -- Registrar operación en log si es necesario
    -- INSERT INTO log_operaciones (tipo, id_prestamo, monto, usuario, fecha)
    -- VALUES ('ABONO', p_id_prestamo, p_monto_abonado, p_usuario_creo, v_fecha_actual);
END;
$$;


ALTER FUNCTION public.registrar_abono_y_actualizar_cuotas(p_id_prestamo integer, p_monto_abonado numeric, p_usuario_creo integer, p_es_prorroga boolean, p_fecha_abono date) OWNER TO postgres;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: abono; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.abono (
    id_abono integer NOT NULL,
    id_prestamo integer NOT NULL,
    fecha_abono date DEFAULT CURRENT_DATE,
    monto_abonado numeric(10,2) NOT NULL,
    es_prorroga boolean DEFAULT false,
    usuario_creo integer,
    fecha_modifico timestamp with time zone,
    usuario_modifico integer,
    fecha_creo timestamp with time zone
);


ALTER TABLE public.abono OWNER TO postgres;

--
-- Name: abono_anulado; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.abono_anulado (
    id_anulacion integer NOT NULL,
    id_abono_original integer NOT NULL,
    id_prestamo integer NOT NULL,
    fecha_abono date,
    monto_abonado numeric(10,2),
    es_prorroga boolean,
    usuario_anulo integer,
    fecha_anulacion timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    motivo text,
    pagos_afectados jsonb
);


ALTER TABLE public.abono_anulado OWNER TO postgres;

--
-- Name: abono_anulado_id_anulacion_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.abono_anulado_id_anulacion_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.abono_anulado_id_anulacion_seq OWNER TO postgres;

--
-- Name: abono_anulado_id_anulacion_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.abono_anulado_id_anulacion_seq OWNED BY public.abono_anulado.id_anulacion;


--
-- Name: abono_cuota; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.abono_cuota (
    id_relacion integer NOT NULL,
    id_abono integer,
    id_pago integer,
    monto_aplicado numeric(10,2) NOT NULL,
    fecha_registro timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.abono_cuota OWNER TO postgres;

--
-- Name: abono_cuota_id_relacion_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.abono_cuota_id_relacion_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.abono_cuota_id_relacion_seq OWNER TO postgres;

--
-- Name: abono_cuota_id_relacion_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.abono_cuota_id_relacion_seq OWNED BY public.abono_cuota.id_relacion;


--
-- Name: abono_id_abono_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.abono_id_abono_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.abono_id_abono_seq OWNER TO postgres;

--
-- Name: abono_id_abono_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.abono_id_abono_seq OWNED BY public.abono.id_abono;


--
-- Name: calendariopago; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.calendariopago (
    id_pago integer NOT NULL,
    id_prestamo integer NOT NULL,
    fecha_pago date NOT NULL,
    monto_cuota numeric(10,2) NOT NULL,
    interes numeric(10,2) NOT NULL,
    principal numeric(10,2) NOT NULL,
    estado text DEFAULT 'Pendiente'::text,
    usuario_creo integer,
    fecha_modifico timestamp with time zone,
    usuario_modifico integer,
    saldo numeric(10,2),
    fecha_creo timestamp with time zone,
    saldo_cuota numeric(10,2),
    CONSTRAINT calendariopago_estado_check CHECK ((estado = ANY (ARRAY['Pendiente'::text, 'Pagado'::text, 'Prorroga'::text])))
);


ALTER TABLE public.calendariopago OWNER TO postgres;

--
-- Name: calendariopago_id_pago_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.calendariopago_id_pago_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.calendariopago_id_pago_seq OWNER TO postgres;

--
-- Name: calendariopago_id_pago_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.calendariopago_id_pago_seq OWNED BY public.calendariopago.id_pago;


--
-- Name: clientes; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.clientes (
    idcliente integer NOT NULL,
    cedula character varying(50) NOT NULL,
    nombre character varying(255) NOT NULL,
    telefono character varying(20) NOT NULL,
    estado_civil character varying(20) NOT NULL,
    actividad_economica text NOT NULL,
    direccion_domicilio text NOT NULL,
    tipo_vivienda character varying(20) NOT NULL,
    anos_habitar integer NOT NULL,
    direccion_negocio text NOT NULL,
    tipo_local character varying(20) NOT NULL,
    tiempo_operar integer NOT NULL,
    rubro character varying(20) NOT NULL,
    idcartera integer,
    fecha_creo timestamp with time zone,
    usuario_creo integer NOT NULL,
    fecha_modifico timestamp with time zone,
    usuario_modifico integer,
    CONSTRAINT clientes_anos_habitar_check CHECK ((anos_habitar >= 0))
);


ALTER TABLE public.clientes OWNER TO postgres;

--
-- Name: clientes_idcliente_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.clientes_idcliente_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.clientes_idcliente_seq OWNER TO postgres;

--
-- Name: clientes_idcliente_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.clientes_idcliente_seq OWNED BY public.clientes.idcliente;


--
-- Name: configuracion_costo_venta; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.configuracion_costo_venta (
    id_config integer NOT NULL,
    rubro character varying(50) NOT NULL,
    margen_venta numeric(5,2),
    tipo_calculo character varying(50) NOT NULL,
    descripcion text,
    activo boolean DEFAULT true
);


ALTER TABLE public.configuracion_costo_venta OWNER TO postgres;

--
-- Name: configuracion_costo_venta_id_config_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.configuracion_costo_venta_id_config_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.configuracion_costo_venta_id_config_seq OWNER TO postgres;

--
-- Name: configuracion_costo_venta_id_config_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.configuracion_costo_venta_id_config_seq OWNED BY public.configuracion_costo_venta.id_config;


--
-- Name: estatus_solicitud; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.estatus_solicitud (
    idestatus integer NOT NULL,
    nombre character varying(50) NOT NULL,
    descripcion text
);


ALTER TABLE public.estatus_solicitud OWNER TO postgres;

--
-- Name: estatus_solicitud_idestatus_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.estatus_solicitud_idestatus_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.estatus_solicitud_idestatus_seq OWNER TO postgres;

--
-- Name: estatus_solicitud_idestatus_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.estatus_solicitud_idestatus_seq OWNED BY public.estatus_solicitud.idestatus;


--
-- Name: garantia; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.garantia (
    id_garantia integer NOT NULL,
    id_solicitud integer NOT NULL,
    descripcion text NOT NULL,
    cantidad integer NOT NULL,
    marca character varying(50),
    color character varying(50),
    ubicacion text,
    valor_realizacion numeric(10,2) NOT NULL
);


ALTER TABLE public.garantia OWNER TO postgres;

--
-- Name: garantia_id_garantia_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.garantia_id_garantia_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.garantia_id_garantia_seq OWNER TO postgres;

--
-- Name: garantia_id_garantia_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.garantia_id_garantia_seq OWNED BY public.garantia.id_garantia;


--
-- Name: mora_diaria; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.mora_diaria (
    id_mora integer NOT NULL,
    id_prestamo integer NOT NULL,
    id_cuota integer NOT NULL,
    fecha_pago date NOT NULL,
    fecha date NOT NULL,
    dias_mora integer NOT NULL,
    monto_mora numeric(12,2) NOT NULL,
    estado_cuota character varying(20),
    fecha_creo timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.mora_diaria OWNER TO postgres;

--
-- Name: mora_diaria_id_mora_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.mora_diaria_id_mora_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.mora_diaria_id_mora_seq OWNER TO postgres;

--
-- Name: mora_diaria_id_mora_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.mora_diaria_id_mora_seq OWNED BY public.mora_diaria.id_mora;


--
-- Name: obligacionesfinancieras; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.obligacionesfinancieras (
    id_obligacion integer NOT NULL,
    id_solicitud integer NOT NULL,
    institucion character varying(100) NOT NULL,
    monto_inicial numeric(10,2) NOT NULL,
    saldo numeric(10,2) NOT NULL,
    cuota numeric(10,2) NOT NULL
);


ALTER TABLE public.obligacionesfinancieras OWNER TO postgres;

--
-- Name: obligacionesfinancieras_id_obligacion_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.obligacionesfinancieras_id_obligacion_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.obligacionesfinancieras_id_obligacion_seq OWNER TO postgres;

--
-- Name: obligacionesfinancieras_id_obligacion_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.obligacionesfinancieras_id_obligacion_seq OWNED BY public.obligacionesfinancieras.id_obligacion;


--
-- Name: prestamo; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.prestamo (
    id_prestamo integer NOT NULL,
    id_solicitud integer NOT NULL,
    monto_aprobado numeric(10,2) NOT NULL,
    interes numeric(5,2) NOT NULL,
    plazo double precision NOT NULL,
    fecha_aprobacion date DEFAULT CURRENT_DATE,
    saldo numeric(10,2) NOT NULL,
    fecha_primer_cuota date,
    comentario text,
    usuario_creo integer,
    fecha_modifico timestamp with time zone,
    usuario_modifico integer,
    monto_interes numeric(10,2),
    montotal numeric(10,2),
    frecuencia integer,
    modalidad character varying(20),
    monto_cuota numeric(10,2),
    interes_semanal numeric(10,2),
    fecha_desembolso date
);


ALTER TABLE public.prestamo OWNER TO postgres;

--
-- Name: prestamo_id_prestamo_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.prestamo_id_prestamo_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.prestamo_id_prestamo_seq OWNER TO postgres;

--
-- Name: prestamo_id_prestamo_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.prestamo_id_prestamo_seq OWNED BY public.prestamo.id_prestamo;


--
-- Name: prorroga; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.prorroga (
    id_prorroga integer NOT NULL,
    id_prestamo integer NOT NULL,
    fecha_prorroga date DEFAULT CURRENT_DATE,
    interes_pagado numeric(10,2) NOT NULL,
    nueva_fecha_pago date NOT NULL
);


ALTER TABLE public.prorroga OWNER TO postgres;

--
-- Name: prorroga_id_prorroga_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.prorroga_id_prorroga_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.prorroga_id_prorroga_seq OWNER TO postgres;

--
-- Name: prorroga_id_prorroga_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.prorroga_id_prorroga_seq OWNED BY public.prorroga.id_prorroga;


--
-- Name: solicitudprestamo; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.solicitudprestamo (
    id_solicitud integer NOT NULL,
    cod_solicitud integer NOT NULL,
    idcliente integer NOT NULL,
    actividad_economica text NOT NULL,
    direccion_negocio text NOT NULL,
    telefono character varying(20),
    tipo_local character varying(50),
    tiempo_operar character varying(50),
    rubro character varying(50),
    monto_solicitado numeric(10,2) NOT NULL,
    plazo_solicitado double precision NOT NULL,
    tasa numeric(5,2) NOT NULL,
    venta_promedio_bueno numeric(10,2) NOT NULL,
    venta_promedio_mediano numeric(10,2) NOT NULL,
    venta_promedio_bajo numeric(10,2) NOT NULL,
    promedio_venta numeric(10,2) NOT NULL,
    ventas_mensuales numeric(10,2) NOT NULL,
    otros_ingresos_negocio numeric(10,2),
    aportes_familiares numeric(10,2),
    otros_ingresos numeric(10,2),
    gasto_costo_venta numeric(10,2),
    gastos_negocio numeric(10,2),
    cuotas_credito numeric(10,2),
    gastos_familiares numeric(10,2),
    utilidad_final numeric(10,2),
    tipo_promedio character varying(20) NOT NULL,
    idcartera integer NOT NULL,
    idestatus integer NOT NULL,
    fecha_solicitud date DEFAULT CURRENT_DATE,
    fecha_creo timestamp with time zone,
    usuario_creo integer NOT NULL,
    fecha_modifico timestamp with time zone,
    usuario_modifico integer,
    tipo_cliente character varying(20),
    total_ingreso numeric(10,2),
    total_gasto numeric(10,2),
    costo_unitario numeric(10,2),
    precio_venta numeric(10,2),
    unidades_producidas numeric(10,2),
    CONSTRAINT solicitudprestamo_tipo_promedio_check CHECK (((tipo_promedio)::text = ANY (ARRAY[('Diario'::character varying)::text, ('Semanal'::character varying)::text])))
);


ALTER TABLE public.solicitudprestamo OWNER TO postgres;

--
-- Name: solicitudprestamo_id_solicitud_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.solicitudprestamo_id_solicitud_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.solicitudprestamo_id_solicitud_seq OWNER TO postgres;

--
-- Name: solicitudprestamo_id_solicitud_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.solicitudprestamo_id_solicitud_seq OWNED BY public.solicitudprestamo.id_solicitud;


--
-- Name: sucursales; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.sucursales (
    sucursal_id integer NOT NULL,
    nombre character varying(100) NOT NULL,
    direccion character varying(255) NOT NULL,
    telefono character varying(15),
    fecha_apertura timestamp with time zone NOT NULL
);


ALTER TABLE public.sucursales OWNER TO postgres;

--
-- Name: sucursales_sucursal_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.sucursales_sucursal_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.sucursales_sucursal_id_seq OWNER TO postgres;

--
-- Name: sucursales_sucursal_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.sucursales_sucursal_id_seq OWNED BY public.sucursales.sucursal_id;


--
-- Name: tblcatcartera; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.tblcatcartera (
    idcartera integer NOT NULL,
    descripcion character varying(255) NOT NULL,
    monto_maximo numeric(18,2) NOT NULL,
    monto_minimo numeric(18,2) NOT NULL,
    fecha_creacion timestamp with time zone,
    usuario_creo character varying(100) NOT NULL,
    fecha_modificacion timestamp with time zone,
    usuario_modifico character varying(100),
    estado boolean DEFAULT true NOT NULL
);


ALTER TABLE public.tblcatcartera OWNER TO postgres;

--
-- Name: tblcatcartera_idcartera_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.tblcatcartera_idcartera_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.tblcatcartera_idcartera_seq OWNER TO postgres;

--
-- Name: tblcatcartera_idcartera_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.tblcatcartera_idcartera_seq OWNED BY public.tblcatcartera.idcartera;


--
-- Name: tblcatformulariodetalle; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.tblcatformulariodetalle (
    idfrm integer,
    idfrmdetalle integer NOT NULL,
    strnombreelemento character varying(50),
    strtipotag character varying(50),
    bolestado boolean DEFAULT false
);


ALTER TABLE public.tblcatformulariodetalle OWNER TO postgres;

--
-- Name: tblcatformulariodetalle_idfrmdetalle_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.tblcatformulariodetalle_idfrmdetalle_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.tblcatformulariodetalle_idfrmdetalle_seq OWNER TO postgres;

--
-- Name: tblcatformulariodetalle_idfrmdetalle_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.tblcatformulariodetalle_idfrmdetalle_seq OWNED BY public.tblcatformulariodetalle.idfrmdetalle;


--
-- Name: tblcatformularios; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.tblcatformularios (
    idfrm integer NOT NULL,
    strformulario character varying(50),
    strnombreform character varying(50),
    bolestado boolean,
    strkeymenu character varying(50)
);


ALTER TABLE public.tblcatformularios OWNER TO postgres;

--
-- Name: tblcatformularios_idfrm_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.tblcatformularios_idfrm_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.tblcatformularios_idfrm_seq OWNER TO postgres;

--
-- Name: tblcatformularios_idfrm_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.tblcatformularios_idfrm_seq OWNED BY public.tblcatformularios.idfrm;


--
-- Name: tblcatmenu; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.tblcatmenu (
    intidmenu integer NOT NULL,
    strmenu character varying(200),
    strtipomenu character varying(50),
    strnivelmenu character varying(100),
    bolactivo boolean,
    strhref character varying(250),
    strclassicono character varying(250)
);


ALTER TABLE public.tblcatmenu OWNER TO postgres;

--
-- Name: tblcatmenu_intidmenu_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.tblcatmenu_intidmenu_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.tblcatmenu_intidmenu_seq OWNER TO postgres;

--
-- Name: tblcatmenu_intidmenu_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.tblcatmenu_intidmenu_seq OWNED BY public.tblcatmenu.intidmenu;


--
-- Name: tblcatmenuperfil; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.tblcatmenuperfil (
    intidmenuperfil integer NOT NULL,
    idperfil integer,
    intidmenu integer,
    bolactivo boolean
);


ALTER TABLE public.tblcatmenuperfil OWNER TO postgres;

--
-- Name: tblcatmenuperfil_intidmenuperfil_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.tblcatmenuperfil_intidmenuperfil_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.tblcatmenuperfil_intidmenuperfil_seq OWNER TO postgres;

--
-- Name: tblcatmenuperfil_intidmenuperfil_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.tblcatmenuperfil_intidmenuperfil_seq OWNED BY public.tblcatmenuperfil.intidmenuperfil;


--
-- Name: tblcatperfilusr; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.tblcatperfilusr (
    idperfil integer NOT NULL,
    strperfil character varying(50),
    bolactivo boolean
);


ALTER TABLE public.tblcatperfilusr OWNER TO postgres;

--
-- Name: tblcatperfilusr_idperfil_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.tblcatperfilusr_idperfil_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.tblcatperfilusr_idperfil_seq OWNER TO postgres;

--
-- Name: tblcatperfilusr_idperfil_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.tblcatperfilusr_idperfil_seq OWNED BY public.tblcatperfilusr.idperfil;


--
-- Name: tblcatperfilusrfrm; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.tblcatperfilusrfrm (
    idperfilusrfrm integer NOT NULL,
    idfrm integer,
    idperfil integer,
    bolactivo boolean
);


ALTER TABLE public.tblcatperfilusrfrm OWNER TO postgres;

--
-- Name: tblcatperfilusrfrm_idperfilusrfrm_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.tblcatperfilusrfrm_idperfilusrfrm_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.tblcatperfilusrfrm_idperfilusrfrm_seq OWNER TO postgres;

--
-- Name: tblcatperfilusrfrm_idperfilusrfrm_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.tblcatperfilusrfrm_idperfilusrfrm_seq OWNED BY public.tblcatperfilusrfrm.idperfilusrfrm;


--
-- Name: tblcatusuario; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.tblcatusuario (
    intid integer NOT NULL,
    strpnombre character varying(50) NOT NULL,
    strsnombre character varying(50),
    strpapellido character varying(50) NOT NULL,
    strsapellido character varying(50),
    strsexo character varying(50),
    strcorreo character varying(50) NOT NULL,
    stridentificacion character varying(20) NOT NULL,
    strdireccion text NOT NULL,
    strcontacto character varying(100) NOT NULL,
    strusuariocreo character varying(50) NOT NULL,
    datfechacreo timestamp without time zone NOT NULL,
    strusuariomodifico character varying(50),
    datfechamodifico timestamp without time zone,
    datfechabaja date,
    bolactivo boolean DEFAULT true,
    strusuario character varying(50),
    strpassword character varying(255) NOT NULL,
    intidperfil integer,
    idcartera integer,
    sucursal_id integer
);


ALTER TABLE public.tblcatusuario OWNER TO postgres;

--
-- Name: tblcatusuario_intid_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.tblcatusuario_intid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.tblcatusuario_intid_seq OWNER TO postgres;

--
-- Name: tblcatusuario_intid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.tblcatusuario_intid_seq OWNED BY public.tblcatusuario.intid;


--
-- Name: v_pagos; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.v_pagos (
    jsonb_agg jsonb
);


ALTER TABLE public.v_pagos OWNER TO postgres;

--
-- Name: vista_abonos_diarios_por_prestamo; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.vista_abonos_diarios_por_prestamo AS
 SELECT e.descripcion AS cartera,
    b.id_prestamo,
    (b.fecha_creo)::date AS fecha_abono,
    COALESCE(sum(b.monto_abonado), 0.00) AS total_abonado,
    COALESCE(sum(a.monto_cuota), 0.00) AS saldo_pendiente,
    COALESCE(sum(a.interes), 0.00) AS interes_colocado
   FROM ((((( SELECT calendariopago.id_prestamo,
            calendariopago.monto_cuota,
            calendariopago.interes
           FROM public.calendariopago
          WHERE (calendariopago.estado = 'Pendiente'::text)) a
     FULL JOIN public.abono b ON ((a.id_prestamo = b.id_prestamo)))
     LEFT JOIN public.prestamo c ON ((a.id_prestamo = c.id_prestamo)))
     LEFT JOIN public.solicitudprestamo d ON ((c.id_solicitud = d.id_solicitud)))
     LEFT JOIN public.tblcatcartera e ON ((d.idcartera = e.idcartera)))
  GROUP BY e.descripcion, b.id_prestamo, ((b.fecha_creo)::date)
  ORDER BY b.id_prestamo;


ALTER VIEW public.vista_abonos_diarios_por_prestamo OWNER TO postgres;

--
-- Name: vista_interes_pendiente_cuotas; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.vista_interes_pendiente_cuotas AS
 SELECT c.id_pago,
    c.id_prestamo,
    c.fecha_pago,
    c.monto_cuota,
    c.interes,
    COALESCE(sum(ac.monto_aplicado), (0)::numeric) AS total_abonado,
    (c.monto_cuota - COALESCE(sum(ac.monto_aplicado), (0)::numeric)) AS saldo_pendiente,
        CASE
            WHEN (COALESCE(sum(ac.monto_aplicado), (0)::numeric) = (0)::numeric) THEN c.interes
            WHEN (COALESCE(sum(ac.monto_aplicado), (0)::numeric) >= c.monto_cuota) THEN (0)::numeric
            ELSE round(((c.interes * (c.monto_cuota - COALESCE(sum(ac.monto_aplicado), (0)::numeric))) / c.monto_cuota), 2)
        END AS interes_pendiente,
        CASE
            WHEN (COALESCE(sum(ac.monto_aplicado), (0)::numeric) = (0)::numeric) THEN 'Sin abonos'::text
            WHEN (COALESCE(sum(ac.monto_aplicado), (0)::numeric) < c.monto_cuota) THEN 'Abono parcial'::text
            ELSE 'Completo'::text
        END AS estado_abono
   FROM (public.calendariopago c
     LEFT JOIN ( SELECT abono_cuota.id_pago,
            (abono_cuota.fecha_registro)::date AS fecha_registro,
            sum(abono_cuota.monto_aplicado) AS monto_aplicado
           FROM public.abono_cuota
          GROUP BY abono_cuota.id_pago, ((abono_cuota.fecha_registro)::date)) ac ON ((c.id_pago = ac.id_pago)))
  GROUP BY c.id_pago, c.id_prestamo, c.fecha_pago, c.monto_cuota, c.interes
  ORDER BY c.fecha_pago;


ALTER VIEW public.vista_interes_pendiente_cuotas OWNER TO postgres;

--
-- Name: vista_mora_por_cliente; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.vista_mora_por_cliente AS
 SELECT c.idcliente,
    c.nombre,
    sum(md.dias_mora) AS total_dias_mora,
    count(DISTINCT md.id_cuota) AS cuotas_en_mora,
    sum(md.monto_mora) AS monto_total_mora
   FROM (((public.mora_diaria md
     JOIN public.prestamo p ON ((p.id_prestamo = md.id_prestamo)))
     JOIN public.solicitudprestamo sp ON ((sp.id_solicitud = p.id_solicitud)))
     JOIN public.clientes c ON ((c.idcliente = sp.idcliente)))
  GROUP BY c.idcliente, c.nombre;


ALTER VIEW public.vista_mora_por_cliente OWNER TO postgres;

--
-- Name: vista_mora_por_prestamo; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.vista_mora_por_prestamo AS
 SELECT md.id_prestamo,
    sp.idcliente,
    sum(md.dias_mora) AS total_dias_mora,
    count(DISTINCT md.id_cuota) AS cuotas_en_mora,
    sum(md.monto_mora) AS monto_total_mora
   FROM ((public.mora_diaria md
     JOIN public.prestamo p ON ((p.id_prestamo = md.id_prestamo)))
     JOIN public.solicitudprestamo sp ON ((sp.id_solicitud = p.id_solicitud)))
  GROUP BY md.id_prestamo, sp.idcliente;


ALTER VIEW public.vista_mora_por_prestamo OWNER TO postgres;

--
-- Name: vw_vista_mora_por_cuota; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.vw_vista_mora_por_cuota AS
 SELECT a.id_pago,
    a.id_prestamo,
    a.fecha_pago,
    a.estado,
    a.monto_cuota,
    COALESCE(pagado.abonos_aplicados, 0.00) AS abonos_aplicados,
    (a.monto_cuota - COALESCE(pagado.abonos_aplicados, 0.00)) AS saldo_por_cuota,
        CASE
            WHEN ((a.fecha_pago < CURRENT_DATE) AND (COALESCE(pagado.abonos_aplicados, 0.00) < a.monto_cuota)) THEN 'Mora'::text
            ELSE a.estado
        END AS estatus
   FROM (public.calendariopago a
     LEFT JOIN ( SELECT abono_cuota.id_pago,
            sum(abono_cuota.monto_aplicado) AS abonos_aplicados
           FROM public.abono_cuota
          GROUP BY abono_cuota.id_pago) pagado ON ((a.id_pago = pagado.id_pago)));


ALTER VIEW public.vw_vista_mora_por_cuota OWNER TO postgres;

--
-- Name: abono id_abono; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.abono ALTER COLUMN id_abono SET DEFAULT nextval('public.abono_id_abono_seq'::regclass);


--
-- Name: abono_anulado id_anulacion; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.abono_anulado ALTER COLUMN id_anulacion SET DEFAULT nextval('public.abono_anulado_id_anulacion_seq'::regclass);


--
-- Name: abono_cuota id_relacion; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.abono_cuota ALTER COLUMN id_relacion SET DEFAULT nextval('public.abono_cuota_id_relacion_seq'::regclass);


--
-- Name: calendariopago id_pago; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.calendariopago ALTER COLUMN id_pago SET DEFAULT nextval('public.calendariopago_id_pago_seq'::regclass);


--
-- Name: clientes idcliente; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.clientes ALTER COLUMN idcliente SET DEFAULT nextval('public.clientes_idcliente_seq'::regclass);


--
-- Name: configuracion_costo_venta id_config; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.configuracion_costo_venta ALTER COLUMN id_config SET DEFAULT nextval('public.configuracion_costo_venta_id_config_seq'::regclass);


--
-- Name: estatus_solicitud idestatus; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.estatus_solicitud ALTER COLUMN idestatus SET DEFAULT nextval('public.estatus_solicitud_idestatus_seq'::regclass);


--
-- Name: garantia id_garantia; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.garantia ALTER COLUMN id_garantia SET DEFAULT nextval('public.garantia_id_garantia_seq'::regclass);


--
-- Name: mora_diaria id_mora; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mora_diaria ALTER COLUMN id_mora SET DEFAULT nextval('public.mora_diaria_id_mora_seq'::regclass);


--
-- Name: obligacionesfinancieras id_obligacion; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.obligacionesfinancieras ALTER COLUMN id_obligacion SET DEFAULT nextval('public.obligacionesfinancieras_id_obligacion_seq'::regclass);


--
-- Name: prestamo id_prestamo; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.prestamo ALTER COLUMN id_prestamo SET DEFAULT nextval('public.prestamo_id_prestamo_seq'::regclass);


--
-- Name: prorroga id_prorroga; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.prorroga ALTER COLUMN id_prorroga SET DEFAULT nextval('public.prorroga_id_prorroga_seq'::regclass);


--
-- Name: solicitudprestamo id_solicitud; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.solicitudprestamo ALTER COLUMN id_solicitud SET DEFAULT nextval('public.solicitudprestamo_id_solicitud_seq'::regclass);


--
-- Name: sucursales sucursal_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sucursales ALTER COLUMN sucursal_id SET DEFAULT nextval('public.sucursales_sucursal_id_seq'::regclass);


--
-- Name: tblcatcartera idcartera; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tblcatcartera ALTER COLUMN idcartera SET DEFAULT nextval('public.tblcatcartera_idcartera_seq'::regclass);


--
-- Name: tblcatformulariodetalle idfrmdetalle; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tblcatformulariodetalle ALTER COLUMN idfrmdetalle SET DEFAULT nextval('public.tblcatformulariodetalle_idfrmdetalle_seq'::regclass);


--
-- Name: tblcatformularios idfrm; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tblcatformularios ALTER COLUMN idfrm SET DEFAULT nextval('public.tblcatformularios_idfrm_seq'::regclass);


--
-- Name: tblcatmenu intidmenu; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tblcatmenu ALTER COLUMN intidmenu SET DEFAULT nextval('public.tblcatmenu_intidmenu_seq'::regclass);


--
-- Name: tblcatmenuperfil intidmenuperfil; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tblcatmenuperfil ALTER COLUMN intidmenuperfil SET DEFAULT nextval('public.tblcatmenuperfil_intidmenuperfil_seq'::regclass);


--
-- Name: tblcatperfilusr idperfil; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tblcatperfilusr ALTER COLUMN idperfil SET DEFAULT nextval('public.tblcatperfilusr_idperfil_seq'::regclass);


--
-- Name: tblcatperfilusrfrm idperfilusrfrm; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tblcatperfilusrfrm ALTER COLUMN idperfilusrfrm SET DEFAULT nextval('public.tblcatperfilusrfrm_idperfilusrfrm_seq'::regclass);


--
-- Name: tblcatusuario intid; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tblcatusuario ALTER COLUMN intid SET DEFAULT nextval('public.tblcatusuario_intid_seq'::regclass);


--
-- Data for Name: abono; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.abono (id_abono, id_prestamo, fecha_abono, monto_abonado, es_prorroga, usuario_creo, fecha_modifico, usuario_modifico, fecha_creo) FROM stdin;
2	27	2025-03-28	577.50	t	1	\N	\N	2025-03-27 18:00:00-06
3	27	2025-03-28	577.50	t	1	\N	\N	2025-03-27 18:00:00-06
4	27	2025-03-28	577.50	f	1	\N	\N	2025-03-27 18:00:00-06
5	27	2025-03-28	577.50	t	1	\N	\N	2025-03-27 18:00:00-06
6	27	2025-03-28	577.50	t	1	\N	\N	2025-03-27 18:00:00-06
7	27	2025-03-28	577.50	f	1	\N	\N	2025-03-27 18:00:00-06
8	41	2025-03-28	2400.00	f	1	\N	\N	2025-03-27 18:00:00-06
9	51	2025-03-29	875.00	f	5	\N	\N	2025-03-28 18:00:00-06
10	51	2025-03-29	875.00	f	5	\N	\N	2025-03-29 04:50:14.650799-06
11	51	2025-03-29	875.00	f	5	\N	\N	2025-03-29 05:00:41.671367-06
12	51	2025-03-29	250.00	t	5	\N	\N	2025-03-29 05:43:27.440192-06
13	51	2025-03-29	875.00	f	5	\N	\N	2025-03-29 05:49:12.221416-06
14	51	2025-03-29	875.00	f	5	\N	\N	2025-03-29 08:40:07.29246-06
15	52	2025-03-29	2125.00	f	5	\N	\N	2025-03-29 08:53:14.36971-06
16	52	2025-04-05	1500.00	t	5	\N	\N	2025-03-29 08:54:18.033165-06
17	53	2025-03-29	3100.00	f	5	\N	\N	2025-03-29 11:30:34.95578-06
18	53	2025-03-29	2000.00	f	5	\N	\N	2025-03-29 11:32:04.516259-06
19	53	2025-03-29	600.00	t	5	\N	\N	2025-03-29 11:42:04.738388-06
20	54	2025-04-03	1225.00	f	\N	\N	\N	\N
21	54	2025-04-03	1225.00	f	5	\N	\N	2025-04-03 22:36:43.506619-06
22	55	2025-04-04	175.00	f	\N	\N	\N	\N
23	55	2025-04-04	175.00	f	5	\N	\N	2025-04-04 08:29:36.828041-06
24	55	2025-04-04	175.00	f	5	\N	\N	2025-04-04 08:30:56.021318-06
25	56	2025-04-04	350.00	f	5	\N	\N	2025-04-04 08:33:30.832163-06
26	56	2025-04-04	350.00	f	5	\N	\N	2025-04-04 11:17:18.645055-06
27	56	2025-04-04	350.00	f	5	\N	\N	2025-04-04 11:19:26.414905-06
28	57	2025-04-08	3150.00	f	5	\N	\N	2025-04-08 09:39:04.355082-06
29	57	2025-04-08	3000.00	f	5	\N	\N	2025-04-08 09:41:00.726067-06
30	57	2025-04-08	150.00	f	5	\N	\N	2025-04-08 10:09:01.949028-06
31	57	2025-04-08	3150.00	f	5	\N	\N	2025-04-08 10:10:33.920263-06
32	57	2025-04-08	3150.00	f	5	\N	\N	2025-04-08 10:14:43.830061-06
33	57	2025-04-08	3000.00	f	5	\N	\N	2025-04-08 10:15:46.103263-06
34	57	2025-04-08	150.00	f	5	\N	\N	2025-04-08 10:16:26.398956-06
35	57	2025-04-08	3300.00	f	5	\N	\N	2025-04-08 10:22:47.255136-06
36	57	2025-04-08	6150.00	f	5	\N	\N	2025-04-08 10:26:44.057895-06
37	57	2025-04-08	150.00	f	5	\N	\N	2025-04-08 10:27:31.067893-06
38	59	2025-04-08	875.00	f	5	\N	\N	2025-04-08 10:31:12.858087-06
39	60	2025-04-08	700.00	f	5	\N	\N	2025-04-08 14:49:25.669493-06
40	60	2025-04-08	500.00	f	5	\N	\N	2025-04-08 14:51:50.728511-06
41	60	2025-04-08	200.00	f	5	\N	\N	2025-04-08 14:55:23.417293-06
42	60	2025-04-08	500.00	f	5	\N	\N	2025-04-08 14:57:53.103286-06
43	60	2025-04-08	200.00	f	5	\N	\N	2025-04-08 14:58:24.467594-06
44	60	2025-04-08	1400.00	f	5	\N	\N	2025-04-08 14:59:00.692244-06
45	60	2025-04-08	350.00	f	5	\N	\N	2025-04-08 15:25:46.847334-06
46	60	2025-04-08	700.00	f	5	\N	\N	2025-04-08 15:43:05.264-06
47	61	2025-04-08	175.00	f	5	\N	\N	2025-04-08 16:05:09.523173-06
48	61	2025-04-08	300.00	f	5	\N	\N	2025-04-08 16:06:28.404534-06
49	61	2025-04-08	50.00	f	5	\N	\N	2025-04-08 16:07:58.022575-06
50	61	2025-04-08	1000.00	f	5	\N	\N	2025-04-08 16:09:59.007467-06
51	54	2025-04-09	1225.00	f	5	\N	\N	2025-04-09 13:58:16.422736-06
52	69	2025-04-09	3150.00	f	5	\N	\N	2025-04-09 15:17:55.712068-06
53	69	2025-04-09	3000.00	f	5	\N	\N	2025-04-09 15:18:19.641706-06
54	69	2025-04-09	3300.00	f	5	\N	\N	2025-04-09 15:18:43.353094-06
55	70	2025-04-12	700.00	f	5	\N	\N	2025-04-12 14:06:25.760816-06
56	70	2025-04-12	300.00	f	5	\N	\N	2025-04-12 14:06:53.65475-06
57	70	2025-04-12	400.00	f	5	\N	\N	2025-04-12 14:07:32.924218-06
58	70	2025-04-14	300.00	f	5	\N	\N	2025-04-14 14:46:07.100279-06
59	70	2025-04-14	400.00	f	5	\N	\N	2025-04-14 14:47:23.292417-06
60	70	2025-04-14	3500.00	f	5	\N	\N	2025-04-14 14:51:56.499852-06
61	71	2025-04-14	1400.00	f	5	\N	\N	2025-04-14 14:55:57.59428-06
62	72	2025-04-14	70.00	f	5	\N	\N	2025-04-14 15:03:28.185362-06
63	73	2025-04-14	56.00	f	5	\N	\N	2025-04-14 15:23:02.645419-06
64	54	2025-04-14	1225.00	f	5	\N	\N	2025-04-14 15:28:25.562649-06
65	55	2025-04-14	175.00	f	5	\N	\N	2025-04-14 15:29:01.906847-06
66	69	2025-04-14	3150.00	f	5	\N	\N	2025-04-14 15:29:41.122512-06
67	69	2025-04-14	3150.00	f	5	\N	\N	2025-04-14 15:30:46.166276-06
68	69	2025-04-14	3150.00	f	5	\N	\N	2025-04-14 15:31:29.842585-06
69	74	2025-04-14	2000.00	f	5	\N	\N	2025-04-14 15:37:35.466249-06
70	74	2025-04-14	2000.00	f	5	\N	\N	2025-04-14 15:38:53.16345-06
71	74	2025-04-14	2000.00	f	5	\N	\N	2025-04-14 15:45:22.732805-06
72	75	2025-04-14	525.00	f	5	\N	\N	2025-04-14 15:49:16.602991-06
73	75	2025-04-14	525.00	f	5	\N	\N	2025-04-14 15:53:40.061816-06
74	75	2025-04-14	525.00	f	5	\N	\N	2025-04-14 15:54:43.169792-06
75	75	2025-04-14	2625.00	f	5	\N	\N	2025-04-14 15:55:23.961312-06
76	76	2025-04-14	4200.00	f	5	\N	\N	2025-04-14 16:00:51.399146-06
77	76	2025-04-14	4200.00	f	5	\N	\N	2025-04-14 21:08:53.549665-06
78	77	2025-04-14	875.00	f	5	\N	\N	2025-04-14 21:15:37.97259-06
79	77	2025-04-14	875.00	f	5	\N	\N	2025-04-14 21:17:17.339259-06
80	77	2025-04-14	875.00	f	5	\N	\N	2025-04-14 21:18:33.066155-06
81	77	2025-04-14	875.00	f	5	\N	\N	2025-04-14 21:19:18.790271-06
82	78	2025-04-14	175.00	f	5	\N	\N	2025-04-14 22:31:14.887162-06
83	78	2025-04-14	1225.00	f	5	\N	\N	2025-04-14 22:32:44.125088-06
84	81	2025-04-26	350.00	f	5	\N	\N	2025-04-26 12:57:38.928737-06
85	81	2025-04-26	400.00	f	5	\N	\N	2025-04-26 12:58:00.037965-06
86	83	2025-05-06	3765.00	f	5	\N	\N	2025-05-06 13:12:42.5781-06
87	83	2025-05-06	25635.00	f	5	\N	\N	2025-05-06 16:53:21.375558-06
88	85	2025-05-10	3150.00	f	5	\N	\N	2025-05-10 00:37:29.139916-06
89	85	2025-05-10	4150.00	f	5	\N	\N	2025-05-10 01:24:06.599985-06
90	85	2025-05-11	1000.00	f	5	\N	\N	2025-05-11 06:35:16.631424-06
91	85	2025-05-11	1000.00	f	5	\N	\N	2025-05-11 06:40:51.793587-06
92	81	2025-05-12	300.00	f	5	\N	\N	2025-05-12 12:44:21.739043-06
93	85	2025-05-12	42.86	f	5	\N	\N	2025-05-12 12:50:29.035341-06
94	90	2025-05-24	867.00	f	5	\N	\N	2025-05-24 12:01:48.324501-06
95	91	2025-06-04	200.00	f	5	\N	\N	2025-06-04 11:16:57.896969-06
96	91	2025-06-04	500.00	f	5	\N	\N	2025-06-04 16:19:43.049366-06
97	94	2025-06-15	164.00	f	5	\N	\N	2025-06-15 21:21:58.384357-06
98	94	2025-06-15	200.00	f	5	\N	\N	2025-06-15 21:22:18.367496-06
99	94	2025-06-15	128.00	f	5	\N	\N	2025-06-15 21:23:02.597998-06
100	95	2025-06-15	2520.00	f	5	\N	\N	2025-06-15 21:32:50.238111-06
101	96	2025-06-15	1000.00	f	5	\N	\N	2025-06-15 21:37:51.248708-06
102	96	2025-06-15	2000.00	f	5	\N	\N	2025-06-15 21:38:15.854195-06
103	98	2025-06-24	875.00	f	5	\N	\N	2025-06-24 10:45:42.014672-06
104	99	2025-06-30	512.50	f	5	\N	\N	2025-06-30 09:28:24.017111-06
105	99	2025-06-30	205.00	f	5	\N	\N	2025-06-30 09:28:58.681333-06
106	101	2025-07-06	700.00	f	5	\N	\N	2025-07-06 21:36:40.068787-06
109	102	2025-09-20	1000.00	f	5	\N	\N	2025-09-20 19:51:05.785128-06
\.


--
-- Data for Name: abono_anulado; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.abono_anulado (id_anulacion, id_abono_original, id_prestamo, fecha_abono, monto_abonado, es_prorroga, usuario_anulo, fecha_anulacion, motivo, pagos_afectados) FROM stdin;
1	107	101	2025-07-29	800.00	f	5	2025-07-29 22:32:31.209947-06	Se equivoco el cliente	[{"id_pago": 567, "monto_aplicado": 700.00}, {"id_pago": 568, "monto_aplicado": 100.00}]
2	108	102	2025-09-20	700.00	f	5	2025-09-20 19:50:37.33334-06	Se equivoco el cliente	[{"id_pago": 574, "monto_aplicado": 700.00}]
\.


--
-- Data for Name: abono_cuota; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.abono_cuota (id_relacion, id_abono, id_pago, monto_aplicado, fecha_registro) FROM stdin;
1	28	225	3150.00	2025-04-08 09:39:04.355082
2	29	226	3000.00	2025-04-08 09:41:00.726067
3	30	226	150.00	2025-04-08 10:09:01.949028
4	31	227	3150.00	2025-04-08 10:10:33.920263
5	32	228	3150.00	2025-04-08 10:14:43.830061
6	33	229	3000.00	2025-04-08 10:15:46.103263
7	34	229	150.00	2025-04-08 10:16:26.398956
8	35	230	3150.00	2025-04-08 10:22:47.255136
9	35	231	150.00	2025-04-08 10:22:47.255136
10	36	231	3150.00	2025-04-08 10:26:44.057895
11	36	232	3000.00	2025-04-08 10:26:44.057895
12	37	232	150.00	2025-04-08 10:27:31.067893
13	38	234	875.00	2025-04-08 10:31:12.858087
14	39	242	700.00	2025-04-08 14:49:25.669493
15	40	243	500.00	2025-04-08 14:51:50.728511
16	41	243	200.00	2025-04-08 14:55:23.417293
17	42	244	500.00	2025-04-08 14:57:53.103286
18	43	244	200.00	2025-04-08 14:58:24.467594
19	44	245	700.00	2025-04-08 14:59:00.692244
20	44	246	700.00	2025-04-08 14:59:00.692244
21	45	247	350.00	2025-04-08 15:25:46.847334
122	87	428	3675.00	2025-05-06 16:53:21.375558
22	46	247	350.00	2025-04-08 15:43:05.264
23	47	250	175.00	2025-04-08 16:05:09.523173
24	48	251	175.00	2025-04-08 16:06:28.404534
25	48	252	125.00	2025-04-08 16:06:28.404534
26	49	252	50.00	2025-04-08 16:07:58.022575
27	50	253	175.00	2025-04-08 16:09:59.007467
28	50	254	175.00	2025-04-08 16:09:59.007467
29	50	255	175.00	2025-04-08 16:09:59.007467
30	50	256	175.00	2025-04-08 16:09:59.007467
31	50	257	175.00	2025-04-08 16:09:59.007467
32	51	201	1225.00	2025-04-09 13:58:16.422736
33	52	307	3150.00	2025-04-09 15:17:55.712068
34	53	308	3000.00	2025-04-09 15:18:19.641706
35	54	308	150.00	2025-04-09 15:18:43.353094
36	54	309	3150.00	2025-04-09 15:18:43.353094
37	55	315	700.00	2025-04-12 14:06:25.760816
38	56	316	300.00	2025-04-12 14:06:53.65475
39	57	316	400.00	2025-04-12 14:07:32.924218
40	58	317	300.00	2025-04-14 14:46:07.100279
41	59	317	400.00	2025-04-14 14:47:23.292417
42	60	318	700.00	2025-04-14 14:51:56.499852
43	60	319	700.00	2025-04-14 14:51:56.499852
44	60	320	700.00	2025-04-14 14:51:56.499852
45	60	321	700.00	2025-04-14 14:51:56.499852
46	60	322	700.00	2025-04-14 14:51:56.499852
47	61	323	175.00	2025-04-14 14:55:57.59428
48	61	324	175.00	2025-04-14 14:55:57.59428
49	61	325	175.00	2025-04-14 14:55:57.59428
50	61	326	175.00	2025-04-14 14:55:57.59428
51	61	327	175.00	2025-04-14 14:55:57.59428
52	61	328	175.00	2025-04-14 14:55:57.59428
53	61	329	175.00	2025-04-14 14:55:57.59428
54	61	330	175.00	2025-04-14 14:55:57.59428
55	62	331	8.75	2025-04-14 15:03:28.185362
56	62	332	8.75	2025-04-14 15:03:28.185362
57	62	333	8.75	2025-04-14 15:03:28.185362
58	62	334	8.75	2025-04-14 15:03:28.185362
59	62	335	8.75	2025-04-14 15:03:28.185362
60	62	336	8.75	2025-04-14 15:03:28.185362
61	62	337	8.75	2025-04-14 15:03:28.185362
62	62	338	8.75	2025-04-14 15:03:28.185362
63	63	339	7.00	2025-04-14 15:23:02.645419
64	63	340	7.00	2025-04-14 15:23:02.645419
65	63	341	7.00	2025-04-14 15:23:02.645419
66	63	342	7.00	2025-04-14 15:23:02.645419
67	63	343	7.00	2025-04-14 15:23:02.645419
68	63	344	7.00	2025-04-14 15:23:02.645419
69	63	345	7.00	2025-04-14 15:23:02.645419
70	63	346	7.00	2025-04-14 15:23:02.645419
71	64	202	1225.00	2025-04-14 15:28:25.562649
72	65	209	175.00	2025-04-14 15:29:01.906847
73	66	310	3150.00	2025-04-14 15:29:41.122512
74	67	311	3150.00	2025-04-14 15:30:46.166276
75	68	312	3150.00	2025-04-14 15:31:29.842585
76	69	347	350.00	2025-04-14 15:37:35.466249
77	69	348	350.00	2025-04-14 15:37:35.466249
78	69	349	350.00	2025-04-14 15:37:35.466249
79	69	350	350.00	2025-04-14 15:37:35.466249
80	69	351	350.00	2025-04-14 15:37:35.466249
81	69	352	250.00	2025-04-14 15:37:35.466249
82	70	352	100.00	2025-04-14 15:38:53.16345
83	70	353	350.00	2025-04-14 15:38:53.16345
84	70	354	350.00	2025-04-14 15:38:53.16345
85	72	355	525.00	2025-04-14 15:49:16.602991
86	73	356	525.00	2025-04-14 15:53:40.061816
87	74	357	525.00	2025-04-14 15:54:43.169792
88	75	358	525.00	2025-04-14 15:55:23.961312
89	75	359	525.00	2025-04-14 15:55:23.961312
90	75	360	525.00	2025-04-14 15:55:23.961312
91	75	361	525.00	2025-04-14 15:55:23.961312
92	75	362	525.00	2025-04-14 15:55:23.961312
93	76	363	525.00	2025-04-14 16:00:51.399146
94	76	364	525.00	2025-04-14 16:00:51.399146
95	76	365	525.00	2025-04-14 16:00:51.399146
96	76	366	525.00	2025-04-14 16:00:51.399146
97	76	367	525.00	2025-04-14 16:00:51.399146
98	76	368	525.00	2025-04-14 16:00:51.399146
99	76	369	525.00	2025-04-14 16:00:51.399146
100	76	370	525.00	2025-04-14 16:00:51.399146
101	78	371	875.00	2025-04-14 21:15:37.97259
102	79	372	875.00	2025-04-14 21:17:17.339259
103	80	373	875.00	2025-04-14 21:18:33.066155
104	81	374	875.00	2025-04-14 21:19:18.790271
105	82	379	175.00	2025-04-14 22:31:14.887162
106	83	380	175.00	2025-04-14 22:32:44.125088
107	83	381	175.00	2025-04-14 22:32:44.125088
108	83	382	175.00	2025-04-14 22:32:44.125088
109	83	383	175.00	2025-04-14 22:32:44.125088
110	83	384	175.00	2025-04-14 22:32:44.125088
111	83	385	175.00	2025-04-14 22:32:44.125088
112	83	386	175.00	2025-04-14 22:32:44.125088
113	84	403	350.00	2025-04-26 12:57:38.928737
114	85	404	350.00	2025-04-26 12:58:00.037965
115	85	405	50.00	2025-04-26 12:58:00.037965
116	86	423	3675.00	2025-05-06 13:12:42.5781
117	86	424	90.00	2025-05-06 13:12:42.5781
118	87	424	3585.00	2025-05-06 16:53:21.375558
119	87	425	3675.00	2025-05-06 16:53:21.375558
120	87	426	3675.00	2025-05-06 16:53:21.375558
121	87	427	3675.00	2025-05-06 16:53:21.375558
123	87	429	3675.00	2025-05-06 16:53:21.375558
124	87	430	3675.00	2025-05-06 16:53:21.375558
125	88	439	3150.00	2025-05-10 00:37:29.139916
126	89	440	3150.00	2025-05-10 01:24:06.599985
127	89	441	1000.00	2025-05-10 01:24:06.599985
128	90	441	1000.00	2025-05-11 06:35:16.631424
129	91	441	1000.00	2025-05-11 06:40:51.793587
130	92	405	300.00	2025-05-12 12:44:21.739043
131	93	441	42.86	2025-05-12 12:50:29.035341
132	94	458	866.67	2025-05-24 12:01:48.324501
133	94	459	0.33	2025-05-24 12:01:48.324501
134	95	464	200.00	2025-06-04 11:16:57.896969
135	96	464	500.00	2025-06-04 16:19:43.049366
136	97	508	164.00	2025-06-15 21:21:58.384357
137	98	509	164.00	2025-06-15 21:22:18.367496
138	98	510	36.00	2025-06-15 21:22:18.367496
139	99	510	128.00	2025-06-15 21:23:02.597998
140	100	538	2520.00	2025-06-15 21:32:50.238111
141	101	540	1000.00	2025-06-15 21:37:51.248708
142	102	540	2000.00	2025-06-15 21:38:15.854195
143	103	542	875.00	2025-06-24 10:45:42.014672
144	104	550	512.50	2025-06-30 09:28:24.017111
145	105	550	205.00	2025-06-30 09:28:58.681333
146	106	566	700.00	2025-07-06 21:36:40.068787
150	109	574	700.00	2025-09-20 19:51:05.785128
151	109	575	300.00	2025-09-20 19:51:05.785128
\.


--
-- Data for Name: calendariopago; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.calendariopago (id_pago, id_prestamo, fecha_pago, monto_cuota, interes, principal, estado, usuario_creo, fecha_modifico, usuario_modifico, saldo, fecha_creo, saldo_cuota) FROM stdin;
1	17	2025-03-31	962.50	275.00	687.50	Pendiente	5	\N	\N	6737.50	2025-03-24 17:58:34.317904-06	\N
2	17	2025-04-07	962.50	275.00	687.50	Pendiente	5	\N	\N	5775.00	2025-03-24 17:58:34.337664-06	\N
3	17	2025-04-14	962.50	275.00	687.50	Pendiente	5	\N	\N	4812.50	2025-03-24 17:58:34.347888-06	\N
4	17	2025-04-21	962.50	275.00	687.50	Pendiente	5	\N	\N	3850.00	2025-03-24 17:58:34.362669-06	\N
5	17	2025-04-28	962.50	275.00	687.50	Pendiente	5	\N	\N	2887.50	2025-03-24 17:58:34.379108-06	\N
6	17	2025-05-05	962.50	275.00	687.50	Pendiente	5	\N	\N	1925.00	2025-03-24 17:58:34.39127-06	\N
7	17	2025-05-12	962.50	275.00	687.50	Pendiente	5	\N	\N	962.50	2025-03-24 17:58:34.408181-06	\N
8	17	2025-05-19	962.50	275.00	687.50	Pendiente	5	\N	\N	0.00	2025-03-24 17:58:34.419645-06	\N
9	18	2025-03-29	175.00	50.00	125.00	Pendiente	5	\N	\N	1225.00	2025-03-25 13:24:18.455095-06	\N
10	18	2025-04-05	175.00	50.00	125.00	Pendiente	5	\N	\N	1050.00	2025-03-25 13:24:18.464145-06	\N
11	18	2025-04-12	175.00	50.00	125.00	Pendiente	5	\N	\N	875.00	2025-03-25 13:24:18.472725-06	\N
12	18	2025-04-19	175.00	50.00	125.00	Pendiente	5	\N	\N	700.00	2025-03-25 13:24:18.481438-06	\N
13	18	2025-04-26	175.00	50.00	125.00	Pendiente	5	\N	\N	525.00	2025-03-25 13:24:18.490091-06	\N
14	18	2025-05-03	175.00	50.00	125.00	Pendiente	5	\N	\N	350.00	2025-03-25 13:24:18.502849-06	\N
15	18	2025-05-10	175.00	50.00	125.00	Pendiente	5	\N	\N	175.00	2025-03-25 13:24:18.513073-06	\N
16	18	2025-05-17	175.00	50.00	125.00	Pendiente	5	\N	\N	0.00	2025-03-25 13:24:18.525105-06	\N
17	19	2025-03-26	175.00	50.00	125.00	Pendiente	5	\N	\N	1225.00	2025-03-25 13:50:43.303517-06	\N
18	19	2025-04-02	175.00	50.00	125.00	Pendiente	5	\N	\N	1050.00	2025-03-25 13:50:43.311992-06	\N
19	19	2025-04-09	175.00	50.00	125.00	Pendiente	5	\N	\N	875.00	2025-03-25 13:50:43.317894-06	\N
20	19	2025-04-16	175.00	50.00	125.00	Pendiente	5	\N	\N	700.00	2025-03-25 13:50:43.324892-06	\N
21	19	2025-04-23	175.00	50.00	125.00	Pendiente	5	\N	\N	525.00	2025-03-25 13:50:43.331684-06	\N
22	19	2025-04-30	175.00	50.00	125.00	Pendiente	5	\N	\N	350.00	2025-03-25 13:50:43.338698-06	\N
23	19	2025-05-07	175.00	50.00	125.00	Pendiente	5	\N	\N	175.00	2025-03-25 13:50:43.345392-06	\N
24	19	2025-05-14	175.00	50.00	125.00	Pendiente	5	\N	\N	0.00	2025-03-25 13:50:43.352505-06	\N
25	26	2025-04-01	2200.00	1200.00	1000.00	Pendiente	5	\N	\N	50600.00	2025-03-25 15:33:52.092214-06	\N
26	26	2025-04-08	2200.00	1200.00	1000.00	Pendiente	5	\N	\N	48400.00	2025-03-25 15:33:52.101671-06	\N
27	26	2025-04-15	2200.00	1200.00	1000.00	Pendiente	5	\N	\N	46200.00	2025-03-25 15:33:52.10897-06	\N
28	26	2025-04-22	2200.00	1200.00	1000.00	Pendiente	5	\N	\N	44000.00	2025-03-25 15:33:52.114167-06	\N
29	26	2025-04-29	2200.00	1200.00	1000.00	Pendiente	5	\N	\N	41800.00	2025-03-25 15:33:52.119996-06	\N
30	26	2025-05-06	2200.00	1200.00	1000.00	Pendiente	5	\N	\N	39600.00	2025-03-25 15:33:52.126822-06	\N
31	26	2025-05-13	2200.00	1200.00	1000.00	Pendiente	5	\N	\N	37400.00	2025-03-25 15:33:52.133678-06	\N
32	26	2025-05-20	2200.00	1200.00	1000.00	Pendiente	5	\N	\N	35200.00	2025-03-25 15:33:52.141796-06	\N
33	26	2025-05-27	2200.00	1200.00	1000.00	Pendiente	5	\N	\N	33000.00	2025-03-25 15:33:52.149573-06	\N
34	26	2025-06-03	2200.00	1200.00	1000.00	Pendiente	5	\N	\N	30800.00	2025-03-25 15:33:52.155959-06	\N
35	26	2025-06-10	2200.00	1200.00	1000.00	Pendiente	5	\N	\N	28600.00	2025-03-25 15:33:52.159627-06	\N
36	26	2025-06-17	2200.00	1200.00	1000.00	Pendiente	5	\N	\N	26400.00	2025-03-25 15:33:52.163057-06	\N
37	26	2025-06-24	2200.00	1200.00	1000.00	Pendiente	5	\N	\N	24200.00	2025-03-25 15:33:52.167369-06	\N
38	26	2025-07-01	2200.00	1200.00	1000.00	Pendiente	5	\N	\N	22000.00	2025-03-25 15:33:52.171851-06	\N
39	26	2025-07-08	2200.00	1200.00	1000.00	Pendiente	5	\N	\N	19800.00	2025-03-25 15:33:52.175402-06	\N
40	26	2025-07-15	2200.00	1200.00	1000.00	Pendiente	5	\N	\N	17600.00	2025-03-25 15:33:52.179437-06	\N
41	26	2025-07-22	2200.00	1200.00	1000.00	Pendiente	5	\N	\N	15400.00	2025-03-25 15:33:52.18331-06	\N
42	26	2025-07-29	2200.00	1200.00	1000.00	Pendiente	5	\N	\N	13200.00	2025-03-25 15:33:52.187321-06	\N
43	26	2025-08-05	2200.00	1200.00	1000.00	Pendiente	5	\N	\N	11000.00	2025-03-25 15:33:52.191122-06	\N
44	26	2025-08-12	2200.00	1200.00	1000.00	Pendiente	5	\N	\N	8800.00	2025-03-25 15:33:52.195061-06	\N
45	26	2025-08-19	2200.00	1200.00	1000.00	Pendiente	5	\N	\N	6600.00	2025-03-25 15:33:52.199313-06	\N
46	26	2025-08-26	2200.00	1200.00	1000.00	Pendiente	5	\N	\N	4400.00	2025-03-25 15:33:52.203848-06	\N
47	26	2025-09-02	2200.00	1200.00	1000.00	Pendiente	5	\N	\N	2200.00	2025-03-25 15:33:52.207981-06	\N
48	26	2025-09-09	2200.00	1200.00	1000.00	Pendiente	5	\N	\N	0.00	2025-03-25 15:33:52.212309-06	\N
49	27	2025-04-04	577.50	165.00	412.50	Pendiente	5	\N	\N	4042.50	2025-03-27 09:50:14.493457-06	\N
50	27	2025-04-11	577.50	165.00	412.50	Pendiente	5	\N	\N	3465.00	2025-03-27 09:50:14.495994-06	\N
51	27	2025-04-18	577.50	165.00	412.50	Pendiente	5	\N	\N	2887.50	2025-03-27 09:50:14.498277-06	\N
52	27	2025-04-25	577.50	165.00	412.50	Pendiente	5	\N	\N	2310.00	2025-03-27 09:50:14.501084-06	\N
53	27	2025-05-02	577.50	165.00	412.50	Pendiente	5	\N	\N	1732.50	2025-03-27 09:50:14.503788-06	\N
54	27	2025-05-09	577.50	165.00	412.50	Pendiente	5	\N	\N	1155.00	2025-03-27 09:50:14.506983-06	\N
55	27	2025-05-16	577.50	165.00	412.50	Pendiente	5	\N	\N	577.50	2025-03-27 09:50:14.509281-06	\N
56	27	2025-05-23	577.50	165.00	412.50	Pendiente	5	\N	\N	0.00	2025-03-27 09:50:14.511573-06	\N
70	41	2025-04-04	2400.00	900.00	1500.00	Pendiente	5	\N	\N	26400.00	2025-03-28 15:46:55.056002-06	\N
71	41	2025-04-11	2400.00	900.00	1500.00	Pendiente	5	\N	\N	24000.00	2025-03-28 15:46:55.058577-06	\N
72	41	2025-04-18	2400.00	900.00	1500.00	Pendiente	5	\N	\N	21600.00	2025-03-28 15:46:55.060809-06	\N
73	41	2025-04-25	2400.00	900.00	1500.00	Pendiente	5	\N	\N	19200.00	2025-03-28 15:46:55.066193-06	\N
74	41	2025-05-02	2400.00	900.00	1500.00	Pendiente	5	\N	\N	16800.00	2025-03-28 15:46:55.068944-06	\N
75	41	2025-05-09	2400.00	900.00	1500.00	Pendiente	5	\N	\N	14400.00	2025-03-28 15:46:55.071046-06	\N
76	41	2025-05-16	2400.00	900.00	1500.00	Pendiente	5	\N	\N	12000.00	2025-03-28 15:46:55.073131-06	\N
77	41	2025-05-23	2400.00	900.00	1500.00	Pendiente	5	\N	\N	9600.00	2025-03-28 15:46:55.075121-06	\N
78	41	2025-05-30	2400.00	900.00	1500.00	Pendiente	5	\N	\N	7200.00	2025-03-28 15:46:55.077204-06	\N
79	41	2025-06-06	2400.00	900.00	1500.00	Pendiente	5	\N	\N	4800.00	2025-03-28 15:46:55.081006-06	\N
80	41	2025-06-13	2400.00	900.00	1500.00	Pendiente	5	\N	\N	2400.00	2025-03-28 15:46:55.083267-06	\N
81	41	2025-06-20	2400.00	900.00	1500.00	Pendiente	5	\N	\N	0.00	2025-03-28 15:46:55.085375-06	\N
84	44	2025-04-05	875.00	250.00	625.00	Pendiente	5	\N	\N	6125.00	2025-03-28 16:00:57.383922-06	\N
85	44	2025-04-12	875.00	250.00	625.00	Pendiente	5	\N	\N	5250.00	2025-03-28 16:00:57.389144-06	\N
86	44	2025-04-19	875.00	250.00	625.00	Pendiente	5	\N	\N	4375.00	2025-03-28 16:00:57.392554-06	\N
87	44	2025-04-26	875.00	250.00	625.00	Pendiente	5	\N	\N	3500.00	2025-03-28 16:00:57.39584-06	\N
88	44	2025-05-03	875.00	250.00	625.00	Pendiente	5	\N	\N	2625.00	2025-03-28 16:00:57.398788-06	\N
89	44	2025-05-10	875.00	250.00	625.00	Pendiente	5	\N	\N	1750.00	2025-03-28 16:00:57.400876-06	\N
90	44	2025-05-17	875.00	250.00	625.00	Pendiente	5	\N	\N	875.00	2025-03-28 16:00:57.404227-06	\N
91	44	2025-05-24	875.00	250.00	625.00	Pendiente	5	\N	\N	0.00	2025-03-28 16:00:57.406599-06	\N
92	45	2025-04-11	700.00	200.00	500.00	Pendiente	5	\N	\N	4900.00	2025-03-28 16:11:01.533932-06	\N
93	45	2025-04-18	700.00	200.00	500.00	Pendiente	5	\N	\N	4200.00	2025-03-28 16:11:01.536463-06	\N
94	45	2025-04-25	700.00	200.00	500.00	Pendiente	5	\N	\N	3500.00	2025-03-28 16:11:01.53923-06	\N
95	45	2025-05-02	700.00	200.00	500.00	Pendiente	5	\N	\N	2800.00	2025-03-28 16:11:01.54246-06	\N
96	45	2025-05-09	700.00	200.00	500.00	Pendiente	5	\N	\N	2100.00	2025-03-28 16:11:01.5446-06	\N
97	45	2025-05-16	700.00	200.00	500.00	Pendiente	5	\N	\N	1400.00	2025-03-28 16:11:01.546753-06	\N
98	45	2025-05-23	700.00	200.00	500.00	Pendiente	5	\N	\N	700.00	2025-03-28 16:11:01.54883-06	\N
99	45	2025-05-30	700.00	200.00	500.00	Pendiente	5	\N	\N	0.00	2025-03-28 16:11:01.551279-06	\N
101	47	2025-04-05	175.00	50.00	125.00	Pendiente	5	\N	\N	1225.00	2025-03-28 16:22:25.537643-06	\N
102	47	2025-04-12	175.00	50.00	125.00	Pendiente	5	\N	\N	1050.00	2025-03-28 16:22:25.540567-06	\N
103	47	2025-04-19	175.00	50.00	125.00	Pendiente	5	\N	\N	875.00	2025-03-28 16:22:25.542776-06	\N
104	47	2025-04-26	175.00	50.00	125.00	Pendiente	5	\N	\N	700.00	2025-03-28 16:22:25.544879-06	\N
105	47	2025-05-03	175.00	50.00	125.00	Pendiente	5	\N	\N	525.00	2025-03-28 16:22:25.547002-06	\N
106	47	2025-05-10	175.00	50.00	125.00	Pendiente	5	\N	\N	350.00	2025-03-28 16:22:25.55118-06	\N
107	47	2025-05-17	175.00	50.00	125.00	Pendiente	5	\N	\N	175.00	2025-03-28 16:22:25.554518-06	\N
108	47	2025-05-24	175.00	50.00	125.00	Pendiente	5	\N	\N	0.00	2025-03-28 16:22:25.556867-06	\N
109	48	2025-04-01	262.50	75.00	187.50	Pendiente	5	\N	\N	1837.50	2025-03-28 16:28:19.626241-06	\N
110	48	2025-04-08	262.50	75.00	187.50	Pendiente	5	\N	\N	1575.00	2025-03-28 16:28:19.631338-06	\N
111	48	2025-04-15	262.50	75.00	187.50	Pendiente	5	\N	\N	1312.50	2025-03-28 16:28:19.633994-06	\N
112	48	2025-04-22	262.50	75.00	187.50	Pendiente	5	\N	\N	1050.00	2025-03-28 16:28:19.636927-06	\N
113	48	2025-04-29	262.50	75.00	187.50	Pendiente	5	\N	\N	787.50	2025-03-28 16:28:19.639701-06	\N
114	48	2025-05-06	262.50	75.00	187.50	Pendiente	5	\N	\N	525.00	2025-03-28 16:28:19.642243-06	\N
115	48	2025-05-13	262.50	75.00	187.50	Pendiente	5	\N	\N	262.50	2025-03-28 16:28:19.646649-06	\N
116	48	2025-05-20	262.50	75.00	187.50	Pendiente	5	\N	\N	0.00	2025-03-28 16:28:19.650383-06	\N
117	49	2025-04-23	1050.00	300.00	750.00	Pendiente	5	\N	\N	7350.00	2025-03-28 16:37:03.976447-06	\N
118	49	2025-04-30	1050.00	300.00	750.00	Pendiente	5	\N	\N	6300.00	2025-03-28 16:37:03.983244-06	\N
119	49	2025-05-07	1050.00	300.00	750.00	Pendiente	5	\N	\N	5250.00	2025-03-28 16:37:03.986322-06	\N
120	49	2025-05-14	1050.00	300.00	750.00	Pendiente	5	\N	\N	4200.00	2025-03-28 16:37:03.988503-06	\N
121	49	2025-05-21	1050.00	300.00	750.00	Pendiente	5	\N	\N	3150.00	2025-03-28 16:37:03.990577-06	\N
122	49	2025-05-28	1050.00	300.00	750.00	Pendiente	5	\N	\N	2100.00	2025-03-28 16:37:03.992648-06	\N
123	49	2025-06-04	1050.00	300.00	750.00	Pendiente	5	\N	\N	1050.00	2025-03-28 16:37:03.995846-06	\N
124	49	2025-06-11	1050.00	300.00	750.00	Pendiente	5	\N	\N	0.00	2025-03-28 16:37:03.998035-06	\N
125	50	2025-03-29	175.00	50.00	125.00	Pendiente	5	\N	\N	1225.00	2025-03-29 03:39:40.37799-06	\N
126	50	2025-04-05	175.00	50.00	125.00	Pendiente	5	\N	\N	1050.00	2025-03-29 03:39:40.386968-06	\N
127	50	2025-04-12	175.00	50.00	125.00	Pendiente	5	\N	\N	875.00	2025-03-29 03:39:40.395163-06	\N
128	50	2025-04-19	175.00	50.00	125.00	Pendiente	5	\N	\N	700.00	2025-03-29 03:39:40.401717-06	\N
129	50	2025-04-26	175.00	50.00	125.00	Pendiente	5	\N	\N	525.00	2025-03-29 03:39:40.40472-06	\N
130	50	2025-05-03	175.00	50.00	125.00	Pendiente	5	\N	\N	350.00	2025-03-29 03:39:40.407169-06	\N
131	50	2025-05-10	175.00	50.00	125.00	Pendiente	5	\N	\N	175.00	2025-03-29 03:39:40.41004-06	\N
132	50	2025-05-17	175.00	50.00	125.00	Pendiente	5	\N	\N	0.00	2025-03-29 03:39:40.414894-06	\N
133	51	2025-04-05	875.00	250.00	625.00	Pendiente	5	\N	\N	6125.00	2025-03-29 03:45:36.053336-06	\N
134	51	2025-04-12	875.00	250.00	625.00	Pendiente	5	\N	\N	5250.00	2025-03-29 03:45:36.065375-06	\N
135	51	2025-04-19	875.00	250.00	625.00	Pendiente	5	\N	\N	4375.00	2025-03-29 03:45:36.075664-06	\N
136	51	2025-04-26	875.00	250.00	625.00	Pendiente	5	\N	\N	3500.00	2025-03-29 03:45:36.085911-06	\N
137	51	2025-05-03	875.00	250.00	625.00	Pendiente	5	\N	\N	2625.00	2025-03-29 03:45:36.08974-06	\N
138	51	2025-05-10	875.00	250.00	625.00	Pendiente	5	\N	\N	1750.00	2025-03-29 03:45:36.092144-06	\N
139	51	2025-05-17	875.00	250.00	625.00	Pendiente	5	\N	\N	875.00	2025-03-29 03:45:36.094525-06	\N
140	51	2025-05-24	875.00	250.00	625.00	Pendiente	5	\N	\N	0.00	2025-03-29 03:45:36.096878-06	\N
141	52	2025-04-05	2125.00	1500.00	625.00	Pendiente	5	\N	\N	99875.00	2025-03-29 08:50:01.640192-06	\N
142	52	2025-04-12	2125.00	1500.00	625.00	Pendiente	5	\N	\N	97750.00	2025-03-29 08:50:01.644269-06	\N
143	52	2025-04-19	2125.00	1500.00	625.00	Pendiente	5	\N	\N	95625.00	2025-03-29 08:50:01.647729-06	\N
144	52	2025-04-26	2125.00	1500.00	625.00	Pendiente	5	\N	\N	93500.00	2025-03-29 08:50:01.650048-06	\N
145	52	2025-05-03	2125.00	1500.00	625.00	Pendiente	5	\N	\N	91375.00	2025-03-29 08:50:01.652211-06	\N
146	52	2025-05-10	2125.00	1500.00	625.00	Pendiente	5	\N	\N	89250.00	2025-03-29 08:50:01.654379-06	\N
147	52	2025-05-17	2125.00	1500.00	625.00	Pendiente	5	\N	\N	87125.00	2025-03-29 08:50:01.656558-06	\N
148	52	2025-05-24	2125.00	1500.00	625.00	Pendiente	5	\N	\N	85000.00	2025-03-29 08:50:01.658701-06	\N
149	52	2025-05-31	2125.00	1500.00	625.00	Pendiente	5	\N	\N	82875.00	2025-03-29 08:50:01.662092-06	\N
150	52	2025-06-07	2125.00	1500.00	625.00	Pendiente	5	\N	\N	80750.00	2025-03-29 08:50:01.665216-06	\N
151	52	2025-06-14	2125.00	1500.00	625.00	Pendiente	5	\N	\N	78625.00	2025-03-29 08:50:01.667467-06	\N
152	52	2025-06-21	2125.00	1500.00	625.00	Pendiente	5	\N	\N	76500.00	2025-03-29 08:50:01.669663-06	\N
153	52	2025-06-28	2125.00	1500.00	625.00	Pendiente	5	\N	\N	74375.00	2025-03-29 08:50:01.671849-06	\N
154	52	2025-07-05	2125.00	1500.00	625.00	Pendiente	5	\N	\N	72250.00	2025-03-29 08:50:01.67405-06	\N
155	52	2025-07-12	2125.00	1500.00	625.00	Pendiente	5	\N	\N	70125.00	2025-03-29 08:50:01.676471-06	\N
156	52	2025-07-19	2125.00	1500.00	625.00	Pendiente	5	\N	\N	68000.00	2025-03-29 08:50:01.679226-06	\N
157	52	2025-07-26	2125.00	1500.00	625.00	Pendiente	5	\N	\N	65875.00	2025-03-29 08:50:01.681423-06	\N
158	52	2025-08-02	2125.00	1500.00	625.00	Pendiente	5	\N	\N	63750.00	2025-03-29 08:50:01.683587-06	\N
159	52	2025-08-09	2125.00	1500.00	625.00	Pendiente	5	\N	\N	61625.00	2025-03-29 08:50:01.685683-06	\N
160	52	2025-08-16	2125.00	1500.00	625.00	Pendiente	5	\N	\N	59500.00	2025-03-29 08:50:01.687815-06	\N
161	52	2025-08-23	2125.00	1500.00	625.00	Pendiente	5	\N	\N	57375.00	2025-03-29 08:50:01.690007-06	\N
162	52	2025-08-30	2125.00	1500.00	625.00	Pendiente	5	\N	\N	55250.00	2025-03-29 08:50:01.692097-06	\N
163	52	2025-09-06	2125.00	1500.00	625.00	Pendiente	5	\N	\N	53125.00	2025-03-29 08:50:01.694655-06	\N
164	52	2025-09-13	2125.00	1500.00	625.00	Pendiente	5	\N	\N	51000.00	2025-03-29 08:50:01.696844-06	\N
165	52	2025-09-20	2125.00	1500.00	625.00	Pendiente	5	\N	\N	48875.00	2025-03-29 08:50:01.698937-06	\N
166	52	2025-09-27	2125.00	1500.00	625.00	Pendiente	5	\N	\N	46750.00	2025-03-29 08:50:01.701-06	\N
167	52	2025-10-04	2125.00	1500.00	625.00	Pendiente	5	\N	\N	44625.00	2025-03-29 08:50:01.703056-06	\N
168	52	2025-10-11	2125.00	1500.00	625.00	Pendiente	5	\N	\N	42500.00	2025-03-29 08:50:01.705157-06	\N
169	52	2025-10-18	2125.00	1500.00	625.00	Pendiente	5	\N	\N	40375.00	2025-03-29 08:50:01.707178-06	\N
170	52	2025-10-25	2125.00	1500.00	625.00	Pendiente	5	\N	\N	38250.00	2025-03-29 08:50:01.709297-06	\N
171	52	2025-11-01	2125.00	1500.00	625.00	Pendiente	5	\N	\N	36125.00	2025-03-29 08:50:01.712019-06	\N
172	52	2025-11-08	2125.00	1500.00	625.00	Pendiente	5	\N	\N	34000.00	2025-03-29 08:50:01.714927-06	\N
173	52	2025-11-15	2125.00	1500.00	625.00	Pendiente	5	\N	\N	31875.00	2025-03-29 08:50:01.717329-06	\N
174	52	2025-11-22	2125.00	1500.00	625.00	Pendiente	5	\N	\N	29750.00	2025-03-29 08:50:01.71971-06	\N
175	52	2025-11-29	2125.00	1500.00	625.00	Pendiente	5	\N	\N	27625.00	2025-03-29 08:50:01.721964-06	\N
176	52	2025-12-06	2125.00	1500.00	625.00	Pendiente	5	\N	\N	25500.00	2025-03-29 08:50:01.724276-06	\N
177	52	2025-12-13	2125.00	1500.00	625.00	Pendiente	5	\N	\N	23375.00	2025-03-29 08:50:01.726659-06	\N
178	52	2025-12-20	2125.00	1500.00	625.00	Pendiente	5	\N	\N	21250.00	2025-03-29 08:50:01.729133-06	\N
179	52	2025-12-27	2125.00	1500.00	625.00	Pendiente	5	\N	\N	19125.00	2025-03-29 08:50:01.731511-06	\N
180	52	2026-01-03	2125.00	1500.00	625.00	Pendiente	5	\N	\N	17000.00	2025-03-29 08:50:01.733843-06	\N
181	52	2026-01-10	2125.00	1500.00	625.00	Pendiente	5	\N	\N	14875.00	2025-03-29 08:50:01.736143-06	\N
182	52	2026-01-17	2125.00	1500.00	625.00	Pendiente	5	\N	\N	12750.00	2025-03-29 08:50:01.738716-06	\N
183	52	2026-01-24	2125.00	1500.00	625.00	Pendiente	5	\N	\N	10625.00	2025-03-29 08:50:01.741041-06	\N
184	52	2026-01-31	2125.00	1500.00	625.00	Pendiente	5	\N	\N	8500.00	2025-03-29 08:50:01.743408-06	\N
185	52	2026-02-07	2125.00	1500.00	625.00	Pendiente	5	\N	\N	6375.00	2025-03-29 08:50:01.745713-06	\N
186	52	2026-02-14	2125.00	1500.00	625.00	Pendiente	5	\N	\N	4250.00	2025-03-29 08:50:01.747972-06	\N
187	52	2026-02-21	2125.00	1500.00	625.00	Pendiente	5	\N	\N	2125.00	2025-03-29 08:50:01.750241-06	\N
188	52	2026-02-28	2125.00	1500.00	625.00	Pendiente	5	\N	\N	0.00	2025-03-29 08:50:01.75244-06	\N
189	53	2025-04-08	3100.00	600.00	2500.00	Pendiente	5	\N	\N	34100.00	2025-03-29 11:15:19.417706-06	\N
190	53	2025-04-15	3100.00	600.00	2500.00	Pendiente	5	\N	\N	31000.00	2025-03-29 11:15:19.42109-06	\N
191	53	2025-04-22	3100.00	600.00	2500.00	Pendiente	5	\N	\N	27900.00	2025-03-29 11:15:19.425554-06	\N
192	53	2025-04-29	3100.00	600.00	2500.00	Pendiente	5	\N	\N	24800.00	2025-03-29 11:15:19.428581-06	\N
193	53	2025-05-06	3100.00	600.00	2500.00	Pendiente	5	\N	\N	21700.00	2025-03-29 11:15:19.431217-06	\N
194	53	2025-05-13	3100.00	600.00	2500.00	Pendiente	5	\N	\N	18600.00	2025-03-29 11:15:19.433839-06	\N
195	53	2025-05-20	3100.00	600.00	2500.00	Pendiente	5	\N	\N	15500.00	2025-03-29 11:15:19.43648-06	\N
196	53	2025-05-27	3100.00	600.00	2500.00	Pendiente	5	\N	\N	12400.00	2025-03-29 11:15:19.440383-06	\N
197	53	2025-06-03	3100.00	600.00	2500.00	Pendiente	5	\N	\N	9300.00	2025-03-29 11:15:19.44388-06	\N
198	53	2025-06-10	3100.00	600.00	2500.00	Pendiente	5	\N	\N	6200.00	2025-03-29 11:15:19.447417-06	\N
199	53	2025-06-17	3100.00	600.00	2500.00	Pendiente	5	\N	\N	3100.00	2025-03-29 11:15:19.450727-06	\N
200	53	2025-06-24	3100.00	600.00	2500.00	Pendiente	5	\N	\N	0.00	2025-03-29 11:15:19.45367-06	\N
203	54	2025-04-17	1225.00	350.00	875.00	Pendiente	5	\N	\N	6125.00	2025-04-03 22:35:10.779808-06	\N
204	54	2025-04-24	1225.00	350.00	875.00	Pendiente	5	\N	\N	4900.00	2025-04-03 22:35:10.785309-06	\N
205	54	2025-05-01	1225.00	350.00	875.00	Pendiente	5	\N	\N	3675.00	2025-04-03 22:35:10.792686-06	\N
206	54	2025-05-08	1225.00	350.00	875.00	Pendiente	5	\N	\N	2450.00	2025-04-03 22:35:10.797125-06	\N
207	54	2025-05-15	1225.00	350.00	875.00	Pendiente	5	\N	\N	1225.00	2025-04-03 22:35:10.801941-06	\N
208	54	2025-05-22	1225.00	350.00	875.00	Pendiente	5	\N	\N	0.00	2025-04-03 22:35:10.8051-06	\N
210	55	2025-04-11	175.00	50.00	125.00	Pendiente	5	\N	\N	1050.00	2025-04-04 08:27:59.01101-06	\N
211	55	2025-04-18	175.00	50.00	125.00	Pendiente	5	\N	\N	875.00	2025-04-04 08:27:59.01817-06	\N
212	55	2025-04-25	175.00	50.00	125.00	Pendiente	5	\N	\N	700.00	2025-04-04 08:27:59.025957-06	\N
213	55	2025-05-02	175.00	50.00	125.00	Pendiente	5	\N	\N	525.00	2025-04-04 08:27:59.032651-06	\N
214	55	2025-05-09	175.00	50.00	125.00	Pendiente	5	\N	\N	350.00	2025-04-04 08:27:59.036721-06	\N
215	55	2025-05-16	175.00	50.00	125.00	Pendiente	5	\N	\N	175.00	2025-04-04 08:27:59.040646-06	\N
216	55	2025-05-23	175.00	50.00	125.00	Pendiente	5	\N	\N	0.00	2025-04-04 08:27:59.044037-06	\N
217	56	2025-04-04	350.00	100.00	250.00	Pendiente	5	\N	\N	2450.00	2025-04-04 08:33:05.043091-06	\N
218	56	2025-04-11	350.00	100.00	250.00	Pendiente	5	\N	\N	2100.00	2025-04-04 08:33:05.048869-06	\N
219	56	2025-04-18	350.00	100.00	250.00	Pendiente	5	\N	\N	1750.00	2025-04-04 08:33:05.054276-06	\N
220	56	2025-04-25	350.00	100.00	250.00	Pendiente	5	\N	\N	1400.00	2025-04-04 08:33:05.058227-06	\N
221	56	2025-05-02	350.00	100.00	250.00	Pendiente	5	\N	\N	1050.00	2025-04-04 08:33:05.062123-06	\N
222	56	2025-05-09	350.00	100.00	250.00	Pendiente	5	\N	\N	700.00	2025-04-04 08:33:05.065185-06	\N
223	56	2025-05-16	350.00	100.00	250.00	Pendiente	5	\N	\N	350.00	2025-04-04 08:33:05.067608-06	\N
224	56	2025-05-23	350.00	100.00	250.00	Pendiente	5	\N	\N	0.00	2025-04-04 08:33:05.070102-06	\N
225	57	2025-04-04	3150.00	900.00	2250.00	Pagado	5	2025-04-08 09:39:04.355082-06	5	0.00	2025-04-04 11:24:29.044052-06	\N
226	57	2025-04-11	3150.00	900.00	2250.00	Pagado	5	2025-04-08 10:09:01.949028-06	5	150.00	2025-04-04 11:24:29.048884-06	\N
227	57	2025-04-18	3150.00	900.00	2250.00	Pagado	5	2025-04-08 10:10:33.920263-06	5	15750.00	2025-04-04 11:24:29.051466-06	\N
228	57	2025-04-25	3150.00	900.00	2250.00	Pagado	5	2025-04-08 10:14:43.830061-06	5	12600.00	2025-04-04 11:24:29.055501-06	\N
229	57	2025-05-02	3150.00	900.00	2250.00	Pagado	5	2025-04-08 10:16:26.398956-06	5	9450.00	2025-04-04 11:24:29.058539-06	\N
230	57	2025-05-09	3150.00	900.00	2250.00	Pagado	5	2025-04-08 10:22:47.255136-06	5	6300.00	2025-04-04 11:24:29.060871-06	\N
231	57	2025-05-16	3150.00	900.00	2250.00	Pagado	5	2025-04-08 10:26:44.057895-06	5	3150.00	2025-04-04 11:24:29.062991-06	\N
232	57	2025-05-23	3150.00	900.00	2250.00	Pagado	5	2025-04-08 10:27:31.067893-06	5	0.00	2025-04-04 11:24:29.065041-06	\N
235	59	2025-04-15	875.00	250.00	625.00	Pendiente	5	\N	\N	5250.00	2025-04-08 10:30:21.561047-06	\N
236	59	2025-04-22	875.00	250.00	625.00	Pendiente	5	\N	\N	4375.00	2025-04-08 10:30:21.56348-06	\N
237	59	2025-04-29	875.00	250.00	625.00	Pendiente	5	\N	\N	3500.00	2025-04-08 10:30:21.565856-06	\N
238	59	2025-05-06	875.00	250.00	625.00	Pendiente	5	\N	\N	2625.00	2025-04-08 10:30:21.568168-06	\N
239	59	2025-05-13	875.00	250.00	625.00	Pendiente	5	\N	\N	1750.00	2025-04-08 10:30:21.570935-06	\N
240	59	2025-05-20	875.00	250.00	625.00	Pendiente	5	\N	\N	875.00	2025-04-08 10:30:21.57554-06	\N
241	59	2025-05-27	875.00	250.00	625.00	Pendiente	5	\N	\N	0.00	2025-04-08 10:30:21.578006-06	\N
234	59	2025-04-08	875.00	250.00	625.00	Pagado	5	2025-04-08 10:31:12.858087-06	5	6125.00	2025-04-08 10:30:21.556101-06	\N
248	60	2025-05-27	700.00	200.00	500.00	Pendiente	5	\N	\N	700.00	2025-04-08 14:48:49.705084-06	\N
249	60	2025-06-03	700.00	200.00	500.00	Pendiente	5	\N	\N	0.00	2025-04-08 14:48:49.707532-06	\N
242	60	2025-04-15	700.00	200.00	500.00	Pagado	5	2025-04-08 14:49:25.669493-06	5	4900.00	2025-04-08 14:48:49.689427-06	0.00
244	60	2025-04-29	700.00	200.00	500.00	Pagado	5	2025-04-08 14:58:24.467594-06	5	3500.00	2025-04-08 14:48:49.695533-06	0.00
245	60	2025-05-06	700.00	200.00	500.00	Pagado	5	2025-04-08 14:59:00.692244-06	5	2800.00	2025-04-08 14:48:49.697923-06	0.00
243	60	2025-04-22	700.00	200.00	500.00	Pagado	5	2025-04-08 14:55:23.417293-06	5	4200.00	2025-04-08 14:48:49.692477-06	0.00
250	61	2025-04-08	175.00	50.00	125.00	Pagado	5	2025-04-08 16:05:09.523173-06	5	1225.00	2025-04-08 15:59:54.487785-06	0.00
246	60	2025-05-13	700.00	200.00	500.00	Pagado	5	2025-04-08 14:59:00.692244-06	5	2100.00	2025-04-08 14:48:49.700514-06	0.00
251	61	2025-04-15	175.00	50.00	125.00	Pagado	5	2025-04-08 16:06:28.404534-06	5	1050.00	2025-04-08 15:59:54.490816-06	0.00
253	61	2025-04-29	175.00	50.00	125.00	Pagado	5	2025-04-08 16:09:59.007467-06	5	700.00	2025-04-08 15:59:54.495726-06	0.00
247	60	2025-05-20	700.00	200.00	500.00	Pagado	5	2025-04-08 15:43:05.264-06	5	1400.00	2025-04-08 14:48:49.702862-06	0.00
252	61	2025-04-22	175.00	50.00	125.00	Pagado	5	2025-04-08 16:07:58.022575-06	5	875.00	2025-04-08 15:59:54.49332-06	0.00
254	61	2025-05-06	175.00	50.00	125.00	Pagado	5	2025-04-08 16:09:59.007467-06	5	525.00	2025-04-08 15:59:54.498648-06	0.00
255	61	2025-05-13	175.00	50.00	125.00	Pagado	5	2025-04-08 16:09:59.007467-06	5	350.00	2025-04-08 15:59:54.502791-06	0.00
256	61	2025-05-20	175.00	50.00	125.00	Pagado	5	2025-04-08 16:09:59.007467-06	5	175.00	2025-04-08 15:59:54.505413-06	0.00
201	54	2025-04-03	1225.00	350.00	875.00	Pagado	5	2025-04-09 13:58:16.422736-06	5	8575.00	2025-04-03 22:35:10.768836-06	0.00
202	54	2025-04-10	1225.00	350.00	875.00	Pagado	5	2025-04-14 15:28:25.562649-06	5	7350.00	2025-04-03 22:35:10.774887-06	0.00
209	55	2025-04-04	175.00	50.00	125.00	Pagado	5	2025-04-14 15:29:01.906847-06	5	1225.00	2025-04-04 08:27:59.003379-06	0.00
257	61	2025-05-27	175.00	50.00	125.00	Pagado	5	2025-04-08 16:09:59.007467-06	5	0.00	2025-04-08 15:59:54.507859-06	0.00
258	62	2025-04-09	175.00	50.00	125.00	Pendiente	5	\N	\N	1225.00	2025-04-09 14:23:04.513666-06	\N
259	62	2025-04-16	175.00	50.00	125.00	Pendiente	5	\N	\N	1050.00	2025-04-09 14:23:04.520849-06	\N
260	62	2025-04-23	175.00	50.00	125.00	Pendiente	5	\N	\N	875.00	2025-04-09 14:23:04.524406-06	\N
261	62	2025-04-30	175.00	50.00	125.00	Pendiente	5	\N	\N	700.00	2025-04-09 14:23:04.526936-06	\N
262	62	2025-05-07	175.00	50.00	125.00	Pendiente	5	\N	\N	525.00	2025-04-09 14:23:04.529543-06	\N
263	62	2025-05-14	175.00	50.00	125.00	Pendiente	5	\N	\N	350.00	2025-04-09 14:23:04.534977-06	\N
264	62	2025-05-21	175.00	50.00	125.00	Pendiente	5	\N	\N	175.00	2025-04-09 14:23:04.537661-06	\N
265	62	2025-05-28	175.00	50.00	125.00	Pendiente	5	\N	\N	0.00	2025-04-09 14:23:04.540101-06	\N
267	64	2025-04-09	612.50	175.00	437.50	Pendiente	5	\N	\N	4287.50	2025-04-09 14:28:09.918622-06	\N
268	64	2025-04-16	612.50	175.00	437.50	Pendiente	5	\N	\N	3675.00	2025-04-09 14:28:09.922635-06	\N
269	64	2025-04-23	612.50	175.00	437.50	Pendiente	5	\N	\N	3062.50	2025-04-09 14:28:09.925198-06	\N
270	64	2025-04-30	612.50	175.00	437.50	Pendiente	5	\N	\N	2450.00	2025-04-09 14:28:09.927563-06	\N
271	64	2025-05-07	612.50	175.00	437.50	Pendiente	5	\N	\N	1837.50	2025-04-09 14:28:09.929903-06	\N
272	64	2025-05-14	612.50	175.00	437.50	Pendiente	5	\N	\N	1225.00	2025-04-09 14:28:09.932942-06	\N
273	64	2025-05-21	612.50	175.00	437.50	Pendiente	5	\N	\N	612.50	2025-04-09 14:28:09.936483-06	\N
274	64	2025-05-28	612.50	175.00	437.50	Pendiente	5	\N	\N	0.00	2025-04-09 14:28:09.938971-06	\N
275	65	2025-04-09	192.50	55.00	137.50	Pendiente	5	\N	\N	1347.50	2025-04-09 14:31:03.246433-06	\N
276	65	2025-04-16	192.50	55.00	137.50	Pendiente	5	\N	\N	1155.00	2025-04-09 14:31:03.249858-06	\N
277	65	2025-04-23	192.50	55.00	137.50	Pendiente	5	\N	\N	962.50	2025-04-09 14:31:03.252384-06	\N
278	65	2025-04-30	192.50	55.00	137.50	Pendiente	5	\N	\N	770.00	2025-04-09 14:31:03.254881-06	\N
279	65	2025-05-07	192.50	55.00	137.50	Pendiente	5	\N	\N	577.50	2025-04-09 14:31:03.258407-06	\N
280	65	2025-05-14	192.50	55.00	137.50	Pendiente	5	\N	\N	385.00	2025-04-09 14:31:03.261439-06	\N
281	65	2025-05-21	192.50	55.00	137.50	Pendiente	5	\N	\N	192.50	2025-04-09 14:31:03.263831-06	\N
282	65	2025-05-28	192.50	55.00	137.50	Pendiente	5	\N	\N	0.00	2025-04-09 14:31:03.266079-06	\N
283	66	2025-04-09	761.25	217.50	543.75	Pendiente	5	\N	\N	5328.75	2025-04-09 14:37:40.459199-06	\N
284	66	2025-04-16	761.25	217.50	543.75	Pendiente	5	\N	\N	4567.50	2025-04-09 14:37:40.46361-06	\N
285	66	2025-04-23	761.25	217.50	543.75	Pendiente	5	\N	\N	3806.25	2025-04-09 14:37:40.466214-06	\N
286	66	2025-04-30	761.25	217.50	543.75	Pendiente	5	\N	\N	3045.00	2025-04-09 14:37:40.468545-06	\N
287	66	2025-05-07	761.25	217.50	543.75	Pendiente	5	\N	\N	2283.75	2025-04-09 14:37:40.47231-06	\N
288	66	2025-05-14	761.25	217.50	543.75	Pendiente	5	\N	\N	1522.50	2025-04-09 14:37:40.475583-06	\N
289	66	2025-05-21	761.25	217.50	543.75	Pendiente	5	\N	\N	761.25	2025-04-09 14:37:40.478008-06	\N
290	66	2025-05-28	761.25	217.50	543.75	Pendiente	5	\N	\N	0.00	2025-04-09 14:37:40.480208-06	\N
291	67	2025-04-09	700.00	200.00	500.00	Pendiente	5	\N	\N	4900.00	2025-04-09 14:47:22.379584-06	\N
292	67	2025-04-16	700.00	200.00	500.00	Pendiente	5	\N	\N	4200.00	2025-04-09 14:47:22.382629-06	\N
293	67	2025-04-23	700.00	200.00	500.00	Pendiente	5	\N	\N	3500.00	2025-04-09 14:47:22.3851-06	\N
294	67	2025-04-30	700.00	200.00	500.00	Pendiente	5	\N	\N	2800.00	2025-04-09 14:47:22.387377-06	\N
295	67	2025-05-07	700.00	200.00	500.00	Pendiente	5	\N	\N	2100.00	2025-04-09 14:47:22.389998-06	\N
296	67	2025-05-14	700.00	200.00	500.00	Pendiente	5	\N	\N	1400.00	2025-04-09 14:47:22.394097-06	\N
297	67	2025-05-21	700.00	200.00	500.00	Pendiente	5	\N	\N	700.00	2025-04-09 14:47:22.397009-06	\N
298	67	2025-05-28	700.00	200.00	500.00	Pendiente	5	\N	\N	0.00	2025-04-09 14:47:22.399405-06	\N
299	68	2025-04-09	1050.00	300.00	750.00	Pendiente	5	\N	\N	7350.00	2025-04-09 15:00:31.473258-06	\N
300	68	2025-04-16	1050.00	300.00	750.00	Pendiente	5	\N	\N	6300.00	2025-04-09 15:00:31.476089-06	\N
301	68	2025-04-23	1050.00	300.00	750.00	Pendiente	5	\N	\N	5250.00	2025-04-09 15:00:31.47865-06	\N
302	68	2025-04-30	1050.00	300.00	750.00	Pendiente	5	\N	\N	4200.00	2025-04-09 15:00:31.481334-06	\N
303	68	2025-05-07	1050.00	300.00	750.00	Pendiente	5	\N	\N	3150.00	2025-04-09 15:00:31.484066-06	\N
304	68	2025-05-14	1050.00	300.00	750.00	Pendiente	5	\N	\N	2100.00	2025-04-09 15:00:31.488465-06	\N
305	68	2025-05-21	1050.00	300.00	750.00	Pendiente	5	\N	\N	1050.00	2025-04-09 15:00:31.491064-06	\N
306	68	2025-05-28	1050.00	300.00	750.00	Pendiente	5	\N	\N	0.00	2025-04-09 15:00:31.493584-06	\N
313	69	2025-05-21	3150.00	900.00	2250.00	Pendiente	5	\N	\N	3150.00	2025-04-09 15:16:09.653092-06	\N
314	69	2025-05-28	3150.00	900.00	2250.00	Pendiente	5	\N	\N	0.00	2025-04-09 15:16:09.655927-06	\N
307	69	2025-04-09	3150.00	900.00	2250.00	Pagado	5	2025-04-09 15:17:55.712068-06	5	22050.00	2025-04-09 15:16:09.634439-06	0.00
317	70	2025-04-26	700.00	200.00	500.00	Pagado	5	2025-04-14 14:47:23.292417-06	5	3500.00	2025-04-12 14:04:28.901735-06	0.00
308	69	2025-04-16	3150.00	900.00	2250.00	Pagado	5	2025-04-09 15:18:43.353094-06	5	18900.00	2025-04-09 15:16:09.639144-06	0.00
309	69	2025-04-23	3150.00	900.00	2250.00	Pagado	5	2025-04-09 15:18:43.353094-06	5	15750.00	2025-04-09 15:16:09.641868-06	0.00
315	70	2025-04-12	700.00	200.00	500.00	Pagado	5	2025-04-12 14:06:25.760816-06	5	4900.00	2025-04-12 14:04:28.876862-06	0.00
318	70	2025-05-03	700.00	200.00	500.00	Pagado	5	2025-04-14 14:51:56.499852-06	5	2800.00	2025-04-12 14:04:28.909772-06	0.00
316	70	2025-04-19	700.00	200.00	500.00	Pagado	5	2025-04-12 14:07:32.924218-06	5	4200.00	2025-04-12 14:04:28.890259-06	0.00
323	71	2025-04-14	175.00	50.00	125.00	Pagado	5	2025-04-14 14:55:57.59428-06	5	1225.00	2025-04-14 14:55:05.637553-06	0.00
319	70	2025-05-10	700.00	200.00	500.00	Pagado	5	2025-04-14 14:51:56.499852-06	5	2100.00	2025-04-12 14:04:28.91769-06	0.00
320	70	2025-05-17	700.00	200.00	500.00	Pagado	5	2025-04-14 14:51:56.499852-06	5	1400.00	2025-04-12 14:04:28.925601-06	0.00
321	70	2025-05-24	700.00	200.00	500.00	Pagado	5	2025-04-14 14:51:56.499852-06	5	700.00	2025-04-12 14:04:28.933729-06	0.00
322	70	2025-05-31	700.00	200.00	500.00	Pagado	5	2025-04-14 14:51:56.499852-06	5	0.00	2025-04-12 14:04:28.946147-06	0.00
324	71	2025-04-21	175.00	50.00	125.00	Pagado	5	2025-04-14 14:55:57.59428-06	5	1050.00	2025-04-14 14:55:05.647362-06	0.00
325	71	2025-04-28	175.00	50.00	125.00	Pagado	5	2025-04-14 14:55:57.59428-06	5	875.00	2025-04-14 14:55:05.655667-06	0.00
326	71	2025-05-05	175.00	50.00	125.00	Pagado	5	2025-04-14 14:55:57.59428-06	5	700.00	2025-04-14 14:55:05.658951-06	0.00
327	71	2025-05-12	175.00	50.00	125.00	Pagado	5	2025-04-14 14:55:57.59428-06	5	525.00	2025-04-14 14:55:05.667496-06	0.00
328	71	2025-05-19	175.00	50.00	125.00	Pagado	5	2025-04-14 14:55:57.59428-06	5	350.00	2025-04-14 14:55:05.674542-06	0.00
329	71	2025-05-26	175.00	50.00	125.00	Pagado	5	2025-04-14 14:55:57.59428-06	5	175.00	2025-04-14 14:55:05.677627-06	0.00
310	69	2025-04-30	3150.00	900.00	2250.00	Pagado	5	2025-04-14 15:29:41.122512-06	5	12600.00	2025-04-09 15:16:09.644235-06	0.00
311	69	2025-05-07	3150.00	900.00	2250.00	Pagado	5	2025-04-14 15:30:46.166276-06	5	9450.00	2025-04-09 15:16:09.646591-06	0.00
312	69	2025-05-14	3150.00	900.00	2250.00	Pagado	5	2025-04-14 15:31:29.842585-06	5	6300.00	2025-04-09 15:16:09.649054-06	0.00
330	71	2025-06-02	175.00	50.00	125.00	Pagado	5	2025-04-14 14:55:57.59428-06	5	0.00	2025-04-14 14:55:05.684941-06	0.00
331	72	2025-04-14	8.75	2.50	6.25	Pagado	5	2025-04-14 15:03:28.185362-06	5	61.25	2025-04-14 15:01:14.800989-06	0.00
332	72	2025-04-21	8.75	2.50	6.25	Pagado	5	2025-04-14 15:03:28.185362-06	5	52.50	2025-04-14 15:01:14.811896-06	0.00
333	72	2025-04-28	8.75	2.50	6.25	Pagado	5	2025-04-14 15:03:28.185362-06	5	43.75	2025-04-14 15:01:14.821401-06	0.00
334	72	2025-05-05	8.75	2.50	6.25	Pagado	5	2025-04-14 15:03:28.185362-06	5	35.00	2025-04-14 15:01:14.824796-06	0.00
335	72	2025-05-12	8.75	2.50	6.25	Pagado	5	2025-04-14 15:03:28.185362-06	5	26.25	2025-04-14 15:01:14.832666-06	0.00
336	72	2025-05-19	8.75	2.50	6.25	Pagado	5	2025-04-14 15:03:28.185362-06	5	17.50	2025-04-14 15:01:14.835357-06	0.00
337	72	2025-05-26	8.75	2.50	6.25	Pagado	5	2025-04-14 15:03:28.185362-06	5	8.75	2025-04-14 15:01:14.843095-06	0.00
338	72	2025-06-02	8.75	2.50	6.25	Pagado	5	2025-04-14 15:03:28.185362-06	5	0.00	2025-04-14 15:01:14.851117-06	0.00
339	73	2025-04-14	7.00	2.00	5.00	Pagado	5	2025-04-14 15:23:02.645419-06	5	49.00	2025-04-14 15:22:09.87788-06	0.00
340	73	2025-04-21	7.00	2.00	5.00	Pagado	5	2025-04-14 15:23:02.645419-06	5	42.00	2025-04-14 15:22:09.890905-06	0.00
341	73	2025-04-28	7.00	2.00	5.00	Pagado	5	2025-04-14 15:23:02.645419-06	5	35.00	2025-04-14 15:22:09.908791-06	0.00
342	73	2025-05-05	7.00	2.00	5.00	Pagado	5	2025-04-14 15:23:02.645419-06	5	28.00	2025-04-14 15:22:09.919083-06	0.00
343	73	2025-05-12	7.00	2.00	5.00	Pagado	5	2025-04-14 15:23:02.645419-06	5	21.00	2025-04-14 15:22:09.928315-06	0.00
344	73	2025-05-19	7.00	2.00	5.00	Pagado	5	2025-04-14 15:23:02.645419-06	5	14.00	2025-04-14 15:22:09.938301-06	0.00
345	73	2025-05-26	7.00	2.00	5.00	Pagado	5	2025-04-14 15:23:02.645419-06	5	7.00	2025-04-14 15:22:09.941579-06	0.00
346	73	2025-06-02	7.00	2.00	5.00	Pagado	5	2025-04-14 15:23:02.645419-06	5	0.00	2025-04-14 15:22:09.949746-06	0.00
347	74	2025-04-14	350.00	100.00	250.00	Pagado	5	2025-04-14 15:37:35.466249-06	5	2450.00	2025-04-14 15:36:58.139604-06	0.00
348	74	2025-04-21	350.00	100.00	250.00	Pagado	5	2025-04-14 15:37:35.466249-06	5	2100.00	2025-04-14 15:36:58.151412-06	0.00
349	74	2025-04-28	350.00	100.00	250.00	Pagado	5	2025-04-14 15:37:35.466249-06	5	1750.00	2025-04-14 15:36:58.17037-06	0.00
350	74	2025-05-05	350.00	100.00	250.00	Pagado	5	2025-04-14 15:37:35.466249-06	5	1400.00	2025-04-14 15:36:58.183482-06	0.00
351	74	2025-05-12	350.00	100.00	250.00	Pagado	5	2025-04-14 15:37:35.466249-06	5	1050.00	2025-04-14 15:36:58.201527-06	0.00
363	76	2025-04-14	525.00	150.00	375.00	Pagado	5	2025-04-14 16:00:51.399146-06	5	3675.00	2025-04-14 16:00:16.340907-06	0.00
352	74	2025-05-19	350.00	100.00	250.00	Pagado	5	2025-04-14 15:38:53.16345-06	5	700.00	2025-04-14 15:36:58.213315-06	0.00
353	74	2025-05-26	350.00	100.00	250.00	Pagado	5	2025-04-14 15:38:53.16345-06	5	350.00	2025-04-14 15:36:58.230407-06	0.00
354	74	2025-06-02	350.00	100.00	250.00	Pagado	5	2025-04-14 15:38:53.16345-06	5	0.00	2025-04-14 15:36:58.243397-06	0.00
355	75	2025-04-14	525.00	150.00	375.00	Pagado	5	2025-04-14 15:49:16.602991-06	5	3675.00	2025-04-14 15:48:47.032943-06	0.00
356	75	2025-04-21	525.00	150.00	375.00	Pagado	5	2025-04-14 15:53:40.061816-06	5	3150.00	2025-04-14 15:48:47.051964-06	0.00
357	75	2025-04-28	525.00	150.00	375.00	Pagado	5	2025-04-14 15:54:43.169792-06	5	2625.00	2025-04-14 15:48:47.071149-06	0.00
358	75	2025-05-05	525.00	150.00	375.00	Pagado	5	2025-04-14 15:55:23.961312-06	5	2100.00	2025-04-14 15:48:47.090315-06	0.00
359	75	2025-05-12	525.00	150.00	375.00	Pagado	5	2025-04-14 15:55:23.961312-06	5	1575.00	2025-04-14 15:48:47.102136-06	0.00
360	75	2025-05-19	525.00	150.00	375.00	Pagado	5	2025-04-14 15:55:23.961312-06	5	1050.00	2025-04-14 15:48:47.120791-06	0.00
361	75	2025-05-26	525.00	150.00	375.00	Pagado	5	2025-04-14 15:55:23.961312-06	5	525.00	2025-04-14 15:48:47.13252-06	0.00
362	75	2025-06-02	525.00	150.00	375.00	Pagado	5	2025-04-14 15:55:23.961312-06	5	0.00	2025-04-14 15:48:47.153309-06	0.00
364	76	2025-04-21	525.00	150.00	375.00	Pagado	5	2025-04-14 16:00:51.399146-06	5	3150.00	2025-04-14 16:00:16.360924-06	0.00
365	76	2025-04-28	525.00	150.00	375.00	Pagado	5	2025-04-14 16:00:51.399146-06	5	2625.00	2025-04-14 16:00:16.380785-06	0.00
366	76	2025-05-05	525.00	150.00	375.00	Pagado	5	2025-04-14 16:00:51.399146-06	5	2100.00	2025-04-14 16:00:16.400741-06	0.00
367	76	2025-05-12	525.00	150.00	375.00	Pagado	5	2025-04-14 16:00:51.399146-06	5	1575.00	2025-04-14 16:00:16.420653-06	0.00
368	76	2025-05-19	525.00	150.00	375.00	Pagado	5	2025-04-14 16:00:51.399146-06	5	1050.00	2025-04-14 16:00:16.439667-06	0.00
369	76	2025-05-26	525.00	150.00	375.00	Pagado	5	2025-04-14 16:00:51.399146-06	5	525.00	2025-04-14 16:00:16.460928-06	0.00
370	76	2025-06-02	525.00	150.00	375.00	Pagado	5	2025-04-14 16:00:51.399146-06	5	0.00	2025-04-14 16:00:16.480338-06	0.00
375	77	2025-05-12	875.00	250.00	625.00	Pendiente	5	\N	\N	2625.00	2025-04-14 21:13:23.846297-06	\N
376	77	2025-05-19	875.00	250.00	625.00	Pendiente	5	\N	\N	1750.00	2025-04-14 21:13:23.849615-06	\N
377	77	2025-05-26	875.00	250.00	625.00	Pendiente	5	\N	\N	875.00	2025-04-14 21:13:23.852428-06	\N
378	77	2025-06-02	875.00	250.00	625.00	Pendiente	5	\N	\N	0.00	2025-04-14 21:13:23.855236-06	\N
371	77	2025-04-14	875.00	250.00	625.00	Pagado	5	2025-04-14 21:15:37.97259-06	5	6125.00	2025-04-14 21:13:23.817637-06	0.00
372	77	2025-04-21	875.00	250.00	625.00	Pagado	5	2025-04-14 21:17:17.339259-06	5	5250.00	2025-04-14 21:13:23.827837-06	0.00
373	77	2025-04-28	875.00	250.00	625.00	Pagado	5	2025-04-14 21:18:33.066155-06	5	4375.00	2025-04-14 21:13:23.837398-06	0.00
374	77	2025-05-05	875.00	250.00	625.00	Pagado	5	2025-04-14 21:19:18.790271-06	5	3500.00	2025-04-14 21:13:23.843009-06	0.00
379	78	2025-04-14	175.00	50.00	125.00	Pagado	5	2025-04-14 22:31:14.887162-06	5	1225.00	2025-04-14 22:29:10.786379-06	0.00
380	78	2025-04-21	175.00	50.00	125.00	Pagado	5	2025-04-14 22:32:44.125088-06	5	1050.00	2025-04-14 22:29:10.798142-06	0.00
381	78	2025-04-28	175.00	50.00	125.00	Pagado	5	2025-04-14 22:32:44.125088-06	5	875.00	2025-04-14 22:29:10.808194-06	0.00
382	78	2025-05-05	175.00	50.00	125.00	Pagado	5	2025-04-14 22:32:44.125088-06	5	700.00	2025-04-14 22:29:10.817229-06	0.00
383	78	2025-05-12	175.00	50.00	125.00	Pagado	5	2025-04-14 22:32:44.125088-06	5	525.00	2025-04-14 22:29:10.826607-06	0.00
384	78	2025-05-19	175.00	50.00	125.00	Pagado	5	2025-04-14 22:32:44.125088-06	5	350.00	2025-04-14 22:29:10.836388-06	0.00
385	78	2025-05-26	175.00	50.00	125.00	Pagado	5	2025-04-14 22:32:44.125088-06	5	175.00	2025-04-14 22:29:10.846521-06	0.00
386	78	2025-06-02	175.00	50.00	125.00	Pagado	5	2025-04-14 22:32:44.125088-06	5	0.00	2025-04-14 22:29:10.853512-06	0.00
387	79	2025-05-02	78.75	22.50	56.25	Pendiente	5	\N	\N	551.25	2025-04-26 12:11:41.75086-06	\N
388	79	2025-05-09	78.75	22.50	56.25	Pendiente	5	\N	\N	472.50	2025-04-26 12:11:41.759307-06	\N
389	79	2025-05-16	78.75	22.50	56.25	Pendiente	5	\N	\N	393.75	2025-04-26 12:11:41.765468-06	\N
390	79	2025-05-23	78.75	22.50	56.25	Pendiente	5	\N	\N	315.00	2025-04-26 12:11:41.771759-06	\N
391	79	2025-05-30	78.75	22.50	56.25	Pendiente	5	\N	\N	236.25	2025-04-26 12:11:41.777283-06	\N
392	79	2025-06-06	78.75	22.50	56.25	Pendiente	5	\N	\N	157.50	2025-04-26 12:11:41.784966-06	\N
393	79	2025-06-13	78.75	22.50	56.25	Pendiente	5	\N	\N	78.75	2025-04-26 12:11:41.792358-06	\N
394	79	2025-06-20	78.75	22.50	56.25	Pendiente	5	\N	\N	0.00	2025-04-26 12:11:41.798831-06	\N
395	80	2025-05-02	61.25	17.50	43.75	Pendiente	5	\N	\N	428.75	2025-04-26 12:52:14.886415-06	\N
396	80	2025-05-09	61.25	17.50	43.75	Pendiente	5	\N	\N	367.50	2025-04-26 12:52:14.892711-06	\N
397	80	2025-05-16	61.25	17.50	43.75	Pendiente	5	\N	\N	306.25	2025-04-26 12:52:14.895869-06	\N
398	80	2025-05-23	61.25	17.50	43.75	Pendiente	5	\N	\N	245.00	2025-04-26 12:52:14.89978-06	\N
399	80	2025-05-30	61.25	17.50	43.75	Pendiente	5	\N	\N	183.75	2025-04-26 12:52:14.904669-06	\N
400	80	2025-06-06	61.25	17.50	43.75	Pendiente	5	\N	\N	122.50	2025-04-26 12:52:14.908379-06	\N
401	80	2025-06-13	61.25	17.50	43.75	Pendiente	5	\N	\N	61.25	2025-04-26 12:52:14.911704-06	\N
402	80	2025-06-20	61.25	17.50	43.75	Pendiente	5	\N	\N	0.00	2025-04-26 12:52:14.914929-06	\N
404	81	2025-05-09	350.00	100.00	250.00	Pagado	5	2025-04-26 12:58:00.037965-06	5	2100.00	2025-04-26 12:56:24.87752-06	0.00
406	81	2025-05-23	350.00	100.00	250.00	Pendiente	5	\N	\N	1400.00	2025-04-26 12:56:24.886984-06	\N
407	81	2025-05-30	350.00	100.00	250.00	Pendiente	5	\N	\N	1050.00	2025-04-26 12:56:24.893219-06	\N
408	81	2025-06-06	350.00	100.00	250.00	Pendiente	5	\N	\N	700.00	2025-04-26 12:56:24.89642-06	\N
409	81	2025-06-13	350.00	100.00	250.00	Pendiente	5	\N	\N	350.00	2025-04-26 12:56:24.900608-06	\N
410	81	2025-06-20	350.00	100.00	250.00	Pendiente	5	\N	\N	0.00	2025-04-26 12:56:24.905494-06	\N
403	81	2025-05-02	350.00	100.00	250.00	Pagado	5	2025-04-26 12:57:38.928737-06	5	2450.00	2025-04-26 12:56:24.868721-06	0.00
458	90	2025-05-22	866.67	200.00	666.67	Pagado	5	2025-05-24 12:01:48.324501-06	5	4333.33	2025-05-16 22:12:03.775458-06	0.00
411	82	2025-05-19	1333.33	500.00	833.33	Pendiente	5	\N	\N	14666.67	2025-05-06 12:02:55.993597-06	\N
412	82	2025-05-26	1333.33	500.00	833.33	Pendiente	5	\N	\N	13333.33	2025-05-06 12:02:55.999201-06	\N
413	82	2025-06-02	1333.33	500.00	833.33	Pendiente	5	\N	\N	12000.00	2025-05-06 12:02:56.005026-06	\N
414	82	2025-06-09	1333.33	500.00	833.33	Pendiente	5	\N	\N	10666.67	2025-05-06 12:02:56.010186-06	\N
415	82	2025-06-16	1333.33	500.00	833.33	Pendiente	5	\N	\N	9333.33	2025-05-06 12:02:56.015654-06	\N
416	82	2025-06-23	1333.33	500.00	833.33	Pendiente	5	\N	\N	8000.00	2025-05-06 12:02:56.020839-06	\N
417	82	2025-06-30	1333.33	500.00	833.33	Pendiente	5	\N	\N	6666.67	2025-05-06 12:02:56.026249-06	\N
418	82	2025-07-07	1333.33	500.00	833.33	Pendiente	5	\N	\N	5333.33	2025-05-06 12:02:56.031571-06	\N
419	82	2025-07-14	1333.33	500.00	833.33	Pendiente	5	\N	\N	4000.00	2025-05-06 12:02:56.036749-06	\N
420	82	2025-07-21	1333.33	500.00	833.33	Pendiente	5	\N	\N	2666.67	2025-05-06 12:02:56.042186-06	\N
421	82	2025-07-28	1333.33	500.00	833.33	Pendiente	5	\N	\N	1333.33	2025-05-06 12:02:56.04753-06	\N
422	82	2025-08-04	1333.33	500.00	833.33	Pendiente	5	\N	\N	0.00	2025-05-06 12:02:56.052016-06	\N
423	83	2025-05-12	3675.00	1050.00	2625.00	Pagado	5	2025-05-06 13:12:42.5781-06	5	25725.00	2025-05-06 12:59:14.985798-06	0.00
459	90	2025-05-29	866.67	200.00	666.67	Pendiente	5	2025-05-24 12:01:48.324501-06	5	3466.67	2025-05-16 22:12:03.786402-06	866.34
424	83	2025-05-19	3675.00	1050.00	2625.00	Pagado	5	2025-05-06 16:53:21.375558-06	5	22050.00	2025-05-06 12:59:14.994684-06	0.00
425	83	2025-05-26	3675.00	1050.00	2625.00	Pagado	5	2025-05-06 16:53:21.375558-06	5	18375.00	2025-05-06 12:59:14.997968-06	0.00
426	83	2025-06-02	3675.00	1050.00	2625.00	Pagado	5	2025-05-06 16:53:21.375558-06	5	14700.00	2025-05-06 12:59:15.00185-06	0.00
427	83	2025-06-09	3675.00	1050.00	2625.00	Pagado	5	2025-05-06 16:53:21.375558-06	5	11025.00	2025-05-06 12:59:15.005663-06	0.00
428	83	2025-06-16	3675.00	1050.00	2625.00	Pagado	5	2025-05-06 16:53:21.375558-06	5	7350.00	2025-05-06 12:59:15.008556-06	0.00
429	83	2025-06-23	3675.00	1050.00	2625.00	Pagado	5	2025-05-06 16:53:21.375558-06	5	3675.00	2025-05-06 12:59:15.011291-06	0.00
430	83	2025-06-30	3675.00	1050.00	2625.00	Pagado	5	2025-05-06 16:53:21.375558-06	5	0.00	2025-05-06 12:59:15.014107-06	0.00
431	84	2025-03-07	875.00	250.00	625.00	Pendiente	5	\N	\N	6125.00	2025-05-06 17:31:24.797628-06	\N
432	84	2025-03-14	875.00	250.00	625.00	Pendiente	5	\N	\N	5250.00	2025-05-06 17:31:24.805198-06	\N
433	84	2025-03-21	875.00	250.00	625.00	Pendiente	5	\N	\N	4375.00	2025-05-06 17:31:24.809592-06	\N
434	84	2025-03-28	875.00	250.00	625.00	Pendiente	5	\N	\N	3500.00	2025-05-06 17:31:24.815009-06	\N
435	84	2025-04-04	875.00	250.00	625.00	Pendiente	5	\N	\N	2625.00	2025-05-06 17:31:24.821172-06	\N
436	84	2025-04-11	875.00	250.00	625.00	Pendiente	5	\N	\N	1750.00	2025-05-06 17:31:24.826977-06	\N
437	84	2025-04-18	875.00	250.00	625.00	Pendiente	5	\N	\N	875.00	2025-05-06 17:31:24.832274-06	\N
438	84	2025-04-25	875.00	250.00	625.00	Pendiente	5	\N	\N	0.00	2025-05-06 17:31:24.837337-06	\N
442	85	2025-06-08	3150.00	900.00	2250.00	Pendiente	5	\N	\N	12600.00	2025-05-10 00:34:33.576333-06	\N
443	85	2025-06-15	3150.00	900.00	2250.00	Pendiente	5	\N	\N	9450.00	2025-05-10 00:34:33.579797-06	\N
444	85	2025-06-22	3150.00	900.00	2250.00	Pendiente	5	\N	\N	6300.00	2025-05-10 00:34:33.583849-06	\N
445	85	2025-06-29	3150.00	900.00	2250.00	Pendiente	5	\N	\N	3150.00	2025-05-10 00:34:33.587205-06	\N
446	85	2025-07-06	3150.00	900.00	2250.00	Pendiente	5	\N	\N	0.00	2025-05-10 00:34:33.590272-06	\N
439	85	2025-05-18	3150.00	900.00	2250.00	Pagado	5	2025-05-10 00:37:29.139916-06	5	22050.00	2025-05-10 00:34:33.564783-06	0.00
440	85	2025-05-25	3150.00	900.00	2250.00	Pagado	5	2025-05-10 01:24:06.599985-06	5	18900.00	2025-05-10 00:34:33.56962-06	0.00
465	91	2025-04-15	700.00	200.00	500.00	Pendiente	5	\N	\N	4200.00	2025-06-03 12:46:34.613674-06	\N
466	91	2025-04-22	700.00	200.00	500.00	Pendiente	5	\N	\N	3500.00	2025-06-03 12:46:34.616556-06	\N
405	81	2025-05-16	350.00	100.00	250.00	Pagado	5	2025-05-12 12:44:21.739043-06	5	1750.00	2025-04-26 12:56:24.882434-06	0.00
441	85	2025-06-01	3150.00	900.00	2250.00	Pendiente	5	2025-05-12 12:50:29.035341-06	5	15750.00	2025-05-10 00:34:33.572553-06	107.14
450	87	2025-05-22	866.67	200.00	666.67	Pendiente	5	\N	\N	4333.33	2025-05-16 21:46:13.781518-06	\N
451	87	2025-05-29	866.67	200.00	666.67	Pendiente	5	\N	\N	3466.67	2025-05-16 21:46:13.787921-06	\N
452	87	2025-06-05	866.67	200.00	666.67	Pendiente	5	\N	\N	2600.00	2025-05-16 21:46:13.791824-06	\N
453	87	2025-06-12	866.67	200.00	666.67	Pendiente	5	\N	\N	1733.33	2025-05-16 21:46:13.795567-06	\N
454	87	2025-06-19	866.67	200.00	666.67	Pendiente	5	\N	\N	866.67	2025-05-16 21:46:13.798444-06	\N
455	87	2025-06-26	866.67	200.00	666.67	Pendiente	5	\N	\N	0.00	2025-05-16 21:46:13.801555-06	\N
460	90	2025-06-05	866.67	200.00	666.67	Pendiente	5	\N	\N	2600.00	2025-05-16 22:12:03.795287-06	\N
461	90	2025-06-12	866.67	200.00	666.67	Pendiente	5	\N	\N	1733.33	2025-05-16 22:12:03.801409-06	\N
462	90	2025-06-19	866.67	200.00	666.67	Pendiente	5	\N	\N	866.67	2025-05-16 22:12:03.806623-06	\N
463	90	2025-06-26	866.67	200.00	666.67	Pendiente	5	\N	\N	0.00	2025-05-16 22:12:03.810297-06	\N
467	91	2025-04-29	700.00	200.00	500.00	Pendiente	5	\N	\N	2800.00	2025-06-03 12:46:34.620406-06	\N
468	91	2025-05-06	700.00	200.00	500.00	Pendiente	5	\N	\N	2100.00	2025-06-03 12:46:34.624039-06	\N
469	91	2025-05-13	700.00	200.00	500.00	Pendiente	5	\N	\N	1400.00	2025-06-03 12:46:34.627053-06	\N
470	91	2025-05-20	700.00	200.00	500.00	Pendiente	5	\N	\N	700.00	2025-06-03 12:46:34.630024-06	\N
471	91	2025-05-27	700.00	200.00	500.00	Pendiente	5	\N	\N	0.00	2025-06-03 12:46:34.633348-06	\N
464	91	2025-04-08	700.00	200.00	500.00	Pagado	5	2025-06-04 16:19:43.049366-06	5	4900.00	2025-06-03 12:46:34.609341-06	0.00
472	92	2025-06-22	1200.00	200.00	1000.00	Pendiente	5	\N	\N	3600.00	2025-06-15 13:13:06.688186-06	\N
473	92	2025-06-29	1200.00	200.00	1000.00	Pendiente	5	\N	\N	2400.00	2025-06-15 13:13:06.695856-06	\N
474	92	2025-07-06	1200.00	200.00	1000.00	Pendiente	5	\N	\N	1200.00	2025-06-15 13:13:06.700007-06	\N
475	92	2025-07-13	1200.00	200.00	1000.00	Pendiente	5	\N	\N	0.00	2025-06-15 13:13:06.704231-06	\N
476	93	2025-06-22	200.00	33.33	166.67	Pendiente	5	\N	\N	5800.00	2025-06-15 13:27:10.650136-06	\N
477	93	2025-06-23	200.00	33.33	166.67	Pendiente	5	\N	\N	5600.00	2025-06-15 13:27:10.659553-06	\N
478	93	2025-06-24	200.00	33.33	166.67	Pendiente	5	\N	\N	5400.00	2025-06-15 13:27:10.663646-06	\N
479	93	2025-06-25	200.00	33.33	166.67	Pendiente	5	\N	\N	5200.00	2025-06-15 13:27:10.667634-06	\N
480	93	2025-06-26	200.00	33.33	166.67	Pendiente	5	\N	\N	5000.00	2025-06-15 13:27:10.671141-06	\N
481	93	2025-06-27	200.00	33.33	166.67	Pendiente	5	\N	\N	4800.00	2025-06-15 13:27:10.674438-06	\N
482	93	2025-06-28	200.00	33.33	166.67	Pendiente	5	\N	\N	4600.00	2025-06-15 13:27:10.67735-06	\N
483	93	2025-06-29	200.00	33.33	166.67	Pendiente	5	\N	\N	4400.00	2025-06-15 13:27:10.680201-06	\N
484	93	2025-06-30	200.00	33.33	166.67	Pendiente	5	\N	\N	4200.00	2025-06-15 13:27:10.683026-06	\N
485	93	2025-07-01	200.00	33.33	166.67	Pendiente	5	\N	\N	4000.00	2025-06-15 13:27:10.688324-06	\N
486	93	2025-07-02	200.00	33.33	166.67	Pendiente	5	\N	\N	3800.00	2025-06-15 13:27:10.691183-06	\N
487	93	2025-07-03	200.00	33.33	166.67	Pendiente	5	\N	\N	3600.00	2025-06-15 13:27:10.694049-06	\N
488	93	2025-07-04	200.00	33.33	166.67	Pendiente	5	\N	\N	3400.00	2025-06-15 13:27:10.696834-06	\N
489	93	2025-07-05	200.00	33.33	166.67	Pendiente	5	\N	\N	3200.00	2025-06-15 13:27:10.700316-06	\N
490	93	2025-07-06	200.00	33.33	166.67	Pendiente	5	\N	\N	3000.00	2025-06-15 13:27:10.703316-06	\N
491	93	2025-07-07	200.00	33.33	166.67	Pendiente	5	\N	\N	2800.00	2025-06-15 13:27:10.706318-06	\N
492	93	2025-07-08	200.00	33.33	166.67	Pendiente	5	\N	\N	2600.00	2025-06-15 13:27:10.709234-06	\N
493	93	2025-07-09	200.00	33.33	166.67	Pendiente	5	\N	\N	2400.00	2025-06-15 13:27:10.711988-06	\N
494	93	2025-07-10	200.00	33.33	166.67	Pendiente	5	\N	\N	2200.00	2025-06-15 13:27:10.714784-06	\N
495	93	2025-07-11	200.00	33.33	166.67	Pendiente	5	\N	\N	2000.00	2025-06-15 13:27:10.717561-06	\N
496	93	2025-07-12	200.00	33.33	166.67	Pendiente	5	\N	\N	1800.00	2025-06-15 13:27:10.720712-06	\N
497	93	2025-07-13	200.00	33.33	166.67	Pendiente	5	\N	\N	1600.00	2025-06-15 13:27:10.723741-06	\N
498	93	2025-07-14	200.00	33.33	166.67	Pendiente	5	\N	\N	1400.00	2025-06-15 13:27:10.726563-06	\N
499	93	2025-07-15	200.00	33.33	166.67	Pendiente	5	\N	\N	1200.00	2025-06-15 13:27:10.729285-06	\N
500	93	2025-07-16	200.00	33.33	166.67	Pendiente	5	\N	\N	1000.00	2025-06-15 13:27:10.732081-06	\N
501	93	2025-07-17	200.00	33.33	166.67	Pendiente	5	\N	\N	800.00	2025-06-15 13:27:10.734798-06	\N
502	93	2025-07-18	200.00	33.33	166.67	Pendiente	5	\N	\N	600.00	2025-06-15 13:27:10.737848-06	\N
503	93	2025-07-19	200.00	33.33	166.67	Pendiente	5	\N	\N	400.00	2025-06-15 13:27:10.740673-06	\N
504	93	2025-07-20	200.00	33.33	166.67	Pendiente	5	\N	\N	200.00	2025-06-15 13:27:10.743497-06	\N
505	93	2025-07-21	200.00	33.33	166.67	Pendiente	5	\N	\N	0.00	2025-06-15 13:27:10.746267-06	\N
511	94	2025-06-25	164.00	27.33	136.67	Pendiente	5	\N	\N	4264.00	2025-06-15 21:03:17.123414-06	\N
512	94	2025-06-26	164.00	27.33	136.67	Pendiente	5	\N	\N	4100.00	2025-06-15 21:03:17.126694-06	\N
513	94	2025-06-27	164.00	27.33	136.67	Pendiente	5	\N	\N	3936.00	2025-06-15 21:03:17.12983-06	\N
514	94	2025-06-28	164.00	27.33	136.67	Pendiente	5	\N	\N	3772.00	2025-06-15 21:03:17.133-06	\N
515	94	2025-06-29	164.00	27.33	136.67	Pendiente	5	\N	\N	3608.00	2025-06-15 21:03:17.136205-06	\N
516	94	2025-06-30	164.00	27.33	136.67	Pendiente	5	\N	\N	3444.00	2025-06-15 21:03:17.140544-06	\N
517	94	2025-07-01	164.00	27.33	136.67	Pendiente	5	\N	\N	3280.00	2025-06-15 21:03:17.143607-06	\N
518	94	2025-07-02	164.00	27.33	136.67	Pendiente	5	\N	\N	3116.00	2025-06-15 21:03:17.146945-06	\N
519	94	2025-07-03	164.00	27.33	136.67	Pendiente	5	\N	\N	2952.00	2025-06-15 21:03:17.150064-06	\N
520	94	2025-07-04	164.00	27.33	136.67	Pendiente	5	\N	\N	2788.00	2025-06-15 21:03:17.153886-06	\N
521	94	2025-07-05	164.00	27.33	136.67	Pendiente	5	\N	\N	2624.00	2025-06-15 21:03:17.159225-06	\N
522	94	2025-07-06	164.00	27.33	136.67	Pendiente	5	\N	\N	2460.00	2025-06-15 21:03:17.163255-06	\N
523	94	2025-07-07	164.00	27.33	136.67	Pendiente	5	\N	\N	2296.00	2025-06-15 21:03:17.166743-06	\N
524	94	2025-07-08	164.00	27.33	136.67	Pendiente	5	\N	\N	2132.00	2025-06-15 21:03:17.170362-06	\N
525	94	2025-07-09	164.00	27.33	136.67	Pendiente	5	\N	\N	1968.00	2025-06-15 21:03:17.17371-06	\N
526	94	2025-07-10	164.00	27.33	136.67	Pendiente	5	\N	\N	1804.00	2025-06-15 21:03:17.177077-06	\N
527	94	2025-07-11	164.00	27.33	136.67	Pendiente	5	\N	\N	1640.00	2025-06-15 21:03:17.180419-06	\N
528	94	2025-07-12	164.00	27.33	136.67	Pendiente	5	\N	\N	1476.00	2025-06-15 21:03:17.183813-06	\N
529	94	2025-07-13	164.00	27.33	136.67	Pendiente	5	\N	\N	1312.00	2025-06-15 21:03:17.187594-06	\N
530	94	2025-07-14	164.00	27.33	136.67	Pendiente	5	\N	\N	1148.00	2025-06-15 21:03:17.191167-06	\N
531	94	2025-07-15	164.00	27.33	136.67	Pendiente	5	\N	\N	984.00	2025-06-15 21:03:17.194708-06	\N
532	94	2025-07-16	164.00	27.33	136.67	Pendiente	5	\N	\N	820.00	2025-06-15 21:03:17.198034-06	\N
533	94	2025-07-17	164.00	27.33	136.67	Pendiente	5	\N	\N	656.00	2025-06-15 21:03:17.201282-06	\N
534	94	2025-07-18	164.00	27.33	136.67	Pendiente	5	\N	\N	492.00	2025-06-15 21:03:17.206035-06	\N
535	94	2025-07-19	164.00	27.33	136.67	Pendiente	5	\N	\N	328.00	2025-06-15 21:03:17.209186-06	\N
536	94	2025-07-20	164.00	27.33	136.67	Pendiente	5	\N	\N	164.00	2025-06-15 21:03:17.212435-06	\N
537	94	2025-07-21	164.00	27.33	136.67	Pendiente	5	\N	\N	0.00	2025-06-15 21:03:17.217743-06	\N
508	94	2025-06-22	164.00	27.33	136.67	Pagado	5	2025-06-15 21:21:58.384357-06	5	4756.00	2025-06-15 21:03:17.104446-06	0.00
509	94	2025-06-23	164.00	27.33	136.67	Pagado	5	2025-06-15 21:22:18.367496-06	5	4592.00	2025-06-15 21:03:17.112112-06	0.00
510	94	2025-06-24	164.00	27.33	136.67	Pagado	5	2025-06-15 21:23:02.597998-06	5	4428.00	2025-06-15 21:03:17.118602-06	0.00
539	95	2025-07-07	2520.00	420.00	2100.00	Pendiente	5	\N	\N	0.00	2025-06-15 21:30:43.628305-06	\N
538	95	2025-06-22	2520.00	420.00	2100.00	Pagado	5	2025-06-15 21:32:50.238111-06	5	2520.00	2025-06-15 21:30:43.620133-06	0.00
550	99	2025-06-30	717.50	205.00	512.50	Pagado	5	2025-06-30 09:28:58.681333-06	5	5022.50	2025-06-30 09:20:41.790168-06	0.00
540	96	2025-06-22	5160.00	860.00	4300.00	Pendiente	5	2025-06-15 21:38:15.854195-06	5	0.00	2025-06-15 21:36:38.685671-06	2160.00
543	98	2025-07-08	875.00	250.00	625.00	Pendiente	5	\N	\N	5250.00	2025-06-24 10:44:32.801535-06	\N
544	98	2025-07-15	875.00	250.00	625.00	Pendiente	5	\N	\N	4375.00	2025-06-24 10:44:32.812427-06	\N
545	98	2025-07-22	875.00	250.00	625.00	Pendiente	5	\N	\N	3500.00	2025-06-24 10:44:32.823037-06	\N
546	98	2025-07-29	875.00	250.00	625.00	Pendiente	5	\N	\N	2625.00	2025-06-24 10:44:32.833472-06	\N
547	98	2025-08-05	875.00	250.00	625.00	Pendiente	5	\N	\N	1750.00	2025-06-24 10:44:32.841853-06	\N
548	98	2025-08-12	875.00	250.00	625.00	Pendiente	5	\N	\N	875.00	2025-06-24 10:44:32.848879-06	\N
549	98	2025-08-19	875.00	250.00	625.00	Pendiente	5	\N	\N	0.00	2025-06-24 10:44:32.856367-06	\N
542	98	2025-07-01	875.00	250.00	625.00	Pagado	5	2025-06-24 10:45:42.014672-06	5	6125.00	2025-06-24 10:44:32.788746-06	0.00
551	99	2025-07-07	717.50	205.00	512.50	Pendiente	5	\N	\N	4305.00	2025-06-30 09:20:41.794356-06	\N
552	99	2025-07-14	717.50	205.00	512.50	Pendiente	5	\N	\N	3587.50	2025-06-30 09:20:41.797225-06	\N
553	99	2025-07-21	717.50	205.00	512.50	Pendiente	5	\N	\N	2870.00	2025-06-30 09:20:41.799945-06	\N
554	99	2025-07-28	717.50	205.00	512.50	Pendiente	5	\N	\N	2152.50	2025-06-30 09:20:41.802726-06	\N
555	99	2025-08-04	717.50	205.00	512.50	Pendiente	5	\N	\N	1435.00	2025-06-30 09:20:41.806096-06	\N
556	99	2025-08-11	717.50	205.00	512.50	Pendiente	5	\N	\N	717.50	2025-06-30 09:20:41.808849-06	\N
557	99	2025-08-18	717.50	205.00	512.50	Pendiente	5	\N	\N	0.00	2025-06-30 09:20:41.811746-06	\N
558	100	2025-06-30	840.00	240.00	600.00	Pendiente	5	\N	\N	5880.00	2025-06-30 13:10:29.583233-06	\N
559	100	2025-07-07	840.00	240.00	600.00	Pendiente	5	\N	\N	5040.00	2025-06-30 13:10:29.589891-06	\N
560	100	2025-07-14	840.00	240.00	600.00	Pendiente	5	\N	\N	4200.00	2025-06-30 13:10:29.59321-06	\N
561	100	2025-07-21	840.00	240.00	600.00	Pendiente	5	\N	\N	3360.00	2025-06-30 13:10:29.596361-06	\N
562	100	2025-07-28	840.00	240.00	600.00	Pendiente	5	\N	\N	2520.00	2025-06-30 13:10:29.599287-06	\N
563	100	2025-08-04	840.00	240.00	600.00	Pendiente	5	\N	\N	1680.00	2025-06-30 13:10:29.602304-06	\N
564	100	2025-08-11	840.00	240.00	600.00	Pendiente	5	\N	\N	840.00	2025-06-30 13:10:29.606164-06	\N
565	100	2025-08-18	840.00	240.00	600.00	Pendiente	5	\N	\N	0.00	2025-06-30 13:10:29.609299-06	\N
569	101	2025-07-20	700.00	200.00	500.00	Pendiente	5	\N	\N	2800.00	2025-06-30 14:14:03.109374-06	\N
570	101	2025-07-27	700.00	200.00	500.00	Pendiente	5	\N	\N	2100.00	2025-06-30 14:14:03.112292-06	\N
571	101	2025-08-03	700.00	200.00	500.00	Pendiente	5	\N	\N	1400.00	2025-06-30 14:14:03.115256-06	\N
572	101	2025-08-10	700.00	200.00	500.00	Pendiente	5	\N	\N	700.00	2025-06-30 14:14:03.118161-06	\N
573	101	2025-08-17	700.00	200.00	500.00	Pendiente	5	\N	\N	0.00	2025-06-30 14:14:03.120886-06	\N
566	101	2025-06-29	700.00	200.00	500.00	Pagado	5	2025-07-06 21:36:40.068787-06	5	4900.00	2025-06-30 14:14:03.095854-06	0.00
567	101	2025-07-06	700.00	200.00	500.00	Pendiente	5	2025-07-29 22:32:31.209947-06	5	4200.00	2025-06-30 14:14:03.101121-06	700.00
568	101	2025-07-13	700.00	200.00	500.00	Pendiente	5	2025-07-29 22:32:31.209947-06	5	3500.00	2025-06-30 14:14:03.105157-06	700.00
576	102	2025-10-13	700.00	200.00	500.00	Pendiente	5	\N	\N	3500.00	2025-09-20 19:49:06.643675-06	\N
577	102	2025-10-20	700.00	200.00	500.00	Pendiente	5	\N	\N	2800.00	2025-09-20 19:49:06.649343-06	\N
578	102	2025-10-27	700.00	200.00	500.00	Pendiente	5	\N	\N	2100.00	2025-09-20 19:49:06.655853-06	\N
579	102	2025-11-03	700.00	200.00	500.00	Pendiente	5	\N	\N	1400.00	2025-09-20 19:49:06.661502-06	\N
580	102	2025-11-10	700.00	200.00	500.00	Pendiente	5	\N	\N	700.00	2025-09-20 19:49:06.666219-06	\N
581	102	2025-11-17	700.00	200.00	500.00	Pendiente	5	\N	\N	0.00	2025-09-20 19:49:06.671347-06	\N
574	102	2025-09-29	700.00	200.00	500.00	Pagado	5	2025-09-20 19:51:05.785128-06	5	4900.00	2025-09-20 19:49:06.626644-06	0.00
575	102	2025-10-06	700.00	200.00	500.00	Pendiente	5	2025-09-20 19:51:05.785128-06	5	4200.00	2025-09-20 19:49:06.636179-06	400.00
\.


--
-- Data for Name: clientes; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.clientes (idcliente, cedula, nombre, telefono, estado_civil, actividad_economica, direccion_domicilio, tipo_vivienda, anos_habitar, direccion_negocio, tipo_local, tiempo_operar, rubro, idcartera, fecha_creo, usuario_creo, fecha_modifico, usuario_modifico) FROM stdin;
2	0011106900034B	Francisco	999999999	soltero	produccion	Laureles	renta	1	Veracruz	renta	8	produccion	1	2025-02-20 00:00:00-06	5	\N	\N
3	0011106900035B	Vicent	77777777	soltero	produccion	Villa Milagro	propio	8	Villa Milagro	propio	10	produccion	1	2025-02-20 00:00:00-06	5	\N	\N
4	0011106900439B	Kevin	55555555	casado	servicio	nuevo domicilio	propio	1	nuevo negocio	albergue	6	comercio	1	2025-02-20 00:00:00-06	5	\N	\N
1	0011106900032B	Jhonny francisco	8888888	casado	servicio	Villa Milagro	propio	7	Villa Milagro	propio	6	servicio	1	2025-02-18 00:00:00-06	5	2025-02-20 16:44:01.981159-06	5
5	0011106932B	car	4444444444	soltero	servicio	Villa Milagro	albergue	3	Villa Milagro	propio	6	comercio	1	2025-02-24 16:38:15.88512-06	5	\N	\N
6	1234	Francisco	999999999	soltero	produccion	Villa Milagro	propio	10	Villa Milagro	propio	6	servicio	1	2025-03-25 15:31:04.51553-06	5	\N	\N
7	00111069032F	kevin Arnold Rivera Gomez	8888888	soltero	Barberia	laureles	propio	1	Villa Milagro	propio	36	servicio	1	2025-03-29 10:22:53.076691-06	5	\N	\N
8	1234567890981a	cliente 1	88888888	soltero	servicio	test	propio	7	test	propio	6	servicio	1	2025-10-25 11:01:36.288735-06	30	\N	\N
\.


--
-- Data for Name: configuracion_costo_venta; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.configuracion_costo_venta (id_config, rubro, margen_venta, tipo_calculo, descripcion, activo) FROM stdin;
1	servicio	70.00	POR_MARGEN	Costo de venta calculado con margen del 70% sobre ventas mensuales	t
2	comercio	55.00	POR_MARGEN	Costo de venta calculado con margen del 55% sobre ventas mensuales	t
3	produccion	\N	COSTO_UNITARIO	Costo de venta calculado como (costo unitario total / costo de venta total) * 100	t
\.


--
-- Data for Name: estatus_solicitud; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.estatus_solicitud (idestatus, nombre, descripcion) FROM stdin;
1	Pendiente	La solicitud ha sido ingresada y está a la espera de revisión.
2	En revisión	La solicitud está siendo evaluada por el comité de crédito.
3	Aprobada	La solicitud ha sido aprobada y el préstamo puede ser desembolsado.
4	Rechazada	La solicitud fue denegada por el comité de crédito.
5	Desembolsado	El préstamo ha sido otorgado y el dinero ha sido entregado al cliente.
6	En mora	El cliente ha incumplido con el pago de sus cuotas y el préstamo está en mora.
7	Cancelado	El préstamo ha sido pagado en su totalidad y se ha cerrado.
\.


--
-- Data for Name: garantia; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.garantia (id_garantia, id_solicitud, descripcion, cantidad, marca, color, ubicacion, valor_realizacion) FROM stdin;
2	66	Refrigerador	1	Mabe	Gris	Sala	24000.00
3	66	Televisor	1	Sony	Negro	Sala	7000.00
4	1	Televisor	1	RCA	Negro	Laureles	13000.00
5	1	Refrigerador	1	Mabe	Gris	test	19000.00
6	1	Abanico	1	Sony	blanco	sala	3000.00
7	1	Laptop	1	Asus	Gris	Oficina	15000.28
18	123	tv	1	Sony	Negro	negocio	6000.00
\.


--
-- Data for Name: mora_diaria; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.mora_diaria (id_mora, id_prestamo, id_cuota, fecha_pago, fecha, dias_mora, monto_mora, estado_cuota, fecha_creo) FROM stdin;
1	17	1	2025-03-31	2025-05-10	40	962.50	Pendiente	2025-05-10 08:37:20.072196
2	17	2	2025-04-07	2025-05-10	33	962.50	Pendiente	2025-05-10 08:37:20.072196
3	17	3	2025-04-14	2025-05-10	26	962.50	Pendiente	2025-05-10 08:37:20.072196
4	17	4	2025-04-21	2025-05-10	19	962.50	Pendiente	2025-05-10 08:37:20.072196
5	17	5	2025-04-28	2025-05-10	12	962.50	Pendiente	2025-05-10 08:37:20.072196
6	17	6	2025-05-05	2025-05-10	5	962.50	Pendiente	2025-05-10 08:37:20.072196
7	18	9	2025-03-29	2025-05-10	42	175.00	Pendiente	2025-05-10 08:37:20.072196
8	18	10	2025-04-05	2025-05-10	35	175.00	Pendiente	2025-05-10 08:37:20.072196
9	18	11	2025-04-12	2025-05-10	28	175.00	Pendiente	2025-05-10 08:37:20.072196
10	18	12	2025-04-19	2025-05-10	21	175.00	Pendiente	2025-05-10 08:37:20.072196
11	18	13	2025-04-26	2025-05-10	14	175.00	Pendiente	2025-05-10 08:37:20.072196
12	18	14	2025-05-03	2025-05-10	7	175.00	Pendiente	2025-05-10 08:37:20.072196
13	19	17	2025-03-26	2025-05-10	45	175.00	Pendiente	2025-05-10 08:37:20.072196
14	19	18	2025-04-02	2025-05-10	38	175.00	Pendiente	2025-05-10 08:37:20.072196
15	19	19	2025-04-09	2025-05-10	31	175.00	Pendiente	2025-05-10 08:37:20.072196
16	19	20	2025-04-16	2025-05-10	24	175.00	Pendiente	2025-05-10 08:37:20.072196
17	19	21	2025-04-23	2025-05-10	17	175.00	Pendiente	2025-05-10 08:37:20.072196
18	19	22	2025-04-30	2025-05-10	10	175.00	Pendiente	2025-05-10 08:37:20.072196
19	19	23	2025-05-07	2025-05-10	3	175.00	Pendiente	2025-05-10 08:37:20.072196
20	26	25	2025-04-01	2025-05-10	39	2200.00	Pendiente	2025-05-10 08:37:20.072196
21	26	26	2025-04-08	2025-05-10	32	2200.00	Pendiente	2025-05-10 08:37:20.072196
22	26	27	2025-04-15	2025-05-10	25	2200.00	Pendiente	2025-05-10 08:37:20.072196
23	26	28	2025-04-22	2025-05-10	18	2200.00	Pendiente	2025-05-10 08:37:20.072196
24	26	29	2025-04-29	2025-05-10	11	2200.00	Pendiente	2025-05-10 08:37:20.072196
25	26	30	2025-05-06	2025-05-10	4	2200.00	Pendiente	2025-05-10 08:37:20.072196
26	27	49	2025-04-04	2025-05-10	36	577.50	Pendiente	2025-05-10 08:37:20.072196
27	27	50	2025-04-11	2025-05-10	29	577.50	Pendiente	2025-05-10 08:37:20.072196
28	27	51	2025-04-18	2025-05-10	22	577.50	Pendiente	2025-05-10 08:37:20.072196
29	27	52	2025-04-25	2025-05-10	15	577.50	Pendiente	2025-05-10 08:37:20.072196
30	27	53	2025-05-02	2025-05-10	8	577.50	Pendiente	2025-05-10 08:37:20.072196
31	27	54	2025-05-09	2025-05-10	1	577.50	Pendiente	2025-05-10 08:37:20.072196
32	41	70	2025-04-04	2025-05-10	36	2400.00	Pendiente	2025-05-10 08:37:20.072196
33	41	71	2025-04-11	2025-05-10	29	2400.00	Pendiente	2025-05-10 08:37:20.072196
34	41	72	2025-04-18	2025-05-10	22	2400.00	Pendiente	2025-05-10 08:37:20.072196
35	41	73	2025-04-25	2025-05-10	15	2400.00	Pendiente	2025-05-10 08:37:20.072196
36	41	74	2025-05-02	2025-05-10	8	2400.00	Pendiente	2025-05-10 08:37:20.072196
37	41	75	2025-05-09	2025-05-10	1	2400.00	Pendiente	2025-05-10 08:37:20.072196
38	44	84	2025-04-05	2025-05-10	35	875.00	Pendiente	2025-05-10 08:37:20.072196
39	44	85	2025-04-12	2025-05-10	28	875.00	Pendiente	2025-05-10 08:37:20.072196
40	44	86	2025-04-19	2025-05-10	21	875.00	Pendiente	2025-05-10 08:37:20.072196
41	44	87	2025-04-26	2025-05-10	14	875.00	Pendiente	2025-05-10 08:37:20.072196
42	44	88	2025-05-03	2025-05-10	7	875.00	Pendiente	2025-05-10 08:37:20.072196
43	45	92	2025-04-11	2025-05-10	29	700.00	Pendiente	2025-05-10 08:37:20.072196
44	45	93	2025-04-18	2025-05-10	22	700.00	Pendiente	2025-05-10 08:37:20.072196
45	45	94	2025-04-25	2025-05-10	15	700.00	Pendiente	2025-05-10 08:37:20.072196
46	45	95	2025-05-02	2025-05-10	8	700.00	Pendiente	2025-05-10 08:37:20.072196
47	45	96	2025-05-09	2025-05-10	1	700.00	Pendiente	2025-05-10 08:37:20.072196
48	47	101	2025-04-05	2025-05-10	35	175.00	Pendiente	2025-05-10 08:37:20.072196
49	47	102	2025-04-12	2025-05-10	28	175.00	Pendiente	2025-05-10 08:37:20.072196
50	47	103	2025-04-19	2025-05-10	21	175.00	Pendiente	2025-05-10 08:37:20.072196
51	47	104	2025-04-26	2025-05-10	14	175.00	Pendiente	2025-05-10 08:37:20.072196
52	47	105	2025-05-03	2025-05-10	7	175.00	Pendiente	2025-05-10 08:37:20.072196
53	48	109	2025-04-01	2025-05-10	39	262.50	Pendiente	2025-05-10 08:37:20.072196
54	48	110	2025-04-08	2025-05-10	32	262.50	Pendiente	2025-05-10 08:37:20.072196
55	48	111	2025-04-15	2025-05-10	25	262.50	Pendiente	2025-05-10 08:37:20.072196
56	48	112	2025-04-22	2025-05-10	18	262.50	Pendiente	2025-05-10 08:37:20.072196
57	48	113	2025-04-29	2025-05-10	11	262.50	Pendiente	2025-05-10 08:37:20.072196
58	48	114	2025-05-06	2025-05-10	4	262.50	Pendiente	2025-05-10 08:37:20.072196
59	49	117	2025-04-23	2025-05-10	17	1050.00	Pendiente	2025-05-10 08:37:20.072196
60	49	118	2025-04-30	2025-05-10	10	1050.00	Pendiente	2025-05-10 08:37:20.072196
61	49	119	2025-05-07	2025-05-10	3	1050.00	Pendiente	2025-05-10 08:37:20.072196
62	50	125	2025-03-29	2025-05-10	42	175.00	Pendiente	2025-05-10 08:37:20.072196
63	50	126	2025-04-05	2025-05-10	35	175.00	Pendiente	2025-05-10 08:37:20.072196
64	50	127	2025-04-12	2025-05-10	28	175.00	Pendiente	2025-05-10 08:37:20.072196
65	50	128	2025-04-19	2025-05-10	21	175.00	Pendiente	2025-05-10 08:37:20.072196
66	50	129	2025-04-26	2025-05-10	14	175.00	Pendiente	2025-05-10 08:37:20.072196
67	50	130	2025-05-03	2025-05-10	7	175.00	Pendiente	2025-05-10 08:37:20.072196
68	51	133	2025-04-05	2025-05-10	35	875.00	Pendiente	2025-05-10 08:37:20.072196
69	51	134	2025-04-12	2025-05-10	28	875.00	Pendiente	2025-05-10 08:37:20.072196
70	51	135	2025-04-19	2025-05-10	21	875.00	Pendiente	2025-05-10 08:37:20.072196
71	51	136	2025-04-26	2025-05-10	14	875.00	Pendiente	2025-05-10 08:37:20.072196
72	51	137	2025-05-03	2025-05-10	7	875.00	Pendiente	2025-05-10 08:37:20.072196
73	52	141	2025-04-05	2025-05-10	35	2125.00	Pendiente	2025-05-10 08:37:20.072196
74	52	142	2025-04-12	2025-05-10	28	2125.00	Pendiente	2025-05-10 08:37:20.072196
75	52	143	2025-04-19	2025-05-10	21	2125.00	Pendiente	2025-05-10 08:37:20.072196
76	52	144	2025-04-26	2025-05-10	14	2125.00	Pendiente	2025-05-10 08:37:20.072196
77	52	145	2025-05-03	2025-05-10	7	2125.00	Pendiente	2025-05-10 08:37:20.072196
78	53	189	2025-04-08	2025-05-10	32	3100.00	Pendiente	2025-05-10 08:37:20.072196
79	53	190	2025-04-15	2025-05-10	25	3100.00	Pendiente	2025-05-10 08:37:20.072196
80	53	191	2025-04-22	2025-05-10	18	3100.00	Pendiente	2025-05-10 08:37:20.072196
81	53	192	2025-04-29	2025-05-10	11	3100.00	Pendiente	2025-05-10 08:37:20.072196
82	53	193	2025-05-06	2025-05-10	4	3100.00	Pendiente	2025-05-10 08:37:20.072196
83	54	203	2025-04-17	2025-05-10	23	1225.00	Pendiente	2025-05-10 08:37:20.072196
84	54	204	2025-04-24	2025-05-10	16	1225.00	Pendiente	2025-05-10 08:37:20.072196
85	54	205	2025-05-01	2025-05-10	9	1225.00	Pendiente	2025-05-10 08:37:20.072196
86	54	206	2025-05-08	2025-05-10	2	1225.00	Pendiente	2025-05-10 08:37:20.072196
87	55	210	2025-04-11	2025-05-10	29	175.00	Pendiente	2025-05-10 08:37:20.072196
88	55	211	2025-04-18	2025-05-10	22	175.00	Pendiente	2025-05-10 08:37:20.072196
89	55	212	2025-04-25	2025-05-10	15	175.00	Pendiente	2025-05-10 08:37:20.072196
90	55	213	2025-05-02	2025-05-10	8	175.00	Pendiente	2025-05-10 08:37:20.072196
91	55	214	2025-05-09	2025-05-10	1	175.00	Pendiente	2025-05-10 08:37:20.072196
92	56	217	2025-04-04	2025-05-10	36	350.00	Pendiente	2025-05-10 08:37:20.072196
93	56	218	2025-04-11	2025-05-10	29	350.00	Pendiente	2025-05-10 08:37:20.072196
94	56	219	2025-04-18	2025-05-10	22	350.00	Pendiente	2025-05-10 08:37:20.072196
95	56	220	2025-04-25	2025-05-10	15	350.00	Pendiente	2025-05-10 08:37:20.072196
96	56	221	2025-05-02	2025-05-10	8	350.00	Pendiente	2025-05-10 08:37:20.072196
97	56	222	2025-05-09	2025-05-10	1	350.00	Pendiente	2025-05-10 08:37:20.072196
98	59	235	2025-04-15	2025-05-10	25	875.00	Pendiente	2025-05-10 08:37:20.072196
99	59	236	2025-04-22	2025-05-10	18	875.00	Pendiente	2025-05-10 08:37:20.072196
100	59	237	2025-04-29	2025-05-10	11	875.00	Pendiente	2025-05-10 08:37:20.072196
101	59	238	2025-05-06	2025-05-10	4	875.00	Pendiente	2025-05-10 08:37:20.072196
102	62	258	2025-04-09	2025-05-10	31	175.00	Pendiente	2025-05-10 08:37:20.072196
103	62	259	2025-04-16	2025-05-10	24	175.00	Pendiente	2025-05-10 08:37:20.072196
104	62	260	2025-04-23	2025-05-10	17	175.00	Pendiente	2025-05-10 08:37:20.072196
105	62	261	2025-04-30	2025-05-10	10	175.00	Pendiente	2025-05-10 08:37:20.072196
106	62	262	2025-05-07	2025-05-10	3	175.00	Pendiente	2025-05-10 08:37:20.072196
107	64	267	2025-04-09	2025-05-10	31	612.50	Pendiente	2025-05-10 08:37:20.072196
108	64	268	2025-04-16	2025-05-10	24	612.50	Pendiente	2025-05-10 08:37:20.072196
109	64	269	2025-04-23	2025-05-10	17	612.50	Pendiente	2025-05-10 08:37:20.072196
110	64	270	2025-04-30	2025-05-10	10	612.50	Pendiente	2025-05-10 08:37:20.072196
111	64	271	2025-05-07	2025-05-10	3	612.50	Pendiente	2025-05-10 08:37:20.072196
112	65	275	2025-04-09	2025-05-10	31	192.50	Pendiente	2025-05-10 08:37:20.072196
113	65	276	2025-04-16	2025-05-10	24	192.50	Pendiente	2025-05-10 08:37:20.072196
114	65	277	2025-04-23	2025-05-10	17	192.50	Pendiente	2025-05-10 08:37:20.072196
115	65	278	2025-04-30	2025-05-10	10	192.50	Pendiente	2025-05-10 08:37:20.072196
116	65	279	2025-05-07	2025-05-10	3	192.50	Pendiente	2025-05-10 08:37:20.072196
117	66	283	2025-04-09	2025-05-10	31	761.25	Pendiente	2025-05-10 08:37:20.072196
118	66	284	2025-04-16	2025-05-10	24	761.25	Pendiente	2025-05-10 08:37:20.072196
119	66	285	2025-04-23	2025-05-10	17	761.25	Pendiente	2025-05-10 08:37:20.072196
120	66	286	2025-04-30	2025-05-10	10	761.25	Pendiente	2025-05-10 08:37:20.072196
121	66	287	2025-05-07	2025-05-10	3	761.25	Pendiente	2025-05-10 08:37:20.072196
122	67	291	2025-04-09	2025-05-10	31	700.00	Pendiente	2025-05-10 08:37:20.072196
123	67	292	2025-04-16	2025-05-10	24	700.00	Pendiente	2025-05-10 08:37:20.072196
124	67	293	2025-04-23	2025-05-10	17	700.00	Pendiente	2025-05-10 08:37:20.072196
125	67	294	2025-04-30	2025-05-10	10	700.00	Pendiente	2025-05-10 08:37:20.072196
126	67	295	2025-05-07	2025-05-10	3	700.00	Pendiente	2025-05-10 08:37:20.072196
127	68	299	2025-04-09	2025-05-10	31	1050.00	Pendiente	2025-05-10 08:37:20.072196
128	68	300	2025-04-16	2025-05-10	24	1050.00	Pendiente	2025-05-10 08:37:20.072196
129	68	301	2025-04-23	2025-05-10	17	1050.00	Pendiente	2025-05-10 08:37:20.072196
130	68	302	2025-04-30	2025-05-10	10	1050.00	Pendiente	2025-05-10 08:37:20.072196
131	68	303	2025-05-07	2025-05-10	3	1050.00	Pendiente	2025-05-10 08:37:20.072196
132	79	387	2025-05-02	2025-05-10	8	78.75	Pendiente	2025-05-10 08:37:20.072196
133	79	388	2025-05-09	2025-05-10	1	78.75	Pendiente	2025-05-10 08:37:20.072196
134	80	395	2025-05-02	2025-05-10	8	61.25	Pendiente	2025-05-10 08:37:20.072196
135	80	396	2025-05-09	2025-05-10	1	61.25	Pendiente	2025-05-10 08:37:20.072196
136	84	431	2025-03-07	2025-05-10	64	875.00	Pendiente	2025-05-10 08:37:20.072196
137	84	432	2025-03-14	2025-05-10	57	875.00	Pendiente	2025-05-10 08:37:20.072196
138	84	433	2025-03-21	2025-05-10	50	875.00	Pendiente	2025-05-10 08:37:20.072196
139	84	434	2025-03-28	2025-05-10	43	875.00	Pendiente	2025-05-10 08:37:20.072196
140	84	435	2025-04-04	2025-05-10	36	875.00	Pendiente	2025-05-10 08:37:20.072196
141	84	436	2025-04-11	2025-05-10	29	875.00	Pendiente	2025-05-10 08:37:20.072196
142	84	437	2025-04-18	2025-05-10	22	875.00	Pendiente	2025-05-10 08:37:20.072196
143	84	438	2025-04-25	2025-05-10	15	875.00	Pendiente	2025-05-10 08:37:20.072196
\.


--
-- Data for Name: obligacionesfinancieras; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.obligacionesfinancieras (id_obligacion, id_solicitud, institucion, monto_inicial, saldo, cuota) FROM stdin;
2	66	Gallo mas gallo	1000.00	750.00	200.00
4	1	Gallo mas gallo	3000.00	1500.00	500.00
5	110	Gallo mas gallo	1000.00	400.00	100.00
6	110	Gallo mas gallo	1.00	0.00	1.00
7	110	Gallo mas gallo	1000.00	100.00	50.00
8	110	Tropigas	1500.00	40.00	30.00
9	110	Tropigas	1500.00	40.00	30.00
10	110	copasa	1000.00	50.00	20.00
\.


--
-- Data for Name: prestamo; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.prestamo (id_prestamo, id_solicitud, monto_aprobado, interes, plazo, fecha_aprobacion, saldo, fecha_primer_cuota, comentario, usuario_creo, fecha_modifico, usuario_modifico, monto_interes, montotal, frecuencia, modalidad, monto_cuota, interes_semanal, fecha_desembolso) FROM stdin;
3	1	18000.00	20.00	2	2025-03-10	2700.00	2025-03-15	Prueba	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
10	2	21000.00	21.00	2	2025-03-10	3176.25	2025-03-15	asdf	5	\N	\N	\N	\N	\N	\N	\N	\N	\N
11	9	5000.00	20.00	3	2025-03-14	666.67	2025-03-14	Prueba	5	\N	\N	\N	\N	\N	\N	\N	\N	\N
15	15	7000.00	20.00	2	2025-03-21	1225.00	2025-03-20	test	5	\N	\N	2800.00	9800.00	8	Semanal	1225.00	\N	\N
16	16	4000.00	20.00	2	2025-03-21	700.00	2025-03-28	test	5	\N	\N	1600.00	5600.00	8	Semanal	700.00	200.00	\N
17	18	5500.00	20.00	2	2025-03-24	962.50	2025-03-31	prueba	5	\N	\N	2200.00	7700.00	8	Semanal	962.50	275.00	\N
18	20	1000.00	20.00	2	2025-03-25	175.00	2025-03-29	test	5	\N	\N	400.00	1400.00	8	Semanal	175.00	50.00	\N
19	21	1000.00	20.00	2	2025-03-25	175.00	2025-03-26	test	5	\N	\N	400.00	1400.00	8	Semanal	175.00	50.00	\N
20	22	2000.00	20.00	2	2025-03-25	350.00	2025-03-29	test	5	\N	\N	800.00	2800.00	8	Semanal	350.00	100.00	\N
22	3	25000.00	21.00	3	2025-03-25	3395.83	2025-03-26	test	5	\N	\N	15750.00	40750.00	12	Semanal	3395.83	1312.50	\N
23	4	13000.00	15.00	2	2025-03-25	2112.50	2025-04-02	test	5	\N	\N	3900.00	16900.00	8	Semanal	2112.50	487.50	\N
24	5	12032025.00	2.00	12	2025-03-25	310827.31	2025-03-29	test	5	\N	\N	2887686.00	14919711.00	48	Semanal	310827.31	60160.13	\N
26	23	24000.00	20.00	6	2025-03-25	2200.00	2025-04-01	test	5	\N	\N	28800.00	52800.00	24	Semanal	2200.00	1200.00	\N
27	24	3300.00	20.00	2	2025-03-27	577.50	2025-04-04	test	5	\N	\N	1320.00	4620.00	8	Semanal	577.50	165.00	\N
41	25	18000.00	20.00	3	2025-03-28	2400.00	2025-04-04	test	5	\N	\N	10800.00	28800.00	12	Semanal	2400.00	900.00	\N
44	26	5000.00	20.00	2	2025-03-28	875.00	2025-04-05	test	5	\N	\N	2000.00	7000.00	8	Semanal	875.00	250.00	\N
45	27	4000.00	20.00	2	2025-03-28	700.00	2025-04-11	test	5	\N	\N	1600.00	5600.00	8	Semanal	700.00	200.00	\N
47	28	1000.00	20.00	2	2025-03-28	175.00	2025-04-05	test	5	\N	\N	400.00	1400.00	8	Semanal	175.00	50.00	\N
48	29	1500.00	20.00	2	2025-03-28	262.50	2025-04-01	test	5	\N	\N	600.00	2100.00	8	Semanal	262.50	75.00	\N
49	30	6000.00	20.00	2	2025-03-28	1050.00	2025-04-23	test	5	\N	\N	2400.00	8400.00	8	Semanal	1050.00	300.00	\N
50	32	1000.00	20.00	2	2025-03-29	175.00	2025-03-29	test	5	\N	\N	400.00	1400.00	8	Semanal	175.00	50.00	\N
51	33	5000.00	20.00	2	2025-03-29	875.00	2025-04-05	test	5	\N	\N	2000.00	7000.00	8	Semanal	875.00	250.00	\N
52	34	30000.00	20.00	12	2025-03-29	2125.00	2025-04-05	aprobado prueba	5	\N	\N	72000.00	102000.00	48	Semanal	2125.00	1500.00	\N
53	35	30000.00	8.00	3	2025-03-29	3100.00	2025-04-08	test	5	\N	\N	7200.00	37200.00	12	Semanal	3100.00	600.00	\N
68	50	6000.00	20.00	2	2025-04-09	8400.00	2025-04-09	test	5	\N	\N	2400.00	8400.00	8	Semanal	1050.00	300.00	\N
69	51	18000.00	20.00	2	2025-04-09	6300.00	2025-04-09	test	5	2025-04-14 15:31:29.842585-06	5	7200.00	25200.00	8	Semanal	3150.00	900.00	\N
85	108	18000.00	20.00	2	2025-05-10	15857.14	2025-05-18	prueba	5	2025-05-12 12:50:29.035341-06	5	7200.00	25200.00	8	Semanal	3150.00	900.00	2025-05-12
56	40	2000.00	20.00	2	2025-04-04	-700.00	2025-04-04	test	5	\N	\N	800.00	2800.00	8	Semanal	350.00	100.00	\N
77	65	5000.00	20.00	2	2025-04-14	3500.00	2025-04-14	test	5	2025-04-14 21:19:18.790271-06	5	2000.00	7000.00	8	Semanal	875.00	250.00	\N
74	62	2000.00	20.00	2	2025-04-14	-3200.00	2025-04-14	test	5	2025-04-14 15:45:22.732805-06	5	800.00	2800.00	8	Semanal	350.00	100.00	\N
57	41	18000.00	20.00	2	2025-04-04	3150.00	2025-04-04	trest	5	2025-04-08 10:27:31.067893-06	5	7200.00	-150.00	8	Semanal	3150.00	900.00	\N
59	42	5000.00	20.00	2	2025-04-08	875.00	2025-04-08	test	5	2025-04-08 10:31:12.858087-06	5	2000.00	6125.00	8	Semanal	875.00	250.00	\N
83	106	21000.00	20.00	2	2025-05-06	0.00	2025-05-12		5	2025-05-06 16:53:21.375558-06	5	8400.00	29400.00	8	Semanal	3675.00	1050.00	2025-05-06
78	66	1000.00	20.00	2	2025-04-14	0.00	2025-04-14	test	5	2025-04-14 22:32:44.125088-06	5	400.00	1400.00	8	Semanal	175.00	50.00	\N
70	58	4000.00	20.00	2	2025-04-12	0.00	2025-04-12	test	5	2025-04-14 14:51:56.499852-06	5	1600.00	5600.00	8	Semanal	700.00	200.00	\N
71	59	1000.00	20.00	2	2025-04-14	0.00	2025-04-14	test	5	2025-04-14 14:55:57.59428-06	5	400.00	1400.00	8	Semanal	175.00	50.00	\N
60	43	4000.00	20.00	2	2025-04-08	1050.00	2025-04-15	test	5	2025-04-08 15:43:05.264-06	5	1600.00	5600.00	8	Semanal	700.00	200.00	\N
72	60	50.00	20.00	2	2025-04-14	0.00	2025-04-14	test	5	2025-04-14 15:03:28.185362-06	5	20.00	70.00	8	Semanal	8.75	2.50	\N
73	61	40.00	20.00	2	2025-04-14	0.00	2025-04-14	test	5	2025-04-14 15:23:02.645419-06	5	16.00	56.00	8	Semanal	7.00	2.00	\N
61	44	1000.00	20.00	2	2025-04-08	-125.00	2025-04-08	test	5	2025-04-08 16:09:59.007467-06	5	400.00	1400.00	8	Semanal	175.00	50.00	\N
75	63	3000.00	20.00	2	2025-04-14	0.00	2025-04-14	test	5	2025-04-14 15:55:23.961312-06	5	1200.00	4200.00	8	Semanal	525.00	150.00	\N
62	45	1000.00	20.00	2	2025-04-09	1400.00	2025-04-09	test	5	\N	\N	400.00	1400.00	8	Semanal	175.00	50.00	\N
64	46	3500.00	20.00	2	2025-04-09	4900.00	2025-04-09	test	5	\N	\N	1400.00	4900.00	8	Semanal	612.50	175.00	\N
65	47	1100.00	20.00	2	2025-04-09	1540.00	2025-04-09	test	5	\N	\N	440.00	1540.00	8	Semanal	192.50	55.00	\N
66	48	4350.00	20.00	2	2025-04-09	6090.00	2025-04-09	test	5	\N	\N	1740.00	6090.00	8	Semanal	761.25	217.50	\N
67	49	4000.00	20.00	2	2025-04-09	5600.00	2025-04-09	test	5	\N	\N	1600.00	5600.00	8	Semanal	700.00	200.00	\N
54	38	7000.00	20.00	2	2025-04-03	-2450.00	2025-04-03	test	5	2025-04-14 15:28:25.562649-06	5	2800.00	9800.00	8	Semanal	1225.00	350.00	\N
55	39	1000.00	20.00	2	2025-04-04	-350.00	2025-04-04	t4st	5	2025-04-14 15:29:01.906847-06	5	400.00	1400.00	8	Semanal	175.00	50.00	\N
79	78	450.00	20.00	2	2025-04-26	630.00	2025-05-02		5	\N	\N	180.00	630.00	8	Semanal	78.75	22.50	\N
80	77	350.00	20.00	2	2025-04-26	490.00	2025-05-02		5	\N	\N	140.00	490.00	8	Semanal	61.25	17.50	\N
76	64	3000.00	20.00	2	2025-04-14	-4200.00	2025-04-14	test	5	2025-04-14 21:08:53.549665-06	5	1200.00	4200.00	8	Semanal	525.00	150.00	\N
84	107	5000.00	20.00	2	2025-05-06	7000.00	2025-03-07		5	\N	\N	2000.00	7000.00	8	Semanal	875.00	250.00	2025-03-01
82	105	10000.00	20.00	3	2025-05-06	16000.00	2025-05-19	Prueba	5	\N	\N	6000.00	16000.00	12	Semanal	1333.33	500.00	2025-05-13
81	76	2000.00	20.00	2	2025-04-26	1750.00	2025-05-02		5	2025-05-12 12:44:21.739043-06	5	800.00	2800.00	8	Semanal	350.00	100.00	2025-04-26
87	109	4000.00	20.00	1.5	2025-05-16	5200.00	2025-05-22		5	\N	\N	1200.00	5200.00	6	Semanal	866.67	200.00	2025-05-16
90	110	4000.00	20.00	1.5	2025-05-16	4333.00	2025-05-22		5	2025-05-24 12:01:48.324501-06	5	1200.00	5200.00	6	Semanal	866.67	200.00	2025-05-16
92	112	4000.00	20.00	1	2025-06-15	4800.00	2025-06-22		5	\N	\N	800.00	4800.00	4	Semanal	1200.00	200.00	2025-06-15
91	111	4000.00	20.00	2	2025-06-03	4900.00	2025-04-08		5	2025-06-04 16:19:43.049366-06	5	1600.00	5600.00	8	Semanal	700.00	200.00	2025-04-01
93	113	5000.00	20.00	1	2025-06-15	6000.00	2025-06-22		5	\N	\N	1000.00	6000.00	4	Semanal	1500.00	250.00	2025-06-15
94	114	4100.00	20.00	1	2025-06-15	4428.00	2025-06-22		5	2025-06-15 21:23:02.597998-06	5	820.00	4920.00	4	Diario	1230.00	205.00	2025-06-15
95	115	4200.00	20.00	1	2025-06-15	2520.00	2025-06-22		5	2025-06-15 21:32:50.238111-06	5	840.00	5040.00	2	Quincenal	2520.00	420.00	2025-06-15
96	116	4300.00	20.00	1	2025-06-15	2160.00	2025-06-22		5	2025-06-15 21:38:15.854195-06	5	860.00	5160.00	1	Mensual	5160.00	860.00	2025-06-15
98	117	5000.00	20.00	2	2025-06-24	6125.00	2025-07-01		5	2025-06-24 10:45:42.014672-06	5	2000.00	7000.00	8	Semanal	875.00	250.00	2025-06-24
99	118	4100.00	20.00	2	2025-06-30	5022.50	2025-06-30		5	2025-06-30 09:28:58.681333-06	5	1640.00	5740.00	8	Semanal	717.50	205.00	2025-06-23
100	119	4800.00	20.00	2	2025-06-30	6720.00	2025-06-30		5	\N	\N	1920.00	6720.00	8	Semanal	840.00	240.00	2025-06-23
101	120	4000.00	20.00	2	2025-06-30	4900.00	2025-06-29		5	2025-07-29 22:32:31.209947-06	5	1600.00	5600.00	8	Semanal	700.00	200.00	2025-06-22
102	123	4000.00	20.00	2	2025-09-20	4600.00	2025-09-29	Prueba	5	2025-09-20 19:51:05.785128-06	5	1600.00	5600.00	8	Semanal	700.00	200.00	2025-09-22
\.


--
-- Data for Name: prorroga; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.prorroga (id_prorroga, id_prestamo, fecha_prorroga, interes_pagado, nueva_fecha_pago) FROM stdin;
\.


--
-- Data for Name: solicitudprestamo; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.solicitudprestamo (id_solicitud, cod_solicitud, idcliente, actividad_economica, direccion_negocio, telefono, tipo_local, tiempo_operar, rubro, monto_solicitado, plazo_solicitado, tasa, venta_promedio_bueno, venta_promedio_mediano, venta_promedio_bajo, promedio_venta, ventas_mensuales, otros_ingresos_negocio, aportes_familiares, otros_ingresos, gasto_costo_venta, gastos_negocio, cuotas_credito, gastos_familiares, utilidad_final, tipo_promedio, idcartera, idestatus, fecha_solicitud, fecha_creo, usuario_creo, fecha_modifico, usuario_modifico, tipo_cliente, total_ingreso, total_gasto, costo_unitario, precio_venta, unidades_producidas) FROM stdin;
2	2	1	servicio	Villa Milagro	8888888	propio	6	servicio	21000.00	2	21.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	1	3	2025-03-07	2025-03-07 21:17:11.840084-06	5	2025-03-10 17:56:08.375986-06	5	Recurrente	\N	\N	\N	\N	\N
23	16	6	produccion	Villa Milagro	999999999	propio	6	servicio	24000.00	6	20.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	1	2	2025-03-25	2025-03-25 15:32:08.252842-06	5	2025-03-28 16:50:04.564513-06	5	Nuevo	\N	\N	\N	\N	\N
18	11	1	servicio	Villa Milagro	8888888	propio	6	servicio	5500.00	2	20.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	1	2	2025-03-24	2025-03-24 17:56:55.522843-06	5	2025-03-28 15:48:32.874396-06	5	Recurrente	\N	\N	\N	\N	\N
4	4	1	servicio	Villa Milagro	8888888	propio	6	servicio	13000.00	2	15.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	1	2	2025-03-10	2025-03-10 17:12:18.06498-06	5	2025-03-25 14:58:15.963885-06	5	Recurrente	\N	\N	\N	\N	\N
5	5	1	servicio	Villa Milagro	8888888	propio	6	servicio	12032025.00	1	2.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	1	2	2025-03-12	2025-03-12 14:15:25.623271-06	5	2025-03-25 15:09:20.081393-06	5	Recurrente	\N	\N	\N	\N	\N
15	8	1	servicio	Villa Milagro	8888888	propio	6	servicio	7000.00	2	20.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	1	2	2025-03-21	2025-03-20 20:59:19.977915-06	5	2025-03-25 22:33:18.001526-06	5	Recurrente	\N	\N	\N	\N	\N
9	6	1	servicio	Villa Milagro	8888888	propio	6	servicio	5000.00	3	20.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	1	2	2025-03-14	2025-03-14 11:09:09.802912-06	5	2025-03-21 16:13:58.680572-06	5	Recurrente	\N	\N	\N	\N	\N
31	24	1	servicio	Villa Milagro	8888888	propio	6	servicio	11000.00	2	20.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	1	1	2025-03-28	2025-03-28 16:55:42.495383-06	5	\N	\N	Recurrente	\N	\N	\N	\N	\N
22	15	1	servicio	Villa Milagro	8888888	propio	6	servicio	2000.00	2	20.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	1	2	2025-03-25	2025-03-25 14:03:19.716512-06	5	2025-03-28 16:31:43.365829-06	5	Recurrente	\N	\N	\N	\N	\N
19	12	1	servicio	Villa Milagro	8888888	propio	6	servicio	5500.00	2	20.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	1	2	2025-03-24	2025-03-24 17:57:11.333745-06	5	2025-03-28 16:08:37.82192-06	5	Recurrente	\N	\N	\N	\N	\N
24	17	6	produccion	Villa Milagro	999999999	propio	6	servicio	3300.00	2	20.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	1	1	2025-03-27	2025-03-27 09:46:33.031509-06	5	\N	\N	Recurrente	\N	\N	\N	\N	\N
32	25	1	servicio	Villa Milagro	8888888	propio	6	servicio	1000.00	2	20.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	1	1	2025-03-29	2025-03-29 03:38:28.99321-06	5	\N	\N	Recurrente	\N	\N	\N	\N	\N
20	13	1	servicio	Villa Milagro	8888888	propio	6	servicio	1000.00	2	20.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	1	2	2025-03-25	2025-03-25 13:15:03.374096-06	5	2025-03-28 16:10:22.357095-06	5	Recurrente	\N	\N	\N	\N	\N
1	1	1	servicio	Villa Milagro	8888888	propio	6	servicio	18000.00	2	20.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	1	2	2025-02-26	2025-02-26 08:43:11.587223-06	5	2025-04-03 12:20:46.93961-06	5	Nuevo	\N	\N	\N	\N	\N
21	14	1	servicio	Villa Milagro	8888888	propio	6	servicio	1000.00	2	20.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	1	2	2025-03-25	2025-03-25 13:21:14.779894-06	5	2025-03-28 16:25:51.475146-06	5	Recurrente	\N	\N	\N	\N	\N
29	22	1	servicio	Villa Milagro	8888888	propio	6	servicio	1500.00	2	20.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	1	1	2025-03-28	2025-03-28 16:26:56.002862-06	5	\N	\N	Recurrente	\N	\N	\N	\N	\N
16	9	1	servicio	Villa Milagro	8888888	propio	6	servicio	4000.00	2	20.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	1	2	2025-03-21	2025-03-21 16:13:16.944897-06	5	2025-03-27 18:02:35.965192-06	5	Recurrente	\N	\N	\N	\N	\N
34	27	1	servicio	Villa Milagro	8888888	propio	6	servicio	30000.00	12	20.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	1	1	2025-03-29	2025-03-29 08:46:59.361574-06	5	\N	\N	Recurrente	\N	\N	\N	\N	\N
25	18	1	servicio	Villa Milagro	8888888	propio	6	servicio	18000.00	2	20.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	1	2	2025-03-28	2025-03-28 15:43:15.440613-06	5	2025-03-29 03:43:14.440281-06	5	Recurrente	\N	\N	\N	\N	\N
17	10	1	servicio	Villa Milagro	8888888	propio	6	servicio	4000.00	2	20.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	1	2	2025-03-21	2025-03-21 16:13:26.913164-06	5	2025-03-28 12:20:15.285773-06	5	Recurrente	\N	\N	\N	\N	\N
3	3	1	servicio	Villa Milagro	8888888	propio	6	servicio	25000.00	3	21.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	1	2	2025-03-10	2025-03-10 09:42:42.254641-06	5	2025-03-27 21:24:00.146211-06	5	Recurrente	\N	\N	\N	\N	\N
33	26	1	servicio	Villa Milagro	8888888	propio	6	servicio	5000.00	2	20.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	1	1	2025-03-29	2025-03-29 03:44:17.728715-06	5	\N	\N	Recurrente	\N	\N	\N	\N	\N
27	20	1	servicio	Villa Milagro	8888888	propio	6	servicio	4000.00	2	20.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	1	2	2025-03-28	2025-03-28 16:10:02.135091-06	5	2025-03-29 08:47:58.466121-06	5	Recurrente	\N	\N	\N	\N	\N
28	21	1	servicio	Villa Milagro	8888888	propio	6	servicio	1000.00	2	20.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	1	2	2025-03-28	2025-03-28 16:21:24.277877-06	5	2025-03-29 11:27:11.410978-06	5	Recurrente	\N	\N	\N	\N	\N
26	19	1	servicio	Villa Milagro	8888888	propio	6	servicio	5000.00	2	20.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	1	2	2025-03-28	2025-03-28 15:58:56.683667-06	5	2025-03-29 09:35:49.82467-06	\N	Recurrente	\N	\N	\N	\N	\N
37	30	1	servicio	Villa Milagro	8888888	propio	6	servicio	5000.00	2	20.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	2	2	2025-03-31	2025-03-31 23:04:29.209582-06	5	2025-04-03 12:38:16.079076-06	5	Recurrente	\N	\N	\N	\N	\N
35	28	7	Barberia	Villa Milagro	8888888	propio	36	servicio	5000.00	2	20.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	1	1	2025-03-29	2025-03-29 10:29:12.472601-06	5	\N	\N	Nuevo	\N	\N	\N	\N	\N
30	23	1	servicio	Villa Milagro	8888888	propio	6	servicio	6000.00	2	20.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	1	2	2025-03-28	2025-03-28 16:36:25.276699-06	5	2025-04-03 12:36:42.376471-06	5	Recurrente	\N	\N	\N	\N	\N
38	31	1	servicio	Villa Milagro	8888888	propio	6	servicio	7000.00	2	20.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	2	2	2025-04-03	2025-04-03 22:33:53.159241-06	5	2025-04-03 22:34:06.774608-06	5	Recurrente	\N	\N	\N	\N	\N
39	32	1	servicio	Villa Milagro	8888888	propio	6	servicio	1000.00	2	20.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	2	2	2025-04-04	2025-04-04 08:27:02.620068-06	5	2025-04-04 08:27:21.234981-06	5	Recurrente	\N	\N	\N	\N	\N
40	33	1	servicio	Villa Milagro	8888888	propio	6	servicio	2000.00	2	20.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	2	2	2025-04-04	2025-04-04 08:32:00.566142-06	5	2025-04-04 08:32:22.646024-06	5	Recurrente	\N	\N	\N	\N	\N
41	34	1	servicio	Villa Milagro	8888888	propio	6	servicio	18000.00	2	20.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	2	2	2025-04-04	2025-04-04 11:21:18.392046-06	5	2025-04-04 11:23:58.907513-06	5	Recurrente	\N	\N	\N	\N	\N
36	29	1	servicio	Villa Milagro	8888888	propio	6	servicio	3000.00	2	20.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	2	4	2025-03-31	2025-03-31 23:00:35.223748-06	5	2025-04-12 13:13:21.772461-06	5	Recurrente	\N	\N	\N	\N	\N
42	35	1	servicio	Villa Milagro	8888888	propio	6	servicio	5000.00	2	20.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	2	2	2025-04-08	2025-04-08 10:29:43.280452-06	5	2025-04-08 10:29:57.201256-06	5	Recurrente	\N	\N	\N	\N	\N
43	36	1	servicio	Villa Milagro	8888888	propio	6	servicio	4000.00	2	20.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	2	2	2025-04-08	2025-04-08 14:47:16.254765-06	5	2025-04-08 14:47:29.557948-06	5	Recurrente	\N	\N	\N	\N	\N
44	37	1	servicio	Villa Milagro	8888888	propio	6	servicio	1000.00	2	20.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	2	2	2025-04-08	2025-04-08 15:58:56.50113-06	5	2025-04-08 15:59:08.001864-06	5	Recurrente	\N	\N	\N	\N	\N
45	38	1	servicio	Villa Milagro	8888888	propio	6	servicio	1000.00	2	20.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	2	2	2025-04-09	2025-04-09 14:21:06.540653-06	5	2025-04-09 14:21:40.910928-06	5	Recurrente	\N	\N	\N	\N	\N
46	39	1	servicio	Villa Milagro	8888888	propio	6	servicio	3500.00	2	20.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	2	2	2025-04-09	2025-04-09 14:26:32.253677-06	5	2025-04-09 14:26:45.122721-06	5	Recurrente	\N	\N	\N	\N	\N
47	40	1	servicio	Villa Milagro	8888888	propio	6	servicio	1100.00	2	20.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	2	2	2025-04-09	2025-04-09 14:29:42.882165-06	5	2025-04-09 14:30:03.806097-06	5	Recurrente	\N	\N	\N	\N	\N
48	41	1	servicio	Villa Milagro	8888888	propio	6	servicio	4350.00	2	20.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	2	2	2025-04-09	2025-04-09 14:36:25.151434-06	5	2025-04-09 14:36:56.169314-06	5	Recurrente	\N	\N	\N	\N	\N
49	42	1	servicio	Villa Milagro	8888888	propio	6	servicio	4000.00	2	20.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	2	2	2025-04-09	2025-04-09 14:39:53.007565-06	5	2025-04-09 14:40:09.677856-06	5	Recurrente	\N	\N	\N	\N	\N
50	43	1	servicio	Villa Milagro	8888888	propio	6	servicio	6000.00	2	20.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	2	2	2025-04-09	2025-04-09 14:58:51.545285-06	5	2025-04-09 14:59:19.016147-06	5	Recurrente	\N	\N	\N	\N	\N
51	44	1	servicio	Villa Milagro	8888888	propio	6	servicio	18000.00	2	20.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	2	3	2025-04-09	2025-04-09 15:14:56.913687-06	5	2025-04-09 15:16:09.661186-06	5	Recurrente	\N	\N	\N	\N	\N
52	45	1	servicio	Villa Milagro	8888888	propio	6	servicio	1000.00	2	20.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	2	2	2025-04-11	2025-04-11 21:40:10.345463-06	5	2025-04-11 21:40:22.225496-06	5	Recurrente	\N	\N	\N	\N	\N
59	49	1	servicio	Villa Milagro	8888888	propio	6	servicio	1000.00	2	20.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	2	3	2025-04-14	2025-04-14 14:54:17.792889-06	5	2025-04-14 14:55:05.697209-06	5	Recurrente	\N	\N	\N	\N	\N
53	46	1	servicio	Villa Milagro	8888888	propio	6	servicio	5000.00	2	20.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	2	4	2025-04-11	2025-04-11 21:56:32.401104-06	5	2025-04-11 22:44:24.177481-06	5	Recurrente	\N	\N	\N	\N	\N
57	47	1	servicio	Villa Milagro	8888888	propio	6	servicio	18000.00	2	20.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	2	4	2025-04-12	2025-04-12 13:20:47.389375-06	5	2025-04-12 13:25:36.252029-06	5	Recurrente	\N	\N	\N	\N	\N
58	48	1	servicio	Villa Milagro	8888888	propio	6	servicio	4000.00	2	20.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	2	3	2025-04-12	2025-04-12 14:03:16.566312-06	5	2025-04-12 14:04:28.963634-06	5	Recurrente	\N	\N	\N	\N	\N
60	50	1	servicio	Villa Milagro	8888888	propio	6	servicio	50.00	2	20.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	2	3	2025-04-14	2025-04-14 14:59:51.436413-06	5	2025-04-14 15:01:14.861891-06	5	Recurrente	\N	\N	\N	\N	\N
61	51	1	servicio	Villa Milagro	8888888	propio	6	servicio	40.00	2	20.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	2	3	2025-04-14	2025-04-14 15:21:25.961413-06	5	2025-04-14 15:22:09.968275-06	5	Recurrente	\N	\N	\N	\N	\N
65	55	1	servicio	Villa Milagro	8888888	propio	6	servicio	5000.00	2	20.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	2	7	2025-04-14	2025-04-14 21:12:16.814659-06	5	2025-04-14 21:18:33.123381-06	5	Recurrente	\N	\N	\N	\N	\N
62	52	1	servicio	Villa Milagro	8888888	propio	6	servicio	2000.00	2	20.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	2	3	2025-04-14	2025-04-14 15:36:11.915436-06	5	2025-04-14 15:36:58.272334-06	5	Recurrente	\N	\N	\N	\N	\N
63	53	1	servicio	Villa Milagro	8888888	propio	6	servicio	3000.00	2	20.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	2	3	2025-04-14	2025-04-14 15:47:46.396225-06	5	2025-04-14 15:48:47.191525-06	5	Recurrente	\N	\N	\N	\N	\N
64	54	1	servicio	Villa Milagro	8888888	propio	6	servicio	3000.00	2	20.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	2	3	2025-04-14	2025-04-14 15:59:26.283335-06	5	2025-04-14 16:00:16.512646-06	5	Recurrente	\N	\N	\N	\N	\N
77	61	1	servicio	Villa Milagro	8888888	propio	6	servicio	350.00	2	20.00	1.00	2.00	3.00	60.00	5.00	6.00	7.00	80.00	9.00	10.00	11.00	12.00	56.00	Diario	2	3	2025-04-24	2025-04-24 12:07:11.787924-06	5	2025-04-26 12:52:14.924357-06	5	Recurrente	98.00	42.00	\N	\N	\N
66	56	1	servicio	Villa Milagro	8888888	propio	6	servicio	1000.00	2	20.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	13.00	Diario	2	7	2025-04-14	2025-04-14 22:28:03.947158-06	5	2025-04-14 22:32:44.185344-06	5	Recurrente	\N	\N	\N	\N	\N
67	57	1	servicio	Villa Milagro	8888888	propio	6	servicio	500.00	2	20.00	1.00	2.00	3.00	4.00	5.00	6.00	7.00	8.00	10.00	11.00	12.00	13.00	15.00	Diario	2	1	2025-04-22	2025-04-22 12:03:18.863253-06	5	\N	\N	Recurrente	9.00	14.00	\N	\N	\N
74	58	1	servicio	Villa Milagro	8888888	propio	6	servicio	1000.00	2	20.00	1.00	2.00	3.00	8.00	5.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	15.00	Semanal	2	1	2025-04-22	2025-04-22 15:39:34.578119-06	5	\N	\N	Recurrente	9.00	14.00	\N	\N	\N
75	59	1	servicio	Villa Milagro	8888888	propio	6	servicio	1000.00	2	20.00	1.00	2.00	3.00	60.00	50.00	6.00	7.00	8.00	9.00	10.00	11.00	12.00	29.00	Diario	2	1	2025-04-24	2025-04-24 11:42:27.26643-06	5	\N	\N	Recurrente	71.00	42.00	\N	\N	\N
78	62	1	servicio	Villa Milagro	8888888	propio	6	produccion	450.00	2	20.00	1.00	2.00	3.00	60.00	5.00	6.00	700.00	8.00	50.00	10.00	11.00	12.00	636.00	Diario	2	3	2025-04-24	2025-04-24 13:47:51.427274-06	5	2025-04-26 12:11:41.814735-06	5	Recurrente	719.00	83.00	50.00	100.00	100.00
76	60	1	servicio	Villa Milagro	8888888	propio	6	produccion	2000.00	2	20.00	1.00	2.00	3.00	60.00	100.00	6.00	7.00	8.00	41.67	10.00	11.00	12.00	46.33	Diario	2	3	2025-04-24	2025-04-24 11:44:07.666488-06	5	2025-04-26 12:56:24.913797-06	5	Recurrente	121.00	74.67	\N	\N	\N
105	64	1	servicio	Villa Milagro	8888888	propio	6	servicio	10000.00	3	20.00	150.00	75.00	50.00	2750.00	10000.00	30000.00	0.00	1000.00	3000.00	3000.00	1000.00	2500.00	31500.00	Diario	2	3	2025-05-06	2025-05-06 12:01:20.576199-06	5	2025-05-06 12:02:56.061585-06	5	Recurrente	41000.00	9500.00	\N	\N	\N
106	65	1	servicio	Villa Milagro	8888888	propio	6	servicio	21000.00	2	20.00	150.00	75.00	50.00	2750.00	10000.00	30000.00	700.00	1000.00	3000.00	3000.00	1000.00	13.00	34687.00	Semanal	2	7	2025-05-06	2025-05-06 12:57:54.542746-06	5	2025-05-06 16:53:21.44897-06	5	Recurrente	41700.00	7013.00	\N	\N	\N
107	66	2	produccion	Veracruz	999999999	renta	8	comercio	5000.00	2	20.00	150.00	75.00	50.00	2750.00	10000.00	30000.00	700.00	80.00	4500.00	3000.00	1000.00	2500.00	29780.00	Diario	2	3	2025-05-06	2025-05-06 17:29:40.360096-06	5	2025-05-06 17:31:24.845918-06	5	Nuevo	40780.00	11000.00	\N	\N	\N
108	67	4	servicio	nuevo negocio	55555555	albergue	6	comercio	18000.00	2	20.00	150.00	75.00	50.00	2750.00	10000.00	30000.00	700.00	1000.00	4500.00	3000.00	1000.00	2500.00	30700.00	Diario	1	3	2025-05-10	2025-05-10 00:16:31.320262-06	5	2025-05-10 00:34:33.597363-06	5	Nuevo	41700.00	11000.00	\N	\N	\N
109	68	1	servicio	Villa Milagro	8888888	propio	6	servicio	4000.00	1.5	20.00	150.00	75.00	50.00	2750.00	2750.00	30000.00	700.00	0.00	825.00	3000.00	0.00	2000.00	27625.00	Diario	1	2	2025-05-16	2025-05-16 21:34:14.069135-06	5	2025-05-16 21:38:51.054078-06	5	Recurrente	33450.00	5825.00	\N	\N	\N
118	77	1	servicio	Villa Milagro	8888888	propio	6	servicio	4100.00	2	20.00	150.00	1000.00	500.00	16500.00	16500.00	30000.00	0.00	0.00	4950.00	0.00	0.00	0.00	41550.00	Diario	1	3	2025-06-30	2025-06-30 09:19:04.370031-06	5	2025-06-30 09:20:41.818097-06	5	Recurrente	46500.00	4950.00	\N	\N	\N
110	69	1	servicio	Villa Milagro	8888888	propio	6	servicio	4000.00	1.5	20.00	150.00	75.00	50.00	2750.00	2750.00	6.00	7000.00	1000.00	1000.00	3000.00	0.00	2000.00	4756.00	Diario	1	3	2025-05-16	2025-05-16 22:10:50.704983-06	5	2025-05-16 22:12:03.817023-06	5	Recurrente	10756.00	6000.00	\N	\N	\N
111	70	1	servicio	Villa Milagro	8888888	propio	6	servicio	4000.00	2	20.00	150.00	75.00	50.00	2750.00	10000.00	0.00	0.00	0.00	3000.00	0.00	0.00	0.00	7000.00	Diario	2	3	2025-06-03	2025-06-03 12:45:01.230972-06	5	2025-06-03 12:46:34.641382-06	5	Recurrente	10000.00	3000.00	\N	\N	\N
104	63	1	servicio	Villa Milagro	8888888	propio	6	servicio	18000.00	2	20.00	1.00	2.00	3.00	60.00	5000.00	6.00	7.00	8.00	1500.00	10.00	11.00	12.00	3488.00	Diario	2	2	2025-05-01	2025-05-01 22:48:04.060503-06	5	2025-06-13 22:13:13.354378-06	5	Recurrente	5021.00	1533.00	\N	\N	\N
112	71	1	servicio	Villa Milagro	8888888	propio	6	servicio	4000.00	1	20.00	150.00	75.00	50.00	2750.00	2750.00	30000.00	0.00	0.00	825.00	1000.00	1000.00	1000.00	28925.00	Diario	2	3	2025-06-15	2025-06-15 13:12:03.428201-06	5	2025-06-15 13:13:06.711547-06	5	Recurrente	32750.00	3825.00	\N	\N	\N
119	78	1	servicio	Villa Milagro	8888888	propio	6	servicio	4800.00	2	20.00	2000.00	1000.00	2000.00	50000.00	50000.00	30000.00	0.00	0.00	15000.00	0.00	1000.00	0.00	64000.00	Diario	1	3	2025-06-30	2025-06-30 13:08:46.41252-06	5	2025-06-30 13:10:29.615952-06	5	Recurrente	80000.00	16000.00	\N	\N	\N
113	72	1	servicio	Villa Milagro	8888888	propio	6	servicio	5000.00	1	20.00	2000.00	750.00	300.00	30500.00	30500.00	1000.00	7.00	1000.00	9150.00	1000.00	0.00	0.00	22357.00	Diario	2	3	2025-06-15	2025-06-15 13:26:06.251637-06	5	2025-06-15 13:27:10.752629-06	5	Recurrente	32507.00	10150.00	\N	\N	\N
114	73	1	servicio	Villa Milagro	8888888	propio	6	servicio	4100.00	1	20.00	1500.00	750.00	500.00	27500.00	27500.00	3000.00	0.00	0.00	8250.00	1000.00	100.00	0.00	21150.00	Diario	2	3	2025-06-15	2025-06-15 20:56:58.940547-06	5	2025-06-15 21:03:17.227759-06	5	Recurrente	30500.00	9350.00	\N	\N	\N
115	74	1	servicio	Villa Milagro	8888888	propio	6	servicio	4200.00	1	20.00	1550.00	1150.00	5000.00	77000.00	77000.00	6.00	0.00	0.00	23100.00	400.00	0.00	0.00	53506.00	Diario	2	3	2025-06-15	2025-06-15 21:28:53.869189-06	5	2025-06-15 21:30:43.643579-06	5	Recurrente	77006.00	23500.00	\N	\N	\N
120	79	1	servicio	Villa Milagro	8888888	propio	6	servicio	4000.00	2	20.00	2000.00	1000.00	2000.00	50000.00	50000.00	0.00	0.00	0.00	15000.00	0.00	0.00	0.00	35000.00	Diario	1	3	2025-06-30	2025-06-30 14:11:56.005144-06	5	2025-06-30 14:14:03.12794-06	5	Recurrente	50000.00	15000.00	\N	\N	\N
116	75	1	servicio	Villa Milagro	8888888	propio	6	servicio	4300.00	2	20.00	2000.00	750.00	2000.00	47500.00	47500.00	0.00	0.00	0.00	14250.00	0.00	0.00	0.00	33250.00	Diario	2	3	2025-06-15	2025-06-15 21:34:52.388951-06	5	2025-06-15 21:36:38.70769-06	5	Recurrente	47500.00	14250.00	\N	\N	\N
117	76	1	servicio	Villa Milagro	8888888	propio	6	servicio	5000.00	2	20.00	2000.00	3000.00	50.00	50500.00	50500.00	0.00	0.00	0.00	15150.00	0.00	0.00	0.00	35350.00	Diario	1	3	2025-06-24	2025-06-24 10:43:41.327397-06	5	2025-06-24 10:44:32.870148-06	5	Recurrente	50500.00	15150.00	\N	\N	\N
121	80	1	servicio	Villa Milagro	8888888	propio	6	servicio	4000.00	2	20.00	100.00	200.00	300.00	6000.00	6000.00	0.00	0.00	0.00	2700.00	0.00	0.00	0.00	3300.00	Diario	1	2	2025-07-06	2025-07-06 12:53:07.842301-06	5	2025-07-06 12:53:24.58829-06	5	Recurrente	6000.00	2700.00	\N	\N	\N
122	81	1	servicio	Villa Milagro	8888888	propio	6	servicio	4001.00	2	20.00	100.00	200.00	300.00	6000.00	6000.00	0.00	0.00	0.00	1800.00	0.00	0.00	300.00	3900.00	Diario	1	2	2025-07-06	2025-07-06 13:03:49.334767-06	5	2025-07-06 13:04:03.902125-06	5	Recurrente	6000.00	2100.00	\N	\N	\N
123	82	1	servicio	Villa Milagro	8888888	propio	6	servicio	4000.00	2	20.00	2000.00	1000.00	2000.00	50000.00	50000.00	0.00	0.00	0.00	15000.00	0.00	0.00	0.00	35000.00	Diario	2	3	2025-09-20	2025-09-20 19:46:41.27891-06	5	2025-09-20 19:49:06.680928-06	5	Recurrente	50000.00	15000.00	\N	\N	\N
\.


--
-- Data for Name: sucursales; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.sucursales (sucursal_id, nombre, direccion, telefono, fecha_apertura) FROM stdin;
2	Laureles Norte	prueba direccion	88888888	2025-12-10 00:00:00-06
3	Villa Milagro	prueba villa milagro	99999999	2025-02-10 00:00:00-06
5	Carretera Norte	Donde fue la pepsi	77777777	2025-04-17 00:00:00-06
4	Sucursal este	sucursal este	333333	2025-03-29 00:00:00-06
\.


--
-- Data for Name: tblcatcartera; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.tblcatcartera (idcartera, descripcion, monto_maximo, monto_minimo, fecha_creacion, usuario_creo, fecha_modificacion, usuario_modifico, estado) FROM stdin;
2	Cartera B	100000.00	4000.00	2025-02-07 00:00:00-06	admin	\N	\N	t
3	Cartera C	50000.00	4000.00	2025-02-07 00:00:00-06	admin	\N	\N	t
4	Cartera D	30000.00	4000.00	2025-02-07 00:00:00-06	admin	\N	\N	t
1	Cartera A	500000.00	4000.00	2025-02-07 00:00:00-06	admin	2025-04-17 14:34:54.72448-06	JHONNY FRANCISCOS GUTIERREZ  GOMEZ	t
5	Cartera E	500000.00	100000.00	2025-04-17 14:35:24.125066-06	JHONNY FRANCISCOS GUTIERREZ  GOMEZ	2025-04-18 02:31:48.654816-06	JHONNY FRANCISCOS GUTIERREZ  GOMEZ	t
\.


--
-- Data for Name: tblcatformulariodetalle; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.tblcatformulariodetalle (idfrm, idfrmdetalle, strnombreelemento, strtipotag, bolestado) FROM stdin;
\.


--
-- Data for Name: tblcatformularios; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.tblcatformularios (idfrm, strformulario, strnombreform, bolestado, strkeymenu) FROM stdin;
1	Catálogo de usuarios	../../pages/usuarios/catusuarios.php	t	mnu-ctrlusuario
2	Catálogo de perfiles	../../pages/usuarios/perfil.php	t	mnu-ctrlusuario
3	General	../../pages/configuraciones/general.php	t	mnu-config
4	Auto Tareas	../../pages/herramientas/autotareas.php	t	mnu-herramientas
5	Tasa de cambio	../../pages/herramientas/tasacambio.php	t	mnu-herramientas
6	Metricas	../../pages/metricas/metricas.php	t	mnu-metricas
9	Clientes	../../pages/clientes/clientes.php	t	mnu-catalogos
7	Solicitud de crédito	../../pages/prestamos/solicitud_prestamos.php	t	mnu-prestamos
10	Aprobación de crédito	../../pages/prestamos/aprobacion_prestamos.php	t	mnu-prestamos
8	Consultar solicitud	../../pages/prestamos/creditos.php	t	mnu-prestamos
11	Sucursal	../../pages/sucursal/sucursal.php	t	mnu-catalogos
12	Cartera	../../pages/cartera/carteras.php	t	mnu-catalogos
13	Reporte mora	../../pages/reportes/rptmora.php	t	mnu-reportes
14	Panel dasboard	../../pages/dashboard/dashboard.php	t	mnu-dashboard
15	Movimiento por cartera	../../pages/reportes/rptmovcartera.php	t	mnu-reportes
16	Reporte de abono diario	../../pages/reportes/rptabonodiario.php	t	mnu-reportes
17	Reporte de cobro diario	../../pages/reportes/rptcobrodiario.php	t	mnu-reportes
\.


--
-- Data for Name: tblcatmenu; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.tblcatmenu (intidmenu, strmenu, strtipomenu, strnivelmenu, bolactivo, strhref, strclassicono) FROM stdin;
4	Metricas	mnu-metricas	1	t	#	\N
6	Catalogo	mnu-catalogos	1	t	#	fas fa-th-list text-warning
3	Herramientas	mnu-herramientas	1	t	#	fa fa-gears fa-fw text-warning
5	Créditos	mnu-prestamos	1	t	#	fas fa-hand-holding-usd text-warning
2	Configuración	mnu-config	1	t	#	fas fa-cog fa-spin text-warning
1	Control de usuario	mnu-ctrlusuario	1	t	#	fas fa-cog fa-spin text-warning
7	Reportes	mnu-reportes	1	t	#	fa fa-file-alt text-warning
8	Dashboard	mnu-dashboard	1	t	#	fa-tachometer-alt text-warning
\.


--
-- Data for Name: tblcatmenuperfil; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.tblcatmenuperfil (intidmenuperfil, idperfil, intidmenu, bolactivo) FROM stdin;
1	1	1	t
8	2	4	f
4	1	4	f
9	1	5	t
10	2	5	t
7	2	3	f
11	1	6	t
3	1	3	f
26	8	2	f
27	8	3	f
28	8	4	f
29	8	6	f
25	8	1	f
30	8	5	t
5	2	1	t
31	1	7	t
2	1	2	f
6	2	2	f
12	2	6	f
32	1	8	t
33	2	8	t
34	8	8	t
35	15	4	f
36	15	6	f
37	15	3	f
38	15	5	f
39	15	2	f
40	15	1	f
41	15	7	f
42	15	8	f
43	18	4	f
44	18	6	f
45	18	3	f
46	18	5	f
47	18	2	f
49	18	7	f
50	18	8	f
48	18	1	t
\.


--
-- Data for Name: tblcatperfilusr; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.tblcatperfilusr (idperfil, strperfil, bolactivo) FROM stdin;
1	Administrador	t
2	Oficial de credito	t
8	Operativo de credito	t
15	Oficial de credito prueba	t
18	Oficial crediticio	t
\.


--
-- Data for Name: tblcatperfilusrfrm; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.tblcatperfilusrfrm (idperfilusrfrm, idfrm, idperfil, bolactivo) FROM stdin;
2	2	1	t
11	5	2	f
12	6	2	f
6	6	1	f
13	7	1	t
16	8	2	t
10	4	2	f
17	9	1	t
15	8	1	t
1	1	1	t
4	4	1	f
5	5	1	f
49	1	8	f
50	2	8	f
51	3	8	f
52	4	8	f
53	5	8	f
54	6	8	f
55	9	8	f
56	7	8	t
57	10	8	t
58	8	8	t
14	7	2	t
7	1	2	t
8	2	2	t
59	11	1	t
60	12	1	t
3	3	1	f
62	13	2	\N
63	13	8	\N
61	13	1	t
9	3	2	f
18	9	2	f
64	14	1	t
65	15	1	t
66	15	2	t
67	15	8	t
68	1	15	f
69	2	15	f
70	3	15	f
71	4	15	f
72	5	15	f
73	6	15	f
74	9	15	f
75	7	15	f
76	10	15	f
77	8	15	f
78	11	15	f
79	12	15	f
80	13	15	f
81	14	15	f
82	15	15	f
83	16	1	t
84	17	1	t
85	16	2	t
86	17	2	t
87	16	8	t
88	17	8	t
89	16	15	t
90	17	15	t
93	3	18	f
94	4	18	f
95	5	18	f
96	6	18	f
97	9	18	f
98	7	18	f
99	10	18	f
100	8	18	f
101	11	18	f
102	12	18	f
103	13	18	f
104	14	18	f
105	15	18	f
106	16	18	f
107	17	18	f
91	1	18	t
92	2	18	t
\.


--
-- Data for Name: tblcatusuario; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.tblcatusuario (intid, strpnombre, strsnombre, strpapellido, strsapellido, strsexo, strcorreo, stridentificacion, strdireccion, strcontacto, strusuariocreo, datfechacreo, strusuariomodifico, datfechamodifico, datfechabaja, bolactivo, strusuario, strpassword, intidperfil, idcartera, sucursal_id) FROM stdin;
26	JHONNY	FRAN	PRUEBA 4		MASCULINO	jhonny4@gmail.com	004		8888888	jhonfc9011@gmail.com	2025-02-10 09:42:38	jgutierrez	2025-02-10 00:00:00	\N	t	jgutierrez	$2y$10$ULjiFIsFexlqEomeOtv2sOmwInMZ/Mr/ekpC84OkmRID90GIh6Tyu	1	2	3
6	KEVIN	ARNOLD	RIVERA	GOMEZ	MASCULINO	kevin@gmail.com	001	Laureles norte	8888888	jhonfc9011@gmail.com	2025-01-31 21:46:43	jgutierrez	2025-02-10 00:00:00	\N	t	kevin@gmail.com	827ccb0eea8a706c4c34a16891f84e7b	1	1	2
8	JHONNY		PRUEBA 3		MASCULINO		003			jhonfc9011@gmail.com	2025-02-07 15:16:10	jhonfc9011@gmail.com	2025-02-07 15:22:06	2025-02-07	t	jhonny@gmail.com	d41d8cd98f00b204e9800998ecf8427e	1	1	2
28	JAQUELINE		REYES	REYES	FEMENINO	jreyes@gmail.com	007	test	5555555	jhonfc9011@gmail.com	2025-03-29 09:39:00	jhonfc9011@gmail.com	2025-03-29 00:00:00	\N	t	jreyes	827ccb0eea8a706c4c34a16891f84e7b	2	2	3
27	JHONNY	FRANCISCO	PRUEBA 5	GUTIERREZ	MASCULINO	jhonny5@gmail.com	005	test	8888888	jgutierrez	2025-02-10 10:45:35	jgutierrez	2025-02-10 00:00:00	\N	t	jgutierrez5	e67c10a4c8fbfc0c400e047bb9a056a1	1	3	2
29	JHONNY		PRUEBA 6		MASCULINO	jhonfc9011@yahoo.com	0011106900032B		8888888	jhonfc9011@gmail.com	2025-08-01 18:21:34	\N	\N	\N	t	jgutierrez	8e1dad13f6d19a1745be05822a9a7fa4	1	1	2
5	JHONNY	FRANCISCOS	GUTIERREZ	GOMEZ	MASCULINO	jhonfc9011@gmail.com	001	Villa milagro	82739363	jhonfc9011@gmail.com	2025-01-31 00:00:00	jgutierrez5	2025-09-20 00:00:00	\N	t	jhonfc9011@gmail.com	$2y$10$faMNxgmcfOHLQIN6O4h.wuwLdgrp0rVMGUH3PtHCE4ifSEyn.GMci	1	2	2
30	ADMINISTRADOR		ROOT		MASCULINO	admin@credimore.com	0011106900032B			jhonfc9011@gmail.com	2025-10-25 08:48:13	\N	\N	\N	t	admin@credimore.com	$2y$10$emt/hmYInDyjga8ltp7iAOvIQvSJ52gBPOndgAk1DJz7H9/An1Fma	1	1	2
7	JHONY		PRUEBA		MASCULINO	jhonny@gmail.com	002			jhonfc9011@gmail.com	2025-02-01 08:49:20	admin@credimore.com	2025-10-25 09:33:23	2025-10-25	f	jhonny@gmail.com	e10adc3949ba59abbe56e057f20f883e	2	1	3
\.


--
-- Data for Name: v_pagos; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.v_pagos (jsonb_agg) FROM stdin;
[{"id_pago": 567, "monto_aplicado": 700.00}, {"id_pago": 568, "monto_aplicado": 100.00}]
\.


--
-- Name: abono_anulado_id_anulacion_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.abono_anulado_id_anulacion_seq', 2, true);


--
-- Name: abono_cuota_id_relacion_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.abono_cuota_id_relacion_seq', 151, true);


--
-- Name: abono_id_abono_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.abono_id_abono_seq', 109, true);


--
-- Name: calendariopago_id_pago_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.calendariopago_id_pago_seq', 581, true);


--
-- Name: clientes_idcliente_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.clientes_idcliente_seq', 8, true);


--
-- Name: configuracion_costo_venta_id_config_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.configuracion_costo_venta_id_config_seq', 3, true);


--
-- Name: estatus_solicitud_idestatus_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.estatus_solicitud_idestatus_seq', 7, true);


--
-- Name: garantia_id_garantia_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.garantia_id_garantia_seq', 18, true);


--
-- Name: mora_diaria_id_mora_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.mora_diaria_id_mora_seq', 143, true);


--
-- Name: obligacionesfinancieras_id_obligacion_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.obligacionesfinancieras_id_obligacion_seq', 10, true);


--
-- Name: prestamo_id_prestamo_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.prestamo_id_prestamo_seq', 102, true);


--
-- Name: prorroga_id_prorroga_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.prorroga_id_prorroga_seq', 1, false);


--
-- Name: solicitudprestamo_id_solicitud_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.solicitudprestamo_id_solicitud_seq', 123, true);


--
-- Name: sucursales_sucursal_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.sucursales_sucursal_id_seq', 5, true);


--
-- Name: tblcatcartera_idcartera_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.tblcatcartera_idcartera_seq', 5, true);


--
-- Name: tblcatformulariodetalle_idfrmdetalle_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.tblcatformulariodetalle_idfrmdetalle_seq', 1, false);


--
-- Name: tblcatformularios_idfrm_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.tblcatformularios_idfrm_seq', 1, true);


--
-- Name: tblcatmenu_intidmenu_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.tblcatmenu_intidmenu_seq', 2, true);


--
-- Name: tblcatmenuperfil_intidmenuperfil_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.tblcatmenuperfil_intidmenuperfil_seq', 50, true);


--
-- Name: tblcatperfilusr_idperfil_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.tblcatperfilusr_idperfil_seq', 18, true);


--
-- Name: tblcatperfilusrfrm_idperfilusrfrm_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.tblcatperfilusrfrm_idperfilusrfrm_seq', 107, true);


--
-- Name: tblcatusuario_intid_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.tblcatusuario_intid_seq', 30, true);


--
-- Name: abono_anulado abono_anulado_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.abono_anulado
    ADD CONSTRAINT abono_anulado_pkey PRIMARY KEY (id_anulacion);


--
-- Name: abono_cuota abono_cuota_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.abono_cuota
    ADD CONSTRAINT abono_cuota_pkey PRIMARY KEY (id_relacion);


--
-- Name: abono abono_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.abono
    ADD CONSTRAINT abono_pkey PRIMARY KEY (id_abono);


--
-- Name: calendariopago calendariopago_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.calendariopago
    ADD CONSTRAINT calendariopago_pkey PRIMARY KEY (id_pago);


--
-- Name: clientes clientes_cedula_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.clientes
    ADD CONSTRAINT clientes_cedula_key UNIQUE (cedula);


--
-- Name: clientes clientes_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.clientes
    ADD CONSTRAINT clientes_pkey PRIMARY KEY (idcliente);


--
-- Name: configuracion_costo_venta configuracion_costo_venta_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.configuracion_costo_venta
    ADD CONSTRAINT configuracion_costo_venta_pkey PRIMARY KEY (id_config);


--
-- Name: estatus_solicitud estatus_solicitud_nombre_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.estatus_solicitud
    ADD CONSTRAINT estatus_solicitud_nombre_key UNIQUE (nombre);


--
-- Name: estatus_solicitud estatus_solicitud_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.estatus_solicitud
    ADD CONSTRAINT estatus_solicitud_pkey PRIMARY KEY (idestatus);


--
-- Name: garantia garantia_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.garantia
    ADD CONSTRAINT garantia_pkey PRIMARY KEY (id_garantia);


--
-- Name: mora_diaria mora_diaria_id_cuota_fecha_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mora_diaria
    ADD CONSTRAINT mora_diaria_id_cuota_fecha_key UNIQUE (id_cuota, fecha);


--
-- Name: mora_diaria mora_diaria_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mora_diaria
    ADD CONSTRAINT mora_diaria_pkey PRIMARY KEY (id_mora);


--
-- Name: obligacionesfinancieras obligacionesfinancieras_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.obligacionesfinancieras
    ADD CONSTRAINT obligacionesfinancieras_pkey PRIMARY KEY (id_obligacion);


--
-- Name: prestamo prestamo_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.prestamo
    ADD CONSTRAINT prestamo_pkey PRIMARY KEY (id_prestamo);


--
-- Name: prorroga prorroga_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.prorroga
    ADD CONSTRAINT prorroga_pkey PRIMARY KEY (id_prorroga);


--
-- Name: solicitudprestamo solicitudprestamo_cod_solicitud_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.solicitudprestamo
    ADD CONSTRAINT solicitudprestamo_cod_solicitud_key UNIQUE (cod_solicitud);


--
-- Name: solicitudprestamo solicitudprestamo_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.solicitudprestamo
    ADD CONSTRAINT solicitudprestamo_pkey PRIMARY KEY (id_solicitud);


--
-- Name: sucursales sucursales_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sucursales
    ADD CONSTRAINT sucursales_pkey PRIMARY KEY (sucursal_id);


--
-- Name: tblcatcartera tblcatcartera_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tblcatcartera
    ADD CONSTRAINT tblcatcartera_pkey PRIMARY KEY (idcartera);


--
-- Name: tblcatformulariodetalle tblcatformulariodetalle_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tblcatformulariodetalle
    ADD CONSTRAINT tblcatformulariodetalle_pkey PRIMARY KEY (idfrmdetalle);


--
-- Name: tblcatformularios tblcatformularios_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tblcatformularios
    ADD CONSTRAINT tblcatformularios_pkey PRIMARY KEY (idfrm);


--
-- Name: tblcatformularios tblcatformularios_strformulario_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tblcatformularios
    ADD CONSTRAINT tblcatformularios_strformulario_key UNIQUE (strformulario);


--
-- Name: tblcatformularios tblcatformularios_strnombreform_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tblcatformularios
    ADD CONSTRAINT tblcatformularios_strnombreform_key UNIQUE (strnombreform);


--
-- Name: tblcatmenu tblcatmenu_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tblcatmenu
    ADD CONSTRAINT tblcatmenu_pkey PRIMARY KEY (intidmenu);


--
-- Name: tblcatmenuperfil tblcatmenuperfil_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tblcatmenuperfil
    ADD CONSTRAINT tblcatmenuperfil_pkey PRIMARY KEY (intidmenuperfil);


--
-- Name: tblcatperfilusr tblcatperfilusr_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tblcatperfilusr
    ADD CONSTRAINT tblcatperfilusr_pkey PRIMARY KEY (idperfil);


--
-- Name: tblcatperfilusr tblcatperfilusr_strperfil_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tblcatperfilusr
    ADD CONSTRAINT tblcatperfilusr_strperfil_key UNIQUE (strperfil);


--
-- Name: tblcatperfilusrfrm tblcatperfilusrfrm_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tblcatperfilusrfrm
    ADD CONSTRAINT tblcatperfilusrfrm_pkey PRIMARY KEY (idperfilusrfrm);


--
-- Name: tblcatusuario tblcatusuario_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tblcatusuario
    ADD CONSTRAINT tblcatusuario_pkey PRIMARY KEY (intid);


--
-- Name: tblcatusuario tblcatusuario_strcorreo_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tblcatusuario
    ADD CONSTRAINT tblcatusuario_strcorreo_key UNIQUE (strcorreo);


--
-- Name: prestamo unique_id_solicitud; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.prestamo
    ADD CONSTRAINT unique_id_solicitud UNIQUE (id_solicitud);


--
-- Name: abono_cuota abono_cuota_id_abono_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.abono_cuota
    ADD CONSTRAINT abono_cuota_id_abono_fkey FOREIGN KEY (id_abono) REFERENCES public.abono(id_abono);


--
-- Name: abono_cuota abono_cuota_id_pago_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.abono_cuota
    ADD CONSTRAINT abono_cuota_id_pago_fkey FOREIGN KEY (id_pago) REFERENCES public.calendariopago(id_pago);


--
-- Name: abono abono_id_prestamo_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.abono
    ADD CONSTRAINT abono_id_prestamo_fkey FOREIGN KEY (id_prestamo) REFERENCES public.prestamo(id_prestamo) ON DELETE CASCADE;


--
-- Name: calendariopago calendariopago_id_prestamo_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.calendariopago
    ADD CONSTRAINT calendariopago_id_prestamo_fkey FOREIGN KEY (id_prestamo) REFERENCES public.prestamo(id_prestamo) ON DELETE CASCADE;


--
-- Name: clientes clientes_idcartera_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.clientes
    ADD CONSTRAINT clientes_idcartera_fkey FOREIGN KEY (idcartera) REFERENCES public.tblcatcartera(idcartera) ON DELETE SET NULL;


--
-- Name: clientes clientes_usuario_creo_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.clientes
    ADD CONSTRAINT clientes_usuario_creo_fkey FOREIGN KEY (usuario_creo) REFERENCES public.tblcatusuario(intid) ON DELETE SET NULL;


--
-- Name: clientes clientes_usuario_modifico_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.clientes
    ADD CONSTRAINT clientes_usuario_modifico_fkey FOREIGN KEY (usuario_modifico) REFERENCES public.tblcatusuario(intid) ON DELETE SET NULL;


--
-- Name: tblcatformulariodetalle fk_frm_frmdet; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tblcatformulariodetalle
    ADD CONSTRAINT fk_frm_frmdet FOREIGN KEY (idfrm) REFERENCES public.tblcatformularios(idfrm);


--
-- Name: tblcatusuario fk_usuario_cartera; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tblcatusuario
    ADD CONSTRAINT fk_usuario_cartera FOREIGN KEY (idcartera) REFERENCES public.tblcatcartera(idcartera) ON DELETE SET NULL;


--
-- Name: tblcatusuario fk_usuario_sucursal; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tblcatusuario
    ADD CONSTRAINT fk_usuario_sucursal FOREIGN KEY (sucursal_id) REFERENCES public.sucursales(sucursal_id) ON DELETE SET NULL;


--
-- Name: garantia garantia_id_solicitud_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.garantia
    ADD CONSTRAINT garantia_id_solicitud_fkey FOREIGN KEY (id_solicitud) REFERENCES public.solicitudprestamo(id_solicitud) ON DELETE CASCADE;


--
-- Name: obligacionesfinancieras obligacionesfinancieras_id_solicitud_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.obligacionesfinancieras
    ADD CONSTRAINT obligacionesfinancieras_id_solicitud_fkey FOREIGN KEY (id_solicitud) REFERENCES public.solicitudprestamo(id_solicitud) ON DELETE CASCADE;


--
-- Name: prestamo prestamo_id_solicitud_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.prestamo
    ADD CONSTRAINT prestamo_id_solicitud_fkey FOREIGN KEY (id_solicitud) REFERENCES public.solicitudprestamo(id_solicitud) ON DELETE CASCADE;


--
-- Name: prorroga prorroga_id_prestamo_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.prorroga
    ADD CONSTRAINT prorroga_id_prestamo_fkey FOREIGN KEY (id_prestamo) REFERENCES public.prestamo(id_prestamo) ON DELETE CASCADE;


--
-- Name: solicitudprestamo solicitudprestamo_idcartera_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.solicitudprestamo
    ADD CONSTRAINT solicitudprestamo_idcartera_fkey FOREIGN KEY (idcartera) REFERENCES public.tblcatcartera(idcartera) ON DELETE SET NULL;


--
-- Name: solicitudprestamo solicitudprestamo_idcliente_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.solicitudprestamo
    ADD CONSTRAINT solicitudprestamo_idcliente_fkey FOREIGN KEY (idcliente) REFERENCES public.clientes(idcliente) ON DELETE CASCADE;


--
-- Name: solicitudprestamo solicitudprestamo_idestatus_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.solicitudprestamo
    ADD CONSTRAINT solicitudprestamo_idestatus_fkey FOREIGN KEY (idestatus) REFERENCES public.estatus_solicitud(idestatus) ON DELETE SET NULL;


--
-- Name: tblcatmenuperfil tblcatmenuperfil_idperfil_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tblcatmenuperfil
    ADD CONSTRAINT tblcatmenuperfil_idperfil_fkey FOREIGN KEY (idperfil) REFERENCES public.tblcatperfilusr(idperfil);


--
-- Name: tblcatmenuperfil tblcatmenuperfil_intidmenu_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tblcatmenuperfil
    ADD CONSTRAINT tblcatmenuperfil_intidmenu_fkey FOREIGN KEY (intidmenu) REFERENCES public.tblcatmenu(intidmenu);


--
-- Name: tblcatperfilusrfrm tblcatperfilusrfrm_idfrm_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tblcatperfilusrfrm
    ADD CONSTRAINT tblcatperfilusrfrm_idfrm_fkey FOREIGN KEY (idfrm) REFERENCES public.tblcatformularios(idfrm);


--
-- Name: tblcatperfilusrfrm tblcatperfilusrfrm_idperfil_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tblcatperfilusrfrm
    ADD CONSTRAINT tblcatperfilusrfrm_idperfil_fkey FOREIGN KEY (idperfil) REFERENCES public.tblcatperfilusr(idperfil);


--
-- Name: tblcatusuario tblcatusuario_intidperfil_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tblcatusuario
    ADD CONSTRAINT tblcatusuario_intidperfil_fkey FOREIGN KEY (intidperfil) REFERENCES public.tblcatperfilusr(idperfil);


--
-- PostgreSQL database dump complete
--

