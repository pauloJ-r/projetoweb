<?php
session_start();
include_once 'validateToken.php'; 
include_once 'connection.php';

if (!validateToken()) {
    $_SESSION['msg'] = "Error: Incorrect email or password";
    header('Location: login.php');
    exit();
} 

echo "Welcome " . getName();



// Se o parâmetro 'id' estiver presente na URL
if (isset($_GET['id'])) {
    $post_id = $_GET['id'];

    $token = $_COOKIE['token'];

      if(!isset($token)) exit(); 

         $arrayToken = explode('.', $token);
          $payload = $arrayToken[1];
          $userData = base64_decode($payload);
         $userData = json_decode($userData);

    // Se o formulário de comentário foi enviado
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comentario'])) {
        $comentario = $_POST['comentario'];

        // Inserir o comentário no banco de dados
        $sqlInsertComentario = "INSERT INTO comentarios (comentario, data_publicacao, mensagem_id, user_id) 
                               VALUES (:comentario, CURRENT_TIMESTAMP, :mensagem_id, :user_id)";

        try {
            $stmt = $pdo->prepare($sqlInsertComentario);
            $stmt->bindParam(':comentario', $comentario, PDO::PARAM_STR);
            $stmt->bindParam(':mensagem_id', $post_id, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $userData->id);
            $stmt->execute();

            // Redirecionar para evitar o reenvio do formulário ao atualizar a página
            header("Location: resposta.php?id=$post_id");
            exit();
        } catch (PDOException $e) {
            die("Erro na execução da consulta: " . $e->getMessage());
        }
    }

// Consulta para obter a mensagem específica e suas respostas
$sql = "SELECT mensagens.id as mensagem_id,mensagens.user_id as mensagem_user_id, mensagens.mensagem, mensagens.data_publicacao,
               user_original.use_name as original_user_name,
               comentarios.id as comentario_id, comentarios.comentario, 
               comentarios.data_publicacao as comentario_data, comentarios.user_id as comentario_user_id,
               user_resposta.use_name as resposta_user_name
        FROM mensagens 
        JOIN user as user_original ON mensagens.user_id = user_original.use_id 
        LEFT JOIN comentarios ON mensagens.id = comentarios.mensagem_id
        LEFT JOIN user as user_resposta ON comentarios.user_id = user_resposta.use_id
        WHERE mensagens.id = :id
        ORDER BY comentarios.data_publicacao ASC";  

try {
    // Preparar e executar a consulta com um parâmetro
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $post_id, PDO::PARAM_INT);
    $stmt->execute();

    // Exibir a mensagem específica e suas respostas
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p><a href='perfil.php?user_id=" . $row['mensagem_user_id'] .  "'><strong>" . $row['original_user_name'] . "</strong></a>" . "</p>";
        echo "<p>" . $row['mensagem'] . "</p>";
        echo "<p>Data de Publicação: " . $row['data_publicacao'] . "</p>";

        // Exibir respostas se existirem
        while ($row && $row['comentario_id'] !== null) {
            echo "<p><a href='perfil.php?user_id=" . $row['comentario_user_id'] .  "'><strong>" . $row['resposta_user_name'] . ":" . "</strong></a>". "</br>". $row['comentario'] . "</p>";
          
            echo "<p>Data de Publicação da Resposta: " . $row['comentario_data'] . "</p>";

            $row = $stmt->fetch(PDO::FETCH_ASSOC); // Avançar para a próxima resposta
        }
    } else {
        echo "Nenhuma mensagem encontrada com o ID fornecido.";
    }
} catch (PDOException $e) {
    die("Erro na execução da consulta: " . $e->getMessage());
        }


        // Formulário para postar um novo comentário
        echo "<form method='POST' action='resposta.php?id=$post_id'>";
        echo "<label for='comentario'>Comente:</label>";
        echo "<textarea name='comentario' id='comentario' required></textarea><br>";
        echo "<input type='submit' value='Postar Comentário'>";
        echo "</form>";

        echo "<hr>";
    } else {
        echo "Nenhuma mensagem encontrada com o ID fornecido.";
    }
    echo "<a href='home.php'>Voltar para Home</a>";

$pdo = null;
?>
