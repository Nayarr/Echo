    <body>
        <?php
        
        $loginHTML = urlencode($utilisateur->getLogin());
        $nomHTML = htmlspecialchars($utilisateur->getNom());
        $prenomHTML = htmlspecialchars($utilisateur->getPrenom());
        $ProfilHTML = htmlspecialchars($utilisateur->getProfil());
  
        
        ?>
        <form method="GET" action="../web/frontController.php">
            <fieldset>

                <legend>Mon fomulaire :</legend>
                <p>

                    <label for="login_id">Prenom</label>:
                    <br>
                    <input type ="text" value="<?php echo $loginHTML ?>" name = "login" id="login_id" required/>
                    
                    <label for="nom_id">Nom</label>:
                    <br>
                    <input type="text" value="<?php echo $nomHTML ?>" name="nom" id="nom_id" required/>

                    <label for="Prenom_id">Prenom</label>:
                    <br>

                    <input type ="text" value="<?php echo $prenomHTML ?>" name = "prenom" id="prenom_id" required/>
                    
                    <label for="ProfilUtilisateur">Profil </label>
                    <br>
    
                    <select name="ProfilUtilisateur" id="ProfilUtilisateur">
        
                    <option value="Chercheur">Chercheur</option>
        
                    <option value="Etudiant">Etudiant</option>
        
                    <option value="GrandPUblic">Grand Public</option>


                </select>
                   
                </p>
                <p>
                    <input type='hidden' name='action' value='updated'>
                    <input type="hidden" name="controller" value="utilisateur">
                    <input type="submit" value="Envoyer" />
                </p>
            </fieldset>
        </form>
    </body>
