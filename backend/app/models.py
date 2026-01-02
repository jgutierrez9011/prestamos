from __future__ import annotations

from datetime import date, datetime
from decimal import Decimal
from typing import Optional

from sqlalchemy import Boolean, Date, DateTime, ForeignKey, Integer, Numeric, String, Text
from sqlalchemy.orm import Mapped, mapped_column, relationship

from .db import Base


class PerfilUsuario(Base):
    __tablename__ = "tblcatperfilusr"

    idperfil: Mapped[int] = mapped_column(Integer, primary_key=True, autoincrement=True)
    strperfil: Mapped[Optional[str]] = mapped_column(String(50))
    bolactivo: Mapped[Optional[bool]] = mapped_column(Boolean)

    usuarios: Mapped[list["Usuario"]] = relationship(back_populates="perfil")


class Cartera(Base):
    __tablename__ = "tblcatcartera"

    idcartera: Mapped[int] = mapped_column(Integer, primary_key=True, autoincrement=True)
    descripcion: Mapped[str] = mapped_column(String(255))
    monto_maximo: Mapped[Decimal] = mapped_column(Numeric(18, 2))
    monto_minimo: Mapped[Decimal] = mapped_column(Numeric(18, 2))
    fecha_creacion: Mapped[Optional[datetime]] = mapped_column(DateTime(timezone=True))
    usuario_creo: Mapped[str] = mapped_column(String(100))
    fecha_modificacion: Mapped[Optional[datetime]] = mapped_column(DateTime(timezone=True))
    usuario_modifico: Mapped[Optional[str]] = mapped_column(String(100))
    estado: Mapped[bool] = mapped_column(Boolean, default=True)

    usuarios: Mapped[list["Usuario"]] = relationship(back_populates="cartera")
    solicitudes: Mapped[list["SolicitudPrestamo"]] = relationship(back_populates="cartera")


class Usuario(Base):
    __tablename__ = "tblcatusuario"

    intid: Mapped[int] = mapped_column(Integer, primary_key=True, autoincrement=True)
    strpnombre: Mapped[str] = mapped_column(String(50))
    strsnombre: Mapped[Optional[str]] = mapped_column(String(50))
    strpapellido: Mapped[str] = mapped_column(String(50))
    strsapellido: Mapped[Optional[str]] = mapped_column(String(50))
    strsexo: Mapped[Optional[str]] = mapped_column(String(50))
    strcorreo: Mapped[str] = mapped_column(String(50))
    stridentificacion: Mapped[str] = mapped_column(String(20))
    strdireccion: Mapped[str] = mapped_column(Text())
    strcontacto: Mapped[str] = mapped_column(String(100))
    strusuariocreo: Mapped[str] = mapped_column(String(50))
    datfechacreo: Mapped[datetime] = mapped_column(DateTime(timezone=False))
    strusuariomodifico: Mapped[Optional[str]] = mapped_column(String(50))
    datfechamodifico: Mapped[Optional[datetime]] = mapped_column(DateTime(timezone=False))
    datfechabaja: Mapped[Optional[date]] = mapped_column(Date)
    bolactivo: Mapped[Optional[bool]] = mapped_column(Boolean, default=True)
    strusuario: Mapped[Optional[str]] = mapped_column(String(50), unique=True)
    strpassword: Mapped[str] = mapped_column(String(255))
    intidperfil: Mapped[Optional[int]] = mapped_column(ForeignKey("tblcatperfilusr.idperfil"))
    idcartera: Mapped[Optional[int]] = mapped_column(ForeignKey("tblcatcartera.idcartera"))
    sucursal_id: Mapped[Optional[int]] = mapped_column(Integer)

    perfil: Mapped[Optional[PerfilUsuario]] = relationship(back_populates="usuarios")
    cartera: Mapped[Optional[Cartera]] = relationship(back_populates="usuarios")


class Cliente(Base):
    __tablename__ = "clientes"

    idcliente: Mapped[int] = mapped_column(Integer, primary_key=True, autoincrement=True)
    cedula: Mapped[str] = mapped_column(String(50))
    nombre: Mapped[str] = mapped_column(String(255))
    telefono: Mapped[str] = mapped_column(String(20))
    estado_civil: Mapped[str] = mapped_column(String(20))
    actividad_economica: Mapped[str] = mapped_column(Text())
    direccion_domicilio: Mapped[str] = mapped_column(Text())
    tipo_vivienda: Mapped[str] = mapped_column(String(20))
    anos_habitar: Mapped[int] = mapped_column(Integer)
    direccion_negocio: Mapped[str] = mapped_column(Text())
    tipo_local: Mapped[str] = mapped_column(String(20))
    tiempo_operar: Mapped[int] = mapped_column(Integer)
    rubro: Mapped[str] = mapped_column(String(20))
    idcartera: Mapped[Optional[int]] = mapped_column(ForeignKey("tblcatcartera.idcartera"))
    fecha_creo: Mapped[Optional[datetime]] = mapped_column(DateTime(timezone=True))
    usuario_creo: Mapped[int] = mapped_column(Integer)
    fecha_modifico: Mapped[Optional[datetime]] = mapped_column(DateTime(timezone=True))
    usuario_modifico: Mapped[Optional[int]] = mapped_column(Integer)

    cartera: Mapped[Optional[Cartera]] = relationship(back_populates="clientes")
    solicitudes: Mapped[list["SolicitudPrestamo"]] = relationship(back_populates="cliente")


Cartera.clientes = relationship("Cliente", back_populates="cartera")


