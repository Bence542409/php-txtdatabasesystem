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

// Ha a fájl nem létezik, hozzuk létre
if (!file_exists($file)) {
    file_put_contents($file, "");
}

// Fájl beolvasása soronként tömbbe
$lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

// Utolsó ID meghatározása
$lastId = 0;
if (!empty($lines)) {
    $lastId = (int)$lines[count($lines) - 4]; // utolsó blokk első sora
}
$newId = $lastId + 1;
$newIdFormatted = str_pad($newId, 4, "0", STR_PAD_LEFT);

// --- ÚJ ADAT HOZZÁADÁSA ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['upload_btn'])) {
    $tartalom = trim($_POST['tartalom'] ?? '');
    $tipus = trim($_POST['tipus'] ?? '');
    $hely = trim($_POST['hely'] ?? '');

    if ($tartalom && $tipus && $hely) {
        $newEntry = $newIdFormatted . "\n" . $tartalom . "\n" . $tipus . "\n" . $hely;

        if (!empty($lines)) {
            $newEntry = "\n" . $newEntry;
        }

        file_put_contents($file, $newEntry, FILE_APPEND);

        // QR kód generálása
        $qrData = "https://garazs.nemeth-bence.com/id.html?" . $newIdFormatted;
        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($qrData);

        $qrDir = __DIR__ . "/barcode/";
        if (!is_dir($qrDir)) {
            mkdir($qrDir, 0777, true);
        }

        $qrFile = $qrDir . $newIdFormatted . ".png";
        file_put_contents($qrFile, file_get_contents($qrUrl));

        // Jelzés, hogy most kell képet feltölteni
        $_SESSION['new_id'] = $newIdFormatted;
    } else {
        $_SESSION['msg'] = ['type' => 'error', 'text' => "Sikertelen művelet"];
    }
}

// --- KÉP FELTÖLTÉS ---
if (isset($_POST['upload_btn']) && isset($_SESSION['new_id'])) {
    $uploadId = $_SESSION['new_id'];
    $pictureDir = __DIR__ . "/picture/";

    if (!is_dir($pictureDir)) {
        mkdir($pictureDir, 0777, true);
    }

    if (isset($_FILES['picture']) && $_FILES['picture']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['picture']['tmp_name'];
        $destFile = $pictureDir . $uploadId . ".png";

        move_uploaded_file($tmpName, $destFile);

        $_SESSION['msg'] = ['type' => 'success', 'text' => "Azonosító sikeresen hozzáadva (ID: {$uploadId})"];
        unset($_SESSION['new_id']); // Töröljük a jelzőt
    } else {
        $_SESSION['msg'] = ['type' => 'error', 'text' => "Kép feltöltése sikertelen"];
    }
}

// --- KÉP FELTÖLTÉS KIHAGYÁSA ---
if (isset($_POST['skip_btn']) && isset($_SESSION['new_id'])) {
    $skipId = $_SESSION['new_id'];
    $_SESSION['msg'] = ['type' => 'success', 'text' => "Azonosító sikeresen hozzáadva (ID: {$skipId})"];
    unset($_SESSION['new_id']);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Azonosító hozzáadása</title>
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
        .next-id {
            font-weight: bold;
            color: #0077cc;
            margin-bottom: 1rem;
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
            background: #0077cc;
            color: white;
            cursor: pointer;
            transition: background 0.2s;
        }
        button:hover {
            background: #005fa3;
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
            <h1>Azonosító hozzáadása</h1>
            <?php if (isset($_SESSION['new_id'])): ?>
                <div class="next-id">Kép feltöltése tárolóhoz: <?php echo $_SESSION['new_id']; ?></div>
                <form method="post" enctype="multipart/form-data">
                    <label for="picture">Válassz képet:</label>
                    <input type="file" id="picture" name="picture" accept="image/*">
                    <button type="submit" name="upload_btn">Kép feltöltése</button>
                    <button type="submit" name="skip_btn" style="background:#cc0000;">Mégsem</button>
                </form>
            <?php else: ?>
                <div class="next-id">Tároló azonosítója: <?php echo $newIdFormatted; ?></div>
                <form method="post">
                    <label for="tartalom">Tárolóban található elemek:</label>
                    <input type="text" id="tartalom" name="tartalom">

                    <label for="tipus">Tároló típusa:</label>
                    <input type="text" id="tipus" name="tipus">

                    <label for="hely">Tároló helye:</label>
                    <input type="text" id="hely" name="hely">

                    <button type="submit">Azonosító hozzáadása</button>
                </form>
            <?php endif; ?>
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
    // Ha a gombot folyamatosan nyomva tartják, ne fusson le újra és újra
    if (e.repeat) return;

    // Ellenőrizzük, hogy aktív-e valamilyen beviteli mező
    const isInputActive = ['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName);
    
    // Ha beviteli mezőben vagyunk, kilépünk a függvényből, 
    // így a gombok a normál dolgukat csinálják (pl. betű törlése)
    if (isInputActive) return;

    // Ha NINCS beviteli mezőben a fókusz, jöhetnek a gyorsgombok:
    if (e.key === "Escape") {
        e.preventDefault(); // Böngésző alapértelmezésének blokkolása
        window.location.href = "../"; // Ugrás egy könyvtárral feljebb
    } else if (e.key === "Backspace") {
        e.preventDefault(); // Megakadályozzuk, hogy a Backspace mást is csináljon
        window.history.back(); // Visszalépés az előző oldalra
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
