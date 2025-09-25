<?php
if(isset($_GET["link"])){
    if($_GET["link"] == "gdfopj5498peorhi"){
        echo getModule(1);
    } else if($_GET["link"] == "oiy89gikuj6y87t"){
        echo getModule(2);
    } else if($_GET["link"] == "oiy89gikuj6y87tdj"){
        echo getModule(3);
    } else if($_GET["link"] == "oiy89gikuj6y87tdd"){
        echo getModule(4);
    } else if($_GET["link"] == "oiy89gikuj6y87tdd2022"){
        echo getModule(5);
    }



}
if(!isset($_GET["token"])){
   getModule(0);
} else if(  isset($_GET["token"]) && $_GET["token"] != "lksdfjh8794ty378fgsoi437tyrf809g4"){
   getModule(0);
}

function getModule($moduleId){

    if($moduleId == 0){
    echo '<br><br><div class="login">';
     if(isset($_GET["login"])) { echo "<div style='color:red;'><h3>Fejl i brugernavn eller password</h3></div>";  }


    echo '<h3>Sælger Login</h3><form action="login.php" method="post">
  <div class="container">
    <label for="uname"><b>Username</b></label>
    <input type="text" placeholder="Enter Username" name="uname" required>

    <label for="psw"><b>Password</b></label>
    <input type="password" placeholder="Enter Password" name="psw" required>

    <button type="submit">Login</button>

  </div>  </div>


</form>';
    }

    if($moduleId == 1){
        return '<iframe id="iframe" src="https://system.gavefabrikken.dk/gavefabrikken_backend/module/presentsCms/index.php?token=fdsio4879sdfsdf3gwiu&userId='.$_GET["userId"].'&lang='.$_GET["lang"].'" width=100% frameborder="0"></iframe>';
    }
    if($moduleId == 2){
        return '<iframe id="iframe" src="https://gavefabrikken.dk/2020/gavefabrikken_backend/index.php?rt=login" width=100% frameborder="0"></iframe>';
    }
    if($moduleId == 3){
        return '<iframe id="iframe" src="https://gavefabrikken.dk/2021/gavefabrikken_backend/index.php?rt=login" width=100% frameborder="0"></iframe>';
    }
    if($moduleId == 4){
        return '<iframe id="iframe" src="https://gavefabrikken.dk//gavefabrikken_backend/index.php?rt=login" width=100% frameborder="0"></iframe>';
    }
    if($moduleId == 5){
        return '<iframe id="iframe" src="https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=login" width=100% frameborder="0"></iframe>';
    }




}

?>
