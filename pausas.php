<?php
// Define o fuso horário do Brasil (UTC-3)
date_default_timezone_set('America/Sao_Paulo');

// Isso instrui o navegador a não armazenar em cache a página
header("Cache-Control: no-cache, must-revalidate");

session_start();

// Verifica se o usuário está autenticado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: logout.php');
    exit;
}

// Verifica se a sessão de redirecionamento está configurada
if (isset($_SESSION['redirect_to_login']) && $_SESSION['redirect_to_login'] === true) {
    unset($_SESSION['redirect_to_login']);
    header('Location: index.php');
    exit;
}

// Configurações do banco de dados (InfinityFree)
$host = 'seu host';
$usuario = 'seu usuario';
$senha = 'sua senha';
$banco = 'seu banco de dados';

// Conexão com o MySQL
$conn = new mysqli($host, $usuario, $senha, $banco);

// Verifica a conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Verifica se o formulário foi submetido para registrar pausa
if (isset($_POST['tipo_pausa'])) {
    $tipo_pausa = $_POST['tipo_pausa'];
    $id_usuario = $_SESSION['id_usuario'];

    // Verifica se já registrou essa pausa hoje
    $sql = "SELECT COUNT(*) AS total FROM pausas WHERE tipo = ? AND id_usuario = ? AND DATE(data_hora) = CURDATE()";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("si", $tipo_pausa, $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row['total'] > 0) {
            echo "<div class='mensagem'>Você já registrou essa pausa hoje.</div>";
        } else {
            $sql_insert = "INSERT INTO pausas (tipo, id_usuario) VALUES (?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            if ($stmt_insert) {
                $stmt_insert->bind_param("si", $tipo_pausa, $id_usuario);
                if ($stmt_insert->execute()) {
                    echo "<div class='mensagem'>Pausa registrada: " . htmlspecialchars($tipo_pausa) . "</div>";
                } else {
                    echo "<div class='mensagem'>Erro ao registrar a pausa: " . $stmt_insert->error . "</div>";
                }
            } else {
                echo "<div class='mensagem'>Erro ao preparar a consulta: " . $conn->error . "</div>";
            }
        }
        $stmt->close();
    } else {
        echo "<div class='mensagem'>Erro ao preparar a consulta: " . $conn->error . "</div>";
    }
}

// Fechamento da conexão ao final da página
function fecharConexao() {
    global $conn;
    if ($conn) {
        $conn->close();
    }
}

register_shutdown_function('fecharConexao');

// Verifica se o botão "Sair" foi clicado
if (isset($_POST['sair'])) {
    echo '<script>
        var confirmar = confirm("Deseja realmente sair?");
        if (confirmar) {
            location.href = "logout.php";
        }
    </script>';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Pausas</title>
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link
  href="https://fonts.googleapis.com/css2?family=Poppins&display=swap"
  rel="stylesheet"
/>
<style>
* {
  font-family: "Poppins", sans-serif;
}
body {
  background-image: linear-gradient(to right, #ffffff, #00000056);
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  flex-direction: column;
}
h2 {
  text-align: center;
  margin-left: 20px;
}
button {
  padding: 10px;
  border-radius: 5px 5px 0 0;
  color: black;
  font-size: 18px;
  font-weight: 400;
  transition: 0.3ms;
  cursor: pointer;
}
button:hover {
  transform: scale(1.05);
}
form {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 20px;
  flex-direction: column;
}
.entrada button { background-color: #83e509; }
.cafe button { background-color: #ad6961; }
.almoco button { background-color: #ea515192; }
.reuniao button { background-color: #676767b3; }
.banheiro button { background-color: #b2b3da; }
.saida button { background-color: #e84227; }
.sair-usuario button {
  background-color: black;
  color: white;
}
a {
  color: black;
  font-weight: bold;
  font-size: 18px;
  text-decoration: none;
}
a:hover {
  text-decoration: underline;
}
.historico {
  position: absolute;
  top: 20px;
  right: 50px;
  color: #2b13ef;
}
.mensagem {
  margin-top: 10px;
  color: #007bff;
  font-size: 14px;
  font-weight: bold;
}
</style>
</head>
<body>
<section>
  <h2>Registrar Pausas</h2>
  <form method="post">
    <div class="entrada">
      <img src="images/entrada.png" alt="entrada" />
      <button type="submit" name="tipo_pausa" value="Entrada">Entrada</button>
    </div>
    <div class="cafe">
      <img src="images/cafe.png" alt="cafe" />
      <button type="submit" name="tipo_pausa" value="Pausa Café">Pausa Café</button>
      <button type="submit" name="tipo_pausa" value="Retorno Café">Retorno Café</button>
    </div>
    <div class="almoco">
      <img src="images/almoço.png" alt="almoço" />
      <button type="submit" name="tipo_pausa" value="Pausa Almoço">Pausa Almoço</button>
      <button type="submit" name="tipo_pausa" value="Retorno Almoço">Retorno Almoço</button>
    </div>
    <div class="reuniao">
      <img src="images/reuniao.png" alt="reunião" />
      <button type="submit" name="tipo_pausa" value="Pausa Reunião">Pausa Reunião</button>
      <button type="submit" name="tipo_pausa" value="Retorno Reunião">Retorno Reunião</button>
    </div>
    <div class="banheiro">
      <img src="images/banheiro.png" alt="banheiro" />
      <button type="submit" name="tipo_pausa" value="Pausa Banheiro">Pausa Banheiro</button>
      <button type="submit" name="tipo_pausa" value="Retorno Banheiro">Retorno Banheiro</button>
    </div>
    <div class="saida">
      <img src="images/saida.png" alt="saida" />
      <button type="submit" name="tipo_pausa" value="Saída">Saída</button>
    </div>
    <br />
    <a class="historico" href="historico.php">Ver Histórico ➡️</a>
    <br />
    <div class="sair-usuario">
      <button type="submit" name="sair">Sair</button>
    </div>
  </form>
</section>
<script>
  window.addEventListener("popstate", function () {
    window.location.href = "index.php";
  });
</script>
</body>
</html>
