<?php
$file = __DIR__ . "/id.txt";
$pictureDir = __DIR__ . "/picture/";

// Ha a fájl nem létezik, üres adatok
if (!file_exists($file)) {
    die("Az id.txt fájl nem található!");
}

// Fájl beolvasása soronként
$lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$totalIds = count($lines) / 4; // minden ID 4 sorból áll

// Tömbök az azonosítók tárolására
$ures_ids = [];
$ismeretlen_ids = [];
$torolva_ids = [];
$nincs_kep_ids = [];

// Sorok feldolgozása 4-es blokkokban
for ($i = 0; $i < count($lines); $i += 4) {
    // Azonosító formázása 4 karakteresre
    $rawId = ltrim($lines[$i] ?? '', "0");
    $idFormatted = str_pad($rawId, 4, "0", STR_PAD_LEFT);
    
    // Mind a három adat kinyerése
    $tartalom = $lines[$i + 1] ?? '';
    $tipus = $lines[$i + 2] ?? '';
    $hely = $lines[$i + 3] ?? '';
    
    // Törölt elemek leválogatása (ezek nem számítanak se üresnek, se ismeretlennek)
    if ($tartalom === '[RENDSZERBŐL TÖRÖLVE]') {
        $torolva_ids[] = $idFormatted;
    } else {
        // Üres tárolók
        if ($tartalom === '[ÜRES]') {
            $ures_ids[] = $idFormatted;
        }
        
        // Ismeretlen tárolók: ha a tartalom, típus vagy hely bármelyike hiányzik
        if ($tartalom === '[NINCS INFORMÁCIÓ]' || $tipus === '[NINCS INFORMÁCIÓ]' || $hely === '[NINCS INFORMÁCIÓ]') {
            $ismeretlen_ids[] = $idFormatted;
        }

        // Kép ellenőrzése
        $picFile = $pictureDir . $idFormatted . ".png";
        if (!file_exists($picFile)) {
            $nincs_kep_ids[] = $idFormatted;
        }
    }
}

// Valós, eltárolt azonosítók kiszámítása (összes mínusz törölt)
$valos_total = $totalIds - count($torolva_ids);

// Valós, eltárolt azonosítók kiszámítása (összes mínusz törölt)
$valos_total = $totalIds - count($torolva_ids);

// PHP segédfüggvény a kattintható linkek generálásához
function renderLinks($ids) {
    if (empty($ids)) return 'Nincs ilyen.';
    $links = array_map(function($id) {
        return "<a href=\"id.html?$id\" class=\"id-link\">$id</a>";
    }, $ids);
    return implode(" ", $links);
}

?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Adatok</title>
    <link rel="stylesheet" type="text/css" href="main.css">
    <link rel="shortcut icon" href="favicon.ico" />
    <link href='https://fonts.googleapis.com/css?family=Roboto' rel='stylesheet'>
    <style>  
    /* --- Alap beállítások --- */
    * {
        box-sizing: border-box;
    }
            
    body {
        font-family: 'Roboto', sans-serif;
        padding: 2rem;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        margin: 0;
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
    }

    h1 {
        text-align: center;
        color: #333;
        margin-bottom: 1.5rem;
        font-size: 1.4rem;
    }

    p {
        font-size: 0.95rem;
        margin: 0.6rem 0;
        color: #333;
        line-height: 1.5;
    }

    .stat-row:hover {
        opacity: 0.5;
        cursor: default;
    }
        
    .id-list {
        display: none;
        font-size: 0.9rem;
        color: #555;
        background: #f9f9f9;
        padding: 0.5rem;
        border-radius: 0 6px 6px 0;
        word-wrap: break-word;
        line-height: 1.5;
    }
        
    .id-link {
        text-decoration: none;
        color: #0077cc;
        background: #e6f2ff;
        padding: 2px 6px;
        border-radius: 4px;
        margin-right: 4px;
        display: inline-block;
        margin-bottom: 4px;
        transition: background 0.2s;
    }

    .id-link:hover {
        background: #0077cc;
        color: white;
    }
    </style>
