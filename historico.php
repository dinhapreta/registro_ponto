<?php
session_start();

// Verifica se o usuário está autenticado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: logout.php');
    exit;
}

// Configurações do banco de dados
$host = 'sql304.infinityfree.com';
$usuario = 'if0_39333353';
$senha = 'mpyp3rkaaFj2wx3';
$banco = 'if0_39333353_registro_ponto';

// Conexão com o MySQL
$mysqli = new mysqli($host, $usuario, $senha, $banco);

// Verifica a conexão
if ($mysqli->connect_error) {
    die("Falha na conexão: " . $mysqli->connect_error);
}

$id_usuario = $_SESSION['id_usuario'];

// Processa requisição Ajax para buscar histórico por data
if (isset($_POST['data'])) {
    $data_selecionada = $_POST['data'];

    // Prepared statement para evitar SQL Injection
    $stmt = $mysqli->prepare("SELECT tipo, data_hora FROM pausas WHERE id_usuario = ? AND DATE(data_hora) = ? ORDER BY data_hora DESC");
    $stmt->bind_param("is", $id_usuario, $data_selecionada);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<h3>Histórico de Pausas em $data_selecionada</h3>";
        echo "<table>";
        echo "<tr><th>Tipo</th><th>Data e Hora</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr><td>" . htmlspecialchars($row["tipo"]) . "</td><td>" . htmlspecialchars($row["data_hora"]) . "</td></tr>";
        }
        echo "</table>";
    } else {
        echo "Nenhum registro de pausa encontrado para esta data.";
    }

    $stmt->close();
    $mysqli->close();
    exit;
}

// Controle de mês e ano na sessão
if (isset($_GET['mes_anterior'])) {
    $_SESSION['mes_atual']--;
    if ($_SESSION['mes_atual'] < 1) {
        $_SESSION['mes_atual'] = 12;
        $_SESSION['ano_atual']--;
    }
} elseif (isset($_GET['mes_seguinte'])) {
    $_SESSION['mes_atual']++;
    if ($_SESSION['mes_atual'] > 12) {
        $_SESSION['mes_atual'] = 1;
        $_SESSION['ano_atual']++;
    }
}

if (isset($_GET['ano'])) {
    $_SESSION['ano_atual'] = intval($_GET['ano']);
}

if (!isset($_SESSION['mes_atual'])) {
    $_SESSION['mes_atual'] = date('n');
}

if (!isset($_SESSION['ano_atual'])) {
    $_SESSION['ano_atual'] = date('Y');
}

$mes_atual = $_SESSION['mes_atual'];
$ano_atual = $_SESSION['ano_atual'];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Calendário e Histórico de Pausas</title>
<style>
/* seu CSS permanece igual */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
}
h1, h2 {
    color: #333;
}
.calendar-container {
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 600px;
    margin: 20px 0;
}
.calendar-container div {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}
.calendar-container form {
    margin: 0;
}
.calendar-container select {
    padding: 5px;
    font-size: 16px;
}
.calendar-container table {
    width: 100%;
    border-collapse: collapse;
}
.calendar-container th, .calendar-container td {
    padding: 10px;
    text-align: center;
    border: 1px solid #ddd;
}
.calendar-container th {
    background-color: #f2f2f2;
    color: #555;
}
.calendar-container td a {
    color: #007bff;
    text-decoration: none;
}
.calendar-container td a:hover {
    text-decoration: underline;
}
#historico {
    margin-top: 20px;
    width: 100%;
    max-width: 600px;
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}
#historico div {
    margin-bottom: 10px;
}
#historico div span {
    display: inline-block;
    margin-right: 10px;
}
a {
    color: #007bff;
    text-decoration: none;
}
a:hover {
    text-decoration: underline;
}
.menu-link {
    position: absolute;
    top: 20px;
    left: 20px;
    color:#2b13ef;
}
</style>
</head>
<body>
<h1>Histórico de Pausas</h1> <br>

<h2>Calendário</h2>
<div class="calendar-container">
<?php
echo "<div>";
echo "<a href='?mes_anterior'>&lt;</a>";
echo date("F Y", mktime(0, 0, 0, $mes_atual, 1, $ano_atual));
echo "<a href='?mes_seguinte'>&gt;</a>";
echo "</div>";

// Menu suspenso ano
echo "<div>";
echo "<form method='get'>";
echo "<select name='ano' onchange='this.form.submit()'>";
for ($ano = date('Y') - 5; $ano <= date('Y') + 5; $ano++) {
    $selected = ($ano == $ano_atual) ? 'selected' : '';
    echo "<option value='$ano' $selected>$ano</option>";
}
echo "</select>";
echo "</form>";
echo "</div>";

echo "<table>";
echo "<tr>";
echo "<th>Seg</th><th>Ter</th><th>Qua</th><th>Qui</th><th>Sex</th><th>Sáb</th><th>Dom</th>";
echo "</tr>";

$total_dias = date('t', mktime(0, 0, 0, $mes_atual, 1, $ano_atual));
$primeiro_dia = date('N', mktime(0, 0, 0, $mes_atual, 1, $ano_atual));
$contador_dias = 1;

for ($i = 1; $i <= 6; $i++) {
    echo "<tr>";
    for ($j = 1; $j <= 7; $j++) {
        if ($contador_dias <= $total_dias && ($i > 1 || $j >= $primeiro_dia)) {
            $data = "$ano_atual-" . str_pad($mes_atual, 2, "0", STR_PAD_LEFT) . "-" . str_pad($contador_dias, 2, "0", STR_PAD_LEFT);
            echo "<td><a href='#' onclick=\"buscarPausas('$data')\">$contador_dias</a></td>";
            $contador_dias++;
        } else {
            echo "<td></td>";
        }
    }
    echo "</tr>";
}

echo "</table>";
?>
</div>

<div id="historico"></div> <!-- Aqui será exibido o histórico de pausas -->

<br><br><a class="menu-link" href='pausas.php'> ↩️ Retornar ao Menu de Pausas</a>

<script>
function buscarPausas(data) {
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            document.getElementById("historico").innerHTML = this.responseText;
        }
    };
    xmlhttp.open("POST", "historico.php", true);
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlhttp.send("data=" + data);
}
</script>

</body>
</html>
