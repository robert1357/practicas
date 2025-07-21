<?php
require_once '../config/session.php';
require_once '../models/User.php';

// Verificar que el usuario esté autenticado y sea admin
if (!isAuthenticated() || $_SESSION['user_type'] !== 'admin') {
    header('Location: login_admin.php');
    exit();
}

$userModel = new User();

// Procesamiento del formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'crear':
                $data = [
                    'email' => $_POST['email'],
                    'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
                    'codigo' => $_POST['codigo'],
                    'nombres' => $_POST['nombres'],
                    'apellidos' => $_POST['apellidos'],
                    'telefono' => $_POST['telefono'],
                    'direccion' => $_POST['direccion'],
                    'fecha_nacimiento' => $_POST['fecha_nacimiento'],
                    'sexo' => $_POST['sexo'],
                    'tipo' => $_POST['tipo'],
                    'especialidad' => $_POST['especialidad'],
                    'semestre' => $_POST['semestre'] ?: null
                ];
                $result = $userModel->create($data);
                $message = $result ? 'Usuario creado exitosamente' : 'Error al crear el usuario';
                break;
                
            case 'actualizar':
                $data = [
                    'email' => $_POST['email'],
                    'codigo' => $_POST['codigo'],
                    'nombres' => $_POST['nombres'],
                    'apellidos' => $_POST['apellidos'],
                    'telefono' => $_POST['telefono'],
                    'direccion' => $_POST['direccion'],
                    'fecha_nacimiento' => $_POST['fecha_nacimiento'],
                    'sexo' => $_POST['sexo'],
                    'tipo' => $_POST['tipo'],
                    'especialidad' => $_POST['especialidad'],
                    'semestre' => $_POST['semestre'] ?: null,
                    'estado' => $_POST['estado']
                ];
                
                // Solo actualizar contraseña si se proporcionó una nueva
                if (!empty($_POST['password'])) {
                    $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
                }
                
                $result = $userModel->update($_POST['id'], $data);
                $message = $result ? 'Usuario actualizado exitosamente' : 'Error al actualizar el usuario';
                break;
                
            case 'cambiar_estado':
                $result = $userModel->updateEstado($_POST['id'], $_POST['estado']);
                $message = $result ? 'Estado actualizado exitosamente' : 'Error al actualizar el estado';
                break;
                
            case 'eliminar':
                $result = $userModel->delete($_POST['id']);
                $message = $result ? 'Usuario eliminado exitosamente' : 'Error al eliminar el usuario';
                break;
        }
    }
}

