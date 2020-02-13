<header>
  <div class='site-width'>
    <h1><a href='display.php'>SOLVO</a></h1>
    <nav id='top-nav'>
      <ul>
        <?php if(empty($_SESSION['user_id'])){ ?>
        <li><a href='signup.php'>signup</a></li>
        <li><a href='login.php'>login</a></li>

        <?php }else{ ?>
        <li><a href='logout.php'>logout</a></li>
        <li><a href='mypage.php'>mypage</a></li>
        <?php } ?>
      </ul>
    </nav>
  </div>
</header>