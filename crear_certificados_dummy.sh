#!/bin/bash
# Script para crear archivos dummy de certificados (solo para evitar errores)
# NOTA: Estos archivos NO funcionarán para firmar realmente

cd /var/www/html

# Crear carpeta si no existe
mkdir -p certificados

# Crear archivo .cer dummy (formato PEM básico)
cat > certificados/dummy.cer << 'EOF'
-----BEGIN CERTIFICATE-----
MIIDXTCCAkWgAwIBAgIJAKL7Q8Q3Q3Q3QMA0GCSqGSIb3DQEBCQUAMHkxCzAJBgNV
BAYTAk1YMRAwDgYDVQQIDAdNZXhpY28xEjAQBgNVBAcMCUNpdWRhZCBkZTEMMAoG
A1UECgwDU0FUMQwwCgYDVQQLDANGSUVMMQwwCgYDVQQDDANGSUVMMSEwHwYJKoZI
hvcNAQkBFhJub2VtYWlsQGV4YW1wbGUuY29tMB4XDTIwMDEwMTAwMDAwMFoXDTI1
MDEwMTAwMDAwMFoweTELMAkGA1UEBhMCTVgxEDAOBgNVBAgMB01leGljbzESMBAG
A1UEBwwJQ2l1ZGFkIGRlMQwwCgYDVQQKDANTRVQxDDAKBgNVBAsMA0ZJRUwxDDAK
BgNVBAMMA0ZJRUwxITAfBgkqhkiG9wNAQkBFhJub2VtYWlsQGV4YW1wbGUuY29t
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAyEjemploDeClavePublica
-----END CERTIFICATE-----
EOF

# Crear archivo .key dummy (formato PEM básico)
cat > certificados/dummy.key << 'EOF'
-----BEGIN PRIVATE KEY-----
MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQDISjemploDeClave
PrivadaNoFuncionalSoloParaEvitarErroresEnElSistemaDeInsigniasTecNM
-----END PRIVATE KEY-----
EOF

# Ajustar permisos
chmod 644 certificados/dummy.cer
chmod 644 certificados/dummy.key

echo "✅ Archivos dummy creados en certificados/"
echo "⚠️  NOTA: Estos archivos NO funcionarán para firmar realmente"
echo "⚠️  Son solo para evitar errores en el sistema"

