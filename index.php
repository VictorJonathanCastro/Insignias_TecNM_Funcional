<?php
// Verificar si la sesión ya está iniciada antes de llamar session_start()
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si el usuario ya está logueado, redirigir al módulo correspondiente
if (isset($_SESSION['usuario_id']) && isset($_SESSION['rol'])) {
    if ($_SESSION['rol'] === 'Admin' || $_SESSION['rol'] === 'SuperUsuario') {
        header('Location: modulo_de_administracion.php');
    } else if ($_SESSION['rol'] === 'Estudiante') {
        header('Location: estudiante_dashboard.php');
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Insignias Digitales TecNM - Reconocimiento Verificable</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    :root {
      --azul-oscuro: #003366;
      --azul-medio: #0066CC;
      --azul-claro: #1976d2;
      --azul-sky: #E3F2FD;
      --blanco: #FFFFFF;
      --gris-claro: #F5F7FA;
      --gris-medio: #E1E8ED;
      --texto-oscuro: #1a1a1a;
      --texto-gris: #6B7280;
    }
    
    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
      font-weight: 400;
      background: 
        radial-gradient(ellipse 800px 600px at 10% 25%, rgba(0, 102, 204, 0.15) 0%, transparent 50%),
        radial-gradient(ellipse 700px 500px at 90% 70%, rgba(0, 51, 102, 0.18) 0%, transparent 50%),
        radial-gradient(ellipse 600px 400px at 50% 20%, rgba(25, 118, 210, 0.12) 0%, transparent 45%),
        radial-gradient(ellipse 500px 400px at 30% 90%, rgba(74, 144, 226, 0.10) 0%, transparent 50%),
        linear-gradient(135deg, 
          #e8f0f8 0%, 
          #ddebf5 15%, 
          #d5e8f2 30%, 
          #ddebf5 45%, 
          #e8f0f8 60%,
          #f0f5fa 75%, 
          #f8fafc 100%);
      background-attachment: fixed;
      background-size: 100% 100%;
      color: var(--texto-oscuro);
      line-height: 1.6;
      min-height: 100vh;
      overflow-x: hidden;
      position: relative;
    }
    
    /* Fondo decorativo con ondas */
    body::before {
      content: '';
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: 
        radial-gradient(ellipse 600px 400px at 20% 30%, rgba(0, 102, 204, 0.18) 0%, transparent 60%),
        radial-gradient(ellipse 500px 350px at 80% 70%, rgba(0, 51, 102, 0.15) 0%, transparent 60%),
        radial-gradient(ellipse 400px 300px at 50% 50%, rgba(25, 118, 210, 0.12) 0%, transparent 55%),
        repeating-linear-gradient(45deg, 
          transparent, 
          transparent 8px, 
          rgba(0, 102, 204, 0.03) 8px, 
          rgba(0, 102, 204, 0.03) 16px);
      z-index: -1;
      animation: subtleFloat 25s ease-in-out infinite;
    }
    
    @keyframes subtleFloat {
      0%, 100% { transform: translateY(0px) translateX(0px) rotate(0deg); }
      33% { transform: translateY(-10px) translateX(5px) rotate(0.5deg); }
      66% { transform: translateY(5px) translateX(-5px) rotate(-0.5deg); }
    }
    
    /* Patrón de cuadrícula sutil */
    body::after {
      content: '';
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-image: 
        repeating-linear-gradient(90deg,
          transparent,
          transparent 58px,
          rgba(0, 102, 204, 0.025) 58px,
          rgba(0, 102, 204, 0.025) 60px),
        repeating-linear-gradient(0deg,
          transparent,
          transparent 58px,
          rgba(0, 102, 204, 0.025) 58px,
          rgba(0, 102, 204, 0.025) 60px);
      background-size: 60px 60px;
      z-index: -1;
      pointer-events: none;
      animation: gridMove 20s linear infinite;
    }
    
    @keyframes gridMove {
      0% { transform: translateY(0px); }
      100% { transform: translateY(60px); }
    }
    
    /* HEADER PROFESIONAL CON GRADIENTE */
    .header {
      background: 
        linear-gradient(135deg, 
          rgba(30, 60, 114, 0.95) 0%, 
          rgba(42, 82, 152, 0.98) 30%,
          rgba(30, 60, 114, 0.95) 60%,
          rgba(26, 52, 100, 0.95) 100%);
      backdrop-filter: blur(60px) saturate(200%);
      -webkit-backdrop-filter: blur(60px) saturate(200%);
      color: white;
      text-align: center;
      position: sticky;
      top: 0;
      z-index: 1000;
      padding: 35px 0;
      box-shadow: 
        0 10px 50px rgba(0,0,0,0.4),
        0 5px 25px rgba(0,0,0,0.2),
        inset 0 2px 0 rgba(255,255,255,0.25),
        inset 0 -1px 0 rgba(255,255,255,0.05);
      border-bottom: 2px solid rgba(255,255,255,0.15);
      border-top: 2px solid rgba(255,255,255,0.1);
    }
    
    .header::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: 
        radial-gradient(circle at 50% 0%, rgba(255,255,255,0.1) 0%, transparent 70%);
      pointer-events: none;
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
    
    .logo-section {
      display: flex;
      align-items: center;
      gap: 0;
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
    
    .logo-section h1 {
      margin: 0;
      font-size: 32px;
      font-weight: 900;
      text-shadow: 
        0 6px 12px rgba(0,0,0,0.5),
        0 0 30px rgba(59, 130, 246, 0.4),
        0 0 60px rgba(59, 130, 246, 0.2);
      background: linear-gradient(135deg, #ffffff 0%, #e8f2fa 25%, #ffffff 50%, #e8f2fa 75%, #ffffff 100%);
      background-size: 200% 200%;
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      animation: titleShimmer 4s ease infinite;
      letter-spacing: -0.5px;
    }
    
    @keyframes titleShimmer {
      0%, 100% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
    }
    
    .nav-actions {
      position: absolute;
      right: 20px;
      top: 50%;
      transform: translateY(-50%);
      display: flex;
      align-items: center;
      gap: 1rem;
    }
    
    .btn-login {
      padding: 0.75rem 1.5rem;
      background: linear-gradient(135deg, #ffffff 0%, #e2e8f0 100%);
      color: var(--azul-oscuro);
      text-decoration: none;
      border-radius: 8px;
      font-weight: 700;
      transition: all 0.3s ease;
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
    
    .btn-login:hover {
      background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
      transform: translateY(-2px) scale(1.05);
      box-shadow: 0 6px 20px rgba(0,0,0,0.3);
    }
    
    /* HERO SECTION CON DISEÑO ELABORADO */
    .hero {
      background: 
        linear-gradient(135deg, 
          #003366 0%, 
          #004c8c 15%, 
          #0066CC 30%,
          #1976d2 50%,
          #4A90E2 70%,
          #0066CC 85%,
          #004c8c 100%);
      padding: 10rem 2rem;
      text-align: center;
      position: relative;
      overflow: hidden;
      box-shadow: 
        0 30px 80px rgba(0, 51, 102, 0.4),
        0 15px 40px rgba(0, 102, 204, 0.3),
        inset 0 2px 0 rgba(255,255,255,0.1);
    }
    
    .hero::before {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: 
        radial-gradient(circle at 30% 40%, rgba(255,255,255,0.15) 0%, transparent 70%);
      animation: heroPulse 20s ease-in-out infinite;
      pointer-events: none;
    }
    
    @keyframes heroPulse {
      0%, 100% { transform: scale(1) rotate(0deg); opacity: 0.5; }
      50% { transform: scale(1.1) rotate(180deg); opacity: 0.8; }
    }
    
    /* Ondas decorativas */
    .hero::after {
      content: '';
      position: absolute;
      bottom: -50%;
      right: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle at 70% 60%, rgba(255,255,255,0.08) 0%, transparent 70%);
      border-radius: 50%;
      animation: float 25s ease-in-out infinite reverse;
      pointer-events: none;
    }
    
    @keyframes float {
      0%, 100% { transform: translateY(0) rotate(0deg); }
      50% { transform: translateY(-20px) rotate(180deg); }
    }
    
    .hero-content h1 {
      color: var(--blanco) !important;
    }
    
    .hero-content .subtitle {
      color: rgba(255,255,255,0.95) !important;
    }
    
    .hero-content {
      max-width: 900px;
      margin: 0 auto;
      position: relative;
      z-index: 1;
    }
    
    .hero h1 {
      font-size: 3.8rem;
      font-weight: 900;
      color: var(--azul-oscuro);
      margin-bottom: 1.5rem;
      line-height: 1.2;
      text-shadow: 
        0 4px 12px rgba(0,0,0,0.3),
        0 2px 6px rgba(0,102,204,0.2);
      letter-spacing: -1px;
      animation: fadeInUp 1s ease;
    }
    
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .hero .subtitle {
      font-size: 1.5rem;
      color: rgba(255,255,255,0.95);
      margin-bottom: 3rem;
      font-weight: 500;
      letter-spacing: 0.02em;
      line-height: 1.7;
      text-shadow: 0 2px 8px rgba(0,0,0,0.3);
      animation: fadeInUp 1.2s ease 0.3s both;
    }
    
    .hero-buttons {
      display: flex;
      gap: 1rem;
      justify-content: center;
      flex-wrap: wrap;
    }
    
    .btn-primary {
      padding: 1rem 2.5rem;
      background: var(--azul-oscuro);
      color: var(--blanco);
      text-decoration: none;
      border-radius: 12px;
      font-weight: 600;
      font-size: 1.1rem;
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .btn-primary:hover {
      background: var(--azul-medio);
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(0,51,102,0.3);
    }
    
    .btn-secondary {
      padding: 1rem 2.5rem;
      background: var(--blanco);
      color: var(--azul-oscuro);
      text-decoration: none;
      border-radius: 12px;
      font-weight: 600;
      font-size: 1.1rem;
      border: 2px solid var(--azul-oscuro);
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .btn-secondary:hover {
      background: var(--azul-sky);
      transform: translateY(-2px);
    }
    
    /* STATS SECTION CON FONDO DECORATIVO */
    .stats {
      background: linear-gradient(135deg, var(--blanco) 0%, #f8fafc 100%);
      padding: 6rem 2rem;
      position: relative;
      overflow: hidden;
    }
    
    .stats::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 1px;
      background: linear-gradient(90deg, transparent, rgba(0,102,204,0.3), transparent);
    }
    
    .stats-grid {
      max-width: 1200px;
      margin: 0 auto;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 2rem;
    }
    
    .stat-card {
      background: 
        linear-gradient(135deg, rgba(255,255,255,0.98) 0%, rgba(227,242,253,0.5) 50%, rgba(255,255,255,0.98) 100%);
      padding: 3rem 2.5rem;
      border-radius: 25px;
      text-align: center;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      border: 3px solid transparent;
      backdrop-filter: blur(20px);
      position: relative;
      overflow: hidden;
      box-shadow: 
        0 10px 40px rgba(0, 51, 102, 0.08),
        0 5px 20px rgba(0, 102, 204, 0.05),
        inset 0 1px 0 rgba(255,255,255,0.9);
    }
    
    .stat-card::before {
      content: '';
      position: absolute;
      top: -50%;
      right: -50%;
      width: 100%;
      height: 100%;
      background: radial-gradient(circle, rgba(0,102,204,0.1) 0%, transparent 70%);
      transition: all 0.5s ease;
    }
    
    .stat-card:hover::before {
      top: 0;
      right: 0;
    }
    
    .stat-card:hover {
      border-color: var(--azul-claro);
      box-shadow: 0 12px 40px rgba(0,102,204,0.2);
    }
    
    .stat-card:hover {
      transform: translateY(-12px) scale(1.02);
      box-shadow: 
        0 25px 60px rgba(0, 51, 102, 0.15),
        0 15px 35px rgba(0, 102, 204, 0.12),
        inset 0 1px 0 rgba(255,255,255,0.95);
      border-color: var(--azul-claro);
      background: 
        linear-gradient(135deg, rgba(255,255,255,1) 0%, rgba(227,242,253,0.6) 50%, rgba(255,255,255,1) 100%);
    }
    
    .stat-number {
      font-size: 3.5rem;
      font-weight: 800;
      background: 
        linear-gradient(135deg, 
          var(--azul-oscuro) 0%, 
          var(--azul-medio) 25%, 
          var(--azul-claro) 50%, 
          var(--azul-medio) 75%, 
          var(--azul-oscuro) 100%);
      background-size: 200% 200%;
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 0.5rem;
      animation: gradientShift 3s ease infinite;
      filter: drop-shadow(0 2px 4px rgba(0,102,204,0.3));
    }
    
    @keyframes gradientShift {
      0%, 100% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
    }
    
    .stat-label {
      font-size: 1rem;
      color: var(--texto-gris);
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    
    /* FEATURES SECTION CON PATRÓN GEOMÉTRICO */
    .features {
      background: linear-gradient(135deg, #e3f2fd 0%, #f8fafc 100%);
      padding: 6rem 2rem;
      position: relative;
      overflow: hidden;
    }
    
    .features::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: 
        repeating-linear-gradient(
          45deg,
          transparent,
          transparent 10px,
          rgba(0,102,204,0.02) 10px,
          rgba(0,102,204,0.02) 20px
        );
    }
    
    .features-content {
      max-width: 1200px;
      margin: 0 auto;
    }
    
    .section-header {
      text-align: center;
      margin-bottom: 4rem;
    }
    
    .section-title {
      font-size: 2.8rem;
      font-weight: 900;
      color: var(--azul-oscuro);
      margin-bottom: 1rem;
      text-shadow: 
        0 4px 12px rgba(0, 102, 204, 0.15),
        0 2px 6px rgba(0, 51, 102, 0.1);
      letter-spacing: -0.02em;
      position: relative;
      display: inline-block;
    }
    
    .section-title::after {
      content: '';
      position: absolute;
      bottom: -10px;
      left: 50%;
      transform: translateX(-50%);
      width: 100px;
      height: 4px;
      background: linear-gradient(90deg, transparent, var(--azul-claro), transparent);
      border-radius: 2px;
      opacity: 0.5;
    }
    
    .section-subtitle {
      font-size: 1.25rem;
      color: var(--texto-gris);
      max-width: 600px;
      margin: 0 auto;
    }
    
    .features-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 2rem;
    }
    
    .feature-card {
      background: 
        linear-gradient(135deg, rgba(255,255,255,0.98) 0%, rgba(227,242,253,0.6) 50%, rgba(255,255,255,0.98) 100%);
      padding: 3rem 2.5rem;
      border-radius: 28px;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      border: 3px solid transparent;
      backdrop-filter: blur(20px);
      position: relative;
      overflow: hidden;
      box-shadow: 
        0 12px 45px rgba(0, 51, 102, 0.08),
        0 6px 25px rgba(0, 102, 204, 0.05),
        inset 0 1px 0 rgba(255,255,255,0.9);
    }
    
    .feature-card::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 4px;
      background: linear-gradient(90deg, var(--azul-oscuro), var(--azul-medio), var(--azul-claro));
      transform: scaleX(0);
      transition: transform 0.3s ease;
    }
    
    .feature-card:hover::after {
      transform: scaleX(1);
    }
    
    .feature-card:hover {
      transform: translateY(-12px) scale(1.01);
      box-shadow: 
        0 30px 70px rgba(0, 51, 102, 0.12),
        0 20px 45px rgba(0, 102, 204, 0.1),
        inset 0 1px 0 rgba(255,255,255,0.95);
      border-color: var(--azul-claro);
      background: 
        linear-gradient(135deg, rgba(255,255,255,1) 0%, rgba(227,242,253,0.7) 50%, rgba(255,255,255,1) 100%);
    }
    
    .feature-icon {
      width: 75px;
      height: 75px;
      background: 
        linear-gradient(135deg, var(--azul-oscuro) 0%, var(--azul-medio) 50%, var(--azul-claro) 100%);
      border-radius: 22px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 1.5rem;
      font-size: 2rem;
      color: var(--blanco);
      box-shadow: 
        0 10px 30px rgba(0,102,204,0.35),
        0 5px 15px rgba(0,102,204,0.25),
        inset 0 1px 0 rgba(255,255,255,0.3);
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      overflow: hidden;
    }
    
    .feature-icon::before {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: linear-gradient(45deg, transparent, rgba(255,255,255,0.2), transparent);
      transform: rotate(45deg);
      transition: all 0.6s ease;
    }
    
    .feature-card:hover .feature-icon {
      transform: translateY(-6px) scale(1.12) rotate(-3deg);
      box-shadow: 
        0 15px 40px rgba(0,102,204,0.45),
        0 8px 20px rgba(0,102,204,0.35),
        inset 0 1px 0 rgba(255,255,255,0.4);
    }
    
    .feature-card:hover .feature-icon::before {
      left: 100%;
    }
    
    .feature-card h3 {
      font-size: 1.6rem;
      font-weight: 800;
      color: var(--azul-oscuro);
      margin-bottom: 1.2rem;
      text-shadow: 0 2px 6px rgba(0, 102, 204, 0.1);
      letter-spacing: -0.01em;
    }
    
    .feature-card p {
      color: var(--texto-gris);
      line-height: 1.8;
    }
    
    /* BADGES CAROUSEL CON GRADIENTE DE FONDO */
    .badges-section {
      background: linear-gradient(135deg, var(--blanco) 0%, #e3f2fd 100%);
      padding: 6rem 2rem;
      position: relative;
    }
    
    .badges-section::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 100%;
      background: 
        radial-gradient(ellipse at 50% 50%, rgba(0,102,204,0.05) 0%, transparent 70%);
      pointer-events: none;
    }
    
    .badges-content {
      max-width: 1200px;
      margin: 0 auto;
    }
    
    .carousel-container {
      position: relative;
      margin-top: 3rem;
      border-radius: 20px;
      background: linear-gradient(135deg, rgba(255,255,255,0.9) 0%, rgba(227,242,253,0.6) 100%);
      padding: 2rem 4rem;
      backdrop-filter: blur(20px);
      border: 2px solid rgba(0,102,204,0.1);
      box-shadow: 0 20px 60px rgba(0,102,204,0.15);
    }
    
    .carousel-wrapper {
      position: relative;
      margin-top: 3rem;
    }
    
    /* Wrapper interno con overflow para badges */
    .badges-section .carousel-container .carousel-track-wrapper {
      overflow: hidden;
      width: 100%;
      position: relative;
      margin: 0 -1rem;
      padding: 0 1rem;
    }
    
    .testimonials-section .carousel-container .testimonials-track-wrapper {
      overflow: hidden;
      width: 100%;
      position: relative;
      margin: 0 -1rem;
      padding: 0 1rem;
    }
    
    .carousel-track {
      display: flex;
      flex-direction: row;
      flex-wrap: nowrap;
      gap: 2rem;
      transition: transform 0.5s ease-in-out;
      width: max-content;
    }
    
    .badge-item {
      background: 
        linear-gradient(135deg, rgba(255,255,255,0.98) 0%, rgba(227,242,253,0.6) 50%, rgba(255,255,255,0.98) 100%);
      padding: 2.5rem;
      border-radius: 22px;
      text-align: center;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      border: 3px solid transparent;
      backdrop-filter: blur(20px);
      min-width: 150px;
      flex-shrink: 0;
      box-shadow: 
        0 12px 40px rgba(0, 51, 102, 0.08),
        0 6px 20px rgba(0, 102, 204, 0.05),
        inset 0 1px 0 rgba(255,255,255,0.9);
      position: relative;
      overflow: hidden;
    }
    
    .badge-item::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
      transition: left 0.6s ease;
    }
    
    .badge-item:hover::before {
      left: 100%;
    }
    
    .badge-item:hover {
      transform: translateY(-10px) scale(1.05);
      box-shadow: 
        0 25px 60px rgba(0, 51, 102, 0.15),
        0 15px 35px rgba(0, 102, 204, 0.12),
        inset 0 1px 0 rgba(255,255,255,0.95);
      border-color: var(--azul-claro);
    }
    
    .badge-image {
      width: 140px;
      height: 140px;
      object-fit: contain;
      margin-bottom: 1.2rem;
      transition: transform 0.3s ease;
      filter: drop-shadow(0 8px 16px rgba(0,102,204,0.15));
    }
    
    .badge-item:hover .badge-image {
      transform: scale(1.1) rotate(5deg);
    }
    
    .badge-name {
      font-size: 1.1rem;
      font-weight: 700;
      color: var(--azul-oscuro);
      letter-spacing: 0.01em;
    }
    
    .carousel-btn {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      background: linear-gradient(135deg, var(--azul-oscuro) 0%, var(--azul-medio) 100%);
      color: white;
      border: none;
      width: 50px;
      height: 50px;
      border-radius: 50%;
      font-size: 1.5rem;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 8px 20px rgba(0,51,102,0.3);
      z-index: 2;
    }
    
    .carousel-btn:hover {
      background: linear-gradient(135deg, var(--azul-medio) 0%, var(--azul-claro) 100%);
      transform: translateY(-50%) scale(1.1);
      box-shadow: 0 12px 30px rgba(0,51,102,0.4);
    }
    
    .carousel-btn-prev {
      left: -25px;
    }
    
    .carousel-btn-next {
      right: -25px;
    }
    
    .carousel-dots {
      display: flex;
      justify-content: center;
      gap: 1rem;
      margin-top: 2rem;
    }
    
    .dot {
      width: 12px;
      height: 12px;
      border-radius: 50%;
      background: rgba(0,51,102,0.3);
      cursor: pointer;
      transition: all 0.3s ease;
    }
    
    .dot.active,
    .dot:hover {
      background: var(--azul-claro);
      transform: scale(1.3);
    }
    
    /* TESTIMONIALS SECTION */
    .testimonials-section {
      background: linear-gradient(135deg, #e3f2fd 0%, #f8fafc 100%);
      padding: 6rem 2rem;
      position: relative;
      overflow: hidden;
    }
    
    .testimonials-content {
      max-width: 1200px;
      margin: 0 auto;
    }
    
    .testimonials-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
      gap: 3rem;
      margin-top: 3rem;
    }
    
    .testimonial-card {
      background: linear-gradient(135deg, rgba(255,255,255,0.98) 0%, rgba(248,250,252,0.9) 100%);
      padding: 3rem;
      border-radius: 24px;
      position: relative;
      transition: all 0.3s ease;
      border: 2px solid transparent;
      backdrop-filter: blur(10px);
      box-shadow: 0 20px 40px rgba(0,51,102,0.1);
      overflow: hidden;
    }
    
    .testimonial-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, var(--azul-oscuro), var(--azul-medio), var(--azul-claro));
    }
    
    .testimonial-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 32px 64px rgba(0,51,102,0.15);
      border-color: var(--azul-claro);
    }
    
    .testimonial-icon {
      font-size: 3rem;
      color: var(--azul-claro);
      opacity: 0.2;
      margin-bottom: 1.5rem;
    }
    
    .testimonial-text {
      font-size: 1.1rem;
      line-height: 1.8;
      color: var(--texto-oscuro);
      margin-bottom: 2rem;
      font-style: italic;
    }
    
    .testimonial-author {
      display: flex;
      align-items: center;
      gap: 1.5rem;
    }
    
    .testimonial-avatar {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--azul-oscuro) 0%, var(--azul-medio) 100%);
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.2rem;
      font-weight: 700;
      box-shadow: 0 8px 20px rgba(0,102,204,0.3);
    }
    
    .testimonial-info h4 {
      font-size: 1.1rem;
      font-weight: 700;
      color: var(--azul-oscuro);
      margin-bottom: 0.25rem;
    }
    
    .testimonial-info p {
      font-size: 0.9rem;
      color: var(--azul-medio);
      font-weight: 600;
    }
    
    /* CARRUSEL DE TESTIMONIOS - Igual que badges */
    .testimonials-track {
      display: flex;
      flex-direction: row !important;
      flex-wrap: nowrap !important;
      gap: 1.25rem;
      transition: transform 0.5s ease-in-out;
      align-items: flex-start;
      will-change: transform;
      width: max-content;
    }
    
    .testimonial-item {
      min-width: 280px;
      max-width: 300px;
      flex-shrink: 0;
      flex-grow: 0;
      width: 300px;
    }
    
    .testimonial-item .testimonial-card {
      height: 100%;
      display: flex;
      flex-direction: column;
      padding: 1.5rem;
      min-height: auto;
    }
    
    .testimonial-item .testimonial-text {
      font-size: 0.9rem;
      line-height: 1.6;
      flex-grow: 0;
      overflow-wrap: break-word;
      word-wrap: break-word;
      word-break: break-word;
      hyphens: auto;
      overflow: visible;
      text-overflow: clip;
      white-space: normal;
      max-height: none;
      margin-bottom: 1.25rem;
    }
    
    .testimonial-item .testimonial-icon {
      font-size: 1.75rem;
      margin-bottom: 0.75rem;
    }
    
    .testimonial-item .testimonial-author {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      margin-top: 0;
    }
    
    .testimonial-item .testimonial-avatar {
      width: 45px;
      height: 45px;
      font-size: 0.9rem;
      flex-shrink: 0;
    }
    
    .testimonial-item .testimonial-info h4 {
      font-size: 0.95rem;
      margin-bottom: 0.15rem;
    }
    
    .testimonial-item .testimonial-info p {
      font-size: 0.8rem;
    }
    
    /* FOOTER PROFESIONAL AZUL */
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
    
    .footer-section h3 {
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
    
    .social-icons {
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
    
    /* RESPONSIVE - Tablet */
    @media (max-width: 1024px) {
      .header {
        padding: 25px 0;
      }
      
      .header-logo {
        height: 50px;
        left: -180px;
      }
      
      .header h1 {
        font-size: 28px;
      }
      
      .hero {
        padding: 8rem 2rem;
      }
    }
    
    /* RESPONSIVE - Móviles y tablets pequeñas */
    @media (max-width: 768px) {
      .header {
        padding: 20px 0;
      }
      
      .header-content {
        padding: 0 15px;
        flex-direction: row;
        justify-content: center;
        align-items: center;
        gap: 12px;
      }
      
      .header-logo {
        position: relative;
        left: auto;
        top: auto;
        transform: none;
        height: 45px;
        width: auto;
        display: block;
        margin: 0;
      }
      
      .header h1 {
        font-size: 22px;
        margin: 0;
      }
      
      .hero {
        padding: 6rem 1.5rem;
      }
      
      .hero h1 {
        font-size: 2.2rem;
        line-height: 1.2;
      }
      
      .hero .subtitle {
        font-size: 1.1rem;
        padding: 0 1rem;
      }
      
      .section-title {
        font-size: 1.8rem;
      }
      
      .hero-buttons {
        flex-direction: column;
        gap: 1rem;
        width: 100%;
        max-width: 300px;
        margin: 0 auto;
      }
      
      .btn-primary,
      .btn-secondary {
        width: 100%;
        padding: 1rem 2rem;
        font-size: 1rem;
      }
      
      .nav-actions {
        position: relative;
        right: auto;
        top: auto;
        transform: none;
        width: 100%;
        justify-content: center;
        margin-top: 15px;
      }
      
      .carousel-btn {
        width: 40px;
        height: 40px;
        font-size: 1.2rem;
      }
      
      .carousel-btn-prev {
        left: -20px;
      }
      
      .carousel-btn-next {
        right: -20px;
      }
      
      .testimonials-grid {
        grid-template-columns: 1fr;
        gap: 2rem;
      }
      
      .testimonial-card {
        padding: 2rem;
      }
      
      .testimonial-text {
        font-size: 1rem;
      }
      
      .badge-item {
        min-width: 120px;
      }
      
      .badge-image {
        width: 100px;
        height: 100px;
      }
      
      .testimonials-track {
        flex-direction: row !important;
        flex-wrap: nowrap !important;
      }
      
      .carousel-track {
        flex-direction: row !important;
        flex-wrap: nowrap !important;
      }
      
      .testimonial-item {
        min-width: 85%;
        max-width: 85%;
        width: 85%;
      }
      
      .testimonial-item .testimonial-card {
        padding: 1.5rem;
      }
      
      .testimonial-item .testimonial-text {
        font-size: 0.95rem;
        line-height: 1.6;
        overflow-wrap: break-word;
        word-wrap: break-word;
        word-break: break-word;
      }
      
      .testimonial-item .testimonial-icon {
        font-size: 2rem;
      }
      
      .testimonial-item .testimonial-avatar {
        width: 45px;
        height: 45px;
        font-size: 0.9rem;
      }
      
      .testimonial-item .testimonial-info h4 {
        font-size: 0.95rem;
      }
      
      .testimonial-item .testimonial-info p {
        font-size: 0.8rem;
      }
      
      .testimonials-section .carousel-container {
        padding: 2rem 3rem;
      }
      
      .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
        padding: 0 1rem;
      }
      
      .stat-card {
        padding: 1.5rem 1rem;
      }
      
      .stat-number {
        font-size: 2rem;
      }
      
      .stat-label {
        font-size: 0.9rem;
      }
    }
    
    /* RESPONSIVE - Móviles pequeños */
    @media (max-width: 480px) {
      .header {
        padding: 15px 0;
      }
      
      .header-content {
        padding: 0 10px;
        gap: 8px;
      }
      
      .header-logo {
        height: 35px;
      }
      
      .header h1 {
        font-size: 18px;
      }
      
      .hero {
        padding: 4rem 1rem;
      }
      
      .hero h1 {
        font-size: 1.8rem;
        line-height: 1.3;
      }
      
      .hero .subtitle {
        font-size: 1rem;
        padding: 0 0.5rem;
      }
      
      .section-title {
        font-size: 1.5rem;
      }
      
      .hero-buttons {
        max-width: 100%;
        padding: 0 1rem;
      }
      
      .btn-primary,
      .btn-secondary {
        padding: 0.9rem 1.5rem;
        font-size: 0.95rem;
      }
      
      .stats-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
        padding: 0 0.5rem;
      }
      
      .stat-card {
        padding: 1.2rem 0.8rem;
      }
      
      .stat-number {
        font-size: 1.8rem;
      }
      
      .stat-label {
        font-size: 0.85rem;
      }
      
      .testimonial-item {
        min-width: 90%;
        max-width: 90%;
        width: 90%;
      }
      
      .testimonial-item .testimonial-card {
        padding: 1.2rem;
      }
      
      .testimonial-text {
        font-size: 0.9rem;
      }
      
      .carousel-btn {
        width: 35px;
        height: 35px;
        font-size: 1rem;
      }
      
      .carousel-btn-prev {
        left: -15px;
      }
      
      .carousel-btn-next {
        right: -15px;
      }
      
      .testimonials-section .carousel-container {
        padding: 1.5rem 2rem;
      }
    }
    
    /* RESPONSIVE - Móviles muy pequeños */
    @media (max-width: 360px) {
      .header {
        padding: 12px 0;
      }
      
      .header-logo {
        height: 30px;
      }
      
      .header h1 {
        font-size: 16px;
      }
      
      .hero {
        padding: 3rem 0.8rem;
      }
      
      .hero h1 {
        font-size: 1.5rem;
      }
      
      .hero .subtitle {
        font-size: 0.9rem;
      }
      
      .section-title {
        font-size: 1.3rem;
      }
      
      .stats-grid {
        padding: 0 0.3rem;
      }
      
      .stat-card {
        padding: 1rem 0.6rem;
      }
      
      .stat-number {
        font-size: 1.6rem;
      }
      
      .stat-label {
        font-size: 0.8rem;
      }
    }
    
    /* Orientación horizontal en móviles */
    @media (max-width: 768px) and (orientation: landscape) {
      .header {
        padding: 15px 0;
      }
      
      .hero {
        padding: 4rem 1.5rem;
      }
      
      .hero h1 {
        font-size: 2rem;
      }
    }
  </style>
</head>
<body>
  <!-- HEADER -->
  <header class="header">
      <div class="header-content">
        <img src="imagen/logo.png" alt="TecNM Logo" class="header-logo">
        <h1>Insignias TecNM</h1>
      </div>
  </header>

  <!-- HERO -->
  <section class="hero">
    <div class="hero-content">
      <h1>Sistema de Insignias Digitales</h1>
      <p class="subtitle">
        Reconocimiento moderno de logros académicos, habilidades y competencias estudiantiles 
        a través de insignias digitales verificables
      </p>
      <div class="hero-buttons">
        <a href="login.php" class="btn-primary">
          <i class="fas fa-rocket"></i> Acceder al Sistema
        </a>
        <a href="#informacion" class="btn-secondary">
          <i class="fas fa-info-circle"></i> Más Información
        </a>
      </div>
    </div>
  </section>

  <!-- STATS -->
  <section class="stats">
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-number">7+</div>
        <div class="stat-label">Tipos de Insignias</div>
      </div>
      <div class="stat-card">
        <div class="stat-number">+6500</div>
        <div class="stat-label">Estudiantes Activos</div>
      </div>
      <div class="stat-card">
        <div class="stat-number">1000+</div>
        <div class="stat-label">Insignias Otorgadas</div>
      </div>
      <a href="lista_instituciones.php" style="text-decoration: none; color: inherit; display: block;">
        <div class="stat-card" style="cursor: pointer;">
          <div class="stat-number">254</div>
          <div class="stat-label">Instituciones</div>
        </div>
      </a>
    </div>
  </section>

  <!-- FEATURES -->
  <section class="features" id="informacion">
    <div class="features-content">
      <div class="section-header">
        <h2 class="section-title">¿Qué son las Insignias Digitales?</h2>
        <p class="section-subtitle">
          Representaciones visuales de logros, habilidades o competencias adquiridas. 
          Más que simples gráficos, son pruebas tangibles y verificables de conocimiento.
        </p>
      </div>
      
      <div class="features-grid">
        <div class="feature-card">
          <div class="feature-icon">
            <i class="fas fa-shield-check"></i>
          </div>
          <h3>Validación de Habilidades</h3>
          <p>
            Proporciona una forma transparente y verificable de validar las habilidades y 
            logros de los individuos. La organización certifica el nivel específico de competencia alcanzado.
          </p>
        </div>
        
        <div class="feature-card">
          <div class="feature-icon">
            <i class="fas fa-graduation-cap"></i>
          </div>
          <h3>Aprendizaje y Desarrollo</h3>
          <p>
            Sirven como fuente constante de motivación para estudiantes. Se promueve la 
            superación personal y se fomenta el deseo de aprender y mejorar constantemente.
          </p>
        </div>
        
        <div class="feature-card">
          <div class="feature-icon">
            <i class="fas fa-trophy"></i>
          </div>
          <h3>Reconocimiento Significativo</h3>
          <p>
            Reconocer logros y contribuciones mejora el compromiso y satisfacción. Las insignias 
            brindan un medio efectivo para reconocer el esfuerzo de manera pública.
          </p>
        </div>
        
        <div class="feature-card">
          <div class="feature-icon">
            <i class="fas fa-briefcase"></i>
          </div>
          <h3>Diferenciación Profesional</h3>
          <p>
            Los estudiantes pueden demostrar de forma más sencilla las habilidades adquiridas, 
            mejorando sus posibilidades en procesos de selección y desarrollo profesional.
          </p>
        </div>
        
        <div class="feature-card">
          <div class="feature-icon">
            <i class="fas fa-wallet"></i>
          </div>
          <h3>Billetera Digital</h3>
          <p>
            Los acreditados pueden almacenar todos sus reconocimientos en una sola billetera 
            digital desde donde podrán compartirlos en redes sociales y LinkedIn.
          </p>
        </div>
        
        <div class="feature-card">
          <div class="feature-icon">
            <i class="fas fa-certificate"></i>
          </div>
          <h3>Metadatos Completos</h3>
          <p>
            Cada insignia contiene información detallada: entidad emisora, criterios de obtención, 
            fecha de emisión, descripción completa y vinculación a evidencias.
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- BADGES CAROUSEL -->
  <section class="badges-section">
    <div class="badges-content">
      <div class="section-header">
        <h2 class="section-title">Insignias Disponibles</h2>
        <p class="section-subtitle">
          Descubre los diferentes tipos de insignias que puedes obtener
        </p>
      </div>
      
      <div class="carousel-wrapper">
        <div class="carousel-container">
          <button class="carousel-btn carousel-btn-prev" onclick="moveCarousel(-1)">
            <i class="fas fa-chevron-left"></i>
          </button>
          <div class="carousel-track-wrapper">
            <div class="carousel-track" id="carouselTrack">
          <div class="badge-item">
            <img src="imagen/Insignias/ResponsabilidadSocial.png" alt="Responsabilidad Social" class="badge-image">
            <p class="badge-name">Responsabilidad Social</p>
          </div>
          <div class="badge-item">
            <img src="imagen/Insignias/EmbajadordelDeporte.png" alt="Embajador del Deporte" class="badge-image">
            <p class="badge-name">Embajador del Deporte</p>
          </div>
          <div class="badge-item">
            <img src="imagen/Insignias/EmbajadordelArte.png" alt="Embajador del Arte" class="badge-image">
            <p class="badge-name">Embajador del Arte</p>
          </div>
          <div class="badge-item">
            <img src="imagen/Insignias/MovilidadeIntercambio.png" alt="Movilidad e Intercambio" class="badge-image">
            <p class="badge-name">Movilidad e Intercambio</p>
          </div>
          <div class="badge-item">
            <img src="imagen/Insignias/FormacionyActualizacion.png" alt="Formación y Actualización" class="badge-image">
            <p class="badge-name">Formación y Actualización</p>
          </div>
          <div class="badge-item">
            <img src="imagen/Insignias/TalentoCientifico.png" alt="Talento Científico" class="badge-image">
            <p class="badge-name">Talento Científico</p>
          </div>
          <div class="badge-item">
            <img src="imagen/Insignias/TalentoInnovador.png" alt="Talento Innovador" class="badge-image">
            <p class="badge-name">Talento Innovador</p>
          </div>
            </div>
          </div>
          <button class="carousel-btn carousel-btn-next" onclick="moveCarousel(1)">
            <i class="fas fa-chevron-right"></i>
          </button>
        </div>
        <div class="carousel-dots">
          <span class="dot active" onclick="goToSlide(0)"></span>
          <span class="dot" onclick="goToSlide(1)"></span>
          <span class="dot" onclick="goToSlide(2)"></span>
        </div>
      </div>
    </div>
  </section>

  <!-- TESTIMONIALS SECTION -->
  <section class="testimonials-section">
    <div class="testimonials-content">
      <div class="section-header">
        <h2 class="section-title">Testimonios</h2>
        <p class="section-subtitle">
          Lo que dicen nuestros usuarios sobre el Sistema de Insignias TecNM
        </p>
      </div>
      
      <div class="carousel-wrapper">
        <div class="carousel-container">
          <button class="carousel-btn carousel-btn-prev" onclick="moveTestimonials(-1)">
            <i class="fas fa-chevron-left"></i>
          </button>
          <div class="testimonials-track-wrapper">
            <div class="testimonials-track" id="testimonialsTrack">
          <!-- Testimonio 1 - Autoridad -->
          <div class="testimonial-item">
            <div class="testimonial-card">
              <div class="testimonial-icon">
                <i class="fas fa-quote-left"></i>
              </div>
              <p class="testimonial-text">
                "El Sistema de Insignias Digitales ha transformado la forma en que reconocemos el esfuerzo y dedicación de nuestros estudiantes. Es una herramienta innovadora que motiva a los alumnos a alcanzar nuevas metas y competencias."
              </p>
              <div class="testimonial-author">
                <div class="testimonial-avatar">
                  <span>AYZF</span>
                </div>
                <div class="testimonial-info">
                  <h4>Andrea Yadira Zarate Fuentes</h4>
                  <p>Secretaria de Extensión y Vinculación del TecNM</p>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Testimonio 2 - Autoridad -->
          <div class="testimonial-item">
            <div class="testimonial-card">
              <div class="testimonial-icon">
                <i class="fas fa-quote-left"></i>
              </div>
              <p class="testimonial-text">
                "La implementación de este sistema de insignias digitales ha fortalecido el reconocimiento de las competencias y logros de nuestros estudiantes en el campus. Es una plataforma que impulsa la excelencia académica y el desarrollo integral."
              </p>
              <div class="testimonial-author">
                <div class="testimonial-avatar">
                  <span>VHAC</span>
                </div>
                <div class="testimonial-info">
                  <h4>Victor Hugo Agatón Catalan</h4>
                  <p>Director del TecNM Campus San Marcos</p>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Testimonio 3 - Estudiante -->
          <div class="testimonial-item">
            <div class="testimonial-card">
              <div class="testimonial-icon">
                <i class="fas fa-quote-left"></i>
              </div>
              <p class="testimonial-text">
                "Recibir mi insignia digital fue un momento muy especial. Es increíble poder mostrar mis logros de forma oficial y compartirlos fácilmente. Esta plataforma me ayuda a construir mi perfil profesional desde la universidad."
              </p>
              <div class="testimonial-author">
                <div class="testimonial-avatar">
                  <span>JCGT</span>
                </div>
                <div class="testimonial-info">
                  <h4>Juan Carlos Gómez Torres</h4>
                  <p>Estudiante de Ingeniería en Sistemas</p>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Testimonio 4 - Estudiante -->
          <div class="testimonial-item">
            <div class="testimonial-card">
              <div class="testimonial-icon">
                <i class="fas fa-quote-left"></i>
              </div>
              <p class="testimonial-text">
                "El sistema de insignias me ha motivado mucho a participar en más actividades extracurriculares. Saber que cada esfuerzo será reconocido oficialmente me da ese impulso extra para buscar la excelencia académica."
              </p>
              <div class="testimonial-author">
                <div class="testimonial-avatar">
                  <span>MFSP</span>
                </div>
                <div class="testimonial-info">
                  <h4>María Fernanda Sánchez Pérez</h4>
                  <p>Estudiante de Administración</p>
              </div>
            </div>
          </div>
          
          <!-- Testimonio 5 - Estudiante -->
          <div class="testimonial-item">
            <div class="testimonial-card">
              <div class="testimonial-icon">
                <i class="fas fa-quote-left"></i>
              </div>
              <p class="testimonial-text">
                "Es una herramienta moderna que refleja realmente el esfuerzo de los estudiantes. Poder compartir mis insignias en LinkedIn ha mejorado significativamente mi perfil profesional y visibilidad laboral."
              </p>
              <div class="testimonial-author">
                <div class="testimonial-avatar">
                  <span>LEHR</span>
                </div>
                <div class="testimonial-info">
                  <h4>Luis Eduardo Hernández Ramírez</h4>
                  <p>Estudiante de Ingeniería Industrial</p>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Testimonio 6 - Estudiante -->
          <div class="testimonial-item">
            <div class="testimonial-card">
              <div class="testimonial-icon">
                <i class="fas fa-quote-left"></i>
              </div>
              <p class="testimonial-text">
                "La transparencia y verificación digital de las insignias es impresionante. Los empleadores pueden verificar fácilmente mis logros y competencias. Es el futuro del reconocimiento académico."
              </p>
              <div class="testimonial-author">
                <div class="testimonial-avatar">
                  <span>APMD</span>
                </div>
                <div class="testimonial-info">
                  <h4>Alejandra Patricia Martínez Díaz</h4>
                  <p>Estudiante de Contaduría</p>
                </div>
              </div>
            </div>
          </div>
            </div>
          </div>
          <button class="carousel-btn carousel-btn-next" onclick="moveTestimonials(1)">
            <i class="fas fa-chevron-right"></i>
          </button>
        </div>
        <div class="carousel-dots">
          <span class="dot active" onclick="goToTestimonial(0)"></span>
          <span class="dot" onclick="goToTestimonial(1)"></span>
          <span class="dot" onclick="goToTestimonial(2)"></span>
        </div>
      </div>
    </div>
  </section>

  <!-- FOOTER -->
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
        <div class="social-icons">
          <a href="https://www.facebook.com/TecNacionalMexico" target="_blank" class="social-icon">f</a>
          <a href="https://twitter.com/TecNacionalMex" target="_blank" class="social-icon">X</a>
          <a href="https://www.youtube.com/user/TecNacionalMexico" target="_blank" class="social-icon">▶</a>
          <a href="https://www.instagram.com/tecnacionalmexico/" target="_blank" class="social-icon">📷</a>
        </div>
      </div>
    </div>
  </footer>

  <script>
    // CAROUSEL FUNCTIONALITY
    let currentIndex = 0;
    const itemsPerView = 3; // Show 3 items at a time
    
    function updateCarousel() {
      const track = document.querySelector('.carousel-track');
      const items = document.querySelectorAll('.badge-item');
      const itemWidth = items[0].offsetWidth + 32; // item width + gap
      const offset = currentIndex * itemWidth;
      
      // Move carousel
      track.style.transform = `translateX(-${offset}px)`;
      
      // Update dots
      const dots = document.querySelectorAll('.dot');
      dots.forEach((dot, index) => {
        dot.classList.toggle('active', index === Math.floor(currentIndex / itemsPerView));
      });
    }
    
    function moveCarousel(direction) {
      const items = document.querySelectorAll('.badge-item');
      const maxIndex = Math.max(0, items.length - itemsPerView);
      
      currentIndex += (direction * itemsPerView);
      
      if (currentIndex < 0) {
        currentIndex = maxIndex;
      } else if (currentIndex > maxIndex) {
        currentIndex = 0;
      }
      
      updateCarousel();
    }
    
    function goToSlide(slideIndex) {
      currentIndex = slideIndex * itemsPerView;
      updateCarousel();
    }
    
    // Auto-scroll carousel every 35 seconds
    setInterval(() => {
      moveCarousel(1);
    }, 35000);
    
    // Initialize carousel on load
    document.addEventListener('DOMContentLoaded', function() {
      updateCarousel();
    });
    
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
          target.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
          });
        }
      });
    });
    
    // Carrusel de Testimonios
    let currentTestimonialIndex = 0;
    let testimonialsPerView = 3; // Número de testimonios visibles a la vez
    
    function calculateTestimonialsPerView() {
      // Siempre mostrar 3 testimonios a la vez
      return 3;
    }
    
    function moveTestimonials(direction) {
      const items = document.querySelectorAll('.testimonial-item');
      if (items.length === 0) return;
      
      testimonialsPerView = calculateTestimonialsPerView();
      const maxIndex = Math.max(0, items.length - testimonialsPerView);
      
      currentTestimonialIndex += (direction * testimonialsPerView);
      if (currentTestimonialIndex < 0) {
        currentTestimonialIndex = maxIndex;
      } else if (currentTestimonialIndex > maxIndex) {
        currentTestimonialIndex = 0;
      }
      updateTestimonialsCarousel();
    }
    
    function goToTestimonial(slideIndex) {
      const items = document.querySelectorAll('.testimonial-item');
      if (items.length === 0) return;
      
      testimonialsPerView = calculateTestimonialsPerView();
      const maxIndex = Math.max(0, items.length - testimonialsPerView);
      currentTestimonialIndex = slideIndex * testimonialsPerView;
      if (currentTestimonialIndex > maxIndex) currentTestimonialIndex = maxIndex;
      updateTestimonialsCarousel();
    }
    
    function updateTestimonialsCarousel() {
      const track = document.getElementById('testimonialsTrack');
      const items = document.querySelectorAll('.testimonial-item');
      if (!track || items.length === 0) return;
      
      testimonialsPerView = calculateTestimonialsPerView();
      const itemWidth = items[0].offsetWidth + 20; // item width + gap (1.25rem = 20px)
      const offset = currentTestimonialIndex * itemWidth;
      track.style.transform = `translateX(-${offset}px)`;
      
      // Calcular número de slides basado en testimonios visibles
      const totalSlides = Math.ceil(items.length / testimonialsPerView);
      
      // Update dots
      const dots = document.querySelectorAll('.testimonials-section .dot');
      const currentSlide = Math.floor(currentTestimonialIndex / testimonialsPerView);
      dots.forEach((dot, index) => {
        dot.classList.toggle('active', index === currentSlide && index < totalSlides);
      });
    }
    
    // Inicializar carrusel cuando la página cargue
    document.addEventListener('DOMContentLoaded', function() {
      // Esperar a que se carguen los estilos
      setTimeout(() => {
        updateTestimonialsCarousel();
        
        // Ajustar en caso de resize
        window.addEventListener('resize', updateTestimonialsCarousel);
        
        // Auto-play cada 35 segundos
        setInterval(() => {
          moveTestimonials(1);
        }, 35000);
      }, 100);
    });
  </script>
</body>
</html>

