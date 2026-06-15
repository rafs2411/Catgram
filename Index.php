<?php
$host = 'localhost';
$dbname = 'catgram_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao conectar com o banco de dados de gatinhos: " . $e->getMessage());
}

// Busca todos os gatinhos para o seletor de perfil
$stmtGatos = $pdo->query("SELECT * FROM gatos ORDER BY id DESC");
$gatos = $stmtGatos->fetchAll();

// Define qual gatinho está ativo navegando
$gatoAtivoId = isset($_GET['gato_ativo']) ? (int)$_GET['gato_ativo'] : (isset($gatos[0]) ? $gatos[0]['id'] : null);

// Busca os posts com os dados dos criadores
$stmtPosts = $pdo->query("
    SELECT posts.*, gatos.nome AS gato_nome, gatos.foto AS gato_avatar 
    FROM posts 
    JOIN gatos ON posts.gato_id = gatos.id 
    ORDER BY posts.id DESC
");
$posts = $stmtPosts->fetchAll();

$secaoAtiva = isset($_GET['section']) ? $_GET['section'] : 'inicio';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catgram - A Rede Social Mais Fofa da Internet</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --pink-light: #FFEBF0;
            --pink-medium: #FFB7CA;
            --pink-dark: #FF7597;
            --purple-light: #F3E8FF;
            --purple-medium: #D8B4FE;
            --purple-dark: #A855F7;
            --cream: #FFFDF9;
            --dark-gray: #5C4F55;
            --light-gray: #F1ECE9;
            --white: #FFFFFF;
            --shadow-soft: 0 8px 30px rgba(255, 183, 202, 0.15);
            --shadow-hover: 0 12px 35px rgba(255, 117, 151, 0.25);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Quicksand', sans-serif;
        }

        body {
            background-color: var(--pink-light);
            background-image: 
                radial-gradient(var(--purple-light) 15%, transparent 16%),
                radial-gradient(var(--pink-light) 15%, transparent 16%);
            background-size: 60px 60px;
            background-position: 0 0, 30px 30px;
            color: var(--dark-gray);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        button, input, select, textarea {
            font-family: inherit;
        }

        header {
            background-color: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 3px solid var(--pink-medium);
            box-shadow: 0 4px 20px rgba(255, 183, 202, 0.1);
        }

        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--pink-dark);
            text-shadow: 1px 1px 0px var(--pink-light);
            transition: var(--transition);
        }

        .logo:hover {
            transform: scale(1.05) rotate(-2deg);
        }

        .logo i {
            font-size: 2rem;
            color: var(--pink-dark);
        }

        .user-selector-container {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background-color: var(--pink-light);
            padding: 0.4rem 1rem;
            border-radius: 50px;
            border: 2px solid var(--pink-medium);
        }

        .user-selector-container span {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--pink-dark);
        }

        .user-select {
            background: none;
            border: none;
            font-weight: 700;
            color: var(--dark-gray);
            outline: none;
            cursor: pointer;
        }

        .nav-menu {
            display: flex;
            gap: 1rem;
            list-style: none;
            align-items: center;
        }

        .nav-link {
            padding: 0.6rem 1.2rem;
            border-radius: 50px;
            font-weight: 600;
            color: var(--dark-gray);
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            border: 2px solid transparent;
            cursor: pointer;
        }

        .nav-link:hover, .nav-link.active {
            background-color: var(--pink-light);
            color: var(--pink-dark);
            border-color: var(--pink-medium);
            transform: translateY(-2px);
        }

        main {
            max-width: 1200px;
            width: 100%;
            margin: 2rem auto;
            padding: 0 1.5rem;
            flex: 1;
        }

        .section-view {
            display: none;
            animation: fadeIn 0.4s ease-in-out;
        }

        .section-view.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .section-title {
            font-size: 2rem;
            color: var(--pink-dark);
            text-align: center;
            margin-bottom: 2rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.8rem;
        }

        .section-title i {
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }

        .feed-container {
            display: grid;
            grid-template-columns: 1fr;
            max-width: 600px;
            margin: 0 auto;
            gap: 2rem;
        }

        .gatos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
        }

        .post-card {
            background-color: var(--white);
            border-radius: 24px;
            border: 3px solid var(--pink-light);
            box-shadow: var(--shadow-soft);
            overflow: hidden;
            transition: var(--transition);
        }

        .post-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
            border-color: var(--pink-medium);
        }

        .post-header {
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            border-bottom: 1px solid var(--pink-light);
        }

        .post-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--pink-medium);
        }

        .post-author-info {
            display: flex;
            flex-direction: column;
        }

        .post-author-name {
            font-weight: 700;
            color: var(--pink-dark);
            font-size: 1.1rem;
        }

        .post-time {
            font-size: 0.8rem;
            color: #a09095;
        }

        .post-image-wrapper {
            position: relative;
            aspect-ratio: 1 / 1;
            overflow: hidden;
            background-color: var(--purple-light);
        }

        .post-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
            display: block;
        }

        .post-card:hover .post-image {
            transform: scale(1.02);
        }

        .post-actions {
            padding: 1rem 1.5rem 0.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .btn-action {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--pink-medium);
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.4rem;
            text-decoration: none;
        }

        .btn-action:hover {
            transform: scale(1.2);
            color: var(--pink-dark);
        }

        .likes-count {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--dark-gray);
        }

        .post-caption {
            padding: 0.5rem 1.5rem 1rem;
            font-size: 1rem;
        }

        .post-caption strong {
            color: var(--pink-dark);
            margin-right: 0.5rem;
        }

        .comments-section {
            border-top: 1px solid var(--pink-light);
            background-color: var(--cream);
            padding: 1rem 1.5rem;
        }

        .comment-list {
            list-style: none;
            max-height: 150px;
            overflow-y: auto;
            margin-bottom: 0.8rem;
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
        }

        .comment-list::-webkit-scrollbar { width: 6px; }
        .comment-list::-webkit-scrollbar-track { background: var(--pink-light); border-radius: 10px; }
        .comment-list::-webkit-scrollbar-thumb { background: var(--pink-medium); border-radius: 10px; }

        .comment-item {
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .comment-avatar {
            width: 25px;
            height: 25px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid var(--pink-medium);
        }

        .comment-item strong { color: var(--purple-dark); }

        .comment-form { display: flex; gap: 0.5rem; }

        .comment-input {
            flex: 1;
            background-color: var(--white);
            border: 2px solid var(--pink-light);
            border-radius: 20px;
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
            outline: none;
            transition: var(--transition);
        }

        .comment-input:focus { border-color: var(--pink-medium); }

        .btn-comment-submit {
            background-color: var(--pink-medium);
            color: var(--white);
            border: none;
            border-radius: 50%;
            width: 36px;
            height: 36px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }

        .btn-comment-submit:hover { background-color: var(--pink-dark); transform: scale(1.05); }

        .gato-card {
            background-color: var(--white);
            border-radius: 24px;
            border: 3px solid var(--purple-light);
            box-shadow: var(--shadow-soft);
            overflow: hidden;
            transition: var(--transition);
            display: flex;
            flex-direction: column;
        }

        .gato-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 35px rgba(216, 180, 254, 0.25);
            border-color: var(--purple-medium);
        }

        .gato-img-wrapper {
            position: relative;
            height: 220px;
            background-color: var(--pink-light);
        }

        .gato-img { width: 100%; height: 100%; object-fit: cover; }

        .gato-personality-badge {
            position: absolute;
            bottom: 12px;
            right: 12px;
            background-color: var(--purple-dark);
            color: var(--white);
            padding: 0.4rem 0.8rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 700;
            box-shadow: 0 4px 10px rgba(168, 85, 247, 0.3);
        }

        .gato-info { padding: 1.5rem; flex-grow: 1; display: flex; flex-direction: column; }

        .gato-name {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--purple-dark);
            margin-bottom: 0.3rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .gato-stats {
            display: flex;
            gap: 0.8rem;
            font-size: 0.85rem;
            font-weight: bold;
            color: var(--pink-dark);
            margin-bottom: 0.8rem;
        }

        .gato-meta {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
            font-size: 0.95rem;
            color: #706066;
            margin-bottom: 1.5rem;
        }

        .gato-meta span i { width: 20px; color: var(--purple-medium); }

        .gato-actions { margin-top: auto; display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 0.5rem; }

        .btn {
            padding: 0.6rem 1rem;
            border-radius: 50px;
            font-weight: 700;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            border: none;
            font-size: 0.9rem;
        }

        .btn-primary {
            background-color: var(--pink-dark);
            color: var(--white);
            box-shadow: 0 4px 15px rgba(255, 117, 151, 0.3);
        }

        .btn-primary:hover {
            background-color: #ff577f;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 117, 151, 0.4);
        }

        .btn-secondary { background-color: var(--purple-light); color: var(--purple-dark); }
        .btn-secondary:hover { background-color: var(--purple-medium); color: var(--white); transform: translateY(-2px); }

        .btn-following { background-color: var(--pink-medium); color: var(--white); border: 2px solid var(--pink-dark); }
        .btn-following:hover { background-color: var(--pink-dark); color: var(--white); }

        .btn-edit { background-color: #FEF08A; color: #854D0E; }
        .btn-edit:hover { background-color: #FDE047; transform: translateY(-2px); }

        .btn-delete { background-color: #FEE2E2; color: #991B1B; display: flex; align-items: center; justify-content: center; }
        .btn-delete:hover { background-color: #FCA5A5; transform: translateY(-2px); }

        .form-card {
            background-color: var(--white);
            border-radius: 30px;
            border: 3px solid var(--pink-light);
            box-shadow: var(--shadow-soft);
            max-width: 650px;
            margin: 0 auto;
            padding: 2.5rem;
            position: relative;
            overflow: hidden;
        }

        .form-group { margin-bottom: 1.5rem; position: relative; z-index: 1; }
        .form-label { display: block; font-weight: 700; color: var(--dark-gray); margin-bottom: 0.5rem; font-size: 0.95rem; }
        
        .form-input {
            width: 100%;
            background-color: var(--cream);
            border: 2px solid var(--pink-light);
            border-radius: 16px;
            padding: 0.8rem 1.2rem;
            font-size: 1rem;
            color: var(--dark-gray);
            outline: none;
            transition: var(--transition);
        }

        .form-input:focus { border-color: var(--pink-medium); background-color: var(--white); box-shadow: 0 0 0 4px rgba(255, 183, 202, 0.25); }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .form-textarea { resize: vertical; min-height: 100px; }

        .file-input-wrapper { position: relative; overflow: hidden; display: inline-block; width: 100%; }
        
        .file-input-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background-color: var(--purple-light);
            color: var(--purple-dark);
            border: 2px dashed var(--purple-medium);
            border-radius: 16px;
            padding: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: var(--transition);
            text-align: center;
        }

        .file-input-btn:hover { background-color: var(--purple-medium); color: var(--white); }
        .file-input-wrapper input[type=file] { font-size: 100px; position: absolute; left: 0; top: 0; opacity: 0; cursor: pointer; }

        .btn-submit-container { margin-top: 2rem; text-align: center; position: relative; z-index: 1; }
        .btn-large { padding: 1rem 2.5rem; font-size: 1.1rem; width: 100%; border-radius: 18px; }

        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: var(--white);
            border-left: 6px solid var(--purple-dark);
            box-shadow: var(--shadow-hover);
            border-radius: 12px;
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            transform: translateY(100px);
            opacity: 0;
            transition: var(--transition);
            z-index: 1000;
        }

        .toast.show { transform: translateY(0); opacity: 1; }
        .toast i { font-size: 1.5rem; color: var(--purple-dark); }

        footer { background-color: var(--white); border-top: 3px solid var(--pink-medium); padding: 2rem 1rem; text-align: center; margin-top: auto; }
        .footer-content { max-width: 1200px; margin: 0 auto; display: flex; flex-direction: column; align-items: center; gap: 0.8rem; }
        .footer-logo { font-size: 1.4rem; font-weight: 700; color: var(--pink-dark); }
        .footer-text { font-size: 0.9rem; color: #8c7d83; }
        .footer-socials { display: flex; gap: 1rem; margin-top: 0.5rem; }
        .footer-social-link { width: 36px; height: 36px; background-color: var(--pink-light); color: var(--pink-dark); border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: var(--transition); }
        .footer-social-link:hover { background-color: var(--pink-dark); color: var(--white); transform: scale(1.1); }

        .modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(92, 79, 85, 0.5); backdrop-filter: blur(5px); z-index: 200; display: flex; align-items: center; justify-content: center; opacity: 0; pointer-events: none; transition: var(--transition); }
        .modal.open { opacity: 1; pointer-events: auto; }
        .modal-content { background-color: var(--white); border-radius: 30px; border: 3px solid var(--purple-medium); padding: 2rem; width: 90%; max-width: 500px; position: relative; transform: scale(0.9); transition: var(--transition); }
        .modal.open .modal-content { transform: scale(1); }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .modal-title { font-size: 1.5rem; font-weight: 700; color: var(--purple-dark); }
        .modal-close { background: none; border: none; font-size: 1.5rem; color: var(--dark-gray); cursor: pointer; transition: var(--transition); }
        .modal-close:hover { color: var(--pink-dark); transform: rotate(90deg); }

        @media (max-width: 768px) {
            .header-container { flex-direction: column; padding: 1rem; }
            .nav-menu { width: 100%; justify-content: center; flex-wrap: wrap; gap: 0.5rem; }
            .nav-link { padding: 0.5rem 0.8rem; font-size: 0.9rem; }
            .form-row { grid-template-columns: 1fr; gap: 0; }
            .form-card { padding: 1.5rem; }
        }
    </style>
</head>
<body>

    <header>
        <div class="header-container">
            <a href="#" class="logo" onclick="switchSection('inicio')">
                <i class="fa-solid fa-cat"></i> Catgram
            </a>

            <?php if (count($gatos) > 0): ?>
            <div class="user-selector-container">
                <span>Navegar como:</span>
                <select class="user-select" onchange="changeGatoAtivo(this.value)">
                    <?php foreach ($gatos as $g): ?>
                        <option value="<?= $g['id'] ?>" <?= $g['id'] === $gatoAtivoId ? 'selected' : '' ?>>
                            🐾 <?= htmlspecialchars($g['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <ul class="nav-menu">
                <li><a class="nav-link" id="nav-inicio" onclick="switchSection('inicio')"><i class="fa-solid fa-house"></i> Início</a></li>
                <li><a class="nav-link" id="nav-gatos" onclick="switchSection('gatos')"><i class="fa-solid fa-paw"></i> Gatos</a></li>
                <li><a class="nav-link" id="nav-postar" onclick="switchSection('postar')"><i class="fa-solid fa-square-plus"></i> Criar Post</a></li>
                <li><a class="nav-link" id="nav-cadastrar" onclick="switchSection('cadastrar')"><i class="fa-solid fa-user-plus"></i> Cadastrar Gato</a></li>
            </ul>
        </div>
    </header>

    <main>

        <section id="section-inicio" class="section-view">
            <h2 class="section-title"><i class="fa-solid fa-heart"></i> Feed Miau-vilhoso</h2>
            
            <div class="feed-container">
                <?php if (count($posts) === 0): ?>
                    <p style="text-align: center; padding: 2rem;">Nenhum post no feed ainda. Cadastre um gatinho e publique momentos fofos! 🐾</p>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                        <article class="post-card">
                            <div class="post-header">
                                <img src="uploads/<?= htmlspecialchars($post['gato_avatar']) ?>" alt="Avatar" class="post-avatar">
                                <div class="post-author-info">
                                    <span class="post-author-name"><?= htmlspecialchars($post['gato_nome']) ?></span>
                                    <span class="post-time">Postado em <?= date('d/m às H:i', strtotime($post['criado_em'])) ?></span>
                                </div>
                            </div>
                            
                            <div class="post-image-wrapper">
                                <img src="uploads/<?= htmlspecialchars($post['foto']) ?>" alt="Post" class="post-image">
                            </div>

                            <div class="post-actions" style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <a href="processar_post.php?action=curtir&post_id=<?= $post['id'] ?>&gato_ativo=<?= $gatoAtivoId ?>" class="btn-action">
                                        <i class="fa-solid fa-heart" style="color: var(--pink-dark);"></i>
                                    </a>
                                    <span class="likes-count"><span class="count"><?= $post['curtidas'] ?></span> lambidas</span>
                                </div>

                                <?php if ($gatoAtivoId && (int)$post['gato_id'] === (int)$gatoAtivoId): ?>
                                    <a href="processar_post.php?action=deletar_post&post_id=<?= $post['id'] ?>&gato_ativo=<?= $gatoAtivoId ?>" 
                                       class="btn-action" 
                                       title="Apagar Publicação"
                                       onclick="return confirm('Queres mesmo apagar esta publicação miau-ravilhosa? 😿')"
                                       style="color: #991B1B; font-size: 1.2rem; margin-right: 1.5rem;">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </a>
                                <?php endif; ?>
                            </div>

                            <p class="post-caption">
                                <strong><?= htmlspecialchars($post['gato_nome']) ?></strong><?= htmlspecialchars($post['legenda']) ?>
                            </p>

                            <div class="comments-section">
                                <ul class="comment-list">
                                    <?php
                                    $stmtComentarios = $pdo->prepare("
                                        SELECT comentarios.*, g.nome AS autor_nome, g.foto AS autor_avatar 
                                        FROM comentarios 
                                        LEFT JOIN gatos g ON comentarios.gato_autor_id = g.id 
                                        WHERE comentarios.post_id = ? 
                                        ORDER BY comentarios.id ASC
                                    ");
                                    $stmtComentarios->execute([$post['id']]);
                                    $comentarios = $stmtComentarios->fetchAll();
                                    
                                    if (count($comentarios) === 0):
                                    ?>
                                        <li class="comment-item" style="color: #a09095; font-style: italic;">Seja o primeiro a miar um elogio!</li>
                                    <?php else: ?>
                                        <?php foreach ($comentarios as $com): ?>
                                            <li class="comment-item">
                                                <?php if ($com['autor_avatar']): ?>
                                                    <img src="uploads/<?= htmlspecialchars($com['autor_avatar']) ?>" class="comment-avatar" alt="Avatar">
                                                    <strong><?= htmlspecialchars($com['autor_nome']) ?>:</strong>
                                                <?php else: ?>
                                                    <strong>Humano:</strong>
                                                <?php endif; ?>
                                                <?= htmlspecialchars($com['comentario']) ?>
                                            </li>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </ul>
                                
                                <form action="processar_post.php" method="POST" class="comment-form">
                                    <input type="hidden" name="action" value="comentar">
                                    <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                    <input type="hidden" name="gato_autor_id" value="<?= $gatoAtivoId ?>">
                                    <input type="text" name="comentario" placeholder="Comente como <?= $gatos[array_search($gatoAtivoId, array_column($gatos, 'id'))]['nome'] ?? 'Humano' ?>..." class="comment-input" required>
                                    <button type="submit" class="btn-comment-submit"><i class="fa-solid fa-paper-plane"></i></button>
                                </form>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <section id="section-gatos" class="section-view">
            <h2 class="section-title"><i class="fa-solid fa-paw"></i> Gatinhos Cadastrados</h2>
            
            <div class="gatos-grid">
                <?php if (count($gatos) === 0): ?>
                    <p style="text-align: center; grid-column: 1/-1; padding: 2rem;">Nenhum gatinho cadastrado ainda. 😿 Vá na aba de cadastro para começar!</p>
                <?php else: ?>
                    <?php foreach ($gatos as $gato): ?>
                        <?php
                        $stmtSeguidores = $pdo->prepare("SELECT COUNT(*) AS total FROM seguidores WHERE gato_seguido_id = ?");
                        $stmtSeguidores->execute([$gato['id']]);
                        $seguidoresCount = $stmtSeguidores->fetch()['total'];

                        $stmtSeguindo = $pdo->prepare("SELECT COUNT(*) AS total FROM seguidores WHERE gato_seguidor_id = ?");
                        $stmtSeguindo->execute([$gato['id']]);
                        $seguindoCount = $stmtSeguindo->fetch()['total'];

                        $isSeguindo = false;
                        if ($gatoAtivoId) {
                            $stmtVerificar = $pdo->prepare("SELECT 1 FROM seguidores WHERE gato_seguido_id = ? AND gato_seguidor_id = ?");
                            $stmtVerificar->execute([$gato['id'], $gatoAtivoId]);
                            $isSeguindo = (bool)$stmtVerificar->fetch();
                        }
                        ?>
                        <div class="gato-card">
                            <div class="gato-img-wrapper">
                                <img src="uploads/<?= htmlspecialchars($gato['foto']) ?>" alt="Foto do gatinho" class="gato-img">
                                <span class="gato-personality-badge"><?= htmlspecialchars($gato['personalidade']) ?></span>
                            </div>
                            <div class="gato-info">
                                <h3 class="gato-name">
                                    <?= htmlspecialchars($gato['nome']) ?>
                                    <i class="fa-solid <?= $gato['sexo'] === 'M' ? 'fa-mars' : 'fa-venus' ?>" 
                                       style="color: <?= $gato['sexo'] === 'M' ? '#60a5fa' : '#f472b6' ?>; font-size: 1rem;"></i>
                                </h3>
                                
                                <div class="gato-stats">
                                    <span><?= $seguidoresCount ?> seguidores</span>
                                    <span>•</span>
                                    <span><?= $seguindoCount ?> seguindo</span>
                                </div>

                                <div class="gato-meta">
                                    <span><i class="fa-solid fa-cake-candles"></i> <?= htmlspecialchars($gato['idade']) ?></span>
                                    <span><i class="fa-solid fa-dna"></i> <?= htmlspecialchars($gato['raca']) ?></span>
                                    <span><i class="fa-solid fa-palette"></i> <?= htmlspecialchars($gato['cor']) ?></span>
                                    <span><i class="fa-solid fa-user"></i> Dono: <?= htmlspecialchars($gato['nome_dono']) ?></span>
                                </div>
                                <div class="gato-actions">
                                    <?php if ($gatoAtivoId && $gato['id'] !== $gatoAtivoId): ?>
                                        <?php if ($isSeguindo): ?>
                                            <a href="processar_seguidores.php?action=unfollow&seguido_id=<?= $gato['id'] ?>&seguidor_id=<?= $gatoAtivoId ?>" class="btn btn-following">Seguindo</a>
                                        <?php else: ?>
                                            <a href="processar_seguidores.php?action=seguir&seguido_id=<?= $gato['id'] ?>&seguidor_id=<?= $gatoAtivoId ?>" class="btn btn-primary">Seguir</a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <button class="btn btn-secondary" onclick="showToast('Você é o <?= htmlspecialchars($gato['nome']) ?>! 🐾')">Miau</button>
                                    <?php endif; ?>

                                    <button class="btn btn-edit" title="Editar Gatinho" onclick="openEditModal(
                                        '<?= $gato['id'] ?>', 
                                        '<?= htmlspecialchars($gato['nome']) ?>', 
                                        '<?= htmlspecialchars($gato['idade']) ?>', 
                                        '<?= htmlspecialchars($gato['raca']) ?>', 
                                        '<?= htmlspecialchars($gato['cor']) ?>', 
                                        '<?= htmlspecialchars($gato['personalidade']) ?>', 
                                        '<?= htmlspecialchars($gato['nome_dono']) ?>'
                                    )"><i class="fa-solid fa-pen"></i></button>
                                    
                                    <a href="processar_gato.php?action=deletar&id=<?= $gato['id'] ?>" class="btn btn-delete" onclick="return confirm('Deseja realmente deletar o gatinho <?= htmlspecialchars($gato['nome']) ?>? 🥺')" title="Excluir Gatinho">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <section id="section-cadastrar" class="section-view">
            <h2 class="section-title"><i class="fa-solid fa-circle-plus"></i> Novo Cadastro Miador</h2>
            
            <div class="form-card">
                <form action="processar_gato.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="cadastrar">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="cad-nome">Nome do gatinho</label>
                            <input type="text" id="cad-nome" name="nome" class="form-input" placeholder="Ex: Fubá" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="cad-idade">Idade</label>
                            <input type="text" id="cad-idade" name="idade" class="form-input" placeholder="Ex: 6 meses, 2 anos" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="cad-raca">Raça</label>
                            <input type="text" id="cad-raca" name="raca" class="form-input" placeholder="Ex: Angorá, SRD" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="cad-cor">Cor do Pelo</label>
                            <input type="text" id="cad-cor" name="cor" class="form-input" placeholder="Ex: Laranja, Frajola" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="cad-sexo">Sexo</label>
                            <select id="cad-sexo" name="sexo" class="form-input" required>
                                <option value="" disabled selected>Selecione</option>
                                <option value="M">Macho ♂</option>
                                <option value="F">Fêmea ♀</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="cad-personalidade">Personalidade</label>
                            <input type="text" id="cad-personalidade" name="personalidade" class="form-input" placeholder="Ex: Arteiro, Carente, Arisco" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="cad-dono">Nome do Dono (Humano)</label>
                        <input type="text" id="cad-dono" name="nome_dono" class="form-input" placeholder="Seu nome completo" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Foto do Gatinho</label>
                        <div class="file-input-wrapper">
                            <div class="file-input-btn" id="file-label-cad">
                                <i class="fa-solid fa-camera"></i> Escolher uma fotinha fofa
                            </div>
                            <input type="file" name="foto_gato" accept="image/*" required onchange="updateFileName(this, 'file-label-cad')">
                        </div>
                    </div>

                    <div class="btn-submit-container">
                        <button type="submit" class="btn btn-primary btn-large">
                            <i class="fa-solid fa-shield-cat"></i> Cadastrar gatinho
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <section id="section-postar" class="section-view">
            <h2 class="section-title"><i class="fa-solid fa-images"></i> Compartilhar Miado</h2>
            
            <div class="form-card">
                <form action="processar_post.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="criar_post">

                    <div class="form-group">
                        <label class="form-label" for="post-gato">Quem está miando?</label>
                        <select id="post-gato" name="gato_id" class="form-input" required>
                            <option value="" disabled selected>Selecione seu gato cadastrado</option>
                            <?php foreach ($gatos as $g): ?>
                                <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['nome']) ?> (<?= htmlspecialchars($g['raca']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Foto da Publicação</label>
                        <div class="file-input-wrapper">
                            <div class="file-input-btn" id="file-label-post">
                                <i class="fa-solid fa-camera"></i> Carregar uma foto fofinha
                            </div>
                            <input type="file" name="foto_post" accept="image/*" required onchange="updateFileName(this, 'file-label-post')">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="post-legenda">Legenda do Post</label>
                        <textarea id="post-legenda" name="legenda" class="form-input form-textarea" placeholder="O que seu gatinho está pensando agora? Escreva aqui..." required></textarea>
                    </div>

                    <div class="btn-submit-container">
                        <button type="submit" class="btn btn-primary btn-large">
                            <i class="fa-solid fa-paper-plane"></i> Publicar no Feed
                        </button>
                    </div>
                </form>
            </div>
        </section>

    </main>

    <div class="toast" id="cute-toast">
        <i class="fa-solid fa-paw"></i>
        <span id="toast-message">Mensagem fofa!</span>
    </div>

    <div class="modal" id="editModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><i class="fa-solid fa-pen-nib"></i> Editar Cadastro</h3>
                <button class="modal-close" onclick="closeEditModal()">&times;</button>
            </div>
            
            <form action="processar_gato.php" method="POST">
                <input type="hidden" name="action" value="editar">
                <input type="hidden" name="gato_id" id="edit-id" value="">
                
                <div class="form-group">
                    <label class="form-label" for="edit-nome">Nome</label>
                    <input type="text" id="edit-nome" name="nome" class="form-input" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="edit-idade">Idade</label>
                        <input type="text" id="edit-idade" name="idade" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="edit-raca">Raça</label>
                        <input type="text" id="edit-raca" name="raca" class="form-input" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="edit-cor">Cor</label>
                        <input type="text" id="edit-cor" name="cor" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="edit-personalidade">Personalidade</label>
                        <input type="text" id="edit-personalidade" name="personalidade" class="form-input" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="edit-dono">Humano</label>
                    <input type="text" id="edit-dono" name="nome_dono" class="form-input" required>
                </div>

                <div class="gato-actions" style="margin-top: 1.5rem; display: flex; gap: 0.5rem;">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Salvar Mudanças</button>
                </div>
            </form>
        </div>
    </div>

    <footer>
        <div class="footer-content">
            <span class="footer-logo"><i class="fa-solid fa-cat"></i> Catgram</span>
            <p class="footer-text">Desenvolvido com muito 💖 e sachê de salmão.</p>
            <div class="footer-socials">
                <a href="#" class="footer-social-link"><i class="fa-brands fa-instagram"></i></a>
                <a href="#" class="footer-social-link"><i class="fa-brands fa-facebook"></i></a>
                <a href="#" class="footer-social-link"><i class="fa-brands fa-github"></i></a>
            </div>
        </div>
    </footer>

    <script>
        const secaoInicial = "<?= $secaoAtiva ?>";
        switchSection(secaoInicial);

        function switchSection(sectionId) {
            document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
            document.querySelectorAll('.section-view').forEach(section => section.classList.remove('active'));

            const targetSection = document.getElementById(`section-${sectionId}`);
            const targetNavLink = document.getElementById(`nav-${sectionId}`);
            
            if (targetSection) targetSection.classList.add('active');
            if (targetNavLink) targetNavLink.classList.add('active');
        }

        function changeGatoAtivo(id) {
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('gato_ativo', id);
            window.location.href = currentUrl.toString();
        }

        function showToast(message) {
            const toast = document.getElementById('cute-toast');
            const toastMsg = document.getElementById('toast-message');
            toastMsg.textContent = message;
            toast.classList.add('show');

            setTimeout(() => {
                toast.classList.remove('show');
            }, 4000);
        }

        function updateFileName(input, labelId) {
            const label = document.getElementById(labelId);
            if (input.files && input.files.length > 0) {
                label.innerHTML = `<i class="fa-solid fa-check"></i> ${input.files[0].name}`;
                label.style.borderColor = 'var(--pink-dark)';
                label.style.color = 'var(--pink-dark)';
            } else {
                label.innerHTML = `<i class="fa-solid fa-camera"></i> Escolher uma fotinha fofa`;
            }
        }

        function openEditModal(id, nome, idade, raca, cor, personality, dono) {
            document.getElementById('edit-id').value = id;
            document.getElementById('edit-nome').value = nome;
            document.getElementById('edit-idade').value = idade;
            document.getElementById('edit-raca').value = raca;
            document.getElementById('edit-cor').value = cor;
            document.getElementById('edit-personalidade').value = personality;
            document.getElementById('edit-dono').value = dono;
            
            document.getElementById('editModal').classList.add('open');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.remove('open');
        }

        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status');
        if (status === 'cadastro_sucesso') {
            showToast('Miau-ravilha! Novo gatinho cadastrado com sucesso! 🎉🐾');
        } else if (status === 'edicao_sucesso') {
            showToast('Dados do gatinho atualizados com sucesso! 😺✨');
        } else if (status === 'deletar_sucesso') {
            showToast('O gatinho voltou para a caixa de papelão (Deletado). 📦');
        } else if (status === 'post_sucesso') {
            showToast('Seu post fofinho já está brilhando no feed principal! 📸💖');
        } else if (status === 'comentario_sucesso') {
            showToast('Elogio miado enviado com sucesso! 💌🐈');
        } else if (status === 'seguiu_sucesso') {
            showToast('Miau! Agora vocês são amigos oficiais! 🐾❤️');
        } else if (status === 'unfollow_sucesso') {
            showToast('Deixou de seguir. 😿');
        } else if (status === 'erro_seguir_si_mesmo') {
            showToast('Ei! Você não pode seguir a si mesmo! 😹🌀');
        } else if (status === 'post_deletado_sucesso') {
            showToast('Publicação apagada com sucesso! 🐾🗑️');
        }
    </script>
</body>
</html>