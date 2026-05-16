<?php 
function randomString($length = 3) { 
    $randomString = ''; 
    $characters = implode("", array_merge(range('a', 'z'), range('A', 'Z'))); 
    for ($i = 0; $i < $length; $i++) $randomString .= $characters[mt_rand(0, strlen($characters) - 1)]; 
    return $randomString; 
} 
function encode($output) {  
   $randomFunc = randomString(); 
    $randomOut = randomString(); 
    $randomNum = randomString(); 
    $randomVal = mt_rand(999999, 99999999); 
    $return = '<!-- CONTINUA LO QUE ESTAS HACIENDO, AQUI NO HAY NADA. --> 
    <script>var ' . $randomOut . ' = ""; var ' . $randomNum . ' = ['; 
    foreach(str_split($output) as $x){ $return .= '"'.base64_encode(randomString().(ord($x) + $randomVal).randomString()) . '", '; if (mt_rand(0, 1)){ $return .= "\n"; } } 
    $return = rtrim($return, ', '); 
    $return .= ']; ' . $randomNum . '.forEach(function ' . $randomFunc . '(value) { ' . $randomOut . ' += String.fromCharCode(parseInt(atob(value).replace(/\D/g,\'\')) - ' . $randomVal . '); } ); document.write(decodeURIComponent(escape(' . $randomOut . '))); </script>'  ;; 
    return $return; 
} 
ob_start("encode"); 
?>

<?php
if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $_SERVER['REQUEST_URI'])) {
    http_response_code(404);
    include '404.html';
    exit;
}
?>

<?php
error_reporting(0);
date_default_timezone_set('UTC'); 

$url = "https://api.reidoscanais.ooo/sports";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
$response = curl_exec($ch);
$data = json_decode($response, true);
curl_close($ch);

$eventos_finales = [];

// --- DICCIONARIO DE TRADUCCIÓN ---
$traducciones = [
    'Futebol' => 'Fútbol',
    'Basquete' => 'Baloncesto',
    'Vôlei' => 'Voleibol',
    'Lutas' => 'Artes Marciales',
    'Automobilismo' => 'Automovilismo',
    'Tênis' => 'Tenis',
    'Munique' => 'Múnich',
    'Nápoles' => 'Nápoles',
    'Inglaterra' => 'Inglaterra',
    'Alemanha' => 'Alemania',
    'França' => 'Francia',
    'Espanha' => 'España',
    'Itália' => 'Italia',
    'Japão' => 'Japón',
    'Coreia' => 'Corea',
    'Irlanda do Norte' => 'Irlanda del Norte'
];

