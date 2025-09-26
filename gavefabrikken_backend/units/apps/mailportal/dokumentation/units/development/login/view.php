<html>
<head>

</head>
<body>

    <h1>Login test page</h1>
    <hr>

    <?php if(trim($output ?? "") != "") { ?>

        <h2>Resultat</h2>
        <p>
            <?php echo $output; ?>
        </p>

    <?php } ?>

    <h2>Test username / password login</h2>
    <form method="post" action="?rt=unit/development/login/login">
        <table>
            <tr>
                <td>Username</td>
                <td><input type="text" name="username" value="<?php echo $_POST["username"] ?? ""; ?>"></td>
            </tr>
            <tr>
                <td>Password</td>
                <td><input type="password" name="password" value="<?php echo $_POST["password"] ?? ""; ?>"></td>
            </tr>
            <tr>
                <td>Lock to shop id</td>
                <td><input type="text" name="shopid" value="<?php echo $_POST["shopid"] ?? ""; ?>"></td>
            </tr>
            <tr>
                <td>Lock to concept</td>
                <td><input type="text" name="concept" value="<?php echo $_POST["concept"] ?? ""; ?>"></td>
            </tr>
            <tr>
                <td>Opret ny token</td>
                <td><input type="checkbox" value="1" name="createtoken" <?php if(intval($_POST["createtoken"] ?? 0) == 1) ?>></td>
            </tr>
            <tr>
                <td></td>
                <td><button type="submit">Tjek login</button></td>
            </tr>
        </table>
    </form>

    <br><hr><br>
    <h2>Check token</h2>
    <form method="post" action="?rt=unit/development/login/token">
        <table>
            <tr>
                <td>Token</td>
                <td><input type="text" name="token" value="<?php echo $_POST["token"] ?? ""; ?>"></td>
            </tr>
            <tr>
                <td>Lock to shop id</td>
                <td><input type="text" name="shopid" value="<?php echo $_POST["shopid"] ?? ""; ?>"></td>
            </tr>
            <tr>
                <td>Lock to concept</td>
                <td><input type="text" name="concept" value="<?php echo $_POST["concept"] ?? ""; ?>"></td>
            </tr>
            <tr>
                <td></td>
                <td><button type="submit">Tjek token</button></td>
            </tr>
        </table>
    </form>

    <br><hr><br>
    <h2>Check shop close date</h2>
    <form method="post" action="?rt=unit/development/login/close">
        <table>
            <tr>
                <td>shop_id</td>
                <td><input type="text" name="shopid" value="<?php echo $_POST["shopid"] ?? ""; ?>"></td>
            </tr>
            <tr>
                <td>expire_date</td>
                <td><input type="expire_date" name="expire_date" value="<?php echo $_POST["expire_date"] ?? ""; ?>"></td>
            </tr>
            <tr>
                <td></td>
                <td><button type="submit">Tjek lukke dato</button></td>
            </tr>
        </table>
    </form>



</body>
</html>