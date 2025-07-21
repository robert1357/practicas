<?php
require_once '../config/session.php';
require_once '../models/DocumentoReglamento.php';

// Verificar que el usuario esté autenticado y sea coordinador
if (!isAuthenticated() || $_SESSION['user_type'] !== 'coordinador') {
    header('Location: login_coordinador.php');
    exit();
}

$documentoModel = new DocumentoReglamento();

// Procesamiento del formulario con upload de archivos
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'crear':
                $archivo_ruta = '';
                
                // Procesar archivo subido
                if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
                    require_once '../config/upload_config.php';
                    
                    $upload_dir = '../uploads/documentos/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $filename = time() . '_' . $_FILES['archivo']['name'];
                    $archivo_ruta = $upload_dir . $filename;
                    
                    if (move_uploaded_file($_FILES['archivo']['tmp_name'], $archivo_ruta)) {
                        $archivo_ruta = 'uploads/documentos/' . $filename; // Ruta relativa para base de datos
                    } else {
                        $error = 'Error al subir el archivo';
                    }
                }
                
                if (!isset($error)) {
                    $user = getCurrentUser();
                    $data = [
                        'titulo' => $_POST['titulo'],
                        'descripcion' => $_POST['descripcion'],
                        'tipo_documento' => $_POST['tipo_documento'],
                        'archivo_url' => $archivo_ruta,
                        'especialidad' => $user['especialidad'],
                        'creado_por' => $_SESSION['user_id']
                    ];
                    $result = $documentoModel->create($data);
                    $message = $result ? 'Documento creado exitosamente' : 'Error al crear el documento';
                }
                break;
                
            case 'actualizar':
                $user = getCurrentUser();
                $data = [
                    'titulo' => $_POST['titulo'],
                    'descripcion' => $_POST['descripcion'],
                    'tipo_documento' => $_POST['tipo_documento'],
                    'archivo_url' => $_POST['archivo_url'] ?? '',
                    'especialidad' => $user['especialidad']
                ];
                $result = $documentoModel->update($_POST['id'], $data);
                $message = $result ? 'Documento actualizado exitosamente' : 'Error al actualizar el documento';
                break;
                
            case 'eliminar':
                $result = $documentoModel->delete($_POST['id']);
                $message = $result ? 'Documento eliminado exitosamente' : 'Error al eliminar el documento';
                break;
        }
    }
}

