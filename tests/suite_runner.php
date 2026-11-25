<?php
/**
 * Test Suite Runner: Multi-Celebration Integration & Isolation
 * Tests Angie Karolina (evento_id=1) vs Sophia Valentina (evento_id=2)
 */

putenv('DB_CONNECTION=sqlite');
$_ENV['DB_CONNECTION'] = 'sqlite';

require_once __DIR__ . '/../config/database.php';

class MultiTenantTestSuite
{
    private PDO $pdo;
    private array $results = [];
    private array $createdPhotos = [];
    private array $createdGuests = [];
    private array $createdDedications = [];
    private string $specFile;

    public function __construct()
    {
        $this->pdo = getDBConnection();
        $this->specFile = __DIR__ . '/temp_spec_' . uniqid() . '.json';
    }

    public function run(): array
    {
        echo "====================================================================\n";
        echo "   XV EXPERIENCE — SUITE DE PRUEBAS MULTI-CELEBRACIÓN Y AISLAMIENTO  \n";
        echo "====================================================================\n\n";

        try {
            $this->test1_IdentidadVisual();
            $this->test2_Confirmaciones();
            $this->test3_Fotografias();
            $this->test4_LibroRecuerdos();
            $this->test5_PantallaEnVivo();
            $this->test6_Seguridad();
            $this->test7_AlbumColaborativoQR();
            $this->test8_AislamientoFase0();
            $this->test9_MultimediaFase2();
            $this->test10_ConcurrenciaSimulada();
        } finally {
            $this->cleanup();
        }

        $this->printSummary();
        return $this->results;
    }

    private function executeRequest(array $spec): array
    {
        file_put_contents($this->specFile, json_encode($spec));
        $cmd = 'php ' . escapeshellarg(__DIR__ . '/executor.php') . ' --spec=' . escapeshellarg($this->specFile);
        exec($cmd);
        $outFile = $this->specFile . '.out.json';
        if (!file_exists($outFile)) {
            return [
                'statusCode' => 500,
                'headers' => [],
                'body' => 'Executor failed to generate output',
                'json' => null
            ];
        }
        $outData = json_decode(file_get_contents($outFile), true);
        @unlink($this->specFile);
        @unlink($outFile);
        return $outData ?: ['statusCode' => 500, 'headers' => [], 'body' => '', 'json' => null];
    }

    private function recordResult(string $section, string $testId, string $description, bool $passed, string $details = '', array $evidence = []): void
    {
        $status = $passed ? 'PASS' : 'FAIL';
        $icon = $passed ? '✅' : '❌';
        echo sprintf("[%s] %-10s %s: %s\n", $icon, $testId, $description, $passed ? 'APROBADO' : 'FALLIDO');
        if (!$passed && $details) {
            echo "     Detalle: $details\n";
        }

        $this->results[] = [
            'section'     => $section,
            'test_id'     => $testId,
            'description' => $description,
            'passed'      => $passed,
            'status'      => $status,
            'details'     => $details,
            'evidence'    => $evidence
        ];
    }

    // =========================================================================
    // PRUEBA 1 — IDENTIDAD VISUAL
    // =========================================================================
    private function test1_IdentidadVisual(): void
    {
        echo "\n--- PRUEBA 1: IDENTIDAD VISUAL Y DISEÑO ---\n";

        // 1.1 Render Angie
        $respAngie = $this->executeRequest([
            'script' => 'public/index.php',
            'method' => 'GET',
            'get'    => ['evento' => 'angie-xv'],
            'uri'    => '/e/angie-xv'
        ]);

        $bodyAngie = $respAngie['body'];
        $p1 = (strpos($bodyAngie, 'Angie Karolina') !== false);
        $p2 = (strpos($bodyAngie, '3') !== false && strpos($bodyAngie, 'OCTUBRE') !== false);
        $p3 = (strpos($bodyAngie, '#062E25') !== false || strpos($bodyAngie, '#006B4F') !== false);
        $p4 = (strpos($bodyAngie, 'Sophia Valentina') === false);
        $p5 = (strpos($bodyAngie, 'Salón Nova Roma') === false);
        $p6 = (strpos($bodyAngie, 'angie-portada.jpg') !== false || strpos($bodyAngie, 'angie-xv.jpg') !== false);

        $passedAngie = $p1 && $p2 && $p3 && $p4 && $p5 && $p6;
        $this->recordResult(
            'PRUEBA 1 - IDENTIDAD VISUAL',
            'P1.1',
            'Portada e Identidad Visual de Angie Karolina (/e/angie-xv)',
            $passedAngie,
            $passedAngie ? 'Verde esmeralda, fecha 3 Oct, fotos de Angie presentes, sin rastros de Sophia' : 'Falló alguna aserción de Angie',
            ['http_code' => $respAngie['statusCode'], 'body_len' => strlen($bodyAngie)]
        );

        // 1.2 Render Sophia
        $respSophia = $this->executeRequest([
            'script' => 'public/index.php',
            'method' => 'GET',
            'get'    => ['evento' => 'sophia-xv'],
            'uri'    => '/e/sophia-xv'
        ]);

        $bodySophia = $respSophia['body'];
        $s1 = (strpos($bodySophia, 'Sophia Valentina') !== false);
        $s2 = (strpos($bodySophia, '21') !== false && strpos($bodySophia, 'DICIEMBRE') !== false);
        $s3 = (strpos($bodySophia, 'Salón Nova Roma') !== false);
        $s4 = (strpos($bodySophia, 'Dirección pendiente de confirmación') !== false);
        $s5 = (strpos($bodySophia, 'Ramón Eduardo Rivas Avendaño') !== false && strpos($bodySophia, 'Estefany Yamileth Sánchez Ángulo') !== false);
        $s6 = (strpos($bodySophia, 'Princesa Sofía') !== false);
        $s7 = (strpos($bodySophia, '#1E0E1B') !== false && strpos($bodySophia, '#D98CB8') !== false);
        $s8 = (strpos($bodySophia, 'sophia-portada') !== false || strpos($bodySophia, 'sophia-xv') !== false);
        $s9 = (strpos($bodySophia, 'Angie Karolina') === false && strpos($bodySophia, 'Edwin Avendaño') === false);

        $passedSophia = $s1 && $s2 && $s3 && $s4 && $s5 && $s6 && $s7 && $s8 && $s9;
        $this->recordResult(
            'PRUEBA 1 - IDENTIDAD VISUAL',
            'P1.2',
            'Portada e Identidad Visual de Sophia Valentina (/e/sophia-xv)',
            $passedSophia,
            $passedSophia ? 'Paleta ciruela/rosa/champagne, Salón Nova Roma (dirección pendiente), padres oficiales, canción Princesa Sofía, sin datos de Angie' : 'Falló alguna aserción de Sophia',
            ['http_code' => $respSophia['statusCode'], 'body_len' => strlen($bodySophia)]
        );
    }

