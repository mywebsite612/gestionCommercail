<?php
include_once 'connexion.php';
session_start();

$errorMessage = "";

// Vérifier si le formulaire est soumis via JS
if (isset($_POST['user']) && isset($_POST['password'])) {
    $user = $_POST['user'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM login WHERE user=? AND password=?");
    $stmt->bind_param("ss", $user, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $_SESSION['user'] = $user;
        header("Location: premierpage.php");
        exit();
    } else {
        $errorMessage = "Utilisateur ou mot de passe incorrect ❌";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion / DATA SOFTWARE</title>
    <link rel="stylesheet" href="user.css">
    <script src="user.js"></script>
    <link rel="website icon" href="logo.png">
</head>
<body>

<!-- MODAL 1 : Vérification du user -->
<div id="modalUserCheck" class="modal" style="display:none;">
    <div class="modal-content">
        <h3>Vérifier votre compte</h3>
        <label>User :</label>
        <input type="text" id="checkUserInput" placeholder="Entrez votre user">
        <button onclick="verifyUser()">Continuer</button>
        <p id="modalUserError" style="color:red;"></p>
    </div>
</div>

<!-- MODAL 2 : Modifier le mot de passe -->
<div id="modalChangePass" class="modal" style="display:none;">
    <div class="modal-content">
        <h3>Nouveau mot de passe</h3>
        <input type="hidden" id="hiddenUser">
        
        <label>Nouveau mot de passe :</label>
        <input type="password" id="newPassword">

        <label>Confirmer :</label>
        <input type="password" id="confirmPassword">

        <button onclick="updatePassword()">Modifier</button>
        <p id="modalPassError" style="color:red;"></p>
    </div>
</div>

<!-- Message erreur connexion -->
<?php if (!empty($errorMessage)) : ?>
    <div id="error-message" style="color: red; text-align:center; margin-bottom:10px;">
        <?php echo $errorMessage; ?>
    </div>
    <script>
        setTimeout(function() {
            var msg = document.getElementById('error-message');
            if (msg) {
                msg.style.display = 'none';
            }
        }, 3000);
    </script>
<?php endif; ?>

<div class="container">
    <div class="login-section">
        <div class="logo">
            <div class="logo-icon"><img src="logo.png"></div>
            <span class="logo-text">DATA SOFTWARE </span>
        </div>
        
        <div class="welcome-message">
            <h1>Bienvenue </h1>
            <p>Connectez-vous à votre compte</p>
        </div>
        
        <form class="login-form">
            <div class="form-group">
                <label for="email">user</label>
                <input type="email" id="email" placeholder="Enter your email" required>
            </div>
            
            <div class="form-group password-group">
                <label for="password">Password</label>
                <div class="password-input-container">
                    <input type="password" id="password" placeholder="Enter your password" required>
                    <button type="button" class="toggle-password" id="togglePassword">
                        <span class="eye-icon">👁️</span>
                    </button>
                </div>
            </div>
            
            <div class="form-options">
                <label class="checkbox-container">
                    <input type="checkbox" id="remember">
                    <span class="checkmark"></span>
                  Sauvegarder le mot de passe
                </label>
               <a href="#" class="forgot-password" onclick="openUserModal()">Oublier le mot de passe ?</a>
            </div>
             <a class="signin-btn" href="#">SE CONNECTER</a>
           
        </form>

    </div>
    
    <div class="welcome-section">
        <div class="welcome-content">
            <h2>Application Gestion Commerciale</h2>
            <p>DATA SOFTWARE </p>
        </div>
    </div>
</div>

<!-- SCRIPT LOGIN -->
<script>
document.querySelector('.signin-btn').addEventListener('click', function(e){
    e.preventDefault(); 

    var user = document.getElementById('email').value;
    var password = document.getElementById('password').value;

    if(user === "" || password === ""){
        alert("Veuillez remplir tous les champs");
        return;
    }

    var form = document.createElement('form');
    form.method = 'POST';
    form.action = ''; 

    var inputUser = document.createElement('input');
    inputUser.type = 'hidden';
    inputUser.name = 'user';
    inputUser.value = user;
    form.appendChild(inputUser);

    var inputPass = document.createElement('input');
    inputPass.type = 'hidden';
    inputPass.name = 'password';
    inputPass.value = password;
    form.appendChild(inputPass);

    document.body.appendChild(form);
    form.submit();
});
</script>

<!-- SCRIPT MOT DE PASSE OUBLIÉ -->
<script>
function openUserModal() {
    document.getElementById("modalUserCheck").style.display = "flex";
}

// Vérifier si user existe
function verifyUser() {
    let user = document.getElementById("checkUserInput").value.trim();

    if (user === "") {
        document.getElementById("modalUserError").innerText = "Veuillez entrer un user.";
        return;
    }

    // Pour le debug : afficher dans la console la requête et la réponse
    fetch("verify_user.php", {
        method: "POST",
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        body: "user=" + encodeURIComponent(user)
    })
    .then(r => r.text())
    .then(raw => {
        const data = raw.trim(); // très important : supprime espaces/newlines
        console.log("verify_user.php response (raw):", JSON.stringify(raw));
        console.log("verify_user.php response (trim):", JSON.stringify(data));
        if (data === "OK") {
            document.getElementById("hiddenUser").value = user;
            document.getElementById("modalUserCheck").style.display = "none";
            document.getElementById("modalChangePass").style.display = "flex";
            document.getElementById("modalUserError").innerText = "";
        } else {
            document.getElementById("modalUserError").innerText = "Utilisateur incorrect ❌";
        }
    })
    .catch(err => {
        console.error("Erreur fetch verify_user.php:", err);
        document.getElementById("modalUserError").innerText = "Erreur serveur. Voir console.";
    });
}



// Modifier le mot de passe
function updatePassword() {
    let user = document.getElementById("hiddenUser").value;
    let pass = document.getElementById("newPassword").value;
    let confirm = document.getElementById("confirmPassword").value;

    if (pass !== confirm) {
        document.getElementById("modalPassError").innerText = "Les mots de passe ne correspondent pas !";
        return;
    }

    fetch("update_password.php", {
        method: "POST",
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        body: "user=" + user + "&password=" + pass
    })
    .then(r => r.text())
    .then(data => {
        if (data === "OK") {
            alert("Mot de passe modifié avec succès !");
            location.reload();
        } else {
            document.getElementById("modalPassError").innerText = "Erreur lors de la modification.";
        }
    });
}
</script>

</body>
</html>
