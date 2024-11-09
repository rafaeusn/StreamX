<?php
// Configuração do banco de dados
$servername = "localhost:3306"; // ou seu servidor de banco de dados
$username = "root"; // seu usuário
$password = "PUC@1234"; // sua senha
$dbname = "StreamX"; // nome do seu banco de dados

// Criando a conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificando a conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Inicializando uma variável para mensagens de erro ou sucesso
$message = "";

// Verificando se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recebendo os dados do formulário
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $senha_confirm = $_POST['senha_confirm'];

    // Validação dos campos
    if ($senha !== $senha_confirm) {
        $message = "As senhas não coincidem!";
    } else {
        // Verificando se o email já existe
        $email_check = $conn->prepare("SELECT * FROM Cliente WHERE Email = ?");
        $email_check->bind_param("s", $email);
        $email_check->execute();
        $email_result = $email_check->get_result();

        if ($email_result->num_rows > 0) {
            $message = "Este email já está cadastrado!";
        } else {
            // Preparando a consulta para inserir a senha sem criptografia
            $stmt = $conn->prepare("INSERT INTO Cliente (Nome, Email, Senha) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $nome, $email, $senha);  // Senha sem criptografar

            // Executando a consulta
            if ($stmt->execute()) {
                // Redirecionar para a página desejada após sucesso
                header("Location: login.php"); // Substitua "sucesso.php" pela sua página de sucesso
                exit(); // Garantir que o script seja interrompido após o redirecionamento
            } else {
                $message = "Erro: " . $stmt->error;
            }

            // Fechando a consulta
            $stmt->close();
        }

        // Fechando a consulta de verificação de email
        $email_check->close();
    }
}

// Fechando a conexão
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro</title>
    <link rel="stylesheet" href="../styles.css/cadastro.css">
</head>
<body>
    <div class="login-container">
        <h1>Cadastro</h1>
        <form action="cadastro.php" method="POST">
            <input type="text" name="nome" placeholder="Nome Completo" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="senha" placeholder="Senha" required>
            <input type="password" name="senha_confirm" placeholder="Confirme a Senha" required>
            <button type="submit">Cadastrar</button>
        </form>
        
        <?php if (!empty($message)): ?>
            <p><?php echo $message; ?></p>
        <?php endif; ?>

        <p>Já tem uma conta? <a href="login.php">Faça login</a></p>
    </div>
</body>
</html>
