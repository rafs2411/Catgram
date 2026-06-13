<?php

require_once 'config/db.php';

if (!is_dir('uploads')) {
    mkdir('uploads', 0777, true);
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cadastrar') {
    $nome = $_POST['nome'];
    $idade = $_POST['idade'];
    $raca = $_POST['raca'];
    $cor = $_POST['cor'];
    $sexo = $_POST['sexo'];
    $personalidade = $_POST['personalidade'];
    $nome_dono = $_POST['nome_dono'];
    
    
    $foto_nome = 'default_cat.jpg';
    if (isset($_FILES['foto_gato']) && $_FILES['foto_gato']['error'] === UPLOAD_ERR_OK) {
        $extensao = pathinfo($_FILES['foto_gato']['name'], PATHINFO_EXTENSION);
        $foto_nome = uniqid('gato_') . '.' . $extensao;
        move_uploaded_file($_FILES['foto_gato']['tmp_name'], 'uploads/' . $foto_nome);
    }

    $sql = "INSERT INTO gatos (nome, idade, raca, cor, sexo, personalidade, nome_dono, foto) 
            VALUES (:nome, :idade, :raca, :cor, :sexo, :personalidade, :nome_dono, :foto)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nome' => $nome,
        ':idade' => $idade,
        ':raca' => $raca,
        ':cor' => $cor,
        ':sexo' => $sexo,
        ':personalidade' => $personalidade,
        ':nome_dono' => $nome_dono,
        ':foto' => $foto_nome
    ]);

    header('Location: index.php?status=cadastro_sucesso&section=gatos');
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'editar') {
    $id = $_POST['gato_id'];
    $nome = $_POST['nome'];
    $idade = $_POST['idade'];
    $raca = $_POST['raca'];
    $cor = $_POST['cor'];
    $personalidade = $_POST['personalidade'];
    $nome_dono = $_POST['nome_dono'];

    $sql = "UPDATE gatos SET nome = :nome, idade = :idade, raca = :raca, cor = :cor, 
            personalidade = :personalidade, nome_dono = :nome_dono WHERE id = :id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nome' => $nome,
        ':idade' => $idade,
        ':raca' => $raca,
        ':cor' => $cor,
        ':personalidade' => $personalidade,
        ':nome_dono' => $nome_dono,
        ':id' => $id
    ]);

    header('Location: index.php?status=edicao_sucesso&section=gatos');
    exit;
}


if (isset($_GET['action']) && $_GET['action'] === 'deletar' && isset($_GET['id'])) {
    $id = $_GET['id'];

    
    $stmt = $pdo->prepare("SELECT foto FROM gatos WHERE id = ?");
    $stmt->execute([$id]);
    $gato = $stmt->fetch();
    if ($gato && $gato['foto'] !== 'default_cat.jpg' && file_exists('uploads/' . $gato['foto'])) {
        unlink('uploads/' . $gato['foto']);
    }

    $sql = "DELETE FROM gatos WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);

    header('Location: index.php?status=deletar_sucesso&section=gatos');
    exit;
}
?>
