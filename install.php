<?php
set_time_limit(0);
ini_set('memory_limit','512M');

$ROOT = __DIR__;
$ENV_FILE = "$ROOT/.env";
$LOCK_FILE = "$ROOT/storage/install.lock";
$IS_WIN = strtoupper(substr(PHP_OS,0,3)) === 'WIN';

if (file_exists($LOCK_FILE)) die("<h2>❌ Application already installed.</h2>");

/* ---------------- UTILITIES ---------------- */
function js($s){ echo "<script>$s</script>"; @ob_flush(); @flush(); }
function logMsg($m){ js("log(".json_encode($m).")"); }
function progress($p){ js("setProgress($p)"); }

function execLogged($cmd, $cwd = null){
    if ($cwd) $cmd = "cd ".escapeshellarg($cwd)." && $cmd";
    exec($cmd . " 2>&1", $out, $code);
    foreach ($out as $line) logMsg($line);
    return $code === 0;
}

function fixPermissions($root, $win){
    $paths = ["$root/storage", "$root/bootstrap/cache"];
    foreach ($paths as $p) {
        if (!is_dir($p)) return false;
        if ($win) {
            if (!is_writable($p)) return false;
        } else {
            exec("chmod -R 775 ".escapeshellarg($p));
        }
    }
    return true;
}

