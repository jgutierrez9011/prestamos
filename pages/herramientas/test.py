# mi_script.py
import sys
import time

def generar_numeros(start, end):
    for i in range(start, end + 1):
        print(i)
        sys.stdout.flush()  # Asegura que la salida se limpie después de cada impresión
        time.sleep(1)  # Tiempo de espera opcional entre impresiones

if __name__ == "__main__":
    if len(sys.argv) != 3:
        print("Por favor, proporciona el inicio y fin del rango.")
    else:
        start = int(sys.argv[1])
        end = int(sys.argv[2])
        generar_numeros(start, end)


