<?php
// ========================================
// DASHBOARD DE ESTUDIANTE
// Interfaz especial para estudiantes
// ========================================

// Iniciar sesión de forma segura
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include("conexion.php");
include("verificar_sesion.php");

// Verificar si el usuario está autenticado y es estudiante
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Verificar que sea estudiante
verificarRoles(['Estudiante']);

// Obtener información del usuario actual
$usuario = obtenerUsuarioActual();

// Obtener la insignia única del estudiante (versión mejorada con debugging)
$insignia_estudiante = null;
$debug_info = [];

try {
    // Debug: Verificar usuario actual
    $debug_info['usuario_id'] = $_SESSION['usuario_id'];
    
    // Buscar insignias en la tabla insigniasotorgadas
    $stmt = $conexion->prepare("
        SELECT i.id as Id_Insignia, i.Nombre_gen_ins as Nombre_Insignia, i.Descripcion, i.Criterio as Criterios_Emision, 
               i.Fecha_Creacion as Fecha_Emision, i.Fecha_Creacion as Institucion, i.Fecha_Creacion as Fecha_Creacion,
               io.fecha_otorgamiento as Fecha_Asignacion, io.clave_insignia, d.Nombre_Completo as Receptor
        FROM insigniasotorgadas io
        INNER JOIN insignias i ON io.insignia_id = i.id
        INNER JOIN destinatario d ON io.destinatario_id = d.id
        WHERE d.id = ?
        LIMIT 1
    ");
    
    if ($stmt) {
        // Obtener el destinatario_id del usuario actual
        $stmt_dest = $conexion->prepare("SELECT id FROM destinatario WHERE Nombre_Completo LIKE ?");
        $nombre_usuario = $_SESSION['nombre'] . '%';
        $stmt_dest->bind_param("s", $nombre_usuario);
        $stmt_dest->execute();
        $result_dest = $stmt_dest->get_result();
        
        if ($result_dest->num_rows > 0) {
            $dest_row = $result_dest->fetch_assoc();
            $destinatario_id = $dest_row['id'];
            $stmt_dest->close();
            
            $stmt->bind_param("i", $destinatario_id);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            $debug_info['insigniasotorgadas_count'] = $resultado->num_rows;
            
            if ($resultado->num_rows > 0) {
                $insignia_estudiante = $resultado->fetch_assoc();
                $debug_info['fuente'] = 'insigniasotorgadas';
            }
            $stmt->close();
        } else {
            $stmt_dest->close();
            $debug_info['error_stmt'] = 'No se encontró destinatario para este usuario';
        }
    } else {
        $debug_info['error_stmt'] = $conexion->error;
    }
    
    // Si no se encontró insignia, intentar con Historial como respaldo
    if (!$insignia_estudiante) {
        $stmt = $conexion->prepare("
            SELECT DISTINCT i.*, h.Fecha_Accion as Fecha_Asignacion
            FROM insignias i
            INNER JOIN Historial h ON i.Id_Insignia = h.Id_Insignia
            WHERE h.Id_Usuario = ? AND h.Id_Insignia IS NOT NULL
            ORDER BY h.Fecha_Accion DESC
            LIMIT 1
        ");
        
        if ($stmt) {
            $stmt->bind_param("i", $_SESSION['usuario_id']);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            $debug_info['historial_count'] = $resultado->num_rows;
            
            if ($resultado->num_rows > 0) {
                $insignia_estudiante = $resultado->fetch_assoc();
                $debug_info['fuente'] = 'Historial';
            }
            $stmt->close();
        } else {
            $debug_info['error_stmt_historial'] = $conexion->error;
        }
    }
    
    // Si aún no se encuentra, intentar por receptor (último recurso)
    if (!$insignia_estudiante) {
        $nombre_completo = $usuario['Nombre'] . ' ' . $usuario['Apellido_Paterno'] . ' ' . $usuario['Apellido_Materno'];
        $stmt = $conexion->prepare("
            SELECT *, Fecha_Emision as Fecha_Asignacion
            FROM insignias 
            WHERE Receptor = ?
            ORDER BY Fecha_Emision DESC
            LIMIT 1
        ");
        
        if ($stmt) {
            $stmt->bind_param("s", $nombre_completo);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            $debug_info['receptor_count'] = $resultado->num_rows;
            $debug_info['nombre_buscado'] = $nombre_completo;
            
            if ($resultado->num_rows > 0) {
                $insignia_estudiante = $resultado->fetch_assoc();
                $debug_info['fuente'] = 'Receptor';
                
                // Crear la relación faltante en insigniasotorgadas
                // Primero obtener el destinatario_id del usuario
                $stmt_destinatario = $conexion->prepare("SELECT id FROM destinatario WHERE Matricula = (SELECT Matricula FROM Usuario WHERE Id_Usuario = ?)");
                $stmt_destinatario->bind_param("i", $_SESSION['usuario_id']);
                $stmt_destinatario->execute();
                $destinatario_result = $stmt_destinatario->get_result();
                
                if ($destinatario_result->num_rows > 0) {
                    $destinatario = $destinatario_result->fetch_assoc();
                    $destinatario_id = $destinatario['id'];
                    
                    // Crear registro en insigniasotorgadas
                    $stmt_relacion = $conexion->prepare("INSERT INTO insigniasotorgadas (insignia_id, destinatario_id, periodo_id, responsable_id, estatus_id, fecha_otorgamiento) VALUES (?, ?, 1, 1, 1, CURDATE())");
                    $stmt_relacion->bind_param("ii", $insignia_estudiante['id'], $destinatario_id);
                    $stmt_relacion->execute();
                    $stmt_relacion->close();
                }
                $stmt_destinatario->close();
            }
            $stmt->close();
        }
    }
    
} catch (Exception $e) {
    error_log("Error al obtener insignia del estudiante: " . $e->getMessage());
    $debug_info['error'] = $e->getMessage();
}

// Contar estadísticas (forzar valor correcto para evitar duplicados)
$total_insignias = $insignia_estudiante ? 1 : 0;
$tiene_insignia = $insignia_estudiante !== null;

// Debug: mostrar información si hay problema
if ($total_insignias > 1) {
    error_log("ERROR: total_insignias = $total_insignias, debería ser máximo 1");
    $total_insignias = 1; // Forzar valor correcto
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Estudiante - Insignias TecNM</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css_profesional.css">
    <style>
        /* Estilos específicos para el dashboard de estudiante */
        
        /* Header personalizado para estudiante */
        header {
          background: linear-gradient(135deg, 
            #1e3c72 0%, 
            #2a5298 50%, 
            #1e3c72 100%);
          backdrop-filter: blur(40px) saturate(180%);
          color: white;
          text-align: center;
          padding: 30px 0;
          position: fixed;
          top: 0;
          left: 0;
          width: 100%;
          z-index: 1000;
          box-shadow: 
            0 8px 32px rgba(0,0,0,0.3),
            inset 0 1px 0 rgba(255,255,255,0.2);
          border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .header-content {
          display: flex;
          align-items: center;
          justify-content: space-between;
          max-width: 1200px;
          margin: 0 auto;
          padding: 0 20px;
          position: relative;
        }
        
        .header-logo {
          position: absolute;
          left: -260px;
          top: 50%;
          transform: translateY(-50%);
          height: 60px;
          width: auto;
          filter: brightness(0) invert(1);
          transition: all 0.3s ease;
        }
        
        .header-logo:hover {
          transform: translateY(-50%) scale(1.1);
          filter: brightness(0) invert(1) drop-shadow(0 0 10px rgba(255, 255, 255, 0.5));
        }
        
        header h1 {
          margin: 0;
          font-size: 28px;
          font-weight: 800;
          text-shadow: 
            0 4px 8px rgba(0,0,0,0.4),
            0 0 20px rgba(59, 130, 246, 0.3);
          background: linear-gradient(135deg, #ffffff 0%, #e2e8f0 50%, #ffffff 100%);
          -webkit-background-clip: text;
          -webkit-text-fill-color: transparent;
          background-clip: text;
          flex: 1;
          text-align: center;
        }
        
        
        
        .user-profile {
          text-align: right;
        }
        
        .user-name {
          font-size: 18px;
          font-weight: 700;
          color: rgba(255, 255, 255, 0.95);
          margin-bottom: 5px;
        }
        
        .user-role {
          font-size: 14px;
          color: rgba(255, 255, 255, 0.8);
          background: rgba(255, 255, 255, 0.1);
          padding: 4px 12px;
          border-radius: 20px;
          display: inline-block;
        }
        
        .logout-btn {
          display: flex;
          align-items: center;
          gap: 8px;
          padding: 12px 20px;
          background: linear-gradient(135deg, #dc3545, #c82333);
          color: white;
          text-decoration: none;
          border-radius: 12px;
          font-weight: 600;
          transition: var(--transition);
          box-shadow: 0 8px 20px rgba(220, 53, 69, 0.3);
        }
        
        .logout-btn:hover {
          transform: translateY(-2px) scale(1.05);
          box-shadow: 0 12px 30px rgba(220, 53, 69, 0.4);
          color: white;
        }
        
        .main-container {
          padding-top: 120px;
          padding-bottom: 40px;
        }
        
        /* Dashboard específico */
        .dashboard-grid {
          display: grid;
          grid-template-columns: 1fr 1fr;
          gap: 30px;
          margin-bottom: 40px;
        }
        
        .insignia-card {
          background: linear-gradient(135deg, 
            rgba(255, 255, 255, 0.1) 0%, 
            rgba(255, 255, 255, 0.05) 100%);
          backdrop-filter: blur(30px);
          border-radius: 20px;
          padding: 40px;
          text-align: center;
          box-shadow: 
            0 15px 30px rgba(0,0,0,0.15),
            inset 0 1px 0 rgba(255,255,255,0.1);
          border: 1px solid rgba(255, 255, 255, 0.1);
          transition: var(--transition);
        }
        
        .insignia-card:hover {
          transform: translateY(-5px) scale(1.02);
          box-shadow: 
            0 25px 50px rgba(0,0,0,0.2),
            inset 0 1px 0 rgba(255,255,255,0.2);
        }
        
        .insignia-image {
          width: 120px;
          height: 120px;
          margin: 0 auto 20px;
          border-radius: 20px;
          box-shadow: 0 10px 30px rgba(0,0,0,0.2);
          transition: var(--transition);
        }
        
        .insignia-image:hover {
          transform: scale(1.1) rotate(5deg);
        }
        
        .insignia-title {
          font-size: 24px;
          font-weight: 800;
          color: rgba(255, 255, 255, 0.95);
          margin-bottom: 15px;
          text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        
        .insignia-description {
          font-size: 16px;
          color: rgba(255, 255, 255, 0.8);
          line-height: 1.6;
          margin-bottom: 20px;
        }
        
        .insignia-date {
          font-size: 14px;
          color: rgba(255, 255, 255, 0.7);
          background: rgba(255, 255, 255, 0.1);
          padding: 8px 16px;
          border-radius: 20px;
          display: inline-block;
        }
        
        /* Stats card */
        .stats-card {
          background: linear-gradient(135deg, 
            rgba(255, 255, 255, 0.08) 0%, 
            rgba(255, 255, 255, 0.03) 100%);
          backdrop-filter: blur(30px);
          border-radius: 20px;
          padding: 30px;
          box-shadow: 
            0 15px 30px rgba(0,0,0,0.15),
            inset 0 1px 0 rgba(255,255,255,0.1);
          border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .stats-title {
          font-size: 20px;
          font-weight: 700;
          color: rgba(255, 255, 255, 0.95);
          margin-bottom: 20px;
          text-align: center;
        }
        
        .stat-item {
          display: flex;
          justify-content: space-between;
          align-items: center;
          padding: 15px 0;
          border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .stat-item:last-child {
          border-bottom: none;
        }
        
        .stat-label {
          font-size: 16px;
          color: rgba(255, 255, 255, 0.8);
          font-weight: 600;
        }
        
        .stat-value {
          font-size: 18px;
          font-weight: 800;
          background: linear-gradient(135deg, 
            #ffffff 0%, 
            #3b82f6 50%, 
            #8b5cf6 100%);
          -webkit-background-clip: text;
          -webkit-text-fill-color: transparent;
          background-clip: text;
        }
        
        /* No insignia state */
        .no-insignia {
          text-align: center;
          padding: 60px 40px;
        }
        
        .no-insignia-icon {
          font-size: 80px;
          color: rgba(255, 255, 255, 0.3);
          margin-bottom: 20px;
        }
        
        .no-insignia-title {
          font-size: 24px;
          font-weight: 700;
          color: rgba(255, 255, 255, 0.8);
          margin-bottom: 15px;
        }
        
        .no-insignia-text {
          font-size: 16px;
          color: rgba(255, 255, 255, 0.6);
          line-height: 1.6;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
          .student-header {
            flex-direction: column;
            gap: 20px;
            text-align: center;
          }
          
          .dashboard-grid {
            grid-template-columns: 1fr;
            gap: 20px;
          }
          
          .insignia-card {
            padding: 30px 20px;
          }
          
          .insignia-image {
            width: 100px;
            height: 100px;
          }
          
          .insignia-title {
            font-size: 20px;
          }
        }

        /* Estilos generales */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            background-attachment: fixed;
            min-height: 100vh;
            color: #ffffff;
        }

        /* Header */
        header {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: white;
            padding: 20px 0;
            box-shadow: 0 8px 40px rgba(0,0,0,0.12);
            position: relative;
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-left h1 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .header-left .subtitle {
            font-size: 14px;
            opacity: 0.9;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-info {
            text-align: right;
        }

        .user-name {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .user-role {
            font-size: 14px;
            opacity: 0.9;
            background: rgba(255,255,255,0.2);
            padding: 4px 12px;
            border-radius: 20px;
        }

        .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            display: inline-block;
        }

        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-2px);
        }

        /* Contenedor principal */
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        /* Tarjetas de estadísticas */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: var(--bg-white);
            padding: 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-light);
            text-align: center;
            transition: var(--transition);
            border-left: 4px solid var(--accent-color);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-medium);
        }

        .stat-card-link {
            text-decoration: none;
            color: inherit;
            display: block;
            transition: var(--transition);
        }

        .stat-card-link:hover {
            text-decoration: none;
            color: inherit;
            transform: translateY(-5px);
            box-shadow: var(--shadow-medium);
        }

        .stat-subtitle {
            font-size: 12px;
            color: var(--text-light);
            margin-top: 5px;
            opacity: 0.8;
        }

        .stat-icon {
            font-size: 48px;
            color: var(--accent-color);
            margin-bottom: 15px;
        }

        .stat-number {
            font-size: 36px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .stat-label {
            font-size: 16px;
            color: var(--text-light);
            font-weight: 500;
        }

        /* Estilos del calendario */
        .calendar-container {
            margin: 15px 0;
        }

        .calendar-header {
            text-align: center;
            margin-bottom: 10px;
        }

        .month-year {
            font-size: 14px;
            font-weight: 600;
            color: var(--primary-color);
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 2px;
            font-size: 11px;
        }

        .calendar-weekday {
            text-align: center;
            font-weight: 600;
            color: var(--text-light);
            padding: 4px 2px;
            font-size: 10px;
        }

        .calendar-day {
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            font-weight: 500;
            transition: var(--transition);
            cursor: pointer;
        }

        .calendar-day.empty {
            visibility: hidden;
        }

        .calendar-day.has-insignia {
            background: var(--accent-color);
            color: white;
            font-weight: 600;
        }

        .calendar-day.today {
            background: var(--primary-color);
            color: white;
            font-weight: 600;
        }

        .calendar-day.today.has-insignia {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
        }

        .calendar-day:hover:not(.empty) {
            background: var(--bg-light);
            transform: scale(1.1);
        }

        .calendar-day.has-insignia:hover {
            background: #20c997;
            transform: scale(1.1);
        }

        /* Estilos del modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            animation: fadeIn 0.3s ease;
        }

        .modal-content {
            background-color: var(--bg-white);
            margin: 15% auto;
            padding: 0;
            border-radius: var(--border-radius);
            width: 90%;
            max-width: 400px;
            box-shadow: var(--shadow-medium);
            animation: slideIn 0.3s ease;
        }

        .modal-header {
            background: var(--primary-color);
            color: white;
            padding: 20px;
            border-radius: var(--border-radius) var(--border-radius) 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 20px;
        }

        .close {
            color: white;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: var(--transition);
        }

        .close:hover {
            opacity: 0.7;
        }

        .modal-body {
            padding: 30px;
            text-align: center;
        }

        .insignia-info {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .modal-footer {
            padding: 20px;
            text-align: center;
            border-top: 1px solid var(--border-color);
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* Sección de insignias */
        .insignias-section {
            background: var(--bg-white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-light);
            overflow: hidden;
        }

        .section-header {
            background: var(--primary-color);
            color: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .section-title {
            font-size: 24px;
            font-weight: 600;
        }

        .view-all-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            font-size: 14px;
        }

        .view-all-btn:hover {
            background: rgba(255,255,255,0.3);
        }

        .insignias-grid {
            padding: 30px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .insignia-card {
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            padding: 20px;
            transition: var(--transition);
            background: var(--bg-white);
        }

        .insignia-card:hover {
            box-shadow: var(--shadow-medium);
            transform: translateY(-2px);
        }

        .insignia-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .insignia-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--accent-color), #20c997);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            margin-right: 15px;
        }

        .insignia-info h3 {
            font-size: 18px;
            color: var(--primary-color);
            margin-bottom: 5px;
        }

        .insignia-date {
            font-size: 14px;
            color: var(--text-light);
        }

        .insignia-description {
            color: var(--text-dark);
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .insignia-actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: var(--secondary-color);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: var(--bg-light);
            color: var(--text-dark);
            border: 1px solid var(--border-color);
        }

        .btn-secondary:hover {
            background: var(--border-color);
        }

        /* Mensaje cuando no hay insignias */
        .no-insignias {
            text-align: center;
            padding: 60px 30px;
            color: var(--text-light);
        }

        .no-insignias i {
            font-size: 64px;
            color: var(--border-color);
            margin-bottom: 20px;
        }

        .no-insignias h3 {
            font-size: 24px;
            margin-bottom: 10px;
            color: var(--text-dark);
        }

        .no-insignias p {
            font-size: 16px;
            line-height: 1.6;
        }

        /* Footer */
        footer {
            background: #1e3c72;
            color: white;
            padding: 40px 0;
            margin-top: 50px;
            text-align: center;
        }
        
        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .footer-section {
            margin-bottom: 25px;
        }
        
        .footer h3 {
            font-size: 16px;
            margin-bottom: 12px;
            color: #fff;
            font-weight: bold;
        }
        
        .footer-links {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 18px;
            margin-bottom: 18px;
        }
        
        .footer-links a {
            color: #fff;
            text-decoration: underline;
            font-size: 14px;
            transition: color 0.3s ease;
        }
        
        .footer-links a:hover {
            color: #a0c4ff;
        }
        
        .footer-social {
            display: flex;
            justify-content: center;
            gap: 18px;
            margin-top: 18px;
        }
        
        .social-icon {
            width: 35px;
            height: 35px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .social-icon:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }
        
        .copyright {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            color: #a0c4ff;
            font-size: 14px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                padding: 0 15px;
            }
            
            .header-logo {
                height: 50px;
                left: -160px;
            }
            
            header h1 {
                font-size: 24px;
            }
            
            .main-container {
                padding-top: 100px;
            }
            
            .header-content {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .header-right {
                flex-direction: column;
                gap: 10px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .insignias-grid {
                grid-template-columns: 1fr;
                padding: 20px;
            }

            .container {
                margin: 20px auto;
                padding: 0 15px;
            }
            
            .footer-links {
                flex-direction: column;
                align-items: center;
                gap: 12px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="header-content">
            <img src="imagen/logo.png" alt="TecNM Logo" class="header-logo">
            <h1>Insignias TecNM</h1>
        </div>
    </header>

    <!-- Contenido principal -->
    <div class="main-container">
        <?php 
        // Mostrar mensaje de éxito del registro
        if (isset($_GET['success']) && $_GET['success'] === 'registro_exitoso'): 
        ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <div>
                    <strong>¡Bienvenido!</strong><br>
                    Tu registro fue exitoso. Ya puedes explorar tu dashboard y ver tus insignias cuando se te asignen.
                </div>
            </div>
        <?php endif; ?>
        
        <div class="dashboard-grid">
            <a href="historial_insignias_simple.php" class="stat-card stat-card-link">
                <div class="stat-icon">
                    <i class="fas fa-trophy"></i>
                </div>
                <div class="stat-number"><?php echo $total_insignias; ?></div>
                <div class="stat-label">Insignia Obtenida</div>
                <div class="stat-subtitle">Ver mi insignia</div>
                <?php if (isset($_GET['debug'])): ?>
                    <div style="font-size: 10px; color: #666; margin-top: 5px;">
                        Debug: insignia_estudiante = <?php echo $insignia_estudiante ? 'EXISTS' : 'NULL'; ?><br>
                        Usuario ID: <?php echo $debug_info['usuario_id']; ?><br>
                        Fuente: <?php echo $debug_info['fuente'] ?? 'Ninguna'; ?><br>
                        insigniasotorgadas: <?php echo $debug_info['insigniasotorgadas_count'] ?? 'N/A'; ?><br>
                        Historial: <?php echo $debug_info['historial_count'] ?? 'N/A'; ?><br>
                        Receptor: <?php echo $debug_info['receptor_count'] ?? 'N/A'; ?><br>
                        <?php if (isset($debug_info['nombre_buscado'])): ?>
                            Nombre buscado: <?php echo htmlspecialchars($debug_info['nombre_buscado']); ?><br>
                        <?php endif; ?>
                        <?php if (isset($debug_info['error'])): ?>
                            Error: <?php echo htmlspecialchars($debug_info['error']); ?><br>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </a>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="calendar-container">
                    <div class="calendar-header">
                        <span class="month-year"><?php echo date('F Y'); ?></span>
                    </div>
                    <div class="calendar-grid">
                        <?php
                        // Obtener días del mes actual
                        $current_month = date('n');
                        $current_year = date('Y');
                        $days_in_month = date('t', mktime(0, 0, 0, $current_month, 1, $current_year));
                        $first_day = date('w', mktime(0, 0, 0, $current_month, 1, $current_year));
                        
                        // Crear array de días con insignias (solo una insignia por usuario)
                        $days_with_insignias = [];
                        if ($insignia_estudiante && isset($insignia_estudiante['Fecha_Asignacion'])) {
                            $fecha = strtotime($insignia_estudiante['Fecha_Asignacion']);
                            if (date('n', $fecha) == $current_month && date('Y', $fecha) == $current_year) {
                                $day = date('j', $fecha);
                                $days_with_insignias[$day] = true;
                            }
                        }
                        
                        // Días de la semana
                        $weekdays = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
                        foreach ($weekdays as $day) {
                            echo "<div class='calendar-weekday'>$day</div>";
                        }
                        
                        // Espacios vacíos para el primer día
                        for ($i = 0; $i < $first_day; $i++) {
                            echo "<div class='calendar-day empty'></div>";
                        }
                        
                        // Días del mes
                        for ($day = 1; $day <= $days_in_month; $day++) {
                            $has_insignia = isset($days_with_insignias[$day]);
                            $is_today = $day == date('j');
                            $class = 'calendar-day';
                            if ($has_insignia) $class .= ' has-insignia';
                            if ($is_today) $class .= ' today';
                            
                            $data_attr = $has_insignia ? "data-has-insignia='true'" : "";
                            echo "<div class='$class' $data_attr>$day</div>";
                        }
                        ?>
                    </div>
                </div>
                <div class="stat-label">Calendario de Insignias</div>
                <div class="stat-subtitle">Días con insignias asignadas</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <img src="imagen/logo.png" alt="TecNM Logo" style="width: 60px; height: 60px; object-fit: contain; border-radius: 8px;">
                </div>
                <div class="stat-label">Institución</div>
                <div class="stat-subtitle">Tecnológico Nacional de México</div>
            </div>
        </div>

        <!-- Insignias recientes -->
        <div class="insignias-section">
            <div class="section-header">
                <h2 class="section-title">🏆 Mi Insignia</h2>
                <a href="historial_insignias_simple.php" class="view-all-btn">
                    Ver Detalles <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <?php if (!$tiene_insignia): ?>
                <div class="no-insignias">
                    <i class="fas fa-award"></i>
                    <h3>¡Aún no tienes insignia!</h3>
                    <p>Tu insignia aparecerá aquí cuando recibas tu reconocimiento. Mantente activo en actividades extracurriculares y programas institucionales para obtener tu insignia.</p>
                </div>
            <?php else: ?>
                <div class="insignias-grid">
                    <div class="insignia-card">
                        <div class="insignia-header">
                            <div class="insignia-icon">
                                <i class="fas fa-medal"></i>
                            </div>
                            <div class="insignia-info">
                                <h3><?php echo htmlspecialchars($insignia_estudiante['Nombre_Insignia']); ?></h3>
                                <div class="insignia-date">
                                    <i class="fas fa-calendar"></i>
                                    <?php echo date('d/m/Y', strtotime($insignia_estudiante['Fecha_Asignacion'])); ?>
                                </div>
                            </div>
                        </div>
                        <div class="insignia-description">
                            <?php echo htmlspecialchars($insignia_estudiante['Descripcion']); ?>
                        </div>
                        <div class="insignia-actions">
                            <a href="reconocimiento_insignia.php?id=<?php echo $insignia_estudiante['Id_Insignia']; ?>" class="btn btn-primary">
                                <i class="fas fa-eye"></i> Ver Insignia
                            </a>
                            <a href="validacion_insignia.php?id=<?php echo $insignia_estudiante['Id_Insignia']; ?>" class="btn btn-secondary">
                                <i class="fas fa-qrcode"></i> Validar
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Enlaces de diagnóstico (solo visible en modo debug) -->
        <?php if (isset($_GET['debug'])): ?>
            <div class="insignias-section" style="margin-top: 20px;">
                <div class="section-header">
                    <h2 class="section-title">🔧 Herramientas de Diagnóstico</h2>
                </div>
                <div style="padding: 20px; text-align: center;">
                    <a href="crear_datos_prueba.php" class="btn btn-primary" style="margin: 5px;">
                        <i class="fas fa-plus"></i> Crear Datos de Prueba
                    </a>
                    <a href="verificacion_rapida.php" class="btn btn-secondary" style="margin: 5px;">
                        <i class="fas fa-search"></i> Verificación Rápida
                    </a>
                    <a href="debug_dashboard_estudiante.php" class="btn btn-secondary" style="margin: 5px;">
                        <i class="fas fa-bug"></i> Debug Completo
                    </a>
                    <a href="verificar_victor.php" class="btn btn-secondary" style="margin: 5px;">
                        <i class="fas fa-user"></i> Verificar Victor
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal para mostrar información de insignia -->
    <div id="insigniaModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>🏆 ¡Insignia Asignada!</h3>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <div class="insignia-info">
                    <i class="fas fa-medal" style="font-size: 48px; color: var(--accent-color); margin-bottom: 15px;"></i>
                    <p id="modalMessage">Se te asignó una insignia en este día.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" onclick="closeModal()">Entendido</button>
            </div>
        </div>
    </div>

    <script>
        // Animaciones suaves al cargar
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.stat-card, .insignia-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });

            // Agregar event listeners a los días del calendario
            const calendarDays = document.querySelectorAll('.calendar-day');
            calendarDays.forEach(day => {
                day.addEventListener('click', function() {
                    if (this.getAttribute('data-has-insignia') === 'true') {
                        const dayNumber = this.textContent;
                        const currentMonth = new Date().toLocaleString('es-ES', { month: 'long' });
                        const currentYear = new Date().getFullYear();
                        
                        document.getElementById('modalMessage').textContent = 
                            `Se te asignó tu insignia el día ${dayNumber} de ${currentMonth} de ${currentYear}.`;
                        
                        document.getElementById('insigniaModal').style.display = 'block';
                    }
                });
            });

            // Cerrar modal al hacer clic en la X
            document.querySelector('.close').addEventListener('click', closeModal);
            
            // Cerrar modal al hacer clic fuera del contenido
            window.addEventListener('click', function(event) {
                const modal = document.getElementById('insigniaModal');
                if (event.target === modal) {
                    closeModal();
                }
            });
        });

        function closeModal() {
            document.getElementById('insigniaModal').style.display = 'none';
        }
    </script>
    
    <!-- Información del usuario y botón Cerrar Sesión -->
    <div style="text-align: center; margin: 30px 0; padding: 20px; background: rgba(255, 255, 255, 0.1); border-radius: 16px; backdrop-filter: blur(10px);">
        <div style="margin-bottom: 15px;">
            <div style="font-size: 18px; font-weight: 700; color: rgba(255, 255, 255, 0.95); margin-bottom: 5px;">
                <?php echo htmlspecialchars($usuario['Nombre'] . ' ' . $usuario['Apellido_Paterno'] . ' ' . $usuario['Apellido_Materno']); ?>
            </div>
            <div style="font-size: 14px; color: rgba(255, 255, 255, 0.8); background: rgba(255, 255, 255, 0.1); padding: 4px 12px; border-radius: 20px; display: inline-block;">
                👨‍🎓 Estudiante
            </div>
        </div>
        <a href="logout.php" class="logout-btn" style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 20px; background: #dc3545; color: white; text-decoration: none; border-radius: 12px; font-weight: 600; transition: all 0.2s ease; box-shadow: 0 4px 12px rgba(220, 53, 69, 0.2);">
            <i class="fas fa-sign-out-alt"></i>
            Cerrar Sesión
        </a>
    </div>
    
    <footer>
        <div class="footer-content">
            <div class="copyright">
                <p>Copyright 2025 - TecNM</p>
                <p>Ultima actualización - Octubre 2025</p>
            </div>
            
            <div class="footer-section">
                <h3>Enlaces</h3>
                <div class="footer-links">
                    <a href="https://datos.gob.mx/" target="_blank">Datos</a>
                    <a href="https://www.gob.mx/publicaciones" target="_blank">Publicaciones</a>
                    <a href="https://consultapublicamx.plataformadetransparencia.org.mx/vut-web/faces/view/consultaPublica.xhtml?idEntidad=MzM=&idSujetoObligado=MTAwMDE=#inicio" target="_blank">Portal de Obligaciones de Transparencia</a>
                    <a href="https://www.gob.mx/pnt" target="_blank">PNT</a>
                    <a href="https://www.inai.org.mx/" target="_blank">INAI</a>
                    <a href="https://www.gob.mx/alerta" target="_blank">Alerta</a>
                    <a href="https://www.gob.mx/denuncia" target="_blank">Denuncia</a>
                </div>
            </div>
            
            <div class="footer-section">
                <h3>¿Qué es gob.mx?</h3>
                <p>Es el portal único de trámites, información y participación ciudadana.</p>
                <a href="https://www.gob.mx/" target="_blank">Leer más</a>
            </div>
            
            <div class="footer-section">
                <div class="footer-links">
                    <a href="https://www.gob.mx/administraciones-anteriores" target="_blank">Administraciones anteriores</a>
                    <a href="https://www.gob.mx/accesibilidad" target="_blank">Declaración de Accesibilidad</a>
                    <a href="https://www.gob.mx/privacidad" target="_blank">Aviso de privacidad</a>
                    <a href="https://www.gob.mx/privacidad-simplificado" target="_blank">Aviso de privacidad simplificado</a>
                    <a href="https://www.gob.mx/terminos" target="_blank">Términos y Condiciones</a>
                </div>
            </div>
            
            <div class="footer-section">
                <div class="footer-links">
                    <a href="https://www.gob.mx/politica-seguridad" target="_blank">Política de seguridad</a>
                    <a href="https://www.gob.mx/denuncia-servidores" target="_blank">Denuncia contra servidores públicos</a>
                </div>
            </div>
            
            <div class="footer-section">
                <h3>Síguenos en</h3>
                <div class="footer-social">
                    <a href="https://www.facebook.com/TecNacionalMexico" target="_blank" class="social-icon">f</a>
                    <a href="https://twitter.com/TecNacionalMex" target="_blank" class="social-icon">X</a>
                    <a href="https://www.youtube.com/user/TecNacionalMexico" target="_blank" class="social-icon">▶</a>
                    <a href="https://www.instagram.com/tecnacionalmexico/" target="_blank" class="social-icon">📷</a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
