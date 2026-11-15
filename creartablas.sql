CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_usuario VARCHAR(50) NOT NULL UNIQUE,
    contrasena VARCHAR(255) NOT NULL,
    rol VARCHAR(20) NOT NULL
);

CREATE TABLE avisos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria VARCHAR(50) NOT NULL,
    nombre_autor VARCHAR(100) NOT NULL,
    telefono_autor VARCHAR(50) NOT NULL,
    resaltado INT DEFAULT 0,
    usuario_publicador VARCHAR(50) NOT NULL,
    fecha_creacion DATETIME NOT NULL,
    empresa VARCHAR(100) NULL,
    descripcion_cargo TEXT NULL,
    sueldo_mensual DECIMAL(10,2) NULL,
    descripcion_producto TEXT NULL,
    precio DECIMAL(10,2) NULL,
    direccion VARCHAR(150) NULL,
    tipo_propiedad VARCHAR(50) NULL,
    inmobiliaria VARCHAR(100) NULL,
    precio_venta DECIMAL(12,2) NULL,
    precio_alquiler DECIMAL(10,2) NULL,
    gastos_comunes DECIMAL(10,2) NULL
);

INSERT INTO usuarios (nombre_usuario, contrasena, rol) VALUES ('admin1', '123456', 'admin');
INSERT INTO usuarios (nombre_usuario, contrasena, rol) VALUES ('pub1', '123456', 'publicador');
