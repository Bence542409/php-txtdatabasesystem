<?php

// Felhasználónév és jelszó
$USERNAME = 'admin';
$PASSWORD = 'admin';

// Ellenőrizzük, hogy van-e bejelentkezés
if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']) ||
    $_SERVER['PHP_AUTH_USER'] !== $USERNAME || $_SERVER['PHP_AUTH_PW'] !== $PASSWORD) {

    header('WWW-Authenticate: Basic realm="Biztonságos terület"');
    header('HTTP/1.0 401 Unauthorized');
    echo '<script>alert("Hozzáférés megtagadva"); window.history.back();</script>';
    exit;
}

session_start();

$file = __DIR__ . "/id.txt";

// Ha POST űrlap érkezett
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $deleteId = trim($_POST['delete_id'] ?? '');

    if ($deleteId === '') {
        $_SESSION['msg'] = ['type' => 'error', 'text' => "Sikertelen művelet"];
    } else {
        $lines = file($file, FILE_IGNORE_NEW_LINES);
        $deleteId = ltrim($deleteId, "0");

        // Megkeressük az utolsó azonosítót a fájlban
        $lastIdLtrim = '';
        if (count($lines) >= 4) {
            $lastIdLtrim = ltrim($lines[count($lines) - 4], "0");
        }

        $found = false;
        $newLines = [];
        for ($i = 0; $i < count($lines); $i += 4) {
            $currentId = ltrim($lines[$i], "0");
            
            if ($currentId === $deleteId) {
                $found = true;
                
                // Ha ez a legutolsó elem, akkor teljesen kihagyjuk (valódi törlés a fájl végéről)
                if ($currentId === $lastIdLtrim) {
                    continue;
                } else {
                    // Ha korábbi elem, akkor felülírjuk a tartalmat, hogy a sorok ne csússzanak el
                    $newLines[] = $lines[$i]; // Az eredeti ID szám megtartása
                    $newLines[] = "[RENDSZERBŐL TÖRÖLVE]";
                    $newLines[] = "[RENDSZERBŐL TÖRÖLVE]";
                    $newLines[] = "[RENDSZERBŐL TÖRÖLVE]";
                    continue;
                }
            } else {
                // Megtartjuk az eredeti sorokat minden más ID-nél
                for ($j = 0; $j < 4 && ($i+$j) < count($lines); $j++) {
                    $newLines[] = $lines[$i + $j];
                }
            }
        }

        if ($found) {
    file_put_contents($file, implode("\n", $newLines));

    // --- QR kód törlése, ha létezik ---
    $qrDir = __DIR__ . "/barcode/";
    $qrFile = $qrDir . str_pad($deleteId, 4, "0", STR_PAD_LEFT) . ".png"; 
    if (file_exists($qrFile)) {
        unlink($qrFile);
    }
            
    // --- kép törlése, ha létezik ---
    $qrDir = __DIR__ . "/picture/";
    $qrFile = $qrDir . str_pad($deleteId, 4, "0", STR_PAD_LEFT) . ".png"; 
    if (file_exists($qrFile)) {
        unlink($qrFile);
    }

    $_SESSION['msg'] = [
        'type' => 'success',
        'text' => "Azonosító sikeresen törölve (ID: {$deleteId})"
    ];
} else {
    $_SESSION['msg'] = [
        'type' => 'error',
        'text' => "Nem létezik a megadott azonosítószám"];
}

    }

}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Azonosító törlése</title>
    <link rel="stylesheet" type="text/css" href="main.css">
    <link rel="shortcut icon" href="favicon.ico" />
    <link href='https://fonts.googleapis.com/css?family=Roboto' rel='stylesheet'>
    <style>
        * {
            box-sizing: border-box;
        }
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
        }
        h1 {
            font-size: 1.4rem;
            margin-bottom: 1rem;
            color: #333;
            text-align: center;
        }
        form label {
            display: block;
            margin: 0.6rem 0 0.3rem;
            color: #444;
            font-size: 0.95rem;
        }
        form input {
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
            background: #cc0000;
            color: white;
            cursor: pointer;
            transition: background 0.2s;
        }
        button:hover {
            background: #a80000;
        }
        .wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
        }
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
        .msg.success {
            background: #e6f4ea;
            color: #256029;
            border: 1px solid #b6dfb9;
        }
        .msg.error {
            background: #fdecea;
            color: #8a1c1c;
            border: 1px solid #f5c2c0;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <h1>Azonosító törlése</h1>
            <form method="post">
                <label for="delete_id">Azonosító:</label>
                <input type="text" id="delete_id" name="delete_id" inputmode="numeric" pattern="[0-9]*">
                <button type="submit">Azonosító törlése</button>
            </form>
        </div>

        <?php if (!empty($_SESSION['msg'])): ?>
            <div class="msg <?php echo $_SESSION['msg']['type']; ?>">
                <?php echo $_SESSION['msg']['text']; ?>
            </div>
            
            <?php if ($_SESSION['msg']['type'] === 'success'): ?>
                <script>
                    setTimeout(() => {
                        window.location.href = "index.html";
                    }, 3000);
                </script>
            <?php endif; ?>

            <?php unset($_SESSION['msg']); ?>
        <?php endif; ?>
    </div>
    <script>
    // üzenet eltüntetése 2 másodperc után
    setTimeout(() => {
        const msg = document.querySelector('.msg');
        if (msg) {
            msg.style.transition = "opacity 0.5s";
            msg.style.opacity = "0";
            setTimeout(() => msg.remove(), 500); // végleg eltávolítja
        }
    }, 2000);
        
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

</body>
</html>