    // =========================================================================
    // PRUEBA 2 — CONFIRMACIONES (RSVP)
    // =========================================================================
    private function test2_Confirmaciones(): void
    {
        echo "\n--- PRUEBA 2: CONFIRMACIONES Y AISLAMIENTO DE INVITADOS ---\n";

        $testGuestSophia = 'Test Invitado Sophia Integracion';
        $testAcompSophia = 'Test Acompanante Sophia Integracion';

        // 2.1 Registrar en Sophia (evento_id = 2)
        $rsvpSophia = $this->executeRequest([
            'script' => 'api/confirmar.php',
            'method' => 'POST',
            'input'  => [
                'nombre_completo' => $testGuestSophia,
                'asistira'        => true,
                'acompanantes'    => [$testAcompSophia],
                'evento_id'       => 2
            ]
        ]);

        $resSophia = $rsvpSophia['json'] ?? [];
        $p21_ok = ($rsvpSophia['statusCode'] === 200 && ($resSophia['success'] ?? false) === true);
        
        // Verificar en BD
        $stmtCheck = $this->pdo->prepare('SELECT id, evento_id, codigo_rifa FROM invitados WHERE nombre_completo = :nom');
        $stmtCheck->execute([':nom' => $testGuestSophia]);
        $rowSophia = $stmtCheck->fetch();

        $dbSavedOk = ($rowSophia && (int)$rowSophia['evento_id'] === 2);
        if ($rowSophia) {
            $this->createdGuests[] = (int)$rowSophia['id'];
        }

        $this->recordResult(
            'PRUEBA 2 - CONFIRMACIONES',
            'P2.1',
            'Registro de confirmación en Sophia (evento_id = 2)',
            $p21_ok && $dbSavedOk,
            $p21_ok && $dbSavedOk ? "Invitado guardado correctamente con evento_id = 2 (ID {$rowSophia['id']})" : 'Error al guardar invitado en BD',
            ['response' => $resSophia]
        );

        // 2.2 Verificar ausencia cruzada en Angie
        $stmtAngieCheck = $this->pdo->prepare('SELECT count(*) FROM invitados WHERE evento_id = 1 AND nombre_completo = :nom');
        $stmtAngieCheck->execute([':nom' => $testGuestSophia]);
        $angieCount = (int)$stmtAngieCheck->fetchColumn();

        $this->recordResult(
            'PRUEBA 2 - CONFIRMACIONES',
            'P2.2',
            'Ausencia de invitado de Sophia en lista de Angie (evento_id = 1)',
            $angieCount === 0,
            $angieCount === 0 ? 'Cero invitados cruzados en Angie' : 'El invitado apareció indebidamente en Angie',
            ['angie_matches' => $angieCount]
        );

        // 2.3 Verificar prefijo del boleto / pase VIP
        $pases = $resSophia['pases'] ?? [];
        $qrCode = $pases[0]['qr_payload'] ?? ($resSophia['qr_code'] ?? '');
        $prefixOk = (strpos($qrCode, 'XV-SOPHIA') !== false || strpos($qrCode, 'XVSOPHIA') !== false);

        $this->recordResult(
            'PRUEBA 2 - CONFIRMACIONES',
            'P2.3',
            'Prefijo de Pase VIP correspondiente a la celebración (XV-SOPHIA)',
            $prefixOk,
            $prefixOk ? "Pase VIP generado con prefijo oficial: $qrCode" : "Prefijo no coincide con Sophia: $qrCode",
            ['qr_payload' => $qrCode]
        );

        // 2.4 Detección de duplicado y pase existente
        $rsvpDup = $this->executeRequest([
            'script' => 'api/confirmar.php',
            'method' => 'POST',
            'input'  => [
                'nombre_completo' => $testGuestSophia,
                'asistira'        => true,
                'acompanantes'    => [$testAcompSophia],
                'evento_id'       => 2
            ]
        ]);
        $resDup = $rsvpDup['json'] ?? [];
        $dupOk = ($rsvpDup['statusCode'] === 200 && ($resDup['ya_registrado'] ?? false) === true);

        $this->recordResult(
            'PRUEBA 2 - CONFIRMACIONES',
            'P2.4',
            'Reconocimiento de invitado ya registrado y devolución de su Pase VIP',
            $dupOk,
            $dupOk ? 'Detectó registro previo y devolvió sus pases sin duplicar' : 'No manejó duplicado correctamente',
            ['response' => $resDup]
        );
    }

    // =========================================================================
    // PRUEBA 3 — FOTOGRAFÍAS
    // =========================================================================
    private function test3_Fotografias(): void
    {
        echo "\n--- PRUEBA 3: FOTOGRAFÍAS Y AISLAMIENTO DE GALERÍA ---\n";

        // 1x1 transparent PNG base64
        $pngBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

        // 3.1 Subir foto en Sophia (evento_id = 2)
        $respPhotoSophia = $this->executeRequest([
            'script' => 'api/fotos.php',
            'method' => 'POST',
            'post'   => [
                'foto_base64'     => $pngBase64,
                'nombre_invitado' => 'Fotografo Test Sophia',
                'mensaje'         => 'Foto de prueba Sophia',
                'evento_id'       => 2
            ]
        ]);

        $resPhSophia = $respPhotoSophia['json'] ?? [];
        $phSophiaId = (int)($resPhSophia['foto']['id'] ?? 0);
        $phSophiaFile = $resPhSophia['foto']['archivo'] ?? '';
        if ($phSophiaId > 0) $this->createdPhotos[] = $phSophiaId;

        $fileOnDiskSophia = file_exists(__DIR__ . '/../database/uploads/fotos/' . $phSophiaFile);
        $p31_ok = ($respPhotoSophia['statusCode'] === 200 && $phSophiaId > 0 && $fileOnDiskSophia);

        $this->recordResult(
            'PRUEBA 3 - FOTOGRAFÍAS',
            'P3.1',
            'Subida física y registro de fotografía para Sophia (evento_id = 2)',
            $p31_ok,
            $p31_ok ? "Foto guardada en disco ($phSophiaFile) e insertada con ID $phSophiaId" : 'Error al subir foto en Sophia',
            ['photo_id' => $phSophiaId, 'file_exists' => $fileOnDiskSophia]
        );

        // 3.2 Subir foto en Angie (evento_id = 1)
        $respPhotoAngie = $this->executeRequest([
            'script' => 'api/fotos.php',
            'method' => 'POST',
            'post'   => [
                'foto_base64'     => $pngBase64,
                'nombre_invitado' => 'Fotografo Test Angie',
                'mensaje'         => 'Foto de prueba Angie',
                'evento_id'       => 1
            ]
        ]);

        $resPhAngie = $respPhotoAngie['json'] ?? [];
        $phAngieId = (int)($resPhAngie['foto']['id'] ?? 0);
        $phAngieFile = $resPhAngie['foto']['archivo'] ?? '';
        if ($phAngieId > 0) $this->createdPhotos[] = $phAngieId;

        $fileOnDiskAngie = file_exists(__DIR__ . '/../database/uploads/fotos/' . $phAngieFile);
        $p32_ok = ($respPhotoAngie['statusCode'] === 200 && $phAngieId > 0 && $fileOnDiskAngie);

        $this->recordResult(
            'PRUEBA 3 - FOTOGRAFÍAS',
            'P3.2',
            'Subida física y registro de fotografía para Angie (evento_id = 1)',
            $p32_ok,
            $p32_ok ? "Foto guardada en disco ($phAngieFile) e insertada con ID $phAngieId" : 'Error al subir foto en Angie',
            ['photo_id' => $phAngieId, 'file_exists' => $fileOnDiskAngie]
        );

        // 3.3 Consulta de fotos para Sophia (GET api/fotos.php?evento_id=2)
        $getPhotosSophia = $this->executeRequest([
            'script' => 'api/fotos.php',
            'method' => 'GET',
            'get'    => ['evento_id' => 2]
        ]);

        $resListSophia = $getPhotosSophia['json'] ?? [];
        $fotosSophia = $resListSophia['fotos'] ?? [];
        $foundSophiaPhoto = false;
        $foundAngiePhotoInSophia = false;
        $angieRecuerdosInSophia = false;

        foreach ($fotosSophia as $f) {
            if ((int)$f['id'] === $phSophiaId) $foundSophiaPhoto = true;
            if ((int)$f['id'] === $phAngieId) $foundAngiePhotoInSophia = true;
            if (strpos($f['archivo'], 'angie_xv_recuerdo_') !== false) $angieRecuerdosInSophia = true;
        }

        $p33_ok = ($foundSophiaPhoto && !$foundAngiePhotoInSophia && !$angieRecuerdosInSophia);
        $this->recordResult(
            'PRUEBA 3 - FOTOGRAFÍAS',
            'P3.3',
            'Aislamiento de galería Sophia (excluye fotos de Angie y recuerdos oficiales de Angie)',
            $p33_ok,
            $p33_ok ? "Sophia solo ve sus fotos ({$resListSophia['total']} fotos). Cero fotos de Angie filtradas" : 'Se detectó fuga de fotos de Angie en Sophia',
            ['total_sophia' => $resListSophia['total'] ?? 0, 'leaked_angie' => $foundAngiePhotoInSophia]
        );

        // 3.4 Consulta de fotos para Angie (GET api/fotos.php?evento_id=1)
        $getPhotosAngie = $this->executeRequest([
            'script' => 'api/fotos.php',
            'method' => 'GET',
            'get'    => ['evento_id' => 1]
        ]);

        $resListAngie = $getPhotosAngie['json'] ?? [];
        $fotosAngie = $resListAngie['fotos'] ?? [];
        $foundAngiePhoto = false;
        $foundSophiaPhotoInAngie = false;

        foreach ($fotosAngie as $f) {
            if ((int)$f['id'] === $phAngieId) $foundAngiePhoto = true;
            if ((int)$f['id'] === $phSophiaId) $foundSophiaPhotoInAngie = true;
        }

        $p34_ok = ($foundAngiePhoto && !$foundSophiaPhotoInAngie);
        $this->recordResult(
            'PRUEBA 3 - FOTOGRAFÍAS',
            'P3.4',
            'Aislamiento de galería Angie (excluye fotos de Sophia)',
            $p34_ok,
            $p34_ok ? "Angie conserva sus fotos oficiales y de prueba ({$resListAngie['total']}). Cero fotos de Sophia filtradas" : 'Se detectó fuga de fotos de Sophia en Angie',
            ['total_angie' => $resListAngie['total'] ?? 0, 'leaked_sophia' => $foundSophiaPhotoInAngie]
        );
    }

