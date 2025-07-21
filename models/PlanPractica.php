<?php
require_once __DIR__ . '/../config/database.php';

class PlanPractica {
    private $conn;
    private $table = 'planes_practica';
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }
    
    // Crear plan de práctica
    public function create($data) {
        $query = "INSERT INTO {$this->table} 
                 (estudiante_id, nombres, apellidos, codigo, especialidad, email, telefono,
                  empresa, ruc, direccion_empresa, telefono_empresa, supervisor, cargo_supervisor,
                  fecha_inicio, fecha_fin, horario, total_horas, actividades, objetivos, estado,
                  archivo_plan, archivo_documento1, archivo_documento2, archivo_documento3) 
                 VALUES 
                 (:estudiante_id, :nombres, :apellidos, :codigo, :especialidad, :email, :telefono,
                  :empresa, :ruc, :direccion_empresa, :telefono_empresa, :supervisor, :cargo_supervisor,
                  :fecha_inicio, :fecha_fin, :horario, :total_horas, :actividades, :objetivos, :estado,
                  :archivo_plan, :archivo_documento1, :archivo_documento2, :archivo_documento3)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':estudiante_id', $data['estudiante_id']);
        $stmt->bindParam(':nombres', $data['nombres']);
        $stmt->bindParam(':apellidos', $data['apellidos']);
        $stmt->bindParam(':codigo', $data['codigo']);
        $stmt->bindParam(':especialidad', $data['especialidad']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':telefono', $data['telefono']);
        $stmt->bindParam(':empresa', $data['empresa']);
        $stmt->bindParam(':ruc', $data['ruc']);
        $stmt->bindParam(':direccion_empresa', $data['direccion_empresa']);
        $stmt->bindParam(':telefono_empresa', $data['telefono_empresa']);
        $stmt->bindParam(':supervisor', $data['supervisor']);
        $stmt->bindParam(':cargo_supervisor', $data['cargo_supervisor']);
        $stmt->bindParam(':fecha_inicio', $data['fecha_inicio']);
        $stmt->bindParam(':fecha_fin', $data['fecha_fin']);
        $stmt->bindParam(':horario', $data['horario']);
        $stmt->bindParam(':total_horas', $data['total_horas']);
        $stmt->bindParam(':actividades', $data['actividades']);
        $stmt->bindParam(':objetivos', $data['objetivos']);
        $stmt->bindParam(':estado', $data['estado']);
        $archivo_plan = $data['archivo_plan'] ?? null;
        $archivo_documento1 = $data['archivo_documento1'] ?? null;
        $archivo_documento2 = $data['archivo_documento2'] ?? null;
        $archivo_documento3 = $data['archivo_documento3'] ?? null;
        
        $stmt->bindParam(':archivo_plan', $archivo_plan);
        $stmt->bindParam(':archivo_documento1', $archivo_documento1);
        $stmt->bindParam(':archivo_documento2', $archivo_documento2);
        $stmt->bindParam(':archivo_documento3', $archivo_documento3);
        
        return $stmt->execute();
    }
    
    // Obtener plan por estudiante
    public function getByEstudiante($estudiante_id) {
        $query = "SELECT p.*, u.nombres, u.apellidos, u.codigo, u.especialidad 
                 FROM {$this->table} p
                 JOIN usuarios u ON p.estudiante_id = u.id
                 WHERE p.estudiante_id = :estudiante_id
                 ORDER BY p.fecha_creacion DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':estudiante_id', $estudiante_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Obtener planes por especialidad
    public function getByEspecialidad($especialidad) {
        $query = "SELECT p.*, u.nombres, u.apellidos, u.codigo, u.especialidad 
                 FROM {$this->table} p
                 JOIN usuarios u ON p.estudiante_id = u.id
                 WHERE u.especialidad = :especialidad
                 ORDER BY p.fecha_creacion DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':especialidad', $especialidad);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Obtener planes pendientes para docente
    public function getPendientesDocente() {
        $query = "SELECT p.*, u.nombres, u.apellidos, u.codigo, u.especialidad 
                 FROM {$this->table} p
                 JOIN usuarios u ON p.estudiante_id = u.id
                 WHERE p.estado = 'pendiente'
                 ORDER BY p.fecha_creacion DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Obtener planes pendientes para coordinador (aprobados por docente)
    public function getPendientesCoordinador($especialidad = null) {
        $query = "SELECT p.*, u.nombres, u.apellidos, u.codigo, u.especialidad 
                 FROM {$this->table} p
                 JOIN usuarios u ON p.estudiante_id = u.id
                 WHERE p.estado = 'aprobado_docente'";
        
        if ($especialidad) {
            $query .= " AND u.especialidad = :especialidad";
        }
        
        $query .= " ORDER BY p.fecha_creacion DESC";
        
        $stmt = $this->conn->prepare($query);
        if ($especialidad) {
            $stmt->bindParam(':especialidad', $especialidad);
        }
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Calificar plan (docente)
    public function calificarDocente($id, $nota, $comentarios) {
        $query = "UPDATE {$this->table} 
                 SET nota_docente = :nota, comentarios_docente = :comentarios,
                     fecha_actualizacion = CURRENT_TIMESTAMP
                 WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nota', $nota);
        $stmt->bindParam(':comentarios', $comentarios);
        
        return $stmt->execute();
    }
    
    // Aprobar/Rechazar plan (coordinador)
    public function aprobarPlan($id, $estado, $comentarios) {
        // Verificar que el plan esté aprobado por docente antes de que coordinador pueda aprobar
        $plan = $this->getById($id);
        if (!$plan) {
            return false;
        }
        
        // Si coordinador quiere aprobar, verificar que docente ya aprobó
        if ($estado == 'aprobado' && $plan['estado'] != 'aprobado_docente') {
            return ['error' => 'El plan debe ser aprobado primero por el docente asesor'];
        }
        
        // Si coordinador aprueba, cambiar a aprobado_final
        $estado_final = ($estado == 'aprobado') ? 'aprobado_final' : $estado;
        
        // Si hay comentarios, usar observaciones_docente como campo temporal para coordinador
        if ($comentarios) {
            $query = "UPDATE {$this->table} 
                     SET estado = :estado, observaciones_docente = CONCAT(IFNULL(observaciones_docente, ''), '\n[COORDINADOR]: ', :comentarios),
                         fecha_revision = CURRENT_TIMESTAMP
                     WHERE id = :id";
        } else {
            $query = "UPDATE {$this->table} 
                     SET estado = :estado, fecha_revision = CURRENT_TIMESTAMP
                     WHERE id = :id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':estado', $estado_final);
        
        if ($comentarios) {
            $stmt->bindParam(':comentarios', $comentarios);
        }
        
        return $stmt->execute();
    }
    
    // Obtener plan por ID
    public function getById($id) {
        $query = "SELECT p.*, u.nombres, u.apellidos, u.codigo, u.especialidad 
                 FROM {$this->table} p
                 JOIN usuarios u ON p.estudiante_id = u.id
                 WHERE p.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Obtener planes por docente asignado
    public function getByDocente($docente_id) {
        $query = "SELECT p.*, u.nombres, u.apellidos, u.codigo, u.especialidad 
                 FROM {$this->table} p
                 JOIN usuarios u ON p.estudiante_id = u.id
                 JOIN asignaciones_docente ad ON p.estudiante_id = ad.estudiante_id
                 WHERE ad.docente_asesor_id = :docente_id
                 ORDER BY p.fecha_creacion DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':docente_id', $docente_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Contar todos los planes
    public function countAll() {
        $query = "SELECT COUNT(*) as total FROM {$this->table}";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
    
    // Contar planes por estado
    public function countByStatus($estado) {
        $query = "SELECT COUNT(*) as total FROM {$this->table} WHERE estado = :estado";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':estado', $estado);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
    
    // Aprobar/Rechazar plan
    public function aprobar($id, $estado, $comentarios) {
        $query = "UPDATE {$this->table} 
                 SET estado = :estado, comentarios_docente = :comentarios, fecha_revision = NOW()
                 WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':comentarios', $comentarios);
        
        return $stmt->execute();
    }
    
    // Actualizar plan
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
    
    // Actualizar estado del plan (usado en aprobar_plan.php)
    public function updateStatus($id, $data) {
        // Para docentes: cambiar a aprobado_docente o rechazado
        $estado_final = $data['estado'];
        if ($data['estado'] == 'aprobado') {
            $estado_final = 'aprobado_docente';
        }
        
        $query = "UPDATE {$this->table} 
                 SET estado = :estado, 
                     calificacion_docente = :calificacion_docente,
                     comentarios_docente = :comentarios_docente,
                     observaciones_docente = :observaciones_docente,
                     fecha_revision = :fecha_revision
                 WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':estado', $estado_final);
        $stmt->bindParam(':calificacion_docente', $data['calificacion_docente']);
        $stmt->bindParam(':comentarios_docente', $data['comentarios_docente']);
        $stmt->bindParam(':observaciones_docente', $data['observaciones_docente']);
        $stmt->bindParam(':fecha_revision', $data['fecha_revision']);
        
        return $stmt->execute();
    }
}
?>