function testDb($h,$d,$u,$p){
    try{
        new PDO("mysql:host=$h;dbname=$d;charset=utf8mb4",$u,$p,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
        return true;
    }catch(Throwable $e){
        logMsg("❌ DB Error: ".$e->getMessage());
        return false;
    }
}

/* ---------------- SYSTEM CHECK ---------------- */
function systemCheck(&$nodeAvailable){
    $r = [];

    $r['PHP'] = ['ok'=>version_compare(PHP_VERSION,'8.2.0','>='),'cur'=>PHP_VERSION,'req'=>'>= 8.2.0'];

    foreach(['openssl','pdo','mbstring','tokenizer','xml','ctype','json','fileinfo','gd'] as $e){
        $r[$e] = ['ok'=>extension_loaded($e),'cur'=>extension_loaded($e)?'Enabled':'Missing','req'=>'Enabled'];
    }

    exec('composer --version',$o,$c);
    $r['Composer'] = ['ok'=>$c===0,'cur'=>$c===0?$o[0]:'Not found','req'=>'Required'];

    $nodeAvailable = false;
    $nodeCmds = [
        'node --version',
        '"C:\\Program Files\\nodejs\\node.exe" --version',
        '"C:\\Program Files (x86)\\nodejs\\node.exe" --version'
    ];
    foreach($nodeCmds as $cmd){
        exec($cmd,$o2,$c2);
        if($c2===0){ $nodeAvailable=true; break; }
    }
    $r['Node.js']=['ok'=>$nodeAvailable,'cur'=>$nodeAvailable?'Detected':'Not detected','req'=>'Optional'];

    return $r;
}

/* ---------------- WIZARD ---------------- */
$STEP = $_POST['step'] ?? 1;
$nodeAvailable = false;
$sys = systemCheck($nodeAvailable);
$block = false;
foreach($sys as $k=>$v) if(!$v['ok'] && $v['req']!=='Optional') $block = true;

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Laravel Installer</title>
<style>
body{background:#020617;color:#e5e7eb;font-family:system-ui}
.box{width:780px;margin:25px auto;background:#020617;padding:30px;border-radius:12px}
.hidden{display:none}
.stepper{display:flex;justify-content:space-between;margin-bottom:20px}
.step{flex:1;text-align:center}
.circle{width:34px;height:34px;border-radius:50%;margin:auto;line-height:34px;background:#374151}
.step.active .circle,.step.done .circle{background:#22c55e}
table{width:100%;border-collapse:collapse}
th,td{border:1px solid #374151;padding:10px}
th{background:#111827}
input,button{width:100%;padding:10px;margin:6px 0}
button{background:#22c55e;border:none;border-radius:6px;font-weight:bold}
.log{background:#000;color:#22c55e;height:260px;overflow:auto;padding:10px;font-family:monospace}
.progress{height:18px;background:#1e293b;border-radius:20px;overflow:hidden}
.progress div{height:100%;width:0;background:#22c55e}
</style>
<script>
function show(id){document.querySelectorAll('.panel').forEach(p=>p.classList.add('hidden'));document.getElementById(id).classList.remove('hidden');}
function log(t){let l=document.getElementById('log');l.innerHTML+=t+"<br>";l.scrollTop=l.scrollHeight;}
function setProgress(p){document.getElementById('bar').style.width=p+"%";}
function toggleSecondary(){document.getElementById('db2').classList.toggle('hidden');}
</script>
</head>
<body>

<!-- STEP 1 -->
<div class="box panel" id="sys">
<h3>System Requirements</h3>
<table>
<tr><th>Item</th><th>Status</th><th>Current</th><th>Required</th></tr>
<?php foreach($sys as $k=>$v): ?>
<tr><td><?=$k?></td><td><?=$v['ok']?'✅':'❌'?></td><td><?=$v['cur']?></td><td><?=$v['req']?></td></tr>
<?php endforeach ?>
</table>
<?php if(!$block): ?><button onclick="show('db')">Next</button><?php else:?><p style="color:red">Fix failed requirements.</p><?php endif ?>
</div>

<!-- STEP 2 -->
<div class="box panel hidden" id="db">
<form method="POST">
<input type="hidden" name="step" value="3">
<h3>Primary Database</h3>
<input name="db_host" required placeholder="DB Host">
<input name="db_database" required placeholder="DB Name">
<input name="db_username" required placeholder="DB User">
<input name="db_password" placeholder="DB Password">
<label><input type="checkbox" name="use_db2" onchange="toggleSecondary()"> Enable Secondary DB</label>
<div id="db2" class="hidden">
<h4>Secondary Database</h4>
<input name="db2_host" placeholder="DB2 Host">
<input name="db2_database" placeholder="DB2 Name">
<input name="db2_username" placeholder="DB2 User">
<input name="db2_password" placeholder="DB2 Password">
</div>
<button>Next</button>
</form>
</div>

<!-- STEP 3 -->
<?php if($STEP==3):
if(!testDb($_POST['db_host'],$_POST['db_database'],$_POST['db_username'],$_POST['db_password'])){
    echo "<script>alert('Primary DB failed');show('db');</script>"; exit;
}
if(isset($_POST['use_db2'])){
    if(!testDb($_POST['db2_host'],$_POST['db2_database'],$_POST['db2_username'],$_POST['db2_password'])){
        echo "<script>alert('Secondary DB failed');show('db');</script>"; exit;
    }
}
?>
<div class="box panel" id="admin">
<form method="POST">
<input type="hidden" name="step" value="4">
<input type="hidden" name="payload" value="<?= htmlspecialchars(json_encode($_POST)) ?>">
<h3>Admin User</h3>
<input name="admin_name" required placeholder="Name">
<input name="admin_email" type="email" required placeholder="Email">
<input name="admin_pass" type="password" required placeholder="Password">
<button>Install</button>
</form>
</div>
<?php endif ?>

<!-- STEP 4 -->
<?php if($STEP==4):
$data=json_decode($_POST['payload'],true);
extract($data); extract($_POST);
?>
<div class="box">
<div class="progress"><div id="bar"></div></div>
<div class="log" id="log"></div>
</div>

<?php
/* 1. COMPOSER UPDATE */
logMsg("▶ Running composer update..."); progress(5);
execLogged("composer update --no-interaction", $ROOT);

/* 2. NPM INSTALL */
if ($nodeAvailable) { logMsg("▶ Running npm install..."); progress(15); execLogged("npm install",$ROOT); }

/* 3. GENERATE .ENV */
logMsg("▶ Generating .env..."); progress(30);
$env = <<<ENV
APP_NAME=Laravel
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=$db_host
DB_DATABASE=$db_database
DB_USERNAME=$db_username
DB_PASSWORD=$db_password

DB_SECOND_CONNECTION=mysql
DB_HOST_SECOND={$db2_host}
DB_DATABASE_SECOND={$db2_database}
DB_USERNAME_SECOND={$db2_username}
DB_PASSWORD_SECOND={$db2_password}
ENV;
file_put_contents($ENV_FILE, $env);

/* 4. APP KEY */
logMsg("▶ Generating app key..."); progress(40);
execLogged("php artisan key:generate",$ROOT);

/* 5. MIGRATIONS */
logMsg("▶ Running migrations..."); progress(50);
execLogged("php artisan migrate --force",$ROOT);

/* 6. SEEDERS */
logMsg("▶ Running seeders..."); progress(55);
execLogged("php artisan db:seed --force",$ROOT);

/* 6b. CREATE ADMIN USER */
logMsg("▶ Creating administrator user...");
require "$ROOT/vendor/autoload.php";
use Illuminate\Database\Capsule\Manager as DB;
$capsule = new DB;
$capsule->addConnection([
    'driver'=>'mysql','host'=>$db_host,'database'=>$db_database,
    'username'=>$db_username,'password'=>$db_password,'charset'=>'utf8mb4','collation'=>'utf8mb4_unicode_ci'
]);
$capsule->setAsGlobal(); $capsule->bootEloquent();
$exists = $capsule::table('users')->where('email',$admin_email)->exists();
if (!$exists) {
    $capsule::table('users')->insert([
        'name'=>$admin_name,'email'=>$admin_email,
        'password'=>password_hash($admin_pass,PASSWORD_BCRYPT),
        'role'=>0,'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')
    ]);
    logMsg("✅ Admin user created.");
} else { logMsg("⚠️ Admin already exists, skipping."); }

/* 7. STORAGE LINK */
logMsg("▶ Creating storage symlink..."); progress(65);
execLogged("php artisan storage:link",$ROOT);

/* 8. PERMISSIONS */
logMsg("▶ Fixing permissions...");
fixPermissions($ROOT,$IS_WIN);

/* 9. OPTIMIZE CLEAR */
logMsg("▶ Clearing optimizations...");
execLogged("php artisan optimize:clear",$ROOT);

/* 10. NPM BUILD */
if ($nodeAvailable && file_exists("$ROOT/package.json")) { logMsg("▶ Building frontend assets..."); progress(85); execLogged("npm run build",$ROOT); }

/* 11. LOCK FILE */
logMsg("▶ Finalizing installation...");
file_put_contents($LOCK_FILE,'installed');

/* 12. SELF DELETE & REDIRECT */
unlink(__FILE__); progress(100);
js("setTimeout(()=>location.href='/public',3000)");
?>
<?php endif ?>
</body>
</html>
