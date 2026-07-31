<?php
session_start();

// Utolsó létező ID meghatározása az id.txt alapján
$file = __DIR__ . "/id.txt";
$lastId = 0;
if (file_exists($file)) {
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!empty($lines)) {
        $lastId = (int)$lines[count($lines) - 4]; // utolsó blokk első sora
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = trim($_POST['id'] ?? '');
    $id = preg_replace('/\D/', '', $id); // csak számjegyek megtartása

    if ($id !== '') {
        $id = str_pad($id, 4, "0", STR_PAD_LEFT); // kitöltés 4 számjegyre

        // Ellenőrzés, hogy az ID létezik-e
        if ((int)$id > $lastId || (int)$id < 1) {
            $_SESSION['msg'] = ['type' => 'error', 'text' => "Az azonosító nem létezik"];
        } else {
            header("Location: id.html?" . $id);
            exit;
        }
    } else {
        $_SESSION['msg'] = ['type' => 'error', 'text' => "Sikertelen művelet"];
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Azonosító szerinti keresés</title>
<link href='https://fonts.googleapis.com/css?family=Roboto' rel='stylesheet'>
<link rel="stylesheet" type="text/css" href="main.css">
<link rel="shortcut icon" href="favicon.ico" />
<style>
* { box-sizing: border-box; }
body {
    font-family: 'Roboto', sans-serif;
    display: flex;
    justify-content: center;
    align-items: flex-start; /* nem középre */
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
    margin-bottom: 1rem;
    color: #333;
}
input[type="text"] {
    width: 100%;
    padding: 0.6rem;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 0.95rem;
}
button {
    margin-top: 1rem;
    width: 100%;
    padding: 0.8rem;
    font-size: 1rem;
    border: none;
    border-radius: 6px;
    background: #0077cc;
    color: white;
    cursor: pointer;
    transition: background 0.2s;
}
button:hover { background: #005fa3; }

/* Üzenetdoboz stílusa */
.msg {
    margin-top: 20px;
    width: 100%;
    max-width: 420px;
    text-align: center;
    padding: 0.8rem;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    font-size: 0.95rem;
}
.msg.success { background: #e6f4ea; color: #256029; border: 1px solid #b6dfb9; }
.msg.error { background: #fdecea; color: #8a1c1c; border: 1px solid #f5c2c0; }
</style>
</head>
<body>
<div class="wrapper" style="width:100%; max-width:420px; margin:0 auto;">
    <div class="container">
        <h1>Azonosító szerinti keresés</h1>
        <form method="post">
            <input type="text" id="id" name="id" inputmode="numeric" pattern="[0-9]*">
            <button type="submit">Keresés</button>
        </form>
    </div>

    <?php if (!empty($_SESSION['msg'])): ?>
        <div class="msg <?php echo $_SESSION['msg']['type']; ?>">
            <?php echo $_SESSION['msg']['text']; ?>
        </div>
        <script>
    // üzenet eltüntetése 2 másodperc után, szép átmenettel
    setTimeout(() => {
        const msg = document.querySelector('.msg');
        if (msg) {
            msg.style.transition = "opacity 0.5s";
            msg.style.opacity = "0";
            setTimeout(() => msg.remove(), 500); // végleg eltávolítja
        }
    }, 2000);
</script>
        <?php unset($_SESSION['msg']); ?>
    <?php endif; ?>
    
<script>
document.addEventListener("keydown", function(e) {
    // Ha a gombot folyamatosan nyomva tartják, ne fusson le többször
    if (e.repeat) return;

    // Ellenőrizzük, hogy aktív-e valamilyen beviteli mező
    const isInputActive = ['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName);
    
    if (isInputActive) {
        // --- HA AKTÍV EGY BEVITELI MEZŐ ---
        if (e.key === "Escape") {
            // Kifókuszál a mezőből
            e.target.blur();
        } else if (e.key === "Tab") {
            e.preventDefault(); // Megakadályozzuk, hogy átugorjon a következő elemre
            e.target.value = ''; // Mező tartalmának törlése
            e.target.dispatchEvent(new Event('input', { bubbles: true })); // Változás jelzése a rendszernek
        }
        return; // Kilépünk, hogy a többi gomb (pl. Backspace) normálisan működjön gépeléskor
    }

    // --- HA NINCS AKTÍV BEVITELI MEZŐ ---
    if (e.key === "Escape") {
        e.preventDefault(); // Böngésző alapértelmezésének blokkolása
        window.location.href = "../"; // Ugrás egy könyvtárral feljebb
    } else if (e.key === "Backspace") {
        e.preventDefault(); // Megakadályozzuk az extra böngészős funkciókat
        window.history.back(); // Visszalépés pontosan egy oldalt
    } else if (e.key === "Enter") {
        e.preventDefault(); // Megakadályozzuk az alapértelmezett viselkedést
        // Megkeressük az első látható, szöveges beviteli mezőt az oldalon
        const firstInput = document.querySelector('input:not([type="hidden"]):not([type="submit"]):not([type="button"])');
        if (firstInput) {
            firstInput.focus(); // Befókuszálunk
        }
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
    
</div>
</body>
</html>
