<?php
session_start();
include_once 'connection.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<link rel="stylesheet" href="logi.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <header>
    <section class="NavBar">
                <a class="semefeito" href="index.php">
                    <img src="./imagens/icone.png" alt="">
                </a>
                <a href="#">Blog</a>
                <a href="#">Sobre</a>
                <a href="#">Linguagens</a>
                <div class="buttons-header">
                    <button class="button-entrar" onclick="window.location.href='login.php'">Entrar</button>
                    <button class="button-cadastrar" onclick="window.location.href='register.php'">Cadastrar</button>
                </div>
            </section>
    </header>
    <main>

        <?php
        // echo password_hash('123456', PASSWORD_DEFAULT);

        $data = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        // var_dump($data);

        if (!empty($data['signIn'])) {
            // var_dump($data);

            $selectUserQuery = "SELECT use_id, use_name, use_email, use_password
					FROM user
					WHERE use_email =:use_email
					LIMIT 1";

            $resultUser = $pdo->prepare($selectUserQuery);
            $resultUser->bindParam(':use_email', $data['use_email']);
            $resultUser->execute();

            if (($resultUser) && ($resultUser->rowCount() != 0)) {
                $rowUser = $resultUser->fetch(PDO::FETCH_ASSOC);
                // var_dump($rowUser);

                if (password_verify($data['use_password'], $rowUser['use_password'])) {

                    // Header
                    $header = [
                        'alg' => 'HS256',
                        'typ' => 'JWT'
                    ];
                    // var_dump($header);

                    $header = json_encode($header);
                    // var_dump($header);

                    $header = base64_encode($header);
                    // var_dump($header);

                    // Payload

                    $expTime = time() + (7 * 24 * 60 * 60);

                    $payload = [
                        // 'iss' => 'localhost', // Domain API
                        // 'aud' => 'localhost',
                        'exp' => $expTime,
                        'id' => $rowUser['use_id'],
                        'name' => $rowUser['use_name']
                    ];

                    $payload = json_encode($payload);
                    // var_dump($payload);

                    $payload = base64_encode($payload);
                    // var_dump($payload);

                    // Signature

                    $key = "JR3rKQea7lgvtOM5wXCD";

                    $signature = hash_hmac('sha256', "$header.$payload", $key, true);

                    $signature = base64_encode($signature);
                    // var_dump($signature);

                    echo "<p> Token : $header.$payload.$signature </p>";

                    setcookie('token', "$header.$payload.$signature", time() + (7 * 24 * 60 * 60));
                    header('Location: home.php');

                } else {
                    $_SESSION['msg'] = "<p> Error: Incorrect email or password </p>";
                }

            } else {
                $_SESSION['msg'] = "<p> Error: Incorrect email or password </p>";
            }
        }
       
        if (isset($_SESSION['msg'])) {
            echo $_SESSION['msg'];
            unset($_SESSION['msg']);
        }
       
       ?>

        <?php 
            $email = "";
            if (isset($data['use_email'])) {
                $email = $data['use_email'];
            }
        ?>

        <form action="" method="POST">
            <label> Email: </label>
            <input type="text" name="use_email" placeholder="Email" value="<?= $email ?>"><br>

        <?php
        $password = "";
        if (isset($data['use_password'])) {
            $password = $data['use_password'];
        }
        ?>
            <label>Password</label>
            <input type="password" name="use_password" placeholder="Password" value="<?= $password ?>"><br>

            <input type="submit" name="signIn" value="Enter">
        </form>
    </main>
</body>
</html>