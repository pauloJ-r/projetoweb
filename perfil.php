<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil</title>
    
</head>
<body>

<?php 
session_start();
include_once 'validateToken.php'; 
include_once 'connection.php';


if (!validateToken()) {
    $_SESSION['msg'] = "Error: Incorrect email or password";
    header('Location: login.php');
    exit();
} 

$user_name = getName();

// Obtém o ID do usuário da URL
if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

   

    
    // Consulta SQL para obter informações do usuário
    $sql = "SELECT use_name, profile_image_path, use_periodo FROM user WHERE use_id = :user_id";
    $sql2 = "SELECT use_id FROM user where use_name = :user_name";
    try {
        // Preparar e executar a consulta
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $stmt2 = $pdo->prepare($sql2);
        $stmt2->bindParam(':user_name', $user_name, PDO::PARAM_STR);
        $stmt2->execute(); 
        $row2 = $stmt2 ->fetch(PDO::FETCH_ASSOC);
        
        // Exibir informações do perfil do usuário
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $userName = $row['use_name'];
            $profileImagePath = $row['profile_image_path'];
            $use_periodo = $row['use_periodo'];
            $user_id_logado = $row2['use_id'];
            echo "<h1>Perfil de $userName</h1>";
            
        
            // Exibir a imagem de perfil atual ou a mensagem para adicionar uma imagem
            if (!empty($profileImagePath)) {
                echo "<img src='$profileImagePath' alt='Imagem de Perfil'>";
            } else {
                echo "<p>Adicione uma imagem de perfil.</p>";
            }

            // Exibir o período do usuário
            echo "<p>Período: $use_periodo</p>";
            
            if($user_id_logado == $user_id) {
            
            // Formulário para enviar uma nova imagem
            echo "<form action='upload_profile_image.php?user_id=$user_id' method='post' enctype='multipart/form-data'>";
            echo "<input type='file' name='profileImage' id='profileImage' accept='image/*' required>";
            echo "<input type='submit' value='Enviar Imagem'>";
            echo "</form>";
        } 
        } else {
            echo "Usuário não encontrado.";
        }
    
    } catch (PDOException $e) {
        die("Erro na execução da consulta: " . $e->getMessage());
    }
    
} else {
    echo "ID do usuário não fornecido na URL.";
}

// Fechar a conexão
$pdo = null;
?>
</body>
</html>
