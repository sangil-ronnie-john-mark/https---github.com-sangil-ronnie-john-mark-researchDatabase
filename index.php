<?php
SESSION_START();

include "css/plugins.php";
?>
<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>HCC RDU | Login</title>
 
</head>
<body class="d-flex flex-column h-100">

<?php include 'css/navbar.php'?>




<section class="p-3 p-md-4 p-xl-5">
  <div class="container">
    <div class="card border-light-subtle shadow-sm">
      <div class="row g-0">
 
 
 <div class="col-12 col-md-6" style="color: #263A56; background-color: #263A56;">
  <div class="d-flex align-items-center justify-content-center py-5">
    <div class="text-center">
      <img class="img-fluid rounded mb-4" loading="lazy" src="assets/images/hcc.png" width="200" alt="HCC Logo">
      <h4 class="h4 mb-2" style="color: #fff;">HOLY CROSS COLLEGE <br> RESEARCH DATABASE PORTAL</h4>
    </div>
  </div>
</div>



        <div class="col-12 col-md-6">
          <div class="card-body p-3 p-md-4 p-xl-5">
            <div class="row">
              <div class="col-12">
                <div class="mb-5">
                  <h3>Log in</h3>
                </div>
              </div>
            </div>
            <form action="config/login.php" method="POST">
              <div class="row gy-3 gy-md-4 overflow-hidden">
                <div class="col-12">
                  <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                  <input type="username" class="form-control" name="username" id="username" required>
                </div>
                <div class="col-12">
                  <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                  <input type="password" class="form-control" name="password" id="password" value="" required>
                </div>
               
                <div class="col-12">
                  <div class="d-grid">
                    <button class="btn bsb-btn-xl btn-primary" type="submit">Log in</button>
                  </div>
                </div>
              </div>
            </form>
			<?php if (isset($_SESSION['error'])) { ?>
			
	<div id="error-alert" role="alert" class="text-danger">
	  <?= $_SESSION['error'] ?>
	</div>

  <script>
  
    setTimeout(() => {
      const alertElement = document.getElementById('error-alert');
      if (alertElement) {
  
        const alertInstance = bootstrap.Alert.getOrCreateInstance(alertElement);
        alertInstance.close();
      }
    }, 3000);
  </script>
<?php
}
unset($_SESSION['error']);
?>
        
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include 'css/footer.php'?>
</body>
</html>