// Obtener usuarios
$usuarios = $userModel->getAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Usuarios - SYSPRE 2025</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen">

    <!-- Header -->
    <header class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <a href="admin_dashboard.php" class="text-xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-arrow-left"></i>
                <i class="fas fa-user-shield"></i>
                SYSPRE 2025
            </a>
            <div class="text-sm text-gray-600">
                Gestión de Usuarios
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 py-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-lg mb-6">
            <div class="bg-red-600 text-white p-6 rounded-t-lg">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold">
                            <i class="fas fa-users mr-2"></i>
                            Gestión de Usuarios
                        </h1>
                        <p class="mt-2 opacity-90">
                            Administra todos los usuarios del sistema
                        </p>
                    </div>
                    <button onclick="openModal('createModal')" class="bg-white text-red-600 px-4 py-2 rounded-lg font-semibold hover:bg-gray-100 transition">
                        <i class="fas fa-user-plus mr-2"></i>Nuevo Usuario
                    </button>
                </div>
            </div>

            <?php if (isset($message)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- Estadísticas -->
            <div class="p-6 border-b border-gray-200">
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-blue-600"><?php echo count($usuarios); ?></div>
                        <div class="text-sm text-gray-600">Total Usuarios</div>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-green-600">
                            <?php echo count(array_filter($usuarios, fn($u) => $u['tipo'] == 'estudiante')); ?>
                        </div>
                        <div class="text-sm text-gray-600">Estudiantes</div>
                    </div>
                    <div class="bg-yellow-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-yellow-600">
                            <?php echo count(array_filter($usuarios, fn($u) => $u['tipo'] == 'docente')); ?>
                        </div>
                        <div class="text-sm text-gray-600">Docentes</div>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-purple-600">
                            <?php echo count(array_filter($usuarios, fn($u) => $u['tipo'] == 'coordinador')); ?>
                        </div>
                        <div class="text-sm text-gray-600">Coordinadores</div>
                    </div>
                    <div class="bg-red-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-red-600">
                            <?php echo count(array_filter($usuarios, fn($u) => $u['tipo'] == 'admin')); ?>
                        </div>
                        <div class="text-sm text-gray-600">Admins</div>
                    </div>
                </div>
            </div>

            <!-- Tabla de Usuarios -->
            <div class="p-6 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contacto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                <i class="fas fa-user text-gray-600"></i>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($usuario['nombres'] . ' ' . $usuario['apellidos']); ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                <?php echo htmlspecialchars($usuario['email']); ?>
                                            </div>
                                            <?php if ($usuario['codigo']): ?>
                                                <div class="text-xs text-gray-400">
                                                    ID: <?php echo htmlspecialchars($usuario['codigo']); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                        <?php 
                                        switch($usuario['tipo']) {
                                            case 'admin':
                                                echo 'bg-red-100 text-red-800';
                                                break;
                                            case 'coordinador':
                                                echo 'bg-purple-100 text-purple-800';
                                                break;
                                            case 'docente':
                                                echo 'bg-yellow-100 text-yellow-800';
                                                break;
                                            case 'estudiante':
                                                echo 'bg-green-100 text-green-800';
                                                break;
                                            default:
                                                echo 'bg-gray-100 text-gray-800';
                                        }
                                        ?>">
                                        <?php echo ucfirst($usuario['tipo']); ?>
                                    </span>
                                    <?php if ($usuario['especialidad']): ?>
                                        <div class="text-xs text-gray-500 mt-1">
                                            <?php echo htmlspecialchars($usuario['especialidad']); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php if ($usuario['telefono']): ?>
                                        <div class="flex items-center">
                                            <i class="fas fa-phone text-gray-400 mr-1"></i>
                                            <?php echo htmlspecialchars($usuario['telefono']); ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($usuario['semestre']): ?>
                                        <div class="text-xs text-gray-500">
                                            Semestre: <?php echo $usuario['semestre']; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                        <?php echo $usuario['estado'] == 'activo' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                        <?php echo ucfirst($usuario['estado']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                    <button onclick="editUser(<?php echo $usuario['id']; ?>)" 
                                            class="text-yellow-600 hover:text-yellow-800">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="toggleUserStatus(<?php echo $usuario['id']; ?>, '<?php echo $usuario['estado'] == 'activo' ? 'inactivo' : 'activo'; ?>')" 
                                            class="text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-<?php echo $usuario['estado'] == 'activo' ? 'ban' : 'check'; ?>"></i>
                                    </button>
                                    <?php if ($usuario['id'] != $_SESSION['user_id']): ?>
                                        <button onclick="deleteUser(<?php echo $usuario['id']; ?>)" 
                                                class="text-red-600 hover:text-red-800">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Modal Crear/Editar Usuario -->
    <div id="createModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg w-full max-w-2xl max-h-screen overflow-y-auto">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold" id="modalTitle">Nuevo Usuario</h2>
                </div>
                <form id="userForm" method="POST" class="p-6">
                    <input type="hidden" name="action" value="crear">
                    <input type="hidden" name="id" id="userId">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                            <input type="email" name="email" id="email" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Contraseña *</label>
                            <input type="password" name="password" id="password" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Código</label>
                            <input type="text" name="codigo" id="codigo" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Usuario *</label>
                            <select name="tipo" id="tipo" required onchange="toggleSemestre()" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                                <option value="">Seleccionar...</option>
                                <option value="estudiante">Estudiante</option>
                                <option value="docente">Docente</option>
                                <option value="coordinador">Coordinador</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nombres *</label>
                            <input type="text" name="nombres" id="nombres" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Apellidos *</label>
                            <input type="text" name="apellidos" id="apellidos" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Teléfono</label>
                            <input type="tel" name="telefono" id="telefono" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fecha de Nacimiento</label>
                            <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Sexo</label>
                            <select name="sexo" id="sexo" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                                <option value="">Seleccionar...</option>
                                <option value="M">Masculino</option>
                                <option value="F">Femenino</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Especialidad</label>
                            <input type="text" name="especialidad" id="especialidad" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                        </div>

                        <div id="semestreDiv" style="display: none;">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Semestre</label>
                            <input type="number" name="semestre" id="semestre" min="1" max="12" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                        </div>

                        <div id="estadoDiv" style="display: none;">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                            <select name="estado" id="estado" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Dirección</label>
                            <textarea name="direccion" id="direccion" rows="2" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="closeModal('createModal')" 
                                class="px-4 py-2 text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50">
                            Cancelar
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openModal(modalId) {
            document.getElementById(modalId).classList.remove('hidden');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
            document.getElementById('userForm').reset();
            document.querySelector('input[name="action"]').value = 'crear';
            document.getElementById('modalTitle').textContent = 'Nuevo Usuario';
            document.getElementById('estadoDiv').style.display = 'none';
            document.getElementById('password').required = true;
        }

        function toggleSemestre() {
            const tipo = document.getElementById('tipo').value;
            const semestreDiv = document.getElementById('semestreDiv');
            
            if (tipo === 'estudiante') {
                semestreDiv.style.display = 'block';
            } else {
                semestreDiv.style.display = 'none';
            }
        }

        function editUser(id) {
            // Aquí deberías cargar los datos del usuario para editarlo
            document.querySelector('input[name="action"]').value = 'actualizar';
            document.getElementById('userId').value = id;
            document.getElementById('modalTitle').textContent = 'Editar Usuario';
            document.getElementById('estadoDiv').style.display = 'block';
            document.getElementById('password').required = false;
            openModal('createModal');
        }

        function toggleUserStatus(id, newStatus) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="cambiar_estado">
                <input type="hidden" name="id" value="${id}">
                <input type="hidden" name="estado" value="${newStatus}">
            `;
            document.body.appendChild(form);
            form.submit();
        }

        function deleteUser(id) {
            if (confirm('¿Estás seguro de que quieres eliminar este usuario?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="eliminar">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>

</body>
</html>