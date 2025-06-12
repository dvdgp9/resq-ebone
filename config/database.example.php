<?php
// Configuración de base de datos para ResQ - TEMPLATE
// INSTRUCCIONES: 
// 1. Copia este archivo como 'database.php'
// 2. Actualiza las credenciales con los datos reales de tu servidor

class Database {
    private static $instance = null;
    private $connection;
    
    // CONFIGURACIÓN DE CONEXIÓN - ACTUALIZAR CON DATOS REALES
    private $host = 'localhost';                    // Servidor de BD
    private $database = 'resq_app';                 // Nombre de la base de datos
    private $username = 'tu_usuario_bd';            // Usuario de BD
    private $password = 'tu_password_bd';           // Contraseña de BD
    private $charset = 'utf8mb4';
    
    private function __construct() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->database};charset={$this->charset}";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            error_log("Error de conexión a BD: " . $e->getMessage());
            die("Error de conexión a la base de datos. Contacte al administrador.");
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    // Método para ejecutar el script de creación de BD
    public function createTables() {
        $sql = file_get_contents(__DIR__ . '/../database/create_tables.sql');
        
        if ($sql === false) {
            throw new Exception("No se pudo leer el archivo de creación de tablas");
        }
        
        // Ejecutar cada statement por separado
        $statements = explode(';', $sql);
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement)) {
                $this->connection->exec($statement);
            }
        }
        
        return true;
    }
}

// Función helper para obtener conexión
function getDB() {
    return Database::getInstance()->getConnection();
}
?> 