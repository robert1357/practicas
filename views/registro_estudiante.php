<?php
require_once '../config/session.php';
require_once '../models/User.php';

// Procesamiento del formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = new User();
    
    $data = [
        'codigo' => $_POST['codigo_estudiante'],
        'nombres' => $_POST['nombres'],
        'apellidos' => $_POST['apellidos'],
        'dni' => $_POST['dni'],
        'telefono' => $_POST['telefono'],
        'direccion' => $_POST['direccion'],
        'email' => $_POST['correo'],
        'password' => $_POST['password'],

        'especialidad' => $_POST['escuela_profesional'],
        'semestre' => $_POST['semestre'],
        'ciclo_academico' => $_POST['ciclo_academico'],
        'tipo' => 'estudiante'
    ];
    
    // Validar que las contraseñas coincidan
    if ($_POST['password'] !== $_POST['confirm_password']) {
        $error = "Las contraseñas no coinciden";
    } else {
        $result = $user->create($data);
        if ($result) {
            header('Location: login_estudiante.php?success=1');
            exit();
        } else {
            $error = "Error al registrar el estudiante";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Registro Estudiante - SYSPRE 2025</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    
    <script>
        tailwind.config = {
            devtools: false,
            theme: {
                extend: {
                    colors: {
                        principal: '#3eb489',
                        fondo: '#f1f5f9',
                        texto: '#1e293b',
                        gris: '#64748b',
                    },
                },
            },
        };
    </script>

    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #3eb489 0%, #2d8659 100%);
        }
        .registro-card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }
        .input-group {
            position: relative;
        }
        .input-group input:focus + label,
        .input-group input:not(:placeholder-shown) + label,
        .input-group select:focus + label,
        .input-group select:not([value=""]) + label,
        .input-group textarea:focus + label,
        .input-group textarea:not(:placeholder-shown) + label {
            transform: translateY(-20px) scale(0.8);
            color: #3eb489;
        }
        .input-group label {
            position: absolute;
            left: 12px;
            top: 12px;  
            transition: all 0.2s ease;
            pointer-events: none;
            color: #64748b;
        }
        .floating {
            animation: floating 3s ease-in-out infinite;
        }
        @keyframes floating {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .slide-in {
            animation: slideIn 0.6s ease-out forwards;
        }
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .password-strength {
            height: 4px;
            transition: all 0.3s ease;
        }
    </style>
</head>
<body class="bg-fondo min-h-screen">

    <!-- Background Pattern -->
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-principal opacity-10 rounded-full"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-blue-500 opacity-10 rounded-full"></div>
    </div>

    <!-- Header -->
    <header class="relative z-10 bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <a href="login_estudiante.php" class="text-xl font-bold text-texto flex items-center gap-2 hover:text-principal transition">
                <i class="fas fa-arrow-left"></i>
                <i class="fas fa-university"></i> 
                SYSPRE 2025
            </a>
            <div class="text-sm text-gris">
                <i class="fas fa-user-plus mr-1"></i>
                Registro de Estudiantes
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="relative z-10 flex items-center justify-center min-h-screen pt-20 pb-10 px-4">
        
        <div class="w-full max-w-4xl">
            
            <!-- Logo y Título -->
            <div class="text-center mb-8 slide-in">
                <div class="bg-gradient-to-br from-principal to-emerald-600 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6 floating">
                    <i class="fas fa-user-plus text-3xl text-white"></i>
                </div>
                <h1 class="text-3xl font-bold text-texto mb-2">Registro de Estudiante</h1>
                <p class="text-gris">Sistema de Prácticas Pre-Profesionales</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Formulario de Registro -->
            <div class="registro-card rounded-2xl shadow-xl p-8 slide-in">
                <form id="registroForm" class="space-y-8" method="post">
                    
                    <!-- Información Académica -->
                    <div>
                        <h3 class="text-lg font-semibold text-texto mb-4 flex items-center">
                            <i class="fas fa-graduation-cap mr-2 text-principal"></i>
                            Información Académica
                        </h3>
                        <div class="grid md:grid-cols-2 gap-6">
                            
                            <div class="input-group">
                                <input
                                    type="text"
                                    id="codigoUnv"
                                    name="codigo_estudiante"
                                    placeholder=" "
                                    pattern="[0-9]{6,10}"
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-principal focus:outline-none transition bg-white"
                                    required
                                />
                                <label for="codigoUnv" class="bg-white px-2">
                                    <i class="fas fa-id-card mr-1"></i>Código de Estudiante
                                </label>
                            </div>

                            <div class="input-group">
                                <select
                                    id="escuela_profesional"
                                    name="escuela_profesional"
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-principal focus:outline-none transition bg-white"
                                    required
                                >
                                    <option value="">Seleccionar Escuela Profesional...</option>
                                    <optgroup label="Ingenierías">
                                        <option value="Ingeniería Agronómica">Ingeniería Agronómica</option>
                                        <option value="Ingeniería Agroindustrial">Ingeniería Agroindustrial</option>
                                        <option value="Ingeniería Agrícola">Ingeniería Agrícola</option>
                                        <option value="Ingeniería Topográfica y Agrimensura">Ingeniería Topográfica y Agrimensura</option>
                                        <option value="Ingeniería Civil">Ingeniería Civil</option>
                                        <option value="Ingeniería Estadística e Informática">Ingeniería Estadística e Informática</option>
                                        <option value="Ingeniería Geológica">Ingeniería Geológica</option>
                                        <option value="Ingeniería Metalúrgica">Ingeniería Metalúrgica</option>
                                        <option value="Ingeniería Química">Ingeniería Química</option>
                                        <option value="Ingeniería de Minas">Ingeniería de Minas</option>
                                        <option value="Ingeniería Mecánica Eléctrica">Ingeniería Mecánica Eléctrica</option>
                                        <option value="Ingeniería Eléctrica">Ingeniería Eléctrica</option>
                                        <option value="Ingeniería de Sistemas">Ingeniería de Sistemas</option>
                                        <option value="Ciencias Físico‑Matemáticas">Ciencias Físico‑Matemáticas</option>
                                        <option value="Arquitectura y Urbanismo">Arquitectura y Urbanismo</option>
                                    </optgroup>
                                    <optgroup label="Ciencias Biomédicas">
                                        <option value="Medicina Humana">Medicina Humana</option>
                                        <option value="Medicina Veterinaria y Zootecnia">Medicina Veterinaria y Zootecnia</option>
                                        <option value="Enfermería">Enfermería</option>
                                        <option value="Nutrición Humana">Nutrición Humana</option>
                                        <option value="Odontología">Odontología</option>
                                        <option value="Biología-Pesquería">Biología-Pesquería</option>
                                        <option value="Biología-Microbiología">Biología-Microbiología</option>
                                        <option value="Biología-Ecología">Biología-Ecología</option>
                                    </optgroup>
                                    <optgroup label="Ciencias Sociales">
                                        <option value="Derecho">Derecho</option>
                                        <option value="Administración">Administración</option>
                                        <option value="Ciencias Contables">Ciencias Contables</option>
                                        <option value="Psicología">Psicología</option>
                                        <option value="Trabajo Social">Trabajo Social</option>
                                        <option value="Sociología">Sociología</option>
                                        <option value="Antropología">Antropología</option>
                                        <option value="Comunicación">Comunicación</option>
                                        <option value="Turismo">Turismo</option>
                                        <option value="Educación Inicial">Educación Inicial</option>
                                        <option value="Educación Primaria">Educación Primaria</option>
                                        <option value="Educación Física">Educación Física</option>
                                        <option value="Educación Secundaria-LITERATURA">Educación Secundaria-LITERATURA</option>
                                        <option value="Educación Secundaria-MATEMATICA">Educación Secundaria-MATEMATICA</option>
                                        <option value="Educación Secundaria-CS">Educación Secundaria-CS</option>
                                        <option value="Educación Secundaria-CTA">Educación Secundaria-CTA</option>
                                        <option value="ARTE-MUSICA">ARTE-MUSICA</option>
                                        <option value="ARTE-PLASTICAS">ARTE-PLASTICAS</option>
                                        <option value="ARTE-DANZA">ARTE-DANZA</option>
                                    </optgroup>
                                </select>
                                <label for="escuela_profesional" class="bg-white px-2">
                                    <i class="fas fa-school mr-1"></i>Escuela Profesional
                                </label>
                            </div>

                            <div class="input-group">
                                <select
                                    id="semestre"
                                    name="semestre"
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-principal focus:outline-none transition bg-white"
                                    required
                                >
                                    <option value="">Seleccionar...</option>
                                    <option value="1">I Semestre</option>
                                    <option value="2">II Semestre</option>
                                    <option value="3">III Semestre</option>
                                    <option value="4">IV Semestre</option>
                                    <option value="5">V Semestre</option>
                                    <option value="6">VI Semestre</option>
                                    <option value="7">VII Semestre</option>
                                    <option value="8">VIII Semestre</option>
                                    <option value="9">IX Semestre</option>
                                    <option value="10">X Semestre</option>
                                </select>
                                <label for="semestre" class="bg-white px-2">
                                    <i class="fas fa-calendar-week mr-1"></i>Semestre
                                </label>
                            </div>

                            <div class="input-group md:col-span-2">
                                <input
                                    type="text"
                                    id="cicloAcademico"
                                    name="ciclo_academico"
                                    placeholder=" "
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-principal focus:outline-none transition bg-white"
                                    required
                                />
                                <label for="cicloAcademico" class="bg-white px-2">
                                    <i class="fas fa-calendar-alt mr-1"></i>Ciclo Académico (ej: 2025-I)
                                </label>
                            </div>

                        </div>
                    </div>

                    <!-- Información Personal -->
                    <div>
                        <h3 class="text-lg font-semibold text-texto mb-4 flex items-center">
                            <i class="fas fa-user mr-2 text-principal"></i>
                            Información Personal
                        </h3>
                        <div class="grid md:grid-cols-2 gap-6">

                            <div class="input-group">
                                <input
                                    type="text"
                                    id="nombres"
                                    name="nombres"
                                    placeholder=" "
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-principal focus:outline-none transition bg-white"
                                    required
                                />
                                <label for="nombres" class="bg-white px-2">
                                    <i class="fas fa-user mr-1"></i>Nombres
                                </label>
                            </div>

                            <div class="input-group">
                                <input
                                    type="text"
                                    id="apellidos"
                                    name="apellidos"
                                    placeholder=" "
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-principal focus:outline-none transition bg-white"
                                    required
                                />
                                <label for="apellidos" class="bg-white px-2">
                                    <i class="fas fa-user mr-1"></i>Apellidos
                                </label>
                            </div>

                            <div class="input-group">
                                <div class="flex gap-2">
                                    <input
                                        type="text"
                                        id="dni"
                                        name="dni"
                                        placeholder="DNI"
                                        maxlength="8"
                                        pattern="[0-9]{8}"
                                        class="flex-1 px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-principal focus:outline-none transition bg-white"
                                        required
                                    />
                                    <button
                                        type="button"
                                        id="consultarDNI"
                                        class="px-4 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition duration-200 flex items-center justify-center min-w-[50px]"
                                        title="Consultar DNI con RENIEC"
                                    >
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                                <label for="dni" class="bg-white px-2">
                                    <i class=" "></i>
                                </label>
                            </div>

                            <div class="input-group">
                                <input
                                    type="tel"
                                    id="telefono"
                                    name="telefono"
                                    placeholder=" "
                                    pattern="[0-9]{9}"
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-principal focus:outline-none transition bg-white"
                                    required
                                />
                                <label for="telefono" class="bg-white px-2">
                                    <i class="fas fa-phone mr-1"></i>Teléfono
                                </label>
                            </div>

                            <div class="input-group md:col-span-2">
                                <textarea
                                    id="direccion"
                                    name="direccion"
                                    rows="3"
                                    placeholder=" "
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-principal focus:outline-none transition bg-white resize-none"
                                    required
                                ></textarea>
                                <label for="direccion" class="bg-white px-2">
                                    <i class="fas fa-map-marker-alt mr-1"></i>Dirección
                                </label>
                            </div>

                        </div>
                    </div>

                    <!-- Información de Acceso -->
                    <div>
                        <h3 class="text-lg font-semibold text-texto mb-4 flex items-center">
                            <i class="fas fa-lock mr-2 text-principal"></i>
                            Información de Acceso
                        </h3>
                        <div class="grid md:grid-cols-2 gap-6">

                            <div class="input-group md:col-span-2">
                                <input
                                    type="email"
                                    id="correo"
                                    name="correo"
                                    placeholder=" "
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-principal focus:outline-none transition bg-white"
                                    required
                                />
                                <label for="correo" class="bg-white px-2">
                                    <i class="fas fa-envelope mr-1"></i>Correo Electrónico
                                </label>
                            </div>

                            <div class="input-group">
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    placeholder=" "
                                    minlength="6"
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-principal focus:outline-none transition bg-white pr-12"
                                    required
                                    oninput="checkPasswordStrength()"
                                />
                                <label for="password" class="bg-white px-2">
                                    <i class="fas fa-lock mr-1"></i>Contraseña
                                </label>
                                <button
                                    type="button"
                                    class="absolute right-3 top-3 text-gris hover:text-texto"
                                    onclick="togglePassword('password')"
                                >
                                    <i class="fas fa-eye" id="passwordIcon"></i>
                                </button>
                            </div>

                            <div class="input-group">
                                <input
                                    type="password"
                                    id="confirmPassword"
                                    name="confirm_password"
                                    placeholder=" "
                                    minlength="6"
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-principal focus:outline-none transition bg-white pr-12"
                                    required
                                />
                                <label for="confirmPassword" class="bg-white px-2">
                                    <i class="fas fa-lock mr-1"></i>Confirmar Contraseña
                                </label>
                                <button
                                    type="button"
                                    class="absolute right-3 top-3 text-gris hover:text-texto"
                                    onclick="togglePassword('confirmPassword')"
                                >
                                    <i class="fas fa-eye" id="confirmPasswordIcon"></i>
                                </button>
                            </div>

                            <!-- Indicador de fortaleza de contraseña -->
                            <div class="md:col-span-2">
                                <div class="text-xs text-gris mb-2">Fortaleza de la contraseña:</div>
                                <div class="w-full bg-gray-200 rounded-full password-strength">
                                    <div id="passwordStrengthBar" class="password-strength rounded-full bg-red-500" style="width: 0%"></div>
                                </div>
                                <div id="passwordStrengthText" class="text-xs text-gris mt-1">Ingresa una contraseña</div>
                            </div>

                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="space-y-4">
                        
                        <button
                            type="submit"
                            class="w-full bg-principal hover:bg-emerald-700 text-white py-3 px-6 rounded-lg font-semibold transition transform hover:scale-105 active:scale-95"
                            id="registroBtn"
                        >
                            <i class="fas fa-user-plus mr-2"></i>
                            <span id="registroText">Registrar Cuenta</span>
                        </button>

                        <div class="relative">
                            <div class="absolute inset-0 flex items-center">
                                <div class="w-full border-t border-gray-300"></div>
                            </div>
                            <div class="relative flex justify-center text-sm">
                                <span class="px-2 bg-white text-gris">o</span>
                            </div>
                        </div>

                        <a
                            href="login_estudiante.php"
                            class="w-full bg-white hover:bg-gray-50 text-texto py-3 px-6 rounded-lg font-semibold border-2 border-gray-200 hover:border-gray-300 transition flex items-center justify-center"
                        >
                            <i class="fas fa-sign-in-alt mr-2"></i>
                            Ya tengo cuenta - Iniciar Sesión
                        </a>

                    </div>

                </form>
            </div>

        </div>

    </main>

    <script>
        // Función para consultar DNI con RENIEC
        document.getElementById('consultarDNI').addEventListener('click', function() {
            const dni = document.getElementById('dni').value;
            const button = this;
            
            if (dni.length !== 8) {
                alert('Por favor ingrese un DNI válido de 8 dígitos');
                return;
            }
            
            // Mostrar loading
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            button.disabled = true;
            
            // Consultar API de RENIEC
            fetch(`../api/reniec_api.php?dni=${dni}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Actualizar campos con datos de RENIEC
                        document.getElementById('nombres').value = data.nombres;
                        document.getElementById('apellidos').value = data.apellidos;
                        
                        // Mostrar mensaje de éxito
                        showMessage('success', 'Datos verificados con RENIEC');
                        
                    } else {
                        // Mostrar error
                        showMessage('error', data.error || 'Error al consultar DNI');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showMessage('error', 'Error al consultar DNI. Intente nuevamente.');
                })
                .finally(() => {
                    // Restaurar botón
                    button.innerHTML = '<i class="fas fa-search"></i>';
                    button.disabled = false;
                });
        });
        
        // Función para mostrar mensajes
        function showMessage(type, message) {
            const existingMessage = document.getElementById('reniec-message');
            if (existingMessage) {
                existingMessage.remove();
            }
            
            const messageDiv = document.createElement('div');
            messageDiv.id = 'reniec-message';
            messageDiv.className = `mt-2 p-3 rounded-lg text-sm ${type === 'success' ? 'bg-green-100 border border-green-300 text-green-700' : 'bg-red-100 border border-red-300 text-red-700'}`;
            messageDiv.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} mr-2"></i>${message}`;
            
            document.getElementById('dni').parentNode.parentNode.appendChild(messageDiv);
            
            // Remover mensaje después de 4 segundos
            setTimeout(() => {
                messageDiv.remove();
            }, 4000);
        }
        
        // Permitir consulta con Enter en campo DNI
        document.getElementById('dni').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('consultarDNI').click();
            }
        });
        
        // Validar solo números en DNI
        document.getElementById('dni').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    </script>

</body>
</html>