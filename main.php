<?php

// Initialize the session
session_start();

// Check if the user is already logged in, if yes then redirect him to welcome page
if (!isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] !== 1) {
    // require_once './connect.php';
    header('Location: ./connect.php');
}

$servername = $_SESSION['servername'];
$database = $_SESSION['database'];
$username = $_SESSION['username'];
$password = $_SESSION['password'];



// Create connection
$conn = mysqli_connect($servername, $username, $password, $database);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if ((!isset($_SESSION['isolation'])) || (empty($_SESSION['isolation']))) {
    $_SESSION["isolation"] = "REPEATABLE READ";
}

if (isset($_POST["isolation"])) {
    $sql="SET SESSION TRANSACTION ISOLATION LEVEL ".$_POST["isolation"];
    $result=mysqli_query($conn, $sql);
    $_SESSION['isolation']=$_POST["isolation"];
}


?>

<html>
    <head></head>
    <body>
        <table align="center" border="0">
            <tr>
                <td width="200px"> <a href="appedit.php">App Edit</a> </td>
                <td width="200px"> <a href="browser.php">App Browser</a> </td>
                <td width="200px"> <a href="logtempo.php">App Log Tempo</a> </td>
                <td width="200px"> <a href="log.php">App Log</a> </td>
            </tr>
        </table>
            <br><br>
        <table align="center" border="0">
            <tr>
            <form action="./main.php" method="post" name="isolationmethod">
               <td>
                   <label for="isolation"?>Isolation Level?</label>
               </td>
                <td>
                    <select name="isolation">
                        <option <?php echo (isset($_SESSION["isolation"])&&$_SESSION["isolation"]==='REPEATABLE READ') ? "selected" :  ""  ?> >REPEATABLE READ</option>
                        <option <?php echo (isset($_SESSION["isolation"])&&$_SESSION["isolation"]==='READ COMMITTED') ? "selected" :  ""  ?> >READ COMMITTED</option>
                        <option <?php echo (isset($_SESSION["isolation"])&&$_SESSION["isolation"]==='READ UNCOMMITTED') ? "selected" :  ""  ?> >READ UNCOMMITTED</option>
                        <option <?php echo (isset($_SESSION["isolation"])&&$_SESSION["isolation"]==='SERIALIZABLE') ? "selected" :  ""  ?> >SERIALIZABLE</option>
                    </select>
                    <button type="submit" name="submit">Set Isolation Level</button>
               </td>
            </form>
            </tr>
            <tr align="center">
                <td colspan="2"><a href="https://dev.mysql.com/doc/refman/5.6/en/innodb-transaction-isolation-levels.html#isolevel_repeatable-read" target="_blank">Documentation</a> </td>
            </tr>
        </table>
    <br><br>
    <?php echo "Current isolation level: <br>"; echo isset($_SESSION["isolation"]) ? $_SESSION["isolation"] : "Default (REPEATABLE READ)"; ?>
    </body>
</html>