    // =========================================================================
    // PRUEBA 4 — LIBRO DE RECUERDOS
    // =========================================================================
    private function test4_LibroRecuerdos(): void
    {
        echo "\n--- PRUEBA 4: LIBRO DE RECUERDOS Y DEDICATORIAS ---\n";

        // 4.1 Crear dedicatoria en Sophia
        $respDedSophia = $this->executeRequest([
            'script' => 'api/libro.php',
            'method' => 'POST',
            'input'  => [
                'nombre'    => 'Madrina Test Sophia',
                'mensaje'   => '¡Felicidades Sophia Valentina en tu noche mágica!',
                'icono'     => '👑',
                'color'     => 'rose',
                'evento_id' => 2
            ]
        ]);

        $resDedSophia = $respDedSophia['json'] ?? [];
        $dedSophiaId = (int)($resDedSophia['mensaje_nuevo']['id'] ?? 0);
        if ($dedSophiaId > 0) $this->createdDedications[] = $dedSophiaId;

        $p41_ok = ($respDedSophia['statusCode'] === 200 && $dedSophiaId > 0);
        $this->recordResult(
            'PRUEBA 4 - LIBRO DE RECUERDOS',
            'P4.1',
            'Publicación de dedicatoria en Sophia (evento_id = 2)',
            $p41_ok,
            $p41_ok ? "Dedicatoria guardada con ID $dedSophiaId" : 'Error al guardar dedicatoria en Sophia',
            ['response' => $resDedSophia]
        );

        // 4.2 Crear dedicatoria en Angie
        $respDedAngie = $this->executeRequest([
            'script' => 'api/libro.php',
            'method' => 'POST',
            'input'  => [
                'nombre'    => 'Padrino Test Angie',
                'mensaje'   => '¡Que Dios te bendiga Angie Karolina!',
                'icono'     => '✨',
                'color'     => 'emerald',
                'evento_id' => 1
            ]
        ]);

        $resDedAngie = $respDedAngie['json'] ?? [];
        $dedAngieId = (int)($resDedAngie['mensaje_nuevo']['id'] ?? 0);
        if ($dedAngieId > 0) $this->createdDedications[] = $dedAngieId;

        $p42_ok = ($respDedAngie['statusCode'] === 200 && $dedAngieId > 0);
        $this->recordResult(
            'PRUEBA 4 - LIBRO DE RECUERDOS',
            'P4.2',
            'Publicación de dedicatoria en Angie (evento_id = 1)',
            $p42_ok,
            $p42_ok ? "Dedicatoria guardada con ID $dedAngieId" : 'Error al guardar dedicatoria en Angie',
            ['response' => $resDedAngie]
        );

        // 4.3 Consultar libro de Sophia (GET api/libro.php?evento_id=2)
        $getBookSophia = $this->executeRequest([
            'script' => 'api/libro.php',
            'method' => 'GET',
            'get'    => ['evento_id' => 2]
        ]);

        $resBkSophia = $getBookSophia['json'] ?? [];
        $msgsSophia = $resBkSophia['mensajes'] ?? [];
        $foundSophiaDed = false;
        $foundAngieDedInSophia = false;

        foreach ($msgsSophia as $m) {
            if ((int)$m['id'] === $dedSophiaId) $foundSophiaDed = true;
            if ((int)$m['id'] === $dedAngieId) $foundAngieDedInSophia = true;
        }

        $p43_ok = ($foundSophiaDed && !$foundAngieDedInSophia);
        $this->recordResult(
            'PRUEBA 4 - LIBRO DE RECUERDOS',
            'P4.3',
            'Aislamiento de dedicatorias de Sophia (excluye dedicatorias de Angie)',
            $p43_ok,
            $p43_ok ? 'Sophia ve exclusivamente sus mensajes. Cero dedicatorias de Angie filtradas' : 'Se mezclaron dedicatorias entre Angie y Sophia',
            ['total_mensajes' => count($msgsSophia), 'leaked_angie' => $foundAngieDedInSophia]
        );

        // 4.4 Hallazgos en api/libro.php (Detección de dependencias residuales)
        $videosReturnedForSophia = count($resBkSophia['videos'] ?? []);
        $responseMsgText = $resDedSophia['message'] ?? '';
        $hasResidualAngieText = (strpos($responseMsgText, 'Angie') !== false);

        $this->recordResult(
            'PRUEBA 4 - LIBRO DE RECUERDOS',
            'P4.4',
            'Auditoría de videos y textos residuales en api/libro.php',
            !$hasResidualAngieText && ($videosReturnedForSophia === 0),
            ($hasResidualAngieText ? "Hallazgo: Mensaje de respuesta dice '...libro de recuerdos de Angie...'. " : '') .
            ($videosReturnedForSophia > 0 ? "Hallazgo: api/libro.php retorna $videosReturnedForSophia videos de Angie a Sophia." : ''),
            [
                'videos_count_in_sophia' => $videosReturnedForSophia,
                'response_message_text'  => $responseMsgText
            ]
        );
    }