class SolicitudPrestamo(Base):
    __tablename__ = "solicitudprestamo"

    id_solicitud: Mapped[int] = mapped_column(Integer, primary_key=True, autoincrement=True)
    cod_solicitud: Mapped[int] = mapped_column(Integer)
    idcliente: Mapped[int] = mapped_column(ForeignKey("clientes.idcliente"))
    actividad_economica: Mapped[str] = mapped_column(Text())
    direccion_negocio: Mapped[str] = mapped_column(Text())
    telefono: Mapped[Optional[str]] = mapped_column(String(20))
    tipo_local: Mapped[Optional[str]] = mapped_column(String(50))
    tiempo_operar: Mapped[Optional[str]] = mapped_column(String(50))
    rubro: Mapped[Optional[str]] = mapped_column(String(50))
    monto_solicitado: Mapped[Decimal] = mapped_column(Numeric(10, 2))
    plazo_solicitado: Mapped[float] = mapped_column()
    tasa: Mapped[Decimal] = mapped_column(Numeric(5, 2))
    venta_promedio_bueno: Mapped[Decimal] = mapped_column(Numeric(10, 2))
    venta_promedio_mediano: Mapped[Decimal] = mapped_column(Numeric(10, 2))
    venta_promedio_bajo: Mapped[Decimal] = mapped_column(Numeric(10, 2))
    promedio_venta: Mapped[Decimal] = mapped_column(Numeric(10, 2))
    ventas_mensuales: Mapped[Decimal] = mapped_column(Numeric(10, 2))
    otros_ingresos_negocio: Mapped[Optional[Decimal]] = mapped_column(Numeric(10, 2))
    aportes_familiares: Mapped[Optional[Decimal]] = mapped_column(Numeric(10, 2))
    otros_ingresos: Mapped[Optional[Decimal]] = mapped_column(Numeric(10, 2))
    gasto_costo_venta: Mapped[Optional[Decimal]] = mapped_column(Numeric(10, 2))
    gastos_negocio: Mapped[Optional[Decimal]] = mapped_column(Numeric(10, 2))
    cuotas_credito: Mapped[Optional[Decimal]] = mapped_column(Numeric(10, 2))
    gastos_familiares: Mapped[Optional[Decimal]] = mapped_column(Numeric(10, 2))
    utilidad_final: Mapped[Optional[Decimal]] = mapped_column(Numeric(10, 2))
    tipo_promedio: Mapped[str] = mapped_column(String(20))
    idcartera: Mapped[int] = mapped_column(ForeignKey("tblcatcartera.idcartera"))
    idestatus: Mapped[int] = mapped_column(Integer)
    fecha_solicitud: Mapped[Optional[date]] = mapped_column(Date)
    fecha_creo: Mapped[Optional[datetime]] = mapped_column(DateTime(timezone=True))
    usuario_creo: Mapped[int] = mapped_column(Integer)
    fecha_modifico: Mapped[Optional[datetime]] = mapped_column(DateTime(timezone=True))
    usuario_modifico: Mapped[Optional[int]] = mapped_column(Integer)
    tipo_cliente: Mapped[Optional[str]] = mapped_column(String(20))
    total_ingreso: Mapped[Optional[Decimal]] = mapped_column(Numeric(10, 2))
    total_gasto: Mapped[Optional[Decimal]] = mapped_column(Numeric(10, 2))
    costo_unitario: Mapped[Optional[Decimal]] = mapped_column(Numeric(10, 2))
    precio_venta: Mapped[Optional[Decimal]] = mapped_column(Numeric(10, 2))
    unidades_producidas: Mapped[Optional[Decimal]] = mapped_column(Numeric(10, 2))

    cliente: Mapped["Cliente"] = relationship(back_populates="solicitudes")
    cartera: Mapped["Cartera"] = relationship(back_populates="solicitudes")
    prestamo: Mapped[Optional["Prestamo"]] = relationship(back_populates="solicitud")


class Prestamo(Base):
    __tablename__ = "prestamo"

    id_prestamo: Mapped[int] = mapped_column(Integer, primary_key=True, autoincrement=True)
    id_solicitud: Mapped[int] = mapped_column(ForeignKey("solicitudprestamo.id_solicitud"))
    monto_aprobado: Mapped[Decimal] = mapped_column(Numeric(10, 2))
    interes: Mapped[Decimal] = mapped_column(Numeric(5, 2))
    plazo: Mapped[float] = mapped_column()
    fecha_aprobacion: Mapped[Optional[date]] = mapped_column(Date)
    saldo: Mapped[Decimal] = mapped_column(Numeric(10, 2))
    fecha_primer_cuota: Mapped[Optional[date]] = mapped_column(Date)
    comentario: Mapped[Optional[str]] = mapped_column(Text())
    usuario_creo: Mapped[Optional[int]] = mapped_column(Integer)
    fecha_modifico: Mapped[Optional[datetime]] = mapped_column(DateTime(timezone=True))
    usuario_modifico: Mapped[Optional[int]] = mapped_column(Integer)
    monto_interes: Mapped[Optional[Decimal]] = mapped_column(Numeric(10, 2))
    montotal: Mapped[Optional[Decimal]] = mapped_column(Numeric(10, 2))
    frecuencia: Mapped[Optional[int]] = mapped_column(Integer)
    modalidad: Mapped[Optional[str]] = mapped_column(String(20))
    monto_cuota: Mapped[Optional[Decimal]] = mapped_column(Numeric(10, 2))
    interes_semanal: Mapped[Optional[Decimal]] = mapped_column(Numeric(10, 2))
    fecha_desembolso: Mapped[Optional[date]] = mapped_column(Date)

    solicitud: Mapped[SolicitudPrestamo] = relationship(back_populates="prestamo")