</head>
<body>
    <div class="container">
        <h1>Adatok</h1>
        <p><strong>Eltárolt azonosítók száma:</strong> <?php echo (int)$totalIds; ?></p>
        
        <p><strong>Aktív azonosítók száma:</strong> <?php echo (int)$valos_total; ?></p>
        
        <!-- Törölt elemek -->
        <p class="stat-row" onclick="toggleList('torolva-list')"><strong>Törölt azonosítók száma:</strong> <?php echo count($torolva_ids); ?></p>
        <div id="torolva-list" class="id-list">
            <?php echo renderLinks($torolva_ids); ?>
        </div>
        
        <!-- Üres tárolók -->
        <p class="stat-row" onclick="toggleList('ures-list')"><strong>Üres tárolók száma:</strong> <?php echo count($ures_ids); ?></p>
        <div id="ures-list" class="id-list">
            <?php echo renderLinks($ures_ids); ?>
        </div>

        <!-- Kép nélküli tárolók -->
        <p class="stat-row" onclick="toggleList('nincs-kep-list')"><strong>Kép nélküli tárolók száma:</strong> <?php echo count($nincs_kep_ids); ?></p>
        <div id="nincs-kep-list" class="id-list">
            <?php echo renderLinks($nincs_kep_ids); ?>
        </div>

        <!-- Ismeretlen tárolók -->
        <p class="stat-row" onclick="toggleList('ismeretlen-list')"><strong>Ismeretlen tárolók száma:</strong> <?php echo count($ismeretlen_ids); ?></p>
        <div id="ismeretlen-list" class="id-list">
            <?php echo renderLinks($ismeretlen_ids); ?>
        </div>

        <p><strong>Módosítás:</strong> <span id="modified"></span></p>
    </div>

    <!-- JavaScript a dátum és a listák kezeléséhez -->
    <script>
    // Listák kinyitása/becsukása
    function toggleList(id) {
        const listEl = document.getElementById(id);
        if (listEl.style.display === "block") {
            listEl.style.display = "none";
        } else {
            listEl.style.display = "block";
        }
    }

    let file = "id.txt";

    function makeRequest(url) {
        return new Promise(function (resolve, reject) {
            let xhr = new XMLHttpRequest();
            let rand = Math.floor(Math.random() * (99999 - 11111) + 11111);
            let newurl = url+"?v="+rand;
            xhr.open("HEAD", newurl);
            xhr.onload = function () {
                if (this.status >= 200 && this.status < 300) {
                    resolve(xhr.getResponseHeader("Last-Modified"));
                } else {
                    reject({
                        status: this.status,
                        statusText: xhr.statusText
                    });
                }
            };
            xhr.onerror = function () {
                reject({
                    status: this.status,
                    statusText: xhr.statusText
                });
            };
            xhr.send();
        });
    }

    async function rando(){
        try {
            let result = await makeRequest(file);
            let time = Date.parse(result)/1000;
            var d = new Date(0);
            d.setUTCSeconds(time);
            
            // Dátum formázása: YYYY-MM-DD HH:mm:ss
            let yyyy = d.getFullYear();
            let mm = String(d.getMonth() + 1).padStart(2, '0');
            let dd = String(d.getDate()).padStart(2, '0');
            let hh = String(d.getHours()).padStart(2, '0');
            let min = String(d.getMinutes()).padStart(2, '0');
            let ss = String(d.getSeconds()).padStart(2, '0');

            document.getElementById('modified').innerHTML = `${yyyy}-${mm}-${dd} ${hh}:${min}:${ss}`;
        } catch (error) {
            document.getElementById('modified').innerHTML = "[HIBA]";
        }
    }

    rando();
        
    document.addEventListener("keyup", function(e) {
    // Ha a gombot folyamatosan nyomva tartják, ne fusson le újra és újra
    if (e.repeat) return;

    if (e.key === "Escape") {
        e.preventDefault(); // Böngésző alapértelmezett funkciójának blokkolása
        window.location.href = "../"; // Azonnali ugrás egy könyvtárral feljebb
    } else if (e.key === "Backspace") {
        e.preventDefault();
        window.history.back(); // Pontosan egyetlen oldal visszalépése
    }
});
        
// --- ZOOMOLÁS LETILTÁSA MOBILON ---

// 1. Kétujjas (pinch-to-zoom) nagyítás letiltása
document.addEventListener('touchmove', function (event) {
    if (event.touches.length > 1) {
        event.preventDefault(); // Ha egynél több ujjal érnek a képernyőhöz, ne csináljon semmit
    }
}, { passive: false });

// 2. Dupla koppintásos (double-tap) nagyítás erőszakos letiltása (ha a CSS nem lenne elég Safari alatt)
let lastTouchEnd = 0;
document.addEventListener('touchend', function (event) {
    let now = (new Date()).getTime();
    if (now - lastTouchEnd <= 300) {
        event.preventDefault(); // Ha 300 milliszekundumon belül két koppintás történik, letiltja
    }
    lastTouchEnd = now;
}, false);
    </script>
</body>
</html>