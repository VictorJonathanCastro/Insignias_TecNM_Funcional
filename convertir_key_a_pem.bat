@echo off
echo ============================================
echo Convertir archivo .key FIEL a formato PEM
echo ============================================
echo.

set KEY_FILE=C:\Users\vc556\Desktop\Fiel\Claveprivada_FIEL_CASV0205071J4_20250314_153222.key
set PEM_FILE=C:\Users\vc556\Desktop\Fiel\clave_privada.pem
set OPENSSL=C:\xampp\apache\bin\openssl.exe

echo Archivo de entrada: %KEY_FILE%
echo Archivo de salida: %PEM_FILE%
echo.
echo NOTA: Necesitaras ingresar la contraseña de tu FIEL cuando se solicite.
echo.

"%OPENSSL%" pkcs8 -inform DER -in "%KEY_FILE%" -out "%PEM_FILE%"

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ============================================
    echo CONVERSION EXITOSA!
    echo ============================================
    echo El archivo PEM se guardo en:
    echo %PEM_FILE%
    echo.
) else (
    echo.
    echo ============================================
    echo ERROR EN LA CONVERSION
    echo ============================================
    echo Verifica:
    echo 1. Que la contraseña sea correcta
    echo 2. Que el archivo .key no este corrupto
    echo.
)

pause

