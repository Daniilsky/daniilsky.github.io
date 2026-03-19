<html>
<head>
    <meta name="robots" content="noindex, nofollow">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tele Full - Streaming</title>
</head>
<body bgcolor="black" style="margin:0; overflow:hidden">
    <script src="//ssl.p.jwpcdn.com/player/v/8.36.4/jwplayer.js"></script>
    <script>jwplayer.key = "XSuP4qMl+9tK17QNb+4+th2Pm9AWgMO/cYH8CI0HGGr7bdjo"</script>
    <script src="//cdn.jsdelivr.net/npm/p2p-media-loader-core@latest/build/p2p-media-loader-core.min.js"></script>
    <script src="//cdn.jsdelivr.net/npm/p2p-media-loader-hlsjs@latest/build/p2p-media-loader-hlsjs.min.js"></script>
    <script src="//cdn.jsdelivr.net/npm/hls.js@latest"></script>

    <div id="player"></div>

    <script>
        // 1. Obtener el ID del canal desde la URL (ej: vlc.php?id=espn)
        const urlParams = new URLSearchParams(window.location.search);
        const canalId = urlParams.get('id') || 'espn'; // 'espn' por defecto si no hay ID

        var player = jwplayer("player");

        player.setup({
            // 2. Pasamos el ID al proxy
            file: "xprox.php?id=" + canalId, 
            type: "hls",
            width: "100%",
            height: "100%",
            autostart: true,
            stretching: "uniform",
            
            // 3. Configuración para evitar que se trabe (Buffer)
            hlsConfig: {
                debug: false,
                enableWorker: true,      // Usa hilos separados para procesar video
                lowLatencyMode: false,   // Desactivar latencia baja ayuda a que sea más estable
                backBufferLength: 60,    // Guarda 60 segundos de video ya visto
                maxBufferLength: 40,     // Intenta tener siempre 40 segundos descargados a futuro
                maxMaxBufferLength: 60
            }
        });

        // Manejo de errores para reintentar si la conexión falla
        player.on('error', function() {
            console.log("Error detectado, reintentando en 5 segundos...");
            setTimeout(function() {
                player.load();
                player.play();
            }, 5000);
        });
    </script>
</body>
</html>
