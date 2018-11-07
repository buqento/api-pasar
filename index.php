<?php
require 'config.php';
require 'Slim/Slim.php';

\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();

//POST
$app->post('/login','login');
$app->post('/signup','signup');
$app->post('/postPesanan','postPesanan');
$app->post('/updateProfil','updateProfil');
$app->post('/deletePesanan','deletePesanan');
$app->post('/addToPesanan','addToPesanan');

//GET
$app->get('/produks', 'getProduks');
$app->get('/penggunas', 'getPenggunas');
$app->get('/pengguna/:id', 'getPengguna');
$app->get('/getPesanans/:id','getPesanans');
$app->get('/getTotalPesananById/:id','getTotalPesananById');

$app->run();

function addToPesanan(){
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());

    $penggunaId=$data->penggunaId;
    $produkId=$data->produkId;
    $jumlah=$data->jumlah;
    $totalBayar=$data->totalBayar;
    $status=$data->status;
    $keterangan=$data->keterangan;

    try {
        $db = getDB();
        $sql = "INSERT INTO pesanan (pengguna_id, produk_id, jumlah, total_bayar, status, keterangan) VALUES (:pengguna_id, :produk_id, :jumlah, :total_bayar, :status, :keterangan)";
        $stmt = $db->prepare($sql);
        $stmt->bindParam("pengguna_id", $penggunaId, PDO::PARAM_STR);
        $stmt->bindParam("produk_id", $produkId, PDO::PARAM_STR);
        $stmt->bindParam("jumlah", $jumlah, PDO::PARAM_STR);
        $stmt->bindParam("total_bayar", $totalBayar, PDO::PARAM_STR);
        $stmt->bindParam("status", $status, PDO::PARAM_STR);
        $stmt->bindParam("keterangan", $keterangan, PDO::PARAM_STR);
        $stmt->execute();
        $db = null;
        echo '{"success":{"text":"Berhasil"}}';
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
    
}

function getProduks() {
$sql = "SELECT * FROM produk";
  try {
    $db = getDB();
    $stmt = $db->query($sql);
    $users = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
    echo json_encode($users);
  }
  catch(PDOException $e) {
    echo json_encode($e->getMessage());
  }
}

function getPenggunas() {
$sql = "SELECT * FROM pengguna";
  try {
    $db = getDB();
    $stmt = $db->query($sql);
    $users = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
    echo json_encode($users);
  }
  catch(PDOException $e) {
    echo json_encode($e->getMessage());
  }
}

function getPengguna($id) {
$sql = "SELECT * FROM pengguna WHERE id=$id";
  try {
    $db = getDB();
    $stmt = $db->query($sql);
    $users = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
    echo json_encode($users);
  }
  catch(PDOException $e) {
    echo json_encode($e->getMessage());
  }
}

