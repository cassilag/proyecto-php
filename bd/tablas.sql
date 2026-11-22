CREATE DATABASE IF NOT EXISTS clasificados_bd;
USE clasificados_bd;

CREATE TABLE Permiso (
    id_permiso INT AUTO_INCREMENT PRIMARY KEY,
    accion VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE Rol (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE Rol_Permiso (
    id_rol INT,
    id_permiso INT,
    PRIMARY KEY (id_rol, id_permiso),
    FOREIGN KEY (id_rol) REFERENCES Rol(id_rol) ON DELETE CASCADE,
    FOREIGN KEY (id_permiso) REFERENCES Permiso(id_permiso) ON DELETE CASCADE
);

CREATE TABLE Categoria (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE Autor (
    id_autor INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    contrasena VARCHAR(255) NOT NULL,
    telefono VARCHAR(30),
    id_rol INT NOT NULL,
    FOREIGN KEY (id_rol) REFERENCES Rol(id_rol) ON DELETE RESTRICT
);

CREATE TABLE Aviso (
    id_aviso INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    resaltado TINYINT(1) NOT NULL DEFAULT 0,
    id_autor INT NOT NULL,
    id_categoria INT NOT NULL,
    FOREIGN KEY (id_autor) REFERENCES Autor(id_autor) ON DELETE RESTRICT,
    FOREIGN KEY (id_categoria) REFERENCES Categoria(id_categoria) ON DELETE RESTRICT
);

CREATE TABLE Aviso_Laboral (
    id_aviso INT PRIMARY KEY,
    empresa VARCHAR(100) NOT NULL,
    descripcion_cargo TEXT NOT NULL,
    sueldo DECIMAL(10,2),
    FOREIGN KEY (id_aviso) REFERENCES Aviso(id_aviso) ON DELETE CASCADE
);

CREATE TABLE Aviso_Producto (
    id_aviso INT PRIMARY KEY,
    descripcion TEXT NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (id_aviso) REFERENCES Aviso(id_aviso) ON DELETE CASCADE
);

CREATE TABLE Aviso_Inmobiliario (
    id_aviso INT PRIMARY KEY,
    direccion VARCHAR(150) NOT NULL,
    tipo_inmueble VARCHAR(50) NOT NULL,
    inmobiliaria VARCHAR(100),
    tipo_operacion VARCHAR(30) NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    gastos_comunes DECIMAL(10,2),
    FOREIGN KEY (id_aviso) REFERENCES Aviso(id_aviso) ON DELETE CASCADE
);

CREATE TABLE Auditoria_Backup (
    id_backup INT AUTO_INCREMENT PRIMARY KEY,
    fecha_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_autor INT NOT NULL,
    tipo_respaldo VARCHAR(30) NOT NULL,
    archivo_generado VARCHAR(150) NOT NULL,
    estado VARCHAR(20) NOT NULL,
    observaciones VARCHAR(255),
    FOREIGN KEY (id_autor) REFERENCES Autor(id_autor) ON DELETE RESTRICT
);

INSERT INTO Permiso (accion) VALUES
('crear_aviso'),
('editar_aviso'),
('eliminar_aviso'),
('ver_avisos'),
('gestionar_usuarios'),
('realizar_backup');

INSERT INTO Rol (nombre) VALUES
('administrador'),
('publicador'),
('visitante'),
('respaldo');

INSERT INTO Rol_Permiso (id_rol, id_permiso) VALUES
(1, 1), (1, 2), (1, 3), (1, 4), (1, 5), (1, 6),
(2, 1), (2, 2), (2, 3), (2, 4),
(3, 4),
(4, 4), (4, 6);

INSERT INTO Categoria (nombre) VALUES
('Laboral'),
('Producto'),
('Inmobiliario');

INSERT INTO Autor (nombre, email, contrasena, telefono, id_rol) VALUES
('admin1', 'admin1@correo.com', '1234', '099111222', 1),
('user1', 'user1@correo.com', '1234', '098333444', 2);

START TRANSACTION;
INSERT INTO Aviso (titulo, fecha_creacion, resaltado, id_autor, id_categoria)
VALUES ('Desarrollador PHP Junior', NOW(), 1, 2, 1);

INSERT INTO Aviso_Laboral (id_aviso, empresa, descripcion_cargo, sueldo)
VALUES (LAST_INSERT_ID(), 'Tech Uruguay S.A.', 'Desarrollo backend en PHP y MySQL.', 45000.00);
COMMIT;

START TRANSACTION;
INSERT INTO Aviso (titulo, fecha_creacion, resaltado, id_autor, id_categoria)
VALUES ('Bicicleta MTB Rodado 29', NOW(), 0, 2, 2);

INSERT INTO Aviso_Producto (id_aviso, descripcion, precio)
VALUES (LAST_INSERT_ID(), 'Cuadro de aluminio, 21 velocidades, frenos a disco.', 14500.00);
COMMIT;

START TRANSACTION;
INSERT INTO Aviso (titulo, fecha_creacion, resaltado, id_autor, id_categoria)
VALUES ('Apartamento 2 dormitorios en Pocitos', NOW(), 0, 2, 3);

INSERT INTO Aviso_Inmobiliario (id_aviso, direccion, tipo_inmueble, inmobiliaria, tipo_operacion, precio, gastos_comunes)
VALUES (LAST_INSERT_ID(), 'Av. Brasil 2500', 'Apartamento', 'Inmobiliaria Sur', 'Alquiler', 32000.00, 4500.00);
COMMIT;

START TRANSACTION;
INSERT INTO Aviso (titulo, fecha_creacion, resaltado, id_autor, id_categoria)
VALUES ('Notebook usada (prueba cancelada)', NOW(), 0, 2, 2);

INSERT INTO Aviso_Producto (id_aviso, descripcion, precio)
VALUES (LAST_INSERT_ID(), '16GB RAM, 512GB SSD', 18000.00);
ROLLBACK;

UPDATE Aviso
SET resaltado = 1
WHERE id_aviso = 2;

UPDATE Autor
SET telefono = '099555666'
WHERE id_autor = 2;

START TRANSACTION;
INSERT INTO Aviso (titulo, fecha_creacion, resaltado, id_autor, id_categoria)
VALUES ('Aviso temporal para prueba de borrado', NOW(), 0, 2, 2);
SET @id_prueba = LAST_INSERT_ID();
INSERT INTO Aviso_Producto (id_aviso, descripcion, precio)
VALUES (@id_prueba, 'Artículo de prueba', 100.00);
COMMIT;

DELETE FROM Aviso WHERE id_aviso = @id_prueba;

INSERT INTO Auditoria_Backup (id_autor, tipo_respaldo, archivo_generado, estado, observaciones)
VALUES (1, 'Completo', 'backup_clasificados_bd_20261122.sql', 'Exitoso', 'Respaldo inicial de la base de datos.');

SELECT
    a.id_aviso,
    a.titulo,
    a.fecha_creacion,
    a.resaltado,
    c.nombre AS categoria,
    au.nombre AS autor,
    au.telefono,
    al.empresa,
    al.descripcion_cargo,
    al.sueldo,
    ap.descripcion AS descripcion_producto,
    ap.precio AS precio_producto,
    ai.direccion,
    ai.tipo_inmueble,
    ai.inmobiliaria,
    ai.tipo_operacion,
    ai.precio AS precio_inmueble,
    ai.gastos_comunes
FROM Aviso a
INNER JOIN Categoria c ON a.id_categoria = c.id_categoria
INNER JOIN Autor au ON a.id_autor = au.id_autor
LEFT JOIN Aviso_Laboral al ON a.id_aviso = al.id_aviso
LEFT JOIN Aviso_Producto ap ON a.id_aviso = ap.id_aviso
LEFT JOIN Aviso_Inmobiliario ai ON a.id_aviso = ai.id_aviso
ORDER BY a.fecha_creacion DESC;

SELECT id_aviso, titulo, fecha_creacion
FROM Aviso
WHERE resaltado = 1
ORDER BY fecha_creacion DESC;

SELECT c.nombre AS categoria, COUNT(a.id_aviso) AS total_avisos
FROM Categoria c
LEFT JOIN Aviso a ON c.id_categoria = a.id_categoria
GROUP BY c.id_categoria, c.nombre;

SELECT r.nombre AS rol, p.accion AS permiso
FROM Rol r
INNER JOIN Rol_Permiso rp ON r.id_rol = rp.id_rol
INNER JOIN Permiso p ON rp.id_permiso = p.id_permiso
ORDER BY r.id_rol, p.id_permiso;

SELECT * FROM Auditoria_Backup ORDER BY fecha_hora DESC;

CREATE USER IF NOT EXISTS 'admin1'@'localhost' IDENTIFIED BY '1234';
CREATE USER IF NOT EXISTS 'user1'@'localhost' IDENTIFIED BY '1234';
CREATE USER IF NOT EXISTS 'visitante1'@'localhost' IDENTIFIED BY '1234';
CREATE USER IF NOT EXISTS 'respaldo1'@'localhost' IDENTIFIED BY '1234';

GRANT ALL PRIVILEGES ON clasificados_bd.* TO 'admin1'@'localhost';

GRANT SELECT ON clasificados_bd.Categoria TO 'user1'@'localhost';
GRANT SELECT ON clasificados_bd.Rol TO 'user1'@'localhost';
GRANT SELECT, INSERT, UPDATE ON clasificados_bd.Autor TO 'user1'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON clasificados_bd.Aviso TO 'user1'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON clasificados_bd.Aviso_Laboral TO 'user1'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON clasificados_bd.Aviso_Producto TO 'user1'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON clasificados_bd.Aviso_Inmobiliario TO 'user1'@'localhost';

GRANT SELECT ON clasificados_bd.Aviso TO 'visitante1'@'localhost';
GRANT SELECT ON clasificados_bd.Aviso_Laboral TO 'visitante1'@'localhost';
GRANT SELECT ON clasificados_bd.Aviso_Producto TO 'visitante1'@'localhost';
GRANT SELECT ON clasificados_bd.Aviso_Inmobiliario TO 'visitante1'@'localhost';
GRANT SELECT ON clasificados_bd.Categoria TO 'visitante1'@'localhost';
GRANT SELECT (id_autor, nombre, email, telefono, id_rol) ON clasificados_bd.Autor TO 'visitante1'@'localhost';

GRANT SELECT, LOCK TABLES, SHOW VIEW, EVENT, TRIGGER ON clasificados_bd.* TO 'respaldo1'@'localhost';
GRANT INSERT ON clasificados_bd.Auditoria_Backup TO 'respaldo1'@'localhost';

FLUSH PRIVILEGES;