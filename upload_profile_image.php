<?php
session_start();
include_once 'validateToken.php';
include_once 'connection.php';

if (!validateToken()) {
    $_SESSION['msg'] = "Error: Incorrect email or password";
    header('Location: login.php');
    exit();
}

// Obtém o ID do usuário da URL
if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    // Verifica se um arquivo foi enviado
    if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] === 0) {
        // Diretório onde as imagens de perfil serão armazenadas
        $upload_directory = 'imagens/';

        // Nome do arquivo
        $file_name = $_FILES['profileImage']['name'];

        // Caminho completo para o arquivo no servidor
        $file_path = $upload_directory . $file_name;

        // Verifica se o arquivo é uma imagem válida
        $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');
        $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);

        if (!in_array(strtolower($file_extension), $allowed_extensions)) {
            echo "Erro: Apenas imagens JPG, JPEG, PNG ou GIF são permitidas.";
            exit();
        }

        // Move o arquivo para o diretório de upload
        if (move_uploaded_file($_FILES['profileImage']['tmp_name'], $file_path)) {
            try {
                // Atualiza o caminho da imagem de perfil no banco de dados
                $update_profile_image = "UPDATE user SET profile_image_path = :profile_image_path WHERE use_id = :user_id";
                $stmt = $pdo->prepare($update_profile_image);
                $stmt->bindParam(':profile_image_path', $file_path, PDO::PARAM_STR);
                $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $stmt->execute();

                echo "Imagem de perfil enviada com sucesso!";
                header("Location: perfil.php?user_id=$user_id"); // Redireciona de volta para o perfil após o upload
                exit();
            } catch (PDOException $e) {
                echo "Erro ao atualizar o caminho da imagem de perfil: " . $e->getMessage();
            }
        } else {
            echo "Erro no envio da imagem de perfil.";
        }
    } else {
        echo "Erro: Nenhum arquivo de imagem enviado.";
    }
} else {
    echo "ID do usuário não fornecido na URL.";
}
?>
