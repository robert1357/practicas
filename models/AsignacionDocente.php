<?php
require_once __DIR__ . '/../config/database.php';

class AsignacionDocente {
    private $conn;
    private $table = 'asignaciones_docente';
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }
    
    // Asignar docente a estudiante
    public function asignar($estudiante_id, $docente_id, $tipo) {
        // Verificar si ya existe asignación para este estudiante
        $query_check = "SELECT id FROM {$this->table} WHERE estudiante_id = :estudiante_id";
        $stmt_check = $this->conn->prepare($query_check);
        $stmt_check->bindParam(':estudiante_id', $estudiante_id);
        $stmt_check->execute();
        
        if ($stmt_check->rowCount() > 0) {
            // Actualizar asignación existente
            if ($tipo == 'asesor') {
                $query = "UPDATE {$this->table} 
                         SET docente_asesor_id = :docente_id, fecha_asignacion = CURRENT_TIMESTAMP
                         WHERE estudiante_id = :estudiante_id";
            } else if ($tipo == 'jurado') {
                $query = "UPDATE {$this->table} 
                         SET docente_jurado_id = :docente_id
                         WHERE estudiante_id = :estudiante_id";
            }
        } else {
            // Crear nueva asignación
            if ($tipo == 'asesor') {
                $query = "INSERT INTO {$this->table} (estudiante_id, docente_asesor_id) 
                         VALUES (:estudiante_id, :docente_id)";
            } else if ($tipo == 'jurado') {
                $query = "INSERT INTO {$this->table} (estudiante_id, docente_jurado_id) 
                         VALUES (:estudiante_id, :docente_id)";
            }
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':estudiante_id', $estudiante_id);
        $stmt->bindParam(':docente_id', $docente_id);
        
        return $stmt->execute();
    }
    
    // Obtener estudiantes asignados a un docente
    public function getEstudiantesByDocente($docente_id, $tipo = null) {
        if ($tipo == 'asesor') {
            $query = "SELECT a.*, u.nombres, u.apellidos, u.codigo, u.especialidad, u.email
                     FROM {$this->table} a
                     JOIN usuarios u ON a.estudiante_id = u.id
                     WHERE a.docente_asesor_id = :docente_id";
        } else if ($tipo == 'jurado') {
            $query = "SELECT a.*, u.nombres, u.apellidos, u.codigo, u.especialidad, u.email
                     FROM {$this->table} a
                     JOIN usuarios u ON a.estudiante_id = u.id
                     WHERE a.docente_jurado_id = :docente_id";
        } else {
            $query = "SELECT a.*, u.nombres, u.apellidos, u.codigo, u.especialidad, u.email
                     FROM {$this->table} a
                     JOIN usuarios u ON a.estudiante_id = u.id
                     WHERE (a.docente_asesor_id = :docente_id OR a.docente_jurado_id = :docente_id)";
        }
        
        $query .= " ORDER BY u.apellidos, u.nombres";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':docente_id', $docente_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Obtener docente asesor de un estudiante
    public function getDocenteAsesor($estudiante_id) {
        $query = "SELECT a.*, u.nombres, u.apellidos, u.codigo, u.email
                 FROM {$this->table} a
                 JOIN usuarios u ON a.docente_asesor_id = u.id
                 WHERE a.estudiante_id = :estudiante_id AND a.docente_asesor_id IS NOT NULL";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':estudiante_id', $estudiante_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Obtener estudiantes por especialidad sin asesor
    public function getEstudiantesSinAsesor($especialidad) {
        $query = "SELECT u.* FROM usuarios u
                 LEFT JOIN {$this->table} a ON u.id = a.estudiante_id
                 WHERE u.tipo = 'estudiante' AND u.especialidad = :especialidad 
                 AND a.docente_asesor_id IS NULL
                 ORDER BY u.apellidos, u.nombres";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':especialidad', $especialidad);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Obtener todos los estudiantes de una especialidad con sus asesores
    public function getEstudiantesConAsesor($especialidad) {
        $query = "SELECT u.*, 
                         da.nombres as docente_nombres, 
                         da.apellidos as docente_apellidos,
                         da.email as docente_email
                 FROM usuarios u
                 LEFT JOIN {$this->table} a ON u.id = a.estudiante_id
                 LEFT JOIN usuarios da ON a.docente_asesor_id = da.id
                 WHERE u.tipo = 'estudiante' AND u.especialidad = :especialidad
                 ORDER BY u.apellidos, u.nombres";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':especialidad', $especialidad);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>