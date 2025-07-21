<?php
require_once __DIR__ . '/../config/database.php';

class InformeFinal {
    private $conn;
    private $table = 'informes_finales';
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }
    
    // Crear informe final
    public function create($data) {
        $query = "INSERT INTO {$this->table} 
                 (estudiante_id, titulo, resumen_ejecutivo, introduccion, 
                  objetivos, metodologia, resultados, conclusiones, 
                  recomendaciones, anexos, archivo_informe, bibliografia, estado) 
                 VALUES 
                 (:estudiante_id, :titulo, :resumen_ejecutivo, :introduccion,
                  :objetivos, :metodologia, :resultados, :conclusiones,
                  :recomendaciones, :anexos, :archivo_informe, :bibliografia, :estado)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':estudiante_id', $data['estudiante_id']);
        $stmt->bindParam(':titulo', $data['titulo']);
        $stmt->bindParam(':resumen_ejecutivo', $data['resumen_ejecutivo']);
        $stmt->bindParam(':introduccion', $data['introduccion']);
        $stmt->bindParam(':objetivos', $data['objetivos']);
        $stmt->bindParam(':metodologia', $data['metodologia']);
        $stmt->bindParam(':resultados', $data['resultados']);
        $stmt->bindParam(':conclusiones', $data['conclusiones']);
        $stmt->bindParam(':recomendaciones', $data['recomendaciones']);
        $stmt->bindParam(':anexos', $data['anexos']);
        $stmt->bindParam(':archivo_informe', $data['archivo_informe']);
        $stmt->bindParam(':bibliografia', $data['bibliografia']);
        $stmt->bindParam(':estado', $data['estado']);
        
        return $stmt->execute();
    }
    
    // Obtener informe por estudiante
    public function getByEstudiante($estudiante_id) {
        $query = "SELECT i.*, u.nombres, u.apellidos, u.codigo, u.especialidad 
                 FROM {$this->table} i
                 JOIN usuarios u ON i.estudiante_id = u.id
                 WHERE i.estudiante_id = :estudiante_id
                 ORDER BY i.fecha_creacion DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':estudiante_id', $estudiante_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Obtener informes por docente
    public function getByDocente($docente_id) {
        $query = "SELECT i.*, u.nombres, u.apellidos, u.codigo, u.especialidad 
                 FROM {$this->table} i
                 JOIN usuarios u ON i.estudiante_id = u.id
                 JOIN asignaciones_docente ad ON i.estudiante_id = ad.estudiante_id
                 WHERE ad.docente_asesor_id = :docente_id
                 ORDER BY i.fecha_creacion DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':docente_id', $docente_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Calificar informe (docente)
    public function calificarDocente($id, $nota, $comentarios, $estado = 'aprobado') {
        $query = "UPDATE {$this->table} 
                 SET calificacion_docente = :nota, comentarios_docente = :comentarios,
                     estado = :estado, fecha_revision = CURRENT_TIMESTAMP
                 WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nota', $nota);
        $stmt->bindParam(':comentarios', $comentarios);
        $stmt->bindParam(':estado', $estado);
        
        return $stmt->execute();
    }
    
    // Aprobar/Rechazar informe (coordinador)
    public function aprobarInforme($id, $estado, $comentarios) {
        $query = "UPDATE {$this->table} 
                 SET estado = :estado, comentarios_coordinador = :comentarios,
                     fecha_revision = CURRENT_TIMESTAMP
                 WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':comentarios', $comentarios);
        
        return $stmt->execute();
    }
    
    // Aprobar por coordinador con documento firmado
    public function aprobarPorCoordinador($id, $comentarios, $documento_firmado = null) {
        $query = "UPDATE {$this->table} 
                 SET estado = :estado, comentarios_coordinador = :comentarios,
                     documento_firmado = :documento_firmado,
                     fecha_revision = CURRENT_TIMESTAMP
                 WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindValue(':estado', 'aprobado_final');
        $stmt->bindParam(':comentarios', $comentarios);
        $stmt->bindParam(':documento_firmado', $documento_firmado);
        
        return $stmt->execute();
    }
    
    // Rechazar por coordinador
    public function rechazarPorCoordinador($id, $comentarios) {
        return $this->aprobarInforme($id, 'rechazado', $comentarios);
    }
    
    // Obtener informes pendientes para coordinador
    public function getPendientesCoordinador($especialidad) {
        $query = "SELECT i.*, u.nombres, u.apellidos, u.codigo, u.especialidad 
                 FROM {$this->table} i
                 JOIN usuarios u ON i.estudiante_id = u.id
                 WHERE u.especialidad = :especialidad AND i.estado = 'aprobado'
                 ORDER BY i.fecha_revision DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':especialidad', $especialidad);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Obtener informe por ID
    public function getById($id) {
        $query = "SELECT i.*, u.nombres, u.apellidos, u.codigo, u.especialidad 
                 FROM {$this->table} i
                 JOIN usuarios u ON i.estudiante_id = u.id
                 WHERE i.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Obtener informes por especialidad
    public function getByEspecialidad($especialidad) {
        $query = "SELECT i.*, u.nombres, u.apellidos, u.codigo, u.especialidad 
                 FROM {$this->table} i
                 JOIN usuarios u ON i.estudiante_id = u.id
                 WHERE u.especialidad = :especialidad
                 ORDER BY i.fecha_creacion DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':especialidad', $especialidad);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Actualizar informe
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