function postPesanan(){
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());

    $pengguna_id=$data->pengguna_id;
    $produk_id=$data->produk_id;   
    $jumlah=$data->jumlah;     
    $total_bayar=$data->total_bayar;
    $keterangan=$data->keterangan;

    try {
        $db = getDB();
        $sql = "INSERT INTO pesanan (pengguna_id, produk_id, jumlah, total_bayar, keterangan) 
        VALUES (:pengguna_id, :produk_id, :jumlah, :total_bayar, :keterangan)";
        $stmt = $db->prepare($sql);
        $stmt->bindParam("pengguna_id", $pengguna_id, PDO::PARAM_STR);
        $stmt->bindParam("produk_id", $produk_id, PDO::PARAM_STR);
        $stmt->bindParam("jumlah", $jumlah, PDO::PARAM_STR);
        $stmt->bindParam("total_bayar", $total_bayar, PDO::PARAM_STR);
        $stmt->bindParam("keterangan", $keterangan, PDO::PARAM_STR);
        $stmt->execute();
        $db = null;
        echo '{"success":{"text":"Saved"}}';
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

function updateProfil(){
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());

    $id=$data->id;
    $nama_lengkap=$data->namaLengkap;   
    $telepon=$data->telepon;  
    $alamat=$data->alamat;

    try {
        $db = getDB();
        $sql = "UPDATE pengguna SET nama_lengkap=:nama_lengkap, telepon=:telepon, alamat=:alamat WHERE id=:id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam("nama_lengkap", $nama_lengkap, PDO::PARAM_STR);
        $stmt->bindParam("telepon", $telepon, PDO::PARAM_STR);
        $stmt->bindParam("alamat", $alamat, PDO::PARAM_STR);
        $stmt->bindParam("id", $id, PDO::PARAM_STR);
        $stmt->execute();
        $db = null;
        echo '{"success":{"text":"Updated"}}';
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

function deletePesanan(){
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
    $id=$data->id;
    try {
        $db = getDB();
        $sql = "DELETE FROM pesanan WHERE id=:id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam("id", $id, PDO::PARAM_INT);
        $stmt->execute();
        $db = null;
        echo '{"success":{"text":"Pesanan deleted"}}';
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }   
}

function getPesanans($id) {
// $sql = "SELECT * FROM summary_pesanan WHERE pengguna_id=$id";

$sql = "SELECT pesanan.id, pesanan.tanggal, pesanan.pengguna_id, pesanan.produk_id, pesanan.jumlah,
Format(pesanan.total_bayar, '##.##0') AS total_bayar, pesanan.keterangan, produk.nama, produk.foto
FROM pesanan INNER JOIN produk
ON pesanan.produk_id = produk.id 
WHERE pesanan.status = 0 AND pesanan.pengguna_id=$id 
AND substr(tanggal,1,10)=substr(current_timestamp(),1,10)
ORDER BY pesanan.tanggal DESC";
  try {
    $db = getDB();
    $stmt = $db->query($sql);
    $pesanans = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
    echo json_encode($pesanans);
  }
  catch(PDOException $e) {
    echo json_encode($e->getMessage());
  }
}

function getTotalPesananById($id) {
$sql = "SELECT SUM(total_bayar) AS total, COUNT(id) AS jumlah FROM pesanan WHERE pengguna_id=$id AND status=0 AND substr(tanggal,1,10)=substr(current_timestamp(),1,10)";
  try {
    $db = getDB();
    $stmt = $db->query($sql);
    $pesanans = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
    echo json_encode($pesanans);
  }
  catch(PDOException $e) {
    echo json_encode($e->getMessage());
  }
}

function login() {
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
    try {
        $db = getDB();
        $userData ='';
        $sql = "SELECT id, nama_lengkap, email, telepon, username, alamat FROM pengguna WHERE (username=:username or email=:username) and password=:password";
        $stmt = $db->prepare($sql);
        $stmt->bindParam("username", $data->username, PDO::PARAM_STR);
        $password=hash('sha256',$data->password);
        $stmt->bindParam("password", $password, PDO::PARAM_STR);
        $stmt->execute();
        $mainCount=$stmt->rowCount();
        $userData = $stmt->fetch(PDO::FETCH_OBJ);
        
        if(!empty($userData))
        {
            $user_id=$userData->user_id;
            $userData->token = apiToken($user_id);
        }
        
        $db = null;
         if($userData){
               $userData = json_encode($userData);
                echo '{"userData": ' .$userData . '}';
            } else {
               echo '{"error":{"text":"Bad request wrong username and password"}}';
            }

           
    }
    catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

function signup() {
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
    $username = $data->username;
    $password = $data->password;
    $nama_lengkap = $data->namaLengkap;
    $email = $data->email;
    $telepon = $data->telepon;  
    $alamat = $data->alamat;  
    try {
        
        $username_check = preg_match('~^[A-Za-z0-9_]{3,20}$~i', $username);
        $password_check = preg_match('~^[A-Za-z0-9!@#$%^&*()_]{6,20}$~i', $password);
        $email_check = preg_match('~^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.([a-zA-Z]{2,4})$~i', $email);
        
        if (strlen(trim($username))>0 && strlen(trim($password))>0 && strlen(trim($email))>0 && $email_check>0 && $username_check>0 && $password_check>0)
        {
            
            $db = getDB();
            $userData = '';
            $sql = "SELECT id FROM pengguna WHERE username=:username or email=:email";
            $stmt = $db->prepare($sql);
            $stmt->bindParam("username", $username,PDO::PARAM_STR);
            $stmt->bindParam("email", $email,PDO::PARAM_STR);
            $stmt->execute();
            $mainCount=$stmt->rowCount();
            $created=time();
            if($mainCount==0)
            {
                
                /*Inserting user values*/
                $sql1="INSERT INTO pengguna (username, password, email, nama_lengkap, telepon, alamat) VALUES (:username, :password, :email, :nama_lengkap, :telepon, :alamat)";
                $stmt1 = $db->prepare($sql1);
                $stmt1->bindParam("username", $username,PDO::PARAM_STR);
                $password=hash('sha256',$data->password);
                $stmt1->bindParam("password", $password,PDO::PARAM_STR);
                $stmt1->bindParam("email", $email,PDO::PARAM_STR);
                $stmt1->bindParam("nama_lengkap", $nama_lengkap,PDO::PARAM_STR);
                $stmt1->bindParam("telepon", $telepon,PDO::PARAM_STR);
                $stmt1->bindParam("alamat", $alamat,PDO::PARAM_STR);
                $stmt1->execute();
                
                $userData=internalUserDetails($email);
                
            }
            
            $db = null;
         

            if($userData){
               $userData = json_encode($userData);
                echo '{"userData": ' .$userData . '}';
            } else {
               echo '{"error":{"text":"Error"}}';
            }

           
        }
        else{
            echo '{"error":{"text":"Data tidak valid"}}';
        }
    }
    catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

function internalUserDetails($input) {
    try {
        $db = getDB();
        $sql = "SELECT id, nama_lengkap, email, telepon, username FROM pengguna WHERE username=:input or email=:input";
        $stmt = $db->prepare($sql);
        $stmt->bindParam("input", $input,PDO::PARAM_STR);
        $stmt->execute();
        $usernameDetails = $stmt->fetch(PDO::FETCH_OBJ);
        $usernameDetails->token = apiToken($usernameDetails->user_id);
        $db = null;
        return $usernameDetails;
        
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
    
}
?>
