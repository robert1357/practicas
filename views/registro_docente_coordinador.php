<?php
require_once '../config/session.php';
require_once '../models/User.php';

// Verificar autenticación y rol de admin
if (!isAuthenticated() || !hasRole('admin')) {
    header("Location: login_admin.php");
    exit();
}

$user = getCurrentUser();
$userModel = new User();
$success = $error = '';

// Procesamiento del formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validar datos
    $required_fields = ['codigo_docente', 'nombres', 'apellidos', 'dni', 'telefono', 'direccion', 'correo', 'password', 'rol', 'escuela_profesional', 'categoria_docente', 'dedicacion', 'grado_academico'];
    
    $missing_fields = [];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $missing_fields[] = $field;
        }
    }
    
    if (!empty($missing_fields)) {
        $error = "Los siguientes campos son requeridos: " . implode(', ', $missing_fields);
    } elseif ($_POST['password'] !== $_POST['confirm_password']) {
        $error = "Las contraseñas no coinciden";
    } elseif ($userModel->emailExists($_POST['correo'])) {
        $error = "El correo electrónico ya está registrado";
    } elseif ($userModel->codigoExists($_POST['codigo_docente'])) {
        $error = "El código de docente ya está registrado";
    } else {
        // Crear nuevo usuario
        $userData = [
            'nombres' => $_POST['nombres'],
            'apellidos' => $_POST['apellidos'],
            'email' => $_POST['correo'],
            'password' => $_POST['password'],
            'codigo' => $_POST['codigo_docente'],
            'tipo' => $_POST['rol'], // 'docente' o 'coordinador'
            'dni' => $_POST['dni'],
            'telefono' => $_POST['telefono'],
            'direccion' => $_POST['direccion'],

            'especialidad' => $_POST['escuela_profesional'],
            'categoria_docente' => $_POST['categoria_docente'],
            'dedicacion' => $_POST['dedicacion'],
            'grado_academico' => $_POST['grado_academico'],
            'estado' => 'activo'
        ];
        
        if ($userModel->create($userData)) {
            $success = "Usuario " . $_POST['rol'] . " registrado exitosamente";
            // Limpiar formulario
            $_POST = [];
        } else {
            $error = "Error al registrar el usuario";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Registro Docente/Coordinador - SYSPRE 2025</title>

  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Font Awesome -->
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
      background: white;
      padding: 0 4px;
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
      <a href="admin_dashboard.php" class="text-xl font-bold text-texto flex items-center gap-2 hover:text-principal transition">
        <i class="fas fa-arrow-left"></i>
        <i class="fas fa-university"></i> 
        SYSPRE 2025
      </a>
      <div class="text-sm text-gris">
        <i class="fas fa-chalkboard-teacher mr-1"></i>
        Registro de Docentes/Coordinadores
      </div>
    </div>
  </header>

  <!-- Main Content -->
  <main class="relative z-10 flex items-center justify-center min-h-screen pt-20 pb-10 px-4">
    
    <div class="w-full max-w-4xl">
      
      <!-- Logo y Título -->
      <div class="text-center mb-8 slide-in">
        <div class="bg-gradient-to-br from-principal to-emerald-600 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6 floating">
          <i class="fas fa-chalkboard-teacher text-3xl text-white"></i>
        </div>
        <h1 class="text-3xl font-bold text-texto mb-2">Registro de Docente/Coordinador</h1>
        <p class="text-gris">Sistema de Prácticas Pre-Profesionales SYSPRE</p>
      </div>

      <!-- Mensajes -->
      <?php if (!empty($success)): ?>
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6 slide-in">
          <i class="fas fa-check-circle mr-2"></i><?php echo $success; ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($error)): ?>
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6 slide-in">
          <i class="fas fa-exclamation-circle mr-2"></i><?php echo $error; ?>
        </div>
      <?php endif; ?>

      <!-- Formulario de Registro -->
      <div class="registro-card rounded-2xl shadow-xl p-8 slide-in">
        <form id="registroForm" class="space-y-8" method="post">
          
          <!-- Información Institucional -->
          <div>
            <h3 class="text-lg font-semibold text-texto mb-4 flex items-center">
              <i class="fas fa-university mr-2 text-principal"></i>
              Información Institucional
            </h3>
            <div class="grid md:grid-cols-2 gap-6">
              
              <div class="input-group">
                <input
                  type="text"
                  id="codigoDocente"
                  name="codigo_docente"
                  placeholder=" "
                  value="<?php echo htmlspecialchars($_POST['codigo_docente'] ?? ''); ?>"
                  class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-principal focus:outline-none transition bg-white"
                  required
                />
                <label for="codigoDocente">
                  <i class="fas fa-id-card mr-1"></i>Código de Docente
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
                  id="categoria"
                  name="categoria_docente"
                  class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-principal focus:outline-none transition bg-white"
                  required
                >
                  <option value="">Seleccionar...</option>
                  <option value="principal" <?php echo ($_POST['categoria_docente'] ?? '') == 'principal' ? 'selected' : ''; ?>>Docente Principal</option>
                  <option value="asociado" <?php echo ($_POST['categoria_docente'] ?? '') == 'asociado' ? 'selected' : ''; ?>>Docente Asociado</option>
                  <option value="auxiliar" <?php echo ($_POST['categoria_docente'] ?? '') == 'auxiliar' ? 'selected' : ''; ?>>Docente Auxiliar</option>
                  <option value="contratado" <?php echo ($_POST['categoria_docente'] ?? '') == 'contratado' ? 'selected' : ''; ?>>Docente Contratado</option>
                  <option value="jefe_practica" <?php echo ($_POST['categoria_docente'] ?? '') == 'jefe_practica' ? 'selected' : ''; ?>>Jefe de Práctica</option>
                </select>
                <label for="categoria">
                  <i class="fas fa-user-tie mr-1"></i>Categoría Docente
                </label>
              </div>

              <div class="input-group">
                <select
                  id="dedicacion"
                  name="dedicacion"
                  class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-principal focus:outline-none transition bg-white"
                  required
                >
                  <option value="">Seleccionar...</option>
                  <option value="exclusiva" <?php echo ($_POST['dedicacion'] ?? '') == 'exclusiva' ? 'selected' : ''; ?>>Dedicación Exclusiva</option>
                  <option value="completa" <?php echo ($_POST['dedicacion'] ?? '') == 'completa' ? 'selected' : ''; ?>>Tiempo Completo</option>
                  <option value="parcial" <?php echo ($_POST['dedicacion'] ?? '') == 'parcial' ? 'selected' : ''; ?>>Tiempo Parcial</option>
                </select>
                <label for="dedicacion">
                  <i class="fas fa-clock mr-1"></i>Dedicación
                </label>
              </div>

              <div class="input-group">
                <select
                  id="gradoAcademico"
                  name="grado_academico"
                  class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-principal focus:outline-none transition bg-white"
                  required
                >
                  <option value="">Seleccionar...</option>
                  <option value="bachiller" <?php echo ($_POST['grado_academico'] ?? '') == 'bachiller' ? 'selected' : ''; ?>>Bachiller</option>
                  <option value="licenciado" <?php echo ($_POST['grado_academico'] ?? '') == 'licenciado' ? 'selected' : ''; ?>>Licenciado/Título Profesional</option>
                  <option value="maestria" <?php echo ($_POST['grado_academico'] ?? '') == 'maestria' ? 'selected' : ''; ?>>Maestría</option>
                  <option value="doctorado" <?php echo ($_POST['grado_academico'] ?? '') == 'doctorado' ? 'selected' : ''; ?>>Doctorado</option>
                </select>
                <label for="gradoAcademico">
                  <i class="fas fa-graduation-cap mr-1"></i>Grado Académico
                </label>
              </div>

              <div class="input-group">
                <select
                  id="rol"
                  name="rol"
                  class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-principal focus:outline-none transition bg-white"
                  required
                >
                  <option value="">Seleccionar...</option>
                  <option value="docente" <?php echo ($_POST['rol'] ?? '') == 'docente' ? 'selected' : ''; ?>>Docente</option>
                  <option value="coordinador" <?php echo ($_POST['rol'] ?? '') == 'coordinador' ? 'selected' : ''; ?>>Coordinador</option>
                </select>
                <label for="rol">
                  <i class="fas fa-user-cog mr-1"></i>Rol en el Sistema
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
                  value="<?php echo htmlspecialchars($_POST['nombres'] ?? ''); ?>"
                  class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-principal focus:outline-none transition bg-white"
                  required
                />
                <label for="nombres">
                  <i class="fas fa-user mr-1"></i>Nombres
                </label>
              </div>

              <div class="input-group">
                <input
                  type="text"
                  id="apellidos"
                  name="apellidos"
                  placeholder=" "
                  value="<?php echo htmlspecialchars($_POST['apellidos'] ?? ''); ?>"
                  class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-principal focus:outline-none transition bg-white"
                  required
                />
                <label for="apellidos">
                  <i class="fas fa-user mr-1"></i>Apellidos
                </label>
              </div>

              <div class="input-group">
                <input
                  type="text"
                  id="dni"
                  name="dni"
                  placeholder=" "
                  value="<?php echo htmlspecialchars($_POST['dni'] ?? ''); ?>"
                  class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-principal focus:outline-none transition bg-white"
                  required
                />
                <label for="dni">
                  <i class="fas fa-id-badge mr-1"></i>DNI
                </label>
              </div>

              <div class="input-group">
                <input
                  type="tel"
                  id="telefono"
                  name="telefono"
                  placeholder=" "
                  value="<?php echo htmlspecialchars($_POST['telefono'] ?? ''); ?>"
                  class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-principal focus:outline-none transition bg-white"
                  required
                />
                <label for="telefono">
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
                ><?php echo htmlspecialchars($_POST['direccion'] ?? ''); ?></textarea>
                <label for="direccion">
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
                  value="<?php echo htmlspecialchars($_POST['correo'] ?? ''); ?>"
                  class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-principal focus:outline-none transition bg-white"
                  required
                />
                <label for="correo">
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
                <label for="password">
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
                <label for="confirmPassword">
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
                <div class="text-xs text-gris mt-1" id="passwordStrengthText">Ingresa una contraseña</div>
              </div>

            </div>
          </div>

          <!-- Botones -->
          <div class="flex justify-center space-x-4 pt-6">
            <a href="admin_dashboard.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-3 px-8 rounded-lg transition-colors">
              <i class="fas fa-times mr-2"></i>Cancelar
            </a>
            <button type="submit" class="bg-principal hover:bg-emerald-600 text-white font-bold py-3 px-8 rounded-lg transition-colors">
              <i class="fas fa-save mr-2"></i>Registrar Usuario
            </button>
          </div>

        </form>
      </div>
    </div>
  </main>

  <script>
    function togglePassword(fieldId) {
      const field = document.getElementById(fieldId);
      const icon = document.getElementById(fieldId + 'Icon');
      
      if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
      } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
      }
    }

    function checkPasswordStrength() {
      const password = document.getElementById('password').value;
      const strengthBar = document.getElementById('passwordStrengthBar');
      const strengthText = document.getElementById('passwordStrengthText');
      
      let strength = 0;
      let text = '';
      
      if (password.length >= 6) strength += 25;
      if (/[a-z]/.test(password)) strength += 25;
      if (/[A-Z]/.test(password)) strength += 25;
      if (/[0-9]/.test(password)) strength += 25;
      
      if (strength < 25) {
        strengthBar.className = 'password-strength rounded-full bg-red-500';
        text = 'Muy débil';
      } else if (strength < 50) {
        strengthBar.className = 'password-strength rounded-full bg-orange-500';
        text = 'Débil';
      } else if (strength < 75) {
        strengthBar.className = 'password-strength rounded-full bg-yellow-500';
        text = 'Regular';
      } else {
        strengthBar.className = 'password-strength rounded-full bg-green-500';
        text = 'Fuerte';
      }
      
      strengthBar.style.width = strength + '%';
      strengthText.textContent = text;
    }

    function actualizarEscuelas() {
      const facultad = document.getElementById('facultad').value;
      const escuela = document.getElementById('escuela');
      
      escuela.innerHTML = '<option value="">Seleccionar escuela...</option>';
      
      if (facultad === 'ingenieria') {
        escuela.innerHTML += '<option value="sistemas">Ingeniería de Sistemas</option>';
        escuela.innerHTML += '<option value="civil">Ingeniería Civil</option>';
        escuela.innerHTML += '<option value="industrial">Ingeniería Industrial</option>';
        escuela.innerHTML += '<option value="electronica">Ingeniería Electrónica</option>';
      } else if (facultad === 'biomedica') {
        escuela.innerHTML += '<option value="medicina">Medicina</option>';
        escuela.innerHTML += '<option value="enfermeria">Enfermería</option>';
        escuela.innerHTML += '<option value="obstetricia">Obstetricia</option>';
        escuela.innerHTML += '<option value="farmacia">Farmacia</option>';
      } else if (facultad === 'sociales') {
        escuela.innerHTML += '<option value="derecho">Derecho</option>';
        escuela.innerHTML += '<option value="educacion">Educación</option>';
        escuela.innerHTML += '<option value="psicologia">Psicología</option>';
        escuela.innerHTML += '<option value="contabilidad">Contabilidad</option>';
      }
      
      escuela.disabled = facultad === '';
    }

    // Validación de formulario
    document.getElementById('registroForm').addEventListener('submit', function(e) {
      const password = document.getElementById('password').value;
      const confirmPassword = document.getElementById('confirmPassword').value;
      
      if (password !== confirmPassword) {
        e.preventDefault();
        alert('Las contraseñas no coinciden');
        return false;
      }
      
      if (password.length < 6) {
        e.preventDefault();
        alert('La contraseña debe tener al menos 6 caracteres');
        return false;
      }
    });
  </script>
</body>
</html>