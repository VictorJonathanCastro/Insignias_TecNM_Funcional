# Script PowerShell para subir archivos de correo al servidor
# Ejecuta este script desde PowerShell: .\subir_cambios_correo.ps1

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Subiendo archivos de correo al servidor" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Configuración
$ruta_llave = "C:\Users\vc556\Desktop\llaves\ssh_insignias"
$servidor = "158.23.160.163"
$usuario = "devusr01"
$ruta_servidor = "/var/www/html/Insignias_TecNM_Funcional"
$ruta_local = "C:\xampp\htdocs\Insignias_TecNM_Funcional"

# Verificar que existe la llave SSH
if (-not (Test-Path $ruta_llave)) {
    Write-Host "❌ Error: No se encuentra la llave SSH en: $ruta_llave" -ForegroundColor Red
    Write-Host "Por favor, verifica la ruta de la llave SSH." -ForegroundColor Yellow
    exit 1
}

# Verificar que existe la carpeta local
if (-not (Test-Path $ruta_local)) {
    Write-Host "❌ Error: No se encuentra la carpeta local: $ruta_local" -ForegroundColor Red
    exit 1
}

# Archivos a subir
$archivos = @(
    "funciones_correo_real.php",
    "config_smtp.php",
    "metadatos_formulario.php",
    "ver_insignia_completa.php",
    "probar_correo_tiempo_real.php",
    "verificar_correos_enviados.php"
)

Write-Host "📋 Archivos a subir:" -ForegroundColor Yellow
foreach ($archivo in $archivos) {
    $ruta_completa = Join-Path $ruta_local $archivo
    if (Test-Path $ruta_completa) {
        Write-Host "  ✅ $archivo" -ForegroundColor Green
    } else {
        Write-Host "  ❌ $archivo (NO ENCONTRADO)" -ForegroundColor Red
    }
}
Write-Host ""

# Confirmar antes de subir
$confirmar = Read-Host "¿Deseas continuar y subir estos archivos? (S/N)"
if ($confirmar -ne "S" -and $confirmar -ne "s") {
    Write-Host "Operación cancelada." -ForegroundColor Yellow
    exit 0
}

Write-Host ""
Write-Host "🚀 Subiendo archivos..." -ForegroundColor Cyan
Write-Host ""

$errores = 0
$exitosos = 0

foreach ($archivo in $archivos) {
    $ruta_completa = Join-Path $ruta_local $archivo
    
    if (-not (Test-Path $ruta_completa)) {
        Write-Host "⚠️  Saltando $archivo (no encontrado)" -ForegroundColor Yellow
        continue
    }
    
    Write-Host "📤 Subiendo $archivo..." -ForegroundColor Cyan
    
    # Comando SCP
    $comando = "scp -i `"$ruta_llave`" `"$ruta_completa`" ${usuario}@${servidor}:${ruta_servidor}/"
    
    try {
        Invoke-Expression $comando
        if ($LASTEXITCODE -eq 0) {
            Write-Host "  ✅ $archivo subido exitosamente" -ForegroundColor Green
            $exitosos++
        } else {
            Write-Host "  ❌ Error al subir $archivo" -ForegroundColor Red
            $errores++
        }
    } catch {
        Write-Host "  ❌ Error al subir $archivo : $_" -ForegroundColor Red
        $errores++
    }
    
    Write-Host ""
}

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Resumen" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "✅ Archivos subidos exitosamente: $exitosos" -ForegroundColor Green
if ($errores -gt 0) {
    Write-Host "❌ Archivos con errores: $errores" -ForegroundColor Red
}
Write-Host ""

if ($errores -eq 0) {
    Write-Host "🎉 ¡Todos los archivos se subieron correctamente!" -ForegroundColor Green
    Write-Host ""
    Write-Host "📝 Próximos pasos:" -ForegroundColor Yellow
    Write-Host "1. Conéctate al servidor y ajusta permisos:" -ForegroundColor White
    Write-Host "   ssh -i `"$ruta_llave`" ${usuario}@${servidor}" -ForegroundColor Gray
    Write-Host "   cd $ruta_servidor" -ForegroundColor Gray
    Write-Host "   sudo chown www-data:www-data *.php" -ForegroundColor Gray
    Write-Host "   sudo chmod 644 *.php" -ForegroundColor Gray
    Write-Host ""
    Write-Host "2. Prueba el correo:" -ForegroundColor White
    Write-Host "   http://${servidor}/probar_correo_tiempo_real.php" -ForegroundColor Gray
} else {
    Write-Host "⚠️  Algunos archivos tuvieron errores. Revisa los mensajes arriba." -ForegroundColor Yellow
}

Write-Host ""

