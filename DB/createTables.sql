-- Tabla de Usuarios (Autores y Revisores)
CREATE TABLE usuario (
    Rut VARCHAR(12) PRIMARY KEY,
    Nombre VARCHAR(50) NOT NULL,
    Correo VARCHAR(150) NOT NULL UNIQUE,
    Contraseña VARCHAR(50) NOT NULL,
    EsAutor BOOLEAN DEFAULT FALSE,
    EsRevisor BOOLEAN DEFAULT FALSE
);

-- Tabla de Artículos
CREATE TABLE articulos (
    IDArticulo INT PRIMARY KEY AUTO_INCREMENT,
    Titulo VARCHAR(50) NOT NULL,
    FechaEnvio DATE NOT NULL,
    Resumen VARCHAR(150) NOT NULL
);

-- Tabla de Tópicos
CREATE TABLE topicos (
    IDTopico INT PRIMARY KEY AUTO_INCREMENT,
    NombreTopico VARCHAR(50) NOT NULL UNIQUE
);

-- Tabla de Revisiones (intermedia)
CREATE TABLE revisiones (
    IDArticulo INT,
    RutRev VARCHAR(12),
    CONSTRAINT PK_revisiones PRIMARY KEY (IDArticulo, RutRev),
    CONSTRAINT FK_revisiones_IDArticulo FOREIGN KEY (IDArticulo) REFERENCES articulos(IDArticulo) ON DELETE CASCADE,
    CONSTRAINT FK_revisiones_RutRev FOREIGN KEY (RutRev) REFERENCES usuario(Rut) ON DELETE CASCADE
);

-- Tabla de Tópicos de Revisores
CREATE TABLE topicosRevisores (
    IDTopico INT,
    RutRev VARCHAR(12),
    CONSTRAINT PK_topicosRevisores PRIMARY KEY (IDTopico, RutRev),
    CONSTRAINT FK_topicosRevisores_IDTopico FOREIGN KEY (IDTopico) REFERENCES topicos(IDTopico) ON DELETE CASCADE,
    CONSTRAINT FK_topicosRevisores_RutRev FOREIGN KEY (RutRev) REFERENCES usuario(Rut) ON DELETE CASCADE
);

-- Tabla de Tópicos de Artículos
CREATE TABLE topicosArticulos (
    IDTopico INT,
    IDArticulo INT,
    CONSTRAINT PK_topicosArticulos PRIMARY KEY (IDTopico, IDArticulo),
    CONSTRAINT FK_topicosArticulos_IDTopico FOREIGN KEY (IDTopico) REFERENCES topicos(IDTopico) ON DELETE CASCADE,
    CONSTRAINT FK_topicosArticulos_IDArticulo FOREIGN KEY (IDArticulo) REFERENCES articulos(IDArticulo) ON DELETE CASCADE
);

-- Tabla de Autores-Artículos (relación N:M con autor de contacto)
CREATE TABLE autoresArticulos (
    IDArticulo INT,
    RutAut VARCHAR(12),
    EsContacto BOOLEAN DEFAULT FALSE,
    CONSTRAINT PK_autoresArticulos PRIMARY KEY (IDArticulo, RutAut),
    CONSTRAINT FK_autoresArticulos_IDArticulo FOREIGN KEY (IDArticulo) REFERENCES articulos(IDArticulo) ON DELETE CASCADE,
    CONSTRAINT FK_autoresArticulos_RutAut FOREIGN KEY (RutAut) REFERENCES usuario(Rut) ON DELETE CASCADE
);

DELIMITER $$

CREATE FUNCTION split_string_index(
    str TEXT,
    delim CHAR(1),
    pos INT
)
RETURNS TEXT
DETERMINISTIC
BEGIN
    RETURN REPLACE(SUBSTRING_INDEX(SUBSTRING_INDEX(str, delim, pos), delim, -1), ' ', '');
END$$

DELIMITER ;

DELIMITER $$

CREATE PROCEDURE crear_articulo(
    IN p_titulo VARCHAR(50),
    IN p_resumen VARCHAR(150),
    IN p_ruts_autores TEXT,       -- Ej: '11111111-1,22222222-2'
    IN p_topicos TEXT,            -- Ej: '3,5'
    IN p_rut_contacto VARCHAR(12) -- Ej: '11111111-1'
)
BEGIN
    DECLARE v_id_articulo INT;
    DECLARE v_total_autores INT DEFAULT 0;
    DECLARE v_total_topicos INT DEFAULT 0;
    DECLARE i INT DEFAULT 1;
    DECLARE rut_actual VARCHAR(12);
    DECLARE topico_actual INT;

    -- Insertar artículo
    INSERT INTO articulos (Titulo, FechaEnvio, Resumen)
    VALUES (p_titulo, CURDATE(), p_resumen);

    -- Obtener ID autogenerado
    SET v_id_articulo = LAST_INSERT_ID();

    -- Contar autores y tópicos
    SET v_total_autores = LENGTH(p_ruts_autores) - LENGTH(REPLACE(p_ruts_autores, ',', '')) + 1;
    SET v_total_topicos = LENGTH(p_topicos) - LENGTH(REPLACE(p_topicos, ',', '')) + 1;

    -- Insertar autores con marca de contacto
    SET i = 1;
    WHILE i <= v_total_autores DO
        SET rut_actual = split_string_index(p_ruts_autores, ',', i);
        INSERT INTO autoresArticulos(IDArticulo, RutAut, EsContacto)
        VALUES (
            v_id_articulo,
            rut_actual,
            IF(rut_actual = p_rut_contacto, TRUE, FALSE)
        );
        SET i = i + 1;
    END WHILE;

    -- Insertar tópicos
    SET i = 1;
    WHILE i <= v_total_topicos DO
        SET topico_actual = CAST(split_string_index(p_topicos, ',', i) AS UNSIGNED);
        INSERT INTO topicosArticulos(IDTopico, IDArticulo)
        VALUES (topico_actual, v_id_articulo);
        SET i = i + 1;
    END WHILE;

END$$

DELIMITER ;
