<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Super ChatGPT Bros</title>

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    overflow:hidden;
    background:#87CEEB;
    font-family:Arial,sans-serif;
}

#hud{
    position:fixed;
    top:15px;
    left:15px;
    z-index:999;
    color:white;
    font-size:24px;
    font-weight:bold;
    text-shadow:2px 2px 4px #000;
}

#game{
    position:relative;
    width:100vw;
    height:100vh;
    overflow:hidden;
}

#world{
    position:absolute;
    width:4000px;
    height:100vh;
}

.ground{
    position:absolute;
    bottom:0;
    width:4000px;
    height:100px;
    background:#3aa63a;
}

.player{
    position:absolute;
    width:42px;
    height:60px;
    border-radius:8px;
    background:#d22;
    border:3px solid #900;
}

.platform{
    position:absolute;
    background:#8B5A2B;
    border:3px solid #5b3718;
}

.coin{
    position:absolute;
    width:24px;
    height:24px;
    border-radius:50%;
    background:gold;
    box-shadow:0 0 12px yellow;
}

.enemy{
    position:absolute;
    width:40px;
    height:40px;
    background:brown;
    border-radius:50%;
}

.flag{
    position:absolute;
    width:10px;
    height:220px;
    background:white;
}

.flagTop{
    position:absolute;
    width:50px;
    height:40px;
    background:red;
    left:10px;
}

#message{
    position:fixed;
    inset:0;
    display:none;
    justify-content:center;
    align-items:center;
    flex-direction:column;
    background:rgba(0,0,0,.8);
    color:white;
    font-size:40px;
    z-index:1000;
}

.cloud{
    position:absolute;
    background:white;
    border-radius:50px;
}
</style>
</head>
<body>

<div id="hud">
    Monedas: <span id="score">0</span>
</div>

<div id="message">
    <h1 id="msgTitle"></h1>
    <p style="font-size:22px;margin-top:20px;">
        Presiona F5 para volver a jugar
    </p>
</div>

<div id="game">
    <div id="world">

        <div class="cloud" style="left:200px;top:80px;width:120px;height:50px"></div>
        <div class="cloud" style="left:900px;top:120px;width:160px;height:60px"></div>
        <div class="cloud" style="left:1700px;top:70px;width:140px;height:55px"></div>

        <div id="player" class="player"></div>

        <div class="ground"></div>

        <!-- Plataformas -->
        <div class="platform" style="left:350px;bottom:180px;width:140px;height:25px"></div>
        <div class="platform" style="left:650px;bottom:260px;width:140px;height:25px"></div>
        <div class="platform" style="left:1000px;bottom:180px;width:180px;height:25px"></div>
        <div class="platform" style="left:1450px;bottom:260px;width:150px;height:25px"></div>
        <div class="platform" style="left:1800px;bottom:330px;width:150px;height:25px"></div>
        <div class="platform" style="left:2200px;bottom:220px;width:200px;height:25px"></div>
        <div class="platform" style="left:2800px;bottom:300px;width:200px;height:25px"></div>

        <!-- Monedas -->
        <div class="coin" style="left:390px;bottom:230px"></div>
        <div class="coin" style="left:700px;bottom:310px"></div>
        <div class="coin" style="left:1050px;bottom:230px"></div>
        <div class="coin" style="left:1500px;bottom:310px"></div>
        <div class="coin" style="left:1850px;bottom:380px"></div>
        <div class="coin" style="left:2260px;bottom:270px"></div>
        <div class="coin" style="left:2880px;bottom:350px"></div>

        <!-- Enemigos -->
        <div class="enemy" style="left:800px;bottom:100px"></div>
        <div class="enemy" style="left:1600px;bottom:100px"></div>
        <div class="enemy" style="left:2600px;bottom:100px"></div>

        <!-- Meta -->
        <div class="flag" style="left:3700px;bottom:100px">
            <div class="flagTop"></div>
        </div>

    </div>
</div>

<script>

