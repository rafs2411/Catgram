CREATE DATABASE IF NOT EXISTS catgram_db 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE catgram_db;

CREATE TABLE IF NOT EXISTS gatos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    idade VARCHAR(50) NOT NULL,
    raca VARCHAR(100) NOT NULL,
    cor VARCHAR(100) NOT NULL,
    sexo CHAR(1) NOT NULL,
    personalidade VARCHAR(100) NOT NULL,
    nome_dono VARCHAR(150) NOT NULL,
    foto VARCHAR(255) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gato_id INT NOT NULL,
    foto VARCHAR(255) NOT NULL,
    legenda TEXT NOT NULL,
    curtidas INT DEFAULT 0,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (gato_id) REFERENCES gatos(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS comentarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    gato_autor_id INT NULL,
    comentario TEXT NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (gato_autor_id) REFERENCES gatos(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS seguidores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gato_seguidor_id INT NOT NULL,
    gato_seguido_id INT NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (gato_seguidor_id) REFERENCES gatos(id) ON DELETE CASCADE,
    FOREIGN KEY (gato_seguido_id) REFERENCES gatos(id) ON DELETE CASCADE,
    UNIQUE KEY unico_seguidor (gato_seguidor_id, gato_seguido_id)
);