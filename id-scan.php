<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>QR Kód Beolvasása</title>
    <link rel="stylesheet" type="text/css" href="1.css">
    <link rel="shortcut icon" href="favicon.ico" />
    <link href='https://fonts.googleapis.com/css?family=Roboto' rel='stylesheet'>
    <style>
        * { box-sizing: border-box; }
        
        body {
            font-family: 'Roboto', sans-serif;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            margin: 0;
            padding: 2rem;
            max-height: 100vh;
            overflow-x: hidden;
        }

        .container {
            background: #fff;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            width: 100%;
            max-width: 420px;
            text-align: center;
        }

        h1 {
            font-size: 1.4rem;
            margin-top: 0;
            margin-bottom: 1.5rem;
            color: #333;
        }

        /* Legördülő menü (Kamera választó) stílusa */
        select {
            width: 100%;
            padding: 0.8rem;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 6px;
            background: #f9f9f9;
            font-family: 'Roboto', sans-serif;
            margin-bottom: 1rem;
            color: #333;
            display: none; /* Alapból rejtett, amíg be nem töltődnek a kamerák */
        }

        #reader {
            width: 100%;
            border-radius: 8px;
            overflow: hidden;
            display: none;
        }
        
        #error-msg, #loading-msg {
            color: #666;
            font-size: 0.95rem;
            margin-top: 10px;
        }
        #error-msg {
            color: #cc0000;
            display: none;
        }
    </style>
    
    <script src="https://unpkg.com/html5-qrcode"></script>
</head>
<body>

    <div class="container">
        <h1>QR Kód Olvasó</h1>
        
        <div id="loading-msg">Kamera indítása...</div>
        
        <!-- Kamera választó (onchange: azonnal vált, ha a felhasználó másikat választ) -->
        <select id="camera-select" onchange="switchCamera()"></select>
        
        <div id="reader"></div>
        <div id="error-msg">Nem sikerült hozzáférni a kamerához.</div>
    </div>

    <script>
        let html5QrCode;
        let isScanning = false; // Állapotváltozó, hogy ne induljon el többször az olvasás

        // --- 1. KAMERÁK BETÖLTÉSE ÉS AUTOMATIKUS INDÍTÁS ---
        Html5Qrcode.getCameras().then(devices => {
            document.getElementById('loading-msg').style.display = 'none';
            
            if (devices && devices.length) {
                const select = document.getElementById('camera-select');
                let defaultCameraId = devices[0].id; // Alapértelmezetten az első
                
                // Végigmegyünk a talált kamerákon
                devices.forEach(device => {
                    const option = document.createElement('option');
                    option.value = device.id;
                    option.text = device.label || `Kamera ${select.length + 1}`;
                    select.appendChild(option);
                    
                    // Megpróbáljuk kitalálni, melyik a hátsó kamera
                    let camName = option.text.toLowerCase();
                    if (camName.includes('back') || camName.includes('hát') || camName.includes('environment')) {
                        defaultCameraId = device.id;
                    }
                });
                
                select.value = defaultCameraId;
                select.style.display = 'block';
                
                // Azonnal elindítjuk a kamerát
                startCamera(defaultCameraId);
                
            } else {
                document.getElementById('error-msg').innerText = "Nem található kamera az eszközön.";
                document.getElementById('error-msg').style.display = 'block';
            }
        }).catch(err => {
            document.getElementById('loading-msg').style.display = 'none';
            document.getElementById('error-msg').style.display = 'block';
            console.error("Kamera hiba: ", err);
        });


        // --- 2. KAMERA INDÍTÁSI LOGIKA ---
        function startCamera(cameraId) {
            document.getElementById('reader').style.display = 'block';
            document.getElementById('error-msg').style.display = 'none';

            if (!html5QrCode) {
                html5QrCode = new Html5Qrcode("reader");
            }

            html5QrCode.start(
                cameraId,
                {
                    fps: 10,
                    qrbox: { width: 250, height: 250 }
                },
                (decodedText, decodedResult) => {
                    // --- SIKERES OLVASÁS ---
                    if (isScanning) {
                        isScanning = false; // Ideiglenesen letiltjuk az olvasást
                        
                        html5QrCode.stop().then(() => {
                            
                            // --- ÁLLÍTSD BE IDE A SAJÁT DOMAINEDET! ---
                            const domainPrefix = "https://garazs.nemeth-bence.com/id.html?"; 

                            if (decodedText.startsWith(domainPrefix)) {
                                let rawId = decodedText.substring(domainPrefix.length);
                                let id = rawId.replace(/\D/g, ''); 

                                if (id !== '') {
                                    id = id.padStart(4, '0');
                                    
                                    // 1. Megnyitás új ablakban/lapon
                                    window.open('id.html?' + id, '_blank');
                                    
                                } else {
                                    alert("Érvénytelen kód - nem tartalmaz azonosítót");
                                }
                            } else {
                                alert("Érvénytelen kód - nem a rendszer kódja");
                            }

                            // Kis szünet (1 másodperc) után automatikusan újraindul a kamera
                            setTimeout(() => {
                                startCamera(document.getElementById('camera-select').value);
                            }, 1000);

                        }).catch(err => console.log("Hiba a leállításnál:", err));
                    }
                },
                (errorMessage) => {
                    // Hiba/üres képkocka csendes ignorálása
                }
            ).then(() => {
                isScanning = true;
            }).catch((err) => {
                document.getElementById('error-msg').style.display = 'block';
            });
        }


        // --- 3. KAMERA VÁLTÁSA MENET KÖZBEN ---
        function switchCamera() {
            const newCameraId = document.getElementById('camera-select').value;
            
            if (isScanning && html5QrCode) {
                isScanning = false;
                // Megállítjuk a régit, majd indítjuk az újat
                html5QrCode.stop().then(() => {
                    startCamera(newCameraId);
                }).catch(err => console.error("Hiba kamera váltásakor", err));
            } else {
                startCamera(newCameraId);
            }
        }


        // --- ZOOMOLÁS LETILTÁSA MOBILON ---
        document.addEventListener('touchmove', function (event) {
            if (event.touches.length > 1) {
                event.preventDefault(); 
            }
        }, { passive: false });

        let lastTouchEnd = 0;
        document.addEventListener('touchend', function (event) {
            let now = (new Date()).getTime();
            if (now - lastTouchEnd <= 300) {
                event.preventDefault(); 
            }
            lastTouchEnd = now;
        }, false);


        // --- UNIVERZÁLIS GYORSGOMBOK ---
        document.addEventListener("keydown", function(e) {
            if (e.repeat) return;
            if (['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)) return;

            if (e.key === "Escape") {
                e.preventDefault();
                window.location.href = "../"; 
            } else if (e.key === "Backspace") {
                e.preventDefault();
                window.history.back();
            }
        });
    </script>
</body>
</html>