const player = document.getElementById("player");
const world = document.getElementById("world");
const scoreEl = document.getElementById("score");
const enemies = [...document.querySelectorAll(".enemy")];
const platforms = [...document.querySelectorAll(".platform")];
const coins = [...document.querySelectorAll(".coin")];
const flag = document.querySelector(".flag");

let score = 0;

let playerData = {
    x:100,
    y:100,
    w:42,
    h:60,
    vx:0,
    vy:0,
    speed:6,
    jump:17,
    grounded:false
};

const keys = {};
const gravity = 0.8;

document.addEventListener("keydown",e=>{
    keys[e.code]=true;

    if(e.code==="Space" && playerData.grounded){
        playerData.vy = playerData.jump;
        playerData.grounded=false;
    }
});

document.addEventListener("keyup",e=>{
    keys[e.code]=false;
});

function rect(a,b){
    return(
        a.x < b.x+b.w &&
        a.x+a.w > b.x &&
        a.y < b.y+b.h &&
        a.y+a.h > b.y
    );
}

function gameOver(text){
    document.getElementById("message").style.display="flex";
    document.getElementById("msgTitle").textContent=text;
    cancelAnimationFrame(loopId);
}

enemies.forEach(enemy=>{
    enemy.dir = Math.random() > .5 ? 1 : -1;
});

function updateEnemies(){

    enemies.forEach(enemy=>{

        let x = parseFloat(enemy.style.left);

        x += enemy.dir * 2;

        if(x < 600 || x > 3400){
            enemy.dir *= -1;
        }

        enemy.style.left = x + "px";

        let e = {
            x,
            y:window.innerHeight-140,
            w:40,
            h:40
        };

        let p = {
            x:playerData.x,
            y:window.innerHeight-playerData.y-playerData.h,
            w:playerData.w,
            h:playerData.h
        };

        if(rect(e,p)){

            let playerBottom =
                (window.innerHeight-playerData.y);

            let enemyTop =
                (window.innerHeight-140);

            if(playerBottom < enemyTop+15){
                enemy.remove();
            }else{
                gameOver("💀 Has perdido");
            }
        }
    });
}

function update(){

    playerData.vx = 0;

    if(keys["ArrowLeft"]) playerData.vx = -playerData.speed;
    if(keys["ArrowRight"]) playerData.vx = playerData.speed;

    playerData.x += playerData.vx;

    playerData.vy -= gravity;
    playerData.y += playerData.vy;

    if(playerData.y <= 100){
        playerData.y = 100;
        playerData.vy = 0;
        playerData.grounded = true;
    }

    platforms.forEach(platform=>{

        let px = platform.offsetLeft;
        let py = parseInt(platform.style.bottom);
        let pw = platform.offsetWidth;
        let ph = platform.offsetHeight;

        let touching =
            playerData.x + playerData.w > px &&
            playerData.x < px + pw &&
            playerData.y <= py + ph + 15 &&
            playerData.y >= py &&
            playerData.vy <= 0;

        if(touching){
            playerData.y = py + ph;
            playerData.vy = 0;
            playerData.grounded = true;
        }
    });

    coins.forEach(coin=>{

        if(coin.dataset.taken) return;

        let cx = coin.offsetLeft;
        let cy = parseInt(coin.style.bottom);

        let hit =
            playerData.x + playerData.w > cx &&
            playerData.x < cx + 24 &&
            playerData.y + playerData.h > cy &&
            playerData.y < cy + 24;

        if(hit){
            coin.dataset.taken = true;
            coin.style.display = "none";
            score++;
            scoreEl.textContent = score;
        }
    });

    let flagX = flag.offsetLeft;

    if(playerData.x > flagX - 50){
        gameOver("🏆 ¡Nivel completado!");
    }

    player.style.left = playerData.x + "px";
    player.style.bottom = playerData.y + "px";

    let camera =
        Math.max(
            0,
            playerData.x - window.innerWidth/2
        );

    world.style.transform =
        `translateX(${-camera}px)`;

    updateEnemies();

    loopId = requestAnimationFrame(update);
}

let loopId;
update();

</script>
</body>
</html>