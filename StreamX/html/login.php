<?php
// Configuração do banco de dados
$servername = "localhost:3306";
$username = "root";
$password = "PUC@1234";
$dbname = "StreamX";

// Criando a conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificando a conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

$errorMessage = "";

// Verificando se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recebendo os dados do formulário
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    // Validando o email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = "Por favor, insira um email válido.";
    } else {
        // Preparando a consulta para buscar o usuário pelo email
        $stmt = $conn->prepare("SELECT * FROM Cliente WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        // Verificando se o email foi encontrado
        if ($result->num_rows > 0) {
            // Obtendo os dados do usuário
            $user = $result->fetch_assoc();
            
            // Comparando a senha diretamente (sem criptografia)
            if ($senha == $user['Senha']) {
                // Login bem-sucedido, redirecionar para a página de aluguel
                header("Location: menu.php");
                exit();
            } else {
                $errorMessage = "Senha incorreta. Tente novamente.";
            }
        } else {
            $errorMessage = "Email não encontrado.";
        }

        // Fechando a consulta
        $stmt->close();
    }
}

// Fechando a conexão
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - StreamX</title>
    <link rel="stylesheet" href="../styles.css/login.css">
</head>
<body>

    <div class="login-container">
        <h1>Login</h1>
        <form action="login.php" method="POST">
            <input type="text" name="email" id="email" placeholder="Email" required>
            <input type="password" name="senha" id="password" placeholder="Senha" required>
            <button type="submit" a href="login.php">Entrar</button>
            <p class="error-message"><?php echo $errorMessage; ?></p>
        </form>
        <p>Não tem uma conta? <a href="cadastro.php">Cadastre-se</a></p>
    </div>

</body>
</html>
