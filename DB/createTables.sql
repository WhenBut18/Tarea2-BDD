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