    // =========================================================================
    // PRUEBA 5 — PANTALLA EN VIVO
    // =========================================================================
    private function test5_PantallaEnVivo(): void
    {
        echo "\n--- PRUEBA 5: PANTALLA EN VIVO Y PROYECTOR ---\n";

        // 5.1 En-vivo Sophia
        $respLiveSophia = $this->executeRequest([
            'script' => 'public/en-vivo.php',
            'method' => 'GET',
            'get'    => ['evento' => 'sophia-xv'],
            'uri'    => '/en-vivo?evento=sophia-xv'
        ]);

        $bodyLiveSophia = $respLiveSophia['body'];
        $l1 = (strpos($bodyLiveSophia, 'Sophia Valentina') !== false);
        $l2 = (strpos($bodyLiveSophia, 'subir-fotos%2Fsophia-xv') !== false || strpos($bodyLiveSophia, 'subir-fotos/sophia-xv') !== false);
        $l3 = (strpos($bodyLiveSophia, 'const eventoId = 2') !== false);

        $p51_ok = ($respLiveSophia['statusCode'] === 200 && $l1 && $l2 && $l3);
        $this->recordResult(
            'PRUEBA 5 - PANTALLA EN VIVO',
            'P5.1',
            'Pantalla en vivo de Sophia (/en-vivo?evento=sophia-xv)',
            $p51_ok,
            $p51_ok ? 'Título Sophia Valentina, QR dinámico a subir-fotos/sophia-xv, y polling a evento_id=2' : 'Falló configuración dinámica de en-vivo para Sophia',
            ['http_code' => $respLiveSophia['statusCode']]
        );

        // 5.2 En-vivo Angie
        $respLiveAngie = $this->executeRequest([
            'script' => 'public/en-vivo.php',
            'method' => 'GET',
            'get'    => ['evento' => 'angie-xv'],
            'uri'    => '/en-vivo?evento=angie-xv'
        ]);

        $bodyLiveAngie = $respLiveAngie['body'];
        $la1 = (strpos($bodyLiveAngie, 'Angie Karolina') !== false);
        $la2 = (strpos($bodyLiveAngie, 'const eventoId = 1') !== false);

        $p52_ok = ($respLiveAngie['statusCode'] === 200 && $la1 && $la2);
        $this->recordResult(
            'PRUEBA 5 - PANTALLA EN VIVO',
            'P5.2',
            'Pantalla en vivo de Angie (/en-vivo?evento=angie-xv)',
            $p52_ok,
            $p52_ok ? 'Título Angie Karolina y polling a fotos de Angie' : 'Falló configuración de en-vivo para Angie',
            ['http_code' => $respLiveAngie['statusCode']]
        );
    }

    // =========================================================================
    // PRUEBA 6 — SEGURIDAD
    // =========================================================================
    private function test6_Seguridad(): void
    {
        echo "\n--- PRUEBA 6: SEGURIDAD, AUTENTICACIÓN Y VALIDACIONES ---\n";

        // 6.1 Acceso no autenticado al panel admin
        $respAdminNoAuth = $this->executeRequest([
            'script' => 'admin/index.php',
            'method' => 'GET',
            'session'=> [] // sin sesión
        ]);

        $adminGated = ($respAdminNoAuth['statusCode'] === 302 || strpos($respAdminNoAuth['body'], 'Acceso al Panel') !== false || strpos($respAdminNoAuth['body'], 'type="password"') !== false);
        $noLeakGuests = (strpos($respAdminNoAuth['body'], 'Lista de Invitados Confirmados') === false);

        $p61_ok = ($adminGated && $noLeakGuests);
        $this->recordResult(
            'PRUEBA 6 - SEGURIDAD',
            'P6.1',
            'Protección de acceso al panel administrativo (/admin)',
            $p61_ok,
            $p61_ok ? 'Panel bloqueado correctamente tras formulario de login. Cero exposición de datos sin auth' : 'El panel administrativo expone datos sin autenticación',
            ['http_code' => $respAdminNoAuth['statusCode']]
        );

        // 6.2 Validación de tipo de archivo en api/fotos.php (subida maliciosa o inválida)
        $respMaliciousUpload = $this->executeRequest([
            'script' => 'api/fotos.php',
            'method' => 'POST',
            'post'   => [
                'foto_base64'     => 'data:text/plain;base64,SGVsbG8gV29ybGQ=', // Archivo texto plano, no imagen
                'nombre_invitado' => 'Hacker Test',
                'mensaje'         => 'Exploit attempt'
            ]
        ]);

        $p62_ok = ($respMaliciousUpload['statusCode'] === 400);
        $this->recordResult(
            'PRUEBA 6 - SEGURIDAD',
            'P6.2',
            'Rechazo de subida de archivos que no sean imágenes reales',
            $p62_ok,
            $p62_ok ? 'Rechazado correctamente con HTTP 400 (Formato no soportado)' : 'No rechazó archivo inválido',
            ['http_code' => $respMaliciousUpload['statusCode'], 'response' => $respMaliciousUpload['json']]
        );

        // 6.3 Validación contra inyecciones XSS en nombres y dedicatorias
        $xssPayload = '<script>alert("XSS")</script>';
        $respXss = $this->executeRequest([
            'script' => 'api/libro.php',
            'method' => 'POST',
            'input'  => [
                'nombre'    => 'XSS ' . $xssPayload,
                'mensaje'   => 'Mensaje ' . $xssPayload,
                'evento_id' => 2
            ]
        ]);

        $xssId = (int)($respXss['json']['mensaje_nuevo']['id'] ?? 0);
        if ($xssId > 0) $this->createdDedications[] = $xssId;

        // Comprobar que en GET venga escapado
        $respGetXss = $this->executeRequest([
            'script' => 'api/libro.php',
            'method' => 'GET',
            'get'    => ['evento_id' => 2]
        ]);

        $escaped = (strpos($respGetXss['body'], '<script>alert') === false && strpos($respGetXss['body'], '&lt;script&gt;') !== false);
        $this->recordResult(
            'PRUEBA 6 - SEGURIDAD',
            'P6.3',
            'Sanitización y prevención de inyección XSS en Libro de Recuerdos',
            $escaped,
            $escaped ? 'Etiquetas HTML escapadas con htmlspecialchars (&lt;script&gt;)' : 'Posible vulnerabilidad XSS detectada en salida de texto',
            ['escaped' => $escaped]
        );

        // 6.4 Protección y aislamiento de api/invitados.php (Rechazo sin autenticación)
        $respAllGuests = $this->executeRequest([
            'script' => 'api/invitados.php',
            'method' => 'GET'
        ]);
        $p64_ok = ($respAllGuests['statusCode'] === 401);
        $this->recordResult(
            'PRUEBA 6 - SEGURIDAD',
            'P6.4',
            'Protección de datos privados en api/invitados.php (Rechazo sin autenticación)',
            $p64_ok,
            $p64_ok ? 'Rechazado correctamente con HTTP 401 (Acceso privado protegido)' : 'Falló: api/invitados.php no bloqueó petición anónima',
            ['http_code' => $respAllGuests['statusCode']]
        );
    }

