<?php
session_start();
include_once 'validateToken.php';
include_once 'connection.php';


if (!validateToken()) {
    $_SESSION['msg'] = "Error: Incorrect email or password";
    header('Location: login.php');
    exit();
}

function getLoggedInUserId()
{
    include 'connection.php';

    // Consulta SQL para obter o ID do usuário com base no nome
    $sql2 = "SELECT use_id FROM user WHERE use_name = :use_name";
    // Tente se conectar ao banco de dados usando PDO
    try {

        $use_name = getName();

        // Preparar e executar a consulta
        $stmt2 = $pdo->prepare($sql2);
        $stmt2->bindParam(':use_name', $use_name, PDO::PARAM_STR);
        $stmt2->execute();

        // Verificar se a consulta retornou alguma linha
        if ($stmt2->rowCount() > 0) {
            $row2 = $stmt2->fetch(PDO::FETCH_ASSOC);
            return $row2['use_id'];
        } else {
            // Caso o usuário não seja encontrado
            return 0;
        }
    } catch (PDOException $e) {
        // Lidar com erros de conexão com o banco de dados
        die("Erro na conexão com o banco de dados: " . $e->getMessage());
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <link rel="stylesheet" href="home.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>studyConnect</title>
</head>

<body>
    <header>
        <section class="NavBar">
            <a class="semefeito" href="home.php">
                <img src="./imagens/icone.png" alt="">
            </a>
            <a href="#">Blog</a>
            <a href="#">Sobre</a>
            <a href="#">Linguagens</a>
            <a href='perfil.php?user_id=<?php echo getLoggedInUserId(); ?>'>Perfil</a>
            <a href="logout.php">Sair</a>
        </section>
    </header>
  <div id="center-card">
    <div id="card">
        <form method="POST" action="salvar_mensagem.php">
            <div class="card-imagem">
            <img src="./imagens/geneton.png" alt="">
            </div>
            <div class="card-conteudo">
            <textarea name="titulo" id="titulo" placeholder="Titulo"></textarea>
            <textarea name="mensagem" id="mensagem" placeholder="Escreva seu Texto..."></textarea>
            <textarea name="tags" id="titulo" placeholder="Adicione Tags da Tecnologia... "></textarea>
            <input type="submit" value="Enviar">
            </div>
            
        </form>
    </div>
    </div>
    <?php
    // Consulta SQL para obter posts e comentários associados
    $sql = "SELECT mensagens.id,mensagens.user_id, mensagens.mensagem, mensagens.data_publicacao,user.use_periodo, user.use_name,
            COUNT(comentarios.id) AS total_comentarios
            FROM mensagens
            JOIN user ON mensagens.user_id = user.use_id 
            LEFT JOIN comentarios ON mensagens.id = comentarios.mensagem_id
            GROUP BY mensagens.id
            ORDER BY mensagens.data_publicacao DESC";

    try {
        // Preparar e executar a consulta
        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        // Exibir as mensagens e comentários
        if ($stmt->rowCount() > 0) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Adicionar um link para o perfil do usuário apenas ao redor do nome
                echo "<div class=" . "publicacao>";
                echo "<p><a href='perfil.php?user_id=" . $row['user_id'] .  "'><strong>" . $row['use_name'] . "</strong></a> - " . $row['use_periodo'] . "</p>";

                echo "<p>" . $row['mensagem'] . "</p>";
                echo "<p>Data de Publicação: " . $row['data_publicacao'] . "</p>";
                echo "<p>Total de Comentários: " . $row['total_comentarios'] . "</p>";

                // Adicionar um link para resposta.php com um parâmetro, como o ID da mensagem
                echo "<p><a href='resposta.php?id=" . $row['id'] . "'>Responder</a></p>";
                echo "<hr>";
                echo "</div>";
            }
        } else {
            echo "Nenhuma mensagem encontrada.";
        }
    } catch (PDOException $e) {
        die("Erro na execução da consulta: " . $e->getMessage());
    }

    // Fechar a conexão
    $pdo = null;
    ?>
</body>

</html>