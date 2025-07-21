<?php
require_once __DIR__ . '/../config/database.php';

class DocumentoReglamento {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }
    
    // Crear nuevo documento
    public function create($data) {
        $query = "INSERT INTO documentos_reglamento (titulo, descripcion, tipo_documento, archivo_url, especialidad, creado_por) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute([
            $data['titulo'],
            $data['descripcion'],
            $data['tipo_documento'],
            $data['archivo_url'],
            $data['especialidad'],
            $data['creado_por']
        ]);
    }
    
    // Obtener todos los documentos
    public function getAll() {
        $query = "SELECT d.*, u.nombres, u.apellidos 
                 FROM documentos_reglamento d 
                 LEFT JOIN usuarios u ON d.creado_por = u.id 
                 WHERE d.activo = 1 
                 ORDER BY d.fecha_creacion DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Obtener documentos por especialidad
    public function getByEspecialidad($especialidad) {
        $query = "SELECT d.*, u.nombres, u.apellidos 
                 FROM documentos_reglamento d 
                 LEFT JOIN usuarios u ON d.creado_por = u.id 
                 WHERE d.especialidad = ? AND d.activo = 1 
                 ORDER BY d.fecha_creacion DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$especialidad]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Obtener documento por ID
    public function findById($id) {
        $query = "SELECT d.*, u.nombres, u.apellidos 
                 FROM documentos_reglamento d 
                 LEFT JOIN usuarios u ON d.creado_por = u.id 
                 WHERE d.id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Obtener documentos por tipo
    public function getByType($tipo) {
        $query = "SELECT d.*, u.nombres, u.apellidos 
                 FROM documentos_reglamento d 
                 LEFT JOIN usuarios u ON d.creado_por = u.id 
                 WHERE d.tipo_documento = ? AND d.activo = 1 
                 ORDER BY d.fecha_creacion DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$tipo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Actualizar documento
    public function update($id, $data) {
        $query = "UPDATE documentos_reglamento 
                 SET titulo = ?, descripcion = ?, tipo_documento = ?, archivo_url = ?, especialidad = ? 
                 WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute([
            $data['titulo'],
            $data['descripcion'],
            $data['tipo_documento'],
            $data['archivo_url'],
            $data['especialidad'],
            $id
        ]);
    }
    
    // Eliminar documento (soft delete)
    public function delete($id) {
        $query = "UPDATE documentos_reglamento SET activo = 0 WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }
    
    // Eliminar documento permanentemente
    public function deletePermanently($id) {
        $query = "DELETE FROM documentos_reglamento WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }
}
?>