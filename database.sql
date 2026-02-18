-- Crear base de datos
CREATE DATABASE IF NOT EXISTS registro_db;
USE registro_db;

-- Tabla 1: Titulares
CREATE TABLE IF NOT EXISTS Titulares (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150),
    direccion VARCHAR(255),
    telefono VARCHAR(50),
    dpi VARCHAR(50),
    nit VARCHAR(50),
    fecha_nacimiento DATE,
    edad INT,
    estado_civil VARCHAR(50),
    nacionalidad VARCHAR(50),
    profesion VARCHAR(100),
    empresa VARCHAR(100),
    direccion_trabajo VARCHAR(255),
    telefonos_trabajo VARCHAR(100),
    puesto VARCHAR(100),
    tiempo_trabajo VARCHAR(50),
    salario DECIMAL(10, 2),
    otros_ingresos DECIMAL(10, 2),
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabla 2: Beneficiarios
CREATE TABLE IF NOT EXISTS Beneficiarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titular_id INT,
    nombre VARCHAR(150),
    direccion VARCHAR(255),
    dpi VARCHAR(50),
    telefonos VARCHAR(100),
    email VARCHAR(100),
    FOREIGN KEY (titular_id) REFERENCES Titulares(id) ON DELETE CASCADE
);

-- Tabla 3: Inmuebles
CREATE TABLE IF NOT EXISTS Inmuebles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titular_id INT,
    lotes VARCHAR(100),
    manzana VARCHAR(50),
    area VARCHAR(50),
    finca VARCHAR(50),
    folio VARCHAR(50),
    libro VARCHAR(50),
    de_lugar VARCHAR(100),
    FOREIGN KEY (titular_id) REFERENCES Titulares(id) ON DELETE SET NULL
);

-- Tabla 4: Compraventas
CREATE TABLE IF NOT EXISTS Compraventas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titular_id INT,
    inmueble_id INT,
    precio_terreno DECIMAL(12, 2),
    gastos_urbanizacion DECIMAL(12, 2),
    total DECIMAL(12, 2),
    otros_descuentos DECIMAL(12, 2),
    pago_inicial DECIMAL(12, 2),
    saldo DECIMAL(12, 2),
    suma_pagos_fraccionados DECIMAL(12, 2),
    FOREIGN KEY (titular_id) REFERENCES Titulares(id),
    FOREIGN KEY (inmueble_id) REFERENCES Inmuebles(id)
);

-- Tabla 5: Pagos Fraccionados (Plan de Pagos)
CREATE TABLE IF NOT EXISTS PagosFraccionados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    compraventa_id INT,
    numero_pago INT,
    fecha DATE,
    cantidad DECIMAL(10, 2),
    FOREIGN KEY (compraventa_id) REFERENCES Compraventas(id) ON DELETE CASCADE
);

-- Tabla 6: Abonos (Historial de Pagos Reales)
CREATE TABLE IF NOT EXISTS Abonos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    compraventa_id INT,
    fecha DATE,
    recibo VARCHAR(50),
    monto DECIMAL(10, 2),
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (compraventa_id) REFERENCES Compraventas(id) ON DELETE CASCADE
);
