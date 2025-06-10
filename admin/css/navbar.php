<?php
include "../css/plugins.php";
?>

<nav class="navbar navbar-expand-sm" style="background-color: #263A56">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php"><img src="../assets/images/rdu.png" height="50px"></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mynavbar">
      <span class="navbar-toggler-icon"></span>
    </button>
	  
    <div class="collapse navbar-collapse" id="mynavbar">
      <ul class="navbar-nav me-auto">
        <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link" style="color: white" href="index.php">Search</a>
        </li>
		<li class="nav-item">
          <a class="nav-link" style="color: white" href="submissions.php">Submissions</a>
        </li>
		<li class="nav-item">
          <a class="nav-link" style="color: white" href="similaritySearch.php">Similarity Index</a>
        </li>
      </ul>
      </ul>
      <form class="d-flex" method="POST" action="../config/logout.php">
        <button class="btn btn-outline-light" type="submit">Logout</button>
      </form>
    </div>
  </div>
</nav>