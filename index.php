<?php
require 'config.php';
require 'Slim/Slim.php';

\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();

//POST
$app->post('/login','login');
$app->post('/update-produk','updateProduk');

//GET
$app->get('/produks', 'getProduks');
$app->get('/produks/:tanggal', 'getProduksByTanggal');
$app->get('/penggunas', 'getPenggunas');
$app->get('/pengguna/:id', 'getPengguna');

$app->run();

function getProduks() {
    $sql = "SELECT id, kode, nama, gambar, keterangan, harga, Format(harga, '##.##0') AS vharga FROM produk";
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

function getProduksByTanggal($tanggal) {
    $sql = "SELECT id, kode, nama, gambar, keterangan, harga, Format(harga, '##.##0') AS vharga, tanggal FROM produk WHERE tanggal='".$tanggal."'";
    try {
        $db = getDB();
        $stmt = $db->query($sql);
        $result = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        echo json_encode($result);
    }
        catch(PDOException $e) {
        echo json_encode($e->getMessage());
    }
}

function updateProduk(){
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());

    $id=$data->id;
    $harga=$data->harga;

    try {
       
        $db = getDB();
        $sql = "UPDATE produk SET harga=:harga WHERE id=:id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam("harga", $harga, PDO::PARAM_STR);
        $stmt->bindParam("id", $id, PDO::PARAM_STR);
        $stmt->execute();
        $db = null;

    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

function getPenggunas() {
    $sql = "SELECT nama_lengkap, telepon, email, alamat FROM pengguna";
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
    $sql = "SELECT nama_lengkap, telepon, email, alamat FROM pengguna WHERE id=$id";
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

function getUserDetails($id) {
    try {
        $db = getDB();
        $sql = "SELECT id, nama_lengkap, email, telepon, username, alamat FROM pengguna WHERE id=:id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam("id", $id,PDO::PARAM_STR);
        $stmt->execute();
        $userDetails = $stmt->fetch(PDO::FETCH_OBJ);
        $userDetails->token = apiToken($id);
        $db = null;
        return $userDetails;
        
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
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
        
        // if(!empty($userData))
        // {
        //     $user_id=$userData->user_id;
        //     $userData->token = apiToken($user_id);
        // }
        
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
        $sql = "SELECT id, nama_lengkap, email, telepon, username, alamat FROM pengguna WHERE username=:input or email=:input";
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