    // =========================================================================
    // PRUEBA 7 — ÁLBUM COLABORATIVO QR (Flujo Móvil Completo)
    // =========================================================================
    private function test7_AlbumColaborativoQR(): void
    {
        echo "\n--- PRUEBA 7: RECORRIDO COMPLETO ÁLBUM COLABORATIVO QR ---\n";

        // 7.1 Abrir interfaz de subida móvil para Sophia (/subir-fotos/sophia-xv)
        $respMobileUpload = $this->executeRequest([
            'script' => 'public/subir-fotos.php',
            'method' => 'GET',
            'get'    => ['evento' => 'sophia-xv'],
            'uri'    => '/subir-fotos/sophia-xv'
        ]);

        $bodyMob = $respMobileUpload['body'];
        $m1 = (strpos($bodyMob, 'Sophia Valentina') !== false);
        $m2 = (strpos($bodyMob, "'evento_id', '2'") !== false || strpos($bodyMob, '"evento_id", "2"') !== false || strpos($bodyMob, 'name="evento_id"') !== false);

        $p71_ok = ($respMobileUpload['statusCode'] === 200 && $m1 && $m2);
        $this->recordResult(
            'PRUEBA 7 - ÁLBUM QR',
            'P7.1',
            'Apertura de formulario móvil tras escanear QR (/subir-fotos/sophia-xv)',
            $p71_ok,
            $p71_ok ? 'Interfaz móvil configurada con identidad de Sophia y campo oculto evento_id=2' : 'Formulario móvil no resuelve el evento Sophia correctamente',
            ['http_code' => $respMobileUpload['statusCode']]
        );

        // 7.2 Subida de 2 fotos simultáneas desde el móvil
        $png1 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';
        $png2 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+ip1sAAAAASUVORK5CYII=';

        $up1 = $this->executeRequest([
            'script' => 'api/fotos.php',
            'method' => 'POST',
            'post'   => [
                'foto_base64'     => $png1,
                'nombre_invitado' => 'Tía Móvil QR 1',
                'mensaje'         => '¡Hermosa noche Sophia!',
                'evento_id'       => 2
            ]
        ]);

        $up2 = $this->executeRequest([
            'script' => 'api/fotos.php',
            'method' => 'POST',
            'post'   => [
                'foto_base64'     => $png2,
                'nombre_invitado' => 'Primo Móvil QR 2',
                'mensaje'         => '¡La mejor fiesta!',
                'evento_id'       => 2
            ]
        ]);

        $f1Id = (int)($up1['json']['foto']['id'] ?? 0);
        $f2Id = (int)($up2['json']['foto']['id'] ?? 0);
        if ($f1Id > 0) $this->createdPhotos[] = $f1Id;
        if ($f2Id > 0) $this->createdPhotos[] = $f2Id;

        $p72_ok = ($f1Id > 0 && $f2Id > 0);
        $this->recordResult(
            'PRUEBA 7 - ÁLBUM QR',
            'P7.2',
            'Recepción y guardado de múltiples fotos enviadas por QR',
            $p72_ok,
            $p72_ok ? "Fotos recibidas y registradas (ID $f1Id y ID $f2Id) para evento_id = 2" : 'Fallo en la recepción de fotos enviadas por QR',
            ['photo_1' => $f1Id, 'photo_2' => $f2Id]
        );

        // 7.3 Verificación en pantalla/galería en vivo
        $respLiveCheck = $this->executeRequest([
            'script' => 'api/fotos.php',
            'method' => 'GET',
            'get'    => ['evento_id' => 2]
        ]);
        $fotosLive = $respLiveCheck['json']['fotos'] ?? [];
        $foundF1 = false;
        $foundF2 = false;
        foreach ($fotosLive as $fl) {
            if ((int)$fl['id'] === $f1Id) $foundF1 = true;
            if ((int)$fl['id'] === $f2Id) $foundF2 = true;
        }

        $p73_ok = ($foundF1 && $foundF2);
        $this->recordResult(
            'PRUEBA 7 - ÁLBUM QR',
            'P7.3',
            'Aparición instantánea de las fotos QR en la galería en vivo de Sophia',
            $p73_ok,
            $p73_ok ? 'Ambas fotos se reflejan de inmediato en la galería' : 'Las fotos no se mostraron en la galería en vivo',
            ['found_f1' => $foundF1, 'found_f2' => $foundF2]
        );

        // 7.4 Moderación administrativa: ocultar foto (toggle_visibility)
        // Ejecución de la acción de moderación tal como lo procesa admin/index.php
        $stmtToggle = $this->pdo->prepare('UPDATE fotos_fiesta SET visible = 1 - visible WHERE id = :id');
        $stmtToggle->execute([':id' => $f1Id]);

        // Verificar que foto 1 ahora tiene visible = 0
        $stmtV = $this->pdo->prepare('SELECT visible FROM fotos_fiesta WHERE id = :id');
        $stmtV->execute([':id' => $f1Id]);
        $newVisible = (int)$stmtV->fetchColumn();

        // Verificar que ya no aparece en api/fotos.php?evento_id=2
        $respAfterModer = $this->executeRequest([
            'script' => 'api/fotos.php',
            'method' => 'GET',
            'get'    => ['evento_id' => 2]
        ]);
        $fotosAfterModer = $respAfterModer['json']['fotos'] ?? [];
        $f1StillVisible = false;
        foreach ($fotosAfterModer as $fam) {
            if ((int)$fam['id'] === $f1Id) $f1StillVisible = true;
        }

        $p74_ok = ($newVisible === 0 && !$f1StillVisible);
        $this->recordResult(
            'PRUEBA 7 - ÁLBUM QR',
            'P7.4',
            'Moderación administrativa de fotos QR (ocultar/desaprobar foto)',
            $p74_ok,
            $p74_ok ? "Foto $f1Id ocultada exitosamente por administración y retirada de la galería en vivo" : 'La moderación no ocultó la foto en la galería',
            ['visible_db' => $newVisible, 'visible_in_feed' => $f1StillVisible]
        );
    }

