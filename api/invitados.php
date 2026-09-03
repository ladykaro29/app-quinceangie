<?php
/**
 * API: Listar Invitados Confirmados (GET)
 * Invitación XV Años - Angie Karolina Avendaño Rivera
 * 
 * Responde JSON con la lista completa de invitados y sus acompañantes,
 * además del conteo total de personas que asistirán.
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido. Use GET.']);
    exit;
}

require_once __DIR__ . '/../config/database.php';

try {
    $pdo = getDBConnection();

    // Obtener todos los invitados
    $stmtInvitados = $pdo->query(
        'SELECT id, nombre_completo, asistira, created_at 
         FROM invitados 
         ORDER BY created_at DESC'
    );
    $invitados = $stmtInvitados->fetchAll();

    // Obtener todos los acompañantes agrupados por invitado_id
    $stmtAcomp = $pdo->query(
        'SELECT invitado_id, nombre_completo 
         FROM acompanantes 
         ORDER BY id ASC'
    );
    $todosAcompanantes = $stmtAcomp->fetchAll();

    // Agrupar acompañantes por invitado_id
    $acompPorInvitado = [];
    foreach ($todosAcompanantes as $acomp) {
        $acompPorInvitado[$acomp['invitado_id']][] = $acomp['nombre_completo'];
    }

    // Construir respuesta con invitados y sus acompañantes
    $resultado = [];
    $totalAsistentes = 0;
    $totalInvitadosConfirmados = 0;

    foreach ($invitados as $inv) {
        $acomps = $acompPorInvitado[$inv['id']] ?? [];
        $invData = [
            'id'              => (int)$inv['id'],
            'nombre_completo' => $inv['nombre_completo'],
            'asistira'        => (bool)$inv['asistira'],
            'acompanantes'    => $acomps,
            'created_at'      => $inv['created_at'],
        ];
        $resultado[] = $invData;

        if ($inv['asistira']) {
            $totalInvitadosConfirmados++;
            $totalAsistentes += 1 + count($acomps); // invitado + acompañantes
        }
    }

    http_response_code(200);
    echo json_encode([
        'success'                    => true,
        'invitados'                  => $resultado,
        'total_registros'            => count($invitados),
        'total_invitados_confirmados'=> $totalInvitadosConfirmados,
        'total_asistentes'           => $totalAsistentes,
    ]);

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Error al obtener la lista de invitados.'
    ]);
    error_log('Error en invitados.php: ' . $e->getMessage());

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Ocurrió un error inesperado.'
    ]);
    error_log('Error en invitados.php: ' . $e->getMessage());
}
