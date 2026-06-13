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

if (isset($_GET['action']) && isset($_GET['seguido_id']) && isset($_GET['seguidor_id'])) {
    $seguido_id = (int)$_GET['seguido_id'];
    $seguidor_id = (int)$_GET['seguidor_id'];
    $action = $_GET['action'];

    if ($seguido_id === $seguidor_id) {
        header('Location: index.php?status=erro_seguir_si_mesmo&section=gatos');
        exit;
    }

    if ($action === 'seguir') {
        try {
            $sql = "INSERT INTO seguidores (gato_seguido_id, gato_seguidor_id) VALUES (?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$seguido_id, $seguidor_id]);
            header('Location: index.php?status=seguiu_sucesso&section=gatos');
            exit;
        } catch (PDOException $e) {
            header('Location: index.php?section=gatos');
            exit;
        }
    } elseif ($action === 'unfollow') {
        $sql = "DELETE FROM seguidores WHERE gato_seguido_id = ? AND gato_seguidor_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$seguido_id, $seguidor_id]);
        header('Location: index.php?status=unfollow_sucesso&section=gatos');
        exit;
    }
}

header('Location: index.php');
exit;