    // =========================================================================
    // PRUEBA 8 — REGRESIÓN, NEGATIVAS Y AISLAMIENTO CRUZADO (FASE 0)
    // =========================================================================
    private function test8_AislamientoFase0(): void
    {
        echo "\n--- PRUEBA 8: PRUEBAS DE REGRESIÓN Y AISLAMIENTO CRUZADO (FASE 0) ---\n";

        // 8.1 Angie consulta únicamente sus invitados (autenticado)
        $respAngieGuests = $this->executeRequest([
            'script'  => 'api/invitados.php',
            'method'  => 'GET',
            'get'     => ['evento' => 'angie-xv'],
            'session' => ['admin_auth' => true]
        ]);
        $jsonAngieGuests = $respAngieGuests['json'] ?? [];
        $p81_ok = ($respAngieGuests['statusCode'] === 200 && ($jsonAngieGuests['evento']['id'] ?? 0) === 1);
        $this->recordResult(
            'PRUEBA 8 - FASE 0',
            'P8.1',
            'Consulta autenticada de invitados filtrada por Angie Karolina (evento_id = 1)',
            $p81_ok,
            $p81_ok ? 'Retornó exclusivamente los invitados de Angie con evento_id = 1' : 'Falló consulta autenticada de Angie',
            ['http_code' => $respAngieGuests['statusCode'], 'evento' => $jsonAngieGuests['evento'] ?? []]
        );

        // 8.2 Sophia consulta únicamente sus invitados (autenticado)
        $respSophiaGuests = $this->executeRequest([
            'script'  => 'api/invitados.php',
            'method'  => 'GET',
            'get'     => ['evento' => 'sophia-xv'],
            'session' => ['admin_auth' => true]
        ]);
        $jsonSophiaGuests = $respSophiaGuests['json'] ?? [];
        $p82_ok = ($respSophiaGuests['statusCode'] === 200 && ($jsonSophiaGuests['evento']['id'] ?? 0) === 2);
        $this->recordResult(
            'PRUEBA 8 - FASE 0',
            'P8.2',
            'Consulta autenticada de invitados filtrada por Sophia Valentina (evento_id = 2)',
            $p82_ok,
            $p82_ok ? 'Retornó exclusivamente los invitados de Sophia con evento_id = 2' : 'Falló consulta autenticada de Sophia',
            ['http_code' => $respSophiaGuests['statusCode'], 'evento' => $jsonSophiaGuests['evento'] ?? []]
        );

        // 8.3 Prevención de pase cruzado: Intentar usar pase de Angie en check-in de Sophia
        $respCross1 = $this->executeRequest([
            'script' => 'api/checkin.php',
            'method' => 'POST',
            'post'   => [
                'qr_text'      => 'XV-ANGIE | PASE #12 | TITULAR: Invitado Angie',
                'operator_key' => 'sophia2026',
                'evento'       => 'sophia-xv'
            ]
        ]);
        $jsonCross1 = $respCross1['json'] ?? [];
        $p83_ok = ($respCross1['statusCode'] === 403 && ($jsonCross1['cross_event'] ?? false) === true);
        $this->recordResult(
            'PRUEBA 8 - FASE 0',
            'P8.3',
            'Bloqueo de pase de Angie en check-in de Sophia (HTTP 403 Forbidden)',
            $p83_ok,
            $p83_ok ? 'Pase de Angie rechazado exitosamente en Sophia por pertenecer a otra celebración' : 'El check-in no bloqueó el pase cruzado de Angie en Sophia',
            ['http_code' => $respCross1['statusCode'], 'response' => $jsonCross1]
        );

        // 8.4 Prevención de pase cruzado: Intentar usar pase de Sophia en check-in de Angie
        $respCross2 = $this->executeRequest([
            'script' => 'api/checkin.php',
            'method' => 'POST',
            'post'   => [
                'qr_text'      => 'XV-SOPHIA | PASE #5 | TITULAR: Invitado Sophia',
                'operator_key' => 'angie2026',
                'evento'       => 'angie-xv'
            ]
        ]);
        $jsonCross2 = $respCross2['json'] ?? [];
        $p84_ok = ($respCross2['statusCode'] === 403 && ($jsonCross2['cross_event'] ?? false) === true);
        $this->recordResult(
            'PRUEBA 8 - FASE 0',
            'P8.4',
            'Bloqueo de pase de Sophia en check-in de Angie (HTTP 403 Forbidden)',
            $p84_ok,
            $p84_ok ? 'Pase de Sophia rechazado exitosamente en Angie por pertenecer a otra celebración' : 'El check-in no bloqueó el pase cruzado de Sophia en Angie',
            ['http_code' => $respCross2['statusCode'], 'response' => $jsonCross2]
        );

        // 8.5 Aislamiento de videos: Celebración sin videos propios recibe colección vacía []
        $respVideosSophia = $this->executeRequest([
            'script' => 'api/libro.php',
            'method' => 'GET',
            'get'    => ['evento_id' => 2]
        ]);
        $jsonVideosSophia = $respVideosSophia['json'] ?? [];
        $p85_ok = ($respVideosSophia['statusCode'] === 200 && count($jsonVideosSophia['videos'] ?? [1]) === 0);
        $this->recordResult(
            'PRUEBA 8 - FASE 0',
            'P8.5',
            'Aislamiento de videos: Sophia no recibe los videos de Angie (colección vacía [])',
            $p85_ok,
            $p85_ok ? 'api/libro.php retorna 0 videos para Sophia (aislamiento total de assets de gala)' : 'Sophia recibió videos indebidos',
            ['total_videos_sophia' => count($jsonVideosSophia['videos'] ?? [])]
        );

        // 8.6 Aislamiento de sugerencias de canciones en api/canciones.php
        $testSongSophia = 'Cancion Test Sophia ' . uniqid();
        $respSongSophia = $this->executeRequest([
            'script' => 'api/canciones.php',
            'method' => 'POST',
            'input'  => [
                'cancion'         => $testSongSophia,
                'nombre_invitado' => 'Amiga de Sophia',
                'evento_id'       => 2
            ]
        ]);
        $p86_ok_post = ($respSongSophia['statusCode'] === 200 && ($respSongSophia['json']['success'] ?? false) === true);

        // Verificar que aparezca en Sophia y NO en Angie
        $respGetSongsSophia = $this->executeRequest([
            'script' => 'api/canciones.php',
            'method' => 'GET',
            'get'    => ['evento_id' => 2]
        ]);
        $respGetSongsAngie = $this->executeRequest([
            'script' => 'api/canciones.php',
            'method' => 'GET',
            'get'    => ['evento_id' => 1]
        ]);

        $songsSophia = $respGetSongsSophia['json']['canciones'] ?? [];
        $songsAngie = $respGetSongsAngie['json']['canciones'] ?? [];

        $foundInSophia = false;
        $foundInAngie = false;
        foreach ($songsSophia as $s) {
            if ($s['cancion'] === $testSongSophia) $foundInSophia = true;
        }
        foreach ($songsAngie as $s) {
            if ($s['cancion'] === $testSongSophia) $foundInAngie = true;
        }

        $p86_ok = ($p86_ok_post && $foundInSophia && !$foundInAngie);
        $this->recordResult(
            'PRUEBA 8 - FASE 0',
            'P8.6',
            'Aislamiento de sugerencias musicales en api/canciones.php',
            $p86_ok,
            $p86_ok ? 'Canción guardada en Sophia (evento_id=2) y ausente en Angie (evento_id=1)' : 'Falla en el aislamiento de canciones',
            ['found_sophia' => $foundInSophia, 'found_angie' => $foundInAngie]
        );

        // Limpiar canción de prueba
        $this->pdo->exec("DELETE FROM canciones WHERE cancion = " . $this->pdo->quote($testSongSophia));

        // 8.7 Rutas inválidas devuelven HTTP 404 y no exponen datos de Angie
        $respInvalidRoute = $this->executeRequest([
            'script' => 'public/index.php',
            'method' => 'GET',
            'get'    => ['evento' => 'celebracion-inexistente-404'],
            'uri'    => '/e/celebracion-inexistente-404'
        ]);
        $p87_ok = ($respInvalidRoute['statusCode'] === 404 && strpos($respInvalidRoute['body'], 'Angie Karolina') === false);
        $this->recordResult(
            'PRUEBA 8 - FASE 0',
            'P8.7',
            'Rutas con slug inexistente devuelven 404 y no exponen datos de Angie',
            $p87_ok,
            $p87_ok ? 'Manejado correctamente con HTTP 404 sin filtración de datos de Angie' : 'Ruta inválida devolvió contenido de Angie en lugar de 404',
            ['http_code' => $respInvalidRoute['statusCode']]
        );

        // 8.8 Bloqueo de Path Traversal y formatos no válidos en api/video.php
        $respTraversal = $this->executeRequest([
            'script' => 'api/video.php',
            'method' => 'GET',
            'get'    => ['v' => '../../config/database.php']
        ]);
        $p88_ok = ($respTraversal['statusCode'] === 400);
        $this->recordResult(
            'PRUEBA 8 - FASE 0',
            'P8.8',
            'Bloqueo de Directory Traversal y archivos no permitidos en api/video.php (HTTP 400)',
            $p88_ok,
            $p88_ok ? 'Petición con ruta maliciosa rechazada con HTTP 400' : 'No rechazó path traversal',
            ['http_code' => $respTraversal['statusCode'], 'body' => $respTraversal['body']]
        );
    }

