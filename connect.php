
<?php
// Initialize the session
session_start();

// Check if the user is already logged in, if yes then redirect him to welcome page
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === 1) {
    header('Location: ./main.php');
    exit;
}
    if (isset($_POST['submit'])) {
        $servername = $_POST['hname'];
        $database = $_POST['dbname'];
        $username = $_POST['uname'];
        $password = $_POST['password'];

        // Create connection
        $conn = mysqli_connect($servername, $username, $password, $database);

        // Check connection
        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
        }
        $_SESSION["loggedin"]=1;
        $_SESSION["servername"]=$servername;
        $_SESSION["database"]=$database;
        $_SESSION["username"]=$username;
        $_SESSION["password"]=$password;
        header('Location: main.php');
    } else {
       // echo "No POST";
    }


?>

<html>
<head></head>
    <body>
        <form action="./connect.php" method="post">
            <table>
                <tr>
                    <td><label for="hname">Host name:</label></td>
                    <td><input type="text" id="hname" name="hname" value="206.189.247.79"><br></td>
                </tr>
            <tr>
                <td><label for="dbname">Database name:</label></td>
                <td><input type="text" id="dbname" name="dbname" value="MEI_TRAB"><br></td>
            </tr>
            <tr>
                <td><label for="uname">User name:</label></td>
                <td><input type="text" id="uname" name="uname" value="meidbt4"><br></td>
            </tr>
            <tr>
                <td><label for="password">Password:</label></td>
                <td><input type="password" id="password" name="password" value="123456"><br></td>
            </tr>
            <tr> <td></td>
                <td><button type="submit" name="submit">Connect</button> </td>
            </tr>
            </table>
        </form>
    </body>
</html>
