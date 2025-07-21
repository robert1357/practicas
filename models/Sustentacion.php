<?php
require_once __DIR__ . '/../config/database.php';

class Sustentacion {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }
    
    // Crear nueva sustentación
    public function create($data) {
        $query = "INSERT INTO sustentaciones (estudiante_id, fecha_sustentacion, hora_sustentacion, lugar, modalidad, presidente_jurado, vocal_jurado, secretario_jurado, observaciones) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute([
            $data['estudiante_id'],
            $data['fecha_sustentacion'],
            $data['hora_sustentacion'],
            $data['lugar'],
            $data['modalidad'],
            $data['presidente_jurado'],
            $data['vocal_jurado'],
            $data['secretario_jurado'],
            $data['observaciones']
        ]);
    }
    
    // Obtener todas las sustentaciones
    public function getAll() {
        $query = "SELECT s.*, 
                         e.nombres as estudiante_nombres, e.apellidos as estudiante_apellidos, e.codigo as estudiante_codigo,
                         p.nombres as presidente_nombres, p.apellidos as presidente_apellidos,
                         v.nombres as vocal_nombres, v.apellidos as vocal_apellidos,
                         sec.nombres as secretario_nombres, sec.apellidos as secretario_apellidos
                  FROM sustentaciones s
                  JOIN usuarios e ON s.estudiante_id = e.id
                  JOIN usuarios p ON s.presidente_jurado = p.id
                  JOIN usuarios v ON s.vocal_jurado = v.id
                  JOIN usuarios sec ON s.secretario_jurado = sec.id
                  ORDER BY s.fecha_sustentacion DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Obtener sustentaciones por estudiante
    public function getByEstudiante($estudiante_id) {
        $query = "SELECT s.*, 
                         CONCAT(p.nombres, ' ', p.apellidos) as presidente_nombre,
                         CONCAT(v.nombres, ' ', v.apellidos) as vocal_nombre,
                         CONCAT(sec.nombres, ' ', sec.apellidos) as secretario_nombre
                  FROM sustentaciones s
                  LEFT JOIN usuarios p ON s.presidente_jurado = p.id
                  LEFT JOIN usuarios v ON s.vocal_jurado = v.id
                  LEFT JOIN usuarios sec ON s.secretario_jurado = sec.id
                  WHERE s.estudiante_id = ?
                  ORDER BY s.fecha_sustentacion DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$estudiante_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC); // Solo la primera sustentación
    }
    
    // Obtener sustentaciones por docente (como jurado)
    public function getByDocente($docente_id) {
        $query = "SELECT s.*, 
                         e.nombres as estudiante_nombres, e.apellidos as estudiante_apellidos, e.codigo as estudiante_codigo,
                         p.nombres as presidente_nombres, p.apellidos as presidente_apellidos,
                         v.nombres as vocal_nombres, v.apellidos as vocal_apellidos,
                         sec.nombres as secretario_nombres, sec.apellidos as secretario_apellidos
                  FROM sustentaciones s
                  JOIN usuarios e ON s.estudiante_id = e.id
                  JOIN usuarios p ON s.presidente_jurado = p.id
                  JOIN usuarios v ON s.vocal_jurado = v.id
                  JOIN usuarios sec ON s.secretario_jurado = sec.id
                  WHERE s.presidente_jurado = ? OR s.vocal_jurado = ? OR s.secretario_jurado = ?
                  ORDER BY s.fecha_sustentacion DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$docente_id, $docente_id, $docente_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Obtener sustentación por ID
    public function findById($id) {
        $query = "SELECT s.*, 
                         e.nombres as estudiante_nombres, e.apellidos as estudiante_apellidos, e.codigo as estudiante_codigo,
                         p.nombres as presidente_nombres, p.apellidos as presidente_apellidos,
                         v.nombres as vocal_nombres, v.apellidos as vocal_apellidos,
                         sec.nombres as secretario_nombres, sec.apellidos as secretario_apellidos
                  FROM sustentaciones s
                  JOIN usuarios e ON s.estudiante_id = e.id
                  JOIN usuarios p ON s.presidente_jurado = p.id
                  JOIN usuarios v ON s.vocal_jurado = v.id
                  JOIN usuarios sec ON s.secretario_jurado = sec.id
                  WHERE s.id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Actualizar sustentación
    public function update($id, $data) {
        $query = "UPDATE sustentaciones 
                 SET fecha_sustentacion = ?, hora_sustentacion = ?, lugar = ?, modalidad = ?, 
                     presidente_jurado = ?, vocal_jurado = ?, secretario_jurado = ?, observaciones = ?
                 WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute([
            $data['fecha_sustentacion'],
            $data['hora_sustentacion'],
            $data['lugar'],
            $data['modalidad'],
            $data['presidente_jurado'],
            $data['vocal_jurado'],
            $data['secretario_jurado'],
            $data['observaciones'],
            $id
        ]);
    }
    
    // Actualizar estado de sustentación
    public function updateEstado($id, $estado, $calificacion = null, $acta = null) {
        if ($calificacion && $acta) {
            $query = "UPDATE sustentaciones SET estado = ?, calificacion_final = ?, acta_sustentacion = ? WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([$estado, $calificacion, $acta, $id]);
        } else {
            $query = "UPDATE sustentaciones SET estado = ? WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([$estado, $id]);
        }
    }
    
    // Aprobar sustentación por presidente del jurado
    public function aprobarSustentacion($id, $presidente_id, $calificacion, $observaciones) {
        $query = "UPDATE sustentaciones 
                 SET estado = 'aprobado', 
                     calificacion_final = ?, 
                     observaciones_aprobacion = ?, 
                     fecha_aprobacion = NOW(),
                     aprobado_por = ?
                 WHERE id = ? AND presidente_jurado = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$calificacion, $observaciones, $presidente_id, $id, $presidente_id]);
    }
    
    // Rechazar sustentación por presidente del jurado
    public function rechazarSustentacion($id, $presidente_id, $observaciones) {
        $query = "UPDATE sustentaciones 
                 SET estado = 'rechazado', 
                     observaciones_aprobacion = ?, 
                     fecha_aprobacion = CURRENT_TIMESTAMP,
                     aprobado_por = ?
                 WHERE id = ? AND presidente_jurado = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$observaciones, $presidente_id, $id, $presidente_id]);
    }
    
    // Obtener sustentaciones donde soy presidente del jurado
    public function getByPresidente($presidente_id) {
        $query = "SELECT s.*, 
                         e.nombres as estudiante_nombres, e.apellidos as estudiante_apellidos, e.codigo as estudiante_codigo,
                         v.nombres as vocal_nombres, v.apellidos as vocal_apellidos,
                         sec.nombres as secretario_nombres, sec.apellidos as secretario_apellidos
                  FROM sustentaciones s
                  JOIN usuarios e ON s.estudiante_id = e.id
                  JOIN usuarios v ON s.vocal_jurado = v.id
                  JOIN usuarios sec ON s.secretario_jurado = sec.id
                  WHERE s.presidente_jurado = ?
                  ORDER BY s.fecha_sustentacion DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$presidente_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Obtener sustentaciones donde soy vocal del jurado
    public function getByVocal($vocal_id) {
        $query = "SELECT s.*, 
                         e.nombres as estudiante_nombres, e.apellidos as estudiante_apellidos, e.codigo as estudiante_codigo,
                         p.nombres as presidente_nombres, p.apellidos as presidente_apellidos,
                         sec.nombres as secretario_nombres, sec.apellidos as secretario_apellidos
                  FROM sustentaciones s
                  JOIN usuarios e ON s.estudiante_id = e.id
                  JOIN usuarios p ON s.presidente_jurado = p.id
                  JOIN usuarios sec ON s.secretario_jurado = sec.id
                  WHERE s.vocal_jurado = ?
                  ORDER BY s.fecha_sustentacion DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$vocal_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Obtener sustentaciones donde soy secretario del jurado
    public function getBySecretario($secretario_id) {
        $query = "SELECT s.*, 
                         e.nombres as estudiante_nombres, e.apellidos as estudiante_apellidos, e.codigo as estudiante_codigo,
                         p.nombres as presidente_nombres, p.apellidos as presidente_apellidos,
                         v.nombres as vocal_nombres, v.apellidos as vocal_apellidos
                  FROM sustentaciones s
                  JOIN usuarios e ON s.estudiante_id = e.id
                  JOIN usuarios p ON s.presidente_jurado = p.id
                  JOIN usuarios v ON s.vocal_jurado = v.id
                  WHERE s.secretario_jurado = ?
                  ORDER BY s.fecha_sustentacion DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$secretario_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
