import os
import shutil
import datetime
import subprocess
#import cx_Oracle
import oracledb
import paramiko
import openpyxl


def generar_reporte_txt():

    print("Procesando...")

    fileout = "clientes_cloud.txt"  # Nombre del archivo de salida

    try:
        with open(fileout, "w", encoding="cp1252") as file_out:
            # Cargar el libro de trabajo Excel
            wb = openpyxl.load_workbook('Reporte Cloud Nicaragua.xlsx', data_only=True)
            sheet = wb.active  # Seleccionar la hoja activa del libro de trabajo

            # Iterar sobre las filas a partir de la tercera fila
            for row in sheet.iter_rows(min_row=3):
                try:
                    # Obtener los valores de las columnas desde 'B' hasta 'Z'
                    columns_B_to_Z = [str(cell.internal_value).strip().replace('"', '') if cell.value else '' for cell in row[1:26]]

                    # Obtener los valores de las columnas 'AS' y 'AT'
                    column_AS = str(row[44].internal_value).strip().replace('"', '') if row[44].value else ''
                    column_AT = str(row[45].internal_value).strip().replace('"', '') if row[45].value else ''

                    # Unir los valores obtenidos en una sola línea de texto
                    line = '|'.join(columns_B_to_Z) + '|' + column_AS + '|' + column_AT

                    # Escribir la línea procesada en el archivo de texto
                    file_out.write(line + '\n')
                except Exception as e:
                    print(f"Error al procesar la fila: {e}")
                    return 1 

        print("El Proceso de extraccion de excel a txt se completo.")
        return 0
    except Exception as e:
        print(f"El Proceso de extraccion de excel a txt presento Error general: {e}")
        return 1


def connect_to_db():
    # Establece la conexión con la base de datos
    conexion = oracledb.connect(user='TRAFICO', password='AdminAI_37', dsn='192.168.8.201:3871/AINP')
    print("Conectando con la base de datos.")
    return conexion


def eliminar_registros_vacios():
  # Establecer conexión con la base de datos Oracle
  #connection = cx_Oracle.connect("TRAFICO", "AdminAI_37", "192.168.8.201:3871/AINP")
  connection = connect_to_db()
  # Elimina un registro existente de la tabla
  cursor = connection.cursor()
  try:
     cursor.execute("delete from clientes_cloud where datfechacarga = (select max(datfechacarga) from clientes_cloud) and fecha_venta is null")
     connection.commit()
     connection.close()
     print("Se eliminaron los regitros vacios no validos.")
  except oracledb.DatabaseError as e:
     print("Error al eliminar el registros vacios no validos:", e)
     connection.rollback()
     connection.close()

#Funcion que elimina la carga del dia si ya fue realizada para evitar duplicados
def eliminar_carga():
  # Establecer conexión con la base de datos Oracle
  #connection = cx_Oracle.connect("TRAFICO", "AdminAI_37", "192.168.8.201:3871/AINP")
  connection = connect_to_db()
  # Elimina un registro existente de la tabla
  cursor = connection.cursor()
  try:
     print("Eliminando los regitros cargados anteriormente en el mismo dia.")
     cursor.execute("delete from clientes_cloud where datfechacarga = trunc(current_date)")

     if cursor.rowcount > 0:
        print(f"{cursor.rowcount} registros eliminados.")
     else:
         print("No se eliminaron registros.")
     
     connection.commit()
     connection.close()
  except oracledb.DatabaseError as e:
     print("Error al eliminar el registros vacios no validos:", e)
     connection.rollback()
     connection.close()

#Ruta en la que se encuentra el archivo de perl
#perl_script = os.path.join(os.path.dirname(os.path.realpath(__file__)), "clientes_cloud_2.pl")

#print("Ruta del archivo perl: " + perl_script)

#print("Ejecutando perl para convertir Excel a txt.")
print("Convirtiendo archivo de Excel a txt.")

#Comandos para ejecutar el archivo de perl
#result = subprocess.run(["perl", perl_script],stdout=subprocess.PIPE)
result = generar_reporte_txt()

#Se valida si el archivo de perl finalizo con exito para seguir con los otros pasos
#if result.returncode == 0:
if result == 0:
    print("El proceso de perl ha finalizado correctamente")
     
    #Llamada a funcion para eliminar carga realizada el mismo dia
    eliminar_carga()

    #Ejecuta el SQL loader que manda a llamar el CTL para la carga de los registros en la base de AINP
    print("Cargando base de clientes cloud a base de datos de AINP")

    # Intentamos ejecutar el comando usando subprocess.call
    try:
        clientes = subprocess.call('sqlldr userid=trafico/AdminAI_37@192.168.8.201:3871/AINP control= CLIENTES_CLOUD.CTL rows=10000 direct=true', shell=True)
        print("El comando sqlldr se ejecutó correctamente.")

       #Valida si la carga del CTL fue exitosa
        #if clientes !=0:
        #    print("La carga de clientes cloud usando el CTL a finalizado con error.", clientes)
        
        #else:

        #    print("La carga de clientes cloud usando el CTL a finalizado con exito.", clientes)


        eliminar_registros_vacios()

        ssh = paramiko.SSHClient()
        ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
        ssh.connect('192.168.8.201', username='sai', password='$JuA1u20.21')
        print("Conectando a servidor AINP.")


        print('Ejecutando Auto-Tarea /usr/bin/java -cp "/cdr02/data/ADI/NM400086/AutoRun_fat.jar:./" auto.Ejecutar ''CLOUD01''')
        stdin, stdout, stderr = ssh.exec_command('/usr/bin/java -cp "/cdr02/data/ADI/NM400086/AutoRun_fat.jar:./" auto.Ejecutar ''CLOUD01''')
        print(stdout.read().decode())

        ssh.close()

        print("Conexiones cerradas.")
        print("Proceso finalizado.")

    except subprocess.CalledProcessError as e:
       print(f"Error al ejecutar sqlldr: {e}")
    except FileNotFoundError as e:
       print(f"Error: {e}")

else:
    print("El proceso de conversion de excel a txt ha finalizado con errores")
    print("Proceso finalizado con error.")

#Elimino el archivo de excel
#archivo_fuente = "Reporte Cloud Nicaragua.xlsx"
#historico = "C:\\cloud\\Fuentes y cargas\\historico\\"
#fecha_actual = datetime.datetime.now()
#fecha_formateada = fecha_actual.strftime("%d%m%Y")

#if os.path.isfile(os.path.join(os.path.dirname(os.path.realpath(__file__)), archivo_fuente)) and archivo_fuente.endswith('.xlsx'):
   #Eliminar el archivo fuente
   #os.remove(os.path.dirname(os.path.realpath(__file__)) + "/Reporte Cloud Nicaragua.xlsx")
   # Mover el archivo al directorio nuevo
 #  shutil.move(os.path.dirname(os.path.realpath(__file__)) + "\\Reporte Cloud Nicaragua.xlsx", historico)




