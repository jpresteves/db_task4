<?php
// Mostra registos do tipo O numa grelha.
// Em cada linha deve conter UserID, EncID, Tempo --> tempo que o user demorou a editar a encomenda DATEDIFF
//  Timer para refresh.

// Initialize the session
session_start();

// Check if the user is already logged in, if yes then redirect him to welcome page
if (!isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] !== 1) {
    // require_once './connect.php';
    header('Location: ./connect.php');
}
$encid = -1;
$refreshrate=5; // seconds
$servername = $_SESSION['servername'];
$database = $_SESSION['database'];
$username = $_SESSION['username'];
$password = $_SESSION['password'];


if (isset($_GET["refreshrate"]) && !Empty($_GET["refreshrate"])) {
    $refreshrate = $_GET["refreshrate"];
}

// Create connection
$conn = mysqli_connect($servername, $username, $password, $database);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$sql = " SELECT LO1.UserId, LO1.Objecto as EncId, TIMEDIFF(LO2.Valor, LO1.Valor) as Tempo ";
$sql = $sql." FROM LogOperations LO1, LogOperations LO2";
$sql = $sql." WHERE LO1.Referencia = LO2.Referencia and LO1.DCriacao < LO2.DCRiacao AND LO1.EventType=LO2.EventType";
$sql = $sql." AND LO1.EventType='O'";

$result = mysqli_query($conn, $sql);
$rows = mysqli_fetch_all($result, MYSQLI_ASSOC);


echo "<html>";
echo "<head>";
echo "<meta http-equiv=\"refresh\" content=\"".$refreshrate."\">";
echo "</head>";
echo "<body>";
echo "<table width='500px'>";
echo "<thead><td>UserId</td><td>EncId</td><td>Tempo</td></thead>";
foreach($rows as $row) {
    echo "<tr>";
    echo "<td>".$row["UserId"]."</td>";
    echo "<td>".$row["EncId"]."</td>";
    echo "<td>".$row["Tempo"]."</td>";
    echo "</tr>";
}
echo "</table>";

echo "<hr>";

echo "<form action=\"./logtempo.php\" method=\"get\" name=\"setrefreshrate\">";
echo "<label for='refreshrate'>Set refresh rate (seconds):</label>";
    echo "<input type='text' name='refreshrate' value='".$refreshrate."'></input>";
    echo "<input type='submit' name='submit' value='Set rate'></input>";
echo "</form>";
echo "<button onClick=\"window.location.reload();\">Refresh Page</button>";
echo "<hr><a href=\"main.php\">Back to main </a>";



echo "</body>";
echo "</html>";




