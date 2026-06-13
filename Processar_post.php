<?php
$host = 'localhost';
$dbname = 'catgram_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro ao conectar com o banco de dados: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'criar_post') {
    $gato_id = $_POST['gato_id'];
    $legenda = $_POST['legenda'];

    if (isset($_FILES['foto_post']) && $_FILES['foto_post']['error'] === UPLOAD_ERR_OK) {
        $extensao = pathinfo($_FILES['foto_post']['name'], PATHINFO_EXTENSION);
        $foto_nome = uniqid('post_') . '.' . $extensao;
        move_uploaded_file($_FILES['foto_post']['tmp_name'], 'uploads/' . $foto_nome);

        $sql = "INSERT INTO posts (gato_id, foto, legenda) VALUES (:gato_id, :foto, :legenda)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':gato_id' => $gato_id,
            ':foto' => $foto_nome,
            ':legenda' => $legenda
        ]);

        header('Location: index.php?status=post_sucesso&section=inicio');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'comentar') {
    $post_id = $_POST['post_id'];
    $gato_autor_id = $_POST['gato_autor_id'] !== "" ? (int)$_POST['gato_autor_id'] : null;
    $comentario = $_POST['comentario'];

    $sql = "INSERT INTO comentarios (post_id, gato_autor_id, comentario) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$post_id, $gato_autor_id, $comentario]);

    header('Location: index.php?status=comentario_sucesso&section=inicio');
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'curtir' && isset($_GET['post_id'])) {
    $post_id = $_GET['post_id'];

    $sql = "UPDATE posts SET curtidas = curtidas + 1 WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$post_id]);

    header('Location: index.php?section=inicio');
    exit;
}
?>