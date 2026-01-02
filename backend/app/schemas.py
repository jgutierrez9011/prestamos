from datetime import datetime
from decimal import Decimal
from typing import Optional

from pydantic import BaseModel, Field


class TokenResponse(BaseModel):
    access_token: str
    token_type: str = "bearer"


class UserSession(BaseModel):
    idusuario: int
    nombreusuario: str
    correousuario: str
    perfilusuario: Optional[str]
    carterausuario: Optional[int]


class LoginRequest(BaseModel):
    usuario: str = Field(..., alias="username")
    clave: str = Field(..., alias="password")

    class Config:
        allow_population_by_field_name = True


class LoginResponse(TokenResponse):
    user: UserSession


class Perfil(BaseModel):
    idperfil: int
    strperfil: Optional[str]

    class Config:
        orm_mode = True


class Cartera(BaseModel):
    idcartera: int
    descripcion: str
    estado: bool

    class Config:
        orm_mode = True


class Usuario(BaseModel):
    intid: int
    strusuario: Optional[str]
    strpnombre: str
    strsnombre: Optional[str]
    strpapellido: str
    strsapellido: Optional[str]
    strcorreo: str
    bolactivo: Optional[bool]
    idcartera: Optional[int]
    intidperfil: Optional[int]
    perfil: Optional[Perfil]
    cartera: Optional[Cartera]

    class Config:
        orm_mode = True


class Cliente(BaseModel):
    idcliente: int
    cedula: str
    nombre: str
    telefono: str
    idcartera: Optional[int]
    fecha_creo: Optional[datetime]
    usuario_creo: Optional[int]

    class Config:
        orm_mode = True


class Prestamo(BaseModel):
    id_prestamo: int
    id_solicitud: int
    monto_aprobado: Decimal
    saldo: Decimal
    fecha_aprobacion: Optional[datetime]

    class Config:
        orm_mode = True
