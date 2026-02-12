// js/app.js

/**
 * Guarda un objeto en localStorage
 * @param {string} key - Clave del ítem (ej: 'titular', 'beneficiario')
 * @param {object} data - Datos a guardar
 */
function guardarDatos(key, data) {
    localStorage.setItem(key, JSON.stringify(data));
    console.log(`Datos guardados en ${key}:`, data);
}

/**
 * Recupera datos de localStorage
 * @param {string} key - Clave del ítem
 * @returns {object|null} - Datos recuperados o null si no existe
 */
function cargarDatos(key) {
    const data = localStorage.getItem(key);
    return data ? JSON.parse(data) : null;
}

/**
 * Rellena un formulario con datos de un objeto
 * @param {HTMLFormElement} form - El elemento del formulario
 * @param {object} data - Los datos a rellenar (las claves deben coincidir con los IDs de los inputs)
 */
function rellenarFormulario(form, data) {
    if (!data) return;
    Object.keys(data).forEach(key => {
        const input = form.querySelector(`#${key}`);
        if (input) {
            input.value = data[key];
        }
    });
}

/**
 * Limpia todos los datos del proceso actual
 */
function limpiarTodo() {
    localStorage.removeItem('titular');
    localStorage.removeItem('beneficiario');
    localStorage.removeItem('inmueble');
    localStorage.removeItem('compraventa');
    console.log("Datos limpiados.");
}

// --- SIMULACIÓN BASE DE DATOS LOCAL ---

/**
 * Guarda un expediente completo en la "Base de Datos" (LocalStorage)
 * @param {object} expediente - Objeto con titular, beneficiario, inmueble, compraventa
 */
function guardarClienteDB(expediente) {
    let db = JSON.parse(localStorage.getItem('db_clientes')) || [];
    // Generar ID único simple
    expediente.id = Date.now().toString();
    expediente.fechaRegistro = new Date().toLocaleDateString();
    expediente.abonos = []; // Inicializar historial de abonos

    db.push(expediente);
    localStorage.setItem('db_clientes', JSON.stringify(db));
    console.log("Cliente guardado en DB local:", expediente);
    return expediente.id;
}

/**
 * Obtiene todos los clientes
 */
function obtenerClientesDB() {
    return JSON.parse(localStorage.getItem('db_clientes')) || [];
}

/**
 * Busca cliente por ID
 */
function obtenerClientePorId(id) {
    const db = obtenerClientesDB();
    return db.find(c => c.id === id);
}

/**
 * Registra un abono a un cliente
 */
function registrarAbonoDB(clienteId, abono) {
    let db = obtenerClientesDB();
    const index = db.findIndex(c => c.id === clienteId);

    if (index !== -1) {
        if (!db[index].abonos) db[index].abonos = [];
        // Agregar abono con fecha y monto
        db[index].abonos.push(abono);

        // Actualizar saldo en compraventa (opcional, pero ayuda a la vista)
        // Nota: El saldo real se debería calcular sumando cargos y restando abonos en el momento

        localStorage.setItem('db_clientes', JSON.stringify(db));
        return true;
    }
    return false;
}

/**
 * Actualiza un abono existente
 */
function actualizarAbonoDB(clienteId, indexAbono, nuevoAbono) {
    let db = obtenerClientesDB();
    const index = db.findIndex(c => c.id === clienteId);

    if (index !== -1 && db[index].abonos && db[index].abonos[indexAbono]) {
        db[index].abonos[indexAbono] = nuevoAbono;
        localStorage.setItem('db_clientes', JSON.stringify(db));
        return true;
    }
    return false;
}