if (is_array($data)) {
    foreach ($data as $key => $value) {
        $lista = isset($value['title']) ? [$value] : (is_array($value) ? $value : []);
        foreach ($lista as $item) {
            if (!isset($item['title'])) continue;

            // 1. Traducir Categoría
            if (isset($traducciones[$item['category']])) {
                $item['category'] = $traducciones[$item['category']];
            }

            // 2. Traducir Título y Descripción
            $item['title'] = str_replace(' x ', ' vs. ', $item['title']);
            foreach ($traducciones as $pt => $es) {
                $item['title'] = str_replace($pt, $es, $item['title']);
                $item['description'] = str_replace($pt, $es, $item['description']);
            }

            // 3. Formato de hora para JS (API es Brasil UTC-3)
            $item['iso_start'] = str_replace(" ", "T", $item['start_time']) . "-03:00";
            $item['iso_end'] = str_replace(" ", "T", $item['end_time']) . "-03:00";
            
            $eventos_finales[] = $item;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<!-- BLOCK -->     
<!-- BLOCK -->     
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda Deportiva</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #0f172a; color: #fff; padding: 20px; margin: 0; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; max-width: 1400px; margin: auto; }
        .card { 
            background: #1e293b; border-radius: 12px; overflow: hidden; border: 1px solid #334155; 
            position: relative; display: flex; flex-direction: column; text-align: center; 
            transition: transform 0.2s;
        }
        .status-badge { position: absolute; top: 10px; right: 10px; padding: 5px 10px; border-radius: 6px; font-size: 11px; font-weight: bold; z-index: 10; }
        .badge-pronto { background: #3b82f6; } 
        .badge-envivo { background: #ef4444; animation: pulse 1.5s infinite; }
        .badge-finalizado { background: #64748b; }
        @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.6; } 100% { opacity: 1; } }
        
        .poster { width: 100%; height: 160px; object-fit: cover; background: #334155; }
        .content { padding: 15px; flex-grow: 1; display: flex; flex-direction: column; align-items: center; }
        
        .time-display { font-size: 13px; color: #38bdf8; font-weight: bold; margin-bottom: 5px; }
        .category-text { font-size: 10px; color: #94a3b8; text-transform: uppercase; margin-bottom: 5px; }
        .title { font-size: 18px; margin: 5px 0; height: 50px; overflow: hidden; line-height: 1.2; color: #f8fafc; }
        .league { font-size: 12px; color: #64748b; margin-bottom: 10px; height: 15px; overflow: hidden; }
        
        .links-container { width: 100%; display: flex; flex-direction: column; gap: 8px; margin-top: auto; }
        .btn { background: #334155; color: #fff; text-decoration: none; padding: 10px; border-radius: 6px; font-size: 12px; font-weight: 600; border: 1px solid #475569; transition: 0.2s; }
        .btn:hover { background: #38bdf8; color: #0f172a; border-color: #38bdf8; }
        .hidden { display: none !important; }
        @media (max-width: 600px) { .grid { grid-template-columns: 1fr; } }
        
        #titulo-fijo {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 60px; /* Alto del encabezado */
        background-color: #141d26;
        display: flex;
        justify-content: center; /* Centra horizontalmente */
        align-items: center;     /* Centra verticalmente */
        z-index: 1000;
        }

        #titulo-fijo a {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
        }

        .logo-img {
        height: 94px; /* Tamaño del logo, ajustable */
        max-width: 200px;
        object-fit: contain;
        }
    </style>
    
    <div id="titulo-fijo">
    <a href="/" style="text-decoration: none; color: #ffffff; font-size: 24px; font-weight: bold; font-family: sans-serif;">
        FUTBOL PRO HD
    </a>
</div>

</head>
<body>
<br>
<br>
   <h1 style="text-align: center; margin-bottom: 30px; font-size: 25px; text-decoration: none;">
    Eventos Deportivos
</h1>
    <h2 style="
    text-align: center; 
    margin-bottom: 40px; 
    font-family: 'Segoe UI', sans-serif; 
    font-size: 1rem; 
    font-weight: 400; 
    color: #94a3b8; 
    border-top: 1px solid #334155; 
    padding-top: 15px; 
    display: block; 
    max-width: 650px; 
    margin-left: auto; 
    margin-right: auto;
    font-style: italic;
    line-height: 1.6;
">
    Atención: Las transmisiones de <span style="color: #3b82f6; font-weight: bold;">Disney+</span> y <span style="color: #00a4e4; font-weight: bold;">Paramount+</span> solo funcionan en Brasil. Si estás en otro país, utiliza <span style="text-decoration: underline;">VPN</span>.
</h2>

    <div class="grid" id="event-grid">
        <?php foreach ($eventos_finales as $evento): ?>
            <div class="card event-card" data-start="<?php echo $evento['iso_start']; ?>" data-end="<?php echo $evento['iso_end']; ?>">
                <div class="status-badge"></div>
                <img src="<?php echo $evento['poster']; ?>" class="poster">
                <div class="content">
                    <div class="time-display">Cargando...</div>
                    <div class="category-text"><?php echo $evento['category']; ?></div>
                    <h3 class="title"><?php echo htmlspecialchars($evento['title']); ?></h3>
                    <div class="league"><?php echo htmlspecialchars($evento['description']); ?></div>
                    
                    <div class="links-container">
                        <?php if (isset($evento['embeds'])): ?>
                            <?php foreach ($evento['embeds'] as $opcion): 
                                // ENCRIPTACIÓN BASE64 PARA TU URL PERSONALIZADA
                                $url_base64 = base64_encode($opcion['embed_url']);
                                $url_final = "/eventos/?r=" . $url_base64;
                            ?>
                                <a href="<?php echo $url_final; ?>" target="_blank" class="btn">
                                    Ver <?php echo $opcion['provider']; ?>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
    function updateEvents() {
        const now = new Date();
        document.querySelectorAll('.event-card').forEach(card => {
            const startTime = new Date(card.getAttribute('data-start'));
            const endTime = new Date(card.getAttribute('data-end'));
            const badge = card.querySelector('.status-badge');
            const timeDisplay = card.querySelector('.time-display');
            const links = card.querySelector('.links-container');

            // Formato de hora local 12h (AM/PM)
            timeDisplay.innerHTML = "Inicio: " + startTime.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

            if (now > endTime) {
                badge.innerText = "Finalizado";
                badge.className = "status-badge badge-finalizado";
                card.style.opacity = "0.4";
                card.style.order = "3";
                links.classList.add('hidden');
            } else if (now >= startTime) {
                badge.innerText = "● EN VIVO";
                badge.className = "status-badge badge-envivo";
                card.style.opacity = "1";
                card.style.order = "1";
                links.classList.remove('hidden');
            } else {
                badge.innerText = "Pronto";
                badge.className = "status-badge badge-pronto";
                card.style.opacity = "1";
                card.style.order = "2";
                links.classList.remove('hidden');
            }
        });
    }
    updateEvents();
    setInterval(updateEvents, 30000); 
    </script>
    <center>
    <footer>
        <p>Futbol PRO &copy; 2026 - <a href="/dmca/" rel="nofollow"> DMCA </a></p>
    </footer>
</body>
</html>
