import hashlib
from datetime import datetime, timedelta
from typing import Annotated, Optional

from fastapi import Depends, HTTPException, status
from fastapi.security import OAuth2PasswordBearer
from jose import JWTError, jwt
from passlib.context import CryptContext
from sqlalchemy import select, update
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy.orm import selectinload

from .db import get_session, settings
from .models import PerfilUsuario, Usuario

pwd_context = CryptContext(schemes=["bcrypt"], deprecated="auto")
oauth2_scheme = OAuth2PasswordBearer(tokenUrl="/auth/login")


async def verify_password_and_migrate(
    session: AsyncSession, user: Usuario, plain_password: str
) -> bool:
    stored_hash = user.strpassword
    # First try bcrypt
    if pwd_context.verify(plain_password, stored_hash):
        # If the stored hash is md5-like, replace it with bcrypt for future logins
        if len(stored_hash) == 32 and all(c in "0123456789abcdef" for c in stored_hash.lower()):
            new_hash = pwd_context.hash(plain_password)
            await session.execute(
                update(Usuario)
                .where(Usuario.intid == user.intid)
                .values(strpassword=new_hash)
            )
            await session.commit()
        return True

    # Backward compatibility with legacy MD5 hashes
    legacy_md5 = hashlib.md5(plain_password.encode()).hexdigest()
    if stored_hash == legacy_md5:
        new_hash = pwd_context.hash(plain_password)
        await session.execute(
            update(Usuario).where(Usuario.intid == user.intid).values(strpassword=new_hash)
        )
        await session.commit()
        return True

    return False


async def authenticate_user(
    session: AsyncSession, username: str, password: str
) -> Optional[Usuario]:
    query = (
        select(Usuario)
        .options(
            selectinload(Usuario.perfil),
            selectinload(Usuario.cartera),
        )
        .where(Usuario.strusuario == username)
        .where(Usuario.bolactivo.is_(True))
    )
    result = await session.execute(query)
    user: Optional[Usuario] = result.scalar_one_or_none()
    if not user:
        return None

    if not await verify_password_and_migrate(session, user, password):
        return None

    # Refresh relationships for downstream claims
    await session.refresh(user)
    if user.intidperfil:
        await session.get(PerfilUsuario, user.intidperfil)
    return user


def create_access_token(data: dict, expires_delta: Optional[timedelta] = None) -> str:
    to_encode = data.copy()
    expire = datetime.utcnow() + (expires_delta or timedelta(minutes=settings.jwt_expires_minutes))
    to_encode.update({"exp": expire})
    encoded_jwt = jwt.encode(
        to_encode,
        settings.jwt_secret,
        algorithm=settings.jwt_algorithm,
    )
    return encoded_jwt


async def get_current_user(
    token: Annotated[str, Depends(oauth2_scheme)],
    session: Annotated[AsyncSession, Depends(get_session)],
) -> Usuario:
    credentials_exception = HTTPException(
        status_code=status.HTTP_401_UNAUTHORIZED,
        detail="Could not validate credentials",
        headers={"WWW-Authenticate": "Bearer"},
    )
    try:
        payload = jwt.decode(token, settings.jwt_secret, algorithms=[settings.jwt_algorithm])
        user_id: Optional[int] = payload.get("idusuario")
        if user_id is None:
            raise credentials_exception
    except JWTError as exc:  # pragma: no cover - defensive
        raise credentials_exception from exc

    user = await session.get(Usuario, user_id)
    if user is None or not user.bolactivo:
        raise credentials_exception
    await session.refresh(user, attribute_names=["perfil", "cartera"])
    return user
