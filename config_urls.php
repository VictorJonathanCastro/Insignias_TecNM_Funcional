<?php
// Configuración central de URLs para el sistema de insignias
// Este archivo centraliza la configuración de URLs públicas y locales

// Configuración de IPs
$IP_LOCAL = '192.168.31.22';
$IP_PUBLICA = '201.113.20.220';
$LOCALTUNNEL_URL = 'https://fat-fly-48.loca.lt';

// Función para obtener la URL base correcta según el contexto
function getBaseUrl() {
    $host = $_SERVER['HTTP_HOST'];
    
    // Si es localhost o IP local, usar IP pública para compartir
    if (strpos($host, 'localhost') !== false || 
        strpos($host, '127.0.0.1') !== false || 
        strpos($host, '192.168.') !== false) {
        return 'http://201.113.20.220/Insignias_TecNM_Funcional';
    }
    
    // Si ya es la IP pública, usar la misma
    if (strpos($host, '201.113.20.220') !== false) {
        return 'http://201.113.20.220/Insignias_TecNM_Funcional';
    }
    
    // Para otros casos, usar la URL actual
    return 'http://' . $host . '/Insignias_TecNM_Funcional';
}

// Función para obtener solo el dominio/IP base
function getBaseDomain() {
    $host = $_SERVER['HTTP_HOST'];
    
    // Si es localhost o IP local, usar IP pública
    if (strpos($host, 'localhost') !== false || 
        strpos($host, '127.0.0.1') !== false || 
        strpos($host, '192.168.') !== false) {
        return 'http://201.113.20.220';
    }
    
    // Si ya es la IP pública, usar la misma
    if (strpos($host, '201.113.20.220') !== false) {
        return 'http://201.113.20.220';
    }
    
    // Para otros casos, usar la URL actual
    return 'http://' . $host;
}

// Función JavaScript para usar en el frontend
function getJavaScriptUrlFunction() {
    return "
    function getCorrectIP() {
        const hostname = window.location.hostname;
        
        // Si es localhost o IP local, usar IP pública para compartir
        if (hostname === 'localhost' || hostname === '127.0.0.1' || hostname.startsWith('192.168.')) {
            return 'http://201.113.20.220';
        }
        
        // Si ya es la IP pública, usar la misma
        if (hostname === '201.113.20.220') {
            return 'http://201.113.20.220';
        }
        
        const port = window.location.port || '80';
        return `${window.location.protocol}//${hostname}${port !== '80' ? ':' + port : ''}`;
    }";
}
?>
