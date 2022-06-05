<?php 
  session_start(); 
  if(!isset($_SESSION["zalogowany"])){
    $_SESSION["zalogowany"] = false;
  }
  $link = @mysqli_connect("localhost", "root", "", "sklep_projekt");

  if(!$link){
      echo "Błąd połączenia";
      header("refresh: 2; url=index.php");
  }
  require_once("czyszczenie.php");
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>POL&ROLLS</title>
    <link rel="stylesheet" href="style.css">
  </head>
  <body>

    <nav>
      <div class="container">
        <div class="logo">
          <a href="index.php">POL & ROLLS</a>
        </div>
        <div class="menu">
          <ul>
            <li><img src="user.png" alt="Konto">
              <div class="menu-plus menu-u">
                <?php
                  if($_SESSION["zalogowany"] == false){
                    echo "
                      <form action='logowanie.php'><input type='submit' value='Zaloguj się'></form>
                      <hr>
                      <form action='rejestracja.php'><input type='submit' value='Załóż konto'></form>
                    ";
                  }else{
                    echo "
                      Witaj, ".$_SESSION["imie_i_nazwisko"]."
                      <hr>
                      <form action='process.php' method='post'><input type='submit' name='submitW'value='Wyloguj się'></form>
                    ";
                  }
                ?>
              </div>
            </li>
            <div class="kreska"></div>
            <li><img src="shopping-cart.png" alt="Koszyk">
              <div class="menu-plus menu-k">
                <?php
                  if(isset($_SESSION["admin"]) && $_SESSION["admin"] == true){
                    echo "<div>Nie można korzystać<br>z koszyka,<br>zalogowano jako administrator</div>";
                  }
                ?>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <main>
      
    <div class="formularz">
        <div class="produkt">
            <form action="process.php" method="post">
                <?php foreach ($_POST as $key => $value) { ?>
                  <input type="hidden" name="<?php echo $key; ?>" value="<?php echo $value; ?>">
                <?php }?>
                <input type="text" name="kategoria" placeholder="Podaj nazwę kategorii...">
				        <input type="submit" name='submitADK' value='Dodaj kategorię'>
            </form>
      	</div>
    </div>
      
    </main>

  </body>
</html>