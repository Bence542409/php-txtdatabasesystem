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

// AJAX kérés az adatok betöltésére
if (isset($_GET['load_id'])) {
    $loadId = trim($_GET['load_id']);
    $response = [
        'found' => false,
        'tartalom' => '',
        'tipus' => '',
        'hely' => ''
    ];

    if ($loadId !== '') {
        $lines = file($file, FILE_IGNORE_NEW_LINES);
        $loadIdLtrim = ltrim($loadId, "0");

        for ($i = 0; $i < count($lines); $i += 4) {
            $currentId = ltrim($lines[$i], "0");

            if ($currentId === $loadIdLtrim) {
                $response['found'] = true;
                $response['tartalom'] = $lines[$i + 1];
                $response['tipus'] = $lines[$i + 2];
                $response['hely'] = $lines[$i + 3];
                break;
            }
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// --- MÓDOSÍTÁS GOMB (Szöveges adatok mentése és lépés a képhez) ---
if (isset($_POST['modify'])) {
    $modifyId = trim($_POST['modify_id'] ?? '');
    $tartalom = trim($_POST['tartalom'] ?? '');
    $tipus = trim($_POST['tipus'] ?? '');
    $hely = trim($_POST['hely'] ?? '');

    if ($modifyId === '' || $tartalom === '' || $tipus === '' || $hely === '') {
        $_SESSION['msg'] = [
            'type' => 'error',
            'text' => "Sikertelen művelet"
        ];
    } else {
        $lines = file($file, FILE_IGNORE_NEW_LINES);
        $modifyIdLtrim = ltrim($modifyId, "0");
        $found = false;

        for ($i = 0; $i < count($lines); $i += 4) {
            $currentId = ltrim($lines[$i], "0");

            if ($currentId === $modifyIdLtrim) {
                $found = true;
                $lines[$i + 1] = $tartalom;
                $lines[$i + 2] = $tipus;
                $lines[$i + 3] = $hely;
                break;
            }
        }

        if ($found) {
            file_put_contents($file, implode("\n", $lines));
            
            // Beállítjuk a sessiont a képfeltöltés lépéshez (4 karakteresre formázva)
            $_SESSION['mod_id'] = str_pad($modifyIdLtrim, 4, "0", STR_PAD_LEFT);
        } else {
            $_SESSION['msg'] = [
                'type' => 'error',
                'text' => "Nem létezik a megadott azonosítószám"
            ];
        }
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// --- ÚJ KÉP FELTÖLTÉSE ---
if (isset($_POST['upload_btn']) && isset($_SESSION['mod_id'])) {
    $uploadId = $_SESSION['mod_id'];
    $pictureDir = __DIR__ . "/picture/";

    if (!is_dir($pictureDir)) {
        mkdir($pictureDir, 0777, true);
    }

    if (isset($_FILES['picture']) && $_FILES['picture']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['picture']['tmp_name'];
        $destFile = $pictureDir . $uploadId . ".png";

        // A move_uploaded_file felülírja a már létező fájlt
        move_uploaded_file($tmpName, $destFile);

        $_SESSION['msg'] = ['type' => 'success', 'text' => "Azonosító és kép sikeresen módosítva (ID: {$uploadId})"];
        unset($_SESSION['mod_id']); 
    } else {
        $_SESSION['msg'] = ['type' => 'error', 'text' => "Kép feltöltése sikertelen, de az adatok frissültek."];
        unset($_SESSION['mod_id']);
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// --- KÉP MÓDOSÍTÁSÁNAK KIHAGYÁSA (Meglévő kép megtartása) ---
if (isset($_POST['skip_btn']) && isset($_SESSION['mod_id'])) {
    $skipId = $_SESSION['mod_id'];
    $_SESSION['msg'] = ['type' => 'success', 'text' => "Azonosító sikeresen módosítva (ID: {$skipId})"];
    unset($_SESSION['mod_id']);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Azonosító módosítása</title>
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
            display: flex;
            justify-content: center;
            align-items: flex-start;
            margin: 0;
            padding: 2rem;
            max-height: 100vh;
            overflow-x: hidden;
        }

        /* --- Container --- */
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
            color: #ff9900;
            margin-bottom: 1rem;
            text-align: center;
        }

        /* --- Form elemek --- */
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
            margin-bottom: 0.5rem;
        }

        button {
            margin-top: 1rem;
            width: 100%;
            padding: 0.8rem;
            font-size: 1rem;
            border: none;
            border-radius: 6px;
            background: #ff9900;
            color: white;
            cursor: pointer;
            transition: background 0.2s;
        }

        button:hover {
            background: #cc7a00;
        }

        /* --- Wrapper --- */
        .wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
        }

        /* --- Üzenetek --- */
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
        <h1>Azonosító módosítása</h1>

        <?php if (isset($_SESSION['mod_id'])): ?>
            <!-- ÚJ KÉP FELTÖLTÉSE SZEKCIÓ -->
            <div class="next-id">Kép cseréje a tárolóhoz: <?php echo $_SESSION['mod_id']; ?></div>
            <form method="post" enctype="multipart/form-data">
                <label for="picture">Válassz képet:</label>
                <input type="file" id="picture" name="picture" accept="image/*">
                <button type="submit" name="upload_btn">Kép lecserélése</button>
                <button type="submit" name="skip_btn" style="background:#cc0000; margin-top: 0.5rem;">Mégsem</button>
            </form>
        <?php else: ?>
            <!-- EREDETI ADATMÓDOSÍTÓ FORMA -->
            <form method="post" id="modifyForm">
                <label for="modify_id">Azonosító:</label>
                <input type="text" id="modify_id" name="modify_id" autocomplete="off" inputmode="numeric" pattern="[0-9]*">

                <label for="tartalom">Tárolóban található elemek:</label>
                <input type="text" id="tartalom" name="tartalom">

                <label for="tipus">Tároló típusa:</label>
                <input type="text" id="tipus" name="tipus">

                <label for="hely">Tároló helye:</label>
                <input type="text" id="hely" name="hely">

                <button type="submit" name="modify">Azonosító módosítása</button>
            </form>
        <?php endif; ?>
    </div>

    <div id="ajaxMsg"></div>

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

const modifyIdInput = document.getElementById('modify_id');
if(modifyIdInput) {
    const tartalomInput = document.getElementById('tartalom');
    const tipusInput = document.getElementById('tipus');
    const helyInput = document.getElementById('hely');
    const ajaxMsg = document.getElementById('ajaxMsg');

    let timeout = null;

    modifyIdInput.addEventListener('input', () => {
        clearTimeout(timeout);

        timeout = setTimeout(() => {
            const id = modifyIdInput.value.trim();
            if (id === '') {
                tartalomInput.value = '';
                tipusInput.value = '';
                helyInput.value = '';
                ajaxMsg.innerHTML = '';
                return;
            }

            fetch(`<?php echo $_SERVER['PHP_SELF']; ?>?load_id=${encodeURIComponent(id)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.found) {
                        tartalomInput.value = data.tartalom;
                        tipusInput.value = data.tipus;
                        helyInput.value = data.hely;
                        ajaxMsg.innerHTML = '';
                    } else {
                        tartalomInput.value = '';
                        tipusInput.value = '';
                        helyInput.value = '';
                        ajaxMsg.innerHTML = '<div class="msg error" style="margin-top:10px;">Nem létezik a megadott azonosítószám</div>';
                    }
                });
        }, 300); // 300ms késleltetés
    });
}
    
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