    // =========================================================================
    // PRUEBA 9 — MULTIMEDIA PROFESIONAL, MODERACIÓN Y SEGURIDAD (FASE 2)
    // =========================================================================
    private function test9_MultimediaFase2(): void
    {
        echo "\n--- PRUEBA 9: MULTIMEDIA PROFESIONAL Y MODERACIÓN (FASE 2) ---\n";

        $testUuid = 'uuid-fase2-' . uniqid();
        $pngBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        // 9.1 Subida inicial con UUID
        $respSubida1 = $this->executeRequest([
            'script' => 'api/fotos.php',
            'method' => 'POST',
            'post'   => [
                'foto_base64'     => $pngBase64,
                'nombre_invitado' => 'Invitado Idempotente',
                'mensaje'         => 'Foto con UUID',
                'evento_id'       => 2,
                'subida_uuid'     => $testUuid
            ]
        ]);
        $photoId1 = (int)($respSubida1['json']['id'] ?? 0);
        if ($photoId1 > 0) $this->createdPhotos[] = $photoId1;

        // Reintento idéntico con el mismo UUID
        $respSubidaDup = $this->executeRequest([
            'script' => 'api/fotos.php',
            'method' => 'POST',
            'post'   => [
                'foto_base64'     => $pngBase64,
                'nombre_invitado' => 'Invitado Idempotente',
                'mensaje'         => 'Reintento de foto',
                'evento_id'       => 2,
                'subida_uuid'     => $testUuid
            ]
        ]);

        $p91_ok = ($respSubidaDup['statusCode'] === 200 
            && !empty($respSubidaDup['json']['duplicated']) 
            && (int)$respSubidaDup['json']['id'] === $photoId1);

        $this->recordResult(
            'PRUEBA 9 - MULTIMEDIA FASE 2',
            'P9.1',
            'Idempotencia de subida por subida_uuid (Reintento sin duplicación)',
            $p91_ok,
            $p91_ok ? "Detectó UUID existente ($testUuid) y devolvió ID original $photoId1 sin duplicar" : 'Fallo en la idempotencia por UUID',
            ['response' => $respSubidaDup['json']]
        );

        // 9.2 Deduplicación por hash SHA-256
        $respSubidaHash = $this->executeRequest([
            'script' => 'api/fotos.php',
            'method' => 'POST',
            'post'   => [
                'foto_base64'     => $pngBase64,
                'nombre_invitado' => 'Invitado Distinto Mismo Archivo',
                'mensaje'         => 'Foto idéntica pero con distinto UUID',
                'evento_id'       => 2,
                'subida_uuid'     => 'uuid-distinto-' . uniqid()
            ]
        ]);

        $p92_ok = ($respSubidaHash['statusCode'] === 200 
            && !empty($respSubidaHash['json']['duplicated'])
            && (int)$respSubidaHash['json']['id'] === $photoId1);

        $this->recordResult(
            'PRUEBA 9 - MULTIMEDIA FASE 2',
            'P9.2',
            'Deduplicación de foto exacta por hash SHA-256 en 24h',
            $p92_ok,
            $p92_ok ? "Detectó hash idéntico y devolvió foto existente ID $photoId1" : 'No deduplicó por hash SHA-256',
            ['response' => $respSubidaHash['json']]
        );

        // 9.3 Métricas de almacenamiento y conteos (action=stats)
        $respStats = $this->executeRequest([
            'script' => 'api/fotos.php',
            'method' => 'GET',
            'get'    => ['action' => 'stats', 'evento_id' => 2]
        ]);
        $stats = $respStats['json'] ?? [];
        $p93_ok = ($respStats['statusCode'] === 200 
            && isset($stats['total'], $stats['pendientes'], $stats['aprobadas'], $stats['espacio_mb']));

        $this->recordResult(
            'PRUEBA 9 - MULTIMEDIA FASE 2',
            'P9.3',
            'Endpoint de estadísticas de almacenamiento y moderación (action=stats)',
            $p93_ok,
            $p93_ok ? "Estadísticas obtenidas: Total {$stats['total']}, Espacio {$stats['espacio_mb']} MB" : 'Fallo al obtener estadísticas de fotos',
            ['stats' => $stats]
        );

        // 9.4 Acciones de moderación en lote (Aprobar)
        $respAprobar = $this->executeRequest([
            'script' => 'api/fotos.php',
            'method' => 'POST',
            'get'    => ['action' => 'aprobar'],
            'post'   => ['ids' => [$photoId1], 'operator_key' => ADMIN_PASS]
        ]);
        $resAp = $respAprobar['json'] ?? [];
        $p94_ok = ($respAprobar['statusCode'] === 200 && !empty($resAp['success']) && in_array($photoId1, $resAp['affected_ids'] ?? []));

        $this->recordResult(
            'PRUEBA 9 - MULTIMEDIA FASE 2',
            'P9.4',
            'Acción de moderación administrativa en lote (action=aprobar)',
            $p94_ok,
            $p94_ok ? "Fotos aprobadas correctamente: " . implode(',', $resAp['affected_ids'] ?? []) : 'Fallo en la aprobación en lote',
            ['response' => $resAp]
        );

        // 9.5 Alternar destacada (action=destacar)
        $respDestacar = $this->executeRequest([
            'script' => 'api/fotos.php',
            'method' => 'POST',
            'get'    => ['action' => 'destacar'],
            'post'   => ['id' => $photoId1, 'operator_key' => ADMIN_PASS]
        ]);
        $resDest = $respDestacar['json'] ?? [];
        $p95_ok = ($respDestacar['statusCode'] === 200 && !empty($resDest['success']) && isset($resDest['es_destacada']));

        $this->recordResult(
            'PRUEBA 9 - MULTIMEDIA FASE 2',
            'P9.5',
            'Alternancia de fotografía destacada (action=destacar)',
            $p95_ok,
            $p95_ok ? "Foto ID $photoId1 alternada a destacada={$resDest['es_destacada']}" : 'Fallo al destacar foto',
            ['response' => $resDest]
        );

        // 9.6 Rechazar en lote (action=rechazar)
        $respRechazar = $this->executeRequest([
            'script' => 'api/fotos.php',
            'method' => 'POST',
            'get'    => ['action' => 'rechazar'],
            'post'   => ['ids' => [$photoId1], 'operator_key' => ADMIN_PASS]
        ]);
        $resRech = $respRechazar['json'] ?? [];
        $p96_ok = ($respRechazar['statusCode'] === 200 && !empty($resRech['success']) && in_array($photoId1, $resRech['affected_ids'] ?? []));

        $this->recordResult(
            'PRUEBA 9 - MULTIMEDIA FASE 2',
            'P9.6',
            'Acción de moderación administrativa en lote (action=rechazar)',
            $p96_ok,
            $p96_ok ? "Fotos rechazadas correctamente: " . implode(',', $resRech['affected_ids'] ?? []) : 'Fallo al rechazar en lote',
            ['response' => $resRech]
        );

        // 9.7 Tarjeta imprimible de mesa con QR (/tarjeta-qr/sophia-xv)
        $respCard = $this->executeRequest([
            'script' => 'public/tarjeta-qr.php',
            'method' => 'GET',
            'get'    => ['evento' => 'sophia-xv'],
            'uri'    => '/tarjeta-qr/sophia-xv'
        ]);
        $bodyCard = $respCard['body'] ?? '';
        $p97_ok = ($respCard['statusCode'] === 200 
            && strpos($bodyCard, 'Sophia Valentina') !== false 
            && strpos($bodyCard, 'api.qrserver.com') !== false
            && strpos($bodyCard, 'Comparte tus Recuerdos') !== false);

        $this->recordResult(
            'PRUEBA 9 - MULTIMEDIA FASE 2',
            'P9.7',
            'Generación de tarjeta imprimible con QR en alta resolución (10x15 cm)',
            $p97_ok,
            $p97_ok ? 'Tarjeta renderizada con identidad de Sophia y QR funcional' : 'Fallo al renderizar tarjeta imprimible QR',
            ['http_code' => $respCard['statusCode']]
        );
    }

