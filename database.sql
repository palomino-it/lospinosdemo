-- Crear base de datos
CREATE DATABASE IF NOT EXISTS registro_db;
USE registro_db;

-- Tabla 1: Titulares
CREATE TABLE IF NOT EXISTS Titulares (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150),
    direccion VARCHAR(255),
    telefono VARCHAR(50),
    dpi VARCHAR(50) UNIQUE,  -- DPI único (PK lógica)
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

-- (Beneficiarios y Titulares_Beneficiarios... se mantienen igual)

-- Tabla 3: Inventario de Lotes (Nuevo Maestro de Inmuebles)
CREATE TABLE IF NOT EXISTS InventarioLotes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    manzana VARCHAR(50),
    lote VARCHAR(50),
    area VARCHAR(50),
    finca VARCHAR(50),
    folio VARCHAR(50),
    libro VARCHAR(50),
    valor_vara2 DECIMAL(10, 2), -- Precio por vara cuadrada
    precio_total DECIMAL(12, 2),
    estado ENUM('DISPONIBLE', 'RESERVADO', 'VENDIDO') DEFAULT 'DISPONIBLE',
    UNIQUE KEY unique_ubicacion (manzana, lote),
    UNIQUE KEY unique_registro (finca, folio, libro)
);

-- Tabla 4: Inmuebles (Historial de Venta)
-- Se mantiene para compatibilidad, pero idealmente debería apuntar al inventario
CREATE TABLE IF NOT EXISTS Inmuebles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titular_id INT,
    inventario_id INT, -- Vinculación con el inventario
    lotes VARCHAR(100), -- Se guarda copia por si cambia el inventario
    manzana VARCHAR(50),
    area VARCHAR(50),
    finca VARCHAR(50),
    folio VARCHAR(50),
    libro VARCHAR(50),
    de_lugar VARCHAR(100),
    FOREIGN KEY (titular_id) REFERENCES Titulares(id) ON DELETE SET NULL,
    FOREIGN KEY (inventario_id) REFERENCES InventarioLotes(id) ON DELETE SET NULL
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
