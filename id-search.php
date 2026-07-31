<?php
session_start();

// id.txt beolvasása
$file = __DIR__ . "/id.txt";
$entries = [];

if (file_exists($file)) {
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    for ($i = 0; $i < count($lines); $i += 4) {
        $id = $lines[$i] ?? '';
        $tartalom = $lines[$i + 1] ?? '';
        
        if (isset($tartalom[0]) && $tartalom[0] === '[') {
        continue;
        }
        
        $entries[] = ['id' => str_pad($id, 4, "0", STR_PAD_LEFT), 'tartalom' => $tartalom];
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kulcsszó szerinti keresés</title>
<link href='https://fonts.googleapis.com/css?family=Roboto' rel='stylesheet'>
<style>
* { box-sizing: border-box; }
body {
    font-family: 'Roboto', sans-serif;
    margin: 0;
    padding: 0;
    background: black;
}
header {
    padding: 1rem;
    color: white;
    text-align: center;
    font-size: 1.3rem;
}
#search-container {
    padding: 10px;
}
#search {
    width: 100%;
    padding: 0.6rem;
    border: 1px solid #ccc;
    font-size: 1rem;
}
.entry {
    padding: 0.8rem;
    margin: 10px;
    border-bottom: 1px solid #ddd;
    cursor: pointer;
    transition: background 0.2s;
    background-color: white;
}
.entry:hover {
    background-color: #eee;
}
.msg {
    position: fixed;
    top: 1rem;
    left: 50%;
    transform: translateX(-50%);
    max-width: 90%;
    padding: 1rem;
    border-radius: 8px;
    text-align: center;
    font-size: 0.95rem;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    z-index: 1000;
}
.msg.success { background: #e6f4ea; color: #256029; border: 1px solid #b6dfb9; }
.msg.error { background: #fdecea; color: #8a1c1c; border: 1px solid #f5c2c0; }
</style>
</head>
<body>
<header>Kulcsszó szerinti keresés</header>
<div id="search-container">
    <input type="text" id="search" placeholder="Keresés tartalom alapján...">
</div>
<div id="list">
    <?php foreach ($entries as $e): ?>
        <div class="entry" data-id="<?php echo $e['id']; ?>">
            <?php echo htmlspecialchars($e['tartalom']); ?>
        </div>
    <?php endforeach; ?>
</div>

<div id="msg-container"></div>

<script>
const searchInput = document.getElementById('search');
const list = document.getElementById('list');
const entries = Array.from(list.getElementsByClassName('entry'));
const msgContainer = document.getElementById('msg-container');

function showMessage(text, type = 'error') {
    const div = document.createElement('div');
    div.className = 'msg ' + type;
    div.textContent = text;
    msgContainer.appendChild(div);
    setTimeout(() => {
        div.style.transition = "opacity 0.5s";
        div.style.opacity = "0";
        setTimeout(() => div.remove(), 500);
    }, 2000);
}

// Keresés
searchInput.addEventListener('input', () => {
    const query = searchInput.value.toLowerCase();
    let anyVisible = false;
    entries.forEach(entry => {
        const text = entry.textContent.toLowerCase();
        const visible = text.includes(query);
        entry.style.display = visible ? '' : 'none';
        if (visible) anyVisible = true;
    });
    if (!anyVisible && query !== '') {
        showMessage("Nincs találat a keresésre", "error");
    }
});

// Kattintás az entry-re
entries.forEach(entry => {
    entry.addEventListener('click', () => {
        const id = entry.getAttribute('data-id');
        window.location.href = 'id.html?' + id;
    });
});
    
document.addEventListener('keydown', function(event) {
    // Ha a gombot nyomva tartják, ne fusson le többször
    if (event.repeat) return;

    const searchInput = document.getElementById('search');
    const isInputActive = (document.activeElement === searchInput);

    if (isInputActive) {
        // --- HA AKTÍV A KERESŐMEZŐ ---
        if (event.key === 'Escape') {
            event.preventDefault(); // Megakadályozzuk az alap viselkedést
            searchInput.blur(); // Kifókuszál
        } else if (event.key === 'Tab') {
            event.preventDefault();
            searchInput.value = ''; // Töröl
            searchInput.dispatchEvent(new Event('input', { bubbles: true })); // Frissít
        } else if (event.key === 'Enter') {
            event.preventDefault();
            const entryElements = Array.from(document.getElementsByClassName('entry'));
            const firstVisible = entryElements.find(entry => entry.style.display !== 'none');
            
            if (firstVisible) {
                window.location.href = 'id.html?' + firstVisible.getAttribute('data-id');
            }
        }
    } else {
        // --- HA NINCS AKTÍV KERESŐMEZŐ ---
        if (event.key === 'Escape') {
            event.preventDefault();
            window.location.href = '../'; // Ugrás egy könyvtárral feljebb
        } else if (event.key === 'Backspace') {
            event.preventDefault();
            window.history.back(); // Visszalépés az előző oldalra
        } else if (event.key === 'Enter') {
            event.preventDefault();
            searchInput.focus(); // Befókuszál a keresőbe
        } else if (event.key >= '1' && event.key <= '9') {
            const index = parseInt(event.key) - 1;
            const entryElements = Array.from(document.getElementsByClassName('entry'));
            const visibleEntries = entryElements.filter(entry => entry.style.display !== 'none');
            
            if (visibleEntries[index]) {
                window.location.href = 'id.html?' + visibleEntries[index].getAttribute('data-id');
            }
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
