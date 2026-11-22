CREATE DATABASE IF NOT EXISTS clasificados_db;
USE clasificados_db;

-- 1. Tablas de Seguridad (RBAC)
CREATE TABLE Rol (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE Permiso (
    id_permiso INT AUTO_INCREMENT PRIMARY KEY,
    accion VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE Rol_Permiso (
    id_rol INT,
    id_permiso INT,
    PRIMARY KEY (id_rol, id_permiso),
    FOREIGN KEY (id_rol) REFERENCES Rol(id_rol) ON DELETE CASCADE,
    FOREIGN KEY (id_permiso) REFERENCES Permiso(id_permiso) ON DELETE CASCADE
);

-- 2. Tablas Principales
CREATE TABLE Autor (
    id_autor INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    contrasena VARCHAR(255) NOT NULL,
    telefono VARCHAR(20),
    id_rol INT NOT NULL,
    FOREIGN KEY (id_rol) REFERENCES Rol(id_rol) ON DELETE RESTRICT
);

CREATE TABLE Categoria (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE Aviso (
    id_aviso INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    resaltado TINYINT(1) DEFAULT 0,
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
    tipo_operacion VARCHAR(50) NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    gastos_comunes DECIMAL(10,2),
    FOREIGN KEY (id_aviso) REFERENCES Aviso(id_aviso) ON DELETE CASCADE
);

CREATE TABLE Auditoria_Backup (
    id_backup INT AUTO_INCREMENT PRIMARY KEY,
    fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    id_autor INT NOT NULL,
    tipo_respaldo VARCHAR(30) NOT NULL,
    archivo_generado VARCHAR(150) NOT NULL,
    estado VARCHAR(20) NOT NULL,
    observaciones VARCHAR(255),
    FOREIGN KEY (id_autor) REFERENCES Autor(id_autor) ON DELETE RESTRICT
);

-- 1. DML (INSERT)

-- Roles exigidos por la consigna
INSERT INTO Rol (nombre) VALUES 
('administrador'),
('publicador'),
('visitante'),
('respaldo');

-- Permisos del sistema
INSERT INTO Permiso (accion) VALUES 
('crear_aviso'),
('editar_aviso'),
('eliminar_aviso'),
('ver_avisos'),
('gestionar_usuarios'),
('realizar_backup');

-- Asignación de permisos a roles (Rol_Permiso)
INSERT INTO Rol_Permiso (id_rol, id_permiso) VALUES 
(1, 1), (1, 2), (1, 3), (1, 4), (1, 5), (1, 6), -- administrador: todo
(2, 1), (2, 2), (2, 3), (2, 4),                 -- publicador: CRUD de avisos
(3, 4),                                         -- visitante: solo ver
(4, 4), (4, 6);                                 -- respaldo: ver y respaldar

-- Categorías mínimas
INSERT INTO Categoria (nombre) VALUES 
('Laboral'),
('Producto'),
('Inmobiliario');

-- Autores de ejemplo
INSERT INTO Autor (nombre, email, contrasena, telefono, id_rol) VALUES 
('Ana Pérez', 'ana.perez@correo.com', 1234, '099111222', 1),
('Carlos Gómez', 'carlos.gomez@correo.com', 1234, '098333444', 2);

-- 3. TCL - TRANSACCIONES (COMMIT y ROLLBACK)

-- Publicar un Aviso Laboral 
START TRANSACTION;
INSERT INTO Aviso (titulo, resaltado, id_autor, id_categoria)
VALUES ('Desarrollador PHP Junior', 1, 2, 1);

INSERT INTO Aviso_Laboral (id_aviso, empresa, descripcion_cargo, sueldo)
VALUES (LAST_INSERT_ID(), 'Tech Uruguay S.A.', 'Desarrollo backend en PHP y MySQL.', 45000.00);
COMMIT;

-- Publicar un Aviso de Producto 
START TRANSACTION;
INSERT INTO Aviso (titulo, resaltado, id_autor, id_categoria)
VALUES ('Bicicleta Mountain Bike Rodado 29', 0, 2, 2);

INSERT INTO Aviso_Producto (id_aviso, descripcion, precio)
VALUES (LAST_INSERT_ID(), 'Cuadro de aluminio, 21 velocidades, frenos a disco.', 14500.00);
COMMIT;

-- Publicar un Aviso Inmobiliario 
START TRANSACTION;
INSERT INTO Aviso (titulo, resaltado, id_autor, id_categoria)
VALUES ('Apartamento 2 dormitorios en Pocitos', 0, 2, 3);

INSERT INTO Aviso_Inmobiliario (id_aviso, direccion, tipo_inmueble, inmobiliaria, tipo_operacion, precio, gastos_comunes)
VALUES (LAST_INSERT_ID(), 'Av. Brasil 2500', 'Apartamento', 'Inmobiliaria Sur', 'Alquiler', 32000.00, 4500.00);
COMMIT;

-- Transacción cancelada 
START TRANSACTION;
INSERT INTO Aviso (titulo, resaltado, id_autor, id_categoria)
VALUES ('Notebook usada (prueba cancelada)', 0, 2, 2);

INSERT INTO Aviso_Producto (id_aviso, descripcion, precio)
VALUES (LAST_INSERT_ID(), '16GB RAM, 512GB SSD', 18000.00);
ROLLBACK;

-- 4. DML - ACTUALIZACIÓN Y BORRADO (UPDATE / DELETE)

-- Destacar el aviso de producto (id_aviso = 2) y actualizar teléfono del autor
UPDATE Aviso 
SET resaltado = 1 
WHERE id_aviso = 2;

UPDATE Autor 
SET telefono = '099555666' 
WHERE id_autor = 2;

-- Ejemplo de DELETE con ON DELETE CASCADE (creamos un aviso temporal para demostrar el borrado en cascada)
START TRANSACTION;
INSERT INTO Aviso (titulo, resaltado, id_autor, id_categoria)
VALUES ('Aviso temporal para prueba de borrado', 0, 2, 2);
SET @id_prueba = LAST_INSERT_ID();
INSERT INTO Aviso_Producto (id_aviso, descripcion, precio)
VALUES (@id_prueba, 'Artículo de prueba', 100.00);
COMMIT;

-- Al borrar en Aviso, se elimina automáticamente su fila en Aviso_Producto
DELETE FROM Aviso WHERE id_aviso = @id_prueba;

-- Registro de ejemplo en Auditoría de Backups
INSERT INTO Auditoria_Backup (id_autor, tipo_respaldo, archivo_generado, estado, observaciones)
VALUES (1,'Completo', 'backup_clasificados_20261109.sql', 'Exitoso', 'Respaldo inicial posterior a carga de datos.');

-- 5. DQL - CONSULTAS (SELECT)

-- Listado completo de avisos con autor, categoría y detalles de subtipo, en orden cronológico exacto
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

-- Consultar únicamente avisos resaltados
SELECT id_aviso, titulo, fecha_creacion
FROM Aviso
WHERE resaltado = 1
ORDER BY fecha_creacion DESC;

-- Cantidad de avisos publicados por categoría
SELECT c.nombre AS categoria, COUNT(a.id_aviso) AS total_avisos
FROM Categoria c
LEFT JOIN Aviso a ON c.id_categoria = a.id_categoria
GROUP BY c.id_categoria, c.nombre;

-- Verificación de matriz RBAC (Roles y sus permisos asignados)
SELECT r.nombre AS rol, p.accion AS permiso
FROM Rol r
INNER JOIN Rol_Permiso rp ON r.id_rol = rp.id_rol
INNER JOIN Permiso p ON rp.id_permiso = p.id_permiso
ORDER BY r.id_rol, p.id_permiso;

-- Historial de respaldos registrados
SELECT * FROM Auditoria_Backup ORDER BY fecha_hora DESC;

-- 6. DCL - SEGURIDAD (USUARIOS Y MÍNIMO PRIVILEGIO)

CREATE USER IF NOT EXISTS 'usr_admin'@'localhost' IDENTIFIED BY 'AdminPass2026!';
CREATE USER IF NOT EXISTS 'usr_publicador'@'localhost' IDENTIFIED BY 'PubPass2026!';
CREATE USER IF NOT EXISTS 'usr_visitante'@'localhost' IDENTIFIED BY 'VisPass2026!';
CREATE USER IF NOT EXISTS 'usr_respaldo'@'localhost' IDENTIFIED BY 'BackPass2026!';

-- Administrador: control total sobre la base de datos del proyecto
GRANT ALL PRIVILEGES ON clasificados_db.* TO 'usr_admin'@'localhost';

-- Publicador: consultar categorías/roles/autores, registrar publicadores y gestionar avisos y subtipos
GRANT SELECT ON clasificados_db.Categoria TO 'usr_publicador'@'localhost';
GRANT SELECT ON clasificados_db.Rol TO 'usr_publicador'@'localhost';
GRANT SELECT, INSERT ON clasificados_db.Autor TO 'usr_publicador'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON clasificados_db.Aviso TO 'usr_publicador'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON clasificados_db.Aviso_Laboral TO 'usr_publicador'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON clasificados_db.Aviso_Producto TO 'usr_publicador'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON clasificados_db.Aviso_Inmobiliario TO 'usr_publicador'@'localhost';

-- Visitante: solo lectura sobre avisos, subtipos, categorías y datos públicos del autor (sin contraseña)
GRANT SELECT ON clasificados_db.Aviso TO 'usr_visitante'@'localhost';
GRANT SELECT ON clasificados_db.Aviso_Laboral TO 'usr_visitante'@'localhost';
GRANT SELECT ON clasificados_db.Aviso_Producto TO 'usr_visitante'@'localhost';
GRANT SELECT ON clasificados_db.Aviso_Inmobiliario TO 'usr_visitante'@'localhost';
GRANT SELECT ON clasificados_db.Categoria TO 'usr_visitante'@'localhost';
GRANT SELECT (id_autor, nombre, email, telefono) ON clasificados_db.Autor TO 'usr_visitante'@'localhost';

-- Respaldo: privilegios mínimos para ejecutar mysqldump y registrar la auditoría
GRANT SELECT, LOCK TABLES, SHOW VIEW, EVENT, TRIGGER ON clasificados_db.* TO 'usr_respaldo'@'localhost';
GRANT INSERT ON clasificados_db.Auditoria_Backup TO 'usr_respaldo'@'localhost';

-- Aplicar los cambios de privilegios
FLUSH PRIVILEGES;
