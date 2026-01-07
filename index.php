<?php
/**
 * APLICACIÓN DE NOTAS - CONEXIÓN EC2 A RDS
 */

// --- 1. CARGA DE CONFIGURACIÓN SEGURA ---
function loadEnv($path) {
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue; // Ignorar comentarios
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            putenv(trim($name) . "=" . trim($value));
        }
    }
}

// Intentamos cargar el archivo .env
loadEnv(__DIR__ . '/.env');

$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');
$db   = getenv('DB_NAME');

// --- 2. CONEXIÓN Y SETUP ---
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("<div style='color:red; border:1px solid red; padding:10px;'>
            <b>Error de conexión:</b> " . $conn->connect_error . "
         </div>");
}

// Crear tabla si no existe 
$setup_sql = "CREATE TABLE IF NOT EXISTS notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($setup_sql);

// --- 3. LÓGICA DE INSERCIÓN ---
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $content = $conn->real_escape_string($_POST['content']);

    $sql = "INSERT INTO notes (title, content) VALUES ('$title', '$content')";
    
    if ($conn->query($sql) === TRUE) {
        $message = "<p class='success'>✅ Registro guardado correctamente en RDS.</p>";
    } else {
        $message = "<p class='error'>❌ Error: " . $conn->error . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor de Activos - AWS</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<h1>📝 Sistema de Notas</h1>
   
    <div class="container">
        <div>
            <h2>Agregar Nota</h2>
            <form method="POST">
                <input type="text" name="title" placeholder="Título" required>
                <textarea name="content" rows="5" placeholder="Contenido" required></textarea>
                <button type="submit">Guardar</button>
            </form>
        </div>
        
        <div>
            <h2>Notas Guardadas</h2>
            <?php if ($notes_result->num_rows > 0): ?>
                <?php while($note = $notes_result->fetch_assoc()): ?>
                    <div class="note">
                        <h3><?php echo htmlspecialchars($note['title']); ?></h3>
                        <p><?php echo nl2br(htmlspecialchars($note['content'])); ?></p>
                        <small><?php echo $note['created_at']; ?></small>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No hay notas aún.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <?php $conn->close(); ?>


</body>
</html>