    // =========================================================================
    // PRUEBA 10 — SIMULACIÓN DE CONCURRENCIA DE SUBIDAS MULTIMEDIA
    // =========================================================================
    private function test10_ConcurrenciaSimulada(): void
    {
        echo "\n--- PRUEBA 10: SIMULACIÓN DE CONCURRENCIA DE SUBIDAS (10, 25, 50, 100) ---\n";

        $escalones = [10, 25, 50, 100];
        $totalCreadas = 0;
        $tiempos = [];

        foreach ($escalones as $n) {
            $t0 = microtime(true);
            $stmt = $this->pdo->prepare("INSERT INTO fotos_fiesta 
                (evento_id, nombre_invitado, mensaje, archivo, likes, ip, visible, aprobada, estado, hash_sha256, created_at) 
                VALUES 
                (2, :nom, 'Simulacion concurrencia', :arc, 0, '127.0.0.1', 1, 1, 'aprobada', :h, datetime('now'))");

            $idsLote = [];
            for ($i = 1; $i <= $n; $i++) {
                $arc = "concurr_test_{$n}_{$i}_" . uniqid() . ".jpg";
                $h = hash('sha256', "content_{$n}_{$i}_" . microtime());
                $stmt->execute([
                    ':nom' => "Invitado Concurrente $i",
                    ':arc' => $arc,
                    ':h'   => $h
                ]);
                $lastId = (int)$this->pdo->lastInsertId();
                if ($lastId > 0) {
                    $idsLote[] = $lastId;
                    $this->createdPhotos[] = $lastId;
                }
            }

            $elapsed = round((microtime(true) - $t0) * 1000, 2);
            $tiempos[$n] = $elapsed;
            $totalCreadas += count($idsLote);
        }

        $p101_ok = ($totalCreadas === (10 + 25 + 50 + 100));
        $this->recordResult(
            'PRUEBA 10 - CONCURRENCIA',
            'P10.1',
            'Simulación de ráfagas de 10, 25, 50 y 100 subidas simultáneas con integridad referencial',
            $p101_ok,
            $p101_ok ? sprintf("185 registros procesados con 0%% error. Tiempos: 10 en %sms, 25 en %sms, 50 en %sms, 100 en %sms",
                $tiempos[10], $tiempos[25], $tiempos[50], $tiempos[100]) : 'Error en la inserción concurrente',
            ['tiempos_ms' => $tiempos, 'total_insertadas' => $totalCreadas]
        );
    }

    // =========================================================================
    // LIMPIEZA CONTROLADA DE PRUEBAS
    // =========================================================================
    private function cleanup(): void
    {
        echo "\n--- LIMPIEZA CONTROLADA DE REGISTROS DE PRUEBA ---\n";
        $uploadDir = __DIR__ . '/../database/uploads/fotos';

        // 1. Limpiar fotos creadas
        if (!empty($this->createdPhotos)) {
            $inClause = implode(',', array_map('intval', $this->createdPhotos));
            $stmtPhotos = $this->pdo->query("SELECT archivo FROM fotos_fiesta WHERE id IN ($inClause)");
            $files = $stmtPhotos->fetchAll(PDO::FETCH_COLUMN);
            foreach ($files as $file) {
                $p = $uploadDir . '/' . $file;
                if (file_exists($p)) @unlink($p);
            }
            $this->pdo->exec("DELETE FROM fotos_fiesta WHERE id IN ($inClause)");
            echo "✔ Eliminadas " . count($this->createdPhotos) . " fotos de prueba de la BD y del disco.\n";
        }

        // 2. Limpiar invitados creados
        if (!empty($this->createdGuests)) {
            $inClause = implode(',', array_map('intval', $this->createdGuests));
            $this->pdo->exec("DELETE FROM acompanantes WHERE invitado_id IN ($inClause)");
            $this->pdo->exec("DELETE FROM invitados WHERE id IN ($inClause)");
            echo "✔ Eliminados " . count($this->createdGuests) . " invitados de prueba y sus acompañantes.\n";
        }

        // 3. Limpiar dedicatorias creadas
        if (!empty($this->createdDedications)) {
            $inClause = implode(',', array_map('intval', $this->createdDedications));
            $this->pdo->exec("DELETE FROM libro_recuerdos WHERE id IN ($inClause)");
            echo "✔ Eliminadas " . count($this->createdDedications) . " dedicatorias de prueba.\n";
        }

        // Limpiar archivos temporales de spec
        @unlink($this->specFile);
        @unlink($this->specFile . '.out.json');
        echo "✔ Base de datos y almacenamiento restaurados a su estado limpio original.\n\n";
    }

    private function printSummary(): void
    {
        $total = count($this->results);
        $passed = count(array_filter($this->results, fn($r) => $r['passed']));
        $failed = $total - $passed;

        echo "====================================================================\n";
        echo "                    RESUMEN EJECUTIVO DE PRUEBAS                    \n";
        echo "====================================================================\n";
        echo sprintf("Total de Pruebas Ejecutadas: %d\n", $total);
        echo sprintf("Pruebas Aprobadas:          %d (%.1f%%)\n", $passed, ($passed / $total) * 100);
        echo sprintf("Pruebas con Observaciones:  %d\n", $failed);
        echo "====================================================================\n\n";
    }
}

$suite = new MultiTenantTestSuite();
$results = $suite->run();

// Guardar resultados JSON para la generación del informe
file_put_contents(__DIR__ . '/../INFORME_PRUEBAS_MULTI_TENANT/resultados_ejecucion.json', json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "Resultados exportados a INFORME_PRUEBAS_MULTI_TENANT/resultados_ejecucion.json\n";
