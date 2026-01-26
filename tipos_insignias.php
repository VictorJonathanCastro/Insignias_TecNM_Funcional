<?php
session_start();
require_once 'conexion.php';

// Definir las insignias con sus descripciones
$insignias = [
    [
        'nombre' => 'Responsabilidad Social',
        'imagen' => 'imagen/Insignias/ResponsabilidadSocial.png',
        'descripcion' => 'Reconoce el compromiso y participación activa de los estudiantes en actividades de responsabilidad social, servicio comunitario y proyectos que benefician a la sociedad. Esta insignia valora el impacto positivo y el desarrollo de valores cívicos y éticos.'
    ],
    [
        'nombre' => 'Embajador del Deporte',
        'imagen' => 'imagen/Insignias/EmbajadordelDeporte.png',
        'descripcion' => 'Destaca la excelencia deportiva y el compromiso con la actividad física. Se otorga a estudiantes que representan al TecNM en competencias deportivas, promueven el deporte y demuestran valores como disciplina, trabajo en equipo y perseverancia.'
    ],
    [
        'nombre' => 'Embajador del Arte',
        'imagen' => 'imagen/Insignias/EmbajadordelArte.png',
        'descripcion' => 'Reconoce el talento artístico y la contribución cultural de los estudiantes. Esta insignia celebra la creatividad, la expresión artística y la participación en actividades culturales que enriquecen la vida estudiantil y la identidad institucional.'
    ],
    [
        'nombre' => 'Movilidad e Intercambio',
        'imagen' => 'imagen/Insignias/MovilidadeIntercambio.png',
        'descripcion' => 'Valora la experiencia internacional y el intercambio académico. Se otorga a estudiantes que participan en programas de movilidad estudiantil, intercambios culturales y experiencias académicas en otras instituciones, fomentando la diversidad y el aprendizaje global.'
    ],
    [
        'nombre' => 'Formación y Actualización',
        'imagen' => 'imagen/Insignias/FormacionyActualizacion.png',
        'descripcion' => 'Reconoce el compromiso continuo con el aprendizaje y el desarrollo profesional. Esta insignia se otorga a estudiantes que participan activamente en cursos de actualización, talleres, certificaciones y programas de formación complementaria que enriquecen su perfil académico.'
    ],
    [
        'nombre' => 'Talento Científico',
        'imagen' => 'imagen/Insignias/TalentoCientifico.png',
        'descripcion' => 'Celebra la excelencia en investigación científica y desarrollo tecnológico. Se otorga a estudiantes que destacan en proyectos de investigación, publicaciones científicas, participación en congresos y contribuciones significativas al avance del conocimiento en su área de estudio.'
    ],
    [
        'nombre' => 'Talento Innovador',
        'imagen' => 'imagen/Insignias/TalentoInnovador.png',
        'descripcion' => 'Reconoce la creatividad, innovación y emprendimiento. Esta insignia valora a estudiantes que desarrollan soluciones innovadoras, participan en concursos de emprendimiento, crean proyectos tecnológicos disruptivos y demuestran capacidad para transformar ideas en realidades.'
    ]
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tipos de Insignias - TecNM</title>
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
            --texto-oscuro: #1a1a1a;
            --texto-gris: #6B7280;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #e8f0f8 0%, #ddebf5 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .header {
            background: linear-gradient(135deg, 
                rgba(30, 60, 114, 0.95) 0%, 
                rgba(42, 82, 152, 0.98) 50%,
                rgba(30, 60, 114, 0.95) 100%);
            color: white;
            padding: 25px 0;
            margin: -20px -20px 30px -20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }
        
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .header h1 {
            font-size: 1.8rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .btn-volver {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-volver:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .stats-bar {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            text-align: center;
        }
        
        .stats-bar h2 {
            color: var(--azul-oscuro);
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .stats-bar p {
            color: var(--texto-gris);
            font-size: 1.1rem;
        }
        
        .insignias-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
        }
        
        .insignia-card {
            background: linear-gradient(135deg, rgba(255,255,255,0.98) 0%, rgba(227,242,253,0.6) 50%, rgba(255,255,255,0.98) 100%);
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 51, 102, 0.08),
                        0 5px 20px rgba(0, 102, 204, 0.05);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: 3px solid transparent;
            backdrop-filter: blur(20px);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        
        .insignia-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.6s ease;
        }
        
        .insignia-card:hover::before {
            left: 100%;
        }
        
        .insignia-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 25px 60px rgba(0, 51, 102, 0.15),
                        0 15px 35px rgba(0, 102, 204, 0.12);
            border-color: var(--azul-claro);
        }
        
        .insignia-image-container {
            width: 200px;
            height: 200px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, rgba(255,255,255,0.9) 0%, rgba(227,242,253,0.5) 100%);
            border-radius: 20px;
            padding: 20px;
            transition: all 0.3s ease;
        }
        
        .insignia-card:hover .insignia-image-container {
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 15px 40px rgba(0,102,204,0.2);
        }
        
        .insignia-image {
            width: 100%;
            height: 100%;
            object-fit: contain;
            filter: drop-shadow(0 8px 16px rgba(0,102,204,0.15));
            transition: transform 0.3s ease;
        }
        
        .insignia-nombre {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--azul-oscuro);
            margin-bottom: 20px;
            text-shadow: 0 2px 6px rgba(0, 102, 204, 0.1);
            letter-spacing: -0.01em;
        }
        
        .insignia-descripcion {
            color: var(--texto-gris);
            line-height: 1.8;
            font-size: 1rem;
            text-align: justify;
        }
        
        @media (max-width: 768px) {
            .insignias-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .header-content {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .insignia-image-container {
                width: 150px;
                height: 150px;
            }
            
            .insignia-nombre {
                font-size: 1.3rem;
            }
            
            .insignia-descripcion {
                font-size: 0.95rem;
            }
        }
        
        @media (max-width: 480px) {
            .insignia-image-container {
                width: 120px;
                height: 120px;
            }
            
            .insignia-card {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>
                <i class="fas fa-award"></i>
                Tipos de Insignias TecNM
            </h1>
            <a href="index.php" class="btn-volver">
                <i class="fas fa-arrow-left"></i>
                Volver al Inicio
            </a>
        </div>
    </div>
    
    <div class="container">
        <div class="stats-bar">
            <h2><?php echo count($insignias); ?> Tipos de Insignias</h2>
            <p>Reconocimientos digitales verificables del Tecnológico Nacional de México</p>
        </div>
        
        <div class="insignias-grid">
            <?php foreach ($insignias as $insignia): ?>
                <div class="insignia-card">
                    <div class="insignia-image-container">
                        <img src="<?php echo htmlspecialchars($insignia['imagen']); ?>" 
                             alt="<?php echo htmlspecialchars($insignia['nombre']); ?>" 
                             class="insignia-image">
                    </div>
                    <h3 class="insignia-nombre"><?php echo htmlspecialchars($insignia['nombre']); ?></h3>
                    <p class="insignia-descripcion"><?php echo htmlspecialchars($insignia['descripcion']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
