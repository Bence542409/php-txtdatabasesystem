var a = location.href; 
let id = a.substring(a.indexOf("?")+1);

(async () => {
    const response = await fetch("id.txt");
    const data = await response.text();
    const lines = data.split("\n");

    // Segédfüggvény: ha az érték undefined, kicseréli [HIBA]-ra
    function checkUndef(val) {
        return (val === undefined || val === "undefined" || val === "") ? "[HIBA]" : val;
    }

    // Adatok kinyerése és ellenőrzése
    let f_id = checkUndef(lines[(id - 1) * 4]);
    let f_content = checkUndef(lines[(id - 1) * 4 + 1]);
    let f_type = checkUndef(lines[(id - 1) * 4 + 2]);
    let f_location = checkUndef(lines[(id - 1) * 4 + 3]);

    // Szöveges adatok beírása
    document.getElementById('header').innerHTML = f_id;
    document.getElementById('id').innerHTML = f_id;
    document.getElementById('content').innerHTML = f_content;
    document.getElementById('type').innerHTML = f_type;
    document.getElementById('location').innerHTML = f_location;

    // Kép és vonalkód kezelése
    if (f_id === "[HIBA]") {
        document.getElementById('picture').innerHTML = "<div style='width: 500px; height: 300px; background-color: #cc0000; color: white; display: flex; justify-content: center; align-items: center; font-size: 2rem; font-weight: bold; border: 1px solid black;'>[HIBA]</div>";
        document.getElementById('barcode').innerHTML = "[HIBA]";
    } else {
        // Kép elem létrehozása
        let imgEl = document.createElement("img");
        imgEl.src = "picture/" + f_id + ".png";
        imgEl.style.width = "500px";
        imgEl.style.height = "300px";
        imgEl.style.border = "1px solid black";
        
        // Ha a kép nem található (pl. 404-es hiba), lecseréljük a piros dobozra
        imgEl.onerror = function() {
            document.getElementById('picture').innerHTML = "<div style='width: 500px; height: 300px; background-color: #cc0000; color: white; display: flex; justify-content: center; align-items: center; font-size: 2rem; font-weight: bold; border: 1px solid black;'>[NINCS KÉP]</div>";
        };
        
        // Kép hozzáadása a weboldalhoz
        let picContainer = document.getElementById('picture');
        picContainer.innerHTML = "";
        picContainer.appendChild(imgEl);

        document.getElementById('barcode').innerHTML = "<a href='barcode/" + f_id + ".png'>QR KÓD</a>";
    }
})();

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

document.onload = rando();

async function rando(){
    let result = await makeRequest(file);
    let time = Date.parse(result)/1000;
    var utcSeconds = time;
    var d = new Date(0); // The 0 there is the key, which sets the date to the epoch
    document.getElementById('modified').innerHTML = new Date(d.setUTCSeconds(utcSeconds)).toLocaleString('hu-hu', { timeZone: 'Europe/Budapest', weekday:"long", year:"numeric", month:"short", day:"numeric", hour:"2-digit", minute:"2-digit"})
}

