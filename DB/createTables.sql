-- Tablas (sin cambios, no necesitan delimiters especiales)

CREATE TABLE usuario (
    Rut VARCHAR(12) PRIMARY KEY,
    Nombre VARCHAR(50) NOT NULL,
    Correo VARCHAR(150) NOT NULL UNIQUE,
    Contraseña VARCHAR(50) NOT NULL,
    EsAutor BOOLEAN DEFAULT FALSE,
    EsRevisor BOOLEAN DEFAULT FALSE
);

CREATE TABLE articulos (
    IDArticulo INT PRIMARY KEY AUTO_INCREMENT,
    Titulo VARCHAR(50) NOT NULL,
    FechaEnvio DATE NOT NULL,
    Resumen VARCHAR(150) NOT NULL,
    EnRevision BOOLEAN DEFAULT FALSE
);

CREATE TABLE topicos (
    IDTopico INT PRIMARY KEY AUTO_INCREMENT,
    NombreTopico VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE revisiones (
    IDArticulo INT,
    RutRev VARCHAR(12),
    CalidadTecnica INT DEFAULT NULL,
    Originalidad INT DEFAULT NULL,
    ValoracionGlobal INT DEFAULT NULL,
    ArgumentosValoracion VARCHAR(256) DEFAULT NULL,
    ComentariosRevisor VARCHAR(256) DEFAULT NULL,
    CONSTRAINT PK_revisiones PRIMARY KEY (IDArticulo, RutRev),
    CONSTRAINT FK_revisiones_IDArticulo FOREIGN KEY (IDArticulo) REFERENCES articulos(IDArticulo) ON DELETE CASCADE,
    CONSTRAINT FK_revisiones_RutRev FOREIGN KEY (RutRev) REFERENCES usuario(Rut) ON DELETE CASCADE
);

CREATE TABLE topicosRevisores (
    IDTopico INT,
    RutRev VARCHAR(12),
    CONSTRAINT PK_topicosRevisores PRIMARY KEY (IDTopico, RutRev),
    CONSTRAINT FK_topicosRevisores_IDTopico FOREIGN KEY (IDTopico) REFERENCES topicos(IDTopico) ON DELETE CASCADE,
    CONSTRAINT FK_topicosRevisores_RutRev FOREIGN KEY (RutRev) REFERENCES usuario(Rut) ON DELETE CASCADE
);

CREATE TABLE topicosArticulos (
    IDTopico INT,
    IDArticulo INT,
    CONSTRAINT PK_topicosArticulos PRIMARY KEY (IDTopico, IDArticulo),
    CONSTRAINT FK_topicosArticulos_IDTopico FOREIGN KEY (IDTopico) REFERENCES topicos(IDTopico) ON DELETE CASCADE,
    CONSTRAINT FK_topicosArticulos_IDArticulo FOREIGN KEY (IDArticulo) REFERENCES articulos(IDArticulo) ON DELETE CASCADE
);

CREATE TABLE autoresArticulos (
    IDArticulo INT,
    RutAut VARCHAR(12),
    EsContacto BOOLEAN DEFAULT FALSE,
    CONSTRAINT PK_autoresArticulos PRIMARY KEY (IDArticulo, RutAut),
    CONSTRAINT FK_autoresArticulos_IDArticulo FOREIGN KEY (IDArticulo) REFERENCES articulos(IDArticulo) ON DELETE CASCADE,
    CONSTRAINT FK_autoresArticulos_RutAut FOREIGN KEY (RutAut) REFERENCES usuario(Rut) ON DELETE CASCADE
);



-- Cambiamos delimitador para crear funciones y procedimientos
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

CREATE PROCEDURE crear_articulo(
    IN p_titulo VARCHAR(50),
    IN p_resumen VARCHAR(150),
    IN p_ruts_autores TEXT,
    IN p_topicos TEXT,
    IN p_rut_contacto VARCHAR(12)
)
BEGIN
    DECLARE v_id_articulo INT;
    DECLARE v_total_autores INT DEFAULT 0;
    DECLARE v_total_topicos INT DEFAULT 0;
    DECLARE i INT DEFAULT 1;
    DECLARE rut_actual VARCHAR(12);
    DECLARE topico_actual INT;

    INSERT INTO articulos (Titulo, FechaEnvio, Resumen)
    VALUES (p_titulo, CURDATE(), p_resumen);

    SET v_id_articulo = LAST_INSERT_ID();

    SET v_total_autores = LENGTH(p_ruts_autores) - LENGTH(REPLACE(p_ruts_autores, ',', '')) + 1;
    SET v_total_topicos = LENGTH(p_topicos) - LENGTH(REPLACE(p_topicos, ',', '')) + 1;

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

    SET i = 1;
    WHILE i <= v_total_topicos DO
        SET topico_actual = CAST(split_string_index(p_topicos, ',', i) AS UNSIGNED);
        INSERT INTO topicosArticulos(IDTopico, IDArticulo)
        VALUES (topico_actual, v_id_articulo);
        SET i = i + 1;
    END WHILE;
END$$

-- Vistas no necesitan delimiters especiales, se pueden crear con ; normal

DELIMITER ;

CREATE OR REPLACE VIEW vista_articulos_autor AS
SELECT 
    a.IDArticulo,
    a.Titulo,
    a.FechaEnvio,
    a.Resumen,
    a.EnRevision,
    aa.RutAut,
    aa.EsContacto
FROM articulos a
JOIN autoresArticulos aa ON a.IDArticulo = aa.IDArticulo;


CREATE VIEW vista_admin_articulos_ordenada AS
SELECT 
    a.IDArticulo,
    a.Titulo,
    GROUP_CONCAT(DISTINCT t.NombreTopico ORDER BY t.NombreTopico SEPARATOR '<br>') AS Topicos,
    GROUP_CONCAT(DISTINCT au.Nombre ORDER BY au.Nombre SEPARATOR '<br>') AS Autores,
    GROUP_CONCAT(DISTINCT rev.Nombre ORDER BY rev.Nombre SEPARATOR '<br>') AS Revisores,
    COUNT(DISTINCT r.RutRev) AS CantRevisores
FROM articulos a
LEFT JOIN topicosArticulos ta ON a.IDArticulo = ta.IDArticulo
LEFT JOIN topicos t ON ta.IDTopico = t.IDTopico
LEFT JOIN autoresArticulos aa ON a.IDArticulo = aa.IDArticulo
LEFT JOIN usuario au ON aa.RutAut = au.Rut
LEFT JOIN revisiones r ON a.IDArticulo = r.IDArticulo
LEFT JOIN usuario rev ON r.RutRev = rev.Rut
GROUP BY a.IDArticulo, a.Titulo
ORDER BY CantRevisores ASC, a.IDArticulo ASC;

CREATE OR REPLACE VIEW vista_revisores_info AS
SELECT 
    u.Rut,
    u.Nombre,
    GROUP_CONCAT(DISTINCT t.NombreTopico ORDER BY t.NombreTopico SEPARATOR ', ') AS Especialidades,
    COUNT(DISTINCT r.IDArticulo) AS TotalAsignados,
    GROUP_CONCAT(DISTINCT a.Titulo ORDER BY a.Titulo SEPARATOR ', ') AS ArticulosAsignados
FROM usuario u
JOIN topicosRevisores tr ON u.Rut = tr.RutRev
JOIN topicos t ON tr.IDTopico = t.IDTopico
LEFT JOIN revisiones r ON u.Rut = r.RutRev
LEFT JOIN articulos a ON r.IDArticulo = a.IDArticulo
WHERE u.EsRevisor = 1
GROUP BY u.Rut, u.Nombre;

CREATE OR REPLACE VIEW vista_revisores_asignados AS
SELECT 
    r.IDArticulo,
    u.Rut AS RutRevisor,
    u.Nombre AS NombreRevisor,
    GROUP_CONCAT(DISTINCT tr2.NombreTopico ORDER BY tr2.NombreTopico SEPARATOR ', ') AS TopicosRevisor,
    GROUP_CONCAT(DISTINCT a2.Titulo ORDER BY a2.IDArticulo SEPARATOR ', ') AS ArticulosAsignados
FROM revisiones r
JOIN usuario u ON r.RutRev = u.Rut
LEFT JOIN topicosRevisores tr ON tr.RutRev = u.Rut
LEFT JOIN topicos tr2 ON tr.IDTopico = tr2.IDTopico
LEFT JOIN revisiones r2 ON u.Rut = r2.RutRev
LEFT JOIN articulos a2 ON a2.IDArticulo = r2.IDArticulo
GROUP BY r.IDArticulo, u.Rut, u.Nombre;

-- Triggers deben usar delimiters $$ también

DELIMITER $$

CREATE TRIGGER triggerRevisionInsert
AFTER INSERT ON revisiones
FOR EACH ROW
BEGIN
    UPDATE articulos
    SET EnRevision = TRUE
    WHERE IDArticulo = NEW.IDArticulo;
END$$

CREATE TRIGGER triggerRevisionDelete
AFTER DELETE ON revisiones
FOR EACH ROW
BEGIN
    DECLARE total INT;

    SELECT COUNT(*) INTO total
    FROM revisiones
    WHERE IDArticulo = OLD.IDArticulo;

    IF total = 0 THEN
        UPDATE articulos
        SET EnRevision = FALSE
        WHERE IDArticulo = OLD.IDArticulo;
    END IF;
END$$

DELIMITER ;

CREATE OR REPLACE VIEW vista_articulos_evaluados AS
SELECT 
    a.IDArticulo,
    a.Titulo,
    a.Resumen,
    GROUP_CONCAT(DISTINCT t.NombreTopico SEPARATOR ', ') AS Topicos,
    GROUP_CONCAT(DISTINCT u.Nombre SEPARATOR ', ') AS Autores,
    ROUND(AVG(r.ValoracionGlobal)) AS ValoracionGlobal
FROM articulos a
JOIN revisiones r ON a.IDArticulo = r.IDArticulo
JOIN autoresArticulos aa ON a.IDArticulo = aa.IDArticulo
JOIN usuario u ON aa.RutAut = u.Rut
JOIN topicosArticulos ta ON a.IDArticulo = ta.IDArticulo
JOIN topicos t ON ta.IDTopico = t.IDTopico
WHERE a.IDArticulo NOT IN (
    SELECT IDArticulo
    FROM revisiones
    WHERE ValoracionGlobal IS NULL 
       OR CalidadTecnica IS NULL
       OR Originalidad IS NULL
       OR ArgumentosValoracion IS NULL
       OR ComentariosRevisor IS NULL
)
GROUP BY a.IDArticulo;

INSERT INTO topicos (IDTopico, NombreTopico) VALUES
(4, 'Ciberseguridad'),
(5, 'Desarrollo Web'),
(6, 'Minería de Datos'),
(7, 'Ingeniería de Software'),
(8, 'Computación Gráfica'),
(9, 'Arquitectura de Computadores'),
(10, 'Sistemas Distribuidos'),
(11, 'Salud Pública'),
(12, 'Nutrición'),
(13, 'Biotecnología'),
(14, 'Genética');


INSERT INTO usuario (Rut, Nombre, Correo, Contraseña, EsAutor, EsRevisor) VALUES
('11111111-1', 'Ana Pérez', 'ana.perez@example.com', 'pass1234', TRUE, FALSE),
('22222222-2', 'Luis Gómez', 'luis.gomez@example.com', 'pass1234', TRUE, TRUE),
('33333333-3', 'María López', 'maria.lopez@example.com', 'pass1234', FALSE, TRUE),
('44444444-4', 'Jorge Díaz', 'jorge.diaz@example.com', 'pass1234', TRUE, TRUE),
('55555555-5', 'Claudia Ruiz', 'claudia.ruiz@example.com', 'pass1234', TRUE, FALSE),
('66666666-6', 'Pedro Martínez', 'pedro.martinez@example.com', 'pass1234', FALSE, TRUE),
('77777777-7', 'Laura Castillo', 'laura.castillo@example.com', 'pass1234', TRUE, FALSE),
('88888888-8', 'Diego Herrera', 'diego.herrera@example.com', 'pass1234', FALSE, TRUE);

INSERT INTO articulos (Titulo, FechaEnvio, Resumen, EnRevision) VALUES
('Avances en Ciberseguridad', '2025-05-01', 'Análisis de nuevas técnicas en seguridad informática.', TRUE),
('Tendencias en Desarrollo Web', '2025-05-05', 'Exploración de frameworks modernos para la web.', TRUE),
('Aplicaciones de Minería de Datos', '2025-05-10', 'Estudio de casos prácticos en minería de datos.', FALSE);

INSERT INTO topicosArticulos (IDTopico, IDArticulo) VALUES
(4, 1),  -- Ciberseguridad para artículo 1
(7, 1),  -- Ingeniería de Software para artículo 1

(5, 2),  -- Desarrollo Web para artículo 2
(7, 2),  -- Ingeniería de Software para artículo 2

(6, 3);  -- Minería de Datos para artículo 3


INSERT INTO topicosRevisores (IDTopico, RutRev) VALUES
(4, '33333333-3'),  -- María López (revisora) en Ciberseguridad
(7, '33333333-3'),  -- María López en Ingeniería de Software

(5, '22222222-2'),  -- Luis Gómez (autor y revisor) en Desarrollo Web
(7, '22222222-2'),

(6, '66666666-6'),  -- Pedro Martínez (revisor) en Minería de Datos
(10, '66666666-6'),

(4, '44444444-4'),  -- Jorge Díaz (autor y revisor) en Ciberseguridad
(7, '44444444-4'),

(5, '88888888-8'),  -- Diego Herrera (revisor) en Desarrollo Web
(6, '88888888-8');

INSERT INTO autoresArticulos (IDArticulo, RutAut, EsContacto) VALUES
(1, '11111111-1', TRUE),   -- Ana Pérez es autor contacto artículo 1
(1, '22222222-2', FALSE),  -- Luis Gómez coautor artículo 1

(2, '44444444-4', TRUE),   -- Jorge Díaz autor contacto artículo 2
(2, '55555555-5', FALSE),  -- Claudia Ruiz coautora artículo 2

(3, '77777777-7', TRUE);   -- Laura Castillo autor contacto artículo 3

INSERT INTO revisiones (
    IDArticulo, RutRev, CalidadTecnica, Originalidad, ValoracionGlobal, ArgumentosValoracion, ComentariosRevisor
) VALUES (
    1, '33333333-3', 8, 9, 7, 'Buena estructura y aporte técnico.', 'Muy recomendable para publicación.'
);


INSERT INTO usuario (Rut, Nombre, Correo, Contraseña, EsAutor, EsRevisor)
VALUES ('admin', 'admin', 'admin@gescon.com', 'admin', FALSE, FALSE);