// Obtener documentos del coordinador por especialidad
$user = getCurrentUser();
$documentos = $documentoModel->getByEspecialidad($user['especialidad']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Documentos - SYSPRE 2025</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen">

    <!-- Header -->
    <header class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <a href="coordinador_dashboard.php" class="text-xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-arrow-left"></i>
                <i class="fas fa-user-tie"></i>
                SYSPRE 2025
            </a>
            <div class="text-sm text-gray-600">
                Gestión de Documentos
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 py-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-lg mb-6">
            <div class="bg-purple-600 text-white p-6 rounded-t-lg">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold">
                            <i class="fas fa-folder-open mr-2"></i>
                            Gestión de Documentos
                        </h1>
                        <p class="mt-2 opacity-90">
                            Administra reglamentos, formatos y guías del sistema
                        </p>
                    </div>
                    <button onclick="openModal('createModal')" class="bg-white text-purple-600 px-4 py-2 rounded-lg font-semibold hover:bg-gray-100 transition">
                        <i class="fas fa-plus mr-2"></i>Nuevo Documento
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
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-blue-600"><?php echo count($documentos); ?></div>
                        <div class="text-sm text-gray-600">Total Documentos</div>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-green-600">
                            <?php echo count(array_filter($documentos, fn($d) => $d['tipo_documento'] == 'reglamento')); ?>
                        </div>
                        <div class="text-sm text-gray-600">Reglamentos</div>
                    </div>
                    <div class="bg-yellow-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-yellow-600">
                            <?php echo count(array_filter($documentos, fn($d) => $d['tipo_documento'] == 'formato')); ?>
                        </div>
                        <div class="text-sm text-gray-600">Formatos</div>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-purple-600">
                            <?php echo count(array_filter($documentos, fn($d) => $d['tipo_documento'] == 'guia')); ?>
                        </div>
                        <div class="text-sm text-gray-600">Guías</div>
                    </div>
                </div>
            </div>

            <!-- Lista de Documentos -->
            <div class="p-6">
                <?php if (empty($documentos)): ?>
                    <div class="text-center py-12">
                        <i class="fas fa-folder-open text-6xl text-gray-300 mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-600 mb-2">No hay documentos registrados</h3>
                        <p class="text-gray-500 mb-6">Comienza agregando el primer documento</p>
                        <button onclick="openModal('createModal')" class="bg-purple-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-purple-700 transition">
                            <i class="fas fa-plus mr-2"></i>Agregar Documento
                        </button>
                    </div>
                <?php else: ?>
                    <div class="grid gap-6">
                        <?php foreach ($documentos as $documento): ?>
                            <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition">
                                <div class="flex justify-between items-start mb-4">
                                    <div class="flex-1">
                                        <h3 class="text-lg font-semibold text-gray-800 mb-2">
                                            <i class="fas fa-file-alt mr-2 text-purple-600"></i>
                                            <?php echo htmlspecialchars($documento['titulo']); ?>
                                        </h3>
                                        <p class="text-gray-600 mb-3">
                                            <?php echo htmlspecialchars($documento['descripcion']); ?>
                                        </p>
                                        <div class="flex items-center space-x-4">
                                            <span class="px-3 py-1 rounded-full text-sm font-medium 
                                                <?php 
                                                switch($documento['tipo_documento']) {
                                                    case 'reglamento':
                                                        echo 'bg-blue-100 text-blue-800';
                                                        break;
                                                    case 'formato':
                                                        echo 'bg-green-100 text-green-800';
                                                        break;
                                                    case 'guia':
                                                        echo 'bg-yellow-100 text-yellow-800';
                                                        break;
                                                    default:
                                                        echo 'bg-gray-100 text-gray-800';
                                                }
                                                ?>">
                                                <?php echo ucfirst($documento['tipo_documento']); ?>
                                            </span>
                                            <span class="text-sm text-gray-500">
                                                <i class="fas fa-calendar mr-1"></i>
                                                <?php echo date('d/m/Y', strtotime($documento['fecha_creacion'])); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex space-x-2">
                                        <?php if ($documento['archivo_url']): ?>
                                            <a href="<?php echo htmlspecialchars($documento['archivo_url']); ?>" 
                                               target="_blank" 
                                               class="text-blue-600 hover:text-blue-800 px-3 py-1 rounded border border-blue-200 text-sm">
                                                <i class="fas fa-download mr-1"></i>Descargar
                                            </a>
                                        <?php endif; ?>
                                        <button onclick="editDocument(<?php echo $documento['id']; ?>)" 
                                                class="text-yellow-600 hover:text-yellow-800 px-3 py-1 rounded border border-yellow-200 text-sm">
                                            <i class="fas fa-edit mr-1"></i>Editar
                                        </button>
                                        <button onclick="deleteDocument(<?php echo $documento['id']; ?>)" 
                                                class="text-red-600 hover:text-red-800 px-3 py-1 rounded border border-red-200 text-sm">
                                            <i class="fas fa-trash mr-1"></i>Eliminar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Modal Crear/Editar Documento -->
    <div id="createModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg w-full max-w-md">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold" id="modalTitle">Nuevo Documento</h2>
                </div>
                <form id="documentForm" method="POST" enctype="multipart/form-data" class="p-6">
                    <input type="hidden" name="action" value="crear">
                    <input type="hidden" name="id" id="documentId">
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Título *
                            </label>
                            <input type="text" name="titulo" id="titulo" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Descripción *
                            </label>
                            <textarea name="descripcion" id="descripcion" required rows="3" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tipo de Documento *
                            </label>
                            <select name="tipo_documento" id="tipo_documento" required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                                <option value="">Seleccionar...</option>
                                <option value="reglamento">Reglamento</option>
                                <option value="formato">Formato</option>
                                <option value="guia">Guía</option>
                                <option value="otro">Otro</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Archivo (PDF, DOC, DOCX)
                            </label>
                            <input type="file" name="archivo" id="archivo" 
                                   accept=".pdf,.doc,.docx"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <p class="text-sm text-gray-500 mt-1">Máximo 10MB. Formatos: PDF, DOC, DOCX</p>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="closeModal('createModal')" 
                                class="px-4 py-2 text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50">
                            Cancelar
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">
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
            document.getElementById('documentForm').reset();
            document.querySelector('input[name="action"]').value = 'crear';
            document.getElementById('modalTitle').textContent = 'Nuevo Documento';
        }

        function editDocument(id) {
            // Aquí deberías cargar los datos del documento para editarlo
            // Por simplicidad, solo cambio el formulario a modo edición
            document.querySelector('input[name="action"]').value = 'actualizar';
            document.getElementById('documentId').value = id;
            document.getElementById('modalTitle').textContent = 'Editar Documento';
            openModal('createModal');
        }

        function deleteDocument(id) {
            if (confirm('¿Estás seguro de que quieres eliminar este documento?')) {
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