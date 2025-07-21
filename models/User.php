<?php
require_once __DIR__ . '/../config/database.php';

class User {
    private $conn;
    private $table = 'users';
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }
    
    // Crear tablas si no existen
    public function createTables() {
        $queries = [
            "CREATE TABLE IF NOT EXISTS usuarios (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                codigo VARCHAR(50) UNIQUE NOT NULL,
                nombres VARCHAR(255) NOT NULL,
                apellidos VARCHAR(255) NOT NULL,
                tipo ENUM('estudiante', 'docente', 'coordinador', 'admin') NOT NULL,
                especialidad VARCHAR(255) DEFAULT NULL,
                telefono VARCHAR(20) DEFAULT NULL,
                dni VARCHAR(20) DEFAULT NULL,
                direccion TEXT DEFAULT NULL,
                semestre VARCHAR(20) DEFAULT NULL,
                ciclo_academico VARCHAR(20) DEFAULT NULL,
                fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                activo TINYINT(1) DEFAULT 1
            )",
            
            "CREATE TABLE IF NOT EXISTS planes_practica (
                id INT AUTO_INCREMENT PRIMARY KEY,
                estudiante_id INT NOT NULL,
                empresa VARCHAR(255) NOT NULL,
                ruc VARCHAR(20) NOT NULL,
                direccion_empresa TEXT NOT NULL,
                supervisor_empresa VARCHAR(255) NOT NULL,
                telefono_empresa VARCHAR(20) NOT NULL,
                area_practica VARCHAR(255) NOT NULL,
                fecha_inicio DATE NOT NULL,
                fecha_fin DATE NOT NULL,
                horas_semanales INT NOT NULL,
                objetivos TEXT NOT NULL,
                actividades TEXT NOT NULL,
                estado ENUM('pendiente', 'aprobado', 'rechazado') DEFAULT 'pendiente',
                comentarios_docente TEXT DEFAULT NULL,
                comentarios_coordinador TEXT DEFAULT NULL,
                nota_docente DECIMAL(3,1) DEFAULT NULL,
                fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (estudiante_id) REFERENCES usuarios(id) ON DELETE CASCADE
            )",
            
            "CREATE TABLE IF NOT EXISTS reportes_semanales (
                id INT AUTO_INCREMENT PRIMARY KEY,
                estudiante_id INT NOT NULL,
                semana INT NOT NULL,
                fecha_inicio DATE NOT NULL,
                fecha_fin DATE NOT NULL,
                actividades_realizadas TEXT NOT NULL,
                logros_obtenidos TEXT NOT NULL,
                dificultades_encontradas TEXT DEFAULT NULL,
                horas_trabajadas INT NOT NULL,
                estado ENUM('pendiente', 'calificado') DEFAULT 'pendiente',
                nota_docente DECIMAL(3,1) DEFAULT NULL,
                comentarios_docente TEXT DEFAULT NULL,
                fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (estudiante_id) REFERENCES usuarios(id) ON DELETE CASCADE
            )",
            
            "CREATE TABLE IF NOT EXISTS informes_finales (
                id INT AUTO_INCREMENT PRIMARY KEY,
                estudiante_id INT NOT NULL,
                titulo VARCHAR(255) NOT NULL,
                resumen_ejecutivo TEXT NOT NULL,
                introduccion TEXT NOT NULL,
                objetivos_cumplidos TEXT NOT NULL,
                metodologia_utilizada TEXT NOT NULL,
                resultados_obtenidos TEXT NOT NULL,
                conclusiones TEXT NOT NULL,
                recomendaciones TEXT NOT NULL,
                anexos TEXT DEFAULT NULL,
                archivo_adjunto VARCHAR(255) DEFAULT NULL,
                estado ENUM('pendiente', 'aprobado', 'rechazado') DEFAULT 'pendiente',
                nota_docente DECIMAL(3,1) DEFAULT NULL,
                comentarios_docente TEXT DEFAULT NULL,
                comentarios_coordinador TEXT DEFAULT NULL,
                fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (estudiante_id) REFERENCES usuarios(id) ON DELETE CASCADE
            )",
            
            "CREATE TABLE IF NOT EXISTS asignaciones_docente (
                id INT AUTO_INCREMENT PRIMARY KEY,
                estudiante_id INT NOT NULL,
                docente_id INT NOT NULL,
                tipo ENUM('asesor', 'jurado') NOT NULL,
                fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (estudiante_id) REFERENCES usuarios(id) ON DELETE CASCADE,
                FOREIGN KEY (docente_id) REFERENCES usuarios(id) ON DELETE CASCADE
            )",
            
            "CREATE TABLE IF NOT EXISTS sustentaciones (
                id INT AUTO_INCREMENT PRIMARY KEY,
                estudiante_id INT NOT NULL,
                fecha_sustentacion DATETIME NOT NULL,
                lugar VARCHAR(255) NOT NULL,
                jurado1_id INT DEFAULT NULL,
                jurado2_id INT DEFAULT NULL,
                jurado3_id INT DEFAULT NULL,
                estado ENUM('programada', 'realizada', 'cancelada') DEFAULT 'programada',
                nota_final DECIMAL(3,1) DEFAULT NULL,
                fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (estudiante_id) REFERENCES usuarios(id) ON DELETE CASCADE,
                FOREIGN KEY (jurado1_id) REFERENCES usuarios(id) ON DELETE SET NULL,
                FOREIGN KEY (jurado2_id) REFERENCES usuarios(id) ON DELETE SET NULL,
                FOREIGN KEY (jurado3_id) REFERENCES usuarios(id) ON DELETE SET NULL
            )",
            
            "CREATE TABLE IF NOT EXISTS documentos_reglamento (
                id INT AUTO_INCREMENT PRIMARY KEY,
                titulo VARCHAR(255) NOT NULL,
                descripcion TEXT DEFAULT NULL,
                archivo_nombre VARCHAR(255) NOT NULL,
                archivo_ruta VARCHAR(255) NOT NULL,
                especialidad VARCHAR(255) NOT NULL,
                coordinador_id INT NOT NULL,
                fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (coordinador_id) REFERENCES usuarios(id) ON DELETE CASCADE
            )"
        ];
        
        foreach ($queries as $query) {
            try {
                $this->conn->exec($query);
            } catch (PDOException $e) {
                echo "Error creando tabla: " . $e->getMessage();
            }
        }
    }
    
    // Autenticación
    public function authenticate($email, $password) {
        $query = "SELECT * FROM usuarios WHERE email = :email AND estado = 'activo'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $user['password'])) {
                return $user;
            }
        }
        return false;
    }
    
    // Crear usuario
    public function create($data) {
        $query = "INSERT INTO usuarios (email, password, codigo, nombres, apellidos, tipo, especialidad, telefono, direccion, dni, semestre, ciclo_academico, categoria_docente, dedicacion, grado_academico) 
                 VALUES (:email, :password, :codigo, :nombres, :apellidos, :tipo, :especialidad, :telefono, :direccion, :dni, :semestre, :ciclo_academico, :categoria_docente, :dedicacion, :grado_academico)";
        
        $stmt = $this->conn->prepare($query);
        
        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $dni = $data['dni'] ?? null;
        $semestre = $data['semestre'] ?? null;
        $ciclo_academico = $data['ciclo_academico'] ?? null;
        $categoria_docente = $data['categoria_docente'] ?? null;
        $dedicacion = $data['dedicacion'] ?? null;
        $grado_academico = $data['grado_academico'] ?? null;
        
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':codigo', $data['codigo']);
        $stmt->bindParam(':nombres', $data['nombres']);
        $stmt->bindParam(':apellidos', $data['apellidos']);
        $stmt->bindParam(':tipo', $data['tipo']);
        $stmt->bindParam(':especialidad', $data['especialidad']);
        $stmt->bindParam(':telefono', $data['telefono']);
        $stmt->bindParam(':direccion', $data['direccion']);
        $stmt->bindParam(':dni', $dni);
        $stmt->bindParam(':semestre', $semestre);
        $stmt->bindParam(':ciclo_academico', $ciclo_academico);
        $stmt->bindParam(':categoria_docente', $categoria_docente);
        $stmt->bindParam(':dedicacion', $dedicacion);
        $stmt->bindParam(':grado_academico', $grado_academico);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }
    
    // Obtener usuario por ID
    public function getById($id) {
        $query = "SELECT * FROM usuarios WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Obtener usuario por email
    public function getByEmail($email) {
        $query = "SELECT * FROM usuarios WHERE email = :email AND estado = 'activo'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Obtener todos los usuarios
    public function getAll() {
        $query = "SELECT * FROM usuarios WHERE estado = 'activo' ORDER BY apellidos, nombres";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Obtener usuarios por tipo
    public function getByType($tipo, $especialidad = null) {
        if ($especialidad) {
            $query = "SELECT * FROM usuarios WHERE tipo = :tipo AND especialidad = :especialidad AND estado = 'activo' ORDER BY apellidos, nombres";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':tipo', $tipo);
            $stmt->bindParam(':especialidad', $especialidad);
        } else {
            $query = "SELECT * FROM usuarios WHERE tipo = :tipo AND estado = 'activo' ORDER BY apellidos, nombres";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':tipo', $tipo);
        }
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Obtener usuarios por especialidad
    public function getByEspecialidad($especialidad) {
        $query = "SELECT * FROM usuarios WHERE especialidad = :especialidad AND estado = 'activo' ORDER BY apellidos, nombres";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':especialidad', $especialidad);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Actualizar usuario
    public function update($id, $data) {
        $query = "UPDATE usuarios SET telefono = :telefono, direccion = :direccion, semestre = :semestre, ciclo_academico = :ciclo_academico, categoria_docente = :categoria_docente, dedicacion = :dedicacion, grado_academico = :grado_academico WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $semestre = $data['semestre'] ?? null;
        $ciclo_academico = $data['ciclo_academico'] ?? null;
        $categoria_docente = $data['categoria_docente'] ?? null;
        $dedicacion = $data['dedicacion'] ?? null;
        $grado_academico = $data['grado_academico'] ?? null;
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':telefono', $data['telefono']);
        $stmt->bindParam(':direccion', $data['direccion']);
        $stmt->bindParam(':semestre', $semestre);
        $stmt->bindParam(':ciclo_academico', $ciclo_academico);
        $stmt->bindParam(':categoria_docente', $categoria_docente);
        $stmt->bindParam(':dedicacion', $dedicacion);
        $stmt->bindParam(':grado_academico', $grado_academico);
        
        return $stmt->execute();
    }
    
    // Cambiar contraseña
    public function changePassword($id, $new_password) {
        $query = "UPDATE usuarios SET password = :password WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':password', $hashed_password);
        
        return $stmt->execute();
    }
    
    // Contar usuarios por tipo
    public function countByType($tipo) {
        $query = "SELECT COUNT(*) as total FROM usuarios WHERE tipo = :tipo AND estado = 'activo'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':tipo', $tipo);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
    
    // Activar usuario
    public function activateUser($id) {
        $query = "UPDATE usuarios SET estado = 'activo' WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
    
    // Desactivar usuario
    public function deactivateUser($id) {
        $query = "UPDATE usuarios SET activo = 0 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
    
    // Verificar si el email ya existe
    public function emailExists($email) {
        $query = "SELECT COUNT(*) as count FROM usuarios WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }
    
    // Verificar si el código ya existe
    public function codigoExists($codigo) {
        $query = "SELECT COUNT(*) as count FROM usuarios WHERE codigo = :codigo";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':codigo', $codigo);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }
    
    // Crear usuario desde Google OAuth
    public function createGoogleUser($data) {
        $query = "INSERT INTO usuarios (email, password, codigo, nombres, apellidos, tipo, especialidad, telefono, direccion, dni, semestre, ciclo_academico) 
                 VALUES (:email, :password, :codigo, :nombres, :apellidos, :tipo, :especialidad, :telefono, :direccion, :dni, :semestre, :ciclo_academico)";
        
        $stmt = $this->conn->prepare($query);
        
        // Para usuarios de Google, creamos una contraseña aleatoria (no se usará)
        $randomPassword = password_hash(uniqid() . time(), PASSWORD_DEFAULT);
        
        $dni = $data['dni'] ?? null;
        $semestre = $data['semestre'] ?? null;
        $ciclo_academico = $data['ciclo_academico'] ?? null;
        
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':password', $randomPassword);
        $stmt->bindParam(':codigo', $data['codigo']);
        $stmt->bindParam(':nombres', $data['nombres']);
        $stmt->bindParam(':apellidos', $data['apellidos']);
        $stmt->bindParam(':tipo', $data['tipo']);
        $stmt->bindParam(':especialidad', $data['especialidad']);
        $stmt->bindParam(':telefono', $data['telefono']);
        $stmt->bindParam(':direccion', $data['direccion']);
        $stmt->bindParam(':dni', $dni);
        $stmt->bindParam(':semestre', $semestre);
        $stmt->bindParam(':ciclo_academico', $ciclo_academico);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }
    
    // Obtener usuario por email (método alternativo)
    public function getUserByEmail($email) {
        $query = "SELECT * FROM usuarios WHERE email = :email AND estado = 'activo'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Obtener usuario por código
    public function getUserByCode($codigo) {
        $query = "SELECT * FROM usuarios WHERE codigo = :codigo AND estado = 'activo'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':codigo', $codigo);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>