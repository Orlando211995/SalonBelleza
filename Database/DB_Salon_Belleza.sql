-- =====================================================
-- BASE DE DATOS COMPLETA - SALON DE BELLEZA
-- Tablas + Vistas
-- =====================================================

DROP DATABASE IF EXISTS salon_belleza;
CREATE DATABASE salon_belleza CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE salon_belleza;

CREATE TABLE administrador(
 id_admin INT AUTO_INCREMENT PRIMARY KEY,
 nombre VARCHAR(100) NOT NULL,
 usuario VARCHAR(50) UNIQUE NOT NULL,
 correo VARCHAR(120) UNIQUE,
 password VARCHAR(255) NOT NULL,
 reset_token VARCHAR(64),
 reset_expires_at DATETIME
);

CREATE TABLE clientes(
 id_cliente INT AUTO_INCREMENT PRIMARY KEY,
 nombre VARCHAR(100) NOT NULL,
 telefono VARCHAR(20) NOT NULL,
 correo VARCHAR(120),
 direccion TEXT,
 provincia VARCHAR(80),
 canton VARCHAR(80),
 distrito VARCHAR(80),
 fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE contacto_mensajes(
 id_mensaje INT AUTO_INCREMENT PRIMARY KEY,
 nombre VARCHAR(120) NOT NULL,
 telefono VARCHAR(30) NOT NULL,
 correo VARCHAR(120) NOT NULL,
 asunto VARCHAR(150) NOT NULL,
 mensaje TEXT NOT NULL,
 ip VARCHAR(45),
 creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_contacto_correo (correo),
 INDEX idx_contacto_creado_en (creado_en)
);

CREATE TABLE categorias(
 id_categoria INT AUTO_INCREMENT PRIMARY KEY,
 nombre VARCHAR(100) NOT NULL,
 descripcion TEXT,
 tipo ENUM('Producto','Servicio','Ambos') DEFAULT 'Producto',
 estado ENUM('Activo','Inactivo') DEFAULT 'Activo'
);

CREATE TABLE productos(
 id_producto INT AUTO_INCREMENT PRIMARY KEY,
 id_categoria INT NOT NULL,
 codigo VARCHAR(30) UNIQUE,
 nombre VARCHAR(150) NOT NULL,
 descripcion TEXT,
 precio DECIMAL(10,2) NOT NULL,
 precio_oferta DECIMAL(10,2),
 destacado BOOLEAN DEFAULT FALSE,
 stock INT DEFAULT 0,
 imagen VARCHAR(255),
 estado ENUM('Disponible','Agotado','Descontinuado') DEFAULT 'Disponible',
 fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 FOREIGN KEY(id_categoria) REFERENCES categorias(id_categoria)
);

CREATE TABLE ofertas(
 id_oferta INT AUTO_INCREMENT PRIMARY KEY,
 id_producto INT NOT NULL,
 porcentaje DECIMAL(5,2),
 precio_oferta DECIMAL(10,2),
 fecha_inicio DATE,
 fecha_fin DATE,
 activa BOOLEAN DEFAULT TRUE,
 FOREIGN KEY(id_producto) REFERENCES productos(id_producto) ON DELETE CASCADE
);

CREATE TABLE servicios(
 id_servicio INT AUTO_INCREMENT PRIMARY KEY,
 nombre VARCHAR(150),
 categoria VARCHAR(80),
 descripcion TEXT,
 precio DECIMAL(10,2),
 duracion INT NOT NULL,
 imagen VARCHAR(255),
 estado ENUM('Disponible','No Disponible') DEFAULT 'Disponible'
);

CREATE TABLE horarios(
 id_horario INT AUTO_INCREMENT PRIMARY KEY,
 hora TIME UNIQUE
);

CREATE TABLE citas(
 id_cita INT AUTO_INCREMENT PRIMARY KEY,
 id_cliente INT NOT NULL,
 id_servicio INT NOT NULL,
 id_horario INT NOT NULL,
 empleado VARCHAR(140),
 fecha DATE NOT NULL,
 observaciones TEXT,
 estado ENUM('Pendiente','Confirmada','En proceso','Finalizada','Cancelada','No asistio') DEFAULT 'Pendiente',
 pago ENUM('SINPE','Efectivo','Tarjeta','No aplica') DEFAULT 'No aplica',
 fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 UNIQUE(fecha,id_horario),
 FOREIGN KEY(id_cliente) REFERENCES clientes(id_cliente),
 FOREIGN KEY(id_servicio) REFERENCES servicios(id_servicio),
 FOREIGN KEY(id_horario) REFERENCES horarios(id_horario)
);

CREATE TABLE pedidos(
 id_pedido INT AUTO_INCREMENT PRIMARY KEY,
 id_cliente INT NOT NULL,
 numero_pedido VARCHAR(20) UNIQUE,
 direccion TEXT,
 metodo_pago ENUM('SINPE','Efectivo'),
 tipo_entrega ENUM('envio','retiro') DEFAULT 'envio',
 total DECIMAL(10,2),
 costo_envio DECIMAL(10,2) DEFAULT 0,
 observaciones TEXT,
 comprobante VARCHAR(255),
 estado ENUM('Pendiente','Pagado','Preparando','Entregado','Cancelado') DEFAULT 'Pendiente',
 fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(id_cliente) REFERENCES clientes(id_cliente)
);

CREATE TABLE detalle_pedido(
 id_detalle INT AUTO_INCREMENT PRIMARY KEY,
 id_pedido INT,id_producto INT,cantidad INT,
 precio_unitario DECIMAL(10,2),subtotal DECIMAL(10,2),
 FOREIGN KEY(id_pedido) REFERENCES pedidos(id_pedido) ON DELETE CASCADE,
 FOREIGN KEY(id_producto) REFERENCES productos(id_producto)
);

CREATE TABLE pagos(
 id_pago INT AUTO_INCREMENT PRIMARY KEY,
 numero_pago VARCHAR(30) UNIQUE,
 id_pedido INT NOT NULL,
 metodo ENUM('SINPE','SINPE Movil','Efectivo','Tarjeta de Debito','Tarjeta de Credito') DEFAULT 'SINPE Movil',
 monto DECIMAL(10,2),
 comprobante VARCHAR(255),
 referencia VARCHAR(100),
 observaciones TEXT,
 fecha_pago TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 estado ENUM('Pendiente','En revision','Aprobado','Rechazado','Reembolsado') DEFAULT 'Pendiente',
 FOREIGN KEY(id_pedido) REFERENCES pedidos(id_pedido) ON DELETE CASCADE
);

INSERT INTO administrador(nombre,usuario,correo,password,reset_token,reset_expires_at) VALUES('Administrador','admin','orlandogc2195@gmail.com','$2y$10$CAMBIAR_HASH',NULL,NULL);
INSERT INTO categorias(nombre) VALUES ('Shampoo'),('Acondicionadores'),('Tratamientos'),('Tintes'),('Barba'),('Peinado'),('Manicure'),('Pedicure'),('Accesorios');
INSERT INTO servicios(nombre,descripcion,precio,duracion) VALUES
('Corte Caballero','Corte',6000,30),('Corte Dama','Corte',9000,60),('Barba','Perfilado',4000,20),('Manicure','Servicio',10000,60),('Pedicure','Servicio',12000,60),('Balayage','Color',65000,180),('Keratina','Tratamiento',45000,180);
INSERT INTO horarios(hora) VALUES
('08:00:00'),('08:30:00'),('09:00:00'),('09:30:00'),('10:00:00'),('10:30:00'),('11:00:00'),('11:30:00'),('12:00:00'),('12:30:00'),('13:00:00'),('13:30:00'),('14:00:00'),('14:30:00'),('15:00:00'),('15:30:00'),('16:00:00'),('16:30:00'),('17:00:00'),('17:30:00');

CREATE INDEX idx_producto_nombre ON productos(nombre);
CREATE INDEX idx_producto_categoria ON productos(id_categoria);
CREATE INDEX idx_citas_fecha ON citas(fecha);
CREATE INDEX idx_citas_fecha_estado ON citas(fecha,estado);
CREATE INDEX idx_pedidos_fecha_estado ON pedidos(fecha,estado);
CREATE INDEX idx_pagos_estado_fecha ON pagos(estado,fecha_pago);
CREATE INDEX idx_admin_reset_token ON administrador(reset_token);

CREATE VIEW vw_productos AS
SELECT p.id_producto,p.codigo,p.nombre,c.nombre categoria,p.descripcion,p.precio,p.stock,p.estado
FROM productos p JOIN categorias c ON p.id_categoria=c.id_categoria;

CREATE VIEW vw_servicios AS
SELECT id_servicio,nombre,descripcion,precio,duracion,estado FROM servicios;

CREATE VIEW vw_citas AS
SELECT ci.id_cita,cl.nombre cliente,s.nombre servicio,h.hora,ci.fecha,ci.estado
FROM citas ci JOIN clientes cl ON ci.id_cliente=cl.id_cliente
JOIN servicios s ON ci.id_servicio=s.id_servicio
JOIN horarios h ON ci.id_horario=h.id_horario;

CREATE VIEW vw_pedidos AS
SELECT p.id_pedido,c.nombre cliente,p.metodo_pago,p.total,p.estado,p.fecha
FROM pedidos p JOIN clientes c ON p.id_cliente=c.id_cliente;

CREATE VIEW vw_pagos AS
SELECT pa.id_pago,c.nombre cliente,pe.id_pedido,pe.total,pa.metodo,pa.estado,pa.comprobante
FROM pagos pa JOIN pedidos pe ON pa.id_pedido=pe.id_pedido
JOIN clientes c ON pe.id_cliente=c.id_cliente;
