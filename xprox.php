<?php
// Desactivar errores y límites de tiempo para flujo continuo
error_reporting(0);
ini_set('display_errors', 0);
set_time_limit(0); 

// Permitir acceso desde cualquier origen (CORS)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// --- CONFIGURACIÓN DE CANALES ---
$canales = [
    "espn" => "http://bantel-cdn1.iptvperu.tv:1935/btnscrtn/espn-mux/playlist.m3u8",
    "espn2"  => "http://bantel-cdn1.iptvperu.tv:1935/btnscrtn/espn2-mux/playlist.m3u8",
    "espn3"  => "http://bantel-cdn1.iptvperu.tv:1935/btnscrtn/espn3-mux/playlist.m3u8",
    "tntsportscl"  => "http://bantel-cdn1.iptvperu.tv:1935/btnscrtn/sportpremium.stream/playlist.m3u8",
    "gourmet"  => "http://bantel-cdn1.iptvperu.tv:1935/bnkjrt/gourmet.stream/playlist.m3u8",
    "hboxtreme"  => "http://bantel-cdn1.iptvperu.tv:1935/btnscrtn/HBO_Xtreme/playlist.m3u8",
    "global"  => "http://bantel-cdn1.iptvperu.tv:1935/btnscrtn/global.stream/playlist.m3u8"
];

$mi_script = "xproxy.php"; 

// 1. Determinar qué estamos pidiendo (ID de canal o URL directa de segmento)
$id = $_GET['id'] ?? '';
$url_param = $_GET['url'] ?? '';

if (!empty($id) && isset($canales[$id])) {
    $url_target = $canales[$id];
} elseif (!empty($url_param)) {
    $url_target = $url_param;
} else {
    header("HTTP/1.1 404 Not Found");
    die("Canal no especificado o no encontrado.");
}

// 2. Manejo de Segmentos de Video (.ts) -> TRANSMISIÓN DIRECTA
if (strpos($url_target, '.ts') !== false || strpos($url_target, '.m4s') !== false) {
    header("Content-Type: video/mp2t");
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url_target);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, false); // No guardar en memoria, soltar de inmediato
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_BUFFERSIZE, 128000);    // Buffer más grande para fluidez
    curl_setopt($ch, CURLOPT_TCP_NODELAY, 1);        // Menos latencia
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'User-Agent: APPMOVIL-',
        'Connection: keep-alive'
    ]);
    
    curl_exec($ch); // Envía el video directamente al navegador del usuario
    curl_close($ch);
    exit;
}

// 3. Manejo de Listas de Reproducción (.m3u8) -> PROCESAMIENTO
header("Content-Type: application/vnd.apple.mpegurl");

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url_target);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Aquí sí necesitamos el texto para editarlo
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_ENCODING, "gzip"); 
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'User-Agent: APPMOVIL-',
    'Accept: */*'
]);

$content = curl_exec($ch);
$base_url = substr($url_target, 0, strrpos($url_target, '/') + 1);
curl_close($ch);

if (!$content) {
    echo "#EXTM3U\n#ERROR: NO_DATA_FROM_SOURCE";
    exit;
}

// 4. Reescribir las rutas para que sigan pasando por el proxy
$lines = explode("\n", trim($content));
echo "#EXTM3U\n";

foreach ($lines as $line) {
    $line = trim($line);
    if (empty($line) || $line == "#EXTM3U") continue;

    if (strpos($line, '#') === 0) {
        echo $line . "\n";
    } else {
        // Construir URL absoluta
        if (strpos($line, 'http') === 0) {
            $full_path = $line;
        } else {
            $full_path = $base_url . $line;
        }
        
        // El truco: Enviamos el segmento a través de ?url= para mantener el flujo
        echo $mi_script . "?url=" . urlencode($full_path) . "\n";
    }
}
