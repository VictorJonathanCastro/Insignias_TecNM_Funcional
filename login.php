<?php 
session_start();

// Si el usuario ya está logueado, redirigir al módulo correspondiente
if (isset($_SESSION['usuario_id']) && isset($_SESSION['rol'])) {
    if ($_SESSION['rol'] === 'Admin' || $_SESSION['rol'] === 'SuperUsuario') {
        header('Location: modulo_de_administracion.php');
    } else if ($_SESSION['rol'] === 'Estudiante') {
        header('Location: estudiante_dashboard.php');
    } else {
        header('Location: index.php');
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sistema de Insignias Digitales TecNM - Iniciar Sesión</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <style>
    /* -------- VARIABLES CSS INSTITUCIONALES TECNM -------- */
    :root {
      --tecnm-azul-principal: #003366;
      --tecnm-azul-secundario: #0066CC;
      --tecnm-azul-claro: #4A90E2;
      --tecnm-blanco: #FFFFFF;
      --tecnm-gris-claro: #F5F7FA;
      --tecnm-gris-medio: #E8ECF0;
      --tecnm-dorado: #FFD700;
      --tecnm-plata: #C0C0C0;
      --text-dark: #1A1A1A;
      --text-light: #6B7280;
      --shadow-light: 0 4px 20px rgba(0,51,102,0.1);
      --shadow-medium: 0 8px 40px rgba(0,51,102,0.15);
      --shadow-heavy: 0 20px 60px rgba(0,51,102,0.2);
      --border-radius: 20px;
      --transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* -------- ESTILOS GENERALES -------- */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    html {
      scroll-behavior: smooth;
    }

    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      font-weight: 400;
      background: 
        radial-gradient(circle at 15% 25%, rgba(0, 102, 204, 0.18) 0%, transparent 45%),
        radial-gradient(circle at 85% 75%, rgba(0, 51, 102, 0.15) 0%, transparent 45%),
        radial-gradient(circle at 50% 15%, rgba(74, 144, 226, 0.12) 0%, transparent 45%),
        radial-gradient(circle at 25% 85%, rgba(0, 102, 204, 0.1) 0%, transparent 45%),
        linear-gradient(135deg, 
          #e8f0f8 0%, 
          #d5e3f0 20%, 
          #c5d8ec 40%, 
          #d5e3f0 60%, 
          #e8f0f8 80%, 
          #f0f5fa 100%);
      background-attachment: fixed;
      background-size: 100% 100%;
      min-height: 100vh;
      position: relative;
      overflow-x: hidden;
      color: var(--tecnm-azul-principal);
    }

    /* Efectos de partículas sutiles institucionales */
    body::before {
      content: '';
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-image: 
        radial-gradient(ellipse at 10% 20%, rgba(0, 102, 204, 0.12) 0%, transparent 50%),
        radial-gradient(ellipse at 90% 80%, rgba(0, 51, 102, 0.10) 0%, transparent 50%),
        radial-gradient(ellipse at 50% 10%, rgba(74, 144, 226, 0.08) 0%, transparent 50%),
        radial-gradient(ellipse at 30% 90%, rgba(0, 102, 204, 0.08) 0%, transparent 50%),
        repeating-linear-gradient(45deg, transparent, transparent 2px, rgba(0, 102, 204, 0.015) 2px, rgba(0, 102, 204, 0.015) 4px);
      z-index: -1;
      animation: subtleMove 30s ease-in-out infinite;
    }
    
    @keyframes subtleMove {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-20px); }
    }
    
    body::after {
      content: '';
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: 
        repeating-linear-gradient(0deg, 
          transparent, 
          transparent 2px, 
          rgba(0, 102, 204, 0.02) 2px, 
          rgba(0, 102, 204, 0.02) 4px);
      z-index: -1;
      pointer-events: none;
    }

    /* -------- CONTENEDOR LOGIN ULTRA-PROFESIONAL -------- */
    .login-container {
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      padding: 40px 20px;
      position: relative;
      animation: fadeIn 0.8s ease-out;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .login-box {
      width: 100%;
      max-width: 520px;
      background: 
        linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(240, 248, 255, 0.9) 50%, rgba(255, 255, 255, 0.95) 100%);
      backdrop-filter: blur(50px) saturate(200%);
      -webkit-backdrop-filter: blur(50px) saturate(200%);
      border-radius: 40px;
      padding: 60px 50px;
      text-align: center;
      box-shadow: 
        0 40px 80px rgba(0, 51, 102, 0.18),
        0 20px 40px rgba(0, 51, 102, 0.12),
        0 10px 20px rgba(0, 51, 102, 0.08),
        inset 0 2px 0 rgba(255, 255, 255, 0.9),
        inset 0 -2px 0 rgba(0, 102, 204, 0.15),
        0 0 0 1px rgba(255, 255, 255, 0.5);
      border: 3px solid rgba(255, 255, 255, 0.3);
      position: relative;
      overflow: hidden;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      animation: slideUp 0.8s ease-out 0.2s both;
    }
    
    @keyframes slideUp {
      from { 
        opacity: 0;
        transform: translateY(40px) scale(0.95);
      }
      to { 
        opacity: 1;
        transform: translateY(0) scale(1);
      }
    }

    /* Elementos decorativos creativos */
    .login-box::after {
      content: '';
      position: absolute;
      top: -100%;
      right: -100%;
      width: 300%;
      height: 300%;
      background: 
        radial-gradient(circle at center, rgba(0, 102, 204, 0.08) 0%, transparent 70%),
        radial-gradient(circle at 30% 40%, rgba(74, 144, 226, 0.05) 0%, transparent 60%);
      animation: float 15s ease-in-out infinite;
      z-index: -1;
      pointer-events: none;
    }
    
    .login-box::before {
      content: '';
      position: absolute;
      bottom: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: 
        radial-gradient(circle at center, rgba(0, 51, 102, 0.06) 0%, transparent 70%);
      animation: float 12s ease-in-out infinite reverse;
      z-index: -1;
      pointer-events: none;
    }

    @keyframes float {
      0%, 100% { transform: translateY(0px) rotate(0deg); }
      50% { transform: translateY(-20px) rotate(180deg); }
    }
    
    .login-box-top-gradient {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 8px;
      background: linear-gradient(90deg, 
        var(--tecnm-azul-principal) 0%, 
        var(--tecnm-azul-secundario) 25%, 
        var(--tecnm-dorado) 50%, 
        var(--tecnm-azul-secundario) 75%, 
        var(--tecnm-azul-principal) 100%);
      border-radius: 40px 40px 0 0;
      box-shadow: 0 4px 12px rgba(0, 102, 204, 0.3);
      z-index: 1;
    }

    .logo-container {
      margin-bottom: 45px;
      position: relative;
      animation: logoFloat 3s ease-in-out infinite;
    }
    
    @keyframes logoFloat {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-8px); }
    }

    .login-box img.logo {
      width: 140px;
      height: 140px;
      border-radius: 50%;
      border: 5px solid transparent;
      background: 
        linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(240, 248, 255, 0.9) 100%),
        linear-gradient(135deg, var(--tecnm-azul-principal) 0%, var(--tecnm-azul-secundario) 50%, var(--tecnm-azul-claro) 100%);
      background-origin: border-box;
      background-clip: padding-box, border-box;
      padding: 15px;
      transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
      box-shadow: 
        0 30px 60px rgba(0, 51, 102, 0.2),
        0 15px 30px rgba(0, 51, 102, 0.15),
        0 5px 15px rgba(0, 51, 102, 0.1),
        inset 0 2px 0 rgba(255, 255, 255, 0.9);
      position: relative;
      z-index: 2;
    }

    .login-box img.logo::before {
      content: '';
      position: absolute;
      top: -8px;
      left: -8px;
      right: -8px;
      bottom: -8px;
      border-radius: 50%;
      background: linear-gradient(45deg, 
        var(--tecnm-azul-principal), 
        var(--tecnm-azul-secundario), 
        var(--tecnm-dorado), 
        var(--tecnm-azul-secundario), 
        var(--tecnm-azul-principal));
      z-index: -1;
      animation: rotate 3s linear infinite;
    }

    @keyframes rotate {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    .login-box img.logo:hover {
      transform: scale(1.1) rotate(5deg);
      box-shadow: 
        0 32px 64px rgba(0, 51, 102, 0.2),
        0 16px 32px rgba(0, 51, 102, 0.15),
        inset 0 1px 0 rgba(255, 255, 255, 0.9);
    }

    /* -------- FLECHA DE NAVEGACIÓN ULTRA-PROFESIONAL -------- */
    .back-arrow {
      position: absolute;
      top: 20px;
      left: 20px;
      z-index: 10;
    }

    .back-arrow a {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 50px;
      height: 50px;
      background: linear-gradient(135deg, 
        var(--tecnm-azul-principal) 0%, 
        var(--tecnm-azul-secundario) 50%, 
        var(--tecnm-azul-principal) 100%);
      color: white;
      border-radius: 50%;
      text-decoration: none;
      transition: var(--transition);
      box-shadow: 
        0 15px 30px rgba(0, 51, 102, 0.4),
        inset 0 1px 0 rgba(255,255,255,0.2);
      border: 2px solid rgba(255, 255, 255, 0.2);
    }

    .back-arrow a:hover {
      transform: translateY(-3px) scale(1.1);
      box-shadow: 
        0 20px 40px rgba(27, 57, 106, 0.5),
        inset 0 1px 0 rgba(255,255,255,0.3);
    }

    .back-arrow i {
      font-size: 18px;
      font-weight: bold;
    }

    .login-box h2 {
      font-size: 36px;
      font-weight: 900;
      background: linear-gradient(135deg, 
        var(--tecnm-azul-principal) 0%, 
        var(--tecnm-azul-secundario) 50%, 
        var(--tecnm-azul-principal) 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 16px;
      text-shadow: 0 0 30px rgba(0, 102, 204, 0.3);
    }

    .login-box .subtitle {
      font-size: 18px;
      color: var(--tecnm-azul-principal);
      font-style: italic;
      margin-bottom: 50px;
      line-height: 1.6;
      text-shadow: 0 2px 4px rgba(0, 51, 102, 0.1);
    }

    /* -------- CAMPOS DE ENTRADA ULTRA-PROFESIONALES -------- */
    .input-group {
      margin: 30px 0;
      text-align: left;
      position: relative;
    }

    .input-group label {
      font-weight: 700;
      font-size: 16px;
      display: block;
      margin-bottom: 12px;
      color: var(--tecnm-azul-principal);
      transition: var(--transition);
      text-shadow: 0 2px 4px rgba(0, 51, 102, 0.1);
    }

    .input-wrapper {
      position: relative;
      display: flex;
      align-items: center;
    }

    .input-wrapper i {
      position: absolute;
      left: 20px;
      color: var(--tecnm-azul-secundario);
      font-size: 18px;
      z-index: 2;
      transition: var(--transition);
    }

    .input-group input {
      width: 100%;
      padding: 20px 20px 20px 60px;
      border: 2px solid var(--tecnm-gris-medio);
      border-radius: 16px;
      font-size: 18px;
      outline: none;
      transition: var(--transition);
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(20px);
      color: var(--tecnm-azul-principal);
      box-shadow: 
        0 8px 32px rgba(0, 51, 102, 0.1),
        inset 0 1px 0 rgba(255, 255, 255, 0.8);
    }

    .input-group input::placeholder {
      color: var(--tecnm-gris-medio);
    }

    .input-group input:focus {
      border-color: var(--tecnm-azul-secundario);
      background: rgba(255, 255, 255, 0.95);
      box-shadow: 
        0 0 0 4px rgba(0, 102, 204, 0.2),
        0 12px 40px rgba(0, 51, 102, 0.15),
        inset 0 1px 0 rgba(255, 255, 255, 0.9);
    }

    .input-group input:focus + i {
      color: var(--tecnm-azul-secundario);
      transform: scale(1.1);
    }

    .input-group input:valid {
      border-color: rgba(34, 197, 94, 0.5);
    }

    .input-group.focused label {
      color: var(--tecnm-azul-secundario);
      transform: translateY(-2px);
    }

    .input-group.focused .input-wrapper i {
      color: rgba(59, 130, 246, 0.8);
      transform: scale(1.1);
    }

    /* -------- BOTÓN MOSTRAR/OCULTAR CONTRASEÑA ULTRA-PROFESIONAL -------- */
    .password-toggle {
      position: absolute;
      right: 20px;
      top: 50%;
      transform: translateY(-50%);
      background: linear-gradient(135deg, 
        rgba(255, 255, 255, 0.1) 0%, 
        rgba(255, 255, 255, 0.05) 100%);
      border: 1px solid rgba(255, 255, 255, 0.1);
      color: rgba(255, 255, 255, 0.6);
      cursor: pointer;
      padding: 8px;
      border-radius: 50%;
      transition: var(--transition);
      z-index: 3;
      display: flex;
      align-items: center;
      justify-content: center;
      width: 40px;
      height: 40px;
      backdrop-filter: blur(10px);
    }

    .password-toggle:hover {
      color: rgba(59, 130, 246, 0.8);
      background: linear-gradient(135deg, 
        rgba(59, 130, 246, 0.2) 0%, 
        rgba(59, 130, 246, 0.1) 100%);
      transform: translateY(-50%) scale(1.1);
      border-color: rgba(59, 130, 246, 0.3);
    }

    .password-toggle:active {
      transform: translateY(-50%) scale(0.95);
    }

    .password-toggle i {
      font-size: 16px;
      transition: var(--transition);
    }

    .password-toggle.active i {
      color: rgba(59, 130, 246, 0.9);
    }

    /* -------- BOTÓN ULTRA-PROFESIONAL -------- */
    .btn {
      background: linear-gradient(135deg, 
        var(--tecnm-azul-principal) 0%, 
        var(--tecnm-azul-secundario) 50%, 
        var(--tecnm-azul-principal) 100%);
      color: white;
      border: none;
      padding: 20px 40px;
      width: 100%;
      border-radius: 20px;
      font-size: 20px;
      font-weight: 800;
      cursor: pointer;
      margin-top: 40px;
      transition: var(--transition);
      position: relative;
      overflow: hidden;
      text-transform: uppercase;
      letter-spacing: 2px;
      box-shadow: 
        0 20px 40px rgba(0, 51, 102, 0.4),
        inset 0 1px 0 rgba(255, 255, 255, 0.2);
      border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .btn::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, 
        transparent, 
        rgba(255,255,255,0.3), 
        transparent);
      transition: left 0.6s;
    }

    .btn:hover {
      transform: translateY(-4px) scale(1.02);
      box-shadow: 
        0 30px 60px rgba(0, 51, 102, 0.5),
        inset 0 1px 0 rgba(255, 255, 255, 0.3);
    }

    .btn:hover::before {
      left: 100%;
    }

    .btn:active {
      transform: translateY(-2px) scale(1.01);
    }

    .btn:disabled {
      opacity: 0.6;
      cursor: not-allowed;
      transform: none;
    }

    .btn.loading {
      background: linear-gradient(135deg, #6c757d, #495057);
      cursor: not-allowed;
    }

    .btn i {
      margin-right: 12px;
      font-size: 18px;
    }

    /* -------- MENSAJE ERROR -------- */
    .error {
      background: linear-gradient(135deg, #ff6b6b, #ee5a52);
      color: white;
      padding: 12px 15px;
      border-radius: var(--border-radius);
      margin-bottom: 20px;
      font-size: 14px;
      font-weight: 500;
      display: flex;
      align-items: center;
      justify-content: center;
      animation: shake 0.5s ease-in-out;
    }

    .error i {
      margin-right: 8px;
      font-size: 16px;
    }

    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      25% { transform: translateX(-5px); }
      75% { transform: translateX(5px); }
    }

    /* -------- HEADER ULTRA-PROFESIONAL -------- */
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
    }
    
    .header-content {
      display: flex;
      align-items: center;
      justify-content: center;
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

    /* -------- FOOTER ULTRA-PROFESIONAL -------- */
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
      
      .footer-links {
        flex-direction: column;
        align-items: center;
        gap: 12px;
      }
    }

    footer p {
      margin: 0;
      opacity: 0.9;
      text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }

    /* -------- RESPONSIVE ULTRA-OPTIMIZADO -------- */
    @media (max-width: 768px) {
      .login-container {
        padding: 30px 15px;
        padding-top: 120px;
        padding-bottom: 120px;
      }
      
      .login-box {
        padding: 50px 40px;
        max-width: 420px;
      }
      
      .login-box h2 {
        font-size: 28px;
      }
      
      .login-box .subtitle {
        font-size: 16px;
      }
      
      header h1 {
        font-size: 24px;
      }
      
      .input-group input {
        font-size: 16px; /* Previene zoom en iOS */
        padding: 18px 18px 18px 55px;
      }
      
      .input-wrapper i {
        left: 18px;
        font-size: 16px;
      }
      
      .password-toggle {
        right: 18px;
        width: 36px;
        height: 36px;
      }
      
      .btn {
        padding: 18px 35px;
        font-size: 18px;
      }
    }

    @media (max-width: 480px) {
      .login-box {
        padding: 40px 30px;
        max-width: 380px;
      }
      
      .login-box img.logo {
        width: 100px;
        height: 100px;
      }
      
      .back-arrow {
        top: 15px;
        left: 15px;
      }
      
      .back-arrow a {
        width: 45px;
        height: 45px;
      }
      
      .back-arrow i {
        font-size: 16px;
      }
      
      .login-box h2 {
        font-size: 24px;
      }
      
      .login-box .subtitle {
        font-size: 14px;
      }
      
      .input-group input {
        padding: 16px 16px 16px 50px;
        font-size: 16px;
      }
      
      .input-wrapper i {
        left: 16px;
        font-size: 14px;
      }
      
      .password-toggle {
        right: 16px;
        width: 32px;
        height: 32px;
      }
      
      .btn {
        padding: 16px 30px;
        font-size: 16px;
      }
    }

    /* -------- BÚSQUEDA DE INSIGNIAS ULTRA-PROFESIONAL -------- */
    .search-section {
      background: linear-gradient(135deg, 
        rgba(255, 255, 255, 0.08) 0%, 
        rgba(255, 255, 255, 0.03) 50%, 
        rgba(255, 255, 255, 0.08) 100%);
      backdrop-filter: blur(50px) saturate(200%);
      padding: 40px;
      border-radius: 20px;
      box-shadow: 
        0 20px 40px rgba(0,0,0,0.15),
        inset 0 1px 0 rgba(255,255,255,0.2);
      margin-bottom: 30px;
      display: none; /* Oculto por defecto */
      border: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .search-form {
      display: flex;
      gap: 20px;
      align-items: center;
      flex-wrap: wrap;
    }
    
    .search-input {
      flex: 1;
      min-width: 280px;
      padding: 18px 20px;
      border: 2px solid rgba(255, 255, 255, 0.1);
      border-radius: 16px;
      font-size: 18px;
      transition: var(--transition);
      background: linear-gradient(135deg, 
        rgba(255, 255, 255, 0.08) 0%, 
        rgba(255, 255, 255, 0.03) 100%);
      backdrop-filter: blur(20px);
      color: rgba(255, 255, 255, 0.9);
      box-shadow: 
        0 8px 32px rgba(0,0,0,0.1),
        inset 0 1px 0 rgba(255,255,255,0.1);
    }
    
    .search-input::placeholder {
      color: rgba(255, 255, 255, 0.5);
    }
    
    .search-input:focus {
      outline: none;
      border-color: rgba(59, 130, 246, 0.5);
      background: linear-gradient(135deg, 
        rgba(255, 255, 255, 0.12) 0%, 
        rgba(255, 255, 255, 0.06) 100%);
      box-shadow: 
        0 0 0 4px rgba(59, 130, 246, 0.2),
        0 12px 40px rgba(0,0,0,0.15),
        inset 0 1px 0 rgba(255,255,255,0.2);
    }
    
    .search-btn {
      background: linear-gradient(135deg, 
        #003366 0%, 
        #0066CC 25%, 
        #4A90E2 50%, 
        #0066CC 75%, 
        #003366 100%);
      color: white;
      border: none;
      padding: 18px 30px;
      border-radius: 16px;
      font-size: 18px;
      font-weight: 700;
      cursor: pointer;
      transition: var(--transition);
      text-decoration: none;
      display: inline-block;
      box-shadow: 
        0 15px 30px rgba(0, 51, 102, 0.4),
        inset 0 1px 0 rgba(255,255,255,0.2);
      border: 1px solid rgba(255,255,255,0.2);
    }
    
    .search-btn:hover {
      transform: translateY(-2px) scale(1.02);
      box-shadow: 
        0 20px 40px rgba(0, 51, 102, 0.5),
        inset 0 1px 0 rgba(255,255,255,0.3);
    }
    
    .clear-btn {
      background: linear-gradient(135deg, #dc3545, #c82333);
      color: white;
      text-decoration: none;
      padding: 18px 25px;
      border-radius: 16px;
      font-size: 18px;
      font-weight: 700;
      transition: var(--transition);
      box-shadow: 
        0 15px 30px rgba(220, 53, 69, 0.4),
        inset 0 1px 0 rgba(255,255,255,0.2);
      border: 1px solid rgba(255,255,255,0.2);
    }
    
    .clear-btn:hover {
      transform: translateY(-2px) scale(1.02);
      box-shadow: 
        0 20px 40px rgba(220, 53, 69, 0.5),
        inset 0 1px 0 rgba(255,255,255,0.3);
      color: white;
    }

    /* -------- ENLACE REGISTRO ULTRA-PROFESIONAL -------- */
    .register-link {
      text-align: center;
      margin-top: 40px;
      padding-top: 30px;
      border-top: 2px solid rgba(0, 102, 204, 0.3);
      background: linear-gradient(135deg, 
        rgba(0, 51, 102, 0.08) 0%, 
        rgba(0, 102, 204, 0.05) 50%, 
        rgba(74, 144, 226, 0.08) 100%);
      border-radius: 0 0 30px 30px;
      margin-left: -50px;
      margin-right: -50px;
      margin-bottom: -60px;
      padding-bottom: 30px;
      box-shadow: inset 0 1px 10px rgba(0, 51, 102, 0.1);
    }

    .register-link a {
      color: white;
      text-decoration: none;
      font-weight: 800;
      transition: var(--transition);
      display: inline-flex;
      align-items: center;
      gap: 12px;
      padding: 16px 32px;
      border: 2px solid rgba(255, 255, 255, 0.2);
      border-radius: 25px;
      background: linear-gradient(135deg, 
        #003366 0%, 
        #0066CC 25%, 
        #4A90E2 50%, 
        #0066CC 75%, 
        #003366 100%);
      font-size: 16px;
      text-transform: uppercase;
      letter-spacing: 2px;
      box-shadow: 
        0 15px 30px rgba(0, 51, 102, 0.4),
        inset 0 1px 0 rgba(255,255,255,0.2);
      position: relative;
      overflow: hidden;
    }

    .register-link a::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, 
        transparent, 
        rgba(255,255,255,0.3), 
        transparent);
      transition: left 0.6s;
    }

    .register-link a:hover {
      color: white;
      transform: translateY(-3px) scale(1.05);
      box-shadow: 
        0 25px 50px rgba(0, 51, 102, 0.5),
        inset 0 1px 0 rgba(255,255,255,0.3);
      border-color: rgba(255, 255, 255, 0.3);
    }

    .register-link a:hover::before {
      left: 100%;
    }

    .register-link a i {
      font-size: 18px;
    }

    /* -------- LOADING STATE -------- */
    .loading {
      position: relative;
      color: transparent;
    }

    .loading::after {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      width: 20px;
      height: 20px;
      margin: -10px 0 0 -10px;
      border: 2px solid transparent;
      border-top: 2px solid white;
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

  </style>
</head>
<body>

  <!-- HEADER AZUL -->
  <header>
    <div class="header-content">
      <img src="imagen/logo.png" alt="TecNM Logo" class="header-logo">
      <h1>Insignias TecNM</h1>
    </div>
  </header>

  <div class="login-container">
    <div class="login-box">
      <!-- Gradiente superior decorativo -->
      <div class="login-box-top-gradient"></div>
      
      <!-- Flecha de navegación hacia atrás -->
      <div class="back-arrow">
        <a href="index.php" title="Volver al inicio">
          <i class="fas fa-arrow-left"></i>
        </a>
      </div>
      
      <div class="logo-container">
        <img src="imagen/logo.png" alt="" class="logo">
      </div>
      
      <h2>Insignias TecNM</h2>
      <p class="subtitle">"¡Bienvenido! El aprendizaje de hoy es el cimiento del cambio que liderarás mañana."<br><span style="font-style: italic; font-size: 0.9em; opacity: 0.8;">— Víctor Castro</span></p>

      <?php 
      // Manejo de mensaje de logout exitoso
      if (isset($_GET['logout']) && $_GET['logout'] === 'success'): 
      ?>
        <div style="background: linear-gradient(135deg, #28a745, #20c997); color: white; padding: 12px 15px; border-radius: 12px; margin-bottom: 20px; font-size: 14px; font-weight: 500; display: flex; align-items: center; justify-content: center; animation: slideDown 0.5s ease-out;">
          <i class="fas fa-check-circle" style="margin-right: 8px; font-size: 16px;"></i>
          Sesión cerrada exitosamente
        </div>
      <?php endif; ?>

      <?php 
      // Manejo de diferentes tipos de errores
      if (isset($_GET['error'])): 
        $error_messages = [
          'credenciales_incorrectas' => 'Usuario o contraseña incorrectos',
          'campos_vacios' => 'Por favor, completa todos los campos',
          'email_invalido' => 'El formato del correo electrónico no es válido',
          'datos_faltantes' => 'Faltan datos en el formulario',
          'demasiados_intentos' => 'Demasiados intentos fallidos. Intenta nuevamente en 15 minutos',
          'error_sistema' => 'Error del sistema. Contacta al administrador',
          'rol_no_valido' => 'Tu rol no tiene permisos para acceder al sistema',
          'usuario_inactivo' => 'Tu cuenta está inactiva. Contacta al administrador',
          'bd_no_configurada' => 'Base de datos no configurada correctamente. Contacta al administrador',
          'acceso_denegado' => 'No tienes permisos para acceder a esta sección',
          'sesion_invalida' => 'Tu sesión ha expirado. Por favor, inicia sesión nuevamente',
          'metodo_no_valido' => 'Método de acceso no válido',
          'usuario_bloqueado' => 'Usuario bloqueado temporalmente'
        ];
        
        $error_message = $error_messages[$_GET['error']] ?? 'Error desconocido';
        $error_icon = ($_GET['error'] === 'demasiados_intentos') ? 'fas fa-clock' : 'fas fa-exclamation-triangle';
      ?>
        <div class="error">
          <i class="<?php echo $error_icon; ?>"></i>
          <?php echo htmlspecialchars($error_message); ?>
        </div>
      <?php endif; ?>

      <form method="post" action="procesar_login.php" id="loginForm">
        <div class="input-group">
          <label for="usuario">
            <i class="fas fa-envelope"></i>
            Correo Electrónico
          </label>
          <div class="input-wrapper">
            <i class="fas fa-envelope"></i>
            <input type="email" name="usuario" id="usuario" placeholder="tu@correo.com" required autocomplete="email">
          </div>
        </div>

        <div class="input-group">
          <label for="contrasena">
            <i class="fas fa-lock"></i>
            Contraseña
          </label>
          <div class="input-wrapper">
            <i class="fas fa-lock"></i>
            <input type="password" name="contrasena" id="contrasena" placeholder="••••••••" required autocomplete="current-password">
            <button type="button" class="password-toggle" id="passwordToggle" title="Mostrar/Ocultar contraseña">
              <i class="fas fa-eye"></i>
            </button>
          </div>
        </div>


        <button class="btn" type="submit" id="submitBtn">
          <i class="fas fa-sign-in-alt"></i>
          Iniciar Sesión
        </button>
      </form>

      <!-- Sección de búsqueda de insignias -->
      <div class="search-section" id="searchSection">
        <h3 style="margin-bottom: 20px; color: var(--primary-color); text-align: center;">
          <i class="fas fa-search"></i> Buscar Insignias
        </h3>
        <div class="search-form">
          <input type="text" class="search-input" 
                 placeholder="Buscar por nombre de insignia o receptor..." 
                 id="searchInput">
          <button type="button" class="search-btn" onclick="buscar()">
            <i class="fas fa-search"></i> Buscar
          </button>
          <button type="button" class="clear-btn" id="closeSearch">
            <i class="fas fa-times"></i> Cerrar
          </button>
        </div>
      </div>

      <div class="register-link">
        <a href="consulta_publica.php" class="btn-consultar">
          <i class="fas fa-search"></i>
          Consultar Insignia
        </a>
      </div>
    </div>
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

  <script>
    // Función simple para buscar
    function buscar() {
      const input = document.getElementById('searchInput');
      const termino = input.value.trim();
      
      console.log('Función buscar ejecutada');
      console.log('Término:', termino);
      
      if (termino === '') {
        alert('Escribe algo para buscar');
        input.focus();
        return;
      }
      
      const url = 'buscar_insignias.php?buscar=' + encodeURIComponent(termino);
      console.log('Redirigiendo a:', url);
      
      // Redirección directa
      window.location.href = url;
    }

    document.addEventListener('DOMContentLoaded', function() {
      const form = document.getElementById('loginForm');
      const submitBtn = document.getElementById('submitBtn');
      const usuarioInput = document.getElementById('usuario');
      const contrasenaInput = document.getElementById('contrasena');
      
      // Validación en tiempo real
      function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
      }
      
      function validatePassword(password) {
        return password.length >= 1; // Validación simple
      }
      
      
      function updateInputValidation(input, isValid) {
        const wrapper = input.closest('.input-wrapper');
        const icon = wrapper.querySelector('i');
        
        if (isValid) {
          input.style.borderColor = '#28a745';
          icon.style.color = '#28a745';
        } else {
          input.style.borderColor = '#dc3545';
          icon.style.color = '#dc3545';
        }
      }
      
      // Validación del email
      usuarioInput.addEventListener('input', function() {
        const isValid = validateEmail(this.value);
        updateInputValidation(this, isValid);
      });
      
      // Validación de la contraseña
      contrasenaInput.addEventListener('input', function() {
        const password = this.value;
        const isValid = password.length >= 1; // Validación simple
        updateInputValidation(this, isValid);
      });
      
      
      // Manejo del envío del formulario
      form.addEventListener('submit', function(e) {
        const emailValid = validateEmail(usuarioInput.value);
        const passwordValid = validatePassword(contrasenaInput.value);
        
        if (!emailValid || !passwordValid) {
          e.preventDefault();
          
          // Mostrar mensaje de error si no hay uno ya
          if (!document.querySelector('.error')) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error';
            
            let errorMessage = 'Por favor, completa todos los campos correctamente';
            
            errorDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> ' + errorMessage;
            form.insertBefore(errorDiv, form.firstChild);
            
            // Remover el mensaje después de 8 segundos
            setTimeout(() => {
              if (errorDiv.parentNode) {
                errorDiv.remove();
              }
            }, 8000);
          }
          return;
        }
        
        // Estado de carga
        submitBtn.disabled = true;
        submitBtn.classList.add('loading');
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Iniciando sesión...';
        
        // Si hay un error después del envío, restaurar el botón
        setTimeout(() => {
          if (document.querySelector('.error')) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('loading');
            submitBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Iniciar Sesión';
          }
        }, 3000);
      });
      
      // Efecto de focus mejorado
      const inputs = document.querySelectorAll('input, select');
      inputs.forEach(input => {
        input.addEventListener('focus', function() {
          this.closest('.input-group').classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
          this.closest('.input-group').classList.remove('focused');
        });
      });
      
      // Animación de entrada para elementos
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
          }
        });
      });
      
      const animatedElements = document.querySelectorAll('.input-group, .btn');
      animatedElements.forEach((el, index) => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;
        observer.observe(el);
      });
      
      // -------- FUNCIONALIDAD MOSTRAR/OCULTAR CONTRASEÑA --------
      const passwordToggle = document.getElementById('passwordToggle');
      const passwordInput = document.getElementById('contrasena');
      const eyeIcon = passwordToggle.querySelector('i');
      
      passwordToggle.addEventListener('click', function() {
        if (passwordInput.type === 'password') {
          passwordInput.type = 'text';
          eyeIcon.classList.remove('fa-eye');
          eyeIcon.classList.add('fa-eye-slash');
          passwordToggle.classList.add('active');
          passwordToggle.title = 'Ocultar contraseña';
        } else {
          passwordInput.type = 'password';
          eyeIcon.classList.remove('fa-eye-slash');
          eyeIcon.classList.add('fa-eye');
          passwordToggle.classList.remove('active');
          passwordToggle.title = 'Mostrar contraseña';
        }
      });
      
      // Mantener el foco en el input después de hacer clic en el botón
      passwordToggle.addEventListener('mousedown', function(e) {
        e.preventDefault();
      });
      
      passwordToggle.addEventListener('click', function(e) {
        e.preventDefault();
        passwordInput.focus();
      });
      
      // -------- FUNCIONALIDAD BÚSQUEDA DE INSIGNIAS --------
      const showSearchBtn = document.getElementById('showSearchBtn');
      const searchSection = document.getElementById('searchSection');
      const closeSearchBtn = document.getElementById('closeSearch');
      
      // Mostrar sección de búsqueda
      showSearchBtn.addEventListener('click', function(e) {
        e.preventDefault();
        searchSection.style.display = 'block';
        document.getElementById('searchInput').focus();
      });
      
      // Ocultar sección de búsqueda
      closeSearchBtn.addEventListener('click', function() {
        searchSection.style.display = 'none';
        document.getElementById('searchInput').value = '';
      });
      
      // Enter en el campo de búsqueda
      document.getElementById('searchInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
          buscar();
        }
      });
    });
  </script>

</body>
</html>
