<?php
require_once __DIR__ . '/../config/database.php';

class ReporteSemanal {
    private $conn;
    private $table = 'reportes_semanales';
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }
    
    // Crear reporte semanal
    public function create($data) {
        $query = "INSERT INTO {$this->table} 
                 (estudiante_id, fecha_inicio, fecha_fin, total_horas, 
                  asesor_empresarial, area_trabajo, actividades, aprendizajes, 
                  dificultades, archivos_adjuntos, estado) 
                 VALUES 
                 (:estudiante_id, :fecha_inicio, :fecha_fin, :total_horas,
                  :asesor_empresarial, :area_trabajo, :actividades, :aprendizajes,
                  :dificultades, :archivos_adjuntos, :estado)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':estudiante_id', $data['estudiante_id']);
        $stmt->bindParam(':fecha_inicio', $data['fecha_inicio']);
        $stmt->bindParam(':fecha_fin', $data['fecha_fin']);
        $stmt->bindParam(':total_horas', $data['total_horas']);
        $stmt->bindParam(':asesor_empresarial', $data['asesor_empresarial']);
        $stmt->bindParam(':area_trabajo', $data['area_trabajo']);
        $stmt->bindParam(':actividades', $data['actividades']);
        $stmt->bindParam(':aprendizajes', $data['aprendizajes']);
        $stmt->bindParam(':dificultades', $data['dificultades']);
        $archivos_adjuntos = isset($data['archivos_adjuntos']) ? json_encode([$data['archivos_adjuntos']]) : null;
        $stmt->bindParam(':archivos_adjuntos', $archivos_adjuntos);
        $stmt->bindParam(':estado', $data['estado']);
        
        return $stmt->execute();
    }
    
    // Obtener reportes por estudiante
    public function getByEstudiante($estudiante_id) {
        $query = "SELECT r.*, u.nombres, u.apellidos, u.codigo 
                 FROM {$this->table} r
                 JOIN usuarios u ON r.estudiante_id = u.id
                 WHERE r.estudiante_id = :estudiante_id
                 ORDER BY r.fecha_creacion DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':estudiante_id', $estudiante_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Obtener reportes por docente
    public function getByDocente($docente_id) {
        $query = "SELECT r.*, u.nombres, u.apellidos, u.codigo, u.especialidad 
                 FROM {$this->table} r
                 JOIN usuarios u ON r.estudiante_id = u.id
                 JOIN asignaciones_docente ad ON r.estudiante_id = ad.estudiante_id
                 WHERE ad.docente_asesor_id = :docente_id
                 ORDER BY r.fecha_creacion DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':docente_id', $docente_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Encontrar reporte por ID
    public function findById($id) {
        $query = "SELECT r.*, u.nombres, u.apellidos, u.codigo, u.especialidad 
                 FROM {$this->table} r
                 JOIN usuarios u ON r.estudiante_id = u.id
                 WHERE r.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Actualizar calificación del reporte
    public function updateCalificacion($id, $data) {
        $query = "UPDATE {$this->table} 
                 SET calificacion_docente = :calificacion_docente,
                     comentarios_docente = :comentarios_docente,
                     observaciones_docente = :observaciones_docente,
                     estado = :estado,
                     fecha_calificacion = :fecha_calificacion
                 WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':calificacion_docente', $data['calificacion_docente']);
        $stmt->bindParam(':comentarios_docente', $data['comentarios_docente']);
        $stmt->bindParam(':observaciones_docente', $data['observaciones_docente']);
        $stmt->bindParam(':estado', $data['estado']);
        $stmt->bindParam(':fecha_calificacion', $data['fecha_calificacion']);
        
        return $stmt->execute();
    }
    
    // Calificar reporte
    public function calificar($id, $nota, $comentarios) {
        $query = "UPDATE {$this->table} 
                 SET calificacion_docente = :nota, comentarios_docente = :comentarios,
                     estado = 'calificado', fecha_calificacion = CURRENT_TIMESTAMP
                 WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nota', $nota);
        $stmt->bindParam(':comentarios', $comentarios);
        
        return $stmt->execute();
    }
    
    // Obtener reporte por ID
    public function getById($id) {
        $query = "SELECT r.*, u.nombres, u.apellidos, u.codigo, u.especialidad 
                 FROM {$this->table} r
                 JOIN usuarios u ON r.estudiante_id = u.id
                 WHERE r.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Actualizar reporte
    public function update($id, $data) {
        $fields = [];
        $params = [':id' => $id];
        
        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
            $params[":$key"] = $value;
        }
        
        $query = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute($params);
    }
}
?>