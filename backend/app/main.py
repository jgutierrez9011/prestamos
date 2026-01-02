from datetime import timedelta
from typing import Annotated, List

from fastapi import Depends, FastAPI, HTTPException, status
from fastapi.middleware.cors import CORSMiddleware
from sqlalchemy import select
from sqlalchemy.ext.asyncio import AsyncSession

from . import models, schemas
from .auth import authenticate_user, create_access_token, get_current_user
from .db import get_session, settings

app = FastAPI(title="Credimore API", version="0.1.0")

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)


@app.post("/auth/login", response_model=schemas.LoginResponse)
async def login(
    credentials: schemas.LoginRequest,
    session: Annotated[AsyncSession, Depends(get_session)],
):
    user = await authenticate_user(session, credentials.usuario, credentials.clave)
    if not user:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Usuario o contraseña incorrectos",
        )

    nombre = " ".join(
        filter(
            None,
            [user.strpnombre, user.strsnombre, user.strpapellido, user.strsapellido],
        )
    )
    claims = {
        "sub": user.strusuario,
        "idusuario": user.intid,
        "nombreusuario": nombre,
        "correousuario": user.strcorreo,
        "perfilusuario": user.perfil.strperfil if user.perfil else None,
        "carterausuario": user.idcartera,
    }

    access_token_expires = timedelta(minutes=settings.jwt_expires_minutes)
    token = create_access_token(claims, expires_delta=access_token_expires)
    return schemas.LoginResponse(
        access_token=token,
        token_type="bearer",
        user=schemas.UserSession(**claims),
    )


CurrentUser = Annotated[models.Usuario, Depends(get_current_user)]


@app.get("/usuarios/me", response_model=schemas.Usuario)
async def read_current_user(current_user: CurrentUser):
    return current_user


def _user_has_full_access(user: models.Usuario) -> bool:
    perfil = (user.perfil.strperfil or "").lower() if user.perfil else ""
    return "admin" in perfil


@app.get("/carteras", response_model=List[schemas.Cartera])
async def listar_carteras(
    current_user: CurrentUser,
    session: Annotated[AsyncSession, Depends(get_session)],
):
    query = select(models.Cartera).where(models.Cartera.estado.is_(True))
    if not _user_has_full_access(current_user) and current_user.idcartera:
        query = query.where(models.Cartera.idcartera == current_user.idcartera)
    result = await session.execute(query)
    return result.scalars().all()


@app.get("/clientes", response_model=List[schemas.Cliente])
async def listar_clientes(
    current_user: CurrentUser,
    session: Annotated[AsyncSession, Depends(get_session)],
):
    query = select(models.Cliente)
    if not _user_has_full_access(current_user) and current_user.idcartera:
        query = query.where(models.Cliente.idcartera == current_user.idcartera)
    result = await session.execute(query)
    return result.scalars().all()


@app.get("/prestamos", response_model=List[schemas.Prestamo])
async def listar_prestamos(
    current_user: CurrentUser,
    session: Annotated[AsyncSession, Depends(get_session)],
):
    query = select(models.Prestamo).join(models.SolicitudPrestamo)
    if not _user_has_full_access(current_user) and current_user.idcartera:
        query = query.where(models.SolicitudPrestamo.idcartera == current_user.idcartera)
    result = await session.execute(query)
    return result.scalars().all()


@app.get("/health")
async def health():
    return {"status": "ok"}


@app.get("/")
async def root():
    return {
        "message": "Credimore API en FastAPI",
        "docs": "/docs",
    }
