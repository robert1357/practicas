<?php
class Database {
    private $host = "www.createxpro.net.pe";
    private $db_name = "bpuma_userMysql11";
    private $username = "bpuma_userMysql11";
    private $password = "4*=K45&KiioEi23";
    private $charset = "utf8mb4";
    private $conn;

    public function connect() {
        $this->conn = null;

        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo "Error de conexión: " . $e->getMessage();
        }

        return $this->conn;
    }
}
?>