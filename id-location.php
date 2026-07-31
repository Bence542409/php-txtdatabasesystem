<?php
$file = __DIR__ . "/id.txt";

if (!file_exists($file)) {
    die("Az id.txt fájl nem található!");
}

$lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

// Helyszínek és a hozzájuk tartozó ID-k tárolása
$locations = [];

// Sorok feldolgozása 4-es blokkokban
for ($i = 0; $i < count($lines); $i += 4) {
    // Azonosító formázása 4 karakteresre
    $rawId = ltrim($lines[$i] ?? '', "0");
    $idFormatted = str_pad($rawId, 4, "0", STR_PAD_LEFT);
    
    $tartalom = $lines[$i + 1] ?? '';
    // A 4. sor a hely (index + 3)
    $hely = $lines[$i + 3] ?? '';

    // Törölt elemeket kihagyjuk
    if ($tartalom === '[RENDSZERBŐL TÖRÖLVE]') {
        continue;
    }
    
    // Kihagyjuk azokat a helyszíneket, amik "[" karakterrel kezdődnek
    if (isset($hely[0]) && $hely[0] === '[') {
        continue;
    }

    // Hely rögzítése a tömbben (ha még nincs ilyen, létrehozza)
    if (!isset($locations[$hely])) {
        $locations[$hely] = [];
    }
    
    // ID hozzáadása a megfelelő helyszínhez
    $locations[$hely][] = $idFormatted;
}

// Helyszínek ábécé sorrendbe rendezése
ksort($locations);
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <!-- Zoomolás tiltása a meta taggel -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Helyszínek</title>
    <link rel="stylesheet" type="text/css" href="1.css">
    <link rel="shortcut icon" href="favicon.ico" />
    <link href='https://fonts.googleapis.com/css?family=Roboto' rel='stylesheet'>
    <style>
    * { box-sizing: border-box; }
    
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

    button, input, a, select {
        touch-action: manipulation; /* CSS védelem dupla koppintás ellen */
    }

    /* Kattintható sorok stílusa */
    .stat-row {
        cursor: pointer;
        user-select: none;
        transition: background 0.2s, color 0.2s;
        border-radius: 6px;
        padding: 10px;
        margin: 6px 0;
        background: #f0f4f8;
        font-weight: bold;
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: #333;
    }

    .stat-row:hover {
        background: #e0f0ff;
        color: #0077cc;
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
        <h1>Tároló helyek</h1>
        
        <?php if (empty($locations)): ?>
            <p style="text-align: center;">Nincsenek rögzített helyszínek.</p>
        <?php else: ?>
            <?php $counter = 0; ?>
            <?php foreach ($locations as $helyName => $ids): ?>
                <?php $counter++; ?>
                
                <div class="stat-row" onclick="toggleList('loc-<?php echo $counter; ?>')">
                    <span><?php echo htmlspecialchars($helyName); ?></span>
                    <span style="font-size: 0.85em; color: #666;"><?php echo count($ids); ?> db</span>
                </div>
                
                <div id="loc-<?php echo $counter; ?>" class="id-list">
                    <?php 
                    // Kattintható linkek generálása az ID-khoz
                    $links = array_map(function($id) {
                        return "<a href=\"id.html?$id\" class=\"id-link\">$id</a>";
                    }, $ids);
                    echo implode(" ", $links);
                    ?>
                </div>
                
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script>
    // Listák kinyitása és becsukása
    function toggleList(id) {
        const listEl = document.getElementById(id);
        if (listEl.style.display === "block") {
            listEl.style.display = "none";
        } else {
            listEl.style.display = "block";
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

        const isInputActive = ['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName);
        
        if (isInputActive) {
            if (e.key === "Escape") {
                e.target.blur();
            } else if (e.key === "Tab") {
                e.preventDefault();
                e.target.value = '';
                e.target.dispatchEvent(new Event('input', { bubbles: true }));
            }
            return;
        }

        if (e.key === "Escape") {
            e.preventDefault();
            window.location.href = "../"; 
        } else if (e.key === "Backspace") {
            e.preventDefault();
            window.history.back();
        } else if (e.key === "Enter") {
            e.preventDefault();
            const firstInput = document.querySelector('input:not([type="hidden"]):not([type="submit"]):not([type="button"])');
            if (firstInput) {
                firstInput.focus();
            }
        }
    });
    </script>
</body>
</html>