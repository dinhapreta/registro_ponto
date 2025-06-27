<?php
session_start();

// Verifica se o formulário foi submetido
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verifica se o botão "Sair" foi clicado
    if(isset($_POST['sair'])) {
        // Configura a sessão de redirecionamento
        $_SESSION['redirect_to_login'] = true;
        // Redireciona para a página de login
        header('Location: logout.php');
        exit;
    }
}

// Conexão com o MySQL
$usuario = 'root';
$senha = '';
$database= 'registro_ponto';
$host='localhost';

// Cria a conexão
$conn = new mysqli($host, $usuario, $senha, $database);

// Verifica a conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Consulta SQL para verificar as credenciais
    $sql = "SELECT id FROM usuarios WHERE username = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Credenciais corretas, obtém o ID do usuário e armazena na sessão
        $row = $result->fetch_assoc();
        session_start();
        $_SESSION['id_usuario'] = $row['id'];
        // Redireciona para pausas.php
        header('Location: pausas.php');
        exit;
    } else {
        // Credenciais incorretas, exibe uma mensagem de erro
        echo "Usuário ou senha incorretos.";
    }
}

// Fecha a conexão
$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
      rel="stylesheet"
    />
    <style>
      body {
        font-family: "Poppins", sans-serif;
        background-color: #e9ecef;
        margin: 0;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100vh;
      }
      .container {
        display: flex;
        background-color: #fff;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        max-width: 800px;
      }
      .image-container {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 20px;
      }
      .image-container img {
        max-width: 400px;
        height: auto;
        border-radius: 10px;
      }
      .form-container {
        max-width: 400px;
      }
      h2 {
        text-align: center;
        color: #5052c9;
        margin-bottom: 20px;
      }
      label {
        font-size: 16px;
        color: #333;
        display: block;
        margin-bottom: 8px;
      }
      input {
        width: 100%;
        padding: 12px;
        margin-bottom: 20px;
        border: 1px solid #ced4da;
        border-radius: 5px;
        box-sizing: border-box;
        font-size: 16px;
        background-color: #f8f9fa;
        transition: background-color 0.3s, border 0.3s;
      }
      input:focus {
        background-color: #fff;
        border: 1px solid #80bdff;
        outline: none;
      }
      .button {
        background-color: #5052c9;
        color: white;
        padding: 12px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
        width: 100%;
        transition: background-color 0.3s;
      }
      .button:hover {
        background-color: #0056b3;
      }
    </style>
  </head>
  <body>
    <div class="container">
      <div class="image-container">
        <img src="./images/fundo.JPG" alt="Logo" />
      </div>
      <div class="form-container">
        <h2>Login</h2>
        <form action="" method="post">
          <label for="username">Usuário:</label>
          <input type="text" id="username" name="username" required />
          <label for="password">Senha:</label>
          <input type="password" id="password" name="password" required />
          <input class="button" type="submit" value="Login" />
        </form>
      </div>
    </div>

    <script>
      // Função para desabilitar o botão de voltar do navegador
      function desabilitarBotaoVoltar() {
        window.history.pushState(null, "", window.location.href);
        window.onpopstate = function () {
          window.history.pushState(null, "", window.location.href);
        };
      }

      // Chama a função ao carregar a página
      desabilitarBotaoVoltar();
    </script>
  </body>
</html>