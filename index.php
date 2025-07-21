<?php
require_once 'config/session.php';
require_once 'models/User.php';

// Crear tablas si no existen
$user = new User();
$user->createTables();

// Si ya está autenticado, redirigir según el rol
if (isAuthenticated()) {
    redirectToRole($_SESSION['user_type']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Selección de Rol - SYSPRE</title>
    <!-- Font Awesome para íconos -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <style>
        :root {
            --azul-estudiante: #4a6fdc;
            --verde-docente: #3aa87a;
            --amarillo-coordinador: #d6a62c;
            --rojo-admin: #d63a3a;
            --gris-oscuro: #1a2639;
            --gris-medio: #5c6b7e;
            --gris-claro: #f5f7fa;
            --blanco: #ffffff;
            --color-encabezado: #ffffff;
            --sombra-suave: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        
        body, html {
            margin: 0; 
            padding: 0; 
            box-sizing: border-box;
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            background: var(--gris-claro);
            color: var(--gris-oscuro);
            line-height: 1.6;
            overflow-x: hidden;
        }
        
        /* Efecto de destello sutil */
        @keyframes destello {
            0% { transform: translateX(-100%) skewX(-15deg); opacity: 0; }
            50% { opacity: 0.15; }
            100% { transform: translateX(100%) skewX(-15deg); opacity: 0; }
        }
        
        .destello-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 100;
            overflow: hidden;
        }
        
        .destello {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.3) 50%, rgba(255,255,255,0) 100%);
            animation: destello 8s infinite;
            animation-delay: 2s;
        }
        
        /* Encabezado Profesional */
        .header {
            background: var(--color-encabezado);
            color: var(--gris-oscuro);
            padding: 1.2rem 2rem;
            box-shadow: var(--sombra-suave);
            position: relative;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }
        
        .header::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--azul-estudiante), var(--verde-docente), var(--amarillo-coordinador), var(--rojo-admin));
        }
        
        .header-title {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }
        
        .header-title i {
            font-size: 1.8rem;
        }
        
        .header-nav {
            display: flex;
            gap: 1.5rem;
        }
        
        .header-nav a {
            color: var(--gris-oscuro);
            font-weight: 500;
            font-size: 1rem;
            padding: 0.5rem 0;
            position: relative;
            text-decoration: none;
            transition: opacity 0.3s ease;
        }
        
        .header-nav a:hover {
            opacity: 0.9;
        }
        
        .header-nav a::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--gris-oscuro);
            transition: width 0.3s ease;
        }
        
        .header-nav a:hover::after {
            width: 100%;
        }
        
        /* Contenido Principal */
        .main-container {
            max-width: 1200px;
            margin: 3rem auto;
            padding: 0 2rem;
            position: relative;
            z-index: 10;
        }
        
        .content-wrapper {
            display: flex;
            min-height: calc(100vh - 180px);
            align-items: center;
        }
        
        /* Panel de Roles */
        .roles-panel {
            flex: 1;
            max-width: 500px;
            padding-right: 3rem;
        }
        
        .roles-title {
            font-weight: 700;
            font-size: 1.6rem;
            margin-bottom: 2.5rem;
            color: var(--gris-oscuro);
            position: relative;
            padding-bottom: 0.8rem;
        }
        
        .roles-title::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: var(--verde-docente);
            border-radius: 3px;
        }
        
        .role-card {
            display: flex;
            background: var(--blanco);
            border-radius: 0.8rem;
            margin-bottom: 1.5rem;
            padding: 1.5rem;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
            text-decoration: none;
            color: inherit;
        }
        
        .role-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--color-rol);
            transition: width 0.3s ease;
        }
        
        .role-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }
        
        .role-card:hover::before {
            width: 6px;
        }
        
        .role-card.estudiante { --color-rol: var(--azul-estudiante); }
        .role-card.docente { --color-rol: var(--verde-docente); }
        .role-card.coordinador { --color-rol: var(--amarillo-coordinador); }
        .role-card.administrador { --color-rol: var(--rojo-admin); }
        
        .role-icon-container {
            flex-shrink: 0;
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            font-size: 1.8rem;
            background: var(--color-rol);
            margin-right: 1.5rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .role-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .role-title {
            font-weight: 700;
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
            color: var(--color-rol);
        }
        
        .role-description {
            font-weight: 400;
            font-size: 0.92rem;
            line-height: 1.5;
            color: var(--gris-medio);
        }
        
        /* Panel de Bienvenida */
        .welcome-panel {
            flex: 1;
            max-width: 600px;
            text-align: center;
            padding-left: 3rem;
        }
        
        .welcome-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0 0 1.5rem 0;
            color: var(--gris-oscuro);
            line-height: 1.2;
        }
        
        .welcome-title span {
            display: block;
            font-size: 3rem;
            background: linear-gradient(90deg, var(--verde-docente), #2a7a5a);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-top: 0.5rem;
        }
        
        .institution-text {
            font-weight: 500;
            font-size: 1.1rem;
            margin-bottom: 2rem;
            color: var(--gris-medio);
            letter-spacing: 0.5px;
        }
        
        .institution-logo {
            height: 180px;
            width: auto;
            object-fit: contain;
            filter: drop-shadow(0 4px 12px rgba(0,0,0,0.1));
            transition: all 0.3s ease;
        }
        
        .institution-logo:hover {
            transform: translateY(-5px);
            filter: drop-shadow(0 6px 16px rgba(0,0,0,0.15));
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .content-wrapper {
                flex-direction: column;
                padding: 1rem 0;
            }
            
            .roles-panel, .welcome-panel {
                max-width: 100%;
                padding: 0;
            }
            
            .roles-panel {
                margin-bottom: 3rem;
                padding-right: 0;
            }
            
            .roles-title::after {
                left: 50%;
                transform: translateX(-50%);
            }
            
            .welcome-panel {
                padding-left: 0;
                padding-top: 2rem;
            }
            
            .welcome-title {
                font-size: 2.2rem;
            }
            
            .welcome-title span {
                font-size: 2.6rem;
            }
            
            .institution-logo {
                height: 150px;
            }
        }
        
        @media (max-width: 576px) {
            .header {
                padding: 1.2rem 1rem;
                flex-direction: column;
                gap: 1rem;
            }
            
            .header-title {
                font-size: 1.3rem;
            }
            
            .role-card {
                flex-direction: column;
                text-align: center;
                padding: 1.5rem 1rem;
            }
            
            .role-icon-container {
                margin: 0 auto 1rem auto;
            }
            
            .role-card::before {
                width: 100%;
                height: 4px;
            }
            
            .institution-logo {
                height: 120px;
            }
        }
    </style>
</head>
<body>
    <!-- Efecto de destello sutil -->
    <div class="destello-container">
        <div class="destello"></div>
    </div>

    <!-- Encabezado Profesional -->
    <header class="header">
        <div class="header-title">
            <i class="fas fa-graduation-cap"></i>
            <span>SYSPRE 2025</span>
        </div>
        <nav class="header-nav">
            <a href="#">Sistema de Gestión</a>
        </nav>
    </header>

    <div class="main-container">
        <div class="content-wrapper">
            <section class="roles-panel" aria-labelledby="roles-title">
                <h2 class="roles-title" id="roles-title">Seleccione su rol:</h2>
                
                <!-- Estudiante -->
                <a href="views/login_estudiante.php" class="role-card estudiante" role="button">
                    <div class="role-icon-container">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="role-content">
                        <div class="role-title">Estudiante</div>
                        <div class="role-description">Registra tu plan de prácticas, reportes semanales y consulta tu progreso académico</div>
                    </div>
                </a>
                
                <!-- Docente -->
                <a href="views/login_docente.php" class="role-card docente" role="button">
                    <div class="role-icon-container">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div class="role-content">
                        <div class="role-title">Docente</div>
                        <div class="role-description">Supervisa estudiantes, revisa planes y evalúa el progreso académico</div>
                    </div>
                </a>
                
                <!-- Coordinador -->
                <a href="views/login_coordinador.php" class="role-card coordinador" role="button">
                    <div class="role-icon-container">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <div class="role-content">
                        <div class="role-title">Coordinador</div>
                        <div class="role-description">Gestiona procesos académicos, asigna asesores y supervisa el programa</div>
                    </div>
                </a>
                
                <!-- Administrador -->
                <a href="views/login_admin.php" class="role-card administrador" role="button">
                    <div class="role-icon-container">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="role-content">
                        <div class="role-title">Administrador</div>
                        <div class="role-description">Control total del sistema, gestión de usuarios y configuraciones</div>
                    </div>
                </a>
            </section>
            
          <section class="welcome-panel" aria-label="Bienvenida">
                <h1 class="welcome-title">Bienvenido a <span>SYSPRE</span></h1>
                <p class="institution-text">Universidad Nacional del Altiplano - Puno</p>
                <div style="margin-top: 2rem;">
                    <img src="views/imagen_unap_-removebg-preview.png" alt="Universidad Nacional del Altiplano" style="height: 300px; width: auto; opacity: 0.8;">
                </div>
            </section>
        </div>
    </div>

    <script>
        // Efecto de carga inicial para los elementos
        document.addEventListener('DOMContentLoaded', function() {
            const roles = document.querySelectorAll('.role-card');
            const welcomePanel = document.querySelector('.welcome-panel');
            
            // Animación para las tarjetas de rol
            roles.forEach((role, index) => {
                setTimeout(() => {
                    role.style.opacity = '1';
                    role.style.transform = 'translateY(0)';
                }, 100 * index);
            });
            
            // Animación para el panel de bienvenida
            setTimeout(() => {
                welcomePanel.style.opacity = '1';
                welcomePanel.style.transform = 'translateY(0)';
            }, 100 * roles.length);
        });
    </script>
</body